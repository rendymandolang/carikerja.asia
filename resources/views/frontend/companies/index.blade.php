@extends('frontend.layouts.app')

@section('title', 'Perusahaan yang Sedang Merekrut - carikerja.asia')
@section('meta_description', 'Temukan perusahaan aktif yang sedang membuka lowongan dengan proses rekrutmen lebih transparan di carikerja.asia.')
@section('canonical', route('companies.index'))

@section('content')
<section class="hero py-5 mb-5"><div class="container py-4"><h1 class="display-5 fw-bold">Perusahaan yang sedang merekrut</h1><p class="lead text-white-50 mb-0">Profil perusahaan nyata dan lowongan yang masih aktif.</p></div></section>
<main class="container">
    <div class="row g-4">
        @forelse ($companies as $company)
            <div class="col-md-6 col-lg-4"><article class="card content-card h-100"><div class="card-body p-4">
                <h2 class="h4"><a class="text-decoration-none text-dark" href="{{ route('companies.show', $company) }}">{{ $company->company_name }}</a></h2>
                <p class="text-muted mb-3">{{ $company->industry ?: 'Informasi industri segera tersedia' }}</p>
                <span class="pill">{{ $company->open_jobs_count }} lowongan aktif</span>
                @if ($company->is_verified)<span class="badge bg-success ms-2">Terverifikasi</span>@endif
            </div></article></div>
        @empty
            <div class="col-12"><div class="card content-card"><div class="card-body p-5 text-center"><h2 class="h4">Belum ada perusahaan dengan lowongan aktif.</h2><p class="text-muted mb-0">Daftar ini akan tampil setelah lowongan pertama dipublikasikan.</p></div></div></div>
        @endforelse
    </div>
    <div class="mt-4">{{ $companies->links() }}</div>
</main>
@endsection
