<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\ReturnRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use RuntimeException;

class GenerateBusinessReport extends Command
{
    protected $signature = 'report:generate
                            {--from= : Start date (Y-m-d)}
                            {--to= : End date (Y-m-d)}
                            {--output= : Override output PDF path}';

    protected $description = 'Aggregate business data and generate an AI-powered PDF report via the Python script';

    /**
     * Validate date options, aggregate report data from Eloquent models,
     * call the Python script via Process::run, and return the generated PDF path.
     *
     * @return int Exit code (0 = success, 1 = failure)
     *
     * @throws RuntimeException When the Python script fails or produces no output.
     */
    public function handle(): int
    {
        $from = $this->option('from');
        $to = $this->option('to');

        // --- validate inputs --------------------------------------------------
        if (!$from || !$to) {
            $this->error('Both --from and --to are required.');
            return self::FAILURE;
        }

        if (!$this->isValidDate($from) || !$this->isValidDate($to)) {
            $this->error('Dates must be in Y-m-d format (e.g. 2026-01-01).');
            return self::FAILURE;
        }

        if ($from >= $to) {
            $this->error('--from must be earlier than --to.');
            return self::FAILURE;
        }

        $daysDiff = (int) (new \DateTimeImmutable($from))->diff(new \DateTimeImmutable($to))->days;
        if ($daysDiff > 365) {
            $this->error("Date range exceeds 365 days ({$daysDiff} days given).");
            return self::FAILURE;
        }

        $this->info("Generating report: {$from} → {$to} ({$daysDiff} days)");

        // --- aggregate data ---------------------------------------------------
        $this->info('Aggregating data…');
        $payload = $this->buildPayload($from, $to);

        // Allow caller to override the output path (used by the queued job)
        if ($overridePath = $this->option('output')) {
            $payload['output_path'] = $overridePath;
        }

        $jsonPayload = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

        // --- call Python script -----------------------------------------------
        $this->info('Calling Python report generator…');

        $pythonBin = config('services.python.path', 'python3');
        $scriptPath = base_path('scripts/generate_report.py');

        $result = Process::timeout(120)
            ->env([
                'GROK_API_KEY' => config('services.grok.key', ''),
                'GROK_MODEL'   => config('services.grok.model', 'grok-beta'),
            ])
            ->input($jsonPayload)
            ->run("{$pythonBin} {$scriptPath}");

        // Forward STDERR diagnostics
        $stderr = $result->errorOutput();
        if (trim($stderr) !== '') {
            $this->line('<comment>Python stderr:</comment>');
            $this->line($stderr);
        }

        if ($result->exitCode() !== 0) {
            Log::error('report:generate — Python script failed', [
                'exit_code' => $result->exitCode(),
                'stderr'    => $stderr,
                'from'      => $from,
                'to'        => $to,
            ]);
            throw new RuntimeException(
                'Report generation failed. Check the application log for details.'
            );
        }

        $pdfPath = trim($result->output());

        if ($pdfPath === '' || !file_exists($pdfPath)) {
            Log::error('report:generate — Python returned empty or invalid path', [
                'stdout' => $pdfPath,
            ]);
            throw new RuntimeException(
                'Report script completed but did not produce a valid PDF path.'
            );
        }

        $sizeKb = round((int) filesize($pdfPath) / 1024, 1);
        $this->info("✅  PDF ready: {$pdfPath} ({$sizeKb} KB)");

        return self::SUCCESS;
    }

    // -----------------------------------------------------------------------
    // Private helpers
    // -----------------------------------------------------------------------

    /**
     * Build the compact, pre-summarised JSON payload sent to the Python script.
     *
     * @return array<string, mixed>
     */
    private function buildPayload(string $from, string $to): array
    {
        $ordersQuery = Order::whereBetween('order_date', [$from, $to]);

        $totalRevenue = (float) (clone $ordersQuery)->sum('total_amount');
        $totalOrders  = (int) (clone $ordersQuery)->count();

        $totalReturns = (int) ReturnRecord::whereBetween('return_date', [$from, $to])->count();

        $returnRatePct = $totalOrders > 0
            ? round(($totalReturns / $totalOrders) * 100, 2)
            : 0.0;

        // Top 5 products by revenue
        $topProducts = Order::whereBetween('order_date', [$from, $to])
            ->join('products', 'orders.product_id', '=', 'products.id')
            ->selectRaw('products.name, SUM(orders.total_amount) as revenue, COUNT(orders.id) as order_count')
            ->groupBy('products.name')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get()
            ->map(fn ($r) => [
                'name'        => $r->name,
                'revenue'     => round((float) $r->revenue, 2),
                'order_count' => (int) $r->order_count,
            ])
            ->toArray();

        // Revenue by category
        $revenueByCategory = Order::whereBetween('order_date', [$from, $to])
            ->join('products', 'orders.product_id', '=', 'products.id')
            ->selectRaw('products.category, SUM(orders.total_amount) as revenue, COUNT(orders.id) as order_count')
            ->groupBy('products.category')
            ->orderByDesc('revenue')
            ->get()
            ->map(fn ($r) => [
                'category'    => $r->category,
                'revenue'     => round((float) $r->revenue, 2),
                'order_count' => (int) $r->order_count,
            ])
            ->toArray();

        return [
            'period'              => ['from' => $from, 'to' => $to],
            'total_revenue'       => round($totalRevenue, 2),
            'total_orders'        => $totalOrders,
            'total_returns'       => $totalReturns,
            'return_rate_pct'     => $returnRatePct,
            'top_products'        => $topProducts,
            'revenue_by_category' => $revenueByCategory,
        ];
    }

    private function isValidDate(string $date): bool
    {
        $d = \DateTimeImmutable::createFromFormat('Y-m-d', $date);
        return $d !== false && $d->format('Y-m-d') === $date;
    }
}
