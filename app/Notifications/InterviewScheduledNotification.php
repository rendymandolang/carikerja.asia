<?php

namespace App\Notifications;

use App\Models\ApplicationInterview;
use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InterviewScheduledNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly ApplicationInterview $interview,
        private readonly string $audience = 'candidate',
    ) {
    }

    public function via(object $notifiable): array
    {
        return $this->audience === 'candidate'
            ? ['database', 'mail']
            : ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $jobTitle = $this->interview->application?->jobPost?->title ?: 'lowongan';
        $companyName = $this->interview->company?->company_name ?: 'company';

        return app(EmailTemplateService::class)->mailMessage(
            'interview_scheduled_candidate',
            [
                'job_title' => $jobTitle,
                'company_name' => $companyName,
                'scheduled_at_label' => $this->interview->scheduledAtLabel(),
                'interview_type_label' => $this->interview->typeLabel(),
                'duration_label' => $this->interview->durationLabel(),
                'meeting_url_line' => $this->interview->meetingUrl() ? 'Meeting link: ' . $this->interview->meetingUrl() : '',
                'location_line' => $this->interview->location ? 'Lokasi: ' . $this->interview->location : '',
                'notes_line' => $this->interview->notes ? 'Catatan: ' . $this->interview->notes : '',
                'action_url' => route('candidate.applications.show', $this->interview->application),
            ],
            $notifiable,
            function () use ($jobTitle, $companyName, $notifiable) {
                $message = (new MailMessage)
                    ->subject("Jadwal interview: {$jobTitle}")
                    ->greeting("Halo {$notifiable->name},")
                    ->line("Interview Anda untuk {$jobTitle} di {$companyName} sudah dijadwalkan.")
                    ->line('Waktu: ' . $this->interview->scheduledAtLabel())
                    ->line('Tipe: ' . $this->interview->typeLabel())
                    ->line('Durasi: ' . $this->interview->durationLabel());

                if ($this->interview->meetingUrl()) {
                    $message->line('Meeting link: ' . $this->interview->meetingUrl());
                }

                if ($this->interview->location) {
                    $message->line('Lokasi: ' . $this->interview->location);
                }

                if ($this->interview->notes) {
                    $message->line('Catatan: ' . $this->interview->notes);
                }

                return $message
                    ->action('Lihat Detail Lamaran', route('candidate.applications.show', $this->interview->application))
                    ->line('Silakan cek Candidate Portal untuk detail terbaru.');
            },
        );
    }

    public function toArray(object $notifiable): array
    {
        $application = $this->interview->application;
        $jobTitle = $application?->jobPost?->title ?: 'Application';
        $candidateName = $application?->candidateProfile?->full_name ?: 'Candidate';
        $companyName = $this->interview->company?->company_name ?: 'Company';

        $isRecruiter = $this->audience === 'recruiter';

        return [
            'title' => $isRecruiter ? 'Interview kandidat dijadwalkan' : 'Interview dijadwalkan',
            'body' => $isRecruiter
                ? "{$candidateName} dijadwalkan interview untuk {$jobTitle} pada {$this->interview->scheduledAtLabel()}."
                : "Interview {$jobTitle} di {$companyName} dijadwalkan pada {$this->interview->scheduledAtLabel()}.",
            'action_url' => $isRecruiter
                ? route('recruiter.applications.show', $application)
                : route('candidate.applications.show', $application),
            'action_label' => $isRecruiter ? 'Lihat Application' : 'Lihat Interview',
            'application_id' => $application?->id,
            'interview_id' => $this->interview->id,
            'job_title' => $jobTitle,
            'candidate_name' => $candidateName,
            'company_name' => $companyName,
            'scheduled_at' => $this->interview->scheduled_at?->toISOString(),
            'scheduled_at_label' => $this->interview->scheduledAtLabel(),
            'timezone' => $this->interview->timezone,
            'interview_type' => $this->interview->interview_type,
            'meeting_url' => $this->interview->meetingUrl(),
            'google_sync_status' => $this->interview->google_sync_status,
            'status' => $this->interview->status,
            'category' => 'interview_scheduled',
        ];
    }
}
