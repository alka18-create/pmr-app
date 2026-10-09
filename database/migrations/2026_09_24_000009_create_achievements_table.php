<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->string('level', 30);
            $table->string('category', 30);
            $table->unsignedSmallInteger('year');
            $table->text('description')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('winner', 200)->nullable();
            $table->boolean('is_public')->default(true);
            $table->timestamps();

            $table->index('year');
            $table->index('level');
            $table->index('is_public');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('achievements');
    }
};
