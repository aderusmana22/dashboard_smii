<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobNote extends Model
{
    use HasFactory;
    protected $table = 'job_notes';
    const UPDATED_AT = null;
    protected $fillable = ['job_id', 'job_route_id', 'note', 'created_by', 'created_at'];
}