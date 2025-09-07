<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Eschool;
use App\Models\KasPayment;
use App\Models\UserEschoolRole;

class KasRecord extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'kas_record';

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

    /**
     * Get the recorder (user eschool role) that created the kas record.
     */
    public function recorder()
    {
        return $this->belongsTo(UserEschoolRole::class, 'recorder_id');
    }
}