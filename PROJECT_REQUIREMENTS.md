# Kebutuhan Awal Sistem ASKI-PTMA

## Status dokumen

Dokumen ini merupakan rangkuman kebutuhan awal berdasarkan template HTML dan pembahasan bersama Pak Jalil. Isinya dapat diperbarui apabila ada revisi berikutnya.

## Gambaran sistem

Sistem memiliki dua role utama:

- Admin
- Member

Sistem terdiri dari:

- Website publik atau landing page
- Dashboard Admin
- Dashboard Member

Fokus tahap awal:

- Pengelolaan anggota
- Data HKI
- Tarif, tagihan, dan pembayaran
- Dokumen keanggotaan
- Konten profil organisasi
- Publikasi berita

## Website publik

Website publik menampilkan informasi ASKI-PTMA dan dapat diakses tanpa login.

Menu utama yang direncanakan:

- Beranda
- Profil
- Anggota
- Data HKI
- Kontak
- Berita
- Login

Ketentuan:

- Landing page menampilkan ringkasan organisasi, statistik anggota, statistik HKI, dan berita terbaru.
- Menu Profil menampilkan profil organisasi, Visi-Misi, dan Struktur Organisasi.
- Visi-Misi dan Struktur Organisasi dikelola melalui Dashboard Admin.
- Artikel berstatus Terbit ditampilkan otomatis pada landing page dan halaman daftar berita.
- Pengunjung dapat membuka halaman detail setiap berita.

## Dashboard Admin

### Dashboard

Menampilkan ringkasan data, antara lain:

- Total anggota
- Permohonan keanggotaan baru
- Total data HKI
- Ringkasan pembayaran

### Manajemen Akun

- Semua akun
- Akun individu
- Akun institusi
- Profil yang belum lengkap

### Keanggotaan

- Permohonan masuk
- Anggota aktif
- Perpanjangan
- Riwayat keanggotaan

### Tarif Keanggotaan

- Tarif individu
- Tarif institusi
- Periode keanggotaan

### Tagihan dan Pembayaran

- Daftar tagihan
- Bukti pembayaran
- Verifikasi pembayaran
- Riwayat pembayaran

### Data HKI

Jenis HKI tidak perlu dibuat menjadi banyak submenu. Jenis HKI digunakan sebagai filter pada halaman Semua Data HKI.

Halaman Semua Data HKI memuat:

- Kartu ringkasan total HKI
- Line chart perkembangan jumlah HKI berdasarkan bulan atau tahun
- Filter berdasarkan periode, institusi, dan jenis HKI
- Tabel seluruh data HKI
- Fitur export data ke Excel sesuai filter yang sedang digunakan

Data HKI yang dimasukkan Member langsung tersimpan dan masuk ke statistik. Pada tahap awal belum diperlukan proses verifikasi khusus oleh Admin.

### Dokumen

- Pedoman
- Notifikasi
- Template sertifikat
- Template kartu keanggotaan
- Daftar sertifikat dan kartu yang telah dibuat

Sertifikat dan kartu keanggotaan dibuat otomatis menggunakan template dari Admin. Nama dan informasi pada dokumen mengambil data profil anggota, bukan data Struktur Organisasi.

### Konten Website

Admin mengelola:

- Konten Beranda
- Profil organisasi
- Visi-Misi
- Struktur Organisasi
- Informasi anggota
- Data HKI yang ditampilkan ke publik
- Kontak
- Artikel berita

Struktur Organisasi bersifat bebas. Admin dapat menambah nama, jabatan, foto jika diperlukan, serta mengatur posisi atau hubungan antarjabatan. Hasilnya ditampilkan sebagai bagan organisasi pada website publik.

Artikel berita memiliki data minimal:

- Judul
- Slug
- Gambar utama
- Ringkasan
- Isi artikel
- Penulis
- Tanggal publikasi
- Status Draft atau Terbit

### Laporan

- Laporan anggota
- Laporan pembayaran
- Laporan HKI
- Laporan berita yang telah diterbitkan

## Dashboard Member

### Dashboard

Menampilkan:

- Status keanggotaan
- Jumlah data HKI
- Total tagihan
- Periode aktif
- Ringkasan keanggotaan
- Data HKI terbaru

### Keanggotaan

- Ringkasan keanggotaan
- Profil individu atau institusi
- Penanggung jawab dan kontak
- Permohonan dan perpanjangan
- Status dan tagihan keanggotaan
- Sertifikat dan kartu keanggotaan
- Riwayat keanggotaan

Dashboard Member tidak memiliki fitur pengelolaan Struktur Organisasi dan Visi-Misi.

### Data HKI

- Semua data HKI milik Member
- Input data HKI
- Melihat detail data
- Mengubah data milik sendiri jika masih diperbolehkan oleh aturan sistem

Form data HKI awal memuat:

- Nomor permohonan
- Tanggal permohonan
- Pencipta
- Pemegang hak
- Jenis HKI
- Nomor pencatatan
- Judul ciptaan
- Tanggal pertama kali diumumkan
- Jangka waktu pelindungan

### Pembayaran

- Tagihan saya
- Upload bukti transfer
- Riwayat pembayaran

### Dokumen dan Panduan

- Dokumen saya
- Panduan pengurusan HKI
- Pedoman ASKI-PTMA
- Template dokumen

### Bantuan

- FAQ
- Hubungi Admin
- Tiket bantuan
- Help Center

### Pengaturan Akun

- Data akun
- Ubah kata sandi
- Pengaturan notifikasi
- Pengguna institusi jika diperlukan
- Keluar

## Alur utama

### Keanggotaan

Registrasi akun -> melengkapi profil -> mengajukan keanggotaan atau perpanjangan -> menerima tagihan -> melakukan pembayaran -> keanggotaan aktif.

### Pembayaran

Tagihan dibuat -> Member mengunggah bukti transfer -> Admin memeriksa pembayaran -> pembayaran dicatat -> status keanggotaan diperbarui.

### Data HKI

Member mengisi formulir -> data tersimpan -> statistik diperbarui -> Admin melihat grafik dan tabel -> Admin dapat export Excel.

### Berita

Admin menulis artikel -> menyimpan sebagai Draft atau Terbit -> artikel berstatus Terbit tampil pada landing page -> pengunjung membuka detail berita.

### Visi-Misi dan Struktur Organisasi

Admin mengelola konten -> data tersimpan -> konten tampil pada menu Profil dan dapat diringkas pada landing page.

## Tahapan pengerjaan sementara

1. Menyiapkan autentikasi, role, layout, dan struktur database.
2. Mengerjakan Manajemen Akun dan Keanggotaan.
3. Mengerjakan Tarif, Tagihan, dan Pembayaran.
4. Mengerjakan Data HKI, line chart, filter, dan export Excel.
5. Mengerjakan Dokumen, sertifikat, dan kartu keanggotaan otomatis.
6. Mengerjakan konten website, Visi-Misi, Struktur Organisasi, dan berita.
7. Melakukan pengujian, perbaikan keamanan, dan penyempurnaan tampilan.

## Catatan terbuka

Beberapa detail masih perlu dikonfirmasi selama pengembangan, misalnya:

- Format nomor anggota, sertifikat, dan kartu
- Template final sertifikat dan kartu
- Aturan masa berlaku keanggotaan
- Batas ukuran dan jenis file upload
- Aturan perubahan data HKI oleh Member
- Data mana yang boleh ditampilkan pada website publik

Jika detail tersebut memengaruhi database atau alur utama, tanyakan kepada pengguna sebelum menerapkannya.
