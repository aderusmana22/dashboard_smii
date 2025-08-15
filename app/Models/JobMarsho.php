<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Collection;

class JobMarsho extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'job_marsho';
    protected $guarded = [];
    protected $casts = [
        'tanggal_job_mulai' => 'datetime',
        'tanggal_job_selesai' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Job')
            ->setDescriptionForEvent(fn(string $eventName) => "Job '{$this->id_job}' has been {$eventName}");
    }

    public static function generateJobId()
    {
        $date = Carbon::now();
        $year = $date->format('y');
        $month = $date->format('m');
        $day = $date->format('d');

        $latestJob = self::whereYear('created_at', $date->year)
                         ->whereMonth('created_at', $date->month)
                         ->whereDay('created_at', $date->day)
                         ->latest('id')
                         ->first();

        $sequence = $latestJob ? (int)substr($latestJob->id_job, -4) + 1 : 1;
        
        return sprintf('JOB-%s%s%s-%04d', $year, $month, $day, $sequence);
    }

    // Relationships
    public function pengaju() { return $this->belongsTo(User::class, 'pengaju_id'); }
    public function penutup() { return $this->belongsTo(User::class, 'penutup_id'); }
    public function area() { return $this->belongsTo(Area::class, 'area_id'); }
    public function routes() { return $this->hasMany(JobRoute::class, 'job_id')->orderBy('created_at'); }
    public function latestRoute() { return $this->hasOne(JobRoute::class, 'job_id')->latestOfMany(); }
    public function attachments() { return $this->hasMany(JobAttachment::class, 'job_id'); }
    public function notes() { return $this->hasMany(JobNote::class, 'job_id'); }

    // Accessors
    public function getInitialAttachmentsAttribute(): Collection
    {
        if (!$this->relationLoaded('attachments')) $this->load('attachments');
        return $this->attachments->filter(fn ($attachment) => str_contains($attachment->file_path, 'job_attachments/open/'));
    }

    public function getClosingAttachmentsAttribute(): Collection
    {
        if (!$this->relationLoaded('attachments')) $this->load('attachments');
        return $this->attachments->filter(fn ($attachment) => str_contains($attachment->file_path, 'job_attachments/closed/'));
    }
}