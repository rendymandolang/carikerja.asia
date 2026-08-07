<?php

namespace App\Notifications;

use App\Models\Application;
use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationStatusUpdatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Application $application,
        private readonly ?string $oldStatus = null,
        private readonly ?string $note = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $jobTitle = $this->application->jobPost?->title ?: 'lowongan';
        $status = $this->statusLabel($this->application->status);

        return app(EmailTemplateService::class)->mailMessage(
            'application_status_updated',
            [
                'job_title' => $jobTitle,
                'status_label' => $status,
                'status_note_line' => $this->note ? "Catatan: {$this->note}" : '',
                'action_url' => route('candidate.applications.show', $this->application),
            ],
            $notifiable,
            function () use ($jobTitle, $status, $notifiable) {
                $message = (new MailMessage)
                    ->subject("Status lamaran diperbarui: {$jobTitle}")
                    ->greeting("Halo {$notifiable->name},")
                    ->line("Status lamaran Anda untuk {$jobTitle} sekarang: {$status}.");

                if ($this->note) {
                    $message->line("Catatan: {$this->note}");
                }

                return $message
                    ->action('Lihat Detail Lamaran', route('candidate.applications.show', $this->application))
                    ->line('Pantau proses rekrutmen Anda melalui Candidate Portal.');
            },
        );
    }

    public function toArray(object $notifiable): array
    {
        $jobTitle = $this->application->jobPost?->title ?: 'Application';
        $status = $this->statusLabel($this->application->status);

        return [
            'title' => 'Status lamaran diperbarui',
            'body' => "Status lamaran {$jobTitle} sekarang: {$status}.",
            'action_url' => route('candidate.applications.show', $this->application),
            'action_label' => 'Lihat Detail',
            'application_id' => $this->application->id,
            'job_title' => $jobTitle,
            'company_name' => $this->application->company?->company_name,
            'old_status' => $this->oldStatus,
            'status' => $this->application->status,
            'status_note' => $this->note,
            'category' => 'application_status_updated',
        ];
    }

    private function statusLabel(?string $status): string
    {
        return ucfirst(str_replace('_', ' ', $status ?: '-'));
    }
}
