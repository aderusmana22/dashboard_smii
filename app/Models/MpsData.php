<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MpsData extends Model
{
    use HasFactory;
    protected $table = 'mps_data';
    protected $guarded = ['id']; // Izinkan mass assignment untuk semua field kecuali id
}