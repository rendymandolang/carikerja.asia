<?php

namespace App\Http\Controllers\Recruiter;

use App\Http\Controllers\Controller;
use App\Models\RecruiterGoogleWorkspace;
use App\Services\GoogleCalendarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleWorkspaceController extends Controller
{
    public function redirect()
    {
        if (! $this->googleIsConfigured()) {
            return back()->withErrors(['google_workspace' => 'Google Workspace belum dikonfigurasi di server.']);
        }

        return Socialite::driver('google')
            ->redirectUrl(config('services.google.workspace_redirect'))
            ->scopes(GoogleCalendarService::scopes())
            ->with([
                'access_type' => 'offline',
                'prompt' => 'consent',
                'include_granted_scopes' => 'true',
            ])
            ->redirect();
    }

    public function callback(Request $request)
    {
        if (! $this->googleIsConfigured()) {
            return redirect()
                ->route('recruiter.dashboard')
                ->withErrors(['google_workspace' => 'Google Workspace belum dikonfigurasi di server.']);
        }

        try {
            $googleUser = Socialite::driver('google')
                ->redirectUrl(config('services.google.workspace_redirect'))
                ->user();
        } catch (Throwable $exception) {
            Log::warning('Google Workspace OAuth callback failed.', [
                'user_id' => Auth::id(),
                'error' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('recruiter.dashboard')
                ->withErrors(['google_workspace' => 'Koneksi Google Workspace gagal. Silakan coba lagi.']);
        }

        $existing = Auth::user()->googleWorkspace;
        $accessToken = $googleUser->token ?? null;
        $refreshToken = $googleUser->refreshToken ?? $existing?->refresh_token;

        if (! $accessToken || ! $refreshToken) {
            return redirect()
                ->route('recruiter.dashboard')
                ->withErrors(['google_workspace' => 'Google tidak memberikan refresh token. Klik connect ulang dan pastikan consent diberikan.']);
        }

        RecruiterGoogleWorkspace::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'google_id' => $googleUser->getId(),
                'google_email' => strtolower(trim($googleUser->getEmail() ?: '')),
                'google_name' => $googleUser->getName() ?: $googleUser->getNickname(),
                'avatar_url' => $googleUser->getAvatar(),
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'token_expires_at' => isset($googleUser->expiresIn) ? now()->addSeconds((int) $googleUser->expiresIn) : null,
                'scopes' => $this->approvedScopes($googleUser),
                'calendar_id' => $existing?->calendar_id ?: 'primary',
                'status' => 'connected',
                'connected_at' => now(),
                'last_error' => null,
            ],
        );

        return redirect()
            ->route('recruiter.dashboard')
            ->with('success', 'Google Workspace berhasil terhubung.');
    }

    public function disconnect(Request $request)
    {
        $workspace = Auth::user()->googleWorkspace;

        if (! $workspace) {
            return back()->with('success', 'Google Workspace sudah tidak terhubung.');
        }

        $this->revokeToken($workspace->refresh_token ?: $workspace->access_token);

        $workspace->forceFill([
            'access_token' => null,
            'refresh_token' => null,
            'token_expires_at' => null,
            'status' => 'revoked',
            'last_error' => null,
        ])->save();

        return back()->with('success', 'Google Workspace berhasil diputus.');
    }

    private function googleIsConfigured(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'))
            && filled(config('services.google.workspace_redirect'));
    }

    private function approvedScopes(object $googleUser): array
    {
        $scopes = $googleUser->approvedScopes ?? null;

        if (is_string($scopes)) {
            return collect(explode(' ', $scopes))->filter()->values()->all();
        }

        if (is_array($scopes)) {
            return array_values(array_filter($scopes));
        }

        return GoogleCalendarService::scopes();
    }

    private function revokeToken(?string $token): void
    {
        if (! $token) {
            return;
        }

        try {
            Http::asForm()->post('https://oauth2.googleapis.com/revoke', [
                'token' => $token,
            ]);
        } catch (Throwable $exception) {
            Log::warning('Google Workspace token revoke failed.', [
                'user_id' => Auth::id(),
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
