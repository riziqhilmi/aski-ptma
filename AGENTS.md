# Panduan Agent — ASKI-PTMA

## Cakupan

Instruksi ini berlaku untuk repository ASKI-PTMA dan seluruh subdirektorinya, kecuali ada `AGENTS.md` yang lebih spesifik. Ikuti instruksi eksplisit pengguna yang berlaku. Jika ada konflik aturan bisnis, identifikasi konflik dan minta keputusan; jangan memilih secara diam-diam.

Tujuan pekerjaan adalah memperbaiki aplikasi Laravel dengan role Admin dan Member sambil menjaga konsistensi proses, integritas data, fitur yang sudah bekerja, dan identitas visual.

## Sumber acuan

1. Baca `docs/PROJECT_REQUIREMENTS_REVISI_ASKI_PTMA.md` sebelum memulai revisi.
2. Periksa requirements lama, konfigurasi, kode, dan test yang relevan untuk memahami aturan bisnis.
3. Requirements revisi berisi observasi UI dan target perbaikan; dugaan di dalamnya bukan bukti akar masalah.
4. Jangan menganggap perilaku kode saat ini selalu benar. Jika kode, test, dan requirements bertentangan, catat perbedaannya.
5. Jika dokumen acuan tidak tersedia, laporkan path yang hilang. Jangan mengarang isi atau menandai requirement selesai.

## Awal pekerjaan

- Periksa status Git dan perubahan lokal sebelum mengedit. Jangan menimpa atau menghapus pekerjaan pengguna.
- Identifikasi versi Laravel/PHP, frontend stack, package manager, dan perintah test/build dari manifest serta dokumentasi repository.
- Jangan mengasumsikan penggunaan Blade, Livewire, Inertia, React, Vue, atau framework CSS tertentu.
- Telusuri route, middleware, policy, model, enum/status, service/action, dan test hanya pada area yang relevan.
- Gunakan `rg` untuk pencarian bila tersedia. Jangan mencetak isi `.env`, kredensial, token, atau data pribadi ke laporan.
- Sampaikan rencana singkat berdasarkan temuan nyata, lalu lanjutkan pekerjaan yang sudah jelas tanpa meminta konfirmasi untuk setiap edit lokal yang dapat ditinjau.

## Urutan dan batas pekerjaan

- Ikuti prioritas requirements: P0 konsistensi status/proses, P1 bahasa/formulir/alur, kemudian P2 navigasi/visual.
- Default pekerjaan awal adalah menyelesaikan P0 dan melaporkan hasil sebelum masuk P1. Jika pengguna secara eksplisit mengizinkan beberapa tahap sekaligus, lanjutkan sesuai cakupan tersebut.
- Jangan mengimplementasikan backlog B-01 sampai B-07 kecuali diminta.
- Jangan melakukan rewrite, migrasi stack, upgrade dependency besar, atau refactor luas yang tidak diperlukan untuk requirement terkait.
- Jika satu keputusan bisnis menghambat pekerjaan, catat sebagai Terhambat dan lanjutkan requirement lain yang independen.
- Jangan menganggap file ini sebagai izin deploy, push, merge, mengirim pesan, atau melakukan transaksi.

## Integritas data dan keselamatan operasi

- Jangan menjalankan reset database, `migrate:fresh`, `db:wipe`, seeder destruktif, truncate, atau penghapusan data tanpa izin eksplisit untuk target tersebut.
- Pastikan test menggunakan database terisolasi. Jangan menjalankan test yang melakukan reset/migration terhadap database aplikasi pengguna.
- Jika konfigurasi database test tidak dapat dipastikan aman, hentikan bagian test tersebut dan laporkan kebutuhan konfigurasinya.
- Boleh menyiapkan migration yang diperlukan, tetapi tinjau perubahan skema dan dampaknya. Jangan menjalankan migration destruktif atau perubahan skema database pengguna tanpa izin.
- Jangan mengubah tarif, masa aktif, rekening, kredensial, atau kebijakan perpanjangan berdasarkan tebakan.
- Koreksi data historis harus diawali laporan/dry-run yang menjelaskan kandidat, alasan, dan dampak. Jangan menjalankan koreksi massal otomatis.
- Jangan memanggil layanan pembayaran nyata, mengirim email/WhatsApp nyata, atau menjalankan job dengan efek eksternal selama pengujian. Gunakan fake/mock atau lingkungan uji yang terverifikasi.
- Jangan menghapus data uji browser `TEST-UX-20261022-01` secara diam-diam. Data tersebut bukan data bisnis dan bukan fixture yang boleh diandalkan oleh automated test.

## Aturan domain keanggotaan dan pembayaran

- Pisahkan status permohonan, invoice, bukti pembayaran, keanggotaan, dan dokumen. Jangan menyatukannya menjadi satu status tanpa dasar domain.
- Dokumentasikan nilai internal, label pengguna, guard, pemicu transisi, dan agregasi berdasarkan implementasi yang telah diverifikasi.
- Gunakan sumber pemetaan label yang konsisten pada dashboard, daftar, detail, dan laporan Admin/Member.
- Keberadaan sertifikat atau kartu bukan bukti tunggal bahwa invoice sudah lunas.
- Proses verifikasi harus tahan pengulangan: tidak boleh menghasilkan periode keanggotaan atau dokumen ganda yang tidak semestinya.
- Gunakan transaksi untuk perubahan database yang harus berhasil bersama. Periksa efek samping queue/event dan kebutuhan eksekusi setelah commit bila relevan.
- Jangan menambal tampilan dengan hardcode ID invoice, nama akun, atau status tertentu hanya untuk membuat contoh audit terlihat benar.
- Selidiki akar masalah sebelum memperbaiki data; perbaikan label saja tidak cukup jika proses bisnisnya memang bermasalah.

