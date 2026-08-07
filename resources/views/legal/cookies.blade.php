@extends('legal.layout')

@section('legal_content')
    <h2>1. Penggunaan Cookie</h2>
    <p>{{ $platformName }} menggunakan cookie dan teknologi sejenis untuk menjaga sesi login, keamanan, preferensi pengguna, dan performa layanan.</p>

    <h2>2. Jenis Cookie</h2>
    <ul>
        <li><strong>Cookie penting:</strong> diperlukan untuk login, keamanan, CSRF protection, dan fungsi utama platform.</li>
        <li><strong>Cookie preferensi:</strong> dapat digunakan untuk menyimpan pengaturan tampilan atau pengalaman pengguna.</li>
        <li><strong>Cookie analitik:</strong> dapat digunakan untuk memahami performa halaman dan penggunaan fitur jika analitik diaktifkan.</li>
    </ul>

    <h2>3. Pengelolaan Cookie</h2>
    <p>Pengguna dapat mengatur cookie melalui browser. Menonaktifkan cookie penting dapat membuat beberapa fitur seperti login, apply job, atau dashboard tidak berjalan dengan benar.</p>

    <h2>4. Perubahan Kebijakan</h2>
    <p>Kebijakan ini dapat diperbarui sesuai kebutuhan produk, keamanan, atau regulasi. Versi terbaru akan tersedia di halaman ini.</p>

    <h2>5. Kontak</h2>
    <p>Untuk pertanyaan tentang cookie, hubungi <a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a>.</p>
@endsection
