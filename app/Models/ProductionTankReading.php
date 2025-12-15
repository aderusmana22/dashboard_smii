<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionTankReading extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    // --- TAMBAHKAN BLOK KODE INI ---
    protected $casts = [
        'reading_date' => 'date', // atau 'date' juga bisa
    ];
    // --------------------------------

    public function productionTank(): BelongsTo
    {
        return $this->belongsTo(ProductionTank::class);
    }
}