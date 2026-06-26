<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ReturnRecord;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Http\Response;

class ReportService
{
    /**
     * Get statistics for a given date range.
     */
    public function getStats(string $from, string $to): array
    {
        $ordersQuery = Order::whereBetween('order_date', [$from, $to]);

        return [
            'total_orders'   => $ordersQuery->count(),
            'total_revenue'  => $ordersQuery->sum('total_amount'),
            'total_returns'  => ReturnRecord::whereBetween('return_date', [$from, $to])->count(),
            'products_count' => Order::whereBetween('order_date', [$from, $to])->distinct()->count('product_id'),
        ];
    }

    /**
     * Generate the PDF report and return the output path.
     */
    public function generate(string $fromDate, string $toDate): ?string
    {
        $outputPath = storage_path(
            'app/private/reports/report_' . now()->format('Ymd_His') . '_' . uniqid() . '.pdf'
        );

        $exitCode = Artisan::call('report:generate', [
            '--from' => $fromDate,
            '--to'   => $toDate,
            '--output'=> $outputPath,
        ]);

        if ($exitCode !== 0) {
            Log::error('Report generation failed via service', [
                'exit_code' => $exitCode,
                'output'    => Artisan::output(),
                'from'      => $fromDate,
                'to'        => $toDate,
            ]);
            return null;
        }

        if (!file_exists($outputPath)) {
            Log::error('Report PDF not found after generation (service)', ['path' => $outputPath]);
            return null;
        }

        return $outputPath;
    }

    /**
     * Generate a preview PDF and return its path.
     */
    public function preview(string $fromDate, string $toDate, bool $darkMode = false): ?string
    {
        $outputPath = storage_path('app/private/reports/report_' . now()->format('Ymd_His') . '_' . uniqid() . '.pdf');
        $env = [];
        if ($darkMode) {
            $env['DARK_MODE'] = '1';
        }
        $process = Process::timeout(120)
            ->env($env)
            ->run('php artisan report:generate --from=' . $fromDate . ' --to=' . $toDate . ' --output=' . $outputPath);

        if ($process->failed()) {
            Log::error('Report preview generation failed (service)', [
                'stderr' => $process->errorOutput(),
                'from'   => $fromDate,
                'to'     => $toDate,
            ]);
            return null;
        }

        if (!file_exists($outputPath)) {
            Log::error('Preview PDF not found after generation (service)', ['path' => $outputPath]);
            return null;
        }

        return $outputPath;
    }
}
?>
