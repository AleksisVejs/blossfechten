<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('training_sessions', function (Blueprint $table) {
            $table->boolean('send_reminder')->default(true)->after('notified_at');
            $table->timestamp('reminded_at')->nullable()->after('send_reminder');
        });

        // Sessions already inside the reminder window would otherwise all fire
        // the moment this deploys. Mark them as done so nobody gets a surprise
        // reminder for something starting in an hour.
        DB::table('training_sessions')
            ->whereNull('reminded_at')
            ->where('starts_at', '<=', now()->addDay())
            ->update(['reminded_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('training_sessions', function (Blueprint $table) {
            $table->dropColumn(['send_reminder', 'reminded_at']);
        });
    }
};
