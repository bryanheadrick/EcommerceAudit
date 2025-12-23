<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Spatie\Browsershot\Browsershot;
use Spatie\Browsershot\Exceptions\CouldNotTakeBrowsershot;

class PuppeteerService
{
    protected int $timeout = 60000;
    protected bool $waitUntilNetworkIdle = true;
    protected array $viewport = ['width' => 1920, 'height' => 1080];

    public function setTimeout(int $milliseconds): self
    {
        $this->timeout = $milliseconds;

        return $this;
    }

    public function setViewport(int $width, int $height): self
    {
        $this->viewport = ['width' => $width, 'height' => $height];

        return $this;
    }

    public function setWaitUntilNetworkIdle(bool $wait): self
    {
        $this->waitUntilNetworkIdle = $wait;

        return $this;
    }

    public function takeScreenshot(string $url, string $savePath, bool $fullPage = true): bool
    {
        try {
            $browsershot = Browsershot::url($url)
                ->timeout($this->timeout)
                ->windowSize($this->viewport['width'], $this->viewport['height']);

            if ($this->waitUntilNetworkIdle) {
                $browsershot->waitUntilNetworkIdle();
            }

            if ($fullPage) {
                $browsershot->fullPage();
            }

            $browsershot->save($savePath);

            Log::info('Screenshot captured', [
                'url' => $url,
                'path' => $savePath,
            ]);

            return true;

        } catch (CouldNotTakeBrowsershot $e) {
            Log::error('Failed to capture screenshot', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function takeMobileScreenshot(string $url, string $savePath): bool
    {
        try {
            Browsershot::url($url)
                ->timeout($this->timeout)
                ->device('iPhone 12 Pro')
                ->waitUntilNetworkIdle()
                ->fullPage()
                ->save($savePath);

            Log::info('Mobile screenshot captured', [
                'url' => $url,
                'path' => $savePath,
            ]);

            return true;

        } catch (CouldNotTakeBrowsershot $e) {
            Log::error('Failed to capture mobile screenshot', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function getPageHtml(string $url): ?string
    {
        try {
            $html = Browsershot::url($url)
                ->timeout($this->timeout)
                ->waitUntilNetworkIdle()
                ->bodyHtml();

            return $html;

        } catch (\Exception $e) {
            Log::error('Failed to get page HTML', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function evaluateJavaScript(string $url, string $script): mixed
    {
        try {
            $result = Browsershot::url($url)
                ->timeout($this->timeout)
                ->waitUntilNetworkIdle()
                ->evaluate($script);

            return $result;

        } catch (\Exception $e) {
            Log::error('Failed to evaluate JavaScript', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function testCheckoutFlow(string $url, array $steps): array
    {
        $results = [];

        try {
            $browsershot = Browsershot::url($url)
                ->timeout($this->timeout)
                ->windowSize(1920, 1080)
                ->waitUntilNetworkIdle();

            foreach ($steps as $index => $step) {
                $stepResult = [
                    'step' => $step['name'] ?? "Step {$index}",
                    'completed' => false,
                    'error' => null,
                    'screenshot' => null,
                ];

                try {
                    if (isset($step['selector'])) {
                        $exists = $this->elementExists($url, $step['selector']);
                        $stepResult['completed'] = $exists;

                        if (! $exists) {
                            $stepResult['error'] = "Element not found: {$step['selector']}";
                        }
                    }

                    if (isset($step['screenshot_path'])) {
                        $browsershot->save($step['screenshot_path']);
                        $stepResult['screenshot'] = $step['screenshot_path'];
                    }

                } catch (\Exception $e) {
                    $stepResult['error'] = $e->getMessage();
                }

                $results[] = $stepResult;
            }

        } catch (\Exception $e) {
            Log::error('Failed to test checkout flow', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
        }

        return $results;
    }

    public function elementExists(string $url, string $selector): bool
    {
        try {
            $script = "document.querySelector('{$selector}') !== null";

            $result = $this->evaluateJavaScript($url, $script);

            return (bool) $result;

        } catch (\Exception $e) {
            return false;
        }
    }

    public function checkMobileViewport(string $url): array
    {
        try {
            $script = <<<'JS'
                {
                    hasViewportMeta: !!document.querySelector('meta[name="viewport"]'),
                    viewportContent: document.querySelector('meta[name="viewport"]')?.getAttribute('content') || null,
                    documentWidth: document.documentElement.clientWidth,
                    bodyWidth: document.body.clientWidth
                }
            JS;

            $result = Browsershot::url($url)
                ->timeout($this->timeout)
                ->device('iPhone 12 Pro')
                ->waitUntilNetworkIdle()
                ->evaluate($script);

            return $result ?? [];

        } catch (\Exception $e) {
            Log::error('Failed to check mobile viewport', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    public function getPdf(string $url, string $savePath): bool
    {
        try {
            Browsershot::url($url)
                ->timeout($this->timeout)
                ->waitUntilNetworkIdle()
                ->margins(10, 10, 10, 10)
                ->savePdf($savePath);

            Log::info('PDF generated', [
                'url' => $url,
                'path' => $savePath,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to generate PDF', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
