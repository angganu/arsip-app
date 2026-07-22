<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BaseRole extends Model
{
    use HasFactory;

    protected $table = 'base_roles';

    protected $fillable = [
        'name',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'base_role_users',
            'base_role_id',
            'base_user_id'
        );
    }
}
