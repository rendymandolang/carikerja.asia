<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Candidate Login - carikerja.asia</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            min-height: 100vh;
            background: #0f172a;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Inter, Arial, sans-serif;
        }

        .login-card {
            width: min(430px, 92%);
            background: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 24px 70px rgba(0, 0, 0, .28);
        }

        .google-button {
            border: 1px solid #d1d5db;
            background: #ffffff;
            color: #111827;
            font-weight: 600;
        }

        .google-mark {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: inline-grid;
            place-items: center;
            background: #f8fafc;
            color: #2563eb;
            font-weight: 800;
        }
    </style>
    <link href="{{ asset('css/carikerja-soft-ui.css') }}?v=20260623" rel="stylesheet">
</head>
<body>
<div class="login-card">
    <div class="fw-bold fs-3 mb-1">Candidate Portal</div>
    <p class="text-muted mb-4">Login untuk melihat status lamaran Anda.</p>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('candidate.login.submit') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" name="remember" value="1" id="remember">
            <label class="form-check-label" for="remember">Remember me</label>
        </div>

        <button class="btn btn-primary w-100">Login</button>
    </form>

    <div class="text-center mt-3">
        <a href="{{ route('candidate.password.request') }}">Forgot password?</a>
    </div>

    <div class="d-flex align-items-center gap-3 my-4">
        <hr class="flex-grow-1">
        <span class="text-muted small">atau</span>
        <hr class="flex-grow-1">
    </div>

    <a href="{{ route('candidate.login.google') }}" class="btn google-button w-100 d-flex align-items-center justify-content-center gap-2">
        <span class="google-mark">G</span>
        Login dengan Gmail
    </a>

    <div class="text-center mt-4">
        <a href="{{ route('jobs.index') }}">Browse jobs</a>
    </div>
</div>
</body>
</html>
