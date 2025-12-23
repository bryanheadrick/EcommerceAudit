<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckoutStep extends Model
{
    use HasUuids;

    protected $fillable = [
        'audit_id',
        'step_number',
        'step_name',
        'url',
        'screenshot_path',
        'form_fields_count',
        'errors_found',
        'load_time',
        'successful',
    ];

    protected $casts = [
        'step_number' => 'integer',
        'form_fields_count' => 'integer',
        'errors_found' => 'array',
        'load_time' => 'integer',
        'successful' => 'boolean',
    ];

    public function audit(): BelongsTo
    {
        return $this->belongsTo(Audit::class);
    }
}
