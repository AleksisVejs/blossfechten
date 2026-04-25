<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('training_sessions', function (Blueprint $table) {
            $table->id();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('location')->default('Riga');
            $table->string('focus')->nullable();
            $table->json('title')->nullable();
            $table->json('description')->nullable();
            $table->unsignedInteger('capacity')->default(20);
            $table->boolean('members_only')->default(false);
            $table->boolean('cancelled')->default(false);
            $table->timestamps();
            $table->index('starts_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_sessions');
    }
};
