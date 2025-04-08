<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoleAndPermission extends Model
{
    use SoftDeletes;
    protected $table = 'role_permissions';

    protected $fillable = [
        'created_at',
        'created_by',
        'deleted_at',
        'deleted_by',
    ];
}
