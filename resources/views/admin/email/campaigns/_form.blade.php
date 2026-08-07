@csrf

@if ($campaign->exists)
    @method('PUT')
@endif

@php
    $scheduledValue = old('scheduled_at', $campaign->scheduled_at?->format('Y-m-d\TH:i'));
    $templatePayload = $marketingTemplates->mapWithKeys(fn ($template) => [$template->id => [
        'name' => $template->name,
        'subject' => $template->subject,
        'preheader' => $template->preheader,
        'body' => $template->body,
        'button_label' => $template->button_label,
        'button_url' => $template->button_url,
    ]]);
@endphp

<div class="mb-3">
    <label class="form-label">Marketing Template</label>
    <select name="email_template_id" id="campaign-template" class="form-select">
        <option value="">Custom campaign</option>
        @foreach ($marketingTemplates as $template)
            <option value="{{ $template->id }}" @selected((int) old('email_template_id', $campaign->email_template_id) === $template->id)>
                {{ $template->name }}
            </option>
        @endforeach
    </select>
</div>

<div class="mb-3">
    <label class="form-label">Campaign Name</label>
    <input type="text" name="name" id="campaign-name" class="form-control" value="{{ old('name', $campaign->name) }}" required>
</div>

<div class="mb-3">
    <label class="form-label">Audience</label>
    <select name="audience" class="form-select" required>
        @foreach ($audiences as $key => $audience)
            <option value="{{ $key }}" @selected(old('audience', $campaign->audience) === $key)>
                {{ $audience['label'] }} ({{ $audience['count'] }} contacts)
            </option>
        @endforeach
    </select>
</div>

<div class="mb-3">
    <label class="form-label">Subject</label>
    <input type="text" name="subject" id="campaign-subject" class="form-control" value="{{ old('subject', $campaign->subject) }}" required>
</div>

<div class="mb-3">
    <label class="form-label">Preheader</label>
    <input type="text" name="preheader" id="campaign-preheader" class="form-control" value="{{ old('preheader', $campaign->preheader) }}">
</div>

<div class="mb-3">
    <label class="form-label">Body</label>
    <textarea name="body" id="campaign-body" rows="12" class="form-control" required>{{ old('body', $campaign->body) }}</textarea>
    <div class="form-text">Variables: <code>@{{ name }}</code>, <code>@{{ email }}</code>, <code>@{{ app_name }}</code>, <code>@{{ unsubscribe_url }}</code></div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Button Label</label>
        <input type="text" name="button_label" id="campaign-button-label" class="form-control" value="{{ old('button_label', $campaign->button_label) }}">
    </div>
    <div class="col-md-8 mb-3">
        <label class="form-label">Button URL</label>
        <input type="text" name="button_url" id="campaign-button-url" class="form-control" value="{{ old('button_url', $campaign->button_url) }}">
    </div>
</div>

<div class="mb-4">
    <label class="form-label">Scheduled At</label>
    <input type="datetime-local" name="scheduled_at" class="form-control" value="{{ $scheduledValue }}">
    <div class="form-text">Kosongkan jika campaign hanya akan dikirim manual.</div>
</div>

<div class="d-flex gap-2">
    <button class="btn btn-primary">{{ $campaign->exists ? 'Save Campaign' : 'Create Campaign' }}</button>
    <a href="{{ $campaign->exists ? route('admin.email.campaigns.show', $campaign) : route('admin.email.campaigns.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>

<script>
    (() => {
        const templates = @json($templatePayload);
        const select = document.getElementById('campaign-template');

        if (! select) {
            return;
        }

        select.addEventListener('change', () => {
            const template = templates[select.value];

            if (! template) {
                return;
            }

            const body = document.getElementById('campaign-body');
            const hasContent = body && body.value.trim().length > 0;

            if (hasContent && ! window.confirm('Gunakan isi template ini untuk mengganti konten campaign?')) {
                return;
            }

            document.getElementById('campaign-name').value = template.name || '';
            document.getElementById('campaign-subject').value = template.subject || '';
            document.getElementById('campaign-preheader').value = template.preheader || '';
            document.getElementById('campaign-body').value = template.body || '';
            document.getElementById('campaign-button-label').value = template.button_label || '';
            document.getElementById('campaign-button-url').value = template.button_url || '';
        });
    })();
</script>
