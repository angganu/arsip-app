<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskMaster extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'task_masters';

    protected $fillable = [
        'task_category_id',
        'code',
        'name',
        'description',
        'has_schedule',
        'interval_schedule',
        'interval_value',
        'planned_by',
        'date_planning_start',
        'date_planning_finish',
        'duration_planning',
        'date_realization_start',
        'date_realization_finish',
        'duration_realization',
        'priority',
        'status',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'has_schedule' => 'boolean',
        'is_active' => 'boolean',
        'date_planning_start' => 'date',
        'date_planning_finish' => 'date',
        'date_realization_start' => 'date',
        'date_realization_finish' => 'date',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(TaskCategory::class, 'task_category_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(TaskDetail::class, 'task_master_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class, 'task_master_id');
    }
}
