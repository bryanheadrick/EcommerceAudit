<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Link extends Model
{
    use HasUuids;

    protected $fillable = [
        'audit_id',
        'source_page_id',
        'destination_url',
        'link_text',
        'link_type',
        'status_code',
        'is_broken',
        'checked_at',
    ];

    protected $casts = [
        'status_code' => 'integer',
        'is_broken' => 'boolean',
        'checked_at' => 'datetime',
    ];

    public function audit(): BelongsTo
    {
        return $this->belongsTo(Audit::class);
    }

    public function sourcePage(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'source_page_id');
    }
}
