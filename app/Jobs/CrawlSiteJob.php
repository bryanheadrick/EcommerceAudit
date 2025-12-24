<?php

namespace App\Jobs;

use App\Models\Audit;
use App\Models\Page;
use App\Services\CrawlerService;
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
            Log::channel('audit')->info("========================================");
            Log::channel('audit')->info("STARTING CRAWL JOB", [
                'audit_id' => $this->audit->id,
                'url' => $this->audit->url,
                'max_pages' => $this->audit->max_pages,
            ]);

            // Update audit status to crawling
            $this->audit->update([
                'status' => 'crawling',
                'started_at' => now(),
            ]);
            Log::channel('audit')->info("Updated audit status to 'crawling'");

            // TODO: Implement Spatie Crawler logic
            // - Configure crawler with max pages from $this->audit->max_pages
            // - Set appropriate timeout and user agent
            // - Respect robots.txt if configured
            // - Discover pages starting from $this->audit->url

            // Placeholder: Simulate discovered pages
            Log::channel('audit')->info("Starting page discovery...");
            $discoveredPages = $this->discoverPages();

            Log::channel('audit')->info("DISCOVERED {$discoveredPages->count()} PAGES", [
                'count' => $discoveredPages->count(),
                'pages' => $discoveredPages->pluck('url')->toArray(),
            ]);

            // Update pages crawled count
            $this->audit->update([
                'pages_crawled' => $discoveredPages->count(),
            ]);

            // Dispatch analysis jobs for each discovered page
            $jobsDispatched = 0;
            foreach ($discoveredPages as $page) {
                Log::channel('audit')->info("Dispatching jobs for page: {$page->url}");

                // Dispatch page analysis job
                AnalyzePageJob::dispatch($page)->onQueue('default');
                $jobsDispatched++;

                // Dispatch performance analysis for both mobile and desktop
                PerformanceAnalysisJob::dispatch($page, 'mobile')->onQueue('default');
                $jobsDispatched++;

                PerformanceAnalysisJob::dispatch($page, 'desktop')->onQueue('default');
                $jobsDispatched++;

                // Dispatch link validation job
                ValidateLinksJob::dispatch($page)->onQueue('default');
                $jobsDispatched++;
            }

            // Dispatch checkout flow test once per audit
            Log::channel('audit')->info("Dispatching checkout flow test");
            TestCheckoutFlowJob::dispatch($this->audit)->onQueue('default');
            $jobsDispatched++;

            // Dispatch aggregate results job
            // This should run after all other jobs complete
            Log::channel('audit')->info("Dispatching aggregate results job");
            AggregateResultsJob::dispatch($this->audit)->onQueue('high');
            $jobsDispatched++;

            Log::channel('audit')->info("SUCCESSFULLY DISPATCHED ALL JOBS", [
                'total_jobs' => $jobsDispatched,
                'audit_id' => $this->audit->id,
            ]);

        } catch (Exception $e) {
            Log::channel('audit')->error("CRAWL JOB FAILED", [
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
     * Discover pages from the target website using Spatie Crawler.
     *
     * @return \Illuminate\Support\Collection<Page>
     */
    protected function discoverPages()
    {
        $crawlerService = app(CrawlerService::class);

        $crawlerService
            ->setMaxPages($this->audit->max_pages)
            ->setConcurrency(config('audit.crawler_concurrency', 5))
            ->setDelayBetweenRequests(config('audit.crawler_delay', 100));

        return $crawlerService->crawl($this->audit);
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
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
