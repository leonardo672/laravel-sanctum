<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePodcastRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'audio' => 'required|file|mimes:mp3,wav',
            'type' => 'required|in:podcast,audiobook',
            'cover_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', // <-- Added
        ];
    }

    /**
     * Custom error messages (optional).
     */
    public function messages(): array
    {
        return [
            'title.required' => 'A title is required for the podcast.',
            'audio.required' => 'An audio file must be uploaded.',
            'audio.mimes' => 'The audio file must be in MP3 or WAV format.',
            'type.in' => 'The type must be either "podcast" or "audiobook".',
            'cover_image.image' => 'The cover must be an image.',
            'cover_image.mimes' => 'The cover image must be a JPG or PNG file.',
            'cover_image.max' => 'The cover image must not exceed 2MB.',
        ];
    }
}
