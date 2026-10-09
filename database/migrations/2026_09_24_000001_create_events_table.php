<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->string('slug', 220);
            $table->string('type', 30);
            $table->text('description')->nullable();
            $table->string('location', 200)->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->string('cover_path')->nullable();
            $table->string('status', 20)->default('draft');
            $table->boolean('is_public')->default(true);
            $table->unsignedInteger('max_participants')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('slug');
            $table->index('starts_at');
            $table->index('status');
            $table->index('is_public');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
