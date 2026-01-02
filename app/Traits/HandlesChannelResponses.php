<?php

namespace App\Traits;

trait HandlesChannelResponses
{
    public function channelExistsResponse()
    {
        return response()->json([
            'status' => 'error',
            'message' => 'You already have a channel.'
        ], 400);
    }

    public function channelNotFoundResponse()
    {
        return response()->json([
            'status' => 'error',
            'message' => 'No channel found for this user.'
        ], 404);
    }

    public function channelCreatedResponse($channel)
    {
        return response()->json([
            'status' => 'success',
            'channel' => $channel,
        ], 201);
    }

    public function channelSuccessResponse($channel)
    {
        return response()->json([
            'status' => 'success',
            'channel' => $channel,
        ], 200);
    }
}
