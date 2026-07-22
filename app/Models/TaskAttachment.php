<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskAttachment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'task_attachments';

    protected $fillable = [
        'task_master_id',
        'task_detail_id',
        'name',
        'original_name',
        'path',
        'extension',
        'size',
        'description',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function master(): BelongsTo
    {
        return $this->belongsTo(TaskMaster::class, 'task_master_id');
    }

    public function detail(): BelongsTo
    {
        return $this->belongsTo(TaskDetail::class, 'task_detail_id');
    }
}
