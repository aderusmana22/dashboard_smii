<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class JobMarsho extends Model
{
    use HasFactory;

    protected $table = 'job_marsho';

    protected $fillable = [
        'id_job', 'pengaju_id', 'area', 'list_job', 'tanggal_job_mulai',
        'tanggal_job_selesai', 'status', 'penutup_id', 'closed_at',
    ];

    protected $casts = [
        'tanggal_job_mulai' => 'date',
        'tanggal_job_selesai' => 'date',
        'closed_at' => 'datetime',
    ];

    public static function generateJobId()
    {
        $year = Carbon::now()->year;
        $prefix = "JOB/{$year}/";
        $lastJob = self::where('id_job', 'like', $prefix . '%')->latest('id')->first();
        $nextNumber = $lastJob ? ((int) substr($lastJob->id_job, -4)) + 1 : 1;
        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function pengaju() { return $this->belongsTo(User::class, 'pengaju_id'); }
    public function penutup() { return $this->belongsTo(User::class, 'penutup_id'); }
    public function routes() { return $this->hasMany(JobRoute::class, 'job_id')->orderBy('created_at'); }
    public function latestRoute() { return $this->hasOne(JobRoute::class, 'job_id')->latestOfMany('created_at'); }
    public function attachments() { return $this->hasMany(JobAttachment::class, 'job_id'); }
    public function notes() { return $this->hasMany(JobNote::class, 'job_id'); }
}