# Project Requirements Revisi â€” ASKI-PTMA

Versi: 1.0  
Tanggal: 22 Oktober 2026  
Status: Acuan revisi berdasarkan audit UI; verifikasi repository dan keputusan bisnis masih diperlukan.  
Platform: Laravel, localhost; role Admin dan Member.

## 1. Tujuan

Memperbaiki konsistensi informasi, kejelasan alur keanggotaan, aksesibilitas, dan kualitas visual tanpa merusak fitur yang sudah bekerja. Dokumen ini merupakan acuan revisi baru, bukan bukti bahwa implementasi telah diperbaiki atau pengganti otomatis bagi requirements lama yang belum ditinjau.

Urutan prioritas: konsistensi proses dan status â†’ kejelasan bahasa dan formulir â†’ kemudahan penggunaan â†’ penyempurnaan visual â†’ fitur tambahan.

## 2. Dasar dan batas audit

Audit dilakukan melalui browser pada antarmuka Admin dan akun Member individu yang sudah aktif. Halaman utama seluruh menu kedua role ditinjau, berikut beberapa halaman detail dan submit formulir. Kode, database, queue, log server, serta konfigurasi deployment belum diperiksa.

Belum teruji: registrasi anggota baru, akun institusi, siklus pembayaran baru dari awal sampai akhir, otorisasi lintas akun, pengiriman notifikasi aktual, responsivitas mobile, dan isi PDF sertifikat. Pratinjau sertifikat membuka tab baru, tetapi pembacaan tab ditolak oleh kebijakan browser; ini bukan bukti kerusakan fitur PDF.

### 2.1 Hasil pengujian yang sudah dilakukan

| Pengujian | Hasil observasi |
|---|---|
| Submit HKI kosong | Ditolak dengan pesan per kolom, masih berbahasa Inggris |
| Tambah HKI uji | Berhasil; data muncul dalam daftar |
| Ubah judul HKI uji | Berhasil; detail menampilkan judul baru |
| Jumlah HKI pada dashboard | Bertambah dari 2 menjadi 3 |
| Submit bantuan kosong | Ditolak dengan pesan validasi |
| Detail tiket lama | Pertanyaan, jawaban Admin, dan status tersedia |
| Tombol Ajukan Permohonan | Memunculkan konfirmasi; tidak terlihat permohonan baru setelah pemeriksaan |

Data uji yang masih tersimpan: nomor `TEST-UX-20261022-01`, judul `[TEST UX] Evaluasi Portal Member â€” ubah berhasil`. Data ini tidak boleh dijadikan dasar data bisnis atau dihapus diam-diam.

### 2.2 Temuan terkonfirmasi

| ID | Temuan | Dampak |
|---|---|---|
| F-01 | Invoice TAG-2026-000013 tampil `paid` di dashboard Member, â€œBelum dibayarâ€ pada daftar Admin/Member, dan â€œDibayarâ€ dengan bukti `verified` pada detail Member | Membingungkan pengguna dan berisiko mendorong pembayaran ulang |
| F-02 | Permohonan terkait masih menunggu pembayaran, sementara keanggotaan aktif dan dokumen sudah tersedia | Tahap proses tidak dapat dipercaya hanya dari label UI |
| F-03 | Timestamp ISO dan kode internal terlihat pada banyak halaman | Informasi sulit dibaca dan terasa belum selesai |
| F-04 | Dashboard menampilkan Total Tagihan Rp0 meskipun terdapat riwayat invoice lunas | Label metrik berpotensi menyesatkan |
| F-05 | Akun aktif masih melihat tombol Ajukan Permohonan | Pendaftaran dan perpanjangan belum jelas secara visual |
| F-06 | Error HKI/bantuan berbahasa Inggris; kolom wajib HKI belum jelas; input HKI tidak terbaca dengan nama label pada inspeksi aksesibilitas | Pengisian dan pemulihan kesalahan kurang mudah |
| F-07 | Setelah tambah/ubah HKI, pemberitahuan sukses eksplisit tidak terlihat pada hasil yang tertangkap | Pengguna perlu memeriksa sendiri apakah penyimpanan berhasil |
| F-08 | Form HKI memakai istilah ciptaan/pencatatan, sementara jenis Paten juga tersedia | Kesesuaian field per jenis belum jelas |
| F-09 | Sidebar Admin panjang; rekening pembayaran terpisah di bagian atas | Navigasi sulit dipindai |
| F-10 | Konten uji masih muncul pada HKI, FAQ, dan pengurus; dua pengurus memiliki urutan 1 | Kesiapan demonstrasi/publikasi berkurang |

Penyebab F-01/F-02 belum diketahui. Pemetaan label, transisi proses, atau data lama merupakan hipotesis, bukan kesimpulan teknis.

### 2.3 Fitur yang perlu dipertahankan

- Tambah/ubah HKI dan pembaruan jumlah dashboard.
- Validasi server pada HKI dan bantuan.
- Riwayat status permohonan dan jawaban tiket bantuan.
- Preferensi notifikasi keanggotaan, tagihan, pembayaran, dokumen, dan berita.
- Ekspor Excel pada Data HKI Admin.
- Akses sertifikat/kartu anggota dan pengelolaan konten yang sudah tersedia.

## 3. Ruang lingkup dan prinsip implementasi

