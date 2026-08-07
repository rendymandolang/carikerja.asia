<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recruiter Login - carikerja.asia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: #0f172a;
            display: grid;
            place-items: center;
        }

        .login-card {
            width: min(440px, calc(100vw - 32px));
            border-radius: 8px;
            border: 0;
            box-shadow: 0 24px 80px rgba(0, 0, 0, 0.25);
        }
    </style>
    <link href="{{ asset('css/carikerja-soft-ui.css') }}?v=20260623" rel="stylesheet">
</head>
<body>
    <div class="card login-card">
        <div class="card-body p-4">
            <div class="fw-bold fs-3 mb-1">Recruiter Portal</div>
            <p class="text-muted mb-4">Login untuk mengelola jobs dan aplikasi kandidat company Anda.</p>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('recruiter.login.submit') }}">
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
                    <input type="checkbox" name="remember" value="1" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>

                <button class="btn btn-primary w-100">Login</button>
            </form>

            <div class="text-center mt-4">
                <a href="{{ route('recruiter.password.request') }}">Forgot password?</a>
            </div>
        </div>
    </div>
</body>
</html>
