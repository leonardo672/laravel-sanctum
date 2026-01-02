<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = ['user_id', 'podcast_id', 'body', 'parent_id'];

    /**
     * The user who made the comment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The podcast the comment belongs to.
     */
    public function podcast()
    {
        return $this->belongsTo(Podcast::class);
    }

    /**
     * Parent comment (for nested replies).
     */
    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Replies to this comment (recursive nesting).
     */
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id')->with('replies');
    }
}