1. Baca instruksi repository dan identifikasi versi Laravel serta frontend stack sebelum mengubah kode.
2. Telusuri implementasi yang ada; jangan mengganti framework atau arsitektur tanpa kebutuhan yang terbukti.
3. Pisahkan status permohonan, invoice, bukti pembayaran, keanggotaan, dan dokumen. Status tersebut saling terkait, tetapi tidak identik.
4. Pertahankan logo/artwork master, identitas hijau tua dan aksen kuning. Struktur, proporsi, tipografi, dan warna artwork tidak boleh diubah tanpa permintaan khusus.
5. Gunakan komponen yang konsisten dan semantik; warna bukan satu-satunya pembeda status.
6. Jangan menghapus data, mereset database, menimpa perubahan lokal, atau memperbaiki data historis secara massal tanpa persetujuan.
7. Tidak mencakup deployment, transaksi pembayaran nyata, perubahan kredensial, atau pengiriman email/WhatsApp nyata.

## 4. Requirement inti

Status awal seluruh requirement berikut: **Belum dikerjakan**. Hasil audit bukan bukti implementasi requirement telah selesai.

### P0 â€” Konsistensi proses dan status

| ID | Requirement | Kriteria penerimaan |
|---|---|---|
| R-01 | Telusuri dan dokumentasikan sumber status serta transisi antarmodul | Dokumen mencantumkan nilai internal, arti, pemicu transisi, guard, dan sumber agregasi berdasarkan kode; ketidakjelasan dicatat sebagai keputusan terbuka |
| R-02 | Samakan status invoice pada daftar, detail, dashboard, dan laporan kedua role | Invoice yang sama memiliki makna status konsisten; regression test mencakup kasus TAG-2026-000013 memakai fixture sintetis |
| R-03 | Selaraskan transisi permohonan, pembayaran, keanggotaan, dan dokumen sesuai aturan bisnis | Setelah verifikasi, entitas terkait berada pada status yang benar menurut aturan terverifikasi; kegagalan tidak meninggalkan perubahan parsial |
| R-04 | Cegah efek samping ganda pada proses verifikasi | Verifikasi berulang tidak membuat keanggotaan, periode, invoice, atau dokumen tambahan yang tidak semestinya; mekanisme idempotensi diuji |
| R-05 | Periksa konsistensi data historis secara aman | Tersedia hasil pemeriksaan/dry-run dengan alasan per kasus; koreksi massal tidak dijalankan otomatis; keberadaan dokumen saja tidak dianggap bukti invoice lunas |
| R-06 | Samakan angka ringkasan dengan aturan agregasi | Hitungan dan nominal dashboard sesuai fixture dan filter; periode serta arti metrik dinyatakan jelas |

Gunakan transaksi database ketika perubahan harus berhasil bersama. Bentuk implementasi ditentukan setelah memeriksa kode, termasuk efek samping asynchronous bila ada.

### P1 â€” Bahasa, formulir, dan kejelasan alur

| ID | Requirement | Kriteria penerimaan |
|---|---|---|
| R-07 | Sentralisasi label status Bahasa Indonesia | Nilai internal tetap dapat digunakan oleh program; pengguna tidak melihat kode mentah pada halaman dalam cakupan |
| R-08 | Standarkan format tanggal dan waktu | Tanggal tampil manusiawi, mengikuti timezone aplikasi yang diverifikasi; tanggal tanpa waktu tidak bergeser akibat konversi timezone |
| R-09 | Perjelas label metrik tagihan | Jika angka merupakan kewajiban belum lunas, label menjadi Total Belum Dibayar; jika berbeda, label mengikuti arti agregasi sebenarnya |
| R-10 | Perbaiki validasi dan aksesibilitas formulir HKI/bantuan | Error berbahasa Indonesia, label terhubung ke input, wajib/opsional jelas, isian bertahan setelah gagal, error mudah ditemukan lewat keyboard |
| R-11 | Berikan umpan balik submit dan cegah submit ganda | Sukses/gagal terlihat jelas; tombol menampilkan keadaan proses; server tetap memvalidasi; tambah/ubah HKI tidak regresi |
| R-12 | Perjelas pendaftaran dan perpanjangan | CTA mengikuti status serta aturan kelayakan yang ditemukan; menampilkan konteks periode/biaya bila tersedia; pengajuan ganda ditangani sesuai aturan terverifikasi |
| R-13 | Tinjau field HKI per jenis | Pemetaan field saat ini didokumentasikan; label diperbaiki bila maknanya pasti; perubahan field wajib/aturan masa pelindungan menunggu keputusan domain yang belum tersedia |
| R-14 | Perjelas konfigurasi rekening yang belum lengkap | Admin mendapat petunjuk konfigurasi; Member tidak menerima instruksi pembayaran yang menampilkan rekening kosong; perilaku mengikuti metode pembayaran yang benar-benar digunakan |

### P2 â€” Navigasi, dashboard, dan konsistensi visual

| ID | Requirement | Kriteria penerimaan |
|---|---|---|
| R-15 | Kelompokkan sidebar Admin | Semua menu tetap dapat diakses, menu aktif jelas, pengelompokan tidak mengubah otorisasi |
| R-16 | Standarkan komponen UI Admin/Member | Heading, spacing, tombol, input, tabel, badge, empty state, fokus dan error memakai pola yang konsisten |
| R-17 | Jadikan dashboard membantu tindakan berikutnya | CTA sesuai kondisi pengguna dan route yang tersedia; tidak mendorong pembayaran ulang atau pengajuan yang tidak layak |
| R-18 | Tingkatkan daftar data yang memerlukan pencarian/filter/pagination | Implementasi mengikuti kebutuhan volume dan pola repository; filter dan pagination mempertahankan konteks, serta menampilkan hasil kosong dengan jelas |
| R-19 | Periksa responsivitas dan akses keyboard | Desktop dan layar kecil diuji; konten utama tidak terpotong, tabel punya perilaku overflow yang layak, kontrol penting dapat digunakan dengan keyboard |
| R-20 | Siapkan konten demonstrasi yang layak | Data uji teridentifikasi; usulan penggantian/pembersihan disajikan untuk ditinjau; tidak ada penghapusan diam-diam |

