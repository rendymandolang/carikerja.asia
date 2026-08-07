<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_interviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('application_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('candidate_profile_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('job_post_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('company_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('scheduled_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('title');
            $table->enum('interview_type', ['video', 'onsite', 'phone', 'other'])->default('video');
            $table->timestamp('scheduled_at');
            $table->unsignedSmallInteger('duration_minutes')->default(60);
            $table->string('timezone')->default('Asia/Jakarta');
            $table->string('meeting_url', 2048)->nullable();
            $table->string('location', 500)->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['scheduled', 'completed', 'cancelled'])->default('scheduled');

            $table->timestamps();

            $table->index(['application_id', 'scheduled_at']);
            $table->index(['candidate_profile_id', 'scheduled_at']);
            $table->index(['company_id', 'scheduled_at']);
            $table->index(['status', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_interviews');
    }
};
