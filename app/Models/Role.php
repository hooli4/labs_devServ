<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use SoftDeletes;

    public $timestamps = false;
    
    protected $fillable = [
        'name',
        'description',
        'code',
        'created_at',
        'created_by',
        'deleted_at',
        'deleted_by',
    ];

    protected $table = 'roles';

    
    public function permissions() {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    public function users() {
        return $this->belongsToMany(User::class, 'user_roles');
    }

    public function logs() {
        return $this->morphMany(ChangeLog::class, 'entity');
    }

}
