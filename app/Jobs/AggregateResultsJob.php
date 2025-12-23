<?php

namespace App\Jobs;

use App\Models\Audit;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Aggregates results from all analysis jobs and calculates final audit score.
 *
 * This job:
 * - Updates audit status to 'analyzing'
 * - Calculates overall score based on weighted categories
 * - Updates audit with final score
 * - Sets completed_at timestamp
 * - Sets status to 'completed'
 */
class AggregateResultsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * The maximum number of seconds the job can run.
     *
     * @var int
     */
    public $timeout = 300;

    /**
     * The audit instance.
     *
     * @var Audit
     */
    protected Audit $audit;

    /**
     * Create a new job instance.
     *
     * @param Audit $audit The audit to aggregate results for
     */
    public function __construct(Audit $audit)
    {
        $this->audit = $audit;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        try {
            Log::info("Aggregating results for audit {$this->audit->id}", [
                'audit_id' => $this->audit->id,
            ]);

            // Update status to analyzing
            $this->audit->update(['status' => 'analyzing']);

            // Calculate category scores
            $performanceScore = $this->calculatePerformanceScore();
            $mobileScore = $this->calculateMobileScore();
            $seoScore = $this->calculateSeoScore();
            $checkoutScore = $this->calculateCheckoutScore();
            $linksScore = $this->calculateLinksScore();

            // Calculate overall score with weighted averages
            // Performance: 30%, Mobile: 25%, SEO: 20%, Checkout: 15%, Links: 10%
            $overallScore = (
                ($performanceScore * 0.30) +
                ($mobileScore * 0.25) +
                ($seoScore * 0.20) +
                ($checkoutScore * 0.15) +
                ($linksScore * 0.10)
            );

            // Round to nearest integer
            $overallScore = (int) round($overallScore);

            Log::info("Calculated scores for audit {$this->audit->id}", [
                'performance' => $performanceScore,
                'mobile' => $mobileScore,
                'seo' => $seoScore,
                'checkout' => $checkoutScore,
                'links' => $linksScore,
                'overall' => $overallScore,
            ]);

            // Update audit with final score and completion status
            $this->audit->update([
                'score' => $overallScore,
                'completed_at' => now(),
                'status' => 'completed',
            ]);

            Log::info("Successfully aggregated results for audit {$this->audit->id}", [
                'final_score' => $overallScore,
            ]);

        } catch (Exception $e) {
            Log::error("Failed to aggregate results for audit {$this->audit->id}", [
                'audit_id' => $this->audit->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Mark audit as failed
            $this->audit->update([
                'status' => 'failed',
                'completed_at' => now(),
            ]);

            throw $e;
        }
    }

    /**
     * Calculate performance score based on Lighthouse metrics.
     *
     * @return float
     */
    protected function calculatePerformanceScore(): float
    {
        $performanceMetrics = $this->audit->pages()
            ->with('performanceMetrics')
            ->get()
            ->flatMap(fn($page) => $page->performanceMetrics);

        if ($performanceMetrics->isEmpty()) {
            return 0;
        }

        // Average Lighthouse performance scores across all pages and device types
        $averageScore = $performanceMetrics
            ->avg('lighthouse_performance_score');

        return $averageScore ?? 0;
    }

    /**
     * Calculate mobile score based on mobile performance and responsive issues.
     *
     * @return float
     */
    protected function calculateMobileScore(): float
    {
        // Get mobile-specific performance metrics
        $mobileMetrics = $this->audit->pages()
            ->with(['performanceMetrics' => fn($query) => $query->where('device_type', 'mobile')])
            ->get()
            ->flatMap(fn($page) => $page->performanceMetrics);

        if ($mobileMetrics->isEmpty()) {
            return 0;
        }

        // Average mobile Lighthouse score (60% weight)
        $mobileLighthouseScore = $mobileMetrics->avg('lighthouse_performance_score') ?? 0;

        // Calculate penalty for mobile-specific issues (40% weight)
        $mobileIssues = $this->audit->issues()
            ->where('category', 'mobile')
            ->get();

        $issuesPenalty = $this->calculateIssuesPenalty($mobileIssues);

        // Mobile score = (Lighthouse * 0.60) + ((100 - penalty) * 0.40)
        return ($mobileLighthouseScore * 0.60) + ((100 - $issuesPenalty) * 0.40);
    }

    /**
     * Calculate SEO score based on on-page and technical SEO issues.
     *
     * @return float
     */
    protected function calculateSeoScore(): float
    {
        $seoIssues = $this->audit->issues()
            ->where('category', 'seo')
            ->get();

        $totalPages = $this->audit->pages()->count();

        if ($totalPages === 0) {
            return 0;
        }

        // Start with perfect score
        $score = 100;

        // Apply penalty based on SEO issues
        $penalty = $this->calculateIssuesPenalty($seoIssues);

        return max(0, $score - $penalty);
    }

    /**
     * Calculate checkout score based on checkout flow test results.
     *
     * @return float
     */
    protected function calculateCheckoutScore(): float
    {
        $checkoutIssues = $this->audit->issues()
            ->where('category', 'checkout')
            ->get();

        // Start with perfect score
        $score = 100;

        // Apply penalty based on checkout issues
        $penalty = $this->calculateIssuesPenalty($checkoutIssues);

        return max(0, $score - $penalty);
    }

    /**
     * Calculate links score based on broken link percentage.
     *
     * @return float
     */
    protected function calculateLinksScore(): float
    {
        $totalLinks = $this->audit->links()->count();

        if ($totalLinks === 0) {
            return 100; // No links to check
        }

        $brokenLinks = $this->audit->links()
            ->where('is_broken', true)
            ->count();

        // Calculate percentage of working links
        $workingLinksPercentage = (($totalLinks - $brokenLinks) / $totalLinks) * 100;

        return $workingLinksPercentage;
    }

    /**
     * Calculate penalty points based on issue severity.
     *
     * @param \Illuminate\Database\Eloquent\Collection $issues
     * @return float
     */
    protected function calculateIssuesPenalty($issues): float
    {
        $penalty = 0;

        foreach ($issues as $issue) {
            switch ($issue->severity) {
                case 'critical':
                    $penalty += 20;
                    break;
                case 'high':
                    $penalty += 10;
                    break;
                case 'medium':
                    $penalty += 5;
                    break;
                case 'low':
                    $penalty += 2;
                    break;
                case 'info':
                    $penalty += 0;
                    break;
            }
        }

        return $penalty;
    }

    /**
     * Handle a job failure.
     *
     * @param Exception $exception
     * @return void
     */
    public function failed(Exception $exception): void
    {
        Log::error("AggregateResultsJob permanently failed for audit {$this->audit->id}", [
            'audit_id' => $this->audit->id,
            'error' => $exception->getMessage(),
        ]);

        // Mark audit as failed
        $this->audit->update([
            'status' => 'failed',
            'completed_at' => now(),
        ]);
    }
}
