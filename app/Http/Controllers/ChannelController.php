<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreChannelRequest;
use App\Traits\HandlesChannelResponses;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ChannelController extends Controller
{
    use HandlesChannelResponses;

    /**
     * Store a new channel for the authenticated user.
     *
     * @param  \App\Http\Requests\StoreChannelRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function store(StoreChannelRequest $request)
    {
        $user = $request->user();

        if ($user->channel) {
            return $this->channelExistsResponse();
        }

        $channel = $user->channel()->create([
            'name' => $request->name,
            'description' => $request->description,
            'slug' => Str::slug($request->name) . '-' . uniqid(),
        ]);

        return $this->channelCreatedResponse($channel);
    }

    /**
     * Show the authenticated user's channel.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function show()
    {
      
        $channel = Auth::user()->channel;

        if (!$channel) {
            return $this->channelNotFoundResponse();
        }

        return $this->channelSuccessResponse($channel);
    }
}
