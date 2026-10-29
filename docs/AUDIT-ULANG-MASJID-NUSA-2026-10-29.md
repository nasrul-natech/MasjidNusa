# Audit Ulang Masjid Nusa — 29 Oktober 2026 — Putaran 2

## Kesimpulan

**Perbaikan sudah memberi kemajuan nyata, tetapi belum layak dinyatakan selesai seluruhnya.** Dari 24 temuan sebelumnya:

- **15 tertangani pada tingkat kode/probe terisolasi.**
- **8 tertangani sebagian.**
- **1 belum tertangani**, yaitu F13: alur 2FA untuk sesi yang sudah terautentikasi.

Status “tertangani” berarti akar masalah yang dicatat sebelumnya sudah diperbaiki pada kode yang diperiksa. Status ini bukan pengesahan produksi, bukan persentase keamanan, dan tidak berarti seluruh jalur terkait bebas masalah. Contohnya, idempotensi settlement dasar sudah bekerja (F02), tetapi upload yang bertabrakan dengan settlement masih dapat membuka kembali donasi (F03).

Pemeriksaan lebih dalam menghasilkan **12 kelompok tindak lanjut: 4 tinggi dan 8 sedang**, mencakup sisa masalah lama dan perluasan pengujian. Jumlah ini tidak ditambahkan ke 24 temuan lama.

**Empat prioritas utama sebelum menutup perbaikan:** race upload/settlement (R01), cache HTML personal (R02), akses bukti petugas tanpa 2FA/permission (R03), dan sinkronisasi saldo yang menimpa pembayaran baru (R04).

## Ruang lingkup dan keamanan pemeriksaan

Audit membaca working tree lokal, bukan hanya HEAD Git. Pada awal pemeriksaan terdapat 83 file tracked yang sudah berubah dan beberapa file untracked milik pekerjaan pengguna. Seluruh perubahan tersebut dipertahankan.

Tidak ada kode aplikasi, konfigurasi, database operasional, aset build, atau data pengguna yang diubah oleh audit. Keluaran baru hanya laporan dan bukti audit. Laporan pertama tetap dipertahankan sebagai riwayat: [audit awal](/Users/nasrul/Herd/update/MasjidNusa/docs/AUDIT-SISTEM-MASJID-NUSA-2026-10-29.md).

Probe PHP memuat autoloader dan kode tertentu tanpa mem-boot Laravel, tanpa membaca .env, dan tanpa menghubungkan database aplikasi. Skema serta data buatan dibuat hanya di SQLite `:memory:`. Email, notifikasi, response factory, dan penulisan file tertentu diganti stub/sink. Simulasi race menggunakan urutan eksekusi deterministik; penguncian produksi MySQL dan HTTP konkuren nyata belum diuji.

## Hasil verifikasi

- **175 file PHP** lulus `php -n -l`.
- **4 skrip deployment** lulus `bash -n`; skrip tidak dieksekusi sebagai deployment.
- Semua aset yang dirujuk manifest lokal tersedia; ini tidak membuktikan versi aset di server.
- `git diff --check` tidak menemukan masalah whitespace.
- Hash **386 berkas** yang diambil sebelum probe tetap sama setelah pemeriksaan; tidak ada perubahan sumber oleh audit.
- Harness PHP selesai dengan exit code 0 dan menghasilkan **41 catatan hasil**, bukan 41 tes aplikasi yang semuanya dinyatakan lulus.
- Probe settlement: pembayaran pertama menambah Rp10.000, pembayaran kedua tidak menambah, callback gagal setelah paid tidak menurunkan status. Exception dalam callback transaksi mengembalikan status pending/saldo nol.
- Probe controller upload aktual: settlement disisipkan saat file writer buatan dipanggil; status kembali waiting_verification dan settlement berikutnya menghasilkan total Rp20.000 untuk satu donasi Rp10.000.
- Probe rekonsiliasi: setelah sync membaca snapshot lama, pembayaran baru masuk; update sync kemudian meninggalkan total Rp0 walaupun transaksi paid berjumlah Rp10.000.
- Probe callback aktual: payload uji dengan signature valid untuk nominal/reference yang tidak sesuai data lokal tetap diterima. Kunci signature dan seluruh transaksi adalah dummy.
- Probe command adzan aktual: offset 5 menit bekerja untuk WIB/WITA/WIT, tetapi tetap menghasilkan pengiriman ke sink ketika modul mati dan untuk akun nonaktif; dua eksekusi pada menit yang sama menggandakan jumlah pengiriman.
- Probe service worker: profil dan bukti dikecualikan, tetapi Quran/khatam/reset-password dan beranda masih dicache serta dikembalikan saat offline.
- Tiga probe sanitizer dasar dan dua belas probe redirect menunjukkan perbaikan yang diharapkan.
- Probe CSV menetralkan empat prefix formula dasar; tab sebelum formula belum diproses helper. Dampak nyata pada data lama/spreadsheet belum diuji.
- Tidak menjalankan `php artisan test`, migration, seeder, build, deployment, transaksi gateway, email, push, atau koneksi produksi. Suite tes aplikasi yang ada belum mencakup skenario audit baru.

