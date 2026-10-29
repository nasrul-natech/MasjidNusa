# Audit Sistem Masjid Nusa — 29 Oktober 2026

Audit terhadap working tree lokal di `/Users/nasrul/Herd/update/MasjidNusa`, termasuk perubahan lokal yang belum di-commit. Tidak ada kode aplikasi, konfigurasi, database, atau aset yang diubah oleh audit ini. Dokumen ini merupakan keluaran audit.

## Kesimpulan

Ditemukan **24 temuan: 10 prioritas tinggi dan 14 sedang**. Prioritas terbesar adalah integritas donasi, pencabutan akses akun, perlindungan data privat, dan keselamatan deployment. Lolos pemeriksaan sintaks tidak berarti alur bisnis aman.

Angka ini menghitung kelompok masalah yang berbeda, bukan jumlah endpoint terdampak, dan bukan jaminan bahwa seluruh kemungkinan cacat telah ditemukan. Tidak ada temuan yang dinyatakan sebagai eksploitasi produksi terkonfirmasi.

## Cakupan dan metode

- Menelusuri routes, middleware, autentikasi password/Google/2FA, pengelolaan akun dan role, donasi manual/gateway, upload bukti, ekspor, konten publik, PWA, jadwal/push adzan, serta skrip deployment.
- Meninjau model/migrasi terkait dan tes yang tersedia. Quran/bookmark/khatam ditinjau pada controller/service, tetapi ketepatan seluruh isi Quran, terjemahan, audio, dan rumus jadwal bukan objek sertifikasi audit ini.
- Mempertahankan 57 perubahan file tracked yang sudah ada beserta file untracked awal. Temuan berasal dari source saat audit, bukan asumsi dari proyek lain atau versi historis.
- Bukti utama berupa pembacaan kode. Dua pemeriksaan terisolasi memakai dependency/kode aktual tanpa boot aplikasi, database, atau jaringan: URL redirect dan handler service worker.
- Tidak menjalankan artisan test, migration, seeder, build, deployment, transaksi pembayaran, pengiriman email/push, atau akses produksi. Tidak membaca isi .env maupun data donatur.
- Nomor baris merujuk source pada saat audit dan dapat bergeser setelah perubahan berikutnya.

## Pemeriksaan yang selesai

- `php -n -l`: **174 file PHP lulus**, mencakup app, bootstrap, config, database, routes dan tests. Blade tidak dikompilasi.
- `bash -n`: **4 skrip deployment lulus**. Ini hanya sintaks, bukan bukti keselamatan operasi skrip.
- Manifest build lokal: **3 entri**, seluruh file output/asset yang dirujuk tersedia. Tidak membuktikan aset telah terpasang di server.
- UrlGenerator Laravel mempertahankan `//example.invalid/path` sebagai tujuan redirect; validasi awalan di login menerimanya.
- Simulasi handler `public/sw.js` dengan cache/network stub: profil privat berhasil masuk cache dan dibaca kembali saat offline. Tidak ada request jaringan nyata; pergantian sesi di browser nyata belum direproduksi.
- Tes proyek yang tersedia berfokus pada branding/manifest dan contoh halaman. Tidak ditemukan tes khusus untuk idempotensi pembayaran, pencabutan sesi, konflik role/2FA, atau scheduler adzan dalam direktori tests yang diperiksa.

## Daftar temuan

### F01 — Tinggi — Callback Duitku terhalang proteksi CSRF

**Area:** Pembayaran. **Status:** Terkonfirmasi dari kode; dampak runtime/produksi belum direproduksi.

**Bukti:** [routes/web.php:255](/Users/nasrul/Herd/update/MasjidNusa/routes/web.php:255); [bootstrap/app.php:13](/Users/nasrul/Herd/update/MasjidNusa/bootstrap/app.php:13); [vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/PreventRequestForgery.php:95](/Users/nasrul/Herd/update/MasjidNusa/vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/PreventRequestForgery.php:95).

Callback POST didaftarkan dalam rute web. Tidak ada pengecualian callback pada konfigurasi middleware. Middleware framework yang terpasang menolak POST tanpa token atau origin yang diterima.

**Dampak dan pemicu:** Callback server-ke-server biasa dari gateway dapat ditolak dengan 419 sebelum controller dipanggil; donasi yang sudah dibayar tetap pending. Belum dicoba ke gateway nyata.

**Arah perbaikan:** Kecualikan hanya endpoint callback dari CSRF, pertahankan verifikasi signature, dan validasi order, nominal, merchant serta referensi transaksi.

**Uji penerimaan yang belum dijalankan:** Di staging, kirim callback sandbox valid tanpa sesi browser: harus diproses. Signature invalid harus ditolak. Uji HTTP nyata karena middleware CSRF dapat dilewati saat unit test.

### F02 — Tinggi — Pembayaran yang sama dapat menambah total donasi berkali-kali

