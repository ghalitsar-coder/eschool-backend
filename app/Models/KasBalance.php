<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KasBalance extends Model
{
    protected $fillable = [
        'eschool_id',
        'user_id',
        'balance',
        'last_transaction_id',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    public function eschool(): BelongsTo
    {
        return $this->belongsTo(Eschool::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lastTransaction(): BelongsTo
    {
        return $this->belongsTo(KasRecord::class, 'last_transaction_id');
    }
}