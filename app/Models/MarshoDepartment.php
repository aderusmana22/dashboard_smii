<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarshoDepartment extends Model
{
    use HasFactory;
    protected $fillable = ['department_name'];

    /**
     * Satu departemen Marsho memiliki banyak profil pengguna Marsho.
     */
    public function marshoUsers()
    {
        return $this->hasMany(MarshoUser::class, 'marsho_department_id');
    }
}