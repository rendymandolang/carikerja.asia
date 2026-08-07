@extends('frontend.layouts.app')

@section('title', $legalTitle . ' - carikerja.asia')
@section('meta_description', $legalTitle . ' ' . $platformName . ' untuk kandidat, recruiter, dan pengunjung platform.')
@section('canonical', url()->current())

@section('content')
<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="mb-4">
                <span class="pill mb-3">Legal</span>
                <h1 class="display-6 fw-bold mb-2">{{ $legalTitle }}</h1>
                <p class="text-muted mb-0">
                    Berlaku sejak {{ \Carbon\Carbon::parse($effectiveDate)->format('d M Y') }}.
                </p>
            </div>

            <div class="card content-card">
                <div class="card-body p-4 p-md-5 legal-content">
                    @yield('legal_content')
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@section('head')
    <style>
        .legal-content h2 {
            margin-top: 28px;
            margin-bottom: 12px;
            font-size: 22px;
            font-weight: 800;
        }

        .legal-content h2:first-child {
            margin-top: 0;
        }

        .legal-content p,
        .legal-content li {
            color: #4f5861;
            line-height: 1.75;
        }

        .legal-content ul {
            padding-left: 20px;
        }
    </style>
@endsection
