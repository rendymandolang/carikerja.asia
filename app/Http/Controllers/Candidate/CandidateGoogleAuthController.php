<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\CandidateProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class CandidateGoogleAuthController extends Controller
{
    public function redirect()
    {
        if (! $this->googleIsConfigured()) {
            return redirect()
                ->route('candidate.login')
                ->withErrors(['email' => 'Login Google belum dikonfigurasi.']);
        }

        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request)
    {
        if (! $this->googleIsConfigured()) {
            return redirect()
                ->route('candidate.login')
                ->withErrors(['email' => 'Login Google belum dikonfigurasi.']);
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable) {
            return redirect()
                ->route('candidate.login')
                ->withErrors(['email' => 'Login Google gagal. Silakan coba lagi.']);
        }

        $email = strtolower(trim($googleUser->getEmail() ?: ''));

        if (! $email) {
            return redirect()
                ->route('candidate.login')
                ->withErrors(['email' => 'Akun Google tidak memberikan email.']);
        }

        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $email)
            ->first();

        if ($user && $user->role !== 'candidate') {
            return redirect()
                ->route('candidate.login')
                ->withErrors(['email' => 'Email Google ini sudah dipakai untuk akun non-kandidat.']);
        }

        if (! $user) {
            $user = User::create([
                'name' => $googleUser->getName() ?: $googleUser->getNickname() ?: $email,
                'email' => $email,
                'password' => Str::password(32),
                'role' => 'candidate',
                'account_status' => 'active',
                'google_id' => $googleUser->getId(),
                'avatar_url' => $googleUser->getAvatar(),
                'email_verified_at' => now(),
            ]);
        } else {
            $user->forceFill([
                'google_id' => $user->google_id ?: $googleUser->getId(),
                'avatar_url' => $googleUser->getAvatar() ?: $user->avatar_url,
                'account_status' => $user->account_status ?: 'active',
                'email_verified_at' => $user->email_verified_at ?: now(),
            ])->save();
        }

        if ($user->account_status !== 'active') {
            return redirect()
                ->route('candidate.login')
                ->withErrors(['email' => 'Akun kandidat Anda belum aktif.']);
        }

        CandidateProfile::firstOrCreate(
            ['email' => $email],
            [
                'user_id' => $user->id,
                'full_name' => $user->name,
                'country' => 'Indonesia',
                'currency' => 'IDR',
                'availability_status' => 'open_to_offers',
            ],
        );

        CandidateProfile::where('email', $email)
            ->whereNull('user_id')
            ->update(['user_id' => $user->id]);

        Auth::login($user);
        $request->session()->regenerate();

        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        return redirect()->route('candidate.dashboard');
    }

    private function googleIsConfigured(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'))
            && filled(config('services.google.redirect'));
    }
}