[Bukti output probe PHP](/Users/nasrul/Herd/update/MasjidNusa/docs/BUKTI-AUDIT-ULANG-MASJID-NUSA-2026-10-29.jsonl). Harness sementara: [probe terisolasi](/private/tmp/masjidnusa-reaudit-probes.php). Harness ini alat audit, bukan suite regresi produksi.

## Status seluruh temuan sebelumnya

### F01 — Tertangani: Callback CSRF

Pengecualian hanya untuk webhook/duitku/callback sudah ditambahkan; verifikasi signature tetap berjalan.

Bukti: [bootstrap/app.php:24](/Users/nasrul/Herd/update/MasjidNusa/bootstrap/app.php:24).

### F02 — Tertangani: Duplikasi settlement biasa

settle() memakai transaksi dan lockForUpdate. Probe: panggilan kedua tidak mengubah saldo, callback gagal setelah paid tidak menurunkan status, dan exception mengembalikan perubahan. Status ini khusus jalur settlement; interaksi upload tetap dibahas pada F03/R01.

Bukti: [app/Models/Donation.php:58](/Users/nasrul/Herd/update/MasjidNusa/app/Models/Donation.php:58).

### F03 — Sebagian: Transisi pembayaran

Upload setelah status final sudah ditolak pada urutan normal, tetapi pemeriksaan upload/approve dilakukan sebelum lock sehingga interleaving masih membuka status atau menyetujui donasi yang baru ditolak. Lihat R01.

Bukti: [app/Http/Controllers/User/DonationUserController.php:132](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/User/DonationUserController.php:132); [app/Models/Donation.php:65](/Users/nasrul/Herd/update/MasjidNusa/app/Models/Donation.php:65).

### F04 — Tertangani: Akun nonaktif

EnsureUserIsActive dipasang pada web, revokeAccess menghapus remember token dan sesi database, dan completeLogin 2FA mengecek aktif. Pengiriman push background ke akun nonaktif adalah jalur lain yang belum diperbaiki, lihat R09.

Bukti: [bootstrap/app.php:30](/Users/nasrul/Herd/update/MasjidNusa/bootstrap/app.php:30); [app/Http/Middleware/EnsureUserIsActive.php:13](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Middleware/EnsureUserIsActive.php:13); [app/Models/User.php:77](/Users/nasrul/Herd/update/MasjidNusa/app/Models/User.php:77).

### F05 — Tertangani: Password lama

required_with:password dan Hash::check kini memblokir perubahan tanpa password lama yang benar. Penilaian berdasarkan kode, bukan uji login HTTP.

Bukti: [app/Http/Controllers/User/ProfileController.php:29](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/User/ProfileController.php:29).

### F06 — Tertangani: Password reset tetap

Password123! diganti Str::password(16), disimpan melalui hashed cast, dan revokeAccess dipanggil. Password sementara masih ditampilkan kepada admin; alur token sekali pakai tetap dapat dipertimbangkan.

Bukti: [app/Http/Controllers/SuperAdmin/UserController.php:156](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/SuperAdmin/UserController.php:156); [app/Models/User.php:51](/Users/nasrul/Herd/update/MasjidNusa/app/Models/User.php:51).

### F07 — Sebagian: Cache privat

Prefix /user, /admin, /super-admin, /two-factor dan bukti sudah dikecualikan; versi cache naik. Tetapi /quran, program khatam, halaman reset serta HTML publik berisi notifikasi pengguna tetap dicache. Lihat R02.

Bukti: [public/sw.js:46](/Users/nasrul/Herd/update/MasjidNusa/public/sw.js:46).

### F08 — Tertangani: HTML konten editor

Empat output publik terkait kini memanggil HtmlSanitizer. Tiga probe dasar menghapus script, event handler, elemen gambar berbahaya dan javascript: URL, serta mempertahankan teks/tautan HTTPS. Ini bukan sertifikasi ketahanan terhadap seluruh variasi parser/XSS.

Bukti: [app/Support/HtmlSanitizer.php:20](/Users/nasrul/Herd/update/MasjidNusa/app/Support/HtmlSanitizer.php:20); [resources/views/public/dakwah/show.blade.php:34](/Users/nasrul/Herd/update/MasjidNusa/resources/views/public/dakwah/show.blade.php:34).

### F09 — Sebagian: Permission

Rute admin utama kini memakai permission. Masih ada endpoint bukti yang mengandalkan role tanpa 2FA/permission dan izin publish_content belum ditegakkan. Lihat R03/R08.

