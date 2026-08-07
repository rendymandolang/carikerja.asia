<?php

namespace App\Services;

use App\Models\EmailTemplate;
use Closure;
use Illuminate\Notifications\Messages\MailMessage;

class EmailTemplateService
{
    public function mailMessage(string $key, array $variables, object $notifiable, Closure $fallback): MailMessage
    {
        $template = EmailTemplate::query()
            ->where('key', $key)
            ->where('is_active', true)
            ->first();

        if (! $template) {
            return $fallback();
        }

        $variables = array_merge([
            'name' => $notifiable->name ?? '',
            'email' => $notifiable->email ?? '',
            'app_name' => config('app.name', 'carikerja.asia'),
            'current_year' => now()->year,
        ], $variables);

        $message = (new MailMessage)
            ->subject($this->renderString($template->subject, $variables));

        if ($greeting = $this->renderString('Halo {{ name }},', $variables)) {
            $message->greeting($greeting);
        }

        if ($template->preheader) {
            $message->line($this->renderString($template->preheader, $variables));
        }

        foreach ($this->bodyLines($this->renderString($template->body, $variables)) as $line) {
            $message->line($line);
        }

        if ($template->button_label && $template->button_url) {
            $message->action(
                $this->renderString($template->button_label, $variables),
                $this->renderString($template->button_url, $variables),
            );
        }

        return $message;
    }

    public function renderString(?string $value, array $variables): string
    {
        if ($value === null) {
            return '';
        }

        return trim((string) preg_replace_callback('/{{\s*([a-zA-Z0-9_.]+)\s*}}/', function (array $matches) use ($variables) {
            $replacement = data_get($variables, $matches[1], '');

            return is_scalar($replacement) ? (string) $replacement : '';
        }, $value));
    }

    /**
     * @return array<int, string>
     */
    public function bodyLines(string $body): array
    {
        return collect(preg_split('/\R+/', $body) ?: [])
            ->map(fn (string $line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }
}
