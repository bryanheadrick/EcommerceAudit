<?php

namespace App\Jobs;

use App\Models\Issue;
use App\Models\Page;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Analyzes a single page for SEO elements and metadata.
 *
 * This job:
 * - Extracts metadata (title, meta description, H1)
 * - Captures screenshot using Browsershot
 * - Stores HTML excerpt (first 2000 chars)
 * - Checks SEO elements and creates Issue records for problems
 * - Updates page record with all extracted data
 */
class AnalyzePageJob implements ShouldQueue
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
     * Create a new job instance.
     *
     * @param Page $page The page to analyze
     */
    public function __construct(Page $page)
    {
        $this->page = $page;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        try {
            Log::channel('audit')->info("→ Analyzing page: {$this->page->url}", [
                'page_id' => $this->page->id,
                'audit_id' => $this->page->audit_id,
            ]);

            // TODO: Fetch page HTML content
            // - Use HTTP client or Browsershot to get page content
            // - Handle redirects, timeouts, and errors appropriately
            Log::channel('audit')->info("  Fetching page HTML...");
            $html = $this->fetchPageHtml();

            // Extract metadata from HTML
            Log::channel('audit')->info("  Extracting metadata...");
            $metadata = $this->extractMetadata($html);

            // TODO: Capture screenshot using Browsershot
            // - Configure viewport size (desktop: 1920x1080)
            // - Set quality to 80%
            // - Store in storage/app/screenshots
            // - Return relative path for database storage
            Log::channel('audit')->info("  Capturing screenshot...");
            $screenshotPath = $this->captureScreenshot();

            // Store HTML excerpt (first 2000 chars)
            $htmlExcerpt = mb_substr($html, 0, 2000);

            // Update page record with extracted data
            $this->page->update([
                'title' => $metadata['title'],
                'meta_description' => $metadata['meta_description'],
                'h1' => $metadata['h1'],
                'screenshot_path' => $screenshotPath,
                'html_excerpt' => $htmlExcerpt,
            ]);

            // Check SEO elements and create issues
            Log::channel('audit')->info("  Checking SEO elements...");
            $issuesFound = $this->checkSeoElements($metadata);

            Log::channel('audit')->info("✓ Page analysis complete", [
                'page_id' => $this->page->id,
                'title' => $metadata['title'],
                'issues_found' => $issuesFound,
            ]);

        } catch (Exception $e) {
            Log::channel('audit')->error("✗ Page analysis failed: {$this->page->url}", [
                'page_id' => $this->page->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Fetch the HTML content of the page.
     *
     * TODO: Replace with actual HTTP client or Browsershot implementation
     *
     * @return string
     */
    protected function fetchPageHtml(): string
    {
        // TODO: Implement actual page fetching logic
        // - Use Guzzle or Laravel HTTP client
        // - Set appropriate user agent
        // - Handle timeouts (30 seconds)
        // - Follow redirects (max 3)
        // - Return full HTML content

        return '<html><head><title>Sample Page</title><meta name="description" content="Sample description"></head><body><h1>Sample H1</h1></body></html>';
    }

    /**
     * Extract metadata from HTML content.
     *
     * @param string $html
     * @return array
     */
    protected function extractMetadata(string $html): array
    {
        // TODO: Implement proper HTML parsing using DOMDocument or Symfony DomCrawler
        // - Extract title tag content
        // - Extract meta description
        // - Extract first H1 tag
        // - Handle missing elements gracefully

        $metadata = [
            'title' => null,
            'meta_description' => null,
            'h1' => null,
        ];

        // Simple regex-based extraction (replace with proper HTML parser)
        if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $matches)) {
            $metadata['title'] = trim($matches[1]);
        }

        if (preg_match('/<meta\s+name=["\']description["\']\s+content=["\'](.*?)["\']/is', $html, $matches)) {
            $metadata['meta_description'] = trim($matches[1]);
        }

        if (preg_match('/<h1[^>]*>(.*?)<\/h1>/is', $html, $matches)) {
            $metadata['h1'] = trim(strip_tags($matches[1]));
        }

        return $metadata;
    }

    /**
     * Capture a screenshot of the page.
     *
     * TODO: Replace with actual Browsershot implementation
     *
     * @return string|null Screenshot path relative to storage
     */
    protected function captureScreenshot(): ?string
    {
        // TODO: Implement Browsershot screenshot capture
        // - Use Browsershot::url($this->page->url)
        // - Set window size to 1920x1080
        // - Set quality to 80
        // - Save to storage/app/screenshots/{audit_id}/{page_id}.png
        // - Return path relative to storage/app

        return "screenshots/{$this->page->audit_id}/{$this->page->id}.png";
    }

    /**
     * Check SEO elements and create issues for problems.
     *
     * @param array $metadata
     * @return int Number of issues found
     */
    protected function checkSeoElements(array $metadata): int
    {
        $issuesFound = 0;
        // Check for missing title
        if (empty($metadata['title'])) {
            Issue::create([
                'audit_id' => $this->page->audit_id,
                'page_id' => $this->page->id,
                'category' => 'seo',
                'severity' => 'high',
                'title' => 'Missing Page Title',
                'description' => "The page at {$this->page->url} does not have a title tag.",
                'recommendation' => 'Add a descriptive title tag (50-60 characters) that includes target keywords.',
                'affected_element' => '<title>',
            ]);
            $issuesFound++;
        } elseif (mb_strlen($metadata['title']) < 30) {
            Issue::create([
                'audit_id' => $this->page->audit_id,
                'page_id' => $this->page->id,
                'category' => 'seo',
                'severity' => 'medium',
                'title' => 'Title Too Short',
                'description' => "The page title is only " . mb_strlen($metadata['title']) . " characters long.",
                'recommendation' => 'Expand the title to 50-60 characters for better SEO.',
                'affected_element' => '<title>',
            ]);
            $issuesFound++;
        } elseif (mb_strlen($metadata['title']) > 60) {
            Issue::create([
                'audit_id' => $this->page->audit_id,
                'page_id' => $this->page->id,
                'category' => 'seo',
                'severity' => 'low',
                'title' => 'Title Too Long',
                'description' => "The page title is " . mb_strlen($metadata['title']) . " characters long and may be truncated in search results.",
                'recommendation' => 'Shorten the title to 50-60 characters.',
                'affected_element' => '<title>',
            ]);
            $issuesFound++;
        }

        // Check for missing meta description
        if (empty($metadata['meta_description'])) {
            Issue::create([
                'audit_id' => $this->page->audit_id,
                'page_id' => $this->page->id,
                'category' => 'seo',
                'severity' => 'medium',
                'title' => 'Missing Meta Description',
                'description' => "The page at {$this->page->url} does not have a meta description.",
                'recommendation' => 'Add a compelling meta description (150-160 characters) that encourages clicks.',
                'affected_element' => '<meta name="description">',
            ]);
            $issuesFound++;
        } elseif (mb_strlen($metadata['meta_description']) > 160) {
            Issue::create([
                'audit_id' => $this->page->audit_id,
                'page_id' => $this->page->id,
                'category' => 'seo',
                'severity' => 'low',
                'title' => 'Meta Description Too Long',
                'description' => "The meta description is " . mb_strlen($metadata['meta_description']) . " characters and may be truncated in search results.",
                'recommendation' => 'Shorten the meta description to 150-160 characters.',
                'affected_element' => '<meta name="description">',
            ]);
            $issuesFound++;
        }

        // Check for missing H1
        if (empty($metadata['h1'])) {
            Issue::create([
                'audit_id' => $this->page->audit_id,
                'page_id' => $this->page->id,
                'category' => 'seo',
                'severity' => 'medium',
                'title' => 'Missing H1 Tag',
                'description' => "The page at {$this->page->url} does not have an H1 heading.",
                'recommendation' => 'Add a single, descriptive H1 heading that clearly indicates the page content.',
                'affected_element' => '<h1>',
            ]);
            $issuesFound++;
        }

        return $issuesFound;
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("AnalyzePageJob permanently failed for page {$this->page->id}", [
            'page_id' => $this->page->id,
            'url' => $this->page->url,
            'error' => $exception->getMessage(),
        ]);

        // Optionally create an issue for the failed analysis
        Issue::create([
            'audit_id' => $this->page->audit_id,
            'page_id' => $this->page->id,
            'category' => 'seo',
            'severity' => 'high',
            'title' => 'Page Analysis Failed',
            'description' => "Failed to analyze page: {$exception->getMessage()}",
            'recommendation' => 'Check if the page is accessible and retry the audit.',
        ]);
    }
}
