# Hire tanpa ghosting

Janji produk ini diukur oleh sistem, bukan sekadar teks pemasaran.

## Standar layanan

- Respons recruiter pertama maksimal 72 jam setelah lamaran dikirim.
- Reminder dikirim 24 jam sebelum jatuh tempo dan sekali lagi setelah terlambat.
- Lowongan terbit wajib dikonfirmasi ulang setiap 30 hari.
- Lowongan yang tidak dikonfirmasi ditutup otomatis. Lamaran yang belum selesai mendapat hasil akhir `Posisi ditutup`.
- Keputusan `Diterima`, `Ditolak`, `Posisi ditutup`, `Posisi dibatalkan`, atau `Ditarik kandidat` selalu memiliki waktu dan alasan akhir.

Nilai tersebut dapat diubah lewat environment tanpa mengubah kode.

## Aturan perpindahan status

`submitted → screening → shortlisted/interview → offer → hired`

Penolakan dan penarikan dapat terjadi pada tahap aktif, tetapi selalu wajib memiliki alasan. Lamaran yang sudah final tidak dapat dibuka atau dipindahkan diam-diam. Admin dapat melewati tahap aktif untuk koreksi operasional, tetapi tetap tidak dapat mengubah hasil yang sudah final.

## Metrik perusahaan

- `response_rate`: persentase lamaran yang sudah memperoleh tindakan recruiter pertama.
- `median_response_hours`: median waktu dari lamaran masuk hingga tindakan recruiter pertama.
- `Aktif merespons`: minimal memiliki satu sampel, response rate minimal 80%, dan aktivitas recruiter dalam 14 hari terakhir.
- `Perusahaan terverifikasi`: diberikan manual oleh admin setelah pemeriksaan identitas/legalitas; tidak berasal dari metrik respons.

Dashboard admin menampilkan pelanggaran SLA, lowongan menjelang batas konfirmasi, perusahaan tidak responsif, dan laporan lowongan dari publik.
