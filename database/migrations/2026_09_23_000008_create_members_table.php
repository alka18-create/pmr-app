<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nis', 20)->nullable();
            $table->string('nisn', 20)->nullable();
            $table->string('full_name', 100);
            $table->string('slug', 120);
            $table->string('gender', 10);
            $table->date('birth_date')->nullable();
            $table->string('birth_place', 100)->nullable();
            $table->text('address')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email_pribadi')->nullable();
            $table->string('parent_name')->nullable();
            $table->string('parent_phone')->nullable();
            $table->string('blood_type', 3)->nullable();
            $table->foreignId('batch_id')->constrained('batches')->restrictOnDelete();
            $table->foreignId('class_id')->constrained('classes')->restrictOnDelete();
            $table->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->string('photo_path')->nullable();
            $table->string('status', 15)->default('aktif');
            $table->date('joined_at');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('slug');
            $table->unique('nis');
            $table->unique('nisn');
            $table->index('class_id');
            $table->index('batch_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
