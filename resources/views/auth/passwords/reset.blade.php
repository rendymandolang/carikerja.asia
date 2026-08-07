<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} - carikerja.asia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/carikerja-soft-ui.css') }}?v=20260623" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: #0f172a;
            display: grid;
            place-items: center;
            font-family: Inter, Arial, sans-serif;
        }

        .reset-card {
            width: min(460px, calc(100vw - 32px));
            background: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 24px 80px rgba(0, 0, 0, .28);
        }
    </style>
</head>
<body>
<div class="reset-card">
    <div class="fw-bold fs-3 mb-1">{{ ucfirst($portal) }} Reset</div>
    <p class="text-muted mb-4">Buat password baru untuk akun Anda.</p>

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ $submitRoute }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $email) }}" required autofocus>
        </div>

        <div class="mb-3">
            <label class="form-label">New Password</label>
            <input type="password" name="password" class="form-control" required autocomplete="new-password">
            <div class="text-muted small">Minimal 10 karakter, huruf besar/kecil, angka, dan simbol.</div>
        </div>

        <div class="mb-4">
            <label class="form-label">Confirm New Password</label>
            <input type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
        </div>

        <button class="btn btn-primary w-100">Reset Password</button>
    </form>

    <div class="text-center mt-4">
        <a href="{{ $loginRoute }}">Back to login</a>
    </div>
</div>
</body>
</html>