Usulan kelompok sidebar: Ringkasan; Keanggotaan; Keuangan; Kekayaan Intelektual; Publikasi; Bantuan; Laporan dan Pengaturan. Rincian final mengikuti struktur route dan kebutuhan penggunaan.

## 5. Backlog terpisah â€” belum masuk implementasi inti

| ID | Usulan | Pemeriksaan sebelum implementasi |
|---|---|---|
| B-01 | Pengingat masa berlaku/perpanjangan | Jadwal, kanal, persetujuan penerima, dan aturan perpanjangan |
| B-02 | Riwayat notifikasi dan status pengiriman | Implementasi preferensi/kanal yang sudah ada; hindari duplikasi |
| B-03 | Audit aktivitas lintas modul | Aktivitas, retensi, akses log, dan perlindungan informasi sensitif |
| B-04 | QR verifikasi dokumen | Informasi publik minimum, validitas/revokasi, dan desain dokumen master |
| B-05 | Pratinjau template sertifikat/kartu | Renderer dan artwork yang dipakai; kesetaraan preview dan hasil unduh |
| B-06 | Ekspor laporan sesuai filter | Hak akses, batas data, dan kesesuaian dengan filter layar |
| B-07 | Field HKI dinamis per jenis | Definisi field dan aturan domain yang disetujui |

## 6. Keputusan bisnis yang masih terbuka

| ID | Pertanyaan | Pengaruh |
|---|---|---|
| D-01 | Diputuskan pengguna: permohonan menjadi `completed` saat pembayaran diverifikasi. | R-01, R-03 |
| D-02 | Diputuskan pengguna: perpanjangan dibuka 30 hari kalender sebelum akhir periode; masa baru 12 bulan; jika dibayar sebelum akhir periode mulai sehari sesudahnya, jika sesudah kedaluwarsa mulai tanggal verifikasi; akhir sehari sebelum tanggal sama tahun berikutnya; invoice jatuh tempo 14 hari kalender sejak diterbitkan. | R-12, B-01 |
| D-03 | Apakah ada status/revisi pembayaran atau skenario pembatalan lain dalam implementasi? | Terverifikasi dari kode: status bukti hanya `submitted`, `verified`, `rejected`; bukti yang ditolak dapat diganti dengan pengajuan baru. Tidak ada status pembatalan/refund atau nominal pembayaran parsial pada model saat ini. |
| D-04 | Zona waktu bisnis untuk tampilan timestamp | Diputuskan pengguna: `Asia/Jakarta` (WIB); diterapkan untuk R-08. |
| D-05 | Diputuskan pengguna: Hak Cipta (judul, jenis ciptaan, pencipta, pemegang, tanggal diumumkan); Paten (judul invensi, inventor, pemegang, jenis, tanggal penerimaan); Merek (nama, pemilik, nomor pendaftaran, kelas, tanggal penerimaan); Desain Industri (judul, pendesain, pemegang, tanggal penerimaan); Rahasia Dagang (nama/kategori, pemilik, tanggal mulai, status kerahasiaan). Masa pelindungan diinput mengikuti dokumen dan tidak dihitung otomatis; detail hukum perlu ditinjau berkala dan mengikuti kategori serta ketentuan berlaku. | R-13, B-07 |
| D-06 | Kanal notifikasi mana yang benar-benar digunakan? | B-01, B-02 |
| D-07 | Apakah tipe akun boleh berubah setelah keanggotaan aktif? | Audit profil dan otorisasi berikutnya |

Cari jawabannya terlebih dahulu pada requirements lama, konfigurasi, kode, dan test. Tanyakan hanya jika aturan tidak ditemukan atau saling bertentangan; jangan mengarang kebijakan.

## 7. Rencana verifikasi

| Area | Bukti yang diperlukan |
|---|---|
| Status dan agregasi | Test database/feature dengan fixture sintetis; hasil konsisten pada kedua role |
| Verifikasi pembayaran | Test transisi, pengulangan, dan kegagalan pada alur yang disentuh |
| Otorisasi | Test role dan kepemilikan pada endpoint yang diubah; Member tidak memperoleh akses data milik akun lain |
| Formulir | Submit kosong, invalid, valid, edit, serta umpan balik; nilai tetap tersimpan setelah error |
| UI | Pemeriksaan desktop/mobile dan keyboard; label status, tanggal, empty state, serta overflow |
| Nonregresi | Tambah/ubah HKI, jumlah dashboard, riwayat permohonan/tiket, dan preferensi notifikasi tetap berfungsi |

Gunakan test yang membuktikan risiko nyata. Pembacaan kode tidak sama dengan pengujian runtime. Catat perintah, hasil, dan bagian yang belum dapat dijalankan.

## 8. Urutan pengerjaan dan definition of done

