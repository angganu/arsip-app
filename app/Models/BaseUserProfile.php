<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BaseUserProfile extends Model
{
    use HasFactory;

    protected $table = 'base_user_profiles';

    protected $fillable = [
        'base_user_id',
        'avatar_path',
        'date_of_birth',
        'phone',
        'address',
        'mst_department_id',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'base_user_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(MstDepartment::class, 'mst_department_id');
    }
}
