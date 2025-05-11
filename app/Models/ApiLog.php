<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    protected $table = 'api_logs';
    protected $fillable = [
        'url',
        'method',
        'status',
        'request_headers',
        'request_body',
        'response_headers',
        'response_body',
        'user_id',
    ];
} 