1. Inventarisasi repository dan verifikasi temuan; isi keputusan yang dapat dijawab dari kode.
2. Selesaikan P0 dengan test regresi dan laporan data historis bila diperlukan.
3. Selesaikan P1, lalu P2 tanpa refactor luas yang tidak berkaitan.
4. Perbarui status requirement per ID: Belum dikerjakan / Dalam pengerjaan / Selesai / Terhambat.
5. Laporkan file yang berubah, akar masalah terkonfirmasi, bukti test, risiko tersisa, dan keputusan terbuka.

Requirement hanya boleh berstatus Selesai setelah acceptance criteria-nya terverifikasi. Pekerjaan yang membutuhkan aturan bisnis belum diputuskan harus berstatus Terhambat, bukan dianggap selesai atau diterapkan berdasarkan tebakan.

## 9. Instruksi singkat untuk Codex

> Baca AGENTS.md dan dokumen requirements ini. Verifikasi temuan melalui repository, lalu implementasikan requirement inti berurutan P0 â†’ P1 â†’ P2. Pertahankan fitur, data, artwork, dan perubahan lokal yang sudah ada. Periksa requirements lama bila tersedia dan catat konflik sebelum mengubah aturan bisnis. Jangan menjalankan backlog atau tindakan di luar cakupan. Buat test yang relevan, perbarui status setiap requirement, dan laporkan bukti serta keterbatasan hasil. Tanyakan hanya keputusan bisnis yang benar-benar menghalangi pekerjaan; lanjutkan bagian lain yang sudah jelas.

## 10. Riwayat revisi

| Versi | Tanggal | Perubahan |
|---|---|---|
| 1.0 | 22 Oktober 2026 | Acuan awal dari audit Admin dan Member; belum ada perubahan kode yang diklaim selesai |

## 11. Pelacakan implementasi repository

Status di bawah merupakan hasil pemeriksaan kode dan regresi yang dijalankan pada 27 Oktober 2026. Dokumen ini berada di root repository; path `docs/PROJECT_REQUIREMENTS_REVISI_ASKI_PTMA.md` yang disebut `AGENTS.md` tidak tersedia.

| ID | Status | Hasil verifikasi / batas |
|---|---|---|
| R-01 | Selesai | Invoice: `unpaid` berarti kewajiban menunggu pembayaran dan `paid` berarti lunas; dibuat saat Admin menyetujui permohonan, dengan guard `submitted` dan tarif aktif. Bukti: `submitted` menunggu pemeriksaan, `verified` diterima, `rejected` ditolak; Member hanya dapat mengirim pada invoice unpaid tanpa bukti submitted/verified. Permohonan: `submitted` menunggu Admin, `revision` dapat diajukan ulang, `approved_waiting_payment` menunggu pembayaran, `rejected` ditolak, `completed` ditetapkan controller saat pembayaran diverifikasi (makna akhirnya masih menunggu D-01). Keanggotaan `active` dan dokumen sertifikat/kartu dibuat saat bukti diverifikasi. Rantai verifikasi memakai transaksi serta guard pada payment/invoice; dokumen dicek per keanggotaan dan jenis. Dashboard mengambil unpaid dari tabel invoice; laporan mengambil pembayaran dan nominal invoice dari bukti verified. |
| R-02 | Selesai | Label daftar, detail Member dan daftar Admin memakai `Invoice::statusLabels()`; filter Admin membedakan `paid` dan `unpaid`. Regression test membuat fixture sintetis bernomor `TAG-2026-000013` dan memeriksa label di kedua role serta detail. Test lulus; pemeriksaan visual browser belum dilakukan. |
| R-03 | Selesai | D-01 dikonfirmasi pengguna: permohonan menjadi `completed` ketika pembayaran diverifikasi. Perubahan pembayaran/invoice/keanggotaan/dokumen berlangsung dalam transaksi; guard verifikasi serta idempotensi diuji. |
| R-04 | Selesai | Pembayaran berulang ditolak; keanggotaan unik per invoice; layanan dokumen mengembalikan dokumen yang sudah ada per keanggotaan/jenis. Feature test lulus untuk verifikasi ulang tanpa keanggotaan tambahan dan memeriksa jumlah dokumen saat penerbitan pertama. Perilaku konkurensi nomor dokumen pada database produksi belum dibuktikan. |
| R-05 | Selesai | Dry-run baca-saja pada 27 Oktober 2026 dengan `SELECT` pada database aplikasi; tidak mencetak nama/email dan tidak melakukan perubahan data. Cakupan snapshot: 3 user, 2 permohonan, 2 invoice, 2 bukti pembayaran, 2 keanggotaan, 4 dokumen keanggotaan. 14 pemeriksaan menghasilkan 0 kandidat: invoice paid tanpa bukti verified; invoice unpaid dengan bukti verified; status verified tidak selaras dengan invoice; bukti verified ganda per invoice; invoice paid tanpa keanggotaan; keanggotaan tanpa invoice paid/bukti verified; permohonan completed tanpa invoice paid/bukti verified; permohonan approved_waiting_payment tanpa invoice; mismatch pemilik invoice/permohonan/pembayaran/keanggotaan; periode keanggotaan terbalik/kosong; dokumen orphan atau pemilik berbeda; serta keanggotaan tanpa sertifikat atau kartu. ID kandidat: tidak ada. Keberadaan dokumen hanya diperiksa sebagai konsistensi kelengkapan—tidak pernah dipakai sebagai bukti pelunasan. Tidak ada koreksi data yang dijalankan. Hasil ini snapshot pada waktu audit; cakupan tidak mencakup rekonsiliasi nominal terhadap mutasi bank atau penilaian sah/tidaknya dokumen. |
| R-06 | Selesai | Dashboard Member menjumlah/menghitung invoice `unpaid`; Dashboard Admin memisahkan invoice unpaid, bukti pembayaran submitted, anggota aktif, dan nilai invoice paid. Laporan menghitung jumlah record bukti pembayaran serta nominal invoice unik yang memiliki bukti `verified`, memakai tanggal pembuatan bukti (`payments.created_at`) untuk filter periode. Query nominal kini mencegah invoice terhitung ganda. Feature test mencakup dua bukti verified pada satu invoice dan bukti dari luar periode; nominal tetap satu kali dan hitungan periode sesuai fixture. |

