<?php

namespace App\Services;

use App\Jobs\CrawlSiteJob;
use App\Models\Audit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuditService
{
    public function __construct(
        private CrawlerService $crawlerService,
        private ScoringService $scoringService
    ) {
    }

    public function createAudit(string $url, User $user, ?int $maxPages = null, ?array $config = []): Audit
    {
        $domain = $this->extractDomain($url);

        if (! $domain) {
            throw new \InvalidArgumentException('Invalid URL provided');
        }

        $audit = Audit::create([
            'domain' => $domain,
            'url' => $url,
            'status' => 'pending',
            'max_pages' => $maxPages ?? config('audit.default_max_pages', 50),
            'config' => $config,
            'created_by' => $user->id,
        ]);

        Log::channel('audit')->info("========================================");
        Log::channel('audit')->info("NEW AUDIT CREATED", [
            'audit_id' => $audit->id,
            'url' => $url,
            'domain' => $domain,
            'max_pages' => $audit->max_pages,
            'created_by' => $user->id,
            'created_by_email' => $user->email,
        ]);

        return $audit;
    }

    public function startAudit(Audit $audit): void
    {
        if (in_array($audit->status, ['crawling', 'analyzing'])) {
            throw new \RuntimeException("Audit {$audit->id} is already processing");
        }

        if ($audit->isCompleted()) {
            throw new \RuntimeException("Audit {$audit->id} has already been completed");
        }

        Log::channel('audit')->info("STARTING AUDIT", [
            'audit_id' => $audit->id,
            'url' => $audit->url,
            'status' => $audit->status,
        ]);

        CrawlSiteJob::dispatch($audit)->onQueue('default');

        Log::channel('audit')->info("Dispatched CrawlSiteJob to queue");
    }

    public function cancelAudit(Audit $audit): void
    {
        if (! $audit->isProcessing()) {
            throw new \RuntimeException("Audit {$audit->id} is not currently processing");
        }

        $audit->update([
            'status' => 'failed',
            'completed_at' => now(),
        ]);

        Log::info("Cancelled audit {$audit->id}");
    }

    public function deleteAudit(Audit $audit): void
    {
        DB::transaction(function () use ($audit) {
            $audit->issues()->delete();
            $audit->links()->delete();
            $audit->checkoutSteps()->delete();

            foreach ($audit->pages as $page) {
                $page->performanceMetrics()->delete();
                $page->delete();
            }

            $audit->delete();
        });

        Log::info("Deleted audit {$audit->id}");
    }

    public function getAuditSummary(Audit $audit): array
    {
        return [
            'audit' => $audit,
            'total_pages' => $audit->pages()->count(),
            'total_issues' => $audit->issues()->count(),
            'critical_issues' => $audit->criticalIssues()->count(),
            'high_issues' => $audit->highIssues()->count(),
            'broken_links' => $audit->brokenLinks()->count(),
            'total_links' => $audit->links()->count(),
            'score' => $audit->score,
            'status' => $audit->status,
            'issues_by_category' => $this->getIssuesByCategory($audit),
            'issues_by_severity' => $this->getIssuesBySeverity($audit),
        ];
    }

    public function getIssuesByCategory(Audit $audit): array
    {
        return $audit->issues()
            ->select('category', DB::raw('count(*) as count'))
            ->groupBy('category')
            ->pluck('count', 'category')
            ->toArray();
    }

    public function getIssuesBySeverity(Audit $audit): array
    {
        return $audit->issues()
            ->select('severity', DB::raw('count(*) as count'))
            ->groupBy('severity')
            ->pluck('count', 'severity')
            ->toArray();
    }

    public function compareAudits(Audit $currentAudit, Audit $previousAudit): array
    {
        return [
            'score_change' => $currentAudit->score - $previousAudit->score,
            'issues_change' => $currentAudit->issues()->count() - $previousAudit->issues()->count(),
            'critical_issues_change' => $currentAudit->criticalIssues()->count() - $previousAudit->criticalIssues()->count(),
            'broken_links_change' => $currentAudit->brokenLinks()->count() - $previousAudit->brokenLinks()->count(),
            'performance_change' => $this->calculatePerformanceChange($currentAudit, $previousAudit),
        ];
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

    protected function extractDomain(string $url): ?string
    {
        $parsedUrl = parse_url($url);

        if (! isset($parsedUrl['host'])) {
            return null;
        }

        return $parsedUrl['host'];
    }
}
