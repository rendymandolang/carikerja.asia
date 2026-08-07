<div class="card table-card">
    <div class="card-header bg-white"><strong>Variables</strong></div>
    <div class="card-body">
        <p class="text-muted small">Gunakan format placeholder berikut di subject, body, atau button URL.</p>
        <div class="d-flex flex-wrap gap-2">
            @foreach (($template->variables ?: []) as $variable)
                <code class="bg-light border rounded px-2 py-1">&#123;&#123; {{ $variable }} &#125;&#125;</code>
            @endforeach
        </div>
    </div>
</div>
