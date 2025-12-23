<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceMetric extends Model
{
    use HasUuids;

    protected $fillable = [
        'page_id',
        'device_type',
        'lcp',
        'fid',
        'cls',
        'fcp',
        'ttfb',
        'speed_index',
        'total_blocking_time',
        'lighthouse_performance_score',
        'lighthouse_accessibility_score',
        'lighthouse_seo_score',
        'lighthouse_best_practices_score',
        'lighthouse_json',
    ];

    protected $casts = [
        'lcp' => 'float',
        'fid' => 'float',
        'cls' => 'float',
        'fcp' => 'float',
        'ttfb' => 'integer',
        'speed_index' => 'float',
        'total_blocking_time' => 'integer',
        'lighthouse_performance_score' => 'integer',
        'lighthouse_accessibility_score' => 'integer',
        'lighthouse_seo_score' => 'integer',
        'lighthouse_best_practices_score' => 'integer',
        'lighthouse_json' => 'array',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}
