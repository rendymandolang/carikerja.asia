<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('category')->default('system')->index();
            $table->string('subject');
            $table->string('preheader')->nullable();
            $table->longText('body');
            $table->string('button_label')->nullable();
            $table->string('button_url')->nullable();
            $table->json('variables')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('marketing_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('audience')->default('all_contacts')->index();
            $table->string('subject');
            $table->string('preheader')->nullable();
            $table->longText('body');
            $table->string('button_label')->nullable();
            $table->string('button_url')->nullable();
            $table->string('status')->default('draft')->index();
            $table->unsignedInteger('recipient_count')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('skipped_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('sent_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        Schema::create('marketing_email_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marketing_campaign_id')->constrained('marketing_campaigns')->cascadeOnDelete();
            $table->string('email');
            $table->string('name')->nullable();
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('status')->default('queued')->index();
            $table->text('failure_reason')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->unique(['marketing_campaign_id', 'email'], 'marketing_campaign_recipient_unique');
            $table->index(['source_type', 'source_id']);
        });

        Schema::create('email_unsubscribes', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('token')->unique();
            $table->timestamp('unsubscribed_at')->nullable()->index();
            $table->string('source')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();
        });

        DB::table('email_templates')->insert($this->defaultTemplates());
    }

    public function down(): void
    {
        Schema::dropIfExists('email_unsubscribes');
        Schema::dropIfExists('marketing_email_recipients');
        Schema::dropIfExists('marketing_campaigns');
        Schema::dropIfExists('email_templates');
    }

    private function defaultTemplates(): array
    {
        $now = now();

        return [
            [
                'key' => 'portal_password_reset',
                'name' => 'Portal password reset',
                'category' => 'system',
                'subject' => 'Reset password {{ portal_label }} carikerja.asia',
                'preheader' => 'Gunakan link ini untuk membuat password baru.',
                'body' => "Kami menerima permintaan reset password untuk akun {{ portal_label }} Anda.\n\nLink ini berlaku selama 60 menit.\n\nJika Anda tidak meminta reset password, abaikan email ini.",
                'button_label' => 'Reset Password',
                'button_url' => '{{ reset_url }}',
                'variables' => json_encode(['name', 'portal_label', 'reset_url']),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'application_submitted',
                'name' => 'Candidate application submitted',
                'category' => 'system',
                'subject' => 'Lamaran terkirim: {{ job_title }}',
                'preheader' => 'Lamaran Anda sudah tercatat di carikerja.asia.',
                'body' => "Lamaran Anda untuk {{ job_title }} di {{ company_name }} sudah berhasil dikirim.\n\nAnda bisa memantau status terbaru melalui Candidate Portal.\n\nTerima kasih sudah menggunakan carikerja.asia.",
                'button_label' => 'Lihat Lamaran',
                'button_url' => '{{ action_url }}',
                'variables' => json_encode(['name', 'job_title', 'company_name', 'action_url']),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'application_status_updated',
                'name' => 'Candidate application status updated',
                'category' => 'system',
                'subject' => 'Status lamaran diperbarui: {{ job_title }}',
                'preheader' => 'Ada update status untuk lamaran Anda.',
                'body' => "Status lamaran Anda untuk {{ job_title }} sekarang: {{ status_label }}.\n\n{{ status_note_line }}\nPantau proses rekrutmen Anda melalui Candidate Portal.",
                'button_label' => 'Lihat Detail Lamaran',
                'button_url' => '{{ action_url }}',
                'variables' => json_encode(['name', 'job_title', 'status_label', 'status_note_line', 'action_url']),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'interview_scheduled_candidate',
                'name' => 'Candidate interview scheduled',
                'category' => 'system',
                'subject' => 'Jadwal interview: {{ job_title }}',
                'preheader' => 'Interview Anda sudah dijadwalkan.',
                'body' => "Interview Anda untuk {{ job_title }} di {{ company_name }} sudah dijadwalkan.\n\nWaktu: {{ scheduled_at_label }}\nTipe: {{ interview_type_label }}\nDurasi: {{ duration_label }}\n{{ meeting_url_line }}\n{{ location_line }}\n{{ notes_line }}\nSilakan cek Candidate Portal untuk detail terbaru.",
                'button_label' => 'Lihat Detail Lamaran',
                'button_url' => '{{ action_url }}',
                'variables' => json_encode(['name', 'job_title', 'company_name', 'scheduled_at_label', 'interview_type_label', 'duration_label', 'meeting_url_line', 'location_line', 'notes_line', 'action_url']),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'application_message_received_candidate',
                'name' => 'Candidate message received',
                'category' => 'system',
                'subject' => 'Pesan baru: {{ job_title }}',
                'preheader' => 'Ada pesan baru terkait lamaran Anda.',
                'body' => "{{ sender_name }} mengirim pesan baru terkait lamaran {{ job_title }} di {{ company_name }}.\n\nPesan: {{ message_excerpt }}\n\nBalas langsung dari Candidate Portal agar komunikasi tetap tercatat.",
                'button_label' => 'Buka Detail Lamaran',
                'button_url' => '{{ action_url }}',
                'variables' => json_encode(['name', 'sender_name', 'job_title', 'company_name', 'message_excerpt', 'action_url']),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'new_application_received',
                'name' => 'Recruiter new application received',
                'category' => 'system',
                'subject' => 'Aplikasi baru: {{ job_title }}',
                'preheader' => 'Ada kandidat baru yang perlu direview.',
                'body' => "{{ candidate_name }} baru saja melamar untuk {{ job_title }}.\n\nSilakan review profil dan resume kandidat dari Recruiter Portal.",
                'button_label' => 'Review Aplikasi',
                'button_url' => '{{ action_url }}',
                'variables' => json_encode(['name', 'candidate_name', 'job_title', 'action_url']),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];
    }
};
