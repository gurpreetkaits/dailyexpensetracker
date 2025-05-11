<?php

// app/Models/Message.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'conversation_id', 'sender', 'content', 'role', 'response'
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}