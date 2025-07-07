<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentApprover extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'user_nik',
        'status',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_nik', 'nik');
    }
}