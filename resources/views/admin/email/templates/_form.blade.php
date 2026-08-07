@if ($template->category === 'marketing' || ! $template->exists)
    <div class="mb-3">
        <label class="form-label">Template Name</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $template->name) }}" required>
    </div>
@endif

<div class="mb-3">
    <label class="form-label">Subject</label>
    <input type="text" name="subject" class="form-control" value="{{ old('subject', $template->subject) }}" required>
</div>

<div class="mb-3">
    <label class="form-label">Preheader</label>
    <input type="text" name="preheader" class="form-control" value="{{ old('preheader', $template->preheader) }}">
</div>

<div class="mb-3">
    <label class="form-label">Body</label>
    <textarea name="body" rows="12" class="form-control" required>{{ old('body', $template->body) }}</textarea>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Button Label</label>
        <input type="text" name="button_label" class="form-control" value="{{ old('button_label', $template->button_label) }}">
    </div>
    <div class="col-md-8 mb-3">
        <label class="form-label">Button URL</label>
        <input type="text" name="button_url" class="form-control" value="{{ old('button_url', $template->button_url) }}">
    </div>
</div>

<div class="form-check form-switch mb-4">
    <input type="hidden" name="is_active" value="0">
    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $template->is_active))>
    <label class="form-check-label" for="is_active">Active</label>
</div>

<div class="d-flex gap-2">
    <button class="btn btn-primary">{{ $template->exists ? 'Save Template' : 'Create Template' }}</button>
    <a href="{{ route('admin.email.templates.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
