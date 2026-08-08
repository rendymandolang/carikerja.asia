<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RecruiterSlaReminderNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Application $application, private readonly bool $overdue = false) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $candidate = $this->application->candidateProfile?->full_name ?: 'Kandidat';
        $job = $this->application->jobPost?->title ?: 'lowongan';

        return (new MailMessage)
            ->subject(($this->overdue ? 'Terlambat merespons: ' : 'Batas respons mendekat: ').$job)
            ->greeting("Halo {$notifiable->name},")
            ->line("Lamaran {$candidate} untuk {$job} ".($this->overdue ? 'telah melewati batas respons.' : 'mendekati batas respons pertama.'))
            ->action('Tinjau Lamaran', route('recruiter.applications.show', $this->application));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->overdue ? 'Lamaran terlambat direspons' : 'Batas respons mendekat',
            'body' => ($this->application->candidateProfile?->full_name ?: 'Kandidat').' — '.($this->application->jobPost?->title ?: 'Lowongan'),
            'action_url' => route('recruiter.applications.show', $this->application),
            'action_label' => 'Tinjau Lamaran', 'application_id' => $this->application->id,
            'category' => $this->overdue ? 'application_response_overdue' : 'application_response_due',
        ];
    }
}
