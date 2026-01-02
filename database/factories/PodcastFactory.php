<?php

namespace Database\Factories;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Podcast>
 */
class PodcastFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    public function definition()
    {
        return [
            'channel_id' => \App\Models\Channel::factory(), // Assumes Channel factory exists
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'audio' => 'audio.mp3', // or use fake storage
            'cover_image' => 'cover.jpg',
            'type' => 'public', // or random from set
        ];
    }

}
