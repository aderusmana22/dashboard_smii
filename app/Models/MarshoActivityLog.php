<?php

namespace App\Models;

use Spatie\Activitylog\Models\Activity as SpatieActivity;
use Illuminate\Database\Eloquent\Builder;

class MarshoActivityLog extends SpatieActivity
{
    /**
     * Nama tabel yang terhubung dengan model.
     *
     * @var string
     */
    protected $table = 'marsho_activity_logs';

    /**
     * Scope query untuk hanya menyertakan aktivitas yang terkait dengan model JobMarsho.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForJobs(Builder $query): Builder
    {
        return $query->where('subject_type', JobMarsho::class);
    }

    /**
     * Scope query untuk hanya menyertakan aktivitas untuk subject ID tertentu.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForSubjectId(Builder $query, int $id): Builder
    {
        return $query->where('subject_id', $id);
    }
}