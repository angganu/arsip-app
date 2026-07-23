<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskDiscussion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'task_discussions';

    protected $fillable = [
        'task_master_id',
        'base_user_id',
        'message',
        'is_read',
    ];

    public function taskMaster(): BelongsTo
    {
        return $this->belongsTo(TaskMaster::class, 'task_master_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'base_user_id');
    }
}
