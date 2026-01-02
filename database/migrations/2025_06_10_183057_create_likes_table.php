<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLikesTable extends Migration
{
    public function up(): void
    {
        Schema::create('likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('podcast_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['user_id', 'podcast_id']); // Prevent duplicate likes
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('likes');
    }
}
