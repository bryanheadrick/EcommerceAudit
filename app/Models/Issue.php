<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Issue extends Model
{
    use HasUuids;

    protected $fillable = [
        'audit_id',
        'page_id',
        'category',
        'severity',
        'title',
        'description',
        'recommendation',
        'affected_element',
        'screenshot_path',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function audit(): BelongsTo
    {
        return $this->belongsTo(Audit::class);
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}
