<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('candidate_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('sender_role', 30);
            $table->text('body');
            $table->timestamp('read_by_candidate_at')->nullable();
            $table->timestamp('read_by_recruiter_at')->nullable();
            $table->timestamps();

            $table->index(['application_id', 'created_at']);
            $table->index(['candidate_profile_id', 'created_at']);
            $table->index(['company_id', 'created_at']);
            $table->index('sender_role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_messages');
    }
};
