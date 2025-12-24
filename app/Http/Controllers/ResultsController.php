<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResultsController extends Controller
{
    public function issues(Audit $audit, Request $request): View
    {
        $this->authorize('view', $audit);

        $issues = $audit->issues()
            ->with('page')
            ->when($request->input('category'), function ($query, $category) {
                return $query->where('category', $category);
            })
            ->when($request->input('severity'), function ($query, $severity) {
                return $query->where('severity', $severity);
            })
            ->when($request->input('search'), function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderByRaw("CASE severity WHEN 'critical' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 WHEN 'low' THEN 4 WHEN 'info' THEN 5 END")
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        $categories = $audit->issues()
            ->distinct()
            ->pluck('category')
            ->toArray();

        return view('results.issues', [
            'audit' => $audit,
            'issues' => $issues,
            'categories' => $categories,
        ]);
    }

    public function performance(Audit $audit, Request $request): View
    {
        $this->authorize('view', $audit);

        $deviceType = $request->input('device', 'mobile');

        $pages = $audit->pages()
            ->with(['performanceMetrics' => function ($query) use ($deviceType) {
                $query->where('device_type', $deviceType);
            }])
            ->get();

        $averageMetrics = $pages
            ->flatMap(fn($page) => $page->performanceMetrics)
            ->pipe(function ($metrics) {
                if ($metrics->isEmpty()) {
                    return null;
                }

                return [
                    'lcp' => $metrics->avg('lcp'),
                    'fid' => $metrics->avg('fid'),
                    'cls' => $metrics->avg('cls'),
                    'fcp' => $metrics->avg('fcp'),
                    'ttfb' => $metrics->avg('ttfb'),
                    'performance_score' => $metrics->avg('lighthouse_performance_score'),
                    'accessibility_score' => $metrics->avg('lighthouse_accessibility_score'),
                    'seo_score' => $metrics->avg('lighthouse_seo_score'),
                    'best_practices_score' => $metrics->avg('lighthouse_best_practices_score'),
                ];
            });

        return view('results.performance', [
            'audit' => $audit,
            'pages' => $pages,
            'averageMetrics' => $averageMetrics,
            'deviceType' => $deviceType,
        ]);
    }

    public function links(Audit $audit, Request $request): View
    {
        $this->authorize('view', $audit);

        $links = $audit->links()
            ->with('page')
            ->when($request->input('broken_only'), function ($query) {
                return $query->where('is_broken', true);
            })
            ->when($request->input('search'), function ($query, $search) {
                return $query->where('url', 'like', "%{$search}%");
            })
            ->orderBy('is_broken', 'desc')
            ->orderBy('status_code')
            ->paginate(100);

        $brokenCount = $audit->brokenLinks()->count();
        $totalCount = $audit->links()->count();

        return view('results.links', [
            'audit' => $audit,
            'links' => $links,
            'brokenCount' => $brokenCount,
            'totalCount' => $totalCount,
        ]);
    }

    public function checkout(Audit $audit): View
    {
        $this->authorize('view', $audit);

        $checkoutSteps = $audit->checkoutSteps()
            ->orderBy('step_number')
            ->get();

        $checkoutIssues = $audit->issues()
            ->where('category', 'checkout')
            ->orderByRaw("CASE severity WHEN 'critical' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 WHEN 'low' THEN 4 WHEN 'info' THEN 5 END")
            ->get();

        return view('results.checkout', [
            'audit' => $audit,
            'checkoutSteps' => $checkoutSteps,
            'checkoutIssues' => $checkoutIssues,
        ]);
    }

    public function comparison(Audit $currentAudit, Audit $previousAudit): View
    {
        $this->authorize('view', $currentAudit);
        $this->authorize('view', $previousAudit);

        if ($currentAudit->domain !== $previousAudit->domain) {
            abort(400, 'Cannot compare audits from different domains.');
        }

        $comparison = [
            'score_change' => $currentAudit->score - $previousAudit->score,
            'issues_change' => $currentAudit->issues()->count() - $previousAudit->issues()->count(),
            'critical_issues_change' => $currentAudit->criticalIssues()->count() - $previousAudit->criticalIssues()->count(),
            'broken_links_change' => $currentAudit->brokenLinks()->count() - $previousAudit->brokenLinks()->count(),
            'performance_change' => $this->calculatePerformanceChange($currentAudit, $previousAudit),
            'new_issues' => $this->getNewIssues($currentAudit, $previousAudit),
            'resolved_issues' => $this->getResolvedIssues($currentAudit, $previousAudit),
        ];

        return view('results.comparison', [
            'currentAudit' => $currentAudit,
            'previousAudit' => $previousAudit,
            'comparison' => $comparison,
        ]);
    }

    protected function calculatePerformanceChange(Audit $currentAudit, Audit $previousAudit): array
    {
        $currentPerformance = $currentAudit->pages()
            ->with('performanceMetrics')
            ->get()
            ->flatMap(fn($page) => $page->performanceMetrics)
            ->avg('lighthouse_performance_score');

        $previousPerformance = $previousAudit->pages()
            ->with('performanceMetrics')
            ->get()
            ->flatMap(fn($page) => $page->performanceMetrics)
            ->avg('lighthouse_performance_score');

        return [
            'current' => $currentPerformance,
            'previous' => $previousPerformance,
            'change' => $currentPerformance - $previousPerformance,
        ];
    }

    protected function getNewIssues(Audit $currentAudit, Audit $previousAudit): int
    {
        $currentIssueSignatures = $currentAudit->issues
            ->map(fn($issue) => $issue->title . '|' . $issue->category)
            ->toArray();

        $previousIssueSignatures = $previousAudit->issues
            ->map(fn($issue) => $issue->title . '|' . $issue->category)
            ->toArray();

        return count(array_diff($currentIssueSignatures, $previousIssueSignatures));
    }

    protected function getResolvedIssues(Audit $currentAudit, Audit $previousAudit): int
    {
        $currentIssueSignatures = $currentAudit->issues
            ->map(fn($issue) => $issue->title . '|' . $issue->category)
            ->toArray();

        $previousIssueSignatures = $previousAudit->issues
            ->map(fn($issue) => $issue->title . '|' . $issue->category)
            ->toArray();

        return count(array_diff($previousIssueSignatures, $currentIssueSignatures));
    }
}
