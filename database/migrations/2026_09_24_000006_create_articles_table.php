<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->string('slug', 220);
            $table->string('excerpt', 500)->nullable();
            $table->text('content');
            $table->string('category', 30)->default('berita');
            $table->string('cover_path')->nullable();
            $table->string('meta_title', 200)->nullable();
            $table->string('meta_description', 300)->nullable();
            $table->string('status', 20)->default('draft');
            $table->boolean('is_public')->default(true);
            $table->dateTime('published_at')->nullable();
            $table->foreignId('author_id')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('views')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique('slug');
            $table->index('status');
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