Bukti: [routes/web.php:205](/Users/nasrul/Herd/update/MasjidNusa/routes/web.php:205); [routes/web.php:238](/Users/nasrul/Herd/update/MasjidNusa/routes/web.php:238); [routes/web.php:308](/Users/nasrul/Herd/update/MasjidNusa/routes/web.php:308).

### F10 — Tertangani: Penghapusan public_html

hostinger-update.sh kini berhenti jika symlink tidak sesuai, tanpa rm -rf. Skrip deployment tidak dijalankan.

Bukti: [deploy/hostinger-update.sh:19](/Users/nasrul/Herd/update/MasjidNusa/deploy/hostinger-update.sh:19).

### F11 — Tertangani: Donasi cepat

quickDonate memeriksa metode aktif, menolak kategori tanpa kampanye aktif, serta memakai startPayment yang sama untuk manual/gateway. UI donasi/index benar-benar memanggil endpoint ini; audit awal belum menemukan pemakaian UI tersebut.

Bukti: [app/Http/Controllers/User/DonationUserController.php:37](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/User/DonationUserController.php:37); [resources/views/public/donasi/index.blade.php:324](/Users/nasrul/Herd/update/MasjidNusa/resources/views/public/donasi/index.blade.php:324).

### F12 — Sebagian: Bukti transfer privat

Upload baru memakai local/private dan URL controller. Bukti lama baru dipindahkan saat dibuka lewat controller; keberadaan sisa file publik belum diperiksa. Lihat R06; otorisasi endpoint baru juga perlu R03.

Bukti: [app/Http/Controllers/User/DonationUserController.php:141](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/User/DonationUserController.php:141); [app/Http/Controllers/User/DonationUserController.php:173](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/User/DonationUserController.php:173).

### F13 — Belum tertangani: 2FA sesi sudah login

Challenge/setup tetap berada di guest, sedangkan middleware admin mengirim user yang sudah login ke sana. Probe registrasi rute memastikan middleware guest masih ada. Lihat R07.

Bukti: [routes/web.php:104](/Users/nasrul/Herd/update/MasjidNusa/routes/web.php:104); [routes/web.php:119](/Users/nasrul/Herd/update/MasjidNusa/routes/web.php:119); [app/Http/Middleware/EnsureTwoFactorVerified.php:19](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Middleware/EnsureTwoFactorVerified.php:19).

### F14 — Tertangani: TypeError setup 2FA

resolvePendingUser kini ?User dan mengembalikan null; showSetup/confirmSetup menangani null dengan redirect. Tidak lagi mengembalikan RedirectResponse sebagai User.

Bukti: [app/Http/Controllers/Auth/TwoFactorController.php:71](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/Auth/TwoFactorController.php:71); [app/Http/Controllers/Auth/TwoFactorController.php:125](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/Auth/TwoFactorController.php:125).

### F15 — Tertangani: Offset adzan

Filter waktu sholat yang bertentangan sudah dihapus. Command aktual dengan offset 5 menit mengirim pada waktu yang diharapkan menggunakan jam terkontrol; pengiriman nyata diganti sink uji.

Bukti: [app/Console/Commands/SendAdzanNotifications.php:66](/Users/nasrul/Herd/update/MasjidNusa/app/Console/Commands/SendAdzanNotifications.php:66).

### F16 — Tertangani: Zona waktu scheduler

timezone() memetakan provinsi ke WIB/WITA/WIT dan command memakai now(timezone). Probe memverifikasi lima provinsi dan pemicu tiga zona. Tanggal di UI/API dan seluruh variasi nama provinsi belum diuji.

Bukti: [app/Models/PrayerLocation.php:50](/Users/nasrul/Herd/update/MasjidNusa/app/Models/PrayerLocation.php:50); [app/Console/Commands/SendAdzanNotifications.php:27](/Users/nasrul/Herd/update/MasjidNusa/app/Console/Commands/SendAdzanNotifications.php:27).

### F17 — Sebagian: Rate limit reset

throttle:5,1 sudah ditambahkan, tetapi token tetap dibuat langsung per request tanpa pembatasan per akun. Banyak IP masih dapat menarget satu email. Lihat R12.

Bukti: [routes/web.php:110](/Users/nasrul/Herd/update/MasjidNusa/routes/web.php:110); [app/Http/Controllers/Auth/PasswordResetController.php:30](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/Auth/PasswordResetController.php:30).

### F18 — Tertangani: Open redirect

Ketiga controller kini menolak //host, backslash dan URL absolut. Dua belas probe helper mempertahankan path internal dan mengembalikan input eksternal uji ke /.

Bukti: [app/Http/Controllers/Auth/LoginController.php:115](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/Auth/LoginController.php:115).

### F19 — Sebagian: Modul nonaktif

