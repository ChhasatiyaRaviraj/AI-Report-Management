<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\ReturnRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class GenerateReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:generate
                            {--from= : Start date (Y-m-d)}
                            {--to= : End date (Y-m-d)}
                            {--output= : Output PDF path (optional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate an AI-powered business report with PDF export';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // 1. Validate date inputs
        $fromDate = $this->option('from');
        $toDate = $this->option('to');

        if (!$fromDate || !$toDate) {
            $this->error('Both --from and --to date options are required.');
            return self::FAILURE;
        }

        if (!$this->isValidDate($fromDate) || !$this->isValidDate($toDate)) {
            $this->error('Dates must be in Y-m-d format (e.g., 2026-01-01).');
            return self::FAILURE;
        }

        if ($fromDate > $toDate) {
            $this->error('The --from date must be before or equal to the --to date.');
            return self::FAILURE;
        }

        $this->info("Generating report for period: {$fromDate} to {$toDate}");

        // 2. Aggregate data from the database
        $this->info('Aggregating data...');
        $reportData = $this->aggregateData($fromDate, $toDate);

        // 3. Set output path
        $outputDir = storage_path('app/private/reports');
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $outputPath = $this->option('output')
            ?: $outputDir . '/report_' . now()->format('Ymd_His') . '.pdf';

        $reportData['output_path'] = $outputPath;

        // 4. Call the Python script
        $this->info('Calling Python script for LLM summary and PDF generation...');
        $jsonInput = json_encode($reportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $pythonPath = config('services.python.path', 'python3');
        $scriptPath = base_path('scripts/generate_report.py');

        if (!file_exists($scriptPath)) {
            $this->error("Python script not found at: {$scriptPath}");
            return self::FAILURE;
        }

        $result = Process::timeout(120)
            ->env([
                'GROK_API_KEY' => config('services.grok.key', ''),
                'GROK_MODEL' => config('services.grok.model', 'grok-beta'),
            ])
            ->input($jsonInput)
            ->run("{$pythonPath} {$scriptPath}");

        // 5. Check exit code and capture output
        if ($result->failed()) {
            $stderr = $result->errorOutput();
            $this->error('Python script failed:');
            $this->error($stderr);
            Log::error('Report generation failed', [
                'exit_code' => $result->exitCode(),
                'stderr' => $stderr,
                'from' => $fromDate,
                'to' => $toDate,
            ]);
            return self::FAILURE;
        }

        // Log any warnings from STDERR even on success
        $stderr = $result->errorOutput();
        if (!empty(trim($stderr))) {
            Log::info('Report generation stderr output', ['stderr' => $stderr]);
            $this->line("<comment>Python script output:</comment>");
            $this->line($stderr);
        }

        // 6. Parse STDOUT JSON envelope
        $stdout = trim($result->output());
        $output = json_decode($stdout, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Failed to parse Python script output as JSON.');
            $this->error("Raw output: {$stdout}");
            Log::error('Report generation: invalid JSON output', [
                'stdout' => $stdout,
                'json_error' => json_last_error_msg(),
            ]);
            return self::FAILURE;
        }

        // 7. Verify the PDF was created
        $pdfPath = $output['pdf_path'] ?? $outputPath;

        if (!file_exists($pdfPath)) {
            $this->error("PDF file was not created at: {$pdfPath}");
            return self::FAILURE;
        }

        $fileSize = round(filesize($pdfPath) / 1024, 1);
        $this->info("✅ Report generated successfully!");
        $this->info("   PDF: {$pdfPath} ({$fileSize} KB)");
        $this->newLine();
        $this->info("Executive Summary Preview:");
        $this->line(substr($output['summary'] ?? '', 0, 300) . '...');

        return self::SUCCESS;
    }

    /**
     * Aggregate business data for the given date range.
     */
    private function aggregateData(string $fromDate, string $toDate): array
    {
        // Total orders and revenue
        $orderStats = Order::whereBetween('order_date', [$fromDate, $toDate])
            ->selectRaw('COUNT(*) as total_orders')
            ->selectRaw('COALESCE(SUM(total_amount), 0) as total_revenue')
            ->first();

        // Returns data
        $returnStats = ReturnRecord::whereBetween('return_date', [$fromDate, $toDate])
            ->selectRaw('COUNT(*) as total_returns')
            ->selectRaw('COALESCE(SUM(refund_amount), 0) as total_refunds')
            ->first();

        $totalOrders = (int) $orderStats->total_orders;
        $totalReturns = (int) $returnStats->total_returns;
        $returnRate = $totalOrders > 0
            ? round(($totalReturns / $totalOrders) * 100, 1)
            : 0;

        // Revenue by category
        $revenueByCategory = Order::whereBetween('order_date', [$fromDate, $toDate])
            ->join('products', 'orders.product_id', '=', 'products.id')
            ->select('products.category')
            ->selectRaw('COUNT(orders.id) as orders')
            ->selectRaw('SUM(orders.total_amount) as revenue')
            ->groupBy('products.category')
            ->orderByDesc('revenue')
            ->get()
            ->map(function ($row) use ($orderStats) {
                $totalRevenue = (float) $orderStats->total_revenue;
                return [
                    'category' => $row->category,
                    'orders' => (int) $row->orders,
                    'revenue' => round((float) $row->revenue, 2),
                    'pct_of_total' => $totalRevenue > 0
                        ? round(((float) $row->revenue / $totalRevenue) * 100, 1)
                        : 0,
                ];
            })
            ->toArray();

        // Top 5 products by revenue
        $topProducts = Order::whereBetween('order_date', [$fromDate, $toDate])
            ->join('products', 'orders.product_id', '=', 'products.id')
            ->select('products.name', 'products.sku')
            ->selectRaw('SUM(orders.total_amount) as revenue')
            ->selectRaw('COUNT(orders.id) as orders')
            ->groupBy('products.name', 'products.sku')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get()
            ->map(function ($row) {
                return [
                    'name' => $row->name,
                    'sku' => $row->sku,
                    'revenue' => round((float) $row->revenue, 2),
                    'orders' => (int) $row->orders,
                ];
            })
            ->toArray();

        return [
            'period' => [
                'from' => $fromDate,
                'to' => $toDate,
            ],
            'total_orders' => $totalOrders,
            'total_revenue' => round((float) $orderStats->total_revenue, 2),
            'total_returns' => $totalReturns,
            'total_refunds' => round((float) $returnStats->total_refunds, 2),
            'return_rate_pct' => $returnRate,
            'revenue_by_category' => $revenueByCategory,
            'top_products' => $topProducts,
        ];
    }

    /**
     * Validate a date string is in Y-m-d format.
     */
    private function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}
