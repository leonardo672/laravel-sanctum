<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserMediaController extends Controller
{
    /**
     * Upload or update user's profile picture
     *
     * @param Request $request
     * @param int $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, $userId)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::findOrFail($userId);

            // Store the uploaded file
            $path = $request->file('image')->store('public/users');

            // Remove 'public/' from the path for web access
            $publicPath = str_replace('public/', '', $path);

            // Delete old media if exists
            if ($user->media) {
                Storage::delete('public/' . $user->media->url);
            }

            // Create or update media record
            $media = $user->media()->updateOrCreate(
                ['type' => 'image'],
                ['url' => $publicPath]
            );

            return response()->json([
                'message' => 'Profile picture uploaded successfully',
                'data' => $media,
                'url' => asset('storage/' . $publicPath) // Full accessible URL
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to upload profile picture',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's profile picture
     *
     * @param int $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($userId)
    {
        try {
            $user = User::with('media')->findOrFail($userId);

            if (!$user->media) {
                return response()->json([
                    'message' => 'No profile picture found'
                ], 404);
            }

            return response()->json([
                'data' => $user->media,
                'url' => asset('storage/' . $user->media->url)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve profile picture',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete user's profile picture
     *
     * @param int $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($userId)
    {
        try {
            $user = User::with('media')->findOrFail($userId);

            if (!$user->media) {
                return response()->json([
                    'message' => 'No profile picture found to delete'
                ], 404);
            }

            // Delete file from storage
            Storage::delete('public/' . $user->media->url);

            // Delete media record
            $user->media()->delete();

            return response()->json([
                'message' => 'Profile picture deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete profile picture',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the full URL for a stored media file
     *
     * @param Media $media
     * @return string
     */
    protected function getMediaUrl(Media $media)
    {
        return asset('storage/' . $media->url);
    }
}