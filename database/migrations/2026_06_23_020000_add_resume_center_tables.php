<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidate_profiles', function (Blueprint $table) {
            $table->string('indeed_url')->nullable()->after('linkedin_url');
            $table->string('desired_job_title')->nullable()->after('current_position');
            $table->string('desired_employment_type')->nullable()->after('availability_status');
            $table->string('desired_work_arrangement')->nullable()->after('desired_employment_type');
        });

        Schema::create('candidate_work_experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_profile_id')->constrained()->cascadeOnDelete();
            $table->string('job_title');
            $table->string('company_name');
            $table->string('employment_type')->nullable();
            $table->string('location')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->longText('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['candidate_profile_id', 'sort_order']);
        });

        Schema::create('candidate_educations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_profile_id')->constrained()->cascadeOnDelete();
            $table->string('school_name');
            $table->string('degree')->nullable();
            $table->string('field_of_study')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->longText('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['candidate_profile_id', 'sort_order']);
        });

        Schema::create('candidate_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_profile_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('proficiency')->nullable();
            $table->unsignedTinyInteger('years_experience')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['candidate_profile_id', 'name']);
            $table->index(['candidate_profile_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_skills');
        Schema::dropIfExists('candidate_educations');
        Schema::dropIfExists('candidate_work_experiences');

        Schema::table('candidate_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'indeed_url',
                'desired_job_title',
                'desired_employment_type',
                'desired_work_arrangement',
            ]);
        });
    }
};
