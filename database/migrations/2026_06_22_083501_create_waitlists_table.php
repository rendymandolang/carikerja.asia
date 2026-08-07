<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waitlists', function (Blueprint $table) {
            $table->id();

            $table->enum('type', ['candidate', 'recruiter']);

            // Candidate fields
            $table->string('full_name')->nullable();
            $table->string('email')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('target_role')->nullable();

            // Recruiter / Company fields
            $table->string('contact_name')->nullable();
            $table->string('company_name')->nullable();
            $table->string('company_email')->nullable();
            $table->string('position')->nullable();

            // Tracking
            $table->text('notes')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            $table->index(['type', 'email']);
            $table->index(['type', 'company_email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waitlists');
    }
};
