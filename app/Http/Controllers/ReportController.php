<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ReturnRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use App\Services\ReportService;

class ReportController extends Controller
{
    /**
     * Show the report dashboard with date picker and quick stats.
     */
    public function index()
    {
        $service = new ReportService();
        $from = now()->subDays(30)->format('Y-m-d');
        $to = now()->format('Y-m-d');
        $stats = $service->getStats($from, $to);
        return view('reports', compact('stats'));
    }

    /**
     * Get statistics for a specific date range.
     */
    public function getStats(Request $request)
    {
        $validated = $request->validate([
            'from_date' => 'required|date|date_format:Y-m-d',
            'to_date' => 'required|date|date_format:Y-m-d|after_or_equal:from_date',
        ]);

        $service = new ReportService();
        $stats = $service->getStats($validated['from_date'], $validated['to_date']);
        return \api_response($stats);
    }

    /**
     * Generate the report and stream the PDF download.
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'from_date' => 'required|date|date_format:Y-m-d',
            'to_date' => 'required|date|date_format:Y-m-d|after_or_equal:from_date',
        ], [
            'from_date.required' => 'Please select a start date.',
            'to_date.required' => 'Please select an end date.',
            'to_date.after_or_equal' => 'The end date must be on or after the start date.',
        ]);

        $fromDate = $validated['from_date'];
        $toDate = $validated['to_date'];

        $daysDiff = (new \DateTime($fromDate))->diff(new \DateTime($toDate))->days;
        if ($daysDiff > 365) {
            return back()
                ->withInput()
                ->withErrors(['to_date' => 'Date range cannot exceed 1 year.']);
        }

        $service = new ReportService();
        $outputPath = $service->generate($fromDate, $toDate);
        if (!$outputPath) {
            return back()
                ->withInput()
                ->with('error', 'Failed to generate the report. Please check the logs or try again.');
        }

        $filename = 'Business_Report_' . $fromDate . '_to_' . $toDate . '.pdf';
        return response()->download($outputPath, $filename, ['Content-Type' => 'application/pdf'])->deleteFileAfterSend(true);
    }
    /**
    * Preview the generated PDF in the browser.
    */
    public function preview(Request $request)
    {
        $validated = $request->validate([
            'from_date' => 'required|date|date_format:Y-m-d',
            'to_date' => 'required|date|date_format:Y-m-d|after_or_equal:from_date',
        ], [
            'from_date.required' => 'Please select a start date.',
            'to_date.required' => 'Please select an end date.',
            'to_date.after_or_equal' => 'The end date must be on or after the start date.',
        ]);

        $darkMode = $request->query('dark', false);
        $service = new ReportService();
        $outputPath = $service->preview($validated['from_date'], $validated['to_date'], $darkMode);
        if (!$outputPath) {
            return back()->with('error', 'Failed to generate preview PDF.');
        }
        return response()->file($outputPath);
    }
}