Modul donasi sudah diterapkan pada rute, popup dicek pada render, subscription push diberi middleware. Pengiriman push yang memakai subscription lama belum memeriksa flag modul. Lihat R09. Callback settlement tidak harus dihentikan ketika donasi baru ditutup.

Bukti: [routes/web.php:140](/Users/nasrul/Herd/update/MasjidNusa/routes/web.php:140); [routes/web.php:310](/Users/nasrul/Herd/update/MasjidNusa/routes/web.php:310); [resources/views/components/popup-ad.blade.php:2](/Users/nasrul/Herd/update/MasjidNusa/resources/views/components/popup-ad.blade.php:2).

### F20 — Tertangani: Dependency build

Saat perlu build, npm memakai --include=dev. Jika manifest sudah ada, build dilewati: perbaikan dependency selesai, tetapi proses rilis harus menjamin aset tersebut sesuai revisi sumber.

Bukti: [deploy/hostinger-php.sh:58](/Users/nasrul/Herd/update/MasjidNusa/deploy/hostinger-php.sh:58).

### F21 — Tertangani: Jejak koreksi saldo

adjustAmount dan syncAmount kini merekam nilai sebelum/sesudah serta alasan. Perubahan saldo dan audit belum atomik, dan sync masih dapat menimpa increment yang bersamaan; lihat R04.

Bukti: [app/Http/Controllers/Admin/Keuangan/CampaignAdminController.php:188](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/Admin/Keuangan/CampaignAdminController.php:188).

### F22 — Sebagian: Super-admin terakhir

Edit biasa kini memeriksa minimal satu admin aktif. Count dan update/syncRoles tetap terpisah tanpa penguncian; perubahan paralel masih berisiko. Lihat R10.

Bukti: [app/Http/Controllers/SuperAdmin/UserController.php:93](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/SuperAdmin/UserController.php:93).

### F23 — Sebagian: Identitas Google

Mismatch google_id untuk email yang sama kini ditolak. Pencarian tetap memakai email dan akun lokal tanpa google_id tetap ditautkan otomatis. Perubahan email pada subject yang sama belum ditangani. Lihat R11.

Bukti: [app/Http/Controllers/Auth/GoogleAuthController.php:42](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/Auth/GoogleAuthController.php:42).

### F24 — Tertangani: Formula CSV dasar

Kolom teks kini melalui csvText; probe =,+,-,@ mendapat prefix apostrof. Input tab di depan formula belum dinetralisasi helper; sebagian input aplikasi sudah di-trim. Kompatibilitas Excel/LibreOffice dan data lama tetap perlu diuji, bukan dianggap aman untuk seluruh format.

Bukti: [app/Http/Controllers/Admin/Keuangan/ReportController.php:70](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/Admin/Keuangan/ReportController.php:70); [app/Http/Controllers/Admin/Keuangan/ReportController.php:86](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/Admin/Keuangan/ReportController.php:86).

## Masalah yang masih perlu ditangani

### R01 — Tinggi: Race pada unggah bukti masih dapat menggandakan saldo

**Hubungan dengan audit lama:** F03; terkait F02.

**Bukti kode:** [app/Http/Controllers/User/DonationUserController.php:125](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/User/DonationUserController.php:125); [app/Http/Controllers/User/DonationUserController.php:141](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/User/DonationUserController.php:141); [app/Models/Donation.php:65](/Users/nasrul/Herd/update/MasjidNusa/app/Models/Donation.php:65); [app/Http/Controllers/Admin/Keuangan/PaymentVerificationController.php:34](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/Admin/Keuangan/PaymentVerificationController.php:34).

uploadProof membaca status tanpa lock, lalu menyimpan file/bukti dan menulis waiting_verification berdasarkan objek lama. Ia juga menerima transaksi Duitku. settle hanya menolak paid/verified; syarat waiting_verification milik approve diperiksa di luar transaksi.

**Hasil pemeriksaan:** Controller upload aktual dijalankan dengan file writer buatan yang memanggil settlement di tengah proses. Respons 302; status yang sudah paid menjadi waiting_verification; settlement kedua membuat saldo Rp10.000 menjadi Rp20.000. Probe terpisah menunjukkan approve yang sudah membaca status lama tetap dapat settle setelah reject. Validasi upload dan penulisan file diganti stub; tidak ada HTTP paralel nyata.

**Pemicu/batas dampak:** Donasi gateway pending dan unggah bukti berjalan berdekatan dengan callback, atau approve/reject dilakukan bersamaan. Ini bukan klaim bahwa setiap upload menggandakan saldo.

**Arah perbaikan dan verifikasi:** Periksa status dan metode di dalam transaksi/lock yang sama dengan perubahan. Upload bukti hanya untuk metode yang memang mendukungnya; settlement menerima daftar status asal yang eksplisit. Uji upload vs callback dan approve vs reject pada MySQL staging.

