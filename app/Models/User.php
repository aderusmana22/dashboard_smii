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
}
