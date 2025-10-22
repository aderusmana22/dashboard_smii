<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyPpicReport extends Model
{
    use HasFactory;
    protected $table = 'daily_ppic_reports';
    protected $guarded = ['id'];
}