**Area:** Pembayaran. **Status:** Terkonfirmasi dari kode; dampak runtime/produksi belum direproduksi.

**Bukti:** [app/Http/Controllers/Admin/Keuangan/PaymentVerificationController.php:26](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/Admin/Keuangan/PaymentVerificationController.php:26); [app/Http/Controllers/Webhook/DuitkuWebhookController.php:43](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/Webhook/DuitkuWebhookController.php:43).

Approve manual dan callback sukses selalu increment collected_amount, tanpa guard status sebelumnya, penguncian, atau transaksi database yang menyatukan perubahan status dan saldo.

**Dampak dan pemicu:** Klik/verifikasi ulang, dua admin bersamaan, atau retry callback menggandakan total kampanye dan notifikasi. Kegagalan di tengah proses dapat meninggalkan data parsial. Jalur callback baru dapat terjangkau setelah F01 ditangani.

**Arah perbaikan:** Buat transisi pembayaran idempoten dalam transaksi database dengan lock/conditional update; increment hanya sekali, dan jalankan notifikasi sesudah commit.

**Uji penerimaan yang belum dijalankan:** Approve dua kali, callback dua kali, dua request paralel, serta simulasi kegagalan di tengah proses: saldo harus tetap bertambah satu kali.

### F03 — Tinggi — Donasi selesai dapat dibuka ulang atau diturunkan statusnya

**Area:** Pembayaran. **Status:** Terkonfirmasi dari kode; dampak runtime/produksi belum direproduksi.

**Bukti:** [app/Http/Controllers/User/DonationUserController.php:150](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/User/DonationUserController.php:150); [app/Http/Controllers/Admin/Keuangan/PaymentVerificationController.php:53](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/Admin/Keuangan/PaymentVerificationController.php:53); [app/Http/Controllers/Webhook/DuitkuWebhookController.php:61](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/Webhook/DuitkuWebhookController.php:61).

uploadProof selalu mengubah status donasi menjadi waiting_verification tanpa memeriksa status terminal/metode pembayaran. Reject manual dan callback gagal juga dapat menimpa status pembayaran sukses; saldo tidak dibalik atau direkonsiliasi.

**Dampak dan pemicu:** Donatur dapat mengunggah ulang bukti setelah verified; approve berikutnya menambah saldo lagi. Callback terlambat atau reject atas donasi selesai membuat status dan total kampanye berbeda.

**Arah perbaikan:** Tetapkan aturan transisi status; bekukan bukti/status pembayaran final. Koreksi pembayaran harus menjadi operasi terpisah dengan pencatatan penyesuaian dan audit.

**Uji penerimaan yang belum dijalankan:** Uji upload setelah paid/verified, reject setelah verified, callback gagal sesudah sukses, dan bukti baru pada transaksi gateway.

### F04 — Tinggi — Menonaktifkan akun tidak menghentikan sesi yang sudah login

**Area:** Akun. **Status:** Terkonfirmasi dari kode; dampak runtime/produksi belum direproduksi.

**Bukti:** [app/Http/Controllers/SuperAdmin/UserController.php:111](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/SuperAdmin/UserController.php:111); [app/Http/Middleware/CheckRole.php:11](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Middleware/CheckRole.php:11); [app/Http/Middleware/EnsureTwoFactorVerified.php:11](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Middleware/EnsureTwoFactorVerified.php:11); [app/Http/Controllers/Auth/TwoFactorController.php:130](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/Auth/TwoFactorController.php:130).

is_active diperiksa saat login password/Google, tetapi tidak pada setiap request terautentikasi. Toggle/bulk deactivate hanya mengubah kolom, tanpa pencabutan sesi. completeLogin 2FA tidak mengecek ulang status aktif.

**Dampak dan pemicu:** Jamaah atau admin yang sudah login dapat tetap mengakses fitur setelah dinonaktifkan. Akun yang dinonaktifkan saat challenge 2FA berlangsung masih dapat menyelesaikan login.

**Arah perbaikan:** Periksa status aktif pada seluruh rute autentikasi, cabut sesi dan remember token saat deaktivasi, serta periksa ulang pada penyelesaian 2FA.

**Uji penerimaan yang belum dijalankan:** Login di dua browser, nonaktifkan akun, lalu ulangi akses user/admin; keduanya harus ditolak. Uji deaktivasi di tengah challenge.

### F05 — Tinggi — Password dapat diganti tanpa password lama

**Area:** Akun. **Status:** Terkonfirmasi dari kode; dampak runtime/produksi belum direproduksi.

**Bukti:** [app/Http/Controllers/User/ProfileController.php:22](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/User/ProfileController.php:22); [routes/web.php:126](/Users/nasrul/Herd/update/MasjidNusa/routes/web.php:126).

current_password bersifat nullable dan hanya diverifikasi jika diisi. Password baru tetap disimpan jika current_password tidak dikirim. Rute profil hanya memakai auth.