D-01, D-02, dan D-05 telah dijawab pengguna. Kebijakan refund/pembatalan belum ada dalam implementasi; bila fitur itu diperlukan, perlu keputusan bisnis baru sebelum ditambahkan.

### P1 â€” hasil tahap lanjutan

| ID | Status | Hasil / batas verifikasi |
|---|---|---|
| R-07 | Selesai | Pemetaan invoice, permohonan, pembayaran, dan tiket berada pada model dan digunakan di dashboard/list/detail/laporan terkait; artikel dan dokumen memakai accessor model, sedangkan status profil/pengurus memakai label terhitung berbahasa Indonesia. Pemindaian template Admin/Member yang relevan tidak menemukan enum status mentah ditampilkan. Nilai internal tetap dipakai untuk logika program. |
| R-08 | Selesai | Pengguna menetapkan `Asia/Jakarta` (WIB). Formatter timestamp bersama menggunakan timezone eksplisit tersebut pada riwayat permohonan, waktu publikasi artikel, dokumen keanggotaan, dan dashboard; field date-only invoice, keanggotaan, periode dashboard, dan HKI memakai formatter yang tidak mengonversi tanggal melalui UTC. Pemeriksaan runtime menunjukkan `2026-01-01T18:00:00Z` tampil sebagai `2 Jan 2026, 01.00 WIB`, sementara date-only `2026-10-27T00:00:00Z` tetap 27 Oktober 2026. Build berhasil. |
| R-09 | Selesai | Metrik Member dihitung dari invoice `unpaid`, sehingga label diganti menjadi â€œTotal Belum Dibayarâ€. |
| R-10 | Selesai | HKI menandai field wajib sesuai FormRequest dan nomor pencatatan sebagai opsional; input punya label terkait, fokus, dan pesan error. HKI/bantuan memberi pesan validasi Bahasa Indonesia serta error terdekat di field. Feature test validasi lulus. |
| R-11 | Selesai | Flash sukses Inertia ditampilkan di layout Admin/Member. Form HKI, tiket, unggah pembayaran, dan keputusan pembayaran menonaktifkan tombol saat proses. Validasi server tetap aktif. Test validasi serta alur yang terkait lulus. |
| R-12 | Selesai | D-02 diterapkan: renewal hanya tersedia sejak 30 hari kalender sebelum akhir keanggotaan dan hingga tanggal akhir; guard server memblokir pengajuan di luar jendela. Invoice renewal berjangka 12 bulan dan jatuh tempo 14 hari kalender dari penerbitan. Verifikasi membentuk periode berlanjut jika keanggotaan lama masih aktif, atau mulai tanggal verifikasi bila sudah berakhir. Test feature lulus untuk batas 30 hari, guard server, dan durasi renewal. UI menampilkan tanggal mulai jendela; build berhasil. |
| R-13 | Selesai | D-05 diterapkan pada Request dan form: jenis HKI memakai dropdown valid; Hak Cipta wajib jenis ciptaan; Paten wajib jenis paten (Paten/Paten Sederhana); Merek wajib nomor pendaftaran dan kelas barang/jasa; tanggal, judul, pencipta/inventor, pemegang/pemilik dilabel ulang sesuai jenis; Rahasia Dagang memiliki status kerahasiaan. Kolom JSON nullable menjaga record lama. Masa pelindungan tetap diinput sesuai dokumen, tidak dihitung otomatis karena aturan bervariasi menurut kategori. Feature test HKI lulus (6 test/44 assertions) dan build berhasil. QA visual browser belum dilakukan karena server dengan SQLite `:memory:` tidak bisa berbagi skema lintas proses; tidak dialihkan ke database file untuk menghindari penyimpanan uji di luar isolasi yang ditentukan. |
| R-14 | Selesai | Jika salah satu data rekening kosong, Member tidak menerima instruksi transfer, halaman Admin memberi peringatan, dan PDF invoice meminta Member menghubungi Admin. Test memastikan flag konfigurasi kosong pada halaman invoice Member. |

Pemeriksaan visual desktop/mobile belum dilakukan; seluruh perubahan UI diverifikasi lewat build Vite dan test server-side.

### P2 â€” hasil tahap lanjutan

