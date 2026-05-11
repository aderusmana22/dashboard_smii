<?php

namespace App\Models\OIL\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryOilStock extends Model
{
    use HasFactory;

    /**
     * Nama tabel jika Anda tidak mengikuti konvensi jamak Laravel
     * (Opsional, jika nama tabel Anda berbeda)
     */
    protected $table = 'inventory_oil_stocks';
    /**
     * Koneksi database khusus untuk model ini
     * Pastikan koneksi 'mysql_oil' sudah didefinisikan di config/database.php
     */
    protected $connection = 'mysql_oil';

    /**
     * Kolom yang dapat diisi secara massal.
     * Pastikan semua kolom dari XML terdaftar di sini.
     */
    protected $fillable = [
        'ld_part',
        'pt_desc1',
        'ld_qty_oh',
        'pt_um',
        'pt_prod_line',
        'ld_loc',
        'created_at',
        'updated_at',
    ];

    /**
     * Casting tipe data agar ld_qty_oh selalu terbaca sebagai angka desimal/float
     */
    protected $casts = [
        'ld_qty_oh' => 'float',
    ];
}
