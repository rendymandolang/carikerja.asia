<?php

namespace App\Notifications;

use App\Models\Application;
use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationSubmittedNotification extends Notification
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
        $jobTitle = $this->application->jobPost?->title ?: 'lowongan';
        $companyName = $this->application->company?->company_name ?: 'company';

        return app(EmailTemplateService::class)->mailMessage(
            'application_submitted',
            [
                'job_title' => $jobTitle,
                'company_name' => $companyName,
                'action_url' => route('candidate.applications.show', $this->application),
            ],
            $notifiable,
            fn () => (new MailMessage)
                ->subject("Lamaran terkirim: {$jobTitle}")
                ->greeting("Halo {$notifiable->name},")
                ->line("Lamaran Anda untuk {$jobTitle} di {$companyName} sudah berhasil dikirim.")
                ->line('Anda bisa memantau status terbaru melalui Candidate Portal.')
                ->action('Lihat Lamaran', route('candidate.applications.show', $this->application))
                ->line('Terima kasih sudah menggunakan carikerja.asia.'),
        );
    }

    public function toArray(object $notifiable): array
    {
        $jobTitle = $this->application->jobPost?->title ?: 'Application';
        $companyName = $this->application->company?->company_name ?: 'Company';

        return [
            'title' => 'Lamaran berhasil dikirim',
            'body' => "Lamaran Anda untuk {$jobTitle} di {$companyName} sudah tercatat.",
            'action_url' => route('candidate.applications.show', $this->application),
            'action_label' => 'Lihat Lamaran',
            'application_id' => $this->application->id,
            'job_title' => $jobTitle,
            'company_name' => $companyName,
            'status' => $this->application->status,
            'category' => 'application_submitted',
        ];
    }
}
