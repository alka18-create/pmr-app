<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->string('slug', 220);
            $table->string('category', 30)->default('lainnya');
            $table->text('content')->nullable();
            $table->string('file_path')->nullable();
            $table->string('level', 15)->default('mula');
            $table->unsignedInteger('order')->default(0);
            $table->boolean('is_public')->default(true);
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->unique('slug');
            $table->index('category');
            $table->index('level');
            $table->index('order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
