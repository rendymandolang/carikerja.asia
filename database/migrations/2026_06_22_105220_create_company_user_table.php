<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_user', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('company_role')->default('recruiter');
            $table->string('job_title')->nullable();
            $table->string('status')->default('active');
            $table->timestamp('invited_at')->nullable();

            $table->timestamps();

            $table->unique(['company_id', 'user_id']);
            $table->index(['company_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_user');
    }
};
