<?php

use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('reports.index');
});

Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
Route::get('/reports/stats', [ReportController::class, 'getStats'])->name('reports.stats');
Route::post('/reports/generate', [ReportController::class, 'generate'])->name('reports.generate');

// Preview route for PDF design
Route::get('/report/preview', [ReportController::class, 'preview'])->name('reports.preview');
