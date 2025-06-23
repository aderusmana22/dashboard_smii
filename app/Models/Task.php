<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Contracts\Activity;
// use Carbon\Carbon; // Tidak secara eksplisit digunakan di sini, tapi baik untuk ada jika dibutuhkan

class Task extends Model
{
    use HasFactory, LogsActivity;

    const STATUS_PENDING_APPROVAL = 'pending_approval';
    const STATUS_REJECTED = 'rejected';
    const STATUS_OPEN = 'open';
    // const STATUS_ON_PROGRESS = 'on_progress'; // Jika Anda memiliki status ini
    const STATUS_COMPLETED = 'completed';
    const STATUS_CLOSED = 'closed';
    const STATUS_CANCELLED = 'cancelled';

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
        'cancel_reason',
        'requester_confirmation_cancel',
    ];

    protected $casts = [
        'tanggal_job_mulai' => 'date:Y-m-d',
        'tanggal_job_selesai' => 'date:Y-m-d',
        'closed_at' => 'datetime',
        'requester_confirmation_cancel' => 'boolean',
    ];

    // Relasi-relasi
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

    public function approvalDetails()
    {
        return $this->hasMany(JobApprovalDetail::class);
    }

    public function pendingApprovalDetails()
    {
        return $this->approvalDetails()->where('status', JobApprovalDetail::STATUS_PENDING);
    }

    public function processedApprovalDetail()
    {
        return $this->approvalDetails()
            ->whereIn('status', [JobApprovalDetail::STATUS_APPROVED, JobApprovalDetail::STATUS_REJECTED])
            ->orderBy('processed_at', 'desc')
            ->first();
    }

    // Logging Aktivitas
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName(config('activitylog.default_log_name', 'Task'))
            ->setDescriptionForEvent(fn(string $eventName) => "Task {$this->id_job} has been {$eventName}");
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        if ($eventName === 'created' && $this->pengaju && $this->department) {
            $activity->description = "Task {$this->id_job} was created by {$this->pengaju->name} for department {$this->department->department_name}.";
        }
    }

    // Helper untuk token unik
    public static function generateUniqueToken(): string
    {
        do {
            $token = Str::random(40);
        } while (JobApprovalDetail::where('token', $token)->exists());
        return $token;
    }

    // === TAMBAHKAN METHOD INI ===
    /**
     * Get all available task statuses with their human-readable names.
     *
     * @return array
     */
    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_PENDING_APPROVAL => 'Pending Approval',
            self::STATUS_REJECTED => 'Ditolak', // Sesuaikan terjemahan jika perlu
            self::STATUS_OPEN => 'Open',
            // self::STATUS_ON_PROGRESS => 'Sedang Dikerjakan', // Contoh jika ada
            self::STATUS_COMPLETED => 'Selesai',
            self::STATUS_CLOSED => 'Ditutup / Diarsipkan',
            self::STATUS_CANCELLED => 'Dibatalkan',
        ];
    }

    // === DAN ACCESSOR INI (JIKA BELUM ADA) ===
    /**
     * Get the human-readable status text.
     *
     * @return string
     */
    public function getStatusTextAttribute(): string
    {
        return self::getAvailableStatuses()[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }

    /**
     * Get the CSS badge class for the current status.
     *
     * @return string
     */
    public function getStatusBadgeClassAttribute(): string
    {
        $map = [
            self::STATUS_PENDING_APPROVAL => 'bg-pending-approval',
            self::STATUS_REJECTED => 'bg-rejected',
            self::STATUS_OPEN => 'bg-open',
            // self::STATUS_ON_PROGRESS => 'bg-on-progress',
            self::STATUS_COMPLETED => 'bg-completed',
            self::STATUS_CLOSED => 'bg-closed',
            self::STATUS_CANCELLED => 'bg-cancelled',
        ];
        // Pastikan Anda memiliki definisi CSS untuk class-class ini di file CSS Anda
        // atau di <style> tag di Blade Anda.
        // Contoh: .bg-pending-approval { background-color: #ffc107; color: #000; }
        return $map[$this->status] ?? 'bg-secondary'; // Default badge
    }
}