**Dampak dan pemicu:** Pemegang sesi login dapat menetapkan password baru tanpa mengetahui password lama; termasuk sesi admin yang memakai halaman profil umum.

**Arah perbaikan:** Wajibkan autentikasi ulang saat perubahan password, termasuk verifikasi password lama untuk akun password dan alur khusus akun Google; pertimbangkan pencabutan sesi lain.

**Uji penerimaan yang belum dijalankan:** Kirim perubahan password tanpa current_password dan dengan password salah: keduanya harus gagal, tanpa mengubah hash.

### F06 — Tinggi — Reset password admin memakai satu password tetap

**Area:** Akun. **Status:** Terkonfirmasi dari kode; dampak runtime/produksi belum direproduksi.

**Bukti:** [app/Http/Controllers/SuperAdmin/UserController.php:135](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/SuperAdmin/UserController.php:135).

resetPassword menetapkan Password123! untuk setiap akun, tanpa token reset unik atau kewajiban mengganti password saat login.

**Dampak dan pemicu:** Akun yang baru direset memiliki password yang dapat ditebak. 2FA tetap menjadi lapisan tambahan pada admin, tetapi akun jamaah tidak memilikinya.

**Arah perbaikan:** Gunakan tautan reset sekali pakai dengan masa berlaku; jangan gunakan password bersama. Cabut sesi yang relevan.

**Uji penerimaan yang belum dijalankan:** Reset dua akun: kredensial tidak boleh sama/tertebak dan token tidak dapat digunakan ulang.

### F07 — Tinggi — Service worker menyimpan halaman privat lintas sesi

**Area:** Privasi / PWA. **Status:** Bukti kode dan pemeriksaan terisolasi; belum end-to-end.

**Bukti:** [public/sw.js:46](/Users/nasrul/Herd/update/MasjidNusa/public/sw.js:46); [public/sw.js:72](/Users/nasrul/Herd/update/MasjidNusa/public/sw.js:72); [app/Http/Controllers/Auth/LoginController.php:107](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/Auth/LoginController.php:107).

Service worker menyimpan respons GET halaman berdasarkan URL, termasuk /user, /admin dan /super-admin, tanpa pemisahan akun. Logout tidak menghapus cache privat. Simulasi terisolasi atas sw.js menunjukkan /user/profile disimpan dan dikembalikan saat offline.

**Dampak dan pemicu:** Pada perangkat/browser bersama, halaman privat yang pernah dimuat dapat tampil kembali setelah logout atau pergantian akun ketika jaringan gagal. Halaman setup 2FA juga termasuk pola yang dicache. Dampak browser nyata masih perlu diuji.

**Arah perbaikan:** Gunakan allowlist cache untuk halaman publik; jangan cache halaman autentikasi atau data privat. Bersihkan cache lama yang sudah terlanjur menyimpan respons sensitif.

**Uji penerimaan yang belum dijalankan:** Login A, buka profil/laporan, logout, lalu offline dan buka URL yang sama sebagai tamu/B: data A tidak boleh tampil. Uji pula halaman setup 2FA.

### F08 — Tinggi — HTML konten disimpan dan dirender tanpa sanitasi server

**Area:** Konten. **Status:** Terkonfirmasi dari kode; dampak runtime/produksi belum direproduksi.

**Bukti:** [app/Http/Controllers/Admin/MediaIT/ArticleAdminController.php:33](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/Admin/MediaIT/ArticleAdminController.php:33); [resources/views/public/dakwah/show.blade.php:34](/Users/nasrul/Herd/update/MasjidNusa/resources/views/public/dakwah/show.blade.php:34); [app/Http/Controllers/Admin/Keuangan/CampaignAdminController.php:57](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/Admin/Keuangan/CampaignAdminController.php:57); [resources/views/public/donasi/show.blade.php:52](/Users/nasrul/Herd/update/MasjidNusa/resources/views/public/donasi/show.blade.php:52); [app/Http/Controllers/Admin/Operasional/DoaAdminController.php:91](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/Admin/Operasional/DoaAdminController.php:91).

Konten artikel/kampanye/doa diterima sebagai string; tampilan publik menggunakan output HTML mentah. Tidak ditemukan sanitizer atau mutator pembersihan HTML pada jalur ini.

**Dampak dan pemicu:** Stored XSS dapat berjalan pada pengunjung yang membuka konten. Prasyaratnya akses editor/admin terkait atau akun editor yang disalahgunakan; ini bukan input anonim.

**Arah perbaikan:** Sanitasi HTML di server dengan allowlist tag/atribut/protokol yang diperlukan editor; tinjau dan bersihkan konten lama. Tambahkan CSP sebagai pertahanan tambahan.

**Uji penerimaan yang belum dijalankan:** Di staging gunakan penanda JavaScript tidak berbahaya pada konten; tidak boleh dieksekusi, sementara format teks yang diizinkan tetap tampil.

