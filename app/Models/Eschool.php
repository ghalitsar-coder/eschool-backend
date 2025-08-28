<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Eschool extends Model
{
    protected $fillable = [
        'school_id',
        'coordinator_id',
        'treasurer_id',
        'name',
        'description',
        'monthly_kas_amount',
        'schedule_days',
        'total_schedule_days',
        'is_active',
    ];

    protected $casts = [
        'schedule_days' => 'array',
        'is_active' => 'boolean',
        'monthly_kas_amount' => 'integer',
        'total_schedule_days' => 'integer',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function coordinator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }

    public function treasurer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'treasurer_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function kasRecords(): HasMany
    {
        return $this->hasMany(KasRecord::class);
    }
}
