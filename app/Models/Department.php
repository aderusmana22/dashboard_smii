<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Department extends Model
{
    use HasFactory;
    protected $connection = 'mysql';
    protected $guarded = ['id'];

    public function users()
    {
        return $this->hasMany(User::class);
    }



    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function departmentApprovers()
    {
        return $this->hasMany(DepartmentApprover::class);
    }

    public function getActiveApproverUsers()
    {
        return User::whereIn(
            'nik',
            $this->departmentApprovers()->where('status', 'active')->pluck('user_nik')->toArray()
        )->get();
    }

    public static function boot()
    {
        parent::boot();

        self::creating(function ($department) {
            if (empty($department->department_slug)) {
                $department->department_slug = Str::slug($department->department_name);
            }
        });

        self::updating(function ($department) {
            if ($department->isDirty('department_name')) {
                $department->department_slug = Str::slug($department->department_name);
            }
        });
    }
}