<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Services\ScoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private ScoringService $scoringService
    ) {
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        $recentAudits = Audit::where('created_by', $user->id)
            ->with('createdBy')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $stats = $this->getStats($user->id);

        $scoreHistory = $this->getScoreHistory($user->id);

        $issuesByCategory = $this->getIssuesByCategory($user->id);

        $topIssues = $this->getTopIssues($user->id);

        return view('dashboard', [
            'recentAudits' => $recentAudits,
            'stats' => $stats,
            'scoreHistory' => $scoreHistory,
            'issuesByCategory' => $issuesByCategory,
            'topIssues' => $topIssues,
        ]);
    }

    protected function getStats(int $userId): array
    {
        $totalAudits = Audit::where('created_by', $userId)->count();

        $completedAudits = Audit::where('created_by', $userId)
            ->where('status', 'completed')
            ->count();

        $processingAudits = Audit::where('created_by', $userId)
            ->whereIn('status', ['pending', 'crawling', 'analyzing'])
            ->count();

        $averageScore = Audit::where('created_by', $userId)
            ->where('status', 'completed')
            ->whereNotNull('score')
            ->avg('score');

        $totalIssues = DB::table('issues')
            ->join('audits', 'issues.audit_id', '=', 'audits.id')
            ->where('audits.created_by', $userId)
            ->count();

        $criticalIssues = DB::table('issues')
            ->join('audits', 'issues.audit_id', '=', 'audits.id')
            ->where('audits.created_by', $userId)
            ->where('issues.severity', 'critical')
            ->count();

        return [
            'total_audits' => $totalAudits,
            'completed_audits' => $completedAudits,
            'processing_audits' => $processingAudits,
            'average_score' => $averageScore ? round($averageScore, 1) : null,
            'total_issues' => $totalIssues,
            'critical_issues' => $criticalIssues,
        ];
    }

    protected function getScoreHistory(int $userId): array
    {
        $audits = Audit::where('created_by', $userId)
            ->where('status', 'completed')
            ->whereNotNull('score')
            ->orderBy('completed_at')
            ->limit(10)
            ->get();

        return $audits->map(function ($audit) {
            return [
                'date' => $audit->completed_at?->format('M d'),
                'score' => $audit->score,
                'domain' => $audit->domain,
                'grade' => $this->scoringService->getScoreGrade($audit->score),
            ];
        })->toArray();
    }

    protected function getIssuesByCategory(int $userId): array
    {
        $issues = DB::table('issues')
            ->join('audits', 'issues.audit_id', '=', 'audits.id')
            ->where('audits.created_by', $userId)
            ->where('audits.status', 'completed')
            ->select('issues.category', DB::raw('count(*) as count'))
            ->groupBy('issues.category')
            ->pluck('count', 'category')
            ->toArray();

        return $issues;
    }

    protected function getTopIssues(int $userId, int $limit = 10): array
    {
        $issues = DB::table('issues')
            ->join('audits', 'issues.audit_id', '=', 'audits.id')
            ->where('audits.created_by', $userId)
            ->where('audits.status', 'completed')
            ->select('issues.title', 'issues.category', 'issues.severity', DB::raw('count(*) as count'))
            ->groupBy('issues.title', 'issues.category', 'issues.severity')
            ->orderBy('count', 'desc')
            ->orderByRaw("CASE severity WHEN 'critical' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 WHEN 'low' THEN 4 WHEN 'info' THEN 5 END")
            ->limit($limit)
            ->get()
            ->toArray();

        return $issues;
    }
}
