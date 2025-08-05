<?php

namespace App\Models;

use Spatie\Activitylog\Models\Activity as SpatieActivity;
use Illuminate\Database\Eloquent\Builder;

class MarshoActivityLog extends SpatieActivity
{
    protected $table = 'marsho_activity_logs';

    public function scopeForJobs(Builder $query): Builder
    {
        return $query->where('subject_type', JobMarsho::class);
    }

    public function scopeForSubjectId(Builder $query, int $id): Builder
    {
        return $query->where('subject_id', $id);
    }
}