<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    use HasUuids;

    protected $fillable = [
        'audit_id',
        'url',
        'status_code',
        'title',
        'meta_description',
        'h1',
        'load_time',
        'screenshot_path',
        'html_excerpt',
        'crawled_at',
    ];

    protected $casts = [
        'status_code' => 'integer',
        'load_time' => 'integer',
        'crawled_at' => 'datetime',
    ];

    public function audit(): BelongsTo
    {
        return $this->belongsTo(Audit::class);
    }

    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class);
    }

    public function performanceMetrics(): HasMany
    {
        return $this->hasMany(PerformanceMetric::class);
    }

    public function links(): HasMany
    {
        return $this->hasMany(Link::class, 'source_page_id');
    }
}