| ID | Status | Hasil / batas verifikasi |
|---|---|---|
| R-15 | Selesai | Navigasi Admin dikelompokkan menurut area tanpa mengubah route atau middleware. Menu Admin dan Member dapat dilipat pada layar kecil, semua tautan tetap tersedia, dan menu aktif terlihat. Pemeriksaan dilakukan melalui build, belum melalui browser. |
| R-16 | Dalam pengerjaan | Badge status profil pada daftar/detail Admin memakai `StatusBadge`; riwayat keanggotaan Member memiliki header tabel semantik. Kontrol filter daftar HKI, Akun, Invoice, Pembayaran, Permohonan, Berita, Dokumen, Tiket, dan kategori Dokumen Member berlabel aksesibel. Form Artikel, Dokumen, Pengurus, Rekening Pembayaran, dan format Sertifikat/Kartu memiliki label terhubung, penanda wajib/opsional berdasarkan validasi server, fokus konsisten, status `aria-invalid`, serta pesan error dekat field. Empty state tagihan dan permohonan Admin tersedia. Build berhasil; browser Admin memeriksa desktop dan lebar 390px pada form Artikel, tanpa overflow dokumen, serta memastikan outline keyboard terlihat. QA visual Member dan audit visual menyeluruh Admin/Member tetap diperlukan sebelum status selesai. |
| R-17 | Selesai | Dashboard Member menampilkan langkah berikutnya dari invoice unpaid, status permohonan, atau keanggotaan aktif. Dashboard Admin memberi tautan ke permohonan/bukti pembayaran yang menunggu. Test fixture Member memastikan CTA menuju invoice unpaid yang benar. Kelayakan renewal memakai aturan D-02 dan diuji melalui test feature. |
| R-18 | Selesai | Daftar utama Admin/Member dan direktori publik berpaginasi; filter yang ada mempertahankan konteks melalui query string dan halaman yang diaudit menampilkan empty state. Ditambahkan empty state tagihan dan permohonan Admin. Snapshot baca-saja: 2 invoice, 2 pembayaran, 3 HKI, 1 tiket, 1 dokumen panduan, 1 FAQ aktif, 3 artikel, 2 pengurus, 1 tarif; volume saat ini tidak menunjukkan kebutuhan pencarian tambahan. Tabel ber-`overflow-x-auto` telah ditinjau dan diberi nama accessible/fokus keyboard. |
| R-19 | Dalam pengerjaan | QA browser pada Admin: tampilan desktop daftar HKI, responsivitas lebar 390px tanpa overflow dokumen, filter bertumpuk, tabel dapat digulir horizontal, navigasi mobile membuka menu dengan `aria-expanded`, serta fokus keyboard terlihat. Uji panah kanan pada tabel fokusable menggeser `scrollLeft` dari 0 menjadi 1.6 px. Semua pembungkus overflow daftar Admin/Member kini fokusable sebagai region bernama dengan outline saat fokus. Percobaan akses browser ke `/member/dashboard` pada sesi Admin mendapat respons Forbidden; QA visual Member membutuhkan sesi Member yang sah dan uji keyboard penuh lintas halaman. |
| R-20 | Selesai | Audit baca-saja menemukan kandidat untuk ditinjau, tanpa perubahan: HKI ID 10 (`judul/nomor permohonan` placeholder `asd`), ID 11 (`asdsad`/`12212`), serta ID 12 dengan nomor `TEST-UX-20261022-01`; direktori publik saat ini menampilkan semua record HKI tanpa flag publikasi. Usulan: konfirmasi pemilik konten; ganti dengan contoh yang disetujui atau arsipkan melalui soft delete setelah persetujuan. FAQ ID 5 aktif berisi pertanyaan percakapan uji “Bang ini gimana bang”; usulan: ganti dengan FAQ dan jawaban resmi yang disetujui atau nonaktifkan. FAQ ID 4 dengan pertanyaan sama telah terarsip. Pengurus aktif ID 4 dan 5 sama-sama memiliki `sort_order=1`; usulan: Admin menentukan urutan benar, lalu ubah ke urutan unik. Tidak ada record yang diubah/dihapus dan nama personal tidak dicantumkan pada laporan. |

P2 tidak mencakup pembersihan data. R-16 dan R-19 masih memerlukan pemeriksaan visual halaman internal Member serta audit menyeluruh lintas halaman. Proposal R-20 siap ditinjau; keputusan pemilik konten diperlukan sebelum kandidat diganti, dinonaktifkan, atau diarsipkan.

### Catatan verifikasi lanjutan — 2026-10-28

- **R-16 — Dalam pengerjaan:** Audit form menemukan field pencipta Rahasia Dagang disembunyikan pada UI tetapi diwajibkan validasi dan skema. UI/validasi kini mengikuti field domain Rahasia Dagang (nama/kategori, pemilik, tanggal mulai, status rahasia); `creator` dibuat nullable untuk tipe ini. Regression test HKI lulus (7 test, 47 assertion); migration disiapkan dan belum dijalankan. Halaman login pada viewport 390px dan 1440px tidak mengalami overflow dan semua input berlabel. QA visual internal Member dan audit visual form Admin lintas halaman tetap tertunda karena browser tidak memiliki sesi Member/Admin yang sah.
- **R-19 — Dalam pengerjaan:** QA browser halaman login pada 390px dan 1440px menunjukkan lebar dokumen sesuai viewport. QA visual Member serta validasi runtime gulir keyboard belum dapat dilakukan tanpa sesi Member yang sah. Audit Admin lintas 16 route dan temuan awal keyboard tetap dicatat pada riwayat di atas.

### Catatan pengerjaan audit panel Admin — 2026-10-28

Fokus audit ini adalah perbaikan fungsional Admin. Status berikut melengkapi, bukan menghapus, catatan audit sebelumnya.