### F09 — Tinggi — Permission yang dicabut tidak membatasi endpoint admin

**Area:** Hak akses. **Status:** Terkonfirmasi dari kode; dampak runtime/produksi belum direproduksi.

**Bukti:** [app/Http/Controllers/SuperAdmin/RoleController.php:20](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/SuperAdmin/RoleController.php:20); [routes/web.php:145](/Users/nasrul/Herd/update/MasjidNusa/routes/web.php:145); [routes/web.php:176](/Users/nasrul/Herd/update/MasjidNusa/routes/web.php:176); [routes/web.php:200](/Users/nasrul/Herd/update/MasjidNusa/routes/web.php:200); [app/Http/Middleware/CheckRole.php:24](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Middleware/CheckRole.php:24).

UI mengubah permission melalui syncPermissions, tetapi kelompok rute admin hanya memeriksa role dan 2FA. Tidak ditemukan pemakaian permission middleware, policy, Gate atau authorize untuk aksi-aksi tersebut.

**Dampak dan pemicu:** Mencabut permission di halaman Roles memberi kesan akses sudah dicabut, tetapi pemilik role masih dapat memanggil operasi terkait melalui URL/request langsung.

**Arah perbaikan:** Terapkan permission/policy per aksi secara konsisten pada backend dan selaraskan navigasi dengan otorisasi tersebut.

**Uji penerimaan yang belum dijalankan:** Cabut izin pengelolaan/verifikasi donasi dari role keuangan, lalu akses endpoint langsung: harus 403 tanpa perubahan data.

### F10 — Tinggi — Skrip update dapat menghapus isi public_html

**Area:** Deployment. **Status:** Terkonfirmasi dari kode; dampak runtime/produksi belum direproduksi.

**Bukti:** [deploy/hostinger-update.sh:20](/Users/nasrul/Herd/update/MasjidNusa/deploy/hostinger-update.sh:20).

Jika public_html bukan symlink atau targetnya berbeda dari app/public, skrip menjalankan rm -rf public_html sebelum membuat symlink; tidak ada backup pada cabang tersebut.

**Dampak dan pemicu:** Jika public_html merupakan direktori nyata berisi file situs, menjalankan update menghapusnya. Ini risiko bersyarat pada tata letak server; skrip tidak dijalankan dan server tidak diperiksa.

**Arah perbaikan:** Hentikan update ketika struktur tidak sesuai, atau pindahkan direktori lama ke backup yang diverifikasi sebelum penggantian symlink.

**Uji penerimaan yang belum dijalankan:** Uji di direktori staging berisi file dummy dengan beberapa bentuk symlink/direktori. File yang ada harus dipertahankan.

### F11 — Sedang — Endpoint donasi cepat melewati aturan metode pembayaran

**Area:** Pembayaran. **Status:** Terkonfirmasi dari kode; dampak runtime/produksi belum direproduksi.

**Bukti:** [routes/web.php:138](/Users/nasrul/Herd/update/MasjidNusa/routes/web.php:138); [app/Http/Controllers/User/DonationUserController.php:24](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/User/DonationUserController.php:24).

quickDonate menerima manual/duitku tanpa memeriksa PaymentConfiguration; kedua pilihan hanya membuat transaksi lokal dan waiting_verification. Ia tidak memanggil createTransaction Duitku. Kategori yang tidak ditemukan jatuh ke kampanye aktif pertama.

**Dampak dan pemicu:** Request langsung tetap bisa membuat donasi saat metode dinonaktifkan; pilihan Duitku tidak menghasilkan payment_url dan tujuan kampanye bisa tidak sesuai maksud donatur. Pemakaian endpoint ini oleh UI aktif belum ditemukan.

**Arah perbaikan:** Satukan alur quickDonate dan store, tegakkan konfigurasi metode, serta pilih kampanye secara eksplisit. Tutup endpoint bila memang tidak dipakai.

**Uji penerimaan yang belum dijalankan:** Uji metode nonaktif, pilihan Duitku, kategori tidak ada, dan kategori tanpa kampanye aktif.

### F12 — Sedang — Bukti transfer disimpan pada storage publik

**Area:** Privasi. **Status:** Terkonfirmasi dari kode; dampak runtime/produksi belum direproduksi.

**Bukti:** [app/Http/Controllers/User/DonationUserController.php:167](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/User/DonationUserController.php:167); [config/filesystems.php:41](/Users/nasrul/Herd/update/MasjidNusa/config/filesystems.php:41); [app/Models/PaymentProof.php:28](/Users/nasrul/Herd/update/MasjidNusa/app/Models/PaymentProof.php:28).

Bukti transfer disimpan pada disk public dan memiliki URL file langsung; pemeriksaan kepemilikan hanya ada pada upload, bukan pada unduhan file melalui web server.

