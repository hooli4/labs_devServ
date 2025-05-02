<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TwoFactorAuth extends Model
{
    protected $table = '2fa';
    protected $fillable = [
        'user_id',
        'browser',
        'ip',
        'platform',
        'token',
        'code',
        'request_count',
        'expires_at',
    ];

    protected function casts() {
        return [
            'token' => 'hashed',
        ];
    }
}
