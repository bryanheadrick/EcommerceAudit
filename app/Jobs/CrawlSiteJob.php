<?php

namespace App\Jobs;

use App\Models\Audit;
use App\Models\Page;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Main orchestrator job for crawling a website and dispatching analysis jobs.
 *
 * This job:
 * - Updates audit status to 'crawling'
 * - Discovers pages using Spatie Crawler (up to max_pages)
 * - Creates Page records for discovered pages
 * - Dispatches child jobs for analysis (AnalyzePageJob, PerformanceAnalysisJob, ValidateLinksJob)
 * - Dispatches TestCheckoutFlowJob once
 * - Dispatches AggregateResultsJob when complete
 * - Handles errors by setting audit status to 'failed'
 */
class CrawlSiteJob implements ShouldQueue
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
     * The audit instance.
     *
     * @var Audit
     */
    protected Audit $audit;

    /**
     * Create a new job instance.
     *
     * @param Audit $audit The audit to crawl
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
            Log::info("Starting crawl for audit {$this->audit->id}", [
                'audit_id' => $this->audit->id,
                'url' => $this->audit->url,
                'max_pages' => $this->audit->max_pages,
            ]);

            // Update audit status to crawling
            $this->audit->update([
                'status' => 'crawling',
                'started_at' => now(),
            ]);

            // TODO: Implement Spatie Crawler logic
            // - Configure crawler with max pages from $this->audit->max_pages
            // - Set appropriate timeout and user agent
            // - Respect robots.txt if configured
            // - Discover pages starting from $this->audit->url

            // Placeholder: Simulate discovered pages
            $discoveredPages = $this->discoverPages();

            Log::info("Discovered {$discoveredPages->count()} pages for audit {$this->audit->id}");

            // Update pages crawled count
            $this->audit->update([
                'pages_crawled' => $discoveredPages->count(),
            ]);

            // Dispatch analysis jobs for each discovered page
            foreach ($discoveredPages as $page) {
                // Dispatch page analysis job
                AnalyzePageJob::dispatch($page)->onQueue('default');

                // Dispatch performance analysis for both mobile and desktop
                PerformanceAnalysisJob::dispatch($page, 'mobile')->onQueue('default');
                PerformanceAnalysisJob::dispatch($page, 'desktop')->onQueue('default');

                // Dispatch link validation job
                ValidateLinksJob::dispatch($page)->onQueue('default');
            }

            // Dispatch checkout flow test once per audit
            TestCheckoutFlowJob::dispatch($this->audit)->onQueue('default');

            // Dispatch aggregate results job
            // This should run after all other jobs complete
            AggregateResultsJob::dispatch($this->audit)->onQueue('high');

            Log::info("Successfully dispatched all jobs for audit {$this->audit->id}");

        } catch (Exception $e) {
            Log::error("Failed to crawl site for audit {$this->audit->id}", [
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
     * Discover pages from the target website.
     *
     * TODO: Replace this placeholder with actual Spatie Crawler implementation
     *
     * @return \Illuminate\Support\Collection<Page>
     */
    protected function discoverPages()
    {
        // TODO: Implement actual crawler logic using Spatie Crawler
        // For now, create a placeholder page record for the homepage

        $page = Page::create([
            'audit_id' => $this->audit->id,
            'url' => $this->audit->url,
            'status_code' => 200,
            'crawled_at' => now(),
        ]);

        return collect([$page]);
    }

    /**
     * Handle a job failure.
     *
     * @param Exception $exception
     * @return void
     */
    public function failed(Exception $exception): void
    {
        Log::error("CrawlSiteJob permanently failed for audit {$this->audit->id}", [
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
