@extends('frontend.layouts.app')

@section('title', $company->company_name . ' - Lowongan Aktif | carikerja.asia')
@section('meta_description', 'Lihat profil dan lowongan aktif dari ' . $company->company_name . ' di carikerja.asia.')
@section('canonical', route('companies.show', $company))

@section('head')
<script type="application/ld+json">{!! json_encode(array_filter(['@context' => 'https://schema.org', '@type' => 'Organization', 'name' => $company->company_name, 'url' => route('companies.show', $company), 'sameAs' => $company->website]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endsection

@section('content')
<section class="hero py-5 mb-5"><div class="container py-4"><span class="pill mb-3">Perusahaan</span><h1 class="display-5 fw-bold">{{ $company->company_name }}</h1><p class="lead text-white-50 mb-0">{{ $company->industry ?: 'Sedang membuka peluang baru' }}@if($company->city) · {{ $company->city }}@endif</p></div></section>
<main class="container">
    <div class="card content-card mb-4"><div class="card-body p-4 d-flex flex-wrap gap-3 align-items-center">
        @if ($company->is_verified)<span class="badge bg-success">Perusahaan terverifikasi</span>@endif
        @if ($company->isActiveResponder())<span class="badge bg-info text-dark">Aktif merespons</span>@endif
        @if ($company->website)<a href="{{ $company->website }}" rel="nofollow noopener" target="_blank">Website perusahaan</a>@endif
    </div></div>
    <h2 class="h3 mb-4">Lowongan aktif</h2>
    <div class="row g-4">
        @foreach ($jobs as $job)
            <div class="col-md-6"><article class="card job-card h-100"><div class="card-body p-4"><h3 class="h4"><a class="text-decoration-none text-dark" href="{{ route('jobs.show', $job) }}">{{ $job->title }}</a></h3><p class="text-muted">{{ trim(($job->city ?: '') . ', ' . ($job->province ?: ''), ', ') ?: ($job->location ?: 'Lokasi fleksibel') }}</p><a class="btn btn-sm btn-primary" href="{{ route('jobs.show', $job) }}">Lihat lowongan</a></div></article></div>
        @endforeach
    </div>
    <div class="mt-4">{{ $jobs->links() }}</div>
</main>
@endsection
