<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OilBatchRefineryTank extends Model
{
    protected $guarded = [];

    public function readings()
    {
        return $this->hasMany(OilBatchRefineryReading::class, 'tank_id');
    }
}