**Dampak dan pemicu:** Siapa pun yang memperoleh URL bukti dapat membukanya tanpa login jika storage symlink aktif. URL acak tidak membuat akses menjadi terotorisasi; ini tidak berarti semua file mudah ditebak.

**Arah perbaikan:** Pindahkan bukti ke storage privat dan sajikan lewat controller berotorisasi untuk pemilik serta petugas keuangan.

**Uji penerimaan yang belum dijalankan:** Akses URL bukti sebagai tamu dan akun lain harus ditolak; pemilik dan petugas sah tetap dapat melihatnya.

### F13 — Sedang — Challenge 2FA tidak dapat dibuka oleh sesi yang sudah terautentikasi

**Area:** 2FA. **Status:** Terkonfirmasi dari kode; dampak runtime/produksi belum direproduksi.

**Bukti:** [app/Http/Middleware/EnsureTwoFactorVerified.php:19](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Middleware/EnsureTwoFactorVerified.php:19); [routes/web.php:102](/Users/nasrul/Herd/update/MasjidNusa/routes/web.php:102); [routes/web.php:116](/Users/nasrul/Herd/update/MasjidNusa/routes/web.php:116).

Middleware admin mengirim user terautentikasi tanpa penanda two_factor_verified ke challenge/setup, tetapi kedua rute itu berada di kelompok guest. Middleware guest mengarahkan user login kembali ke home.

**Dampak dan pemicu:** Admin yang dipulihkan lewat remember-me setelah sesi berakhir, atau user yang dipromosikan ketika masih login, dapat gagal masuk panel sampai logout/login ulang. Tidak ditemukan bypass 2FA pada jalur ini.

**Arah perbaikan:** Sediakan alur challenge/setup yang mendukung sesi terautentikasi dengan status pending yang aman; tetap cegah akses panel sebelum verifikasi.

**Uji penerimaan yang belum dijalankan:** Uji pemulihan remember-me dengan sesi baru dan promosi jamaah menjadi admin saat sesi masih aktif.

### F14 — Sedang — Akses langsung setup 2FA dapat menghasilkan TypeError

**Area:** 2FA. **Status:** Terkonfirmasi dari kode; dampak runtime/produksi belum direproduksi.

**Bukti:** [app/Http/Controllers/Auth/TwoFactorController.php:68](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/Auth/TwoFactorController.php:68); [app/Http/Controllers/Auth/TwoFactorController.php:119](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/Auth/TwoFactorController.php:119).

resolvePendingUser dideklarasikan mengembalikan User, tetapi ketika login.id tidak ada ia mengembalikan RedirectResponse. PHP tidak menerima jenis pengembalian tersebut.

**Dampak dan pemicu:** Membuka /two-factor/setup tanpa sesi pending, atau setelah sesi kedaluwarsa, berpotensi berakhir sebagai HTTP 500 alih-alih kembali ke login.

**Arah perbaikan:** Tangani redirect di controller sebelum pemanggilan helper, atau gunakan struktur kontrol/return type yang konsisten.

**Uji penerimaan yang belum dijalankan:** Buka setup sebagai tamu baru dan ulangi setelah sesi pending hilang: harus redirect aman, bukan 500.

### F15 — Sedang — Pengingat sebelum waktu sholat tidak pernah terkirim

**Area:** Adzan. **Status:** Terkonfirmasi dari kode; dampak runtime/produksi belum direproduksi.

**Bukti:** [app/Console/Commands/SendAdzanNotifications.php:39](/Users/nasrul/Herd/update/MasjidNusa/app/Console/Commands/SendAdzanNotifications.php:39); [app/Console/Commands/SendAdzanNotifications.php:52](/Users/nasrul/Herd/update/MasjidNusa/app/Console/Commands/SendAdzanNotifications.php:52).

Pemeriksaan pertama hanya melanjutkan ketika currentTime sama dengan waktu sholat. Setelah itu kode mengurangi minutes_before dan menuntut hasilnya tetap sama dengan currentTime.

**Dampak dan pemicu:** Untuk minutes_before 1–30, dua kondisi tidak dapat terpenuhi bersamaan: sebelum waktu sholat dilewati, tepat waktu sholat juga dilewati. Temuan ini mengenai push dari scheduler; timer browser adalah jalur berbeda.

**Arah perbaikan:** Hitung waktu notifikasi per pengaturan sebelum memfilter kandidat; gunakan pencatatan pengiriman agar pemrosesan ulang tidak menggandakan notifikasi.

**Uji penerimaan yang belum dijalankan:** Dengan jam terkontrol, uji offset 0, 5, 10 dan 30 menit, termasuk lintas tanggal.

### F16 — Sedang — Scheduler tidak membedakan zona waktu lokasi

**Area:** Adzan. **Status:** Terkonfirmasi dari kode; dampak runtime/produksi belum direproduksi.

