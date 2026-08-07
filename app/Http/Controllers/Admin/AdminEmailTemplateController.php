<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminEmailTemplateController extends Controller
{
    public function index()
    {
        return view('admin.email.templates.index', [
            'templates' => EmailTemplate::query()
                ->with('updatedBy')
                ->orderBy('category')
                ->orderBy('name')
                ->paginate(20),
        ]);
    }

    public function create()
    {
        return view('admin.email.templates.create', [
            'template' => new EmailTemplate([
                'category' => 'marketing',
                'is_active' => true,
                'button_label' => 'Buka carikerja.asia',
                'button_url' => route('landing'),
                'variables' => ['name', 'email', 'app_name', 'unsubscribe_url', 'current_year'],
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);
        $name = $validated['name'];

        $template = EmailTemplate::create([
            ...$validated,
            'key' => $this->uniqueMarketingKey($name),
            'category' => 'marketing',
            'variables' => ['name', 'email', 'app_name', 'unsubscribe_url', 'current_year'],
            'is_active' => $request->boolean('is_active'),
            'updated_by_user_id' => $request->user()->id,
        ]);

        return redirect()
            ->route('admin.email.templates.edit', $template)
            ->with('success', 'Template marketing berhasil dibuat.');
    }

    public function edit(EmailTemplate $template)
    {
        return view('admin.email.templates.edit', compact('template'));
    }

    public function update(Request $request, EmailTemplate $template)
    {
        $validated = $this->validated($request, $template);

        $template->update([
            ...$validated,
            'is_active' => $request->boolean('is_active'),
            'updated_by_user_id' => $request->user()->id,
        ]);

        return redirect()
            ->route('admin.email.templates.edit', $template)
            ->with('success', 'Template email berhasil diperbarui.');
    }

    public function destroy(EmailTemplate $template)
    {
        abort_if($template->category !== 'marketing', 403);

        $template->delete();

        return redirect()
            ->route('admin.email.templates.index')
            ->with('success', 'Template marketing berhasil dihapus.');
    }

    private function validated(Request $request, ?EmailTemplate $template = null): array
    {
        $rules = [
            'subject' => ['required', 'string', 'max:255'],
            'preheader' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:10000'],
            'button_label' => ['nullable', 'string', 'max:80'],
            'button_url' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ];

        if (! $template || $template->category === 'marketing') {
            $rules['name'] = ['required', 'string', 'max:150'];
        }

        $validated = $request->validate($rules);

        if ($template && $template->category !== 'marketing') {
            unset($validated['name']);
        }

        return $validated;
    }

    private function uniqueMarketingKey(string $name): string
    {
        $slug = Str::slug($name, '_') ?: 'template';
        $base = 'marketing_' . $slug;
        $key = $base;
        $suffix = 2;

        while (EmailTemplate::where('key', $key)->exists()) {
            $key = "{$base}_{$suffix}";
            $suffix++;
        }

        return $key;
    }
}
