<?php

namespace App\Services;

use Throwable;

class MailServerReadinessService
{
    public function snapshot(): array
    {
        $checks = [
            $this->mailerCheck(),
            $this->fromAddressCheck(),
            $this->mxCheck(),
            $this->spfCheck(),
            $this->dmarcCheck(),
            $this->dkimCheck(),
            $this->smtpSocketCheck(),
        ];

        return [
            'summary' => $this->summary($checks),
            'checks' => $checks,
            'settings' => $this->settings(),
        ];
    }

    private function mailerCheck(): array
    {
        $mailer = config('mail.default');

        if (in_array($mailer, ['log', 'array'], true)) {
            return $this->check('mailer', 'Laravel Mailer', 'warning', "MAIL_MAILER is {$mailer}; email is not sent to real inboxes yet.");
        }

        if ($mailer !== 'smtp') {
            return $this->check('mailer', 'Laravel Mailer', 'ok', "MAIL_MAILER is {$mailer}.");
        }

        $host = (string) config('mail.mailers.smtp.host');
        $port = (int) config('mail.mailers.smtp.port');
        $resolvedHost = gethostbyname($host);
        $isLocalRelay = (
            in_array($host, ['127.0.0.1', 'localhost', '::1'], true)
            || in_array($resolvedHost, ['127.0.0.1', '::1'], true)
        ) && in_array($port, [25, 2525], true);

        $missing = collect([
            'MAIL_HOST' => $host,
            'MAIL_PORT' => $port,
        ])->filter(fn ($value) => blank($value));

        if ($missing->isNotEmpty()) {
            return $this->check('mailer', 'Laravel Mailer', 'critical', 'Missing SMTP config: ' . $missing->keys()->implode(', '));
        }

        if ($isLocalRelay) {
            return $this->check('mailer', 'Laravel Mailer', 'ok', "Local SMTP relay is configured on {$host}:{$port}.");
        }

        $missingAuth = collect([
            'MAIL_USERNAME' => config('mail.mailers.smtp.username'),
            'MAIL_PASSWORD' => config('mail.mailers.smtp.password'),
        ])->filter(fn ($value) => blank($value));

        if ($missingAuth->isNotEmpty()) {
            return $this->check('mailer', 'Laravel Mailer', 'critical', 'Missing SMTP auth config: ' . $missingAuth->keys()->implode(', '));
        }

        return $this->check('mailer', 'Laravel Mailer', 'ok', 'SMTP config is present.');
    }

    private function fromAddressCheck(): array
    {
        $address = config('mail.from.address');
        $domain = config('mail_server.domain');

        if (! filter_var($address, FILTER_VALIDATE_EMAIL)) {
            return $this->check('from_address', 'From Address', 'critical', 'MAIL_FROM_ADDRESS is not a valid email address.');
        }

        if (! str_ends_with(strtolower($address), '@' . strtolower($domain))) {
            return $this->check('from_address', 'From Address', 'warning', "From address is outside {$domain}.");
        }

        return $this->check('from_address', 'From Address', 'ok', $address);
    }

    private function mxCheck(): array
    {
        $domain = config('mail_server.domain');
        $hostname = config('mail_server.hostname');
        $records = $this->dns($domain, DNS_MX);

        if ($records === []) {
            return $this->check('mx', 'MX Records', 'critical', "No MX record found for {$domain}.");
        }

        $targets = collect($records)
            ->pluck('target')
            ->filter()
            ->map(fn (string $target) => strtolower(rtrim($target, '.')))
            ->values();

        if (config('mail_server.mode') === 'self_hosted' && ! $targets->contains(strtolower($hostname))) {
            return $this->check('mx', 'MX Records', 'warning', 'MX does not point to ' . $hostname . '. Current: ' . $targets->implode(', '));
        }

        return $this->check('mx', 'MX Records', 'ok', $targets->implode(', '));
    }

