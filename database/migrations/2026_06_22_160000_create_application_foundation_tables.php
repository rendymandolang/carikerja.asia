<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidate_profiles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('source_waitlist_id')
                ->nullable()
                ->constrained('waitlists')
                ->nullOnDelete();

            $table->string('full_name');
            $table->string('email')->index();
            $table->string('phone')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('portfolio_url')->nullable();

            $table->string('headline')->nullable();
            $table->string('current_position')->nullable();
            $table->string('location')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('country')->default('Indonesia');

            $table->decimal('expected_salary_min', 12, 2)->nullable();
            $table->decimal('expected_salary_max', 12, 2)->nullable();
            $table->string('currency')->default('IDR');

            $table->enum('availability_status', [
                'immediate',
                'notice_period',
                'open_to_offers',
                'not_looking',
            ])->default('open_to_offers');

            $table->string('resume_path')->nullable();
            $table->longText('summary')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['city', 'province']);
            $table->index('availability_status');
        });

        Schema::create('applications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('candidate_profile_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('job_post_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('company_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('source_waitlist_id')
                ->nullable()
                ->constrained('waitlists')
                ->nullOnDelete();

            $table->enum('status', [
                'submitted',
                'screening',
                'shortlisted',
                'interview',
                'offer',
                'hired',
                'rejected',
                'withdrawn',
            ])->default('submitted');

            $table->string('current_stage')->nullable();

            $table->enum('source', [
                'public_job',
                'waitlist',
                'admin',
                'recruiter',
            ])->default('admin');

            $table->longText('cover_letter')->nullable();
            $table->string('resume_path')->nullable();
            $table->json('answers')->nullable();
            $table->text('admin_notes')->nullable();

            $table->timestamp('applied_at')->nullable();
            $table->timestamp('last_status_changed_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();

            $table->foreignId('reviewed_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['candidate_profile_id', 'job_post_id']);
            $table->index(['status', 'applied_at']);
            $table->index(['company_id', 'status']);
            $table->index(['job_post_id', 'status']);
        });

        Schema::create('application_status_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('application_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->text('notes')->nullable();

            $table->foreignId('changed_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('changed_at');
            $table->timestamps();

            $table->index(['application_id', 'changed_at']);
            $table->index('to_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_status_histories');
        Schema::dropIfExists('applications');
        Schema::dropIfExists('candidate_profiles');
    }
};
