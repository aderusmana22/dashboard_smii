<?php

// app/Models/TiktokShop.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class TiktokShop extends Model
{
    use HasFactory;

    protected $fillable = [
        'open_id',
        'seller_name',
        'access_token',
        'refresh_token',
        'access_token_expires_at',
        'refresh_token_expires_at',
    ];

    protected $casts = [
        'access_token_expires_at' => 'datetime',
        'refresh_token_expires_at' => 'datetime',
    ];
}