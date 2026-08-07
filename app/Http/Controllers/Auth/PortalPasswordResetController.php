<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\PortalPasswordResetNotification;
use Illuminate\Contracts\Auth\PasswordBroker as PasswordBrokerContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class PortalPasswordResetController extends Controller
{
    public function showForgot(Request $request)
    {
        $portal = $this->portal($request);

        return view('auth.passwords.forgot', [
            'portal' => $portal,
            'title' => $this->portalLabel($portal) . ' Forgot Password',
            'submitRoute' => route("{$portal}.password.email"),
            'loginRoute' => route("{$portal}.login"),
        ]);
    }

    public function sendResetLink(Request $request)
    {
        $portal = $this->portal($request);

        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', strtolower(trim($validated['email'])))
            ->where('role', $portal)
            ->first();

        if ($user && $this->canResetPassword($user, $portal)) {
            $token = PasswordBroker::broker()->createToken($user);
            $user->notify(new PortalPasswordResetNotification($token, $portal));
        }

        return back()->with('success', 'Jika email terdaftar, link reset password sudah dikirim.');
    }

    public function showReset(Request $request, string $token)
    {
        $portal = $this->portal($request);

        return view('auth.passwords.reset', [
            'portal' => $portal,
            'title' => $this->portalLabel($portal) . ' Reset Password',
            'submitRoute' => route("{$portal}.password.update"),
            'loginRoute' => route("{$portal}.login"),
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function reset(Request $request)
    {
        $portal = $this->portal($request);

        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Password::min(10)->mixedCase()->numbers()->symbols()],
        ]);

        $user = User::where('email', strtolower(trim($validated['email'])))
            ->where('role', $portal)
            ->first();

        if (! $user || ! $this->canResetPassword($user, $portal)) {
            return back()
                ->withErrors(['email' => 'Token reset tidak valid atau akun tidak aktif.'])
                ->withInput(['email' => $validated['email']]);
        }

        $status = PasswordBroker::broker()->reset($validated, function (User $user, string $password) {
            $user->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();
        });

        if ($status !== PasswordBrokerContract::PASSWORD_RESET) {
            return back()
                ->withErrors(['email' => __($status)])
                ->withInput(['email' => $validated['email']]);
        }

        return redirect()
            ->route("{$portal}.login")
            ->with('success', 'Password berhasil direset. Silakan login dengan password baru.');
    }

    private function portal(Request $request): string
    {
        return $request->route('portal') ?: Str::before($request->route()?->getName() ?: 'candidate.password.request', '.');
    }

    private function portalLabel(string $portal): string
    {
        return match ($portal) {
            'admin' => 'Admin',
            'recruiter' => 'Recruiter',
            default => 'Candidate',
        };
    }

    private function canResetPassword(User $user, string $portal): bool
    {
        if ($portal === 'admin') {
            return $user->role === 'admin';
        }

        return $user->role === $portal && $user->account_status === 'active';
    }
}
