<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('url'); // Stores the image path (e.g., 'uploads/users/avatar.jpg')
            $table->string('type')->default('image'); // Optional: e.g., 'image', 'video'
            $table->unsignedBigInteger('mediable_id'); // Polymorphic ID (User ID)
            $table->string('mediable_type'); // Polymorphic type (User model)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
