<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gallery_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('gallery_categories')->nullOnDelete();
            $table->string('title', 200);
            $table->string('description', 500)->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_type', 20)->default('image');
            $table->string('video_url', 500)->nullable();
            $table->foreignId('event_id')->nullable()->constrained('events')->nullOnDelete();
            $table->boolean('is_public')->default(true);
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->date('taken_at')->nullable();
            $table->timestamps();

            $table->index('is_public');
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_items');
    }
};
