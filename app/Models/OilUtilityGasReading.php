<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OilUtilityGasReading extends Model
{
    protected $table = 'oil_stock_utility_gas_readings';
    protected $guarded = [];

    public function master()
    {
        return $this->belongsTo(OilUtilityGasMaster::class, 'master_id');
    }
}