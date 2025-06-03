<?php

namespace App\Models;


use App\Models\PCR\Initiator;
use App\Models\PCR\PCC;
use App\Models\QAD\Approver as QADApprover;
use App\Models\QAD\RequisitionMaster;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, CanResetPassword;

    protected $guarded = ['id'];


    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }


    public function rqmMstr()
    {
        return $this->hasMany(RequisitionMaster::class);
    }

    public function qadApprovals()
    {
        return $this->hasMany(QADApprover::class, 'rqa_apr', 'username');
    }



    public function pccs()
    {
        return $this->hasMany(PCC::class, 'user_id', 'id');
    }

    public function initiators()
    {
        return $this->hasMany(Initiator::class, 'user_id', 'id');
    }



    public function getUsernameAttribute($value)
    {
        return strtolower($value);
    }


    public function createdTasks()
    {
        return $this->hasMany(Task::class, 'pengaju_id');
    }

    public function closedTasks()
    {
        return $this->hasMany(Task::class, 'penutup_id');
    }

    public function jobApprovalDetails()
    {
        return $this->hasMany(JobApprovalDetail::class, 'approver_nik', 'nik');
    }

    public function departmentApprovals()
    {
        return $this->hasMany(DepartmentApprover::class, 'user_nik', 'nik');
    }

    public function isSuperAdmin()
    {

        return $this->username === 'superadmin';

    }

    public function isAdminProject()
    {

        return optional($this->position)->name === 'Admin Project';

    }
}