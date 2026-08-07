<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_posts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('created_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('title');
            $table->string('slug')->unique();

            $table->string('department')->nullable();
            $table->string('location')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('country')->default('Indonesia');

            $table->enum('employment_type', [
                'full_time',
                'part_time',
                'contract',
                'internship',
                'freelance',
            ])->default('full_time');

            $table->enum('work_arrangement', [
                'onsite',
                'hybrid',
                'remote',
            ])->default('onsite');

            $table->decimal('salary_min', 12, 2)->nullable();
            $table->decimal('salary_max', 12, 2)->nullable();
            $table->string('currency')->default('IDR');

            $table->longText('description');
            $table->longText('requirements')->nullable();
            $table->longText('benefits')->nullable();

            $table->date('application_deadline')->nullable();

            $table->enum('status', [
                'draft',
                'published',
                'closed',
                'archived',
            ])->default('draft');

            $table->timestamp('published_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index(['city', 'province']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_posts');
    }
};
