<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Audit extends Model
{
    use HasUuids;

    protected $fillable = [
        'domain',
        'url',
        'status',
        'score',
        'pages_crawled',
        'max_pages',
        'config',
        'started_at',
        'completed_at',
        'created_by',
        'jobs_total',
        'jobs_completed',
        'jobs_failed',
        'current_step',
        'error_message',
    ];

    protected $casts = [
        'config' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'score' => 'integer',
        'pages_crawled' => 'integer',
        'max_pages' => 'integer',
        'jobs_total' => 'integer',
        'jobs_completed' => 'integer',
        'jobs_failed' => 'integer',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function pages(): HasMany
    {
        return $this->hasMany(Page::class);
    }

    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class);
    }

    public function links(): HasMany
    {
        return $this->hasMany(Link::class);
    }

    public function checkoutSteps(): HasMany
    {
        return $this->hasMany(CheckoutStep::class);
    }

    public function criticalIssues(): HasMany
    {
        return $this->issues()->where('severity', 'critical');
    }

    public function highIssues(): HasMany
    {
        return $this->issues()->where('severity', 'high');
    }

    public function brokenLinks(): HasMany
    {
        return $this->links()->where('is_broken', true);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isProcessing(): bool
    {
        return in_array($this->status, ['pending', 'crawling', 'analyzing']);
    }

    public function getProgressPercentage(): int
    {
        if ($this->jobs_total === 0) {
            return 0;
        }

        return (int) round(($this->jobs_completed / $this->jobs_total) * 100);
    }

    public function hasFailedJobs(): bool
    {
        return $this->jobs_failed > 0;
    }

    public function updateJobProgress(string $step, bool $increment = true): void
    {
        $data = ['current_step' => $step];

        if ($increment) {
            $data['jobs_completed'] = $this->jobs_completed + 1;
        }

        $this->update($data);
    }

    public function markJobFailed(string $errorMessage): void
    {
        $this->update([
            'jobs_failed' => $this->jobs_failed + 1,
            'error_message' => $errorMessage,
        ]);
    }
}