### R02 — Tinggi: Cache tetap menyimpan program khatam dan notifikasi pribadi

**Hubungan dengan audit lama:** F07.

**Bukti kode:** [public/sw.js:46](/Users/nasrul/Herd/update/MasjidNusa/public/sw.js:46); [public/sw.js:77](/Users/nasrul/Herd/update/MasjidNusa/public/sw.js:77); [app/Http/Controllers/Public/QuranController.php:32](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/Public/QuranController.php:32); [resources/views/components/notification-dropdown.blade.php:1](/Users/nasrul/Herd/update/MasjidNusa/resources/views/components/notification-dropdown.blade.php:1); [resources/views/layouts/public.blade.php:99](/Users/nasrul/Herd/update/MasjidNusa/resources/views/layouts/public.blade.php:99).

Blocklist baru belum mencakup /quran dan /quran/khatam. Selain itu layout publik menyisipkan notifikasi akun, sehingga halaman / yang tampak publik juga dapat berisi data pribadi. Halaman reset-password bertoken ikut dicache.

**Hasil pemeriksaan:** Simulasi sw.js aktual menunjukkan /user/profile dan /bukti-transfer/1 tidak diintersep, tetapi /quran, /quran/khatam/1, /reset-password/DUMMY_TOKEN dan / tetap disimpan lalu dikembalikan saat offline. Isi respons berupa penanda data uji; keberadaan data pribadi pada halaman aktual dikonfirmasi dari controller/view.

**Pemicu/batas dampak:** Perangkat bersama: pengguna A membuka halaman, logout, kemudian pengguna B/tamu mengakses URL yang sama saat offline. HTML lama masih berpotensi tampil.

**Arah perbaikan dan verifikasi:** Gunakan allowlist untuk aset/halaman yang benar-benar tidak personal. Jangan cache HTML terautentikasi atau halaman token. Pisahkan fragmen akun, dan bersihkan cache yang terlanjur disimpan saat logout/pergantian akun.

### R03 — Tinggi: Endpoint bukti baru melewati 2FA dan permission petugas

**Hubungan dengan audit lama:** F09/F12; jalur baru.

**Bukti kode:** [routes/web.php:308](/Users/nasrul/Herd/update/MasjidNusa/routes/web.php:308); [app/Http/Controllers/User/DonationUserController.php:158](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/User/DonationUserController.php:158).

GET /bukti-transfer/{proof} hanya memakai auth. showProof mengizinkan seluruh admin_keuangan/super_admin berdasarkan role, tanpa mengecek two_factor_verified maupun permission melihat/verifikasi donasi.

**Hasil pemeriksaan:** Registrasi rute aktual menghasilkan middleware [web,auth]. Pemeriksaan controller menunjukkan cabang hasAnyRole cukup untuk membaca bukti akun lain. Belum dilakukan unduhan file nyata.

**Pemicu/batas dampak:** Petugas yang sudah terautentikasi tetapi belum lolos 2FA pada sesi baru, atau permission keuangannya sudah dicabut, tetap memiliki jalur membaca bukti donatur lain.

**Arah perbaikan dan verifikasi:** Pisahkan akses pemilik dari akses petugas melalui policy. Cabang petugas wajib menjalankan 2FA dan permission yang sesuai. Uji kepemilikan, sesi remember-me, dan permission yang dicabut.

### R04 — Tinggi: Sinkronisasi saldo dapat menimpa pembayaran yang baru masuk

**Hubungan dengan audit lama:** Perluasan pengujian F02/F21.

**Bukti kode:** [app/Http/Controllers/Admin/Keuangan/CampaignAdminController.php:201](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/Admin/Keuangan/CampaignAdminController.php:201); [app/Http/Controllers/Admin/Keuangan/CampaignAdminController.php:188](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/Admin/Keuangan/CampaignAdminController.php:188).

syncAmount menghitung SUM lalu mengupdate kampanye tanpa transaksi/lock bersama settlement. Koreksi manual dan pencatatan audit juga terpisah.

**Hasil pemeriksaan:** Interleaving operasi model aktual: kampanye semula memiliki selisih Rp5.000; sync membaca total paid Rp0; donasi Rp10.000 selesai; sync kemudian menulis Rp0. Hasil akhir collected_amount Rp0 sementara SUM paid Rp10.000. Ini simulasi urutan deterministik di SQLite in-memory, bukan pembuktian locking MySQL.

**Pemicu/batas dampak:** Admin menekan sinkronisasi/koreksi bersamaan dengan callback atau verifikasi. Saldo yang ditampilkan dapat lebih kecil daripada transaksi yang sudah lunas.

**Arah perbaikan dan verifikasi:** Tentukan urutan lock konsisten untuk settlement dan rekonsiliasi. Catat penyesuaian/audit secara atomik. Uji interleaving sync dengan pembayaran sebelum menyatakan rekonsiliasi aman.

