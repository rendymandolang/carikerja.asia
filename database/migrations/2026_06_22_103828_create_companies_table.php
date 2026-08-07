<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            $table->foreignId('source_waitlist_id')
                ->nullable()
                ->constrained('waitlists')
                ->nullOnDelete();

            $table->string('company_name');
            $table->string('legal_name')->nullable();
            $table->string('slug')->unique();

            $table->string('industry')->nullable();
            $table->string('website')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();

            $table->enum('status', [
                'pending',
                'active',
                'suspended',
                'rejected',
            ])->default('pending');

            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'city']);
            $table->index('company_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
