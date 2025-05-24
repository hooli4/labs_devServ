<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogRequest extends Model
{
    public $timestamps = false;
    protected $table = 'LogsRequests';

    protected $fillable = [
        'URL_API_METHOD',
        'METHOD_HTTP_REQUEST',
        'CONTROLLER_PATH',
        'NAME_METHOD_CONTROLLER',
        'BODY_REQUEST',
        'HEADERS_REQUEST',
        'USER_ID',
        'USER_IP_ADDRESS',
        'USER_USER-AGENT',
        'CODE_STATUS_RESPONSE',
        'BODY_RESPONSE',
        'HEADERS_RESPONSE',
        'TIME_CALL',
    ];
}
