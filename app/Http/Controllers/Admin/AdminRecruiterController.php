<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminRecruiterController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()
            ->where('role', 'recruiter')
            ->with('companies')
            ->latest();

        if ($request->filled('account_status')) {
            $query->where('account_status', $request->account_status);
        }

        if ($request->filled('company_id')) {
            $query->whereHas('companies', function ($q) use ($request) {
                $q->where('companies.id', $request->company_id);
            });
        }

        if ($request->filled('q')) {
            $keyword = $request->q;

            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%")
                    ->orWhere('phone', 'like', "%{$keyword}%");
            });
        }

        return view('admin.recruiters.index', [
            'recruiters' => $query->paginate(15)->withQueryString(),
            'companies' => Company::orderBy('company_name')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.recruiters.create', [
            'recruiter' => new User([
                'role' => 'recruiter',
                'account_status' => 'active',
            ]),
            'companies' => Company::orderBy('company_name')->get(),
            'selectedCompanyId' => null,
            'pivotData' => [
                'company_role' => 'recruiter',
                'job_title' => null,
                'status' => 'active',
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateRecruiter($request);

        $recruiter = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'recruiter',
            'account_status' => $validated['account_status'],
        ]);

        $recruiter->companies()->attach($validated['company_id'], [
            'company_role' => $validated['company_role'],
            'job_title' => $validated['job_title'] ?? null,
            'status' => $validated['company_user_status'],
            'invited_at' => now(),
        ]);

        return redirect()
            ->route('admin.recruiters.show', $recruiter)
            ->with('success', 'Recruiter berhasil dibuat dan dihubungkan ke company.');
    }

    public function show(User $recruiter)
    {
        $this->ensureRecruiter($recruiter);

        $recruiter->load('companies');

        return view('admin.recruiters.show', compact('recruiter'));
    }

    public function edit(User $recruiter)
    {
        $this->ensureRecruiter($recruiter);

        $recruiter->load('companies');

        $company = $recruiter->companies->first();

        return view('admin.recruiters.edit', [
            'recruiter' => $recruiter,
            'companies' => Company::orderBy('company_name')->get(),
            'selectedCompanyId' => $company?->id,
            'pivotData' => [
                'company_role' => $company?->pivot?->company_role ?? 'recruiter',
                'job_title' => $company?->pivot?->job_title,
                'status' => $company?->pivot?->status ?? 'active',
            ],
        ]);
    }

    public function update(Request $request, User $recruiter)
    {
        $this->ensureRecruiter($recruiter);

        $validated = $this->validateRecruiter($request, $recruiter);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role' => 'recruiter',
            'account_status' => $validated['account_status'],
        ];

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $recruiter->update($data);

        $recruiter->companies()->sync([
            $validated['company_id'] => [
                'company_role' => $validated['company_role'],
                'job_title' => $validated['job_title'] ?? null,
                'status' => $validated['company_user_status'],
            ],
        ]);

        return redirect()
            ->route('admin.recruiters.show', $recruiter)
            ->with('success', 'Recruiter berhasil diperbarui.');
    }

    private function validateRecruiter(Request $request, ?User $recruiter = null): array
    {
        $emailRule = Rule::unique('users', 'email');

        if ($recruiter) {
            $emailRule->ignore($recruiter->id);
        }

        $passwordRules = $recruiter
            ? ['nullable', 'string', 'min:10']
            : ['required', 'string', 'min:10'];

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', $emailRule],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => $passwordRules,
            'company_id' => ['required', 'exists:companies,id'],
            'company_role' => ['required', Rule::in(['owner', 'admin', 'recruiter'])],
            'job_title' => ['nullable', 'string', 'max:255'],
            'account_status' => ['required', Rule::in(['active', 'inactive', 'suspended'])],
            'company_user_status' => ['required', Rule::in(['active', 'inactive', 'suspended'])],
        ]);
    }

    private function ensureRecruiter(User $user): void
    {
        abort_if($user->role !== 'recruiter', 404);
    }
}
