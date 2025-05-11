<?php

// app/Models/Conversation.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conversation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'model', 'status', 'message', 'response', 'conversation_id', 'role', 'content', 'created_at', 'updated_at', 'deleted_at'
    ];

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}