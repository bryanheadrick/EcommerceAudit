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
    ];

    protected $casts = [
        'config' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'score' => 'integer',
        'pages_crawled' => 'integer',
        'max_pages' => 'integer',
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
}
