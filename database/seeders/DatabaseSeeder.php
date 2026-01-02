<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Database\Seeders\PodcastAndCommentSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'Homam@example.com',
        ]);

        $this->call(PodcastAndCommentSeeder::class);
    }
}
