<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('notify_new_events')->default(true)->after('locale');
            $table->string('unsubscribe_token', 64)->nullable()->unique()->after('notify_new_events');
        });

        // Backfill tokens so existing members get a working unsubscribe link
        // on the very first announcement they receive.
        DB::table('users')->whereNull('unsubscribe_token')->orderBy('id')
            ->chunkById(200, function ($users) {
                foreach ($users as $user) {
                    DB::table('users')->where('id', $user->id)
                        ->update(['unsubscribe_token' => Str::random(64)]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['notify_new_events', 'unsubscribe_token']);
        });
    }
};
