<?php

namespace App\Notifications;

use App\Models\MarketingCampaign;
use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MarketingCampaignNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly MarketingCampaign $campaign,
        private readonly string $recipientName,
        private readonly string $recipientEmail,
        private readonly string $unsubscribeUrl,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $renderer = app(EmailTemplateService::class);
        $variables = [
            'name' => $this->recipientName ?: 'there',
            'email' => $this->recipientEmail,
            'unsubscribe_url' => $this->unsubscribeUrl,
            'app_name' => config('app.name', 'carikerja.asia'),
            'current_year' => now()->year,
        ];

        return (new MailMessage)
            ->subject($renderer->renderString($this->campaign->subject, $variables))
            ->view('emails.marketing-campaign', [
                'subject' => $renderer->renderString($this->campaign->subject, $variables),
                'preheader' => $renderer->renderString($this->campaign->preheader, $variables),
                'body' => $renderer->renderString($this->campaign->body, $variables),
                'buttonLabel' => $renderer->renderString($this->campaign->button_label, $variables),
                'buttonUrl' => $renderer->renderString($this->campaign->button_url, $variables),
                'recipientName' => $this->recipientName,
                'unsubscribeUrl' => $this->unsubscribeUrl,
            ]);
    }
}
