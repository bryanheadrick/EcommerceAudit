<?php

use App\Http\Controllers\AuditController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ResultsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('audits', AuditController::class);

    Route::post('/audits/{audit}/restart', [AuditController::class, 'restart'])->name('audits.restart');
    Route::post('/audits/{audit}/cancel', [AuditController::class, 'cancel'])->name('audits.cancel');

    Route::get('/audits/{audit}/results/issues', [ResultsController::class, 'issues'])->name('audits.results.issues');
    Route::get('/audits/{audit}/results/performance', [ResultsController::class, 'performance'])->name('audits.results.performance');
    Route::get('/audits/{audit}/results/links', [ResultsController::class, 'links'])->name('audits.results.links');
    Route::get('/audits/{audit}/results/checkout', [ResultsController::class, 'checkout'])->name('audits.results.checkout');
    Route::get('/audits/{currentAudit}/compare/{previousAudit}', [ResultsController::class, 'comparison'])->name('audits.results.comparison');

    Route::get('/audits/{audit}/report/pdf', [ReportController::class, 'downloadPdf'])->name('audits.report.pdf');
    Route::get('/audits/{audit}/report/pdf/stream', [ReportController::class, 'streamPdf'])->name('audits.report.pdf.stream');
    Route::get('/audits/{audit}/report/csv', [ReportController::class, 'downloadCsv'])->name('audits.report.csv');
    Route::get('/audits/{audit}/report/json', [ReportController::class, 'downloadJson'])->name('audits.report.json');
    Route::post('/audits/{audit}/report/pdf/save', [ReportController::class, 'savePdf'])->name('audits.report.pdf.save');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
