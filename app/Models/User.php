<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\PCR\Initiator;
use App\Models\PCR\PCC;
use App\Models\QAD\Approver;
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

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id'];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
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

    public function approvers()
    {
        return $this->hasMany(Approver::class, 'rqa_apr', 'username');
    }



    /*
    * PCR RELATIONS
    */

    protected $connection = 'mysql';

    public function pccs()
    {
        return $this->hasMany(PCC::class, 'user_id', 'id');
    }

    public function initiators()
    {
        return $this->hasMany(Initiator::class, 'user_id', 'id')->connection('mysql_pcr');
    }

    public function pcc()
    {
        return $this->setConnection('mysql_pcr')->belongsTo(PCC::class, 'user_id', 'id');
    }

    public function getUsernameAttribute($value)
    {
        return strtolower($value);
    }


    // Kanban
    public function tasksDiajukan()
    {
        return $this->hasMany(Task::class, 'pengaju_id');
    }

    public function tasksDitutup()
    {
        return $this->hasMany(Task::class, 'penutup_id');
    }

    public function tasksApproved() // New
    {
        return $this->hasMany(Task::class, 'approver_id');
    }

    public function isAdminProject()
    {
        return $this->level >= 4;
    }

    public function isSuperAdmin()
    {
        return $this->position_id == 1 && $this->username === 'super';
    }

    // New: Check if user can approve tasks for a specific department
    // This is a simple check, you might have more complex role/permission system
    public function canApproveForDepartment(Department $department)
    {
        // Example: User must belong to the department and have a certain level/role
        // For simplicity, let's say any user in that department can approve.
        // Or, perhaps only department heads (e.g., user->is_department_head && user->department_id == $department->id)
        return $this->department_id === $department->id;
    }
}
