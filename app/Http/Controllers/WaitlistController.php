<?php

namespace App\Http\Controllers;

use App\Models\Waitlist;
use Illuminate\Http\Request;

class WaitlistController extends Controller
{
    public function landing()
    {
        return view('landing');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'in:candidate,recruiter'],

            'full_name' => ['required_if:type,candidate', 'nullable', 'string', 'max:150'],
            'email' => ['required_if:type,candidate', 'nullable', 'email', 'max:190'],
            'linkedin_url' => ['required_if:type,candidate', 'nullable', 'string', 'max:255'],
            'target_role' => ['nullable', 'string', 'max:150'],

            'contact_name' => ['required_if:type,recruiter', 'nullable', 'string', 'max:150'],
            'company_name' => ['required_if:type,recruiter', 'nullable', 'string', 'max:190'],
            'company_email' => ['required_if:type,recruiter', 'nullable', 'email', 'max:190'],
            'position' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['ip_address'] = $request->ip();
        $validated['user_agent'] = $request->userAgent();

        Waitlist::create($validated);

        $message = $validated['type'] === 'candidate'
            ? 'Terima kasih. Anda sudah masuk daftar tunggu pencari kerja carikerja.asia.'
            : 'Terima kasih. Perusahaan Anda sudah masuk daftar tunggu recruiter carikerja.asia.';

        return back()->with('success', $message);
    }
}