**Bukti:** [app/Models/PrayerLocation.php:9](/Users/nasrul/Herd/update/MasjidNusa/app/Models/PrayerLocation.php:9); [app/Services/PrayerScheduleService.php:114](/Users/nasrul/Herd/update/MasjidNusa/app/Services/PrayerScheduleService.php:114); [app/Console/Commands/SendAdzanNotifications.php:22](/Users/nasrul/Herd/update/MasjidNusa/app/Console/Commands/SendAdzanNotifications.php:22); [config/app.php:68](/Users/nasrul/Herd/update/MasjidNusa/config/app.php:68).

Waktu jadwal disimpan sebagai jam lokal tanpa metadata zona waktu; scheduler membandingkan semua lokasi dengan satu now() zona aplikasi. Default aplikasi Asia/Jakarta.

**Dampak dan pemicu:** Jika lokasi dari WIB, WITA dan WIT digunakan bersama, pengiriman push dapat meleset satu atau dua jam. Tidak ada klaim bahwa lokasi non-WIB sedang aktif di produksi.

**Arah perbaikan:** Simpan zona IANA per lokasi dan konversikan jadwal menjadi waktu absolut saat membandingkan waktu pengiriman.

**Uji penerimaan yang belum dijalankan:** Bandingkan lokasi Jakarta, Makassar dan Jayapura pada satu instant UTC yang sama, termasuk pergantian tanggal.

### F17 — Sedang — Permintaan reset password tidak dibatasi frekuensinya

**Area:** Akun / Keandalan. **Status:** Terkonfirmasi dari kode; dampak runtime/produksi belum direproduksi.

**Bukti:** [routes/web.php:108](/Users/nasrul/Herd/update/MasjidNusa/routes/web.php:108); [app/Http/Controllers/Auth/PasswordResetController.php:22](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/Auth/PasswordResetController.php:22); [vendor/laravel/framework/src/Illuminate/Auth/Passwords/PasswordBroker.php:195](/Users/nasrul/Herd/update/MasjidNusa/vendor/laravel/framework/src/Illuminate/Auth/Passwords/PasswordBroker.php:195).

Rute forgot-password tidak memakai throttle dan controller memanggil createToken langsung. Implementasi broker yang terpasang meneruskan createToken langsung ke token repository, tanpa pemeriksaan recentlyCreatedToken pada jalur ini.

**Dampak dan pemicu:** Permintaan berulang dapat membanjiri email dan mengganti token reset yang baru dikirim, sehingga pemulihan akun terganggu. Besarnya dampak bergantung konfigurasi SMTP dan proteksi di luar aplikasi.

**Arah perbaikan:** Tambahkan rate limit per IP serta akun dan gunakan jalur broker yang menegakkan jeda pengiriman, dengan respons generik yang tetap menjaga privasi email.

**Uji penerimaan yang belum dijalankan:** Kirim permintaan beruntun di staging: jumlah email/token baru harus dibatasi dan tautan sah tidak terus-menerus dibatalkan.

### F18 — Sedang — Redirect login menerima URL eksternal berawalan //

**Area:** Akun. **Status:** Bukti kode dan pemeriksaan terisolasi; belum end-to-end.

**Bukti:** [app/Http/Controllers/Auth/LoginController.php:44](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/Auth/LoginController.php:44); [app/Http/Controllers/Auth/GoogleAuthController.php:100](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/Auth/GoogleAuthController.php:100).

Validasi hanya memeriksa awalan /. Nilai //example.invalid/path lolos; pemeriksaan terisolasi pada UrlGenerator yang terpasang mempertahankan target tersebut.

**Dampak dan pemicu:** Tautan login yang dimanipulasi dapat membawa pengguna ke domain lain setelah login. Ini risiko pengalihan/phishing, bukan bukti kebocoran token.

**Arah perbaikan:** Terima path relatif internal yang tervalidasi atau nama rute dari allowlist; tolak protocol-relative URL dan variasi normalisasinya.

**Uji penerimaan yang belum dijalankan:** Uji path internal, URL absolut, //host dan variasi slash/backslash. Hanya tujuan internal yang boleh diterima.

### F19 — Sedang — Tombol nonaktifkan modul tidak menghentikan endpoint

**Area:** Modul. **Status:** Terkonfirmasi dari kode; dampak runtime/produksi belum direproduksi.

**Bukti:** [app/Http/Controllers/SuperAdmin/ModuleController.php:26](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/SuperAdmin/ModuleController.php:26); [app/Http/Middleware/EnsureModuleActive.php:12](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Middleware/EnsureModuleActive.php:12); [bootstrap/app.php:17](/Users/nasrul/Herd/update/MasjidNusa/bootstrap/app.php:17); [routes/web.php:64](/Users/nasrul/Herd/update/MasjidNusa/routes/web.php:64).

EnsureModuleActive terdaftar sebagai alias, tetapi tidak diterapkan pada rute modul; pencarian juga tidak menemukan pemeriksaan SystemModule::isActive di controller fitur.

