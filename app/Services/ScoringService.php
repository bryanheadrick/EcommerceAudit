<?php

namespace App\Services;

use App\Models\Audit;
use Illuminate\Database\Eloquent\Collection;

class ScoringService
{
    protected array $weights = [
        'performance' => 0.30,
        'mobile' => 0.25,
        'seo' => 0.20,
        'checkout' => 0.15,
        'links' => 0.10,
    ];

    protected array $severityPenalties = [
        'critical' => 20,
        'high' => 10,
        'medium' => 5,
        'low' => 2,
        'info' => 0,
    ];

    public function setWeights(array $weights): self
    {
        $this->weights = array_merge($this->weights, $weights);

        return $this;
    }

    public function setSeverityPenalties(array $penalties): self
    {
        $this->severityPenalties = array_merge($this->severityPenalties, $penalties);

        return $this;
    }

    public function calculateOverallScore(Audit $audit): int
    {
        $performanceScore = $this->calculatePerformanceScore($audit);
        $mobileScore = $this->calculateMobileScore($audit);
        $seoScore = $this->calculateSeoScore($audit);
        $checkoutScore = $this->calculateCheckoutScore($audit);
        $linksScore = $this->calculateLinksScore($audit);

        $overallScore = (
            ($performanceScore * $this->weights['performance']) +
            ($mobileScore * $this->weights['mobile']) +
            ($seoScore * $this->weights['seo']) +
            ($checkoutScore * $this->weights['checkout']) +
            ($linksScore * $this->weights['links'])
        );

        return (int) round($overallScore);
    }

    public function calculateCategoryScores(Audit $audit): array
    {
        return [
            'performance' => $this->calculatePerformanceScore($audit),
            'mobile' => $this->calculateMobileScore($audit),
            'seo' => $this->calculateSeoScore($audit),
            'checkout' => $this->calculateCheckoutScore($audit),
            'links' => $this->calculateLinksScore($audit),
        ];
    }

    public function calculatePerformanceScore(Audit $audit): float
    {
        $performanceMetrics = $audit->pages()
            ->with('performanceMetrics')
            ->get()
            ->flatMap(fn($page) => $page->performanceMetrics);

        if ($performanceMetrics->isEmpty()) {
            return 0;
        }

        $averageScore = $performanceMetrics->avg('lighthouse_performance_score');

        return $averageScore ?? 0;
    }

    public function calculateMobileScore(Audit $audit): float
    {
        $mobileMetrics = $audit->pages()
            ->with(['performanceMetrics' => fn($query) => $query->where('device_type', 'mobile')])
            ->get()
            ->flatMap(fn($page) => $page->performanceMetrics);

        if ($mobileMetrics->isEmpty()) {
            return 0;
        }

        $mobileLighthouseScore = $mobileMetrics->avg('lighthouse_performance_score') ?? 0;

        $mobileIssues = $audit->issues()
            ->where('category', 'mobile')
            ->get();

        $issuesPenalty = $this->calculateIssuesPenalty($mobileIssues);

        return ($mobileLighthouseScore * 0.60) + ((100 - $issuesPenalty) * 0.40);
    }

    public function calculateSeoScore(Audit $audit): float
    {
        $seoIssues = $audit->issues()
            ->where('category', 'seo')
            ->get();

        $totalPages = $audit->pages()->count();

        if ($totalPages === 0) {
            return 0;
        }

        $score = 100;
        $penalty = $this->calculateIssuesPenalty($seoIssues);

        return max(0, $score - $penalty);
    }

    public function calculateCheckoutScore(Audit $audit): float
    {
        $checkoutIssues = $audit->issues()
            ->where('category', 'checkout')
            ->get();

        $score = 100;
        $penalty = $this->calculateIssuesPenalty($checkoutIssues);

        return max(0, $score - $penalty);
    }

    public function calculateLinksScore(Audit $audit): float
    {
        $totalLinks = $audit->links()->count();

        if ($totalLinks === 0) {
            return 100;
        }

        $brokenLinks = $audit->links()
            ->where('is_broken', true)
            ->count();

        $workingLinksPercentage = (($totalLinks - $brokenLinks) / $totalLinks) * 100;

        return $workingLinksPercentage;
    }

    public function calculateIssuesPenalty(Collection $issues): float
    {
        $penalty = 0;

        foreach ($issues as $issue) {
            $penalty += $this->severityPenalties[$issue->severity] ?? 0;
        }

        return $penalty;
    }

    public function getScoreGrade(int $score): string
    {
        if ($score >= 90) {
            return 'A';
        }

        if ($score >= 80) {
            return 'B';
        }

        if ($score >= 70) {
            return 'C';
        }

        if ($score >= 60) {
            return 'D';
        }

        return 'F';
    }

    public function getScoreLabel(int $score): string
    {
        if ($score >= 90) {
            return 'Excellent';
        }

        if ($score >= 80) {
            return 'Good';
        }

        if ($score >= 70) {
            return 'Fair';
        }

        if ($score >= 60) {
            return 'Poor';
        }

        return 'Critical';
    }

    public function getScoreColor(int $score): string
    {
        if ($score >= 90) {
            return 'green';
        }

        if ($score >= 80) {
            return 'blue';
        }

        if ($score >= 70) {
            return 'yellow';
        }

        if ($score >= 60) {
            return 'orange';
        }

        return 'red';
    }

    public function calculateScoreChange(int $currentScore, int $previousScore): array
    {
        $change = $currentScore - $previousScore;

        $percentageChange = $previousScore > 0
            ? (($change / $previousScore) * 100)
            : 0;

        return [
            'absolute' => $change,
            'percentage' => round($percentageChange, 2),
            'direction' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'neutral'),
        ];
    }
}