| Temuan audit | Status | Hasil / bukti |
|---|---|---|
| Status permohonan tidak mengikuti pembayaran verified | Selesai untuk alur saat ini; data lama perlu tinjauan | Jalur verifikasi yang sekarang mengubah invoice menjadi paid dan permohonan menjadi completed dalam satu transaksi. Ditambahkan assertion regresi pada aktivasi. Snapshot baca-saja invoice `TAG-2026-000013` sebelumnya menemukan payment verified dan invoice paid sementara permohonan masih `approved_waiting_payment`; detail akun dan permohonan sekarang menandai mismatch serta menghubungkan ke tagihan. Record lama tidak diubah. |
| Filter tipe/status akun Admin | Dalam verifikasi | Perbaikan mengambil nilai dari FormData form, menambahkan status profil lengkap dan pencarian nama/email/nomor anggota. Feature test filter institusi, kelengkapan, dan pencarian ditambahkan. |
| Periode tarif kosong saat ubah dan pesan validasi | Selesai berdasarkan pemeriksaan source/build | Date-only disiapkan sebagai `YYYY-MM-DD` tanpa konversi timezone; label periode berlaku tarif dipisahkan dari durasi keanggotaan. Request memiliki nama atribut dan pesan validasi Bahasa Indonesia. |
| Tiket tertutup masih dapat dijawab | Dalam verifikasi | Form UI hanya menawarkan buka kembali; server menolak jawaban langsung atas tiket tertutup, transisi reopen dan answer dites, dan pembaruan memakai transaksi/lock. |
| Nilai dashboard tidak menjelaskan rentang | Selesai berdasarkan query | Label nominal diganti menjadi “Nilai Tagihan Lunas (sepanjang waktu)”; query menjumlah invoice paid tanpa filter tanggal. |
| Menu dokumen aktif bersamaan dengan pengaturan | Selesai berdasarkan route match | Link daftar memakai pencocokan route index yang persis agar tidak aktif pada halaman pengaturan. |

Item audit Admin lainnya (termasuk perluasan detail invoice/pembayaran, pencarian HKI, laporan, serta audit visual lintas modul) belum seluruhnya ditangani. Status keseluruhan R-16/R-19 tetap Dalam pengerjaan sampai acceptance criteria lintas halaman dan QA visual selesai.

### Catatan pengerjaan tampilan Member — 2026-10-28

| Area | Status | Hasil / batas verifikasi |
|---|---|---|
| Profil Member | Dalam verifikasi | Field wajib sekarang mengikuti aturan `MemberProfileUpdateRequest`, termasuk penanda wajib, `aria-invalid`, validasi nama/email/telepon/alamat, dan field institusi bersyarat; tombol menampilkan keadaan simpan/sukses. |
| Permohonan/perpanjangan | Dalam verifikasi | Pemberitahuan jendela perpanjangan memakai formatter date-only bersama dan menyebut tanggal mulai jendela serta akhir keanggotaan. Riwayat status menampilkan waktu WIB dan empty state; tombol kirim menunjukkan proses dan mencegah klik ganda. Aturan tanggal tetap berasal dari D-02. |
| Tagihan/pembayaran | Dalam verifikasi | Detail bukti menampilkan waktu kirim dan waktu pemeriksaan, tautan bukti memakai `rel="noopener noreferrer"`, dan state rekening belum dikonfigurasi tetap dijelaskan. |
| Navigasi konten Member | Dalam verifikasi | Pusat bantuan menggunakan Bahasa Indonesia; menu pengaturan menyesuaikan label berdasarkan kelengkapan profil; halaman sertifikat/kartu memakai label Indonesia dan menjelaskan keadaan kosong. |
| R-16 / R-19 | Dalam pengerjaan | Regression suites Member terkait lulus dan Vite build berhasil. QA browser responsif/keyboard untuk halaman Member belum dilakukan karena sesi Member berwenang tidak tersedia. Audit visual seluruh route Member masih terbuka. |

### Catatan lanjutan tampilan Member — 2026-10-28

- **Ringkasan keanggotaan:** riwayat kini memuat relasi tagihan, menampilkan nomor tagihan yang dapat dibuka (bukan ID internal), dan membedakan periode aktif, akan dimulai, serta berakhir berdasarkan tanggal server dan status membership. Perubahan ini menjaga perbedaan nilai status tersimpan dengan masa berlaku tanggal.
- **Detail HKI:** label untuk Rahasia Dagang kini memakai “Pemilik” dan tidak menampilkan field pencipta yang tidak berlaku. Nilai pencipta kosong pada record lama ditampilkan sebagai tanda kosong.
- **Tagihan dan tiket Member:** jika pembayaran ditolak, detail tagihan memberi arahan untuk membaca catatan Admin dan mengirim bukti baru; detail tiket menunjukkan waktu pengajuan dan jawaban dalam timezone bisnis.
- **Verifikasi 2026-10-28:** `MemberAccountAndMembershipTest` lulus (3 test, 26 assertion), termasuk status periode aktif/berakhir dan tautan nomor tagihan; `IntellectualPropertyTest` lulus (7 test, 47 assertion); `PaymentAndMembershipActivationTest` lulus (10 test, 100 assertion); `npm run build` berhasil. Status R-16/R-19 tetap Dalam pengerjaan hingga QA visual browser desktop/mobile dan keyboard dilakukan.

### Hasil QA browser Member lanjutan — 2026-10-28

