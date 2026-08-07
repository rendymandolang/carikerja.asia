<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketing_campaigns', function (Blueprint $table) {
            if (! Schema::hasColumn('marketing_campaigns', 'email_template_id')) {
                $table->unsignedBigInteger('email_template_id')->nullable()->index();
            }

            if (! Schema::hasColumn('marketing_campaigns', 'scheduled_at')) {
                $table->timestamp('scheduled_at')->nullable()->index();
            }

            if (! Schema::hasColumn('marketing_campaigns', 'queued_at')) {
                $table->timestamp('queued_at')->nullable();
            }

            if (! Schema::hasColumn('marketing_campaigns', 'started_at')) {
                $table->timestamp('started_at')->nullable();
            }

            if (! Schema::hasColumn('marketing_campaigns', 'finished_at')) {
                $table->timestamp('finished_at')->nullable();
            }

            if (! Schema::hasColumn('marketing_campaigns', 'last_error')) {
                $table->text('last_error')->nullable();
            }
        });

        foreach ($this->marketingTemplates() as $template) {
            $exists = DB::table('email_templates')->where('key', $template['key'])->exists();
            $payload = array_merge($template, ['updated_at' => now()]);

            if (! $exists) {
                $payload['created_at'] = now();
            }

            DB::table('email_templates')->updateOrInsert(
                ['key' => $template['key']],
                $payload,
            );
        }
    }

    public function down(): void
    {
        Schema::table('marketing_campaigns', function (Blueprint $table) {
            foreach (['email_template_id', 'scheduled_at', 'queued_at', 'started_at', 'finished_at', 'last_error'] as $column) {
                if (Schema::hasColumn('marketing_campaigns', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        DB::table('email_templates')
            ->whereIn('key', collect($this->marketingTemplates())->pluck('key')->all())
            ->delete();
    }

    private function marketingTemplates(): array
    {
        $variables = json_encode(['name', 'email', 'app_name', 'unsubscribe_url', 'current_year']);

        return [
            [
                'key' => 'marketing_general_update',
                'name' => 'Marketing general update',
                'category' => 'marketing',
                'subject' => 'Update terbaru dari {{ app_name }}',
                'preheader' => 'Informasi singkat untuk membantu proses rekrutmen Anda.',
                'body' => "Halo {{ name }},\n\nKami punya update terbaru dari {{ app_name }} untuk membantu proses cari kerja dan rekrutmen berjalan lebih jelas.\n\nSilakan cek informasi lengkapnya melalui tombol di bawah.",
                'button_label' => 'Buka carikerja.asia',
                'button_url' => config('app.url'),
                'variables' => $variables,
                'is_active' => true,
            ],
            [
                'key' => 'marketing_candidate_job_alert',
                'name' => 'Candidate job alert',
                'category' => 'marketing',
                'subject' => 'Lowongan baru yang mungkin cocok untuk Anda',
                'preheader' => 'Cek lowongan terbaru dan pantau status lamaran dari Candidate Portal.',
                'body' => "Halo {{ name }},\n\nAda lowongan dan update karier baru di {{ app_name }}.\n\nLengkapi resume center Anda agar peluang match dengan lowongan makin baik, lalu pantau proses lamaran langsung dari Candidate Portal.",
                'button_label' => 'Lihat Lowongan',
                'button_url' => url('/jobs'),
                'variables' => $variables,
                'is_active' => true,
            ],
            [
                'key' => 'marketing_recruiter_onboarding',
                'name' => 'Recruiter onboarding',
                'category' => 'marketing',
                'subject' => 'Kelola hiring lebih transparan di {{ app_name }}',
                'preheader' => 'Posting lowongan, review kandidat, jadwalkan interview, dan kirim update dari satu portal.',
                'body' => "Halo {{ name }},\n\n{{ app_name }} membantu recruiter mengelola lowongan, kandidat, interview, dan komunikasi dalam satu alur yang lebih rapi.\n\nGunakan portal recruiter untuk menjaga proses hiring tetap transparan dan mudah dipantau.",
                'button_label' => 'Buka Platform',
                'button_url' => config('app.url'),
                'variables' => $variables,
                'is_active' => true,
            ],
        ];
    }
};
