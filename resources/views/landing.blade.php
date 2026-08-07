<!DOCTYPE html>
<html lang="id">
<head>
    @php
        $pageTitle = config('seo.title');
        $metaDescription = config('seo.description');
        $canonicalUrl = route('landing');
        $ogImage = asset(config('seo.image'));
        $organizationSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => 'carikerja.asia',
            'url' => route('landing'),
            'logo' => asset(config('seo.image')),
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'email' => config('seo.contact_email'),
                'contactType' => 'customer support',
            ],
        ];
        $websiteSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => 'carikerja.asia',
            'url' => route('landing'),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => route('jobs.index') . '?q={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ];
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="keywords" content="{{ config('seo.keywords') }}">
    <meta name="robots" content="index,follow">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <meta property="og:site_name" content="{{ config('seo.site_name') }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    <link href="{{ asset('css/carikerja-soft-ui.css') }}?v=20260623" rel="stylesheet">

    <script type="application/ld+json">
        {!! json_encode($organizationSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    <script type="application/ld+json">
        {!! json_encode($websiteSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>

    <style>
        :root {
            --page-bg: #f7f8fa;
            --surface: #ffffff;
            --surface-soft: #fff8f1;
            --ink: #111214;
            --muted: #626a73;
            --line: #e6e8eb;
            --brand: #f97300;
            --brand-dark: #d95f00;
            --brand-soft: #fff1e4;
            --success: #087443;
            --success-soft: #ecfdf3;
            --danger: #b42318;
            --danger-soft: #fff1f0;
            --shadow: 0 18px 42px rgba(17, 18, 20, .07);
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body.landing-page {
            margin: 0;
            background: var(--page-bg);
            color: var(--ink);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
            letter-spacing: 0;
        }

        a {
            color: inherit;
        }

        .landing-container {
            width: min(1120px, calc(100% - 36px));
            margin: 0 auto;
        }

        .landing-topbar {
            position: sticky;
            top: 0;
            z-index: 30;
            border-bottom: 1px solid rgba(230, 232, 235, .86);
            background: rgba(255, 255, 255, .94);
            backdrop-filter: blur(14px);
        }

        .landing-nav {
            min-height: 72px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
        }

        .landing-brand {
            display: inline-flex;
            align-items: center;
            min-width: 0;
            text-decoration: none;
        }

        .landing-logo {
            display: block;
            width: auto;
            height: 34px;
        }

        .landing-actions,
        .hero-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .landing-link,
        .landing-button {
            min-height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            padding: 0 14px;
            font-size: 14px;
            font-weight: 800;
            line-height: 1;
            text-decoration: none;
            white-space: nowrap;
        }

        .landing-link {
            color: #3f464d;
            border: 1px solid transparent;
        }

        .landing-link:hover {
            color: var(--brand-dark);
            background: var(--brand-soft);
        }

        .landing-button {
            border: 1px solid #d9dde2;
            color: var(--ink);
            background: var(--surface);
        }

        .landing-button:hover {
            border-color: #c7ccd1;
            background: #f9fafb;
        }

        .landing-button.primary {
            border-color: var(--brand);
            color: #ffffff;
            background: var(--brand);
        }

        .landing-button.primary:hover {
            border-color: var(--brand-dark);
            background: var(--brand-dark);
        }

        .hero-band {
            position: relative;
            overflow: hidden;
            border-bottom: 1px solid var(--line);
            background: var(--surface);
        }

        .hero-band::before {
            content: "";
            position: absolute;
            right: -210px;
            bottom: 18px;
            width: 760px;
            height: 166px;
            background: url("{{ asset('images/carikerja-logo.png') }}?v=20260629") center / contain no-repeat;
            opacity: .055;
            pointer-events: none;
        }

        .hero-inner {
            position: relative;
            z-index: 1;
            max-width: 820px;
            padding: 76px 0 58px;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            margin-bottom: 18px;
            border: 1px solid #ffd7b1;
            border-radius: 999px;
            padding: 8px 12px;
            background: var(--brand-soft);
            color: #9a4a00;
            font-size: 13px;
            font-weight: 900;
        }

        .hero-title {
            margin: 0;
            max-width: 780px;
            color: var(--ink);
            font-size: 64px;
            line-height: 1.03;
            font-weight: 950;
        }

        .hero-title span {
            color: var(--brand);
        }

        .hero-copy {
            max-width: 700px;
            margin: 20px 0 28px;
            color: #4f5861;
            font-size: 20px;
            line-height: 1.65;
        }

        .hero-actions {
            margin-bottom: 34px;
        }

        .entry-actions {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
            margin: 0 0 24px;
        }

        .entry-actions .landing-button {
            min-height: 48px;
            text-align: center;
        }

        .signal-row {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            max-width: 820px;
        }

        .signal-item {
            min-height: 108px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #fbfcfd;
            padding: 18px;
        }

        .signal-item strong {
            display: block;
            margin-bottom: 7px;
            color: var(--ink);
            font-size: 15px;
        }

        .signal-item span {
            color: var(--muted);
            font-size: 14px;
            line-height: 1.5;
        }

        .section {
            padding: 58px 0;
        }

        .section.white {
            border-top: 1px solid var(--line);
            border-bottom: 1px solid var(--line);
            background: var(--surface);
        }

        .section-kicker {
            margin: 0 0 10px;
            color: var(--brand-dark);
            font-size: 13px;
            font-weight: 900;
        }

        .section-heading {
            max-width: 760px;
            margin: 0;
            color: var(--ink);
            font-size: 38px;
            line-height: 1.14;
            font-weight: 930;
        }

        .section-desc {
            max-width: 720px;
            margin: 14px 0 28px;
            color: var(--muted);
            font-size: 17px;
            line-height: 1.65;
        }

        .feature-grid,
        .waitlist-grid {
            display: grid;
            gap: 16px;
        }

        .feature-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .waitlist-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            align-items: start;
        }

        .feature-card,
        .signup-panel {
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--surface);
            box-shadow: 0 1px 2px rgba(17, 18, 20, .04);
        }

        .feature-card {
            min-height: 168px;
            padding: 22px;
        }

        .feature-card mark {
            display: inline-flex;
            margin-bottom: 16px;
            border-radius: 999px;
            padding: 6px 10px;
            background: var(--brand-soft);
            color: #9a4a00;
            font-size: 12px;
            font-weight: 900;
        }

        .feature-card strong,
        .signup-panel h2 {
            display: block;
            margin: 0 0 9px;
            color: var(--ink);
            font-size: 20px;
            line-height: 1.25;
            font-weight: 900;
        }

        .feature-card p,
        .signup-panel p {
            margin: 0;
            color: var(--muted);
            line-height: 1.58;
        }

        .signup-panel {
            padding: 24px;
        }

        .signup-panel p {
            margin-bottom: 18px;
        }

        .field-grid {
            display: grid;
            gap: 12px;
        }

        .landing-input,
        .landing-textarea {
            width: 100%;
            min-height: 48px;
            border: 1px solid #d9dde2;
            border-radius: 8px;
            background: #ffffff;
            color: var(--ink);
            font: inherit;
            padding: 12px 13px;
        }

        .landing-textarea {
            min-height: 94px;
            resize: vertical;
        }

        .landing-input:focus,
        .landing-textarea:focus {
            outline: none;
            border-color: var(--brand);
            box-shadow: 0 0 0 .2rem rgba(249, 115, 0, .14);
        }

        .form-submit {
            width: 100%;
            min-height: 48px;
            border: 0;
            border-radius: 8px;
            color: #ffffff;
            background: var(--ink);
            font: inherit;
            font-weight: 900;
            cursor: pointer;
        }

        .form-submit.primary {
            background: var(--brand);
        }

        .form-submit:hover {
            filter: brightness(.96);
        }

        .alert-box {
            margin: 24px 0 0;
            border-radius: 8px;
            padding: 14px 16px;
            line-height: 1.5;
        }

        .alert-box.success {
            border: 1px solid #abefc6;
            background: var(--success-soft);
            color: var(--success);
        }

        .alert-box.error {
            border: 1px solid #fecdca;
            background: var(--danger-soft);
            color: var(--danger);
        }

        .alert-box ul {
            margin: 8px 0 0;
            padding-left: 20px;
        }

        .landing-footer {
            border-top: 1px solid var(--line);
            background: var(--surface);
            color: var(--muted);
            padding: 24px 0;
            font-size: 14px;
        }

        .footer-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .footer-links {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }

        .footer-links a {
            color: var(--muted);
            text-decoration: none;
        }

        .footer-links a:hover {
            color: var(--brand-dark);
        }

        @media (max-width: 920px) {
            .landing-nav {
                min-height: auto;
                align-items: flex-start;
                flex-direction: column;
                padding: 16px 0;
            }

            .landing-actions {
                width: 100%;
            }

            .hero-title {
                font-size: 52px;
            }

            .signal-row,
            .feature-grid,
            .waitlist-grid {
                grid-template-columns: 1fr;
            }

            .entry-actions {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .hero-band::before {
                right: -280px;
                bottom: 34px;
            }
        }

        @media (max-width: 640px) {
            .landing-container {
                width: min(100% - 28px, 1120px);
            }

            .landing-nav {
                gap: 12px;
                padding: 12px 0;
            }

            .landing-logo {
                height: 30px;
            }

            .landing-actions {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 6px;
            }

            .landing-link,
            .landing-button {
                flex: none;
                min-height: 36px;
                padding: 0 6px;
                font-size: 12px;
            }

            .hero-inner {
                padding: 42px 0 38px;
            }

            .hero-title {
                font-size: 42px;
            }

            .hero-copy {
                font-size: 17px;
            }

            .entry-actions {
                grid-template-columns: 1fr;
            }

            .section {
                padding: 44px 0;
            }

            .section-heading {
                font-size: 30px;
            }

            .footer-row {
                flex-direction: column;
            }
        }
    </style>
</head>
<body class="landing-page">
<header class="landing-topbar">
    <div class="landing-container landing-nav">
        <a href="{{ route('landing') }}" class="landing-brand" aria-label="carikerja.asia">
            <img src="{{ asset('images/carikerja-logo.png') }}?v=20260629" alt="carikerja.asia" class="landing-logo" width="164" height="36">
        </a>

        <nav class="landing-actions" aria-label="Navigasi utama">
            <a href="{{ route('jobs.index') }}" class="landing-link">Lowongan</a>
            <a href="{{ route('candidate.login') }}" class="landing-link">Kandidat</a>
            <a href="{{ route('recruiter.login') }}" class="landing-link">Recruiter</a>
            <a href="#mulai" class="landing-button primary">Mulai</a>
        </nav>
    </div>
</header>

<main>
    <section class="hero-band">
        <div class="landing-container">
            @if (session('success'))
                <div class="alert-box success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert-box error">
                    <strong>Mohon cek kembali data Anda.</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="hero-inner">
                <div class="eyebrow">Hiring lebih jelas, dari apply sampai interview</div>
                <h1 class="hero-title">Cari kerja <span>tanpa digantung.</span></h1>
                <p class="hero-copy">
                    Temukan lowongan dengan proses yang lebih transparan. Kandidat dapat memantau perkembangan lamaran, sementara recruiter mengelola hiring dalam satu alur yang rapi.
                </p>

                <div class="hero-actions">
                    <a href="{{ route('jobs.index') }}" class="landing-button primary">Cari Lowongan</a>
                    <a href="#candidate-start" class="landing-button">Buat Profil Kandidat</a>
                </div>

                <div class="signal-row" aria-label="Keunggulan carikerja.asia">
                    <div class="signal-item">
                        <strong>Status lamaran jelas</strong>
                        <span>Kandidat bisa memantau progres tanpa menunggu kabar yang tidak pasti.</span>
                    </div>
                    <div class="signal-item">
                        <strong>Recruiter lebih rapi</strong>
                        <span>Job, kandidat, interview, dan pesan dikelola dalam satu alur.</span>
                    </div>
                    <div class="signal-item">
                        <strong>Resume siap dicocokkan</strong>
                        <span>Profil kandidat dibuat lebih mudah dibaca untuk kebutuhan hiring.</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section white">
        <div class="landing-container">
            <p class="section-kicker">Core platform</p>
            <h2 class="section-heading">Dibangun sebagai job platform dengan fondasi ATS ringan.</h2>
            <p class="section-desc">
                Fokusnya sederhana: kandidat tahu posisi mereka, recruiter punya data yang tertata, dan komunikasi hiring tidak hilang di tengah proses.
            </p>

            <div class="feature-grid">
                <div class="feature-card">
                    <mark>Kandidat</mark>
                    <strong>Portal kandidat</strong>
                    <p>Login email, resume center, status lamaran, jadwal interview, dan notifikasi.</p>
                </div>
                <div class="feature-card">
                    <mark>Recruiter</mark>
                    <strong>Pipeline lamaran</strong>
                    <p>Review kandidat, update status, kirim pesan, dan jadwalkan interview dari dashboard.</p>
                </div>
                <div class="feature-card">
                    <mark>Perusahaan</mark>
                    <strong>Hiring lebih profesional</strong>
                    <p>Bangun pengalaman kandidat yang lebih jelas, cepat, dan dapat dipertanggungjawabkan.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="mulai" class="section">
        <div class="landing-container">
            <p class="section-kicker">Mulai sekarang</p>
            <h2 class="section-heading">Cari peluang atau bangun tim bersama carikerja.asia.</h2>
            <p class="section-desc">
                Kandidat dapat mencari lowongan dan membangun profil awal. Recruiter serta perusahaan dapat mengajukan kerja sama untuk memasang lowongan dan mengelola proses hiring.
            </p>

            <div class="entry-actions" aria-label="Pilihan utama carikerja.asia">
                <a href="{{ route('jobs.index') }}" class="landing-button primary">Cari Lowongan</a>
                <a href="#candidate-start" class="landing-button">Buat Profil Kandidat</a>
                <a href="#company-start" class="landing-button">Pasang Lowongan</a>
                <a href="{{ route('recruiter.login') }}" class="landing-button">Masuk sebagai Recruiter</a>
            </div>

            <div class="waitlist-grid">
                <div class="signup-panel" id="candidate-start">
                    <h2>Buat profil kandidat</h2>
                    <p>Daftarkan minat kerja Anda agar kami dapat menghubungkan profil dengan lowongan yang relevan.</p>

                    <form method="POST" action="{{ route('waitlist.store') }}" class="field-grid">
                        @csrf
                        <input type="hidden" name="type" value="candidate">

                        <input type="text" name="full_name" class="landing-input" placeholder="Nama lengkap" value="{{ old('full_name') }}" required>
                        <input type="email" name="email" class="landing-input" placeholder="Email aktif" value="{{ old('email') }}" required>
                        <input type="text" name="linkedin_url" class="landing-input" placeholder="LinkedIn profile URL" value="{{ old('linkedin_url') }}" required>
                        <input type="text" name="target_role" class="landing-input" placeholder="Posisi yang dicari" value="{{ old('target_role') }}">

                        <button type="submit" class="form-submit primary">Kirim profil kandidat</button>
                    </form>
                </div>

                <div class="signup-panel" id="company-start">
                    <h2>Kerja sama recruiter / perusahaan</h2>
                    <p>Ajukan kerja sama untuk memasang lowongan dan mengelola proses hiring melalui carikerja.asia.</p>

                    <form method="POST" action="{{ route('waitlist.store') }}" class="field-grid">
                        @csrf
                        <input type="hidden" name="type" value="recruiter">

                        <input type="text" name="contact_name" class="landing-input" placeholder="Nama PIC / HR" value="{{ old('contact_name') }}" required>
                        <input type="text" name="company_name" class="landing-input" placeholder="Nama perusahaan" value="{{ old('company_name') }}" required>
                        <input type="email" name="company_email" class="landing-input" placeholder="Email perusahaan" value="{{ old('company_email') }}" required>
                        <input type="text" name="position" class="landing-input" placeholder="Jabatan Anda" value="{{ old('position') }}">
                        <textarea name="notes" class="landing-textarea" placeholder="Kebutuhan awal">{{ old('notes') }}</textarea>

                        <button type="submit" class="form-submit">Ajukan kerja sama</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>

<footer class="landing-footer">
    <div class="landing-container footer-row">
        <div>&copy; {{ date('Y') }} carikerja.asia</div>
        <div class="footer-links">
            <a href="{{ route('legal.privacy') }}">Privacy</a>
            <a href="{{ route('legal.terms') }}">Terms</a>
            <a href="{{ route('legal.cookies') }}">Cookies</a>
        </div>
    </div>
</footer>
</body>
</html>
