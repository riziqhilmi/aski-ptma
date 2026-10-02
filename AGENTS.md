# Petunjuk Kerja Codex - ASKI-PTMA

## Tentang proyek

Proyek ini adalah sistem informasi ASKI-PTMA untuk pengelolaan anggota, data HKI, pembayaran, dokumen, dan publikasi berita.

Sebelum mulai mengerjakan tugas apa pun, baca file `PROJECT_REQUIREMENTS.md` sebagai sumber kebutuhan utama proyek.

## Teknologi

- Backend: Laravel
- Frontend: Vue dengan Inertia.js
- Database: MySQL
- Styling: Tailwind CSS
- Grafik: Chart.js atau library sejenis yang sesuai
- Export data: Excel
- Dokumen: PDF untuk sertifikat dan kartu keanggotaan

Jangan mengganti teknologi utama tanpa persetujuan pengguna.

## Cara bekerja

1. Pelajari struktur proyek dan file yang sudah ada sebelum melakukan perubahan.
2. Kerjakan secara bertahap, satu modul atau satu tujuan yang jelas dalam setiap tahap.
3. Jelaskan rencana singkat sebelum membuat perubahan besar.
4. Jangan menghapus atau menimpa pekerjaan yang sudah ada tanpa pemeriksaan.
5. Gunakan migration, model, controller, request validation, policy, dan relasi database yang rapi.
6. Gunakan komponen Vue yang dapat digunakan kembali untuk tabel, form, modal, kartu statistik, dan notifikasi.
7. Pastikan tampilan responsif pada desktop dan perangkat mobile.
8. Gunakan Bahasa Indonesia untuk label menu dan antarmuka, kecuali istilah teknis yang lebih jelas dalam Bahasa Inggris.
9. Jangan menambahkan fitur di luar `PROJECT_REQUIREMENTS.md` tanpa persetujuan.
10. Jika kebutuhan belum jelas atau memiliki dampak besar terhadap database dan alur sistem, tanyakan terlebih dahulu.

## Kualitas kode

- Ikuti konvensi Laravel dan Vue.
- Hindari duplikasi kode.
- Validasi seluruh input di server.
- Batasi akses berdasarkan role Admin dan Member.
- Jangan menyimpan kata sandi, token, atau kredensial di repository.
- Gunakan transaksi database untuk proses penting jika diperlukan.
- Gunakan soft delete untuk data penting yang mungkin perlu dipulihkan.
- Berikan nama tabel, kolom, route, class, dan method yang konsisten.
- Buat pesan kesalahan yang mudah dipahami pengguna.

## Keamanan

- Gunakan autentikasi dan otorisasi Laravel.
- Lindungi route berdasarkan role.
- Validasi jenis dan ukuran file yang diunggah.
- Simpan file privat di storage yang tidak dapat diakses langsung tanpa izin.
- Hindari query mentah jika Query Builder atau Eloquent sudah mencukupi.
- Jangan menampilkan informasi sensitif pada pesan error.

## Pemeriksaan sebelum selesai

- Jalankan migration dan seeder yang berkaitan jika aman dilakukan.
- Jalankan test yang tersedia.
- Periksa error pada backend dan frontend.
- Pastikan perubahan tidak merusak modul lain.
- Ringkas file yang diubah, fitur yang selesai, dan hal yang masih perlu dikerjakan.

