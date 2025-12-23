<?php

namespace App\Services;

use App\Models\Audit;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ReportService
{
    public function __construct(
        private ScoringService $scoringService
    ) {
    }

    public function generatePdfReport(Audit $audit, bool $saveToStorage = true): ?string
    {
        if (! $audit->isCompleted()) {
            throw new \RuntimeException("Cannot generate report for incomplete audit {$audit->id}");
        }

        try {
            $data = $this->prepareReportData($audit);

            $pdf = Pdf::loadView('reports.audit', $data);

            $pdf->setPaper('a4', 'portrait');

            if ($saveToStorage) {
                $filename = "audit-report-{$audit->id}-" . now()->format('Y-m-d-His') . '.pdf';
                $path = "reports/{$filename}";

                Storage::disk('local')->put($path, $pdf->output());

                Log::info("PDF report saved to storage", [
                    'audit_id' => $audit->id,
                    'path' => $path,
                ]);

                return $path;
            }

            return $pdf->output();

        } catch (\Exception $e) {
            Log::error('Failed to generate PDF report', [
                'audit_id' => $audit->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function downloadPdfReport(Audit $audit, ?string $filename = null): \Illuminate\Http\Response
    {
        $data = $this->prepareReportData($audit);

        $pdf = Pdf::loadView('reports.audit', $data);

        $downloadFilename = $filename ?? "audit-report-{$audit->domain}-" . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($downloadFilename);
    }

    public function streamPdfReport(Audit $audit): \Illuminate\Http\Response
    {
        $data = $this->prepareReportData($audit);

        $pdf = Pdf::loadView('reports.audit', $data);

        return $pdf->stream("audit-report-{$audit->id}.pdf");
    }

    public function prepareReportData(Audit $audit): array
    {
        $categoryScores = $this->scoringService->calculateCategoryScores($audit);

        return [
            'audit' => $audit,
            'overall_score' => $audit->score,
            'grade' => $this->scoringService->getScoreGrade($audit->score),
            'score_label' => $this->scoringService->getScoreLabel($audit->score),
            'category_scores' => $categoryScores,
            'total_pages' => $audit->pages()->count(),
            'total_issues' => $audit->issues()->count(),
            'critical_issues' => $audit->criticalIssues()->get(),
            'high_issues' => $audit->highIssues()->get(),
            'issues_by_category' => $this->getIssuesByCategory($audit),
            'issues_by_severity' => $this->getIssuesBySeverity($audit),
            'broken_links' => $audit->brokenLinks()->get(),
            'total_links' => $audit->links()->count(),
            'performance_metrics' => $this->getPerformanceMetrics($audit),
            'checkout_steps' => $audit->checkoutSteps()->get(),
            'generated_at' => now(),
        ];
    }

    public function generateCsvExport(Audit $audit): string
    {
        $issues = $audit->issues()->get();

        $csv = "Category,Severity,Title,Description,Recommendation,Page URL\n";

        foreach ($issues as $issue) {
            $pageUrl = $issue->page?->url ?? 'N/A';

            $csv .= sprintf(
                "%s,%s,%s,%s,%s,%s\n",
                $this->escapeCsv($issue->category),
                $this->escapeCsv($issue->severity),
                $this->escapeCsv($issue->title),
                $this->escapeCsv($issue->description),
                $this->escapeCsv($issue->recommendation),
                $this->escapeCsv($pageUrl)
            );
        }

        return $csv;
    }

    public function saveCsvExport(Audit $audit): string
    {
        $csv = $this->generateCsvExport($audit);

        $filename = "audit-issues-{$audit->id}-" . now()->format('Y-m-d-His') . '.csv';
        $path = "exports/{$filename}";

        Storage::disk('local')->put($path, $csv);

        Log::info("CSV export saved to storage", [
            'audit_id' => $audit->id,
            'path' => $path,
        ]);

        return $path;
    }

    public function generateJsonExport(Audit $audit): string
    {
        $data = [
            'audit' => [
                'id' => $audit->id,
                'domain' => $audit->domain,
                'url' => $audit->url,
                'score' => $audit->score,
                'status' => $audit->status,
                'pages_crawled' => $audit->pages_crawled,
                'started_at' => $audit->started_at?->toIso8601String(),
                'completed_at' => $audit->completed_at?->toIso8601String(),
            ],
            'category_scores' => $this->scoringService->calculateCategoryScores($audit),
            'issues' => $audit->issues()->get()->map(fn($issue) => [
                'category' => $issue->category,
                'severity' => $issue->severity,
                'title' => $issue->title,
                'description' => $issue->description,
                'recommendation' => $issue->recommendation,
                'page_url' => $issue->page?->url,
                'metadata' => $issue->metadata,
            ]),
            'performance_metrics' => $this->getPerformanceMetrics($audit),
            'broken_links' => $audit->brokenLinks()->get()->map(fn($link) => [
                'url' => $link->url,
                'source_url' => $link->page?->url,
                'status_code' => $link->status_code,
                'error_message' => $link->error_message,
            ]),
        ];

        return json_encode($data, JSON_PRETTY_PRINT);
    }

    protected function getIssuesByCategory(Audit $audit): array
    {
        return $audit->issues()
            ->selectRaw('category, count(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category')
            ->toArray();
    }

    protected function getIssuesBySeverity(Audit $audit): array
    {
        return $audit->issues()
            ->selectRaw('severity, count(*) as count')
            ->groupBy('severity')
            ->pluck('count', 'severity')
            ->toArray();
    }

    protected function getPerformanceMetrics(Audit $audit): array
    {
        $metrics = $audit->pages()
            ->with('performanceMetrics')
            ->get()
            ->flatMap(fn($page) => $page->performanceMetrics);

        if ($metrics->isEmpty()) {
            return [];
        }

        return [
            'mobile' => [
                'lcp' => $metrics->where('device_type', 'mobile')->avg('lcp'),
                'fid' => $metrics->where('device_type', 'mobile')->avg('fid'),
                'cls' => $metrics->where('device_type', 'mobile')->avg('cls'),
                'performance_score' => $metrics->where('device_type', 'mobile')->avg('lighthouse_performance_score'),
            ],
            'desktop' => [
                'lcp' => $metrics->where('device_type', 'desktop')->avg('lcp'),
                'fid' => $metrics->where('device_type', 'desktop')->avg('fid'),
                'cls' => $metrics->where('device_type', 'desktop')->avg('cls'),
                'performance_score' => $metrics->where('device_type', 'desktop')->avg('lighthouse_performance_score'),
            ],
        ];
    }

    protected function escapeCsv(string $value): string
    {
        $value = str_replace('"', '""', $value);

        if (str_contains($value, ',') || str_contains($value, "\n") || str_contains($value, '"')) {
            return "\"{$value}\"";
        }

        return $value;
    }
}
