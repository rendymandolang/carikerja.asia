<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->timestamp('response_due_at')->nullable()->index();
            $table->timestamp('first_responded_at')->nullable()->index();
            $table->timestamp('pre_due_reminder_sent_at')->nullable();
            $table->timestamp('overdue_reminder_sent_at')->nullable();
            $table->timestamp('finalized_at')->nullable()->index();
            $table->string('resolution', 40)->nullable()->index();
            $table->text('final_reason')->nullable();
        });

        Schema::table('job_posts', function (Blueprint $table) {
            $table->timestamp('last_confirmed_at')->nullable();
            $table->timestamp('confirmation_due_at')->nullable()->index();
            $table->timestamp('auto_paused_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->string('closure_type', 40)->nullable()->index();
            $table->text('closed_reason')->nullable();
            $table->unsignedInteger('report_count')->default(0);
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->boolean('is_verified')->default(false)->index();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('last_recruiter_activity_at')->nullable();
            $table->decimal('response_rate', 5, 2)->nullable();
            $table->decimal('median_response_hours', 10, 2)->nullable();
            $table->unsignedInteger('response_sample_size')->default(0);
        });

        Schema::create('job_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_post_id')->constrained()->cascadeOnDelete();
            $table->string('reason', 40);
            $table->text('details')->nullable();
            $table->string('reporter_email')->nullable();
            $table->string('ip_hash', 64)->nullable()->index();
            $table->string('status', 30)->default('new')->index();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
            $table->index(['job_post_id', 'status']);
        });

        $responseHours = (int) config('hiring.first_response_hours', 72);
        DB::table('applications')->whereNull('response_due_at')->orderBy('id')->eachById(function ($application) use ($responseHours) {
            $base = $application->applied_at ?: $application->created_at;
            $updates = [
                'response_due_at' => Carbon::parse($base)->addHours($responseHours),
            ];
            if ($application->status !== 'submitted') {
                $updates['first_responded_at'] = $application->reviewed_at ?: $application->last_status_changed_at ?: $application->updated_at;
            }
            if (in_array($application->status, ['hired', 'rejected', 'withdrawn'], true)) {
                $updates['resolution'] = $application->status;
                $updates['finalized_at'] = $application->last_status_changed_at ?: $application->updated_at;
                $updates['final_reason'] = 'Status akhir dimigrasikan dari proses rekrutmen sebelumnya.';
            }
            DB::table('applications')->where('id', $application->id)->update($updates);
        });

        $confirmationDays = (int) config('hiring.job_confirmation_days', 30);
        DB::table('job_posts')->where('status', 'published')->orderBy('id')->eachById(function ($job) use ($confirmationDays) {
            $base = now();
            DB::table('job_posts')->where('id', $job->id)->update([
                'last_confirmed_at' => $base,
                'confirmation_due_at' => Carbon::parse($base)->addDays($confirmationDays),
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_reports');
        Schema::table('companies', function (Blueprint $table) {
            $table->dropConstrainedForeignId('verified_by_user_id');
            $table->dropColumn(['is_verified', 'verified_at', 'last_recruiter_activity_at', 'response_rate', 'median_response_hours', 'response_sample_size']);
        });
        Schema::table('job_posts', function (Blueprint $table) {
            $table->dropColumn(['last_confirmed_at', 'confirmation_due_at', 'auto_paused_at', 'closed_at', 'closure_type', 'closed_reason', 'report_count']);
        });
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['response_due_at', 'first_responded_at', 'pre_due_reminder_sent_at', 'overdue_reminder_sent_at', 'finalized_at', 'resolution', 'final_reason']);
        });
    }
};
