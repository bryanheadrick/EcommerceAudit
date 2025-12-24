<?php

namespace App\Jobs;

use App\Models\Issue;
use App\Models\Page;
use App\Models\PerformanceMetric;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Runs performance analysis on a page using Lighthouse CLI.
 *
 * This job:
 * - Runs Lighthouse CLI audit for specified device type (mobile/desktop)
 * - Parses JSON results
 * - Creates PerformanceMetric record with Core Web Vitals and Lighthouse scores
 * - Creates Issue records for failed metrics
 */
class PerformanceAnalysisJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     *
     * @var int
     */
    public $timeout = 300;

    /**
     * The page to analyze.
     *
     * @var Page
     */
    protected Page $page;

    /**
     * The device type for analysis.
     *
     * @var string
     */
    protected string $deviceType;

    /**
     * Create a new job instance.
     *
     * @param Page $page The page to analyze
     * @param string $deviceType The device type (mobile or desktop)
     */
    public function __construct(Page $page, string $deviceType = 'mobile')
    {
        $this->page = $page;
        $this->deviceType = $deviceType;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        try {
            Log::info("Running performance analysis for page {$this->page->id}", [
                'page_id' => $this->page->id,
                'url' => $this->page->url,
                'device_type' => $this->deviceType,
            ]);

            // TODO: Run Lighthouse CLI audit
            // - Execute lighthouse command with appropriate flags
            // - Set device type (--preset=mobile or --preset=desktop)
            // - Set output format to JSON
            // - Configure throttling based on device type
            // - Parse JSON results
            $lighthouseResults = $this->runLighthouseAudit();

            // Extract metrics from Lighthouse results
            $metrics = $this->extractMetrics($lighthouseResults);

            // Create PerformanceMetric record
            $performanceMetric = PerformanceMetric::create([
                'page_id' => $this->page->id,
                'device_type' => $this->deviceType,
                'lcp' => $metrics['lcp'],
                'fid' => $metrics['fid'],
                'cls' => $metrics['cls'],
                'fcp' => $metrics['fcp'],
                'ttfb' => $metrics['ttfb'],
                'speed_index' => $metrics['speed_index'],
                'total_blocking_time' => $metrics['total_blocking_time'],
                'lighthouse_performance_score' => $metrics['performance_score'],
                'lighthouse_accessibility_score' => $metrics['accessibility_score'],
                'lighthouse_seo_score' => $metrics['seo_score'],
                'lighthouse_best_practices_score' => $metrics['best_practices_score'],
                'lighthouse_json' => $lighthouseResults,
            ]);

            // Check metrics and create issues
            $this->checkPerformanceMetrics($metrics);

            Log::info("Successfully completed performance analysis for page {$this->page->id}", [
                'device_type' => $this->deviceType,
                'performance_score' => $metrics['performance_score'],
            ]);

        } catch (Exception $e) {
            Log::error("Failed to run performance analysis for page {$this->page->id}", [
                'page_id' => $this->page->id,
                'url' => $this->page->url,
                'device_type' => $this->deviceType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Run Lighthouse CLI audit on the page.
     *
     * TODO: Replace with actual Lighthouse CLI execution
     *
     * @return array
     */
    protected function runLighthouseAudit(): array
    {
        // TODO: Implement Lighthouse CLI execution
        // - Build command: lighthouse {url} --output=json --preset={device_type}
        // - Set appropriate timeout (120 seconds)
        // - Execute command and capture JSON output
        // - Parse JSON response
        // - Handle errors and timeouts

        // Example command:
        // lighthouse https://example.com --output=json --preset=mobile --chrome-flags="--headless"

        // Placeholder return
        return [
            'lighthouseVersion' => '10.0.0',
            'requestedUrl' => $this->page->url,
            'finalUrl' => $this->page->url,
            'categories' => [
                'performance' => ['score' => 0.85],
                'accessibility' => ['score' => 0.90],
                'seo' => ['score' => 0.95],
                'best-practices' => ['score' => 0.88],
            ],
            'audits' => [
                'largest-contentful-paint' => ['numericValue' => 2500],
                'max-potential-fid' => ['numericValue' => 100],
                'cumulative-layout-shift' => ['numericValue' => 0.1],
                'first-contentful-paint' => ['numericValue' => 1800],
                'server-response-time' => ['numericValue' => 600],
                'speed-index' => ['numericValue' => 3000],
                'total-blocking-time' => ['numericValue' => 200],
            ],
        ];
    }

    /**
     * Extract metrics from Lighthouse results.
     *
     * @param array $results
     * @return array
     */
    protected function extractMetrics(array $results): array
    {
        $audits = $results['audits'] ?? [];
        $categories = $results['categories'] ?? [];

        return [
            'lcp' => isset($audits['largest-contentful-paint']['numericValue'])
                ? $audits['largest-contentful-paint']['numericValue'] / 1000 // Convert to seconds
                : null,
            'fid' => isset($audits['max-potential-fid']['numericValue'])
                ? $audits['max-potential-fid']['numericValue']
                : null,
            'cls' => $audits['cumulative-layout-shift']['numericValue'] ?? null,
            'fcp' => isset($audits['first-contentful-paint']['numericValue'])
                ? $audits['first-contentful-paint']['numericValue'] / 1000
                : null,
            'ttfb' => $audits['server-response-time']['numericValue'] ?? null,
            'speed_index' => $audits['speed-index']['numericValue'] ?? null,
            'total_blocking_time' => $audits['total-blocking-time']['numericValue'] ?? null,
            'performance_score' => isset($categories['performance']['score'])
                ? (int) ($categories['performance']['score'] * 100)
                : null,
            'accessibility_score' => isset($categories['accessibility']['score'])
                ? (int) ($categories['accessibility']['score'] * 100)
                : null,
            'seo_score' => isset($categories['seo']['score'])
                ? (int) ($categories['seo']['score'] * 100)
                : null,
            'best_practices_score' => isset($categories['best-practices']['score'])
                ? (int) ($categories['best-practices']['score'] * 100)
                : null,
        ];
    }

    /**
     * Check performance metrics and create issues for poor scores.
     *
     * @param array $metrics
     * @return void
     */
    protected function checkPerformanceMetrics(array $metrics): void
    {
        $deviceLabel = ucfirst($this->deviceType);

        // Check LCP (Largest Contentful Paint)
        // Good: < 2.5s, Needs Improvement: 2.5s - 4s, Poor: > 4s
        if ($metrics['lcp'] !== null && $metrics['lcp'] > 4.0) {
            Issue::create([
                'audit_id' => $this->page->audit_id,
                'page_id' => $this->page->id,
                'category' => 'performance',
                'severity' => 'critical',
                'title' => "Poor LCP Score ({$deviceLabel})",
                'description' => "Largest Contentful Paint is {$metrics['lcp']}s, which is considered poor (should be < 2.5s).",
                'recommendation' => 'Optimize images, reduce server response times, eliminate render-blocking resources, and use a CDN.',
                'metadata' => ['metric' => 'lcp', 'value' => $metrics['lcp'], 'device' => $this->deviceType],
            ]);
        } elseif ($metrics['lcp'] !== null && $metrics['lcp'] > 2.5) {
            Issue::create([
                'audit_id' => $this->page->audit_id,
                'page_id' => $this->page->id,
                'category' => 'performance',
                'severity' => 'high',
                'title' => "LCP Needs Improvement ({$deviceLabel})",
                'description' => "Largest Contentful Paint is {$metrics['lcp']}s, which needs improvement (should be < 2.5s).",
                'recommendation' => 'Optimize images and reduce server response times.',
                'metadata' => ['metric' => 'lcp', 'value' => $metrics['lcp'], 'device' => $this->deviceType],
            ]);
        }

        // Check CLS (Cumulative Layout Shift)
        // Good: < 0.1, Needs Improvement: 0.1 - 0.25, Poor: > 0.25
        if ($metrics['cls'] !== null && $metrics['cls'] > 0.25) {
            Issue::create([
                'audit_id' => $this->page->audit_id,
                'page_id' => $this->page->id,
                'category' => 'performance',
                'severity' => 'high',
                'title' => "Poor CLS Score ({$deviceLabel})",
                'description' => "Cumulative Layout Shift is {$metrics['cls']}, which is considered poor (should be < 0.1).",
                'recommendation' => 'Include size attributes on images and video elements, avoid inserting content above existing content, and use CSS transforms.',
                'metadata' => ['metric' => 'cls', 'value' => $metrics['cls'], 'device' => $this->deviceType],
            ]);
        } elseif ($metrics['cls'] !== null && $metrics['cls'] > 0.1) {
            Issue::create([
                'audit_id' => $this->page->audit_id,
                'page_id' => $this->page->id,
                'category' => 'performance',
                'severity' => 'medium',
                'title' => "CLS Needs Improvement ({$deviceLabel})",
                'description' => "Cumulative Layout Shift is {$metrics['cls']}, which needs improvement (should be < 0.1).",
                'recommendation' => 'Add size attributes to images and avoid dynamic content insertion.',
                'metadata' => ['metric' => 'cls', 'value' => $metrics['cls'], 'device' => $this->deviceType],
            ]);
        }

        // Check overall performance score
        if ($metrics['performance_score'] !== null && $metrics['performance_score'] < 50) {
            Issue::create([
                'audit_id' => $this->page->audit_id,
                'page_id' => $this->page->id,
                'category' => 'performance',
                'severity' => 'critical',
                'title' => "Poor Performance Score ({$deviceLabel})",
                'description' => "Lighthouse performance score is {$metrics['performance_score']}/100, which is poor.",
                'recommendation' => 'Review Lighthouse report for specific recommendations. Focus on optimizing images, reducing JavaScript, and improving server response times.',
                'metadata' => ['score' => $metrics['performance_score'], 'device' => $this->deviceType],
            ]);
        } elseif ($metrics['performance_score'] !== null && $metrics['performance_score'] < 75) {
            Issue::create([
                'audit_id' => $this->page->audit_id,
                'page_id' => $this->page->id,
                'category' => 'performance',
                'severity' => 'medium',
                'title' => "Performance Score Needs Improvement ({$deviceLabel})",
                'description' => "Lighthouse performance score is {$metrics['performance_score']}/100.",
                'recommendation' => 'Review Lighthouse report for optimization opportunities.',
                'metadata' => ['score' => $metrics['performance_score'], 'device' => $this->deviceType],
            ]);
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("PerformanceAnalysisJob permanently failed for page {$this->page->id}", [
            'page_id' => $this->page->id,
            'url' => $this->page->url,
            'device_type' => $this->deviceType,
            'error' => $exception->getMessage(),
        ]);

        Issue::create([
            'audit_id' => $this->page->audit_id,
            'page_id' => $this->page->id,
            'category' => 'performance',
            'severity' => 'high',
            'title' => 'Performance Analysis Failed',
            'description' => "Failed to run performance analysis: {$exception->getMessage()}",
            'recommendation' => 'Check if Lighthouse CLI is properly installed and the page is accessible.',
        ]);
    }
}
