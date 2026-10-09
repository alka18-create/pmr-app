<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('level', 10);
            $table->integer('year');
            $table->timestamps();

            $table->unique('name');
            $table->index('level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