    private function spfCheck(): array
    {
        $domain = config('mail_server.domain');
        $spf = $this->txtRecords($domain)
            ->first(fn (string $record) => str_starts_with(strtolower($record), 'v=spf1'));

        if (! $spf) {
            return $this->check('spf', 'SPF', 'critical', "No SPF record found for {$domain}.");
        }

        if (config('mail_server.mode') === 'self_hosted' && ! str_contains($spf, ' mx') && ! str_contains($spf, 'ip4:')) {
            return $this->check('spf', 'SPF', 'warning', 'SPF exists but does not explicitly authorize self-hosted SMTP.');
        }

        return $this->check('spf', 'SPF', 'ok', $spf);
    }

    private function dmarcCheck(): array
    {
        $domain = config('mail_server.domain');
        $record = $this->txtRecords('_dmarc.' . $domain)
            ->first(fn (string $record) => str_starts_with(strtolower($record), 'v=dmarc1'));

        if (! $record) {
            return $this->check('dmarc', 'DMARC', 'warning', 'No DMARC record found.');
        }

        return $this->check('dmarc', 'DMARC', 'ok', $record);
    }

    private function dkimCheck(): array
    {
        $domain = config('mail_server.domain');
        $selector = config('mail_server.dkim_selector');
        $record = $this->txtRecords($selector . '._domainkey.' . $domain)
            ->first(fn (string $record) => str_contains(strtolower($record), 'v=dkim1'));

        if (! $record) {
            return $this->check('dkim', 'DKIM', 'warning', "No DKIM record found for selector {$selector}.");
        }

        return $this->check('dkim', 'DKIM', 'ok', 'DKIM record is present.');
    }

    private function smtpSocketCheck(): array
    {
        if (config('mail.default') !== 'smtp') {
            return $this->check('smtp_socket', 'SMTP Socket', 'warning', 'Skipped because MAIL_MAILER is not smtp.');
        }

        $host = config('mail.mailers.smtp.host');
        $port = (int) config('mail.mailers.smtp.port');

        try {
            $connection = @fsockopen($host, $port, $errno, $error, 3);

            if (! $connection) {
                return $this->check('smtp_socket', 'SMTP Socket', 'critical', trim($error) ?: "Unable to connect to {$host}:{$port}.");
            }

            fclose($connection);

            return $this->check('smtp_socket', 'SMTP Socket', 'ok', "Connected to {$host}:{$port}.");
        } catch (Throwable $exception) {
            return $this->check('smtp_socket', 'SMTP Socket', 'critical', $exception->getMessage());
        }
    }

    private function settings(): array
    {
        return [
            ['label' => 'Mode', 'value' => config('mail_server.mode')],
            ['label' => 'Domain', 'value' => config('mail_server.domain')],
            ['label' => 'Mail Hostname', 'value' => config('mail_server.hostname')],
            ['label' => 'DKIM Selector', 'value' => config('mail_server.dkim_selector')],
            ['label' => 'Postmaster', 'value' => config('mail_server.postmaster')],
            ['label' => 'Mailer', 'value' => config('mail.default')],
            ['label' => 'SMTP Host', 'value' => config('mail.mailers.smtp.host')],
            ['label' => 'SMTP Port', 'value' => config('mail.mailers.smtp.port')],
        ];
    }

    private function dns(string $name, int $type): array
    {
        try {
            return dns_get_record($name, $type) ?: [];
        } catch (Throwable) {
            return [];
        }
    }

    private function txtRecords(string $name): \Illuminate\Support\Collection
    {
        return collect($this->dns($name, DNS_TXT))
            ->map(fn (array $record) => $record['txt'] ?? null)
            ->filter()
            ->values();
    }

    private function summary(array $checks): array
    {
        $statuses = collect($checks)->pluck('status');
        $overall = $statuses->contains('critical') ? 'critical' : ($statuses->contains('warning') ? 'warning' : 'ok');

        return [
            'overall' => $overall,
            'ok' => $statuses->filter(fn (string $status) => $status === 'ok')->count(),
            'warning' => $statuses->filter(fn (string $status) => $status === 'warning')->count(),
            'critical' => $statuses->filter(fn (string $status) => $status === 'critical')->count(),
        ];
    }

    private function check(string $key, string $label, string $status, string $message): array
    {
        return compact('key', 'label', 'status', 'message');
    }
}
