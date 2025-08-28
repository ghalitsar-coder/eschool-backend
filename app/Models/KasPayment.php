<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KasPayment extends Model
{
    protected $fillable = [
        'member_id',
        'kas_record_id',
        'amount',
        'month',
        'year',
        'is_paid',
        'paid_date',
    ];

    protected $casts = [
        'amount' => 'integer',
        'month' => 'integer',
        'year' => 'integer',
        'is_paid' => 'boolean',
        'paid_date' => 'datetime',
        'payment_date' => 'datetime',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function kasRecord(): BelongsTo
    {
        return $this->belongsTo(KasRecord::class);
    }
}
