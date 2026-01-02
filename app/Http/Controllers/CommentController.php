<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\Comment;
use App\Models\Podcast;
use App\Traits\HttpResponses;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class CommentController extends Controller
{
    use HttpResponses, AuthorizesRequests;

    /**
     * Get all top-level comments for a given podcast with nested replies
     */
    public function index(int $podcastId)
    {
        $podcast = Podcast::findOrFail($podcastId);

        $comments = $podcast->comments()
            ->whereNull('parent_id') // only top-level comments
            ->with(['replies.user', 'user'])
            ->latest()
            ->get();

        return $this->success($comments);
    }

    /**
     * Store a new comment or reply
     */
    public function store(StoreCommentRequest $request, int $podcastId)
    {
        $podcast = Podcast::findOrFail($podcastId);

        $comment = $podcast->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $request->body,
            'parent_id' => $request->parent_id,
        ]);

        return $this->created($comment->load('user', 'replies.user'));
    }

    /**
     * Delete a comment if authorized (owner only)
     */
    public function destroy(int $commentId)
    {
        $comment = Comment::findOrFail($commentId);

        $this->authorize('delete', $comment);

        $comment->delete();

        return $this->success(['message' => 'Comment deleted successfully']);
    }
}
