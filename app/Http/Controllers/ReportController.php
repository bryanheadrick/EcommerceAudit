<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Services\ReportService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    public function __construct(
        private ReportService $reportService
    ) {
    }

    public function downloadPdf(Audit $audit): Response
    {
        $this->authorize('view', $audit);

        if (! $audit->isCompleted()) {
            abort(400, 'Cannot generate report for incomplete audit.');
        }

        return $this->reportService->downloadPdfReport($audit);
    }

    public function streamPdf(Audit $audit): Response
    {
        $this->authorize('view', $audit);

        if (! $audit->isCompleted()) {
            abort(400, 'Cannot generate report for incomplete audit.');
        }

        return $this->reportService->streamPdfReport($audit);
    }

    public function downloadCsv(Audit $audit): Response
    {
        $this->authorize('view', $audit);

        $csv = $this->reportService->generateCsvExport($audit);

        $filename = "audit-issues-{$audit->domain}-" . now()->format('Y-m-d') . '.csv';

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    public function downloadJson(Audit $audit): Response
    {
        $this->authorize('view', $audit);

        $json = $this->reportService->generateJsonExport($audit);

        $filename = "audit-data-{$audit->domain}-" . now()->format('Y-m-d') . '.json';

        return response($json)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    public function savePdf(Audit $audit): Response
    {
        $this->authorize('view', $audit);

        if (! $audit->isCompleted()) {
            abort(400, 'Cannot generate report for incomplete audit.');
        }

        $path = $this->reportService->generatePdfReport($audit, saveToStorage: true);

        if (! $path) {
            abort(500, 'Failed to generate PDF report.');
        }

        return response()->json([
            'success' => true,
            'path' => $path,
            'url' => Storage::url($path),
        ]);
    }
}
