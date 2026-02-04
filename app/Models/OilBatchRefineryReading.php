<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OilBatchRefineryReading extends Model
{
    protected $guarded = [];

    // TAMBAHKAN BARIS INI
    protected $casts = [
        'reading_date' => 'date',
    ];

    public function tank()
    {
        return $this->belongsTo(OilBatchRefineryTank::class, 'tank_id');
    }
}