<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Admin Dashboard') &mdash; carikerja.asia</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css" rel="stylesheet">

    <style>
        body {
            background: #f4f7fb;
            font-family: Inter, Arial, sans-serif;
        }

        .admin-shell {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 260px;
            background: #0f172a;
            color: #e2e8f0;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            padding: 22px 18px;
        }

        .brand {
            font-size: 22px;
            font-weight: 800;
            color: #fff;
            margin-bottom: 28px;
        }

        .nav-title {
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 22px 0 10px;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #cbd5e1;
            text-decoration: none;
            padding: 11px 12px;
            border-radius: 8px;
            margin-bottom: 6px;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #1d4ed8;
            color: #fff;
        }

        .main {
            margin-left: 260px;
            width: calc(100% - 260px);
        }

        .topbar {
            height: 70px;
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .content {
            padding: 28px;
        }

        .stat-card,
        .table-card {
            border: 0;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, .06);
        }

        .table-card {
            overflow: hidden;
        }

        .badge-status {
            text-transform: capitalize;
        }

        @media (max-width: 900px) {
            .sidebar {
                position: relative;
                width: 100%;
            }

            .admin-shell {
                display: block;
            }

            .main {
                margin-left: 0;
                width: 100%;
            }
        }
    </style>
    <link href="{{ asset('css/carikerja-soft-ui.css') }}?v=20260623" rel="stylesheet">
</head>
<body>
<div class="admin-shell">
    <aside class="sidebar">
        <div class="brand">carikerja.asia</div>

        <div class="nav-title">Main</div>

        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="mdi mdi-view-dashboard"></i>
            Dashboard
        </a>

        <a href="{{ route('admin.waitlists.index') }}" class="{{ request()->routeIs('admin.waitlists.*') ? 'active' : '' }}">
            <i class="mdi mdi-account-multiple-plus"></i>
            Waitlists
        </a>

        <div class="nav-title">ATS Core</div>

        <a href="{{ route('admin.companies.index') }}" class="{{ request()->routeIs('admin.companies.*') ? 'active' : '' }}">
            <i class="mdi mdi-domain"></i>
            Companies
        </a>

        <a href="{{ route('admin.recruiters.index') }}" class="{{ request()->routeIs('admin.recruiters.*') ? 'active' : '' }}">
            <i class="mdi mdi-account-tie"></i>
            Recruiters
        </a>

        <a href="{{ route('admin.jobs.index') }}" class="{{ request()->routeIs('admin.jobs.*') ? 'active' : '' }}">
            <i class="mdi mdi-briefcase-outline"></i>
            Jobs
        </a>

        <a href="{{ route('admin.applications.index') }}" class="{{ request()->routeIs('admin.applications.*') ? 'active' : '' }}">
            <i class="mdi mdi-file-document-check-outline"></i>
            Applications
        </a>

        <div class="nav-title">Growth</div>

        <a href="{{ route('admin.email.index') }}" class="{{ request()->routeIs('admin.email.*') ? 'active' : '' }}">
            <i class="mdi mdi-email-newsletter"></i>
            Email Center
        </a>

        <div class="nav-title">Operations</div>

        <a href="{{ route('admin.system.index') }}" class="{{ request()->routeIs('admin.system.*') ? 'active' : '' }}">
            <i class="mdi mdi-server-security"></i>
            System
        </a>

        <div class="nav-title">Security</div>

        <a href="{{ route('admin.account.security.edit') }}" class="{{ request()->routeIs('admin.account.security.*') ? 'active' : '' }}">
            <i class="mdi mdi-shield-account-outline"></i>
            Account
        </a>
    </aside>

    <main class="main">
        <div class="topbar">
            <div>
                <strong>@yield('page_title', 'Dashboard')</strong>
                <div class="text-muted small">Integrated ATS & Talent Marketplace</div>
            </div>

            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button class="btn btn-outline-danger btn-sm">
                    <i class="mdi mdi-logout"></i> Logout
                </button>
            </form>
        </div>

        <div class="content">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            @yield('content')
        </div>
    </main>
</div>
</body>
</html>
