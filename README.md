# carikerja.asia

carikerja.asia adalah platform rekrutmen dan applicant tracking system untuk membantu kandidat memantau proses lamaran serta membantu recruiter mengelola kebutuhan hiring secara terstruktur.

## Fitur utama

- Landing page dan waitlist kandidat maupun recruiter.
- Job board publik dan alur lamaran pekerjaan.
- Portal kandidat dengan profil, resume, notifikasi, dan riwayat lamaran.
- Portal recruiter untuk perusahaan, lowongan, kandidat, interview, dan komunikasi.
- Dashboard admin untuk operasional platform, user, perusahaan, lowongan, dan email.
- Integrasi Google untuk autentikasi kandidat dan Google Workspace recruiter.
- Queue, scheduler, backup operasional, dan monitoring internal.
- SEO metadata, sitemap, robots.txt, serta halaman legal.

## Teknologi

- PHP 8.3+
- Laravel 13
- MySQL atau MariaDB
- Vite 8
- Tailwind CSS 4
- Redis opsional

## Instalasi lokal

Pastikan PHP, Composer, Node.js, npm, dan database sudah tersedia.

```bash
git clone https://github.com/rendymandolang/carikerja.asia.git
cd carikerja.asia
composer install
cp .env.example .env
php artisan key:generate
```

Sesuaikan koneksi database di `.env`, kemudian jalankan:

```bash
php artisan migrate
npm install
npm run build
php artisan serve
```

Untuk proses queue pada lingkungan pengembangan:

```bash
php artisan queue:work
```

## Pengujian

```bash
php artisan test
```

## Keamanan

Jangan commit `.env`, credential, token, backup database, atau private key. Laporkan kerentanan keamanan secara privat melalui `hello@carikerja.asia`.
