<?php

namespace App\Jobs;

use App\Models\Issue;
use App\Models\Link;
use App\Models\Page;
use App\Services\CrawlerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Validates all links on a page and identifies broken links.
 *
 * This job:
 * - Extracts all links from page HTML
 * - Determines link type (internal, external, asset)
 * - Checks HTTP status for each link
 * - Creates Link records
 * - Creates Issue records for broken links
 */
class ValidateLinksJob implements ShouldQueue
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
     * The page to validate links for.
     *
     * @var Page
     */
    protected Page $page;

    /**
     * Create a new job instance.
     *
     * @param Page $page The page to validate links for
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
            Log::info("Validating links for page {$this->page->id}", [
                'page_id' => $this->page->id,
                'url' => $this->page->url,
            ]);

            // TODO: Fetch page HTML if not already stored
            $html = $this->getPageHtml();

            // Extract all links from HTML
            $links = $this->extractLinks($html);

            Log::info("Found {$links->count()} links on page {$this->page->id}");

            $brokenLinksCount = 0;
            $brokenLinks = [];

            // Validate each link
            foreach ($links as $linkData) {
                $statusCode = $this->checkLinkStatus($linkData['url']);
                $isBroken = $this->isBrokenStatusCode($statusCode);

                // Create link record
                Link::create([
                    'audit_id' => $this->page->audit_id,
                    'source_page_id' => $this->page->id,
                    'destination_url' => $linkData['url'],
                    'link_text' => $linkData['text'],
                    'link_type' => $linkData['type'],
                    'status_code' => $statusCode,
                    'is_broken' => $isBroken,
                    'checked_at' => now(),
                ]);

                if ($isBroken) {
                    $brokenLinksCount++;
                    $brokenLinks[] = [
                        'url' => $linkData['url'],
                        'text' => $linkData['text'],
                        'status' => $statusCode,
                    ];
                }
            }

            // Create issues for broken links
            if ($brokenLinksCount > 0) {
                $this->createBrokenLinkIssues($brokenLinks, $brokenLinksCount);
            }

            Log::info("Successfully validated links for page {$this->page->id}", [
                'total_links' => $links->count(),
                'broken_links' => $brokenLinksCount,
            ]);

        } catch (Exception $e) {
            Log::error("Failed to validate links for page {$this->page->id}", [
                'page_id' => $this->page->id,
                'url' => $this->page->url,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Get the HTML content of the page from stored data or fetch it.
     *
     * @return string
     */
    protected function getPageHtml(): string
    {
        if ($this->page->html_excerpt) {
            return $this->page->html_excerpt;
        }

        try {
            $response = Http::timeout(30)->get($this->page->url);

            return $response->body();
        } catch (\Exception $e) {
            Log::warning('Failed to fetch HTML for link validation', [
                'page_id' => $this->page->id,
                'url' => $this->page->url,
                'error' => $e->getMessage(),
            ]);

            return '';
        }
    }

    /**
     * Extract all links from HTML using CrawlerService.
     *
     * @param string $html
     * @return \Illuminate\Support\Collection
     */
    protected function extractLinks(string $html): \Illuminate\Support\Collection
    {
        $crawlerService = app(CrawlerService::class);

        $extractedLinks = $crawlerService->extractLinksFromHtml($html, $this->page->url);

        $links = collect();

        foreach ($extractedLinks as $linkData) {
            $links->push([
                'url' => $linkData['url'],
                'text' => $linkData['text'] ?? null,
                'type' => $this->determineLinkType($linkData['url']),
            ]);
        }

        // Also extract image sources
        if (preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/is', $html, $matches)) {
            foreach ($matches[1] as $src) {
                $absoluteUrl = $crawlerService->extractLinksFromHtml("<a href=\"{$src}\"></a>", $this->page->url)[0]['url'] ?? $src;

                $links->push([
                    'url' => $absoluteUrl,
                    'text' => null,
                    'type' => 'asset',
                ]);
            }
        }

        return $links->unique('url');
    }

    /**
     * Normalize a URL (convert relative to absolute).
     *
     * @param string $url
     * @return string
     */
    protected function normalizeUrl(string $url): string
    {
        // Skip if already absolute URL
        if (preg_match('/^https?:\/\//', $url)) {
            return $url;
        }

        // Skip anchors and javascript links
        if (str_starts_with($url, '#') || str_starts_with($url, 'javascript:')) {
            return $url;
        }

        // TODO: Implement proper URL normalization
        // - Parse base URL from $this->page->url
        // - Resolve relative paths
        // - Handle protocol-relative URLs

        $baseUrl = parse_url($this->page->url);
        $scheme = $baseUrl['scheme'] ?? 'https';
        $host = $baseUrl['host'] ?? '';

        if (str_starts_with($url, '//')) {
            return $scheme . ':' . $url;
        }

        if (str_starts_with($url, '/')) {
            return $scheme . '://' . $host . $url;
        }

        return $url;
    }

    /**
     * Determine the type of link.
     *
     * @param string $url
     * @return string
     */
    protected function determineLinkType(string $url): string
    {
        // Skip special URLs
        if (str_starts_with($url, '#') || str_starts_with($url, 'javascript:') || str_starts_with($url, 'mailto:')) {
            return 'internal';
        }

        $pageHost = parse_url($this->page->url, PHP_URL_HOST);
        $linkHost = parse_url($url, PHP_URL_HOST);

        // Check if asset based on extension
        $assetExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'css', 'js', 'pdf', 'zip'];
        $extension = strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));

        if (in_array($extension, $assetExtensions)) {
            return 'asset';
        }

        // Check if internal or external
        if ($linkHost === $pageHost) {
            return 'internal';
        }

        return 'external';
    }

    /**
     * Check the HTTP status of a link using Laravel HTTP client.
     *
     * @param string $url
     * @return int|null
     */
    protected function checkLinkStatus(string $url): ?int
    {
        // Skip special URLs
        if (str_starts_with($url, '#') || str_starts_with($url, 'javascript:') || str_starts_with($url, 'mailto:')) {
            return 200;
        }

        try {
            $response = Http::timeout(5)
                ->withOptions(['allow_redirects' => ['max' => 3]])
                ->head($url);

            return $response->status();

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::warning("Connection failed for link", [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return null;

        } catch (\Exception $e) {
            Log::warning("Failed to check link status", [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Determine if a status code indicates a broken link.
     *
     * @param int|null $statusCode
     * @return bool
     */
    protected function isBrokenStatusCode(?int $statusCode): bool
    {
        if ($statusCode === null) {
            return true;
        }

        return $statusCode >= 400;
    }

    /**
     * Create issue records for broken links.
     *
     * @param array $brokenLinks
     * @param int $count
     * @return void
     */
    protected function createBrokenLinkIssues(array $brokenLinks, int $count): void
    {
        $severity = $count > 5 ? 'high' : 'medium';
        $linksList = array_slice($brokenLinks, 0, 5); // Show first 5 broken links

        $description = "Found {$count} broken link(s) on this page:\n";
        foreach ($linksList as $link) {
            $description .= "- {$link['url']} (Status: {$link['status']})";
            if ($link['text']) {
                $description .= " - Link text: \"{$link['text']}\"";
            }
            $description .= "\n";
        }

        if ($count > 5) {
            $description .= "\n... and " . ($count - 5) . " more broken links.";
        }

        Issue::create([
            'audit_id' => $this->page->audit_id,
            'page_id' => $this->page->id,
            'category' => 'links',
            'severity' => $severity,
            'title' => "Broken Links Found ({$count})",
            'description' => $description,
            'recommendation' => 'Fix or remove broken links. Check if the linked pages have moved or been deleted, and update the URLs accordingly.',
            'metadata' => ['broken_links_count' => $count],
        ]);
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("ValidateLinksJob permanently failed for page {$this->page->id}", [
            'page_id' => $this->page->id,
            'url' => $this->page->url,
            'error' => $exception->getMessage(),
        ]);

        Issue::create([
            'audit_id' => $this->page->audit_id,
            'page_id' => $this->page->id,
            'category' => 'links',
            'severity' => 'medium',
            'title' => 'Link Validation Failed',
            'description' => "Failed to validate links on this page: {$exception->getMessage()}",
            'recommendation' => 'Retry the audit or manually check links on this page.',
        ]);
    }
}
