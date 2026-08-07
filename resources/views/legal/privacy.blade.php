@extends('legal.layout')

@section('legal_content')
    <h2>1. Data yang Kami Kumpulkan</h2>
    <p>{{ $platformName }} mengumpulkan data yang Anda berikan saat mendaftar, melamar pekerjaan, membuat profil, mengunggah resume, mengirim pesan, atau menggunakan fitur recruiter. Data dapat mencakup nama, email, nomor telepon, profil profesional, pengalaman kerja, pendidikan, skill, resume, data perusahaan, aktivitas login, dan riwayat lamaran.</p>

    <h2>2. Cara Kami Menggunakan Data</h2>
    <p>Data digunakan untuk menjalankan layanan job portal dan ATS, memproses lamaran, membantu recruiter meninjau kandidat, menampilkan status lamaran, mengirim notifikasi, menjaga keamanan akun, meningkatkan layanan, dan mengirim komunikasi produk atau marketing jika relevan.</p>

    <h2>3. Berbagi Data</h2>
    <p>Data kandidat dapat dibagikan kepada recruiter atau perusahaan yang menerima lamaran kandidat. Data recruiter dan perusahaan dapat digunakan untuk memverifikasi akun, mengelola lowongan, dan komunikasi layanan. Kami tidak menjual data pribadi pengguna.</p>

    <h2>4. Penyimpanan dan Keamanan</h2>
    <p>Kami menerapkan kontrol akses, autentikasi, backup, dan pemantauan sistem untuk membantu menjaga data. Walau begitu, tidak ada sistem internet yang sepenuhnya bebas risiko, sehingga pengguna tetap perlu menjaga keamanan password dan akses email masing-masing.</p>

    <h2>5. Email dan Notifikasi</h2>
    <p>Kami dapat mengirim email operasional seperti reset password, status lamaran, jadwal interview, pesan recruiter, dan notifikasi penting. Untuk email marketing, pengguna dapat berhenti berlangganan melalui link unsubscribe yang tersedia.</p>

    <h2>6. Hak Pengguna</h2>
    <p>Pengguna dapat meminta akses, koreksi, pembaruan, atau penghapusan data tertentu sejauh diperbolehkan oleh kebutuhan operasional, keamanan, kewajiban hukum, dan catatan transaksi layanan.</p>

    <h2>7. Kontak</h2>
    <p>Untuk pertanyaan privasi, hubungi kami melalui <a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a>.</p>
@endsection
