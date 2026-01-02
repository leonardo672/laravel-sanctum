<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Media extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'url',
        'type',
        'mediable_id',
        'mediable_type',
    ];

    /**
     * Get the parent model (User, Post, etc.).
     */
    public function mediable()
    {
        return $this->morphTo();
    }
}