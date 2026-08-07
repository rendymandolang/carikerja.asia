<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Candidate Portal') - carikerja.asia</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css" rel="stylesheet">

    <style>
        body {
            background: #f8fafc;
            color: #0f172a;
            font-family: Inter, Arial, sans-serif;
        }

        .portal-nav {
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
        }

        .brand {
            font-weight: 900;
            color: #0f172a;
            text-decoration: none;
        }

        .brand span {
            color: #2563eb;
        }

        .portal-card {
            border: 0;
            border-radius: 8px;
            box-shadow: 0 12px 34px rgba(15, 23, 42, .07);
        }

        .status-pill {
            text-transform: capitalize;
        }
    </style>
    <link href="{{ asset('css/carikerja-soft-ui.css') }}?v=20260623" rel="stylesheet">
</head>
<body>
<nav class="portal-nav py-3">
    <div class="container d-flex flex-wrap justify-content-between align-items-center gap-3">
        <a href="{{ route('candidate.dashboard') }}" class="brand fs-4">
            carikerja<span>.asia</span>
        </a>

        <div class="d-flex flex-wrap align-items-center gap-2">
            @php
                $currentUser = auth()->user();
                $candidateProfile = $currentUser?->candidateProfile;
                $unreadNotifications = $currentUser?->unreadNotifications()->count() ?? 0;
                $unreadMessages = $candidateProfile
                    ? \App\Models\ApplicationMessage::query()
                        ->where('candidate_profile_id', $candidateProfile->id)
                        ->where('sender_role', '!=', 'candidate')
                        ->whereNull('read_by_candidate_at')
                        ->count()
                    : 0;
            @endphp
            <a href="{{ route('candidate.dashboard') }}" class="btn btn-sm {{ request()->routeIs('candidate.dashboard') ? 'btn-primary' : 'btn-outline-primary' }}">Dashboard</a>
            <a href="{{ route('candidate.profile.edit') }}" class="btn btn-sm {{ request()->routeIs('candidate.profile.*') ? 'btn-primary' : 'btn-outline-primary' }}">Resume Center</a>
            <a href="{{ route('candidate.job-matches.index') }}" class="btn btn-sm {{ request()->routeIs('candidate.job-matches.*') ? 'btn-primary' : 'btn-outline-primary' }}">Matches</a>
            <a href="{{ route('candidate.interviews.index') }}" class="btn btn-sm {{ request()->routeIs('candidate.interviews.*') ? 'btn-primary' : 'btn-outline-primary' }}">Interviews</a>
            <a href="{{ route('candidate.applications.index') }}" class="btn btn-sm {{ request()->routeIs('candidate.applications.*') ? 'btn-primary' : 'btn-outline-primary' }}">
                Applications
                @if ($unreadMessages > 0)
                    <span class="badge text-bg-danger ms-1">{{ $unreadMessages }}</span>
                @endif
            </a>
            <a href="{{ route('candidate.notifications.index') }}" class="btn btn-sm {{ request()->routeIs('candidate.notifications.*') ? 'btn-primary' : 'btn-outline-primary' }}">
                Notifications
                @if ($unreadNotifications > 0)
                    <span class="badge text-bg-danger ms-1">{{ $unreadNotifications }}</span>
                @endif
            </a>
            <a href="{{ route('candidate.account.security.edit') }}" class="btn btn-sm {{ request()->routeIs('candidate.account.security.*') ? 'btn-primary' : 'btn-outline-primary' }}">Account</a>
            <a href="{{ route('jobs.index') }}" class="btn btn-sm btn-outline-secondary">Browse Jobs</a>
            <form method="POST" action="{{ route('candidate.logout') }}">
                @csrf
                <button class="btn btn-sm btn-outline-danger">Logout</button>
            </form>
        </div>
    </div>
</nav>

<main class="container py-4">
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    @yield('content')
</main>
</body>
</html>