### R05 — Sedang: Callback bertanda tangan sah belum dicocokkan dengan rincian transaksi

**Hubungan dengan audit lama:** Perluasan F01.

**Bukti kode:** [app/Services/DuitkuService.php:86](/Users/nasrul/Herd/update/MasjidNusa/app/Services/DuitkuService.php:86); [app/Http/Controllers/Webhook/DuitkuWebhookController.php:34](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/Webhook/DuitkuWebhookController.php:34).

Verifikasi menghitung signature memakai amount dari payload tetapi tidak membandingkannya dengan amount transaksi lokal. Controller memakai latestTransaction tanpa mencocokkan reference/metode; merchantCode payload juga tidak dibandingkan.

**Hasil pemeriksaan:** Controller aktual menerima payload uji bersignature valid untuk Rp1.000 dengan reference/merchantCode berbeda; donasi lokal Rp10.000 berubah paid dan saldo bertambah Rp10.000, respons 200. Signature dibuat dengan kunci dummy milik harness.

**Pemicu/batas dampak:** Memerlukan callback valid yang tidak konsisten dengan data lokal, misalnya kesalahan integrasi/rekonsiliasi. Probe ini tidak menunjukkan pihak anonim dapat memalsukan signature gateway atau menguasai kunci.

**Arah perbaikan dan verifikasi:** Validasi bentuk payload, merchantCode, order, amount, reference dan payment_method_type terhadap transaksi yang tepat. Tolak ketidaksesuaian tanpa settlement; uji memakai sandbox gateway.

### R06 — Sedang: Bukti transfer lama tetap publik sampai dibuka satu per satu

**Hubungan dengan audit lama:** F12.

**Bukti kode:** [app/Http/Controllers/User/DonationUserController.php:173](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/User/DonationUserController.php:173); [config/filesystems.php:76](/Users/nasrul/Herd/update/MasjidNusa/config/filesystems.php:76).

Pemindahan dari disk public ke local hanya terjadi pada showProof saat file privat belum ada. URL publik lama melewati controller dan tidak memicu pemindahan.

**Hasil pemeriksaan:** Dikonfirmasi dari jalur kode; isi storage nyata sengaja tidak diperiksa sehingga jumlah bukti lama yang masih publik tidak diketahui. Cabang file sudah ada di kedua disk juga tidak menghapus salinan publik.

**Pemicu/batas dampak:** Instalasi yang pernah menerima bukti sebelum perbaikan dan masih menyajikan storage publik. Perbaikan upload baru tidak otomatis melindungi arsip lama.

**Arah perbaikan dan verifikasi:** Siapkan migrasi berkas dengan verifikasi salinan, inventaris seluruh bukti lama, dan blokir akses HTTP ke direktori bukti publik. Jangan menghapus sumber sebelum salinan privat tervalidasi.

### R07 — Sedang: Admin dengan sesi terpulihkan masih gagal menyelesaikan 2FA

**Hubungan dengan audit lama:** F13.

**Bukti kode:** [routes/web.php:104](/Users/nasrul/Herd/update/MasjidNusa/routes/web.php:104); [routes/web.php:119](/Users/nasrul/Herd/update/MasjidNusa/routes/web.php:119); [app/Http/Middleware/EnsureTwoFactorVerified.php:19](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Middleware/EnsureTwoFactorVerified.php:19); [app/Http/Controllers/Auth/TwoFactorController.php:20](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/Auth/TwoFactorController.php:20).

Challenge/setup tetap berada dalam guest dan masih mensyaratkan login.id. Middleware admin mengarahkan pengguna yang sudah login tanpa two_factor_verified ke rute tersebut.

**Hasil pemeriksaan:** Probe daftar middleware aktual memastikan guest masih dipasang. Middleware guest framework mengirim pengguna login ke home. Ini masalah akses/kelangsungan alur, bukan bypass 2FA panel.

**Pemicu/batas dampak:** Sesi dipulihkan lewat remember-me, atau role jamaah dipromosikan ketika sesi masih aktif. Pengguna harus logout/login ulang sebagai jalan memutar.

**Arah perbaikan dan verifikasi:** Sediakan state pending 2FA untuk pengguna yang sudah terautentikasi; challenge hanya terbuka dalam state itu dan tetap melarang akses panel sebelum verifikasi.

### R08 — Sedang: Izin publish_content belum membatasi publikasi

**Hubungan dengan audit lama:** F09.

