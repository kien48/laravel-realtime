<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessagePrivate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'conversation_id',
        'content',
    ];
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
