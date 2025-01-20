<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('external_reference');
            $table->string('category');
            $table->json('source');
            $table->string('news_url');
            $table->text('image_url')->nullable();
            $table->string('published_date');
            $table->text('title');
            $table->text('description')->nullable();
            $table->text('content')->nullable();
            $table->text('author')->nullable();
            $table->enum('provider', ['news_api', 'guardian_api', 'new_york_times']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
