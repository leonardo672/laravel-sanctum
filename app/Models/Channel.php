<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    protected $fillable = ['user_id', 'name', 'description', 'slug'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function podcasts()
    {
        return $this->hasMany(Podcast::class);
    }
}