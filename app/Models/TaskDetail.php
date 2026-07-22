<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'task_details';

    protected $fillable = [
        'code',
        'activity',
        'task_master_id',
        'date_planning_start',
        'date_planning_finish',
        'duration_planning',
        'date_realization_start',
        'date_realization_finish',
        'duration_realization',
        'point_target',
        'point_achievement',
        'description',
        'note',
        'status',
        'unfinished',
        'unscheduled',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'date_planning_start' => 'datetime',
        'date_planning_finish' => 'datetime',
        'date_realization_start' => 'datetime',
        'date_realization_finish' => 'datetime',
        'unscheduled' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function master(): BelongsTo
    {
        return $this->belongsTo(TaskMaster::class, 'task_master_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class, 'task_detail_id');
    }
}