**Dampak dan pemicu:** Modul yang ditandai nonaktif tetap dapat diakses melalui endpoint langsung. Pengaturan dapat memberi kesan fitur berhenti padahal backend tetap berjalan.

**Arah perbaikan:** Pasang pemeriksaan modul pada seluruh rute/operasi terkait, termasuk jalur publik dan background bila diperlukan.

**Uji penerimaan yang belum dijalankan:** Nonaktifkan modul non-core, lalu uji halaman dan request mutasi langsung: operasi fitur harus ditolak secara konsisten.

### F20 — Sedang — Skrip memasang dependency tanpa Vite lalu menjalankan build

**Area:** Deployment. **Status:** Terkonfirmasi dari kode; dampak runtime/produksi belum direproduksi.

**Bukti:** [deploy/hostinger-setup.sh:104](/Users/nasrul/Herd/update/MasjidNusa/deploy/hostinger-setup.sh:104); [deploy/hostinger-update.sh:48](/Users/nasrul/Herd/update/MasjidNusa/deploy/hostinger-update.sh:48); [package.json:5](/Users/nasrul/Herd/update/MasjidNusa/package.json:5).

Skrip menjalankan npm ci --omit=dev atau npm install --omit=dev, kemudian npm run build. Vite dan plugin build seluruhnya berada di devDependencies.

**Dampak dan pemicu:** Pada instalasi server bersih dengan npm tersedia, build dapat gagal karena vite tidak terpasang; set -e menghentikan deployment setelah sebagian langkah sudah berjalan.

**Arah perbaikan:** Build di CI/lokal dan kirim aset, atau pasang dependency build sementara sebelum membangun lalu lakukan pruning setelahnya.

**Uji penerimaan yang belum dijalankan:** Uji deployment pada direktori bersih tanpa node_modules maupun Vite global; seluruh entri manifest harus tersedia setelah build.

### F21 — Sedang — Penyesuaian saldo tidak menyimpan alasan dan jejak perubahan

**Area:** Audit keuangan. **Status:** Terkonfirmasi dari kode; dampak runtime/produksi belum direproduksi.

**Bukti:** [app/Http/Controllers/Admin/Keuangan/CampaignAdminController.php:180](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/Admin/Keuangan/CampaignAdminController.php:180); [app/Http/Controllers/Admin/Keuangan/CampaignAdminController.php:192](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/Admin/Keuangan/CampaignAdminController.php:192).

adjustAmount mewajibkan reason tetapi hanya menyimpan collected_amount. Nilai lama, alasan, dan pelaku tidak dicatat. syncAmount juga mengubah saldo tanpa AuditLog.

**Dampak dan pemicu:** Selisih angka kampanye sulit ditelusuri dan perubahan manual tidak dapat dijelaskan dari audit trail. Ini masalah akuntabilitas, bukan bukti bahwa perubahan tidak sah pernah terjadi.

**Arah perbaikan:** Catat nilai sebelum/sesudah, alasan, pelaku dan sumber perubahan secara atomik; gunakan ledger penyesuaian terpisah jika donasi offline didukung.

**Uji penerimaan yang belum dijalankan:** Lakukan koreksi di staging dan pastikan alasan serta angka sebelum/sesudah dapat ditemukan kembali, termasuk sinkronisasi.

### F22 — Sedang — Perlindungan super-admin terakhir dapat dilewati lewat edit akun

**Area:** Administrasi. **Status:** Terkonfirmasi dari kode; dampak runtime/produksi belum direproduksi.

**Bukti:** [app/Http/Controllers/SuperAdmin/UserController.php:79](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/SuperAdmin/UserController.php:79); [app/Http/Controllers/SuperAdmin/UserController.php:111](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/SuperAdmin/UserController.php:111).

toggleActive memeriksa akun sendiri dan jumlah super-admin aktif, tetapi update menerima is_active dan melakukan syncRoles tanpa perlindungan setara.

**Dampak dan pemicu:** Super-admin terakhir dapat menonaktifkan atau menurunkan role dirinya melalui form edit, sehingga tidak ada admin aktif yang bisa mengelola sistem setelah sesi berakhir/dibatasi.

**Arah perbaikan:** Sentralisasikan aturan minimal satu super-admin aktif pada seluruh jalur edit, toggle, delete dan bulk; jalankan pemeriksaan bersama perubahan dalam transaksi.

**Uji penerimaan yang belum dijalankan:** Coba edit super-admin aktif terakhir menjadi jamaah/nonaktif serta uji perubahan paralel: perubahan yang menghilangkan admin terakhir harus ditolak.

### F23 — Sedang — Identitas Google dicocokkan lewat email tanpa menegakkan google_id

**Area:** Google login. **Status:** Terkonfirmasi dari kode; dampak runtime/produksi belum direproduksi.

