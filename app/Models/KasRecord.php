<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Eschool;
use App\Models\KasPayment;

class KasRecord extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'eschool_id',
        'description',
        'category',
        'amount',
        'date',
        'recorder_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    /**
     * Get the eschool that owns the kas record.
     */
    public function eschool()
    {
        return $this->belongsTo(Eschool::class);
    }

    /**
     * Get the kas payments for the kas record.
     */
    public function kasPayments()
    {
        return $this->hasMany(KasPayment::class);
    }
}