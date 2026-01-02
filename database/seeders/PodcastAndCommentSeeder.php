<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Channel;
use App\Models\Podcast;
use App\Models\Comment;

class PodcastAndCommentSeeder extends Seeder
{
    public function run(): void
    {
        // Create 10 users and assign each one a channel
        User::factory(10)->create()->each(function ($user) {
            $user->channel()->create([
                'name' => fake()->company,
                'description' => fake()->catchPhrase,
            ]);
        });

        // Get all created channels
        $channels = Channel::all();

        // Create 10 podcasts, each belonging to a random channel
        Podcast::factory(10)->create()->each(function ($podcast) use ($channels) {
            $podcast->channel_id = $channels->random()->id;
            $podcast->save();

            // Attach 2–8 comments to the podcast
            Comment::factory(rand(2, 8))->create([
                'podcast_id' => $podcast->id,
                'user_id' => User::inRandomOrder()->first()->id,
            ]);
        });
    }
}
