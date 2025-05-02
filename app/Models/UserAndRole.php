<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserAndRole extends Model
{
    Use SoftDeletes;
    public $timestamps = false;
    protected $table = 'user_roles';

    protected $fillable = [
        'created_at',
        'created_by',
        'deleted_at',
        'deleted_by',
    ];
}
