<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('waitlists', function (Blueprint $table) {
            $table->string('admin_status')->default('new')->after('user_agent');
            $table->text('admin_notes')->nullable()->after('admin_status');
            $table->timestamp('followed_up_at')->nullable()->after('admin_notes');
        });
    }

    public function down(): void
    {
        Schema::table('waitlists', function (Blueprint $table) {
            $table->dropColumn([
                'admin_status',
                'admin_notes',
                'followed_up_at',
            ]);
        });
    }
};
