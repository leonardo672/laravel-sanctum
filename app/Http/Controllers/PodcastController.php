<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Podcast;
use App\Models\User;

class PodcastController extends Controller
{
    /**
     * Store a new podcast or audiobook.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'audio' => 'required|file|mimes:mp3,wav',
            'type' => 'required|in:podcast,audiobook',
        ]);

        $channel = $request->user()->channel;

        if (!$channel) {
            return response()->json([
                'status' => 'error',
                'message' => 'You need to create a channel first.'
            ], 403);
        }

        $path = $request->file('audio')->store('podcasts', 'public');

        $podcast = $channel->podcasts()->create([
            'title' => $request->title,
            'description' => $request->description,
            'audio_path' => $path,
            'type' => $request->type,
        ]);

        return response()->json([
            'status' => 'success',
            'podcast' => $podcast,
        ], 201);
    }

    /**
     * Get all podcasts.
     */
    public function index()
    {
        $podcasts = Podcast::with('channel.user')->latest()->get();

        return response()->json([
            'status' => 'success',
            'podcasts' => $podcasts,
        ], 200);
    }

    /**
     * Get podcasts by user ID.
     */
    public function userPodcasts($userId)
    {
        $user = User::find($userId);

        if (!$user || !$user->channel) {
            return response()->json([
                'status' => 'error',
                'message' => 'User or channel not found.'
            ], 404);
        }

        $podcasts = $user->channel->podcasts;

        return response()->json([
            'status' => 'success',
            'podcasts' => $podcasts
        ], 200);
    }
}
