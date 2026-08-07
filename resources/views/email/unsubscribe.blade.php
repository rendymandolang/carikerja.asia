<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribe - carikerja.asia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/carikerja-soft-ui.css') }}?v=20260623" rel="stylesheet">
</head>
<body class="bg-light">
<main class="container py-5" style="max-width: 640px;">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4 p-md-5">
            <img src="{{ asset('images/carikerja-logo.png') }}" alt="carikerja.asia" style="height:34px;width:auto;" class="mb-4">
            <h1 class="h3 mb-3">Email marketing dihentikan</h1>
            <p class="text-muted mb-0">
                {{ $unsubscribe->email }} tidak akan menerima email marketing dari carikerja.asia lagi. Email penting terkait akun, lamaran, keamanan, dan interview tetap dapat dikirim bila diperlukan.
            </p>
        </div>
    </div>
</main>
</body>
</html>
