<?php

namespace App\Notifications;

use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PortalPasswordResetNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $token,
        private readonly string $portal,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $portalLabel = ucfirst($this->portal);
        $url = route("{$this->portal}.password.reset", [
            'token' => $this->token,
            'email' => $notifiable->email,
        ]);

        return app(EmailTemplateService::class)->mailMessage(
            'portal_password_reset',
            [
                'portal_label' => $portalLabel,
                'reset_url' => $url,
            ],
            $notifiable,
            fn () => (new MailMessage)
                ->subject("Reset password {$portalLabel} carikerja.asia")
                ->greeting("Halo {$notifiable->name},")
                ->line("Kami menerima permintaan reset password untuk akun {$portalLabel} Anda.")
                ->action('Reset Password', $url)
                ->line('Link ini berlaku selama 60 menit.')
                ->line('Jika Anda tidak meminta reset password, abaikan email ini.'),
        );
    }
}