**Bukti:** [app/Http/Controllers/Auth/GoogleAuthController.php:42](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/Auth/GoogleAuthController.php:42); [app/Http/Controllers/Auth/GoogleAuthController.php:61](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/Auth/GoogleAuthController.php:61).

Callback mencari akun berdasarkan email. Bila google_id sudah terisi, nilainya tidak dibandingkan dengan ID Google yang masuk; bila kosong, akun ditautkan otomatis. Tidak ada alur persetujuan penautan ulang.

**Dampak dan pemicu:** Dalam kondisi identitas/email berubah, alamat dialihkan, atau data penautan tidak konsisten, akun lokal dapat masuk lewat identitas Google yang berbeda dari ikatan tersimpan. Tidak membuktikan pengambilalihan akun Gmail normal.

**Arah perbaikan:** Cari berdasarkan provider subject/google_id, tolak mismatch, dan gunakan penautan eksplisit dengan autentikasi ulang untuk akun lokal yang sudah ada.

**Uji penerimaan yang belum dijalankan:** Mock callback dengan email sama tetapi google_id berbeda dan subject sama dengan email berubah: hasil harus mengikuti kebijakan penautan yang eksplisit.

### F24 — Sedang — Nilai pengguna diekspor ke CSV tanpa netralisasi formula

**Area:** Ekspor. **Status:** Terkonfirmasi dari kode; dampak runtime/produksi belum direproduksi.

**Bukti:** [app/Http/Controllers/Admin/Keuangan/ReportController.php:64](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/Admin/Keuangan/ReportController.php:64); [app/Http/Controllers/User/DonationUserController.php:28](/Users/nasrul/Herd/update/MasjidNusa/app/Http/Controllers/User/DonationUserController.php:28).

Nama donatur dan kolom teks lainnya diteruskan langsung ke fputcsv. Validasi nama mengizinkan string yang diawali karakter formula.

**Dampak dan pemicu:** Saat laporan dibuka pada spreadsheet yang menafsirkan formula, data donatur dapat menjadi formula. Perilaku persis bergantung aplikasi spreadsheet dan pengaturannya; tidak diuji pada Excel.

**Arah perbaikan:** Netralisasi karakter awal formula pada kolom teks atau gunakan format ekspor dengan tipe sel teks eksplisit; jangan mengubah nominal numerik sah.

**Uji penerimaan yang belum dijalankan:** Ekspor nama dengan formula aritmetika tidak berbahaya dan pastikan tampil literal di spreadsheet sasaran.

## Urutan tindak lanjut yang disarankan

1. Tangani F01–F03 bersama sebelum mengandalkan pencatatan pembayaran otomatis: membuka akses callback saja akan membuat masalah idempotensi pada callback menjadi terjangkau. Cocokkan transaksi gateway, status donasi, dan total kampanye sebelum mengoreksi data nyata.
2. Tutup risiko akun dan privasi F04–F09; lanjutkan F12–F14, F17–F18 dan F23. Perbaikan harus mencakup sesi/cache yang sudah terlanjur ada.
3. Tinjau F10 dan F20 sebelum menjalankan skrip deployment berikutnya.
4. Benahi F11, F15–F16, F19 dan F21–F24, kemudian tambahkan pengujian regresi yang relevan di database staging terpisah.

Semua ini berupa usulan. Audit ini tidak mengimplementasikan perbaikannya.

## Kontrol yang sudah terlihat

- Pembatasan role dan kewajiban 2FA sudah ada pada kelompok rute admin.
- Login password memiliki rate limit; challenge 2FA juga dibatasi.
- Callback melakukan pemeriksaan signature di controller, walau ada masalah reachability dan transisi status.
- Upload bukti memeriksa kepemilikan donasi; akses riwayat donasi dibatasi user_id.
- Query detail program khatam dibatasi relasi pengguna.
- Reset password mandiri memakai broker token dan merotasi remember_token.
- Penghapusan kampanye yang sudah memiliki donasi ditolak.
- Konfigurasi PHPUnit saat ini mengarahkan database ke SQLite in-memory; suite tidak dijalankan dalam audit ini.

## Batas dan verifikasi lanjutan

Audit tidak memeriksa log produksi, keadaan database aktual, konfigurasi web server, HTTPS/header keamanan efektif, cron yang benar-benar aktif, reputasi/pengiriman SMTP, subscription browser nyata, atau callback sandbox Duitku. Tidak dilakukan audit advisory/CVE dependency daring. Temuan bersyarat seperti tata letak public_html, lokasi lintas zona waktu, dan penautan identitas Google perlu dikonfirmasi dengan konfigurasi penggunaan sebenarnya.

Sebelum menyatakan perbaikan selesai, jalankan uji penerimaan tiap temuan di lingkungan staging terisolasi. Jangan menggunakan database operasional untuk tes yang menghapus atau membangun ulang tabel.

