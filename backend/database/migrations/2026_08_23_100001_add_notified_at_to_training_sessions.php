<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('training_sessions', function (Blueprint $table) {
            // Set the moment announcement mail goes out, so a session can never
            // be announced twice however many times it is edited afterwards.
            $table->timestamp('notified_at')->nullable()->after('cancelled');
        });
    }

    public function down(): void
    {
        Schema::table('training_sessions', function (Blueprint $table) {
            $table->dropColumn('notified_at');
        });
    }
};
