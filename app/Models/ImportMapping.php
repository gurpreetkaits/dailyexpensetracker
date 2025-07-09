<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'mappings'
    ];

    protected $casts = [
        'mappings' => 'json'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 