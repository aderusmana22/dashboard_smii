<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Task extends Model
{
    use HasFactory;

    public const STATUS_PENDING_APPROVAL = 'pending_approval';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_OPEN = 'open';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_CANCELLED = 'cancelled';


    protected $fillable = [
        'id_job',
        'pengaju_id',
        'department_id',
        'area',
        'list_job',
        'tanggal_job_mulai',
        'tanggal_job_selesai',
        'status',
        'penutup_id',
        'closed_at',
        'approver_id',
        'approved_at',
        'rejection_reason',
        'approval_token',
    ];

    protected $casts = [
        'tanggal_job_mulai' => 'date:Y-m-d',
        'tanggal_job_selesai' => 'date:Y-m-d',
        'closed_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function pengaju()
    {
        return $this->belongsTo(User::class, 'pengaju_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function penutup()
    {
        return $this->belongsTo(User::class, 'penutup_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($task) {
            if (empty($task->approval_token)) {
                $task->approval_token = Str::random(40);
            }
            if (empty($task->status)) {
                $task->status = self::STATUS_PENDING_APPROVAL;
            }
        });
    }
}