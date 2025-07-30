<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobAttachment extends Model
{
    use HasFactory;
    protected $table = 'job_attachments';
    const UPDATED_AT = null;
    protected $fillable = ['job_id', 'job_route_id', 'file_path', 'file_name', 'uploaded_by', 'uploaded_at'];
}