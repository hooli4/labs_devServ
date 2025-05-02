<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
{
    use SoftDeletes;

    public $timestamps = false;
    protected $table = 'permissions';

    protected $fillable = [
        'name',
        'description',
        'code',
        'created_at',
        'created_by',
        'deleted_at',
        'deleted_by',
    ];

    public function roles() {
        return $this->belongsToMany(Role::class, 'role_permissions');
    }

    public function logs() {
        return $this->morphMany(ChangeLog::class, 'entity');
    }

}
