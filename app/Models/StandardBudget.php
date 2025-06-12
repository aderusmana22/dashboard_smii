<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log; // <-- ADD THIS

class StandardBudget extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand_name',
        'name_region',
        'amount',
        'month',
        'year',
    ];

    // Ensure 'id' is NOT in $fillable
    // Ensure public $incrementing = true; (this is default, so usually not needed to state explicitly)
    // Ensure protected $primaryKey = 'id'; (this is default)
    // Ensure protected $keyType = 'int'; (this is default for integer IDs)

    protected $casts = [
        'amount' => 'decimal:4',
        'month' => 'integer',
        'year' => 'integer',
    ];

    // ADD THIS METHOD FOR LOGGING
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            Log::debug("[MODEL EVENT][CREATING] Attributes: " . json_encode($model->getAttributes()));
            Log::debug("[MODEL EVENT][CREATING] Is ID set? " . (isset($model->attributes['id']) ? $model->attributes['id'] : 'NOT SET'));
        });

        static::created(function ($model) {
            Log::debug("[MODEL EVENT][CREATED] Final ID: " . $model->id . ", Attributes: " . json_encode($model->getAttributes()));
        });

        static::saving(function ($model) {
            Log::debug("[MODEL EVENT][SAVING] Attributes: " . json_encode($model->getAttributes()));
            Log::debug("[MODEL EVENT][SAVING] Is ID set? " . (isset($model->attributes['id']) ? $model->attributes['id'] : 'NOT SET'));
            Log::debug("[MODEL EVENT][SAVING] Model exists in DB? " . ($model->exists ? 'Yes' : 'No'));
            Log::debug("[MODEL EVENT][SAVING] Dirty attributes: " . json_encode($model->getDirty()));
        });

        static::saved(function ($model) {
            Log::debug("[MODEL EVENT][SAVED] Final ID: " . $model->id . ", Attributes: " . json_encode($model->getAttributes()));
        });
    }
}