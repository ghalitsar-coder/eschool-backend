<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\KasRecord;
use App\Models\UserEschoolRole;

class KasPayment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kas_record_id',
        'member_id',
        'amount',
        'month',
        'year',
        'is_paid',
        'paid_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'is_paid' => 'boolean',
        'paid_date' => 'date',
    ];

    /**
     * Get the kas record that owns the kas payment.
     */
    public function kasRecord()
    {
        return $this->belongsTo(KasRecord::class);
    }

    /**
     * Get the user eschool role (member) that owns the kas payment.
     */
    public function member()
    {
        return $this->belongsTo(UserEschoolRole::class, 'member_id');
    }
}