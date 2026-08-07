@extends('legal.layout')

@section('legal_content')
    <h2>1. Penerimaan Ketentuan</h2>
    <p>Dengan mengakses atau menggunakan {{ $platformName }}, Anda setuju untuk mengikuti syarat dan ketentuan ini. Jika Anda tidak setuju, mohon tidak menggunakan layanan.</p>

    <h2>2. Layanan Platform</h2>
    <p>{{ $platformName }} menyediakan job portal, candidate portal, recruiter dashboard, ATS ringan, komunikasi lamaran, interview scheduling, notifikasi, dan fitur pendukung hiring. Fitur dapat berubah sesuai pengembangan produk.</p>

    <h2>3. Akun Pengguna</h2>
    <p>Pengguna bertanggung jawab atas kebenaran data, keamanan akun, penggunaan password, dan seluruh aktivitas yang terjadi melalui akun masing-masing. Akun tidak boleh digunakan untuk penipuan, spam, scraping, pelanggaran hukum, atau aktivitas yang merugikan pengguna lain.</p>

    <h2>4. Lowongan dan Lamaran</h2>
    <p>Recruiter bertanggung jawab memastikan informasi lowongan benar, tidak menyesatkan, dan sesuai hukum ketenagakerjaan yang berlaku. Kandidat bertanggung jawab atas keakuratan profil, resume, dan informasi lamaran yang dikirim.</p>

    <h2>5. Komunikasi dan Transparansi</h2>
    <p>Platform dirancang untuk membantu komunikasi yang lebih jelas antara kandidat dan recruiter. Namun, keputusan hiring, jadwal, feedback, dan tindakan recruiter tetap menjadi tanggung jawab perusahaan masing-masing.</p>

    <h2>6. Pembatasan Tanggung Jawab</h2>
    <p>{{ $platformName }} tidak menjamin kandidat pasti diterima kerja, lowongan selalu tersedia, atau proses hiring bebas perubahan. Layanan disediakan sebagaimana adanya dengan upaya wajar untuk menjaga stabilitas dan keamanan.</p>

    <h2>7. Penghentian Akses</h2>
    <p>Kami dapat membatasi atau menonaktifkan akun yang melanggar ketentuan, membahayakan sistem, atau menyalahgunakan layanan.</p>

    <h2>8. Kontak</h2>
    <p>Untuk pertanyaan terkait ketentuan layanan, hubungi <a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a>.</p>
@endsection
