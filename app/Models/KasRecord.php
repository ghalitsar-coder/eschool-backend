<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KasRecord extends Model
{
    protected $fillable = [
        'eschool_id',
        'recorder_id',
        'type',
        'amount',
        'description',
        'date',
    ];

    protected $casts = [
        'amount' => 'integer',
        'date' => 'datetime',
    ];

    public function eschool(): BelongsTo
    {
        return $this->belongsTo(Eschool::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorder_id');
    }



    public function kasPayments(): HasMany
    {
        return $this->hasMany(KasPayment::class);
    }

    // Alias for easier access
    public function payments(): HasMany
    {
        return $this->hasMany(KasPayment::class);
    }
}
