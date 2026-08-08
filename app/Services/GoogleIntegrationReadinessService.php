<?php

namespace App\Services;

class GoogleIntegrationReadinessService
{
    public function snapshot(): array
    {
        $checks = [
            $this->check('OAuth Client ID', filled(config('services.google.client_id')), 'GOOGLE_CLIENT_ID'),
            $this->check('OAuth Client Secret', filled(config('services.google.client_secret')), 'GOOGLE_CLIENT_SECRET'),
            $this->check('Candidate redirect', filled(config('services.google.redirect')), 'GOOGLE_REDIRECT_URI'),
            $this->check('Recruiter Calendar redirect', filled(config('services.google.workspace_redirect')), 'GOOGLE_WORKSPACE_REDIRECT_URI'),
        ];

        return [
            'ready' => collect($checks)->every(fn (array $check) => $check['configured']),
            'checks' => $checks,
            'site_verification_configured' => filled(config('seo.google_site_verification')),
            'authorized_origin' => config('app.url'),
            'candidate_redirect' => route('candidate.login.google.callback'),
            'workspace_redirect' => route('recruiter.google-workspace.callback'),
        ];
    }

    private function check(string $label, bool $configured, string $environmentKey): array
    {
        return compact('label', 'configured', 'environmentKey');
    }
}
