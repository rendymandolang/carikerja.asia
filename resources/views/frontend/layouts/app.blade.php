<!DOCTYPE html>
<html lang="id">
<head>
    @php
        $pageTitle = trim($__env->yieldContent('title', config('seo.title')));
        $metaDescription = trim($__env->yieldContent('meta_description', config('seo.description')));
        $canonicalUrl = trim($__env->yieldContent('canonical', url()->current()));
        $metaRobots = trim($__env->yieldContent('meta_robots', request()->attributes->get('seo_robots', 'index,follow')));
        $ogImage = trim($__env->yieldContent('og_image', asset(config('seo.image'))));
    @endphp
    <meta charset="UTF-8">
    <title>{{ $pageTitle }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="keywords" content="{{ config('seo.keywords') }}">
    <meta name="robots" content="{{ $metaRobots }}">
    @if (config('seo.google_site_verification'))
        <meta name="google-site-verification" content="{{ config('seo.google_site_verification') }}">
    @endif
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <meta property="og:site_name" content="{{ config('seo.site_name') }}">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css" rel="stylesheet">

    <style>
        body {
            background: #f8fafc;
            color: #111214;
            font-family: Inter, Arial, sans-serif;
        }

        .site-nav {
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            text-decoration: none;
            letter-spacing: 0;
        }

        .brand-logo {
            display: block;
            width: auto;
            height: 32px;
        }

        .btn-primary {
            --bs-btn-bg: #f97300;
            --bs-btn-border-color: #f97300;
            --bs-btn-hover-bg: #d95f00;
            --bs-btn-hover-border-color: #d95f00;
            --bs-btn-active-bg: #d95f00;
            --bs-btn-active-border-color: #d95f00;
        }

        .btn-outline-primary {
            --bs-btn-color: #d95f00;
            --bs-btn-border-color: #f97300;
            --bs-btn-hover-bg: #f97300;
            --bs-btn-hover-border-color: #f97300;
            --bs-btn-active-bg: #d95f00;
            --bs-btn-active-border-color: #d95f00;
        }

        .hero {
            background: #111214;
            color: #ffffff;
            border-radius: 0 0 8px 8px;
        }

        .job-card {
            border: 0;
            border-radius: 8px;
            box-shadow: 0 16px 40px rgba(15, 23, 42, .07);
            transition: .18s ease;
        }

        .job-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 22px 55px rgba(15, 23, 42, .11);
        }

        .pill {
            display: inline-flex;
            align-items: center;
            padding: 6px 10px;
            border-radius: 999px;
            background: #fff1e4;
            color: #9a4a00;
            font-size: 13px;
            font-weight: 600;
        }

        .muted-box {
            background: #f1f5f9;
            border-radius: 8px;
            padding: 18px;
        }

        .content-card {
            border: 0;
            border-radius: 8px;
            box-shadow: 0 16px 40px rgba(15, 23, 42, .07);
        }

        .preline {
            white-space: pre-line;
        }

        .footer {
            color: #64748b;
            border-top: 1px solid #e5e7eb;
        }
    </style>
    <link href="{{ asset('css/carikerja-soft-ui.css') }}?v=20260623" rel="stylesheet">

    @yield('head')
</head>
<body>
<nav class="site-nav py-3">
    <div class="container d-flex justify-content-between align-items-center">
        <a href="{{ route('landing') }}" class="brand fs-4">
            <img src="{{ asset('images/carikerja-logo.png') }}?v=20260629" alt="carikerja.asia" class="brand-logo" width="146" height="32">
        </a>

        <div class="d-flex gap-2">
            <a href="{{ route('jobs.index') }}" class="btn btn-outline-primary btn-sm">Lowongan</a>
            <a href="{{ route('companies.index') }}" class="btn btn-outline-primary btn-sm">Perusahaan</a>
            <a href="{{ route('landing') }}#waitlist" class="btn btn-primary btn-sm">Daftar</a>
        </div>
    </div>
</nav>

@yield('content')

<footer class="footer py-4 mt-5">
    <div class="container d-flex flex-column flex-md-row justify-content-between gap-2">
        <div>&copy; {{ date('Y') }} carikerja.asia</div>
        <div class="d-flex flex-wrap gap-3">
            <a href="{{ route('legal.privacy') }}" class="text-muted text-decoration-none">Privacy</a>
            <a href="{{ route('legal.terms') }}" class="text-muted text-decoration-none">Terms</a>
            <a href="{{ route('legal.cookies') }}" class="text-muted text-decoration-none">Cookies</a>
        </div>
    </div>
</footer>
</body>
</html>