**Bukti kode:** [database/seeders/RolesAndPermissionsSeeder.php:53](/Users/nasrul/Herd/update/MasjidNusa/database/seeders/RolesAndPermissionsSeeder.php:53); [routes/web.php:234](/Users/nasrul/Herd/update/MasjidNusa/routes/web.php:234); [app/Http/Controllers/Admin/MediaIT/ArticleAdminController.php:33](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/Admin/MediaIT/ArticleAdminController.php:33); [app/Http/Controllers/Admin/MediaIT/ArticleAdminController.php:90](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/Admin/MediaIT/ArticleAdminController.php:90).

publish_content tersedia sebagai permission, tetapi rute publish artikel/kegiatan hanya mensyaratkan manage_articles/manage_activities. Store/update juga menerima status publish tanpa permission tambahan.

**Hasil pemeriksaan:** Registrasi rute artikel.publish menunjukkan permission:manage_articles saja. Pencarian controller tidak menemukan pemeriksaan publish_content.

**Pemicu/batas dampak:** Super-admin mencabut izin publish namun tetap memberi izin edit: editor masih dapat menerbitkan konten lewat endpoint publish maupun simpan status.

**Arah perbaikan dan verifikasi:** Tegakkan publish_content pada seluruh transisi ke publish, bukan hanya tombol UI atau endpoint khusus. Pertahankan izin mengedit draft secara terpisah.

### R09 — Sedang: Push tidak mengikuti status modul/akun dan tidak mencegah pengiriman ulang

**Hubungan dengan audit lama:** F19; perluasan F15.

**Bukti kode:** [app/Console/Commands/SendAdzanNotifications.php:22](/Users/nasrul/Herd/update/MasjidNusa/app/Console/Commands/SendAdzanNotifications.php:22); [app/Console/Commands/SendAdzanNotifications.php:62](/Users/nasrul/Herd/update/MasjidNusa/app/Console/Commands/SendAdzanNotifications.php:62); [app/Services/NotificationService.php:37](/Users/nasrul/Herd/update/MasjidNusa/app/Services/NotificationService.php:37); [routes/console.php:17](/Users/nasrul/Herd/update/MasjidNusa/routes/console.php:17).

Middleware modul baru membatasi subscribe/unsubscribe, tetapi command dan service pengiriman tidak memeriksa SystemModule. Command juga tidak menyaring user.is_active dan tidak mencatat pengiriman per user/waktu.

**Hasil pemeriksaan:** Command aktual dengan jam tetap, 3 lokasi dan offset 5 menit menghasilkan 3 pengiriman ke sink uji walaupun push_notification=false, termasuk 1 akun nonaktif. Dijalankan dua kali pada menit yang sama menghasilkan total 6. Tidak ada jaringan/push nyata.

**Pemicu/batas dampak:** Subscription lama masih aktif setelah modul/akun dinonaktifkan; cron tumpang tindih, konfigurasi dua scheduler, atau pengulangan manual dalam menit yang sama menyebabkan duplikasi.

**Arah perbaikan dan verifikasi:** Periksa flag modul dan status aktif sebelum kirim; tambahkan kunci deduplikasi persisten per user/lokasi/sholat/tanggal. Pastikan unsubscribe tetap dapat dilakukan ketika modul ditutup.

### R10 — Sedang: Perlindungan super-admin terakhir belum aman terhadap request paralel

**Hubungan dengan audit lama:** F22.

**Bukti kode:** [app/Http/Controllers/SuperAdmin/UserController.php:90](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/SuperAdmin/UserController.php:90); [app/Http/Controllers/SuperAdmin/UserController.php:103](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/SuperAdmin/UserController.php:103).

Pengecekan count admin aktif dilakukan terpisah dari update/syncRoles tanpa transaksi atau lock.

**Hasil pemeriksaan:** Skenario statis: dua super-admin aktif masing-masing membaca count=2, lalu keduanya menonaktifkan/menurunkan role diri lewat update. Kedua request dapat lolos dan menyisakan nol super-admin aktif. Belum diuji dengan request konkuren nyata.

**Pemicu/batas dampak:** Dua perubahan admin yang bersamaan. Guard untuk satu request biasa sudah membaik dan tidak dinyatakan masih gagal pada skenario itu.

**Arah perbaikan dan verifikasi:** Serialisasikan perubahan keanggotaan admin dan periksa invariant dalam transaksi yang sama. Terapkan pada update, toggle, bulk dan delete sesuai kebijakan.

### R11 — Sedang: Penautan Google masih berbasis email

**Hubungan dengan audit lama:** F23.

**Bukti kode:** [app/Http/Controllers/Auth/GoogleAuthController.php:42](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/Auth/GoogleAuthController.php:42); [app/Http/Controllers/Auth/GoogleAuthController.php:67](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/Auth/GoogleAuthController.php:67); [database/migrations/2026_08_30_000001_add_auth_security_fields_to_users_table.php:12](/Users/nasrul/Herd/update/MasjidNusa/database/migrations/2026_08_30_000001_add_auth_security_fields_to_users_table.php:12).

