<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePodcastRequest;
use App\Models\Podcast;
use App\Models\User;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class PodcastController extends Controller
{
    use HttpResponses;

    /**
     * Get all podcasts
     */
    public function index()
    {
        try {
            $podcasts = Podcast::with(['channel.user'])
                ->withCount('likedBy')
                ->latest()
                ->get();

            return $this->success($podcasts);

        } catch (\Exception $e) {
            Log::error("Podcast index error: {$e->getMessage()}");
            return $this->error('Failed to retrieve podcasts', 500);
        }
    }

    /**
     * Store a new podcast
     */
    public function store(StorePodcastRequest $request)
    {
        try {
            if (!$request->user()->channel) {
                return $this->error('You need to create a channel first', 403);
            }

            $audioPath = $request->file('audio')->store('podcasts', 'public');

            $coverImagePath = null;
            if ($request->hasFile('cover_image')) {
                $coverImagePath = $request->file('cover_image')->store('podcast_covers', 'public');
            }

            $podcast = $request->user()->channel->podcasts()->create([
                'title' => $request->validated('title'),
                'description' => $request->validated('description'),
                'audio_path' => $audioPath,
                'cover_image' => $coverImagePath,
                'type' => $request->validated('type'),
            ]);

            // Sync categories
            $podcast->categories()->sync($request->validated('category_ids'));

            return $this->created($podcast);

        } catch (\Exception $e) {
            Log::error("Podcast store error: {$e->getMessage()}");
            return $this->error('Failed to create podcast', 500);
        }
    }


    /**
     * Get user's podcasts
     */
    public function userPodcasts(int $userId)
    {
        try {
            $user = User::with('channel.podcasts')->find($userId);

            return $user?->channel 
                ? $this->success($user->channel->podcasts)
                : $this->notFound('User or channel not found');

        } catch (\Exception $e) {
            Log::error("User podcasts error [{$userId}]: {$e->getMessage()}");
            return $this->error('Failed to retrieve user podcasts', 500);
        }
    }

    /**
     * Show a single podcast with top-level comments and their nested replies
     */
    public function showWithComments(int $id)
    {
        try {
            $podcast = Podcast::with([
                'comments' => function ($query) {
                    $query->whereNull('parent_id')
                        ->with(['replies.user', 'user']);
                }
            ])->findOrFail($id);

            return $this->success($podcast);

        } catch (\Exception $e) {
            Log::error("Podcast showWithComments error [{$id}]: {$e->getMessage()}");
            return $this->error('Failed to retrieve podcast with comments', 500);
        }
    }


    public function toggleLike(Request $request, int $podcastId)
    {
        $user = $request->user();
        $podcast = Podcast::findOrFail($podcastId);

        if ($podcast->likedBy()->where('user_id', $user->id)->exists()) {
            $podcast->likedBy()->detach($user->id);
            return $this->success(['message' => 'Podcast unliked.']);
        } else {
            $podcast->likedBy()->attach($user->id);
            return $this->success(['message' => 'Podcast liked.']);
        }
    }


    public function random(Request $request)
    {
        try {
            // Determine pagination size (default to 10)
            $perPage = $request->query('per_page', 10);

            // Fetch paginated random podcasts
            $podcasts = Podcast::with(['channel.user'])
                ->withCount('likedBy')
                ->inRandomOrder()
                ->paginate($perPage);

            return $this->success($podcasts);

        } catch (\Exception $e) {
            Log::error("Random podcast fetch error: {$e->getMessage()}");
            return $this->error('Failed to retrieve random podcasts', 500);
        }
    }



}