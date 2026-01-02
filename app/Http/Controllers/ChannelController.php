<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Channel;

class ChannelController extends Controller
{
    /**
     * Store a new channel for the authenticated user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($request->user()->channel) {
            return response()->json([
                'status' => 'error',
                'message' => 'You already have a channel.'
            ], 400);
        }

        $channel = $request->user()->channel()->create([
            'name' => $request->name,
            'description' => $request->description,
            'slug' => Str::slug($request->name) . '-' . uniqid()
        ]);

        return response()->json([
            'status' => 'success',
            'channel' => $channel
        ], 201);
    }

    /**
     * Show the authenticated user's channel.
     */
    public function show(Request $request)
    {
        $channel = $request->user()->channel;

        if (!$channel) {
            return response()->json([
                'status' => 'error',
                'message' => 'No channel found for this user.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'channel' => $channel
        ], 200);
    }
}
