<?php

namespace App\Services;

use App\Models\ApplicationInterview;
use App\Models\RecruiterGoogleWorkspace;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class GoogleCalendarService
{
    private const CALENDAR_SCOPE = 'https://www.googleapis.com/auth/calendar.events';

    public static function scopes(): array
    {
        return [self::CALENDAR_SCOPE];
    }

    public function createInterviewEvent(ApplicationInterview $interview, RecruiterGoogleWorkspace $workspace): array
    {
        $accessToken = $this->accessToken($workspace);
        $interview->loadMissing(['application.candidateProfile', 'application.jobPost', 'application.company', 'scheduledBy']);

        $start = $interview->scheduled_at->copy()->setTimezone($interview->timezone);
        $end = $start->copy()->addMinutes($interview->duration_minutes);
        $application = $interview->application;
        $candidate = $application?->candidateProfile;
        $job = $application?->jobPost;
        $company = $application?->company;

        $attendees = collect([
            $candidate?->email,
            $interview->scheduledBy?->email,
        ])
            ->filter()
            ->unique()
            ->map(fn (string $email) => ['email' => $email])
            ->values()
            ->all();

        $event = [
            'summary' => $interview->title,
            'description' => trim(implode("\n", array_filter([
                $job?->title ? 'Job: ' . $job->title : null,
                $company?->company_name ? 'Company: ' . $company->company_name : null,
                $candidate?->full_name ? 'Candidate: ' . $candidate->full_name : null,
                $interview->notes ? "\nNotes:\n" . $interview->notes : null,
                'Created from carikerja.asia',
            ]))),
            'start' => [
                'dateTime' => $start->format(DATE_RFC3339),
                'timeZone' => $interview->timezone,
            ],
            'end' => [
                'dateTime' => $end->format(DATE_RFC3339),
                'timeZone' => $interview->timezone,
            ],
            'attendees' => $attendees,
            'conferenceData' => [
                'createRequest' => [
                    'requestId' => (string) Str::uuid(),
                    'conferenceSolutionKey' => [
                        'type' => 'hangoutsMeet',
                    ],
                ],
            ],
        ];

        $calendarId = rawurlencode($workspace->calendar_id ?: 'primary');
        $query = http_build_query([
            'conferenceDataVersion' => 1,
            'sendUpdates' => config('services.google.calendar_send_updates', 'all'),
        ]);

        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->post("https://www.googleapis.com/calendar/v3/calendars/{$calendarId}/events?{$query}", $event);

        if ($response->failed()) {
            throw new RuntimeException($this->googleError($response->json(), 'Google Calendar event creation failed.'));
        }

        $data = $response->json();
        $meetLink = $data['hangoutLink'] ?? $this->firstConferenceUri($data);

        return [
            'event_id' => $data['id'] ?? null,
            'event_url' => $data['htmlLink'] ?? null,
            'meet_link' => $meetLink,
            'raw' => $data,
        ];
    }

    private function accessToken(RecruiterGoogleWorkspace $workspace): string
    {
        if (! $workspace->tokenNeedsRefresh() && filled($workspace->access_token)) {
            return $workspace->access_token;
        }

        if (! $workspace->refresh_token) {
            throw new RuntimeException('Google Workspace connection needs reconnect.');
        }

        $response = Http::asForm()
            ->acceptJson()
            ->post('https://oauth2.googleapis.com/token', [
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'refresh_token' => $workspace->refresh_token,
                'grant_type' => 'refresh_token',
            ]);

        if ($response->failed()) {
            throw new RuntimeException($this->googleError($response->json(), 'Google token refresh failed.'));
        }

        $data = $response->json();

        $workspace->forceFill([
            'access_token' => $data['access_token'] ?? $workspace->access_token,
            'token_expires_at' => isset($data['expires_in']) ? now()->addSeconds((int) $data['expires_in']) : null,
            'status' => 'connected',
            'last_error' => null,
        ])->save();

        return $workspace->access_token;
    }

    private function firstConferenceUri(array $data): ?string
    {
        foreach ($data['conferenceData']['entryPoints'] ?? [] as $entryPoint) {
            if (($entryPoint['entryPointType'] ?? null) === 'video' && filled($entryPoint['uri'] ?? null)) {
                return $entryPoint['uri'];
            }
        }

        return null;
    }

    private function googleError(?array $data, string $fallback): string
    {
        if (is_string($data['error'] ?? null)) {
            return $data['error'];
        }

        return $data['error']['message']
            ?? $data['error_description']
            ?? $fallback;
    }
}
