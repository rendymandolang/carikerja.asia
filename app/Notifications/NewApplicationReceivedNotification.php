<?php

namespace App\Notifications;

use App\Models\Application;
use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewApplicationReceivedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Application $application)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $candidateName = $this->application->candidateProfile?->full_name ?: 'Kandidat baru';
        $jobTitle = $this->application->jobPost?->title ?: 'lowongan';

        return app(EmailTemplateService::class)->mailMessage(
            'new_application_received',
            [
                'candidate_name' => $candidateName,
                'job_title' => $jobTitle,
                'action_url' => route('recruiter.applications.show', $this->application),
            ],
            $notifiable,
            fn () => (new MailMessage)
                ->subject("Aplikasi baru: {$jobTitle}")
                ->greeting("Halo {$notifiable->name},")
                ->line("{$candidateName} baru saja melamar untuk {$jobTitle}.")
                ->line('Silakan review profil dan resume kandidat dari Recruiter Portal.')
                ->action('Review Aplikasi', route('recruiter.applications.show', $this->application)),
        );
    }

    public function toArray(object $notifiable): array
    {
        $candidateName = $this->application->candidateProfile?->full_name ?: 'Kandidat baru';
        $jobTitle = $this->application->jobPost?->title ?: 'Application';

        return [
            'title' => 'Aplikasi kandidat baru',
            'body' => "{$candidateName} melamar untuk {$jobTitle}.",
            'action_url' => route('recruiter.applications.show', $this->application),
            'action_label' => 'Review Aplikasi',
            'application_id' => $this->application->id,
            'candidate_name' => $candidateName,
            'job_title' => $jobTitle,
            'company_name' => $this->application->company?->company_name,
            'status' => $this->application->status,
            'category' => 'new_application_received',
        ];
    }
}
