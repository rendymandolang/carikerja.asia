<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminCompanyController extends Controller
{
    public function index(Request $request)
    {
        $query = Company::query()->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $keyword = $request->q;

            $query->where(function ($q) use ($keyword) {
                $q->where('company_name', 'like', "%{$keyword}%")
                    ->orWhere('legal_name', 'like', "%{$keyword}%")
                    ->orWhere('industry', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%")
                    ->orWhere('phone', 'like', "%{$keyword}%")
                    ->orWhere('city', 'like', "%{$keyword}%")
                    ->orWhere('province', 'like', "%{$keyword}%");
            });
        }

        return view('admin.companies.index', [
            'companies' => $query->paginate(15)->withQueryString(),
        ]);
    }

    public function create()
    {
        return view('admin.companies.create', [
            'company' => new Company([
                'status' => 'pending',
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateCompany($request);

        $validated['slug'] = $this->generateUniqueSlug($validated['company_name']);

        $company = Company::create($validated);

        return redirect()
            ->route('admin.companies.show', $company)
            ->with('success', 'Company berhasil dibuat.');
    }

    public function show(Company $company)
    {
        return view('admin.companies.show', compact('company'));
    }

    public function edit(Company $company)
    {
        return view('admin.companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $validated = $this->validateCompany($request);

        if ($company->company_name !== $validated['company_name']) {
            $validated['slug'] = $this->generateUniqueSlug($validated['company_name'], $company->id);
        }

        $company->update($validated);

        return redirect()
            ->route('admin.companies.show', $company)
            ->with('success', 'Company berhasil diperbarui.');
    }

    private function validateCompany(Request $request): array
    {
        return $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'industry' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:2000'],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'status' => ['required', Rule::in(['pending', 'active', 'suspended', 'rejected'])],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function generateUniqueSlug(string $companyName, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($companyName);
        $slug = $baseSlug;
        $counter = 1;

        while (
            Company::where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
