<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MstDepartment extends Model
{
    use HasFactory;

    protected $table = 'mst_departments';

    protected $fillable = [
        'code',
        'name',
    ];

    public function userProfiles(): HasMany
    {
        return $this->hasMany(BaseUserProfile::class, 'mst_department_id');
    }
}