## Standar implementasi Laravel

- Ikuti pola repository yang sudah ada; gunakan abstraksi secukupnya untuk masalah yang nyata.
- Terapkan validasi server dan otorisasi pada endpoint, bukan hanya menyembunyikan tombol di UI.
- Pastikan Member hanya dapat membaca/mengubah data yang memang menjadi haknya, termasuk file dan dokumen.
- Jangan melemahkan CSRF, middleware, policy, atau validasi untuk mempermudah test.
- Hindari mass assignment yang terlalu luas; ikuti model/request/resource conventions yang tersedia.
- Pertahankan nilai internal/API yang masih digunakan kecuali perubahan terencana mencakup seluruh dependensinya.
- Bedakan date-only dan timestamp; gunakan timezone bisnis aplikasi yang diverifikasi, bukan timezone mesin agent.
- Tambahkan dependency hanya jika benar-benar diperlukan dan jelaskan alasannya. Jangan mengganti lockfile/package manager tanpa alasan.

## Bahasa, aksesibilitas, dan desain

- Gunakan Bahasa Indonesia yang jelas untuk teks pengguna, status, dan pesan validasi.
- Jangan tampilkan enum mentah, nama kolom database, atau timestamp ISO sebagai label UI.
- Hubungkan label dengan input, tandai wajib/opsional, tampilkan error dekat field, pertahankan input setelah gagal, dan sediakan fokus keyboard yang terlihat.
- Berikan keadaan loading, sukses, gagal, dan kosong yang jelas. Jangan mengandalkan warna saja untuk menyampaikan status.
- Gunakan komponen konsisten untuk heading, spacing, tombol, input, tabel, dan badge.
- Pertahankan brand hijau tua dan aksen kuning serta nilai warna yang benar-benar ditemukan di project. Jangan mengarang HEX baru sebagai warna resmi.
- Perlakukan artwork/logo/template yang diberikan sebagai master. Jangan mendistorsi struktur, proporsi, tipografi, spacing, atau hubungan elemennya.
- Jangan menambah dekorasi, animasi, chart, atau fitur baru hanya agar terlihat modern.
- Periksa desktop dan mobile pada bagian yang diubah; tabel boleh memiliki overflow yang dirancang, tetapi kontrol penting harus tetap dapat digunakan.
- Field HKI dan masa pelindungan membutuhkan aturan domain yang jelas. Jangan membuat asumsi hukum atau otomatisasi masa pelindungan secara spekulatif.

## Verifikasi

- Gunakan perintah test/build/lint yang benar-benar tersedia di repository.
- Prioritaskan regression test untuk konsistensi status, agregasi, otorisasi, validasi, idempotensi, dan kegagalan pada alur yang disentuh.
- Gunakan fixture sintetis dan terisolasi; jangan menggantungkan test pada akun atau invoice milik pengguna.
- Untuk perubahan UI, lakukan pemeriksaan visual/runtime jika lingkungan mendukung. Jika tidak, jelaskan batas verifikasi.
- Jangan menambah test yang hanya mengulang implementasi atau snapshot rapuh untuk perubahan kosmetik kecil.
- Jalankan pemeriksaan yang cukup untuk risiko perubahan; perluas hanya bila ada risiko konkret atau gate repository.
- Bedakan test gagal karena perubahan dengan kegagalan baseline/lingkungan. Jangan mengklaim seluruh aplikasi lolos jika hanya subset yang diuji.
- Jangan menyebut pembacaan kode sebagai pengujian runtime.

## Pembaruan requirements

- Pertahankan ID requirement dan catat status: Belum dikerjakan / Dalam pengerjaan / Selesai / Terhambat.
- Jika dokumen masih menggunakan status awal kolektif, tambahkan tabel pelacakan per ID tanpa menghapus definisi requirement atau bukti audit.
- Tandai Selesai hanya setelah acceptance criteria diverifikasi.
- Catat keputusan terbuka dengan ID yang relevan serta alasan pekerjaan terhambat.
- Bedakan temuan awal dari hasil verifikasi kode dan kondisi setelah perbaikan; jangan menulis ulang sejarah audit seolah bug tidak pernah ada.

## Komunikasi dan penyerahan

Gunakan Bahasa Indonesia untuk pembaruan dan laporan, kecuali diminta sebaliknya. Berikan pembaruan singkat ketika menemukan akar masalah, batas akses, atau perubahan cakupan.

Laporan akhir harus mencakup:

1. Requirement yang ditangani dan statusnya.
2. Akar masalah yang terkonfirmasi, terpisah dari dugaan.
3. Perubahan utama dan file terkait.
4. Test/perintah verifikasi yang dijalankan beserta hasilnya.
5. Perubahan skema atau data yang disiapkan/dijalankan, jika ada.
6. Risiko tersisa, keterbatasan verifikasi, dan keputusan yang diperlukan.

Jangan mengklaim pekerjaan selesai ketika ada acceptance criteria penting yang belum terverifikasi. Jangan melakukan commit, push, merge, atau deploy kecuali diminta pengguna.
