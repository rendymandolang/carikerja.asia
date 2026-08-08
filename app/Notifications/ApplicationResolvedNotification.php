<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationResolvedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Application $application) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $job = $this->application->jobPost?->title ?: 'lowongan';
        $message = (new MailMessage)->subject("Hasil akhir lamaran: {$job}")
            ->greeting("Halo {$notifiable->name},")
            ->line("Proses lamaran Anda untuk {$job} telah selesai: {$this->application->resolutionLabel()}.");
        if ($this->application->final_reason) {
            $message->line("Alasan: {$this->application->final_reason}");
        }

        return $message->action('Lihat Detail', route('candidate.applications.show', $this->application));
    }

    public function toArray(object $notifiable): array
    {
        return ['title' => 'Hasil akhir lamaran', 'body' => ($this->application->jobPost?->title ?: 'Lamaran').': '.$this->application->resolutionLabel(),
            'action_url' => route('candidate.applications.show', $this->application), 'action_label' => 'Lihat Detail',
            'application_id' => $this->application->id, 'resolution' => $this->application->resolution, 'category' => 'application_resolved'];
    }
}
