<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin Login &mdash; carikerja.asia</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(37,99,235,.35), transparent 35%),
                radial-gradient(circle at bottom right, rgba(34,197,94,.25), transparent 35%),
                #0f172a;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Inter, Arial, sans-serif;
        }

        .login-card {
            width: min(420px, 92%);
            background: #fff;
            border-radius: 8px;
            padding: 32px;
            box-shadow: 0 25px 80px rgba(0,0,0,.35);
        }

        .brand {
            font-weight: 800;
            font-size: 26px;
            color: #0f172a;
        }
    </style>
    <link href="{{ asset('css/carikerja-soft-ui.css') }}?v=20260623" rel="stylesheet">
</head>
<body>
<div class="login-card">
    <div class="brand mb-1">carikerja.asia</div>
    <p class="text-muted mb-4">Admin Dashboard Login</p>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.login.submit') }}">
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

        <button class="btn btn-primary w-100">Login Admin</button>
    </form>

    <div class="text-center mt-4">
        <a href="{{ route('admin.password.request') }}">Forgot password?</a>
    </div>
</div>
</body>
</html>
