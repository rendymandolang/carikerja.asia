<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AccountSecurityController extends Controller
{
    public function edit(Request $request)
    {
        $portal = $this->portal($request);

        return view('account.security', [
            'portal' => $portal,
            'layout' => $this->layout($portal),
            'title' => 'Account Security',
            'updateRoute' => route("{$portal}.account.security.update"),
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request)
    {
        $portal = $this->portal($request);
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(10)->mixedCase()->numbers()->symbols()],
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()
                ->withErrors(['current_password' => 'Password saat ini tidak sesuai.'])
                ->onlyInput('current_password');
        }

        $user->forceFill([
            'password' => $validated['password'],
            'remember_token' => null,
        ])->save();

        $request->session()->regenerate();

        return redirect()
            ->route("{$portal}.account.security.edit")
            ->with('success', 'Password berhasil diperbarui.');
    }

    private function portal(Request $request): string
    {
        return $request->route('portal') ?: $request->user()?->role ?: 'candidate';
    }

    private function layout(string $portal): string
    {
        return match ($portal) {
            'admin' => 'admin.layouts.app',
            'recruiter' => 'recruiter.layouts.app',
            default => 'candidate.layouts.app',
        };
    }
}
