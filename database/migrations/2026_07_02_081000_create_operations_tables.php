<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_heartbeats', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('status')->default('ok')->index();
            $table->timestamp('last_ping_at')->nullable()->index();
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        Schema::create('backup_runs', function (Blueprint $table) {
            $table->id();
            $table->string('type')->index();
            $table->string('status')->default('running')->index();
            $table->string('disk')->default('local');
            $table->text('path')->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->text('error_message')->nullable();
            $table->foreignId('triggered_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_runs');
        Schema::dropIfExists('system_heartbeats');
    }
};
