<?php

namespace App\Models;

use Spatie\Activitylog\Models\Activity as SpatieActivity;

class KanbanActivityLog extends SpatieActivity
{
    // Jika Anda menggunakan nama tabel kustom dan BELUM mengkonfigurasinya di config/activitylog.php,
    // Anda bisa menentukannya di sini juga, meskipun konfigurasi di config lebih dianjurkan.
    // protected $table = 'kanban_activity_log';

    /**
     * Scope a query to only include activities for a specific subject type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForSubjectType($query, string $type)
    {
        return $query->where('subject_type', $type);
    }

    /**
     * Scope a query to only include activities for a specific subject ID.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForSubjectId($query, int $id)
    {
        return $query->where('subject_id', $id);
    }

    /**
     * Scope a query to only include activities related to Task model.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForTasks($query)
    {
        return $query->where('subject_type', Task::class); // atau 'App\Models\Task'
    }

    // Anda bisa menambahkan relasi di sini jika diperlukan,
    // misalnya jika Anda ingin causer() mengembalikan instance User Anda
    // Meskipun Activity model dari Spatie sudah memiliki relasi causer dan subject.
}