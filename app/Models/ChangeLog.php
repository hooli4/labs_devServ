<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChangeLog extends Model
{
    protected $table = 'changelogs';

    protected $fillable = [
        'field',
        'new_value',
        'old_value',
        'created_at',
        'created_by',
    ];

    public $timestamps = false;

    public function entity() {
        return $this->morphTo();
    }
}