- Sesi Member yang sudah aktif diperiksa secara baca-saja. Pada viewport 390px: dashboard keanggotaan, profil, permohonan, detail tagihan, detail tiket, dan daftar HKI tidak memperlebar dokumen melewati viewport; daftar HKI diuji pada 390px dan 1536px. Menu mobile dapat dibuka dan fokus Tab bergerak ke tautan navigasi. Tabel HKI dapat digulir memakai ArrowRight (nilai `scrollLeft` berubah dari 0); membership/tiket juga diberi lebar minimum supaya kolom tidak terjepit. Override viewport di-reset dan tab dikembalikan ke halaman awal.
- QA menemukan pada sesi tersebut satu riwayat permohonan belum `completed` walau invoice terkait paid, bukti verified, dan keanggotaan aktif. UI Member kini memberi penjelasan serta tautan ke invoice saat kombinasi itu ditemukan. Ini murni indikator baca-saja; data aplikasi tidak diubah.
- Test final setelah perubahan indikator: `MemberAccountAndMembershipTest` lulus (4 test, 38 assertion), `MembershipApplicationTest` lulus (4 test, 42 assertion), dan `npm run build` berhasil. R-16/R-19 tetap **Dalam pengerjaan** karena QA visual/keyboard belum mencakup seluruh route dan seluruh keadaan formulir di desktop/mobile.

### Perbaikan tindak lanjut temuan Member — 2026-10-28

- **Form HKI legacy (R-13/R-16):** nilai date-only dikirim ke input sebagai `YYYY-MM-DD`; QA Chrome pada record yang tersedia menampilkan tanggal pengajuan dan tanggal pengumuman sebagai `2026-10-22`. Rincian khusus tipe HKI tetap wajib untuk record baru dan saat jenis HKI berubah. Saat edit record lama dengan rincian nullable yang memang belum tercatat, field tersebut ditandai belum tercatat dan tidak memblokir penyimpanan; tanggal pengumuman Hak Cipta tetap wajib sesuai skema `NOT NULL`.
- **Mismatch permohonan/payment (P0):** alur verifikasi baru tetap menandai permohonan selesai dalam transaksi. Untuk data historis, Admin kini dapat merekonsiliasi satu permohonan dari halaman detail hanya jika invoice paid, ada pembayaran verified, dan keanggotaan terkait sudah tercatat. Endpoint mengunci transaksi dan menambahkan entri histori; tidak membuat ulang membership/dokumen. Test juga memastikan permohonan tanpa membership tidak berubah. Dry-run baca-saja pada database aplikasi mengonfirmasi satu kandidat: `TAG-2026-000013` (invoice 11, permohonan 13) berstatus paid, memiliki 1 pembayaran verified dan 1 membership, sementara status permohonan masih `approved_waiting_payment`. Dampak koreksi terbatas pada status menjadi `completed` dan satu entri histori Admin; belum diterapkan sambil menunggu persetujuan eksplisit setelah dry-run.
- **FAQ berisi teks uji:** dibiarkan tanpa perubahan karena teks pengganti perlu disetujui pemilik konten; tidak ada teks resmi dalam repository.
- **Verifikasi:** `PaymentAndMembershipActivationTest` lulus (12 test/108 assertion); hasil akhir `IntellectualPropertyTest` dan build dicatat setelah verifikasi ulang. Tidak ada migration atau perubahan data aplikasi yang dijalankan.
- **R-16/R-19:** tetap Dalam pengerjaan karena audit visual dan keyboard lengkap lintas route belum selesai.

### Penyesuaian form HKI mengikuti referensi klien — 2026-10-28

- Form Member HKI memakai panel terpusat dengan header hijau, aksen kuning, heading bagian, susunan dua kolom pada layar lebar, placeholder, tombol aksi, keterangan wajib, dan footer sesuai komposisi referensi. Susunan berubah menjadi satu kolom di viewport kecil; selector Jenis HKI tetap terlihat karena dibutuhkan untuk menampilkan field domain yang sesuai.
- Untuk Hak Cipta, urutan field mengikuti gambar klien: nomor dan tanggal permohonan; pencipta dan pemegang hak; jenis ciptaan dan nomor pencatatan; judul ciptaan; tanggal pertama diumumkan dan jangka waktu pelindungan. Jenis Ciptaan memakai dropdown kategori Pasal 40 UU 28/2014 dengan pilihan `Lainnya` yang tetap menerima teks bebas.
- Opsi tanggal tetap menggunakan kontrol date HTML dengan format date-only aplikasi; label tetap menyesuaikan jenis HKI. Field wajib dan validasi server tetap dipertahankan.
- QA Chrome setelah penataan urutan: field Hak Cipta terverifikasi satu per satu pada lebar 1536 px dan 390 px; pada mobile `document.documentElement.scrollWidth` 375 px terhadap viewport 390 px. Build Vite dan regression test HKI berhasil.

### Perbaikan revisi permohonan dan Profil Pemohon Admin — 2026-10-29

- **R-11 — Dalam pengerjaan:** jalur pengajuan ulang permohonan revisi kini harus memakai form revisi; endpoint pengajuan biasa tidak lagi mengubah status revisi tanpa pembaruan data. Validasi server mengikuti field yang diminta Admin, perubahan dibatasi ke field tersebut, dan catatan revisi lama tanpa daftar field tetap dapat diedit. Perubahan tipe akun ke institusi meminta nama institusi dan penanggung jawab.
- **R-16 — Dalam pengerjaan:** Profil Pemohon Admin menampilkan nama, email, jenis akun, telepon, alamat, serta data institusi dalam layout responsif. Belum dilakukan QA visual browser atau verifikasi keyboard untuk perubahan ini.
- **Verifikasi:** lint PHP controller berhasil dan `git diff --check` bersih. Test feature dan Vite build tidak dijalankan pada perubahan ini; belum ada perubahan skema atau data aplikasi.