Mismatch google_id sudah ditolak, tetapi lookup tidak mencari subject terlebih dahulu. Akun lokal tanpa google_id langsung ditautkan lewat email.

**Hasil pemeriksaan:** Dikonfirmasi dari kode. Bila subject yang sama datang dengan email baru, kode masuk cabang create, sementara google_id memiliki unique index: berpotensi gagal dengan konflik, bukan memperbarui identitas yang sama.

**Pemicu/batas dampak:** Perubahan email identitas Google/Workspace atau penautan akun lokal yang sudah ada. Tidak ada klaim pengambilalihan akun Gmail biasa berhasil.

**Arah perbaikan dan verifikasi:** Cari identitas berdasarkan google_id terlebih dahulu; gunakan penautan eksplisit untuk akun lokal yang sudah ada dan kebijakan pembaruan email yang tervalidasi.

### R12 — Sedang: Reset password baru dibatasi per IP, belum per akun

**Hubungan dengan audit lama:** F17.

**Bukti kode:** [routes/web.php:110](/Users/nasrul/Herd/update/MasjidNusa/routes/web.php:110); [app/Http/Controllers/Auth/PasswordResetController.php:30](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/Auth/PasswordResetController.php:30).

throttle:5,1 membatasi guest berdasarkan IP/domain, tetapi createToken langsung tetap membuat token baru tanpa cooldown akun.

**Hasil pemeriksaan:** Registrasi rute memverifikasi throttle. Controller tidak berubah pada bagian broker createToken; implementasi broker lokal tidak menjalankan recentlyCreatedToken di jalur itu.

**Pemicu/batas dampak:** Permintaan dari banyak IP ke satu email masih dapat mengganti token dan memicu email berulang. Batas per IP yang baru tetap memberi perlindungan nyata terhadap satu sumber.

**Arah perbaikan dan verifikasi:** Gabungkan batas per akun/email dan IP, serta cooldown token/pengiriman. Pertahankan respons generik untuk menjaga privasi email.

## Catatan operasional tambahan

- Build kini memasang devDependencies ketika diperlukan. Namun, `npm_build_assets()` melewati build hanya karena manifest sudah ada. Proses rilis harus memastikan manifest/aset berasal dari revisi sumber yang sama; audit tidak menemukan bukti bahwa aset produksi saat ini usang.
- Sanitasi konten sudah dilakukan ketika dirender. Pengujian variasi HTML yang lebih luas dan konten lama tetap diperlukan sebelum menganggap seluruh XSS tertutup.
- Bukti privat lama perlu ditangani melalui migrasi berkas yang tervalidasi. Perubahan lokasi upload baru saja tidak membuktikan seluruh bukti historis sudah terlindungi.
- Pencabutan sesi database sudah ditambahkan. Jika produksi memakai driver sesi lain, pastikan kebijakan pencabutan setelah reset password tetap berjalan; middleware status aktif sudah membantu penonaktifan akun.
- Perubahan saldo dan pencatatan AuditLog sebaiknya disatukan dalam transaksi. Audit baru sudah menyimpan alasan, tetapi kegagalan penulisan audit masih dapat terjadi setelah saldo tersimpan.
- API/halaman jadwal dan timer browser belum diuji lintas pergantian tanggal. Perbaikan zona waktu yang diverifikasi di sini berfokus pada scheduler push.
- Koreksi audit awal: pemakaian quickDonate oleh UI ditemukan pada `resources/views/public/donasi/index.blade.php`; endpoint itu bukan sekadar jalur yang mungkin tidak digunakan.

## Kriteria penutupan

1. Reproduksi R01/R04 harus gagal menimbulkan saldo berbeda pada staging dengan database yang sama jenisnya dengan produksi.
2. Pengguna yang tidak berhak atau petugas yang belum lolos 2FA tidak boleh membaca bukti milik orang lain.
3. Pergantian akun/logout dan mode offline tidak boleh menampilkan data akun sebelumnya, termasuk notifikasi pada beranda dan program khatam.
4. Admin dari sesi remember-me harus dapat melanjutkan challenge secara aman tanpa berputar kembali ke home.
5. Modul push mati/akun nonaktif harus menghasilkan nol pengiriman; pengulangan command tidak menggandakan pengiriman yang sama.
6. Tambahkan tes regresi untuk jalur kritis di atas, lalu verifikasi callback sandbox, browser/PWA, bukti lama, dan konfigurasi deployment secara terpisah.

**Kesimpulan akhir audit:** pertahankan perbaikan yang sudah benar, tetapi jangan menutup seluruh audit atau menganggap sistem sudah aman hanya dari lolos sintaks. Fokus berikutnya adalah konsistensi pembayaran saat proses berbarengan dan penutupan seluruh jalur akses data pribadi. Tidak ada perbaikan sistem yang diterapkan dalam audit ulang ini.

