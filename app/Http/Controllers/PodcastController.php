<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePodcastRequest;
use App\Models\Podcast;
use App\Models\User;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Log;

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
                return $this->error(
                    'You need to create a channel first', 
                    403
                );
            }

            // Store audio file
            $audioPath = $request->file('audio')->store('podcasts', 'public');

            // Store cover image if provided
            $coverImagePath = null;
            if ($request->hasFile('cover_image')) {
                $coverImagePath = $request->file('cover_image')->store('podcast_covers', 'public');
            }

            // Create podcast
            $podcast = $request->user()->channel->podcasts()->create([
                'title' => $request->validated('title'),
                'description' => $request->validated('description'),
                'audio_path' => $audioPath,
                'cover_image' => $coverImagePath,
                'type' => $request->validated('type'),
            ]);

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
}