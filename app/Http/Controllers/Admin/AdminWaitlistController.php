<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Waitlist;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminWaitlistController extends Controller
{
    public function index(Request $request)
    {
        $query = Waitlist::query()->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('admin_status', $request->status);
        }

        if ($request->filled('q')) {
            $keyword = $request->q;

            $query->where(function ($q) use ($keyword) {
                $q->where('full_name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%")
                    ->orWhere('linkedin_url', 'like', "%{$keyword}%")
                    ->orWhere('target_role', 'like', "%{$keyword}%")
                    ->orWhere('contact_name', 'like', "%{$keyword}%")
                    ->orWhere('company_name', 'like', "%{$keyword}%")
                    ->orWhere('company_email', 'like', "%{$keyword}%")
                    ->orWhere('position', 'like', "%{$keyword}%");
            });
        }

        return view('admin.waitlists.index', [
            'waitlists' => $query->paginate(15)->withQueryString(),
        ]);
    }

    public function show(Waitlist $waitlist)
    {
        return view('admin.waitlists.show', compact('waitlist'));
    }

    public function update(Request $request, Waitlist $waitlist)
    {
        $validated = $request->validate([
            'admin_status' => ['required', 'in:new,contacted,qualified,onboarded,rejected'],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $validated['followed_up_at'] = now();

        $waitlist->update($validated);

        return back()->with('success', 'Status waitlist berhasil diperbarui.');
    }

    public function export(Request $request): StreamedResponse
    {
        $fileName = 'carikerja-waitlists-' . now()->format('Ymd-His') . '.csv';

        $query = Waitlist::query()->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('admin_status', $request->status);
        }

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'ID',
                'Type',
                'Full Name',
                'Email',
                'LinkedIn',
                'Target Role',
                'Contact Name',
                'Company Name',
                'Company Email',
                'Position',
                'Admin Status',
                'Created At',
            ]);

            $query->chunk(200, function ($waitlists) use ($handle) {
                foreach ($waitlists as $item) {
                    fputcsv($handle, [
                        $item->id,
                        $item->type,
                        $item->full_name,
                        $item->email,
                        $item->linkedin_url,
                        $item->target_role,
                        $item->contact_name,
                        $item->company_name,
                        $item->company_email,
                        $item->position,
                        $item->admin_status,
                        $item->created_at,
                    ]);
                }
            });

            fclose($handle);
        }, $fileName);
    }
}
