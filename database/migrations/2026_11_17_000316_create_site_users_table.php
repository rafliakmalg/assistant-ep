<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('site_users', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('site_id')->constrained()->restrictOnDelete();
            $table->string('external_id');
            $table->string('name');
            $table->string('role')->nullable();

            $table->unique(['site_id', 'external_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_users');
    }
};
