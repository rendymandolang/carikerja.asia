<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Recruiter Portal') - carikerja.asia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css" rel="stylesheet">
    <style>
        body {
            background: #f6f8fb;
            color: #1f2937;
        }

        .portal-nav {
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
        }

        .portal-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
        }

        .brand {
            color: #0f172a;
            font-weight: 800;
            letter-spacing: 0;
            text-decoration: none;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
        }
    </style>
    <link href="{{ asset('css/carikerja-soft-ui.css') }}?v=20260623" rel="stylesheet">
</head>
<body>
    <nav class="portal-nav py-3 mb-4">
        <div class="container d-flex flex-wrap gap-3 justify-content-between align-items-center">
            <a href="{{ route('recruiter.dashboard') }}" class="brand fs-4">
                carikerja.asia Recruiter
            </a>

            <div class="d-flex flex-wrap gap-2 align-items-center">
                @php
                    $currentUser = auth()->user();
                    $activeCompanyIds = $currentUser
                        ? $currentUser->companies()->wherePivot('status', 'active')->pluck('companies.id')->all()
                        : [];
                    $unreadNotifications = $currentUser?->unreadNotifications()->count() ?? 0;
                    $unreadMessages = \App\Models\ApplicationMessage::query()
                        ->whereIn('company_id', $activeCompanyIds)
                        ->where('sender_role', '!=', 'recruiter')
                        ->whereNull('read_by_recruiter_at')
                        ->count();
                @endphp
                <a href="{{ route('recruiter.dashboard') }}" class="btn btn-sm {{ request()->routeIs('recruiter.dashboard') ? 'btn-primary' : 'btn-outline-primary' }}">Dashboard</a>
                <a href="{{ route('recruiter.jobs.index') }}" class="btn btn-sm {{ request()->routeIs('recruiter.jobs.*') ? 'btn-primary' : 'btn-outline-primary' }}">Jobs</a>
                <a href="{{ route('recruiter.applications.index') }}" class="btn btn-sm {{ request()->routeIs('recruiter.applications.*') ? 'btn-primary' : 'btn-outline-primary' }}">
                    Applications
                    @if ($unreadMessages > 0)
                        <span class="badge text-bg-danger ms-1">{{ $unreadMessages }}</span>
                    @endif
                </a>
                <a href="{{ route('recruiter.notifications.index') }}" class="btn btn-sm {{ request()->routeIs('recruiter.notifications.*') ? 'btn-primary' : 'btn-outline-primary' }}">
                    Notifications
                    @if ($unreadNotifications > 0)
                        <span class="badge text-bg-danger ms-1">{{ $unreadNotifications }}</span>
                    @endif
                </a>
                <a href="{{ route('recruiter.account.security.edit') }}" class="btn btn-sm {{ request()->routeIs('recruiter.account.security.*') ? 'btn-primary' : 'btn-outline-primary' }}">Account</a>

                <form method="POST" action="{{ route('recruiter.logout') }}">
                    @csrf
                    <button class="btn btn-sm btn-outline-secondary">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <main class="container pb-5">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if (session('warning'))
            <div class="alert alert-warning">{{ session('warning') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        @yield('content')
    </main>
</body>
</html>
