<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskMasterArchive extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'task_master_archives';

    protected $fillable = [
        'task_master_id',
        'name',
        'path',
        'extension',
        'size',
        'description',
        'generated_by',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function taskMaster(): BelongsTo
    {
        return $this->belongsTo(TaskMaster::class, 'task_master_id');
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
