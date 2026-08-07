<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('application_interviews', 'google_workspace_id')) {
            Schema::table('application_interviews', function (Blueprint $table) {
                $table->foreignId('google_workspace_id')
                    ->nullable()
                    ->after('scheduled_by_user_id')
                    ->constrained('recruiter_google_workspaces')
                    ->nullOnDelete();
                $table->string('google_calendar_event_id')->nullable()->after('meeting_url');
                $table->string('google_calendar_event_url', 2048)->nullable()->after('google_calendar_event_id');
                $table->string('google_meet_link', 2048)->nullable()->after('google_calendar_event_url');
                $table->string('google_sync_status', 30)->default('manual')->after('google_meet_link');
                $table->text('google_sync_error')->nullable()->after('google_sync_status');
                $table->timestamp('google_synced_at')->nullable()->after('google_sync_error');
            });
        }

        Schema::table('application_interviews', function (Blueprint $table) {
            $table->index(['google_workspace_id', 'google_sync_status'], 'app_int_google_ws_sync_idx');
            $table->index('google_calendar_event_id', 'app_int_google_event_idx');
        });
    }

    public function down(): void
    {
        Schema::table('application_interviews', function (Blueprint $table) {
            $table->dropIndex('app_int_google_ws_sync_idx');
            $table->dropIndex('app_int_google_event_idx');
            $table->dropConstrainedForeignId('google_workspace_id');
            $table->dropColumn([
                'google_calendar_event_id',
                'google_calendar_event_url',
                'google_meet_link',
                'google_sync_status',
                'google_sync_error',
                'google_synced_at',
            ]);
        });
    }
};
