<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('members')->restrictOnDelete();
            $table->unsignedInteger('attempt_no')->default(1);
            $table->dateTime('started_at');
            $table->dateTime('finished_at')->nullable();
            $table->unsignedTinyInteger('score')->nullable();
            $table->string('status', 15)->default('running');
            $table->string('ip', 45)->nullable();
            $table->timestamps();

            $table->unique(['quiz_id', 'member_id', 'attempt_no']);
            $table->index(['quiz_id', 'status']);
            $table->index('member_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};
