<?php

namespace App\Models\OIL\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterOilTank extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $connection = 'mysql_oil';
    protected $table = 'master_oil_tank';
}
