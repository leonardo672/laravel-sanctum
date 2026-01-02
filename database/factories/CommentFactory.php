<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'podcast_id' => \App\Models\Podcast::factory(), // Overridden in seeder
            'user_id' => \App\Models\User::factory(),       // Or use seeded users
            'body' => $this->faker->sentence,
            'parent_id' => null, // For top-level comments
        ];
    }

}
