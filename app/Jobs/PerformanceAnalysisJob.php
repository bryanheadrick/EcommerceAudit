<?php

namespace App\Jobs;

use App\Models\Issue;
use App\Models\Page;
use App\Models\PerformanceMetric;
use App\Services\LighthouseService;
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
     * Run Lighthouse CLI audit on the page using LighthouseService.
     *
     * @return array
     */
    protected function runLighthouseAudit(): array
    {
        $lighthouseService = app(LighthouseService::class);

        $results = $lighthouseService->runAudit($this->page->url, $this->deviceType);

        if (! $results) {
            throw new \Exception('Lighthouse audit failed to return results');
        }

        return $results;
    }

    /**
     * Extract metrics from Lighthouse results using LighthouseService.
     *
     * @param array $results
     * @return array
     */
    protected function extractMetrics(array $results): array
    {
        $lighthouseService = app(LighthouseService::class);

        return $lighthouseService->extractMetrics($results);
    }

    /**
     * Check performance metrics and create issues for poor scores using LighthouseService.
     *
     * @param array $metrics
     * @return void
     */
    protected function checkPerformanceMetrics(array $metrics): void
    {
        $lighthouseService = app(LighthouseService::class);

        $issues = $lighthouseService->checkMetricThresholds($metrics, $this->deviceType);

        foreach ($issues as $issueData) {
            Issue::create([
                'audit_id' => $this->page->audit_id,
                'page_id' => $this->page->id,
                'category' => $issueData['category'],
                'severity' => $issueData['severity'],
                'title' => $issueData['title'],
                'description' => $issueData['description'],
                'recommendation' => $issueData['recommendation'],
                'metadata' => $issueData['metadata'],
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
