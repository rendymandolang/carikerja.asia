<?php

namespace App\Notifications;

use App\Models\ApplicationMessage;
use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class ApplicationMessageReceivedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly ApplicationMessage $message,
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
        $application = $this->message->application;
        $jobTitle = $application?->jobPost?->title ?: 'lamaran';
        $companyName = $application?->company?->company_name ?: 'company';
        $senderName = $this->message->sender?->name ?: $this->message->senderLabel();

        return app(EmailTemplateService::class)->mailMessage(
            'application_message_received_candidate',
            [
                'sender_name' => $senderName,
                'job_title' => $jobTitle,
                'company_name' => $companyName,
                'message_excerpt' => Str::limit($this->message->body, 180),
                'action_url' => route('candidate.applications.show', $application),
            ],
            $notifiable,
            fn () => (new MailMessage)
                ->subject("Pesan baru: {$jobTitle}")
                ->greeting("Halo {$notifiable->name},")
                ->line("{$senderName} mengirim pesan baru terkait lamaran {$jobTitle} di {$companyName}.")
                ->line('Pesan: ' . Str::limit($this->message->body, 180))
                ->action('Buka Detail Lamaran', route('candidate.applications.show', $application))
                ->line('Balas langsung dari Candidate Portal agar komunikasi tetap tercatat.'),
        );
    }

    public function toArray(object $notifiable): array
    {
        $application = $this->message->application;
        $jobTitle = $application?->jobPost?->title ?: 'Application';
        $companyName = $application?->company?->company_name ?: 'Company';
        $candidateName = $application?->candidateProfile?->full_name ?: 'Candidate';
        $senderName = $this->message->sender?->name ?: $this->message->senderLabel();
        $isRecruiter = $this->audience === 'recruiter';

        return [
            'title' => $isRecruiter ? 'Pesan baru dari kandidat' : 'Pesan baru dari recruiter',
            'body' => $isRecruiter
                ? "{$candidateName} mengirim pesan untuk {$jobTitle}."
                : "{$senderName} mengirim pesan terkait {$jobTitle} di {$companyName}.",
            'action_url' => $isRecruiter
                ? route('recruiter.applications.show', $application)
                : route('candidate.applications.show', $application),
            'action_label' => 'Buka Thread',
            'application_id' => $application?->id,
            'message_id' => $this->message->id,
            'job_title' => $jobTitle,
            'company_name' => $companyName,
            'candidate_name' => $candidateName,
            'sender_name' => $senderName,
            'sender_role' => $this->message->sender_role,
            'excerpt' => Str::limit($this->message->body, 140),
            'category' => 'application_message_received',
        ];
    }
}
