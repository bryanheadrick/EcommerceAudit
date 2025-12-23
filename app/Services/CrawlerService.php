<?php

namespace App\Services;

use App\Models\Audit;
use App\Models\Page;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Spatie\Crawler\Crawler;
use Spatie\Crawler\CrawlObservers\CrawlObserver;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

class CrawlerService
{
    protected int $maxPages = 50;
    protected int $concurrency = 5;
    protected int $delayBetweenRequests = 100;

    public function setMaxPages(int $maxPages): self
    {
        $this->maxPages = $maxPages;

        return $this;
    }

    public function setConcurrency(int $concurrency): self
    {
        $this->concurrency = $concurrency;

        return $this;
    }

    public function setDelayBetweenRequests(int $milliseconds): self
    {
        $this->delayBetweenRequests = $milliseconds;

        return $this;
    }

    /**
     * @return Collection<int, Page>
     */
    public function crawl(Audit $audit): Collection
    {
        $discoveredPages = collect();

        $observer = new class($audit, $discoveredPages, $this->maxPages) extends CrawlObserver
        {
            protected int $pageCount = 0;

            public function __construct(
                protected Audit $audit,
                protected Collection $discoveredPages,
                protected int $maxPages
            ) {
            }

            public function crawled(UriInterface $url, ResponseInterface $response, ?UriInterface $foundOnUrl = null, ?string $linkText = null): void
            {
                if ($this->pageCount >= $this->maxPages) {
                    return;
                }

                $statusCode = $response->getStatusCode();

                if ($statusCode >= 200 && $statusCode < 300) {
                    $page = Page::create([
                        'audit_id' => $this->audit->id,
                        'url' => (string) $url,
                        'status_code' => $statusCode,
                        'crawled_at' => now(),
                    ]);

                    $this->discoveredPages->push($page);
                    $this->pageCount++;

                    Log::info("Crawled page {$this->pageCount}/{$this->maxPages}", [
                        'url' => (string) $url,
                        'status_code' => $statusCode,
                    ]);
                }
            }

            public function crawlFailed(UriInterface $url, $exception, ?UriInterface $foundOnUrl = null, ?string $linkText = null): void
            {
                Log::warning('Failed to crawl URL', [
                    'url' => (string) $url,
                    'exception' => $exception->getMessage(),
                ]);
            }
        };

        Crawler::create([
            RequestOptions::TIMEOUT => 30,
            RequestOptions::ALLOW_REDIRECTS => true,
            RequestOptions::HEADERS => [
                'User-Agent' => 'EcommerceAuditBot/1.0 (Conversion Audit Tool)',
            ],
        ])
            ->setConcurrency($this->concurrency)
            ->setDelayBetweenRequests($this->delayBetweenRequests)
            ->setMaximumDepth(3)
            ->ignoreRobots()
            ->acceptNofollowLinks()
            ->setCrawlObserver($observer)
            ->startCrawling($audit->url);

        Log::info("Crawling completed for audit {$audit->id}", [
            'pages_discovered' => $discoveredPages->count(),
        ]);

        return $discoveredPages;
    }

    public function validateUrl(string $url): bool
    {
        $parsedUrl = parse_url($url);

        if (! isset($parsedUrl['scheme']) || ! isset($parsedUrl['host'])) {
            return false;
        }

        if (! in_array($parsedUrl['scheme'], ['http', 'https'])) {
            return false;
        }

        return true;
    }

    public function extractLinksFromHtml(string $html, string $baseUrl): array
    {
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);

        $links = [];
        $anchorTags = $dom->getElementsByTagName('a');

        foreach ($anchorTags as $anchor) {
            $href = $anchor->getAttribute('href');

            if (empty($href)) {
                continue;
            }

            $absoluteUrl = $this->makeAbsoluteUrl($href, $baseUrl);

            if ($absoluteUrl && $this->validateUrl($absoluteUrl)) {
                $links[] = [
                    'url' => $absoluteUrl,
                    'text' => trim($anchor->textContent),
                    'rel' => $anchor->getAttribute('rel'),
                ];
            }
        }

        return $links;
    }

    protected function makeAbsoluteUrl(string $url, string $baseUrl): ?string
    {
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        $parsedBase = parse_url($baseUrl);

        if (! $parsedBase) {
            return null;
        }

        if (str_starts_with($url, '//')) {
            return ($parsedBase['scheme'] ?? 'https') . ':' . $url;
        }

        if (str_starts_with($url, '/')) {
            return ($parsedBase['scheme'] ?? 'https') . '://' . $parsedBase['host'] . $url;
        }

        if (str_starts_with($url, '#') || str_starts_with($url, 'javascript:') || str_starts_with($url, 'mailto:')) {
            return null;
        }

        $basePath = dirname($parsedBase['path'] ?? '/');

        return ($parsedBase['scheme'] ?? 'https') . '://' . $parsedBase['host'] . rtrim($basePath, '/') . '/' . $url;
    }
}
