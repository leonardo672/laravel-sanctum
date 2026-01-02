<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Podcast extends Model
{
    protected $fillable = ['channel_id', 'title', 'description', 'audio_path', 'type'];

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    
}
