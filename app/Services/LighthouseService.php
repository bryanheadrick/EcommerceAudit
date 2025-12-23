<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class LighthouseService
{
    protected string $lighthousePath;
    protected int $timeout = 120;
    protected string $chromePath;

    public function __construct()
    {
        $this->lighthousePath = config('audit.lighthouse_path', 'lighthouse');
        $this->chromePath = config('audit.chrome_path', '');
    }

    public function setTimeout(int $seconds): self
    {
        $this->timeout = $seconds;

        return $this;
    }

    public function setLighthousePath(string $path): self
    {
        $this->lighthousePath = $path;

        return $this;
    }

    public function setChromePath(string $path): self
    {
        $this->chromePath = $path;

        return $this;
    }

    public function runAudit(string $url, string $deviceType = 'mobile'): ?array
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'lighthouse_');
        $outputFile = "{$tempFile}.json";

        try {
            $command = $this->buildCommand($url, $outputFile, $deviceType);

            Log::info('Running Lighthouse audit', [
                'url' => $url,
                'device_type' => $deviceType,
                'command' => $command,
            ]);

            $result = Process::timeout($this->timeout)->run($command);

            if (! $result->successful()) {
                Log::error('Lighthouse audit failed', [
                    'url' => $url,
                    'output' => $result->output(),
                    'error' => $result->errorOutput(),
                ]);

                return null;
            }

            if (! file_exists($outputFile)) {
                Log::error('Lighthouse output file not found', [
                    'expected_path' => $outputFile,
                ]);

                return null;
            }

            $json = file_get_contents($outputFile);
            $data = json_decode($json, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Failed to parse Lighthouse JSON', [
                    'error' => json_last_error_msg(),
                ]);

                return null;
            }

            Log::info('Lighthouse audit completed', [
                'url' => $url,
                'device_type' => $deviceType,
                'performance_score' => $data['categories']['performance']['score'] ?? null,
            ]);

            return $data;

        } catch (\Exception $e) {
            Log::error('Exception during Lighthouse audit', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return null;

        } finally {
            if (file_exists($tempFile)) {
                @unlink($tempFile);
            }

            if (file_exists($outputFile)) {
                @unlink($outputFile);
            }
        }
    }

    public function extractMetrics(array $lighthouseData): array
    {
        $audits = $lighthouseData['audits'] ?? [];
        $categories = $lighthouseData['categories'] ?? [];

        return [
            'lcp' => $this->extractMetricValue($audits, 'largest-contentful-paint', true),
            'fid' => $this->extractMetricValue($audits, 'max-potential-fid', false),
            'cls' => $this->extractMetricValue($audits, 'cumulative-layout-shift', false),
            'fcp' => $this->extractMetricValue($audits, 'first-contentful-paint', true),
            'ttfb' => $this->extractMetricValue($audits, 'server-response-time', false),
            'speed_index' => $this->extractMetricValue($audits, 'speed-index', false),
            'total_blocking_time' => $this->extractMetricValue($audits, 'total-blocking-time', false),
            'performance_score' => $this->extractCategoryScore($categories, 'performance'),
            'accessibility_score' => $this->extractCategoryScore($categories, 'accessibility'),
            'seo_score' => $this->extractCategoryScore($categories, 'seo'),
            'best_practices_score' => $this->extractCategoryScore($categories, 'best-practices'),
        ];
    }

    protected function buildCommand(string $url, string $outputFile, string $deviceType): string
    {
        $preset = $deviceType === 'mobile' ? 'perf' : 'desktop';

        $flags = [
            '--output=json',
            "--output-path={$outputFile}",
            "--preset={$preset}",
            '--quiet',
            '--chrome-flags="--headless --no-sandbox --disable-gpu"',
        ];

        if (! empty($this->chromePath)) {
            $flags[] = "--chrome-path={$this->chromePath}";
        }

        return "{$this->lighthousePath} {$url} " . implode(' ', $flags);
    }

    protected function extractMetricValue(array $audits, string $auditKey, bool $convertToSeconds): ?float
    {
        if (! isset($audits[$auditKey]['numericValue'])) {
            return null;
        }

        $value = $audits[$auditKey]['numericValue'];

        if ($convertToSeconds) {
            return $value / 1000;
        }

        return $value;
    }

    protected function extractCategoryScore(array $categories, string $categoryKey): ?int
    {
        if (! isset($categories[$categoryKey]['score'])) {
            return null;
        }

        return (int) ($categories[$categoryKey]['score'] * 100);
    }

    public function checkMetricThresholds(array $metrics, string $deviceType): array
    {
        $issues = [];

        if (isset($metrics['lcp'])) {
            if ($metrics['lcp'] > 4.0) {
                $issues[] = $this->createIssue(
                    'performance',
                    'critical',
                    "Poor LCP Score (" . ucfirst($deviceType) . ")",
                    "Largest Contentful Paint is {$metrics['lcp']}s, which is considered poor (should be < 2.5s).",
                    'Optimize images, reduce server response times, eliminate render-blocking resources, and use a CDN.',
                    ['metric' => 'lcp', 'value' => $metrics['lcp'], 'device' => $deviceType]
                );
            } elseif ($metrics['lcp'] > 2.5) {
                $issues[] = $this->createIssue(
                    'performance',
                    'high',
                    "LCP Needs Improvement (" . ucfirst($deviceType) . ")",
                    "Largest Contentful Paint is {$metrics['lcp']}s, which needs improvement (should be < 2.5s).",
                    'Optimize images and reduce server response times.',
                    ['metric' => 'lcp', 'value' => $metrics['lcp'], 'device' => $deviceType]
                );
            }
        }

        if (isset($metrics['cls'])) {
            if ($metrics['cls'] > 0.25) {
                $issues[] = $this->createIssue(
                    'performance',
                    'high',
                    "Poor CLS Score (" . ucfirst($deviceType) . ")",
                    "Cumulative Layout Shift is {$metrics['cls']}, which is considered poor (should be < 0.1).",
                    'Include size attributes on images and video elements, avoid inserting content above existing content, and use CSS transforms.',
                    ['metric' => 'cls', 'value' => $metrics['cls'], 'device' => $deviceType]
                );
            } elseif ($metrics['cls'] > 0.1) {
                $issues[] = $this->createIssue(
                    'performance',
                    'medium',
                    "CLS Needs Improvement (" . ucfirst($deviceType) . ")",
                    "Cumulative Layout Shift is {$metrics['cls']}, which needs improvement (should be < 0.1).",
                    'Add size attributes to images and avoid dynamic content insertion.',
                    ['metric' => 'cls', 'value' => $metrics['cls'], 'device' => $deviceType]
                );
            }
        }

        if (isset($metrics['performance_score'])) {
            if ($metrics['performance_score'] < 50) {
                $issues[] = $this->createIssue(
                    'performance',
                    'critical',
                    "Poor Performance Score (" . ucfirst($deviceType) . ")",
                    "Lighthouse performance score is {$metrics['performance_score']}/100, which is poor.",
                    'Review Lighthouse report for specific recommendations. Focus on optimizing images, reducing JavaScript, and improving server response times.',
                    ['score' => $metrics['performance_score'], 'device' => $deviceType]
                );
            } elseif ($metrics['performance_score'] < 75) {
                $issues[] = $this->createIssue(
                    'performance',
                    'medium',
                    "Performance Score Needs Improvement (" . ucfirst($deviceType) . ")",
                    "Lighthouse performance score is {$metrics['performance_score']}/100.",
                    'Review Lighthouse report for optimization opportunities.',
                    ['score' => $metrics['performance_score'], 'device' => $deviceType]
                );
            }
        }

        return $issues;
    }

    protected function createIssue(string $category, string $severity, string $title, string $description, string $recommendation, array $metadata): array
    {
        return [
            'category' => $category,
            'severity' => $severity,
            'title' => $title,
            'description' => $description,
            'recommendation' => $recommendation,
            'metadata' => $metadata,
        ];
    }
}
