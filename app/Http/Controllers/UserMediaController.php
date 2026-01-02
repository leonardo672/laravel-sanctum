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
     * Upload or update user's profile picture.
     */
    public function store(Request $request, $userId)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $file = $request->file('image');

        // Validate actual MIME type using finfo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file->getRealPath());
        finfo_close($finfo);

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];

        if (!in_array($mimeType, $allowedMimeTypes)) {
            return response()->json([
                'message' => 'Invalid image content type.',
            ], 400);
        }

        $user = User::findOrFail($userId);

        // Generate unique filename
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('public/users', $filename);
        $publicPath = str_replace('public/', '', $path);

        // Delete previous image
        if ($user->media) {
            Storage::delete('public/' . $user->media->url);
        }

        // Save or update media record
        $media = $user->media()->updateOrCreate(
            ['type' => 'image'],
            ['url' => $publicPath]
        );

        return response()->json([
            'message' => 'Profile picture uploaded successfully',
            'data' => $media,
            'url' => $this->getMediaUrl($media),
        ], 201);
    }

    /**
     * Get user's profile picture.
     */
    public function show($userId)
    {
        $user = User::with('media')->findOrFail($userId);

        if (!$user->media) {
            return response()->json([
                'message' => 'No profile picture found',
            ], 404);
        }

        return response()->json([
            'data' => $user->media,
            'url' => $this->getMediaUrl($user->media),
        ]);
    }

    /**
     * Delete user's profile picture.
     */
    public function destroy($userId)
    {
        $user = User::with('media')->findOrFail($userId);

        if (!$user->media) {
            return response()->json([
                'message' => 'No profile picture found to delete',
            ], 404);
        }

        Storage::delete('public/' . $user->media->url);
        $user->media()->delete();

        return response()->json([
            'message' => 'Profile picture deleted successfully',
        ]);
    }

    /**
     * Get full URL of media.
     */
    protected function getMediaUrl(Media $media): string
    {
        return asset('storage/' . $media->url);
    }
}
