<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->foreignId('member_id')->constrained('members')->restrictOnDelete();
            $table->string('source_type', 20)->default('quiz');
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('title', 200);
            $table->date('issued_at');
            $table->string('file_path')->nullable();
            $table->boolean('is_revoked')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['source_type', 'source_id']);
            $table->index('member_id');
            $table->index('is_revoked');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
