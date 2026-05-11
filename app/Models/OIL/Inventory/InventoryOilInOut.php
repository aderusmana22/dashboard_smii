<?php

namespace App\Models\OIL\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryOilInOut extends Model
{
    use HasFactory;

    protected $table = 'inventory_oil_in_outs';
    protected $connection = 'mysql_oil';
    /**
     * Mass assignable fields for InventoryOilInOut
     * Keep in sync with DB columns.
     *
     * @var array
     */
    protected $fillable = [
        'tr_trnbr',
        'tr_part',
        'tr_part_name',
        'tr_addr',
        'tr_addr_name',
        'tr_date',
        'tr_qty_chg',
        'tr_um',   
        'type'
    ];
}
