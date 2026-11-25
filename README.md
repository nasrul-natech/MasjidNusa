# MasjidNusa by natech.id

Sistem informasi dan manajemen masjid berbasis Laravel untuk menghubungkan masjid, umat, dan kebaikan.

**Platform:** MasjidNusa by [natech.id](https://natech.id).

**Tagline:** Menghubungkan Masjid, Umat & Kebaikan

MasjidNusa mencakup informasi publik, kegiatan dan dakwah, donasi digital, Al-Qur'an, doa dan zikir, jadwal salat, presensi jamaah, reward kehadiran, serta display informasi masjid untuk layar TV. Pengelolaan dibagi menjadi panel Operasional, Keuangan, Media IT, dan Super Admin.

> Dokumentasi ini menjelaskan implementasi dalam repository, bukan jaminan bahwa seluruh modul sudah aktif atau terkonfigurasi pada server tertentu. Modul presensi, reward, dan display masjid nonaktif secara default.

## Daftar Isi

- [Fitur Sistem](#fitur-sistem)
- [Peran dan Akses](#peran-dan-akses)
- [Teknologi dan Prasyarat](#teknologi-dan-prasyarat)
- [Instalasi Lokal](#instalasi-lokal)
- [Akun Demo](#akun-demo)
- [Modul dan Aktivasi](#modul-dan-aktivasi)
- [Panduan Fitur Baru](#panduan-fitur-baru)
- [Konfigurasi Integrasi](#konfigurasi-integrasi)
- [Operasional Terjadwal](#operasional-terjadwal)
- [Pengujian](#pengujian)
- [Deployment Production](#deployment-production)
- [Struktur Repository](#struktur-repository)
- [Kebijakan File Git](#kebijakan-file-git)
- [Troubleshooting](#troubleshooting)
- [Lisensi](#lisensi)

## Fitur Sistem

### Halaman Publik

- **Beranda:** banner, popup kegiatan, informasi masjid, dan ringkasan jadwal salat.
- **Kegiatan:** daftar dan detail kegiatan, galeri, serta cerita kegiatan.
- **Dakwah dan artikel:** konten terbit berdasarkan kategori.
- **Info masjid:** profil, sejarah masjid, sejarah kepengurusan, dan struktur organisasi.
- **Jadwal salat:** pemilihan lokasi, pencarian lokasi terdekat, dan jadwal harian.
- **Doa dan zikir:** teks Arab, latin, terjemahan, dan kategori.
- **Al-Qur'an:** bacaan per surah, juz, atau ayat, serta pencarian.
- **Donasi:** kampanye aktif dan detail donasi, bergantung pada modul `donasi`.
- **Display masjid:** layar informasi publik di `/display-masjid`, bergantung pada modul dan publikasi konfigurasi.
- **PWA:** manifest, service worker, dan halaman offline. Dukungan ini tidak berarti seluruh fitur tersedia tanpa internet.
- **Info sistem:** halaman `/info-sistem` untuk informasi platform.

### Pengguna Login

- Registrasi, login, lupa/reset password, serta login Google untuk jamaah.
- Pengelolaan profil dan notifikasi pribadi.
- Favorit doa, bookmark ayat, posisi bacaan terakhir, dan program khatam.
- Donasi, riwayat transaksi, serta unggah bukti transfer manual.
- Pengaturan adzan: lokasi, waktu pengingat, pilihan suara, volume, dan push notification.

Profil, notifikasi, dan fitur bacaan dilindungi autentikasi; tidak semuanya eksklusif untuk role `jamaah`. Presensi dan peringkat memiliki pembatasan jamaah tersendiri.

### Khusus Jamaah

- Check-in kegiatan ketika sesi presensi dibuka operator.
- Riwayat kehadiran dan total poin.
- Peringkat kehadiran keseluruhan dan filter periode reward.

Presensi dan peringkat hanya untuk jamaah aktif tanpa peran admin. Akun yang sekaligus memiliki role jamaah dan admin tidak memenuhi syarat fitur ini.

### Panel Operasional

Lokasi: `/admin/operasional/dashboard`.

- Profil, sejarah masjid, sejarah kepengurusan, dan struktur organisasi.
- Jadwal salat manual, lokasi beserta koordinat, impor CSV, dan pengambilan jadwal bulanan dari API.
- Doa dan kategori doa.
- Pembukaan sesi presensi kegiatan sesuai permission.
- Periode reward, pratinjau peringkat, aktivasi/finalisasi, dan pencatatan penyerahan hadiah.

### Panel Keuangan

Lokasi: `/admin/keuangan/dashboard`. Panel bergantung pada modul `donasi`.

- Kampanye dan kategori donasi, target, serta status unggulan.
- Daftar transaksi dan detail donasi.
- Verifikasi atau penolakan bukti pembayaran manual.
- Rekening bank dan metode pembayaran.
- Konfigurasi Duitku.
- Laporan dan ekspor data keuangan.

### Panel Media IT

Lokasi: `/admin/media-it/dashboard`.

- Kegiatan, galeri, cerita, artikel, dan publikasi konten.
- Banner serta popup iklan kegiatan.
- Broadcast push notification.
- Pengaturan presensi per kegiatan dan aksi penyelesaian kegiatan untuk membuka sesi check-in.
- Pengaturan display masjid, playlist, preview, dan publish.

### Panel Super Admin

Lokasi: `/super-admin/dashboard`.

- Pengguna, peran, status akun, dan reset password.
- SMTP dan pengujian email, serta template email.
- Aturan pembayaran global.
- Pengaturan sistem, modul, dan branding.
- Audit log.
- Akses lintas panel sesuai pemeriksaan permission pada route.

## Peran dan Akses

- `super_admin`: administrasi sistem dan akses lintas panel.
- `admin_operasional`: informasi masjid, jadwal salat, doa, serta presensi/reward sesuai izin.
- `admin_keuangan`: donasi, verifikasi, pembayaran, dan laporan.
- `admin_media_it`: konten, notifikasi, pengaturan presensi kegiatan, dan display.
- `jamaah`: fitur pengguna serta presensi/peringkat sesuai kelayakan akun.

Panel admin memerlukan **login, verifikasi 2FA, role yang sesuai, dan permission terkait**. Role saja tidak menggantikan pemeriksaan permission. Status modul juga dapat membatasi akses.

Permission dasar berasal dari `RolesAndPermissionsSeeder`. Permission presensi, reward, dan display dikelola melalui `AttendanceFeaturePermissions` dan command provisioning. Middleware `role`, `permission`, `module`, serta `new-feature` memiliki fungsi berbeda.

Admin wajib menggunakan login email/password dan 2FA. Login Google tidak digunakan untuk masuk sebagai admin.

## Teknologi dan Prasyarat

### Backend dan Data

- Laravel 13; versi terkunci saat pembaruan dokumentasi: `13.29.0`.
- **PHP >=8.4.1 untuk `composer.lock` saat ini.** Constraint root `composer.json` masih `^8.3`, tetapi dependensi Symfony terkunci membutuhkan versi lebih tinggi.
- Composer 2 dengan runtime API yang memenuhi lockfile. Periksa melalui `composer check-platform-reqs --lock`.
- MySQL/MariaDB untuk instalasi aplikasi; `.env.example` memakai MySQL.
- SQLite untuk pengujian otomatis dalam memori.
- Spatie Laravel Permission 8, Laravel Socialite 5, Google2FA 8, Bacon QR Code 3, Intervention Image 3, dan Laravel WebPush 12.
- Duitku untuk pembayaran; Aladhan dengan metode Kemenag untuk jadwal salat. Command impor Al-Qur'an menggunakan Quran.com API v4.

### Frontend

- Blade, Tailwind CSS 4, Alpine.js 3, Tom Select, dan Axios.
- Vite 7 dengan Laravel Vite Plugin 2.
- **Node.js `^20.19.0 || >=22.12.0`** dan npm untuk instalasi/build aset sesuai lockfile.
- HTTPS untuk deployment publik, OAuth, dan fitur browser seperti push notification.

Pastikan ekstensi PHP memenuhi Composer, termasuk ekstensi XML, cURL, fileinfo, dan driver PDO database yang digunakan. Pemrosesan gambar memerlukan driver gambar yang sesuai, seperti GD atau Imagick. Kebutuhan kode aplikasi dan driver database tetap perlu diperiksa selain hasil Composer.

## Instalasi Lokal

### 1. Ambil Source dan Dependensi

```bash
git clone git@github.com:nasrul-natech/MasjidNusa.git
cd MasjidNusa
composer install
cp .env.example .env
php artisan key:generate
```

Alternatif URL clone: `https://github.com/nasrul-natech/MasjidNusa.git`.

### 2. Siapkan Environment dan Database

Buat database lokal terlebih dahulu, lalu sesuaikan `.env`:

```dotenv
APP_ENV=local
APP_DEBUG=true
APP_URL=https://masjidnusa.test
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=Nusa_Masjid
DB_USERNAME=root
DB_PASSWORD=
```

Session, cache, dan queue pada contoh environment menggunakan database. Migrasi harus selesai sebelum aplikasi digunakan.

### 3. Inisialisasi Instalasi Development Baru

> Perintah seeder berikut hanya untuk database development baru yang memang ingin diisi data demo. Jangan menggunakannya sebagai langkah update production.

```bash
php artisan migrate --seed
php artisan features:provision-attendance-display
php artisan storage:link
npm ci
npm run build
```

Seeder role kini menambahkan permission fitur baru setelah role terbentuk. Command provisioning tetap tersedia untuk instalasi lama atau pemeriksaan ulang; bersifat idempoten, tidak mengganti permission lama, dan tidak mengaktifkan modul.

Data Al-Qur'an lokal tidak dimasukkan oleh `DatabaseSeeder`. Bila diperlukan, impor terpisah dengan akses internet:

```bash
php artisan quran:seed
```

Command tersebut menulis data Al-Qur'an ke database. Jangan memakai opsi `--fresh` sebagai langkah rutin.

### 4. Jalankan Aplikasi

**Laravel Herd:** hubungkan folder sebagai site `masjidnusa`, gunakan PHP yang memenuhi lockfile, dan aktifkan HTTPS agar sesuai `APP_URL`. Akses `https://masjidnusa.test`.

**Tanpa Herd:** sesuaikan `APP_URL=http://localhost:8000`, kemudian jalankan:

```bash
composer run dev
```

Script tersebut menjalankan server Laravel, queue listener, log viewer, dan Vite. Alternatifnya, jalankan proses berikut di terminal terpisah:

```bash
php artisan serve
npm run dev
php artisan queue:work
php artisan schedule:work
```

Scheduler diperlukan untuk pengingat adzan dan pengambilan jadwal otomatis; `composer run dev` tidak menjalankannya.

## Akun Demo

`UserSeeder` menyediakan akun berikut dengan password **`password`** untuk development:

- Super Admin: `superadmin@masjidnusa.id`.
- Admin Operasional: `operasional@masjidnusa.id`.
- Admin Keuangan: `keuangan@masjidnusa.id`.
- Admin Media IT: `mediait@masjidnusa.id`.
- Jamaah Demo: `jamaah@masjidnusa.id`.

> Jangan mengekspos akun demo dengan password bawaan ke internet. `UserSeeder` menggunakan `updateOrCreate` dan `syncRoles`; menjalankannya ulang dapat mengganti password serta peran akun bawaan. Siapkan akun production melalui prosedur provisioning yang ditinjau, gunakan password unik, dan selesaikan setup 2FA admin.

## Modul dan Aktivasi

Modul dikelola melalui panel Super Admin. Daftar modul dalam kode:

- `kegiatan`: modul inti kegiatan.
- `artikel`: modul inti dakwah/artikel.
- `donasi`: donasi dan panel keuangan.
- `push_notification`: push notification.
- `popup_ads`: popup iklan.
- `activity_attendance`: presensi kegiatan, **default nonaktif**.
- `attendance_rewards`: reward kehadiran, **default nonaktif**, memerlukan `activity_attendance` aktif.
- `mosque_display`: display masjid, **default nonaktif**.

Untuk melengkapi permission fitur baru pada role yang sudah ada:

```bash
php artisan features:provision-attendance-display
```

Provisioning permission tidak sama dengan aktivasi modul. Presensi masih perlu diaktifkan per kegiatan, sedangkan display membutuhkan publikasi konfigurasi.

Route dengan middleware modul lama dapat mengembalikan `503` saat modul dinonaktifkan. Route fitur baru mengembalikan `404` saat fitur tidak tersedia atau nonaktif. Perilaku ini bukan otomatis berarti route hilang dari kode.

## Panduan Fitur Baru

### Presensi Kegiatan

1. Super Admin mengaktifkan `activity_attendance` setelah pengujian dan persetujuan operator.
2. Media IT membuat atau mengedit kegiatan, mengaktifkan presensi, dan menetapkan durasi check-in 1-30 menit.
3. Kegiatan harus sudah terbit dan waktu mulai sudah tercapai sebelum operator membuka sesi. Operasional memiliki route pembukaan sesuai permission; Media IT dapat membuka sesi melalui aksi penyelesaian kegiatan.
4. Jamaah aktif tanpa peran admin melakukan check-in selama sesi terbuka. Satu check-in dihitung satu poin; pengulangan tidak menambah catatan yang sama.
5. Jamaah melihat riwayat di `/user/kehadiran` dan peringkat di `/user/kehadiran/peringkat`.

Sesi tidak terbuka otomatis hanya karena jadwal kegiatan tercapai. Pengaturan presensi terkunci setelah sesi dibuka. Check-in berbasis login dan tombol, **bukan verifikasi kehadiran fisik dengan GPS atau QR**.

### Reward Kehadiran

1. Aktifkan `activity_attendance` dan `attendance_rewards`.
2. Operator berizin membuat periode dengan tanggal, timezone, dan pilihan 3 atau 10 pemenang.
3. Tinjau peringkat, kemudian aktifkan periode. Alur status: `draft`, `active`, `final`.
4. Finalisasi sesuai aturan cut-off dan pastikan tidak ada sesi presensi terbuka dalam rentang periode. Finalisasi menyimpan snapshot hasil.
5. Catat status hadiah sebagai `pending`, `ready`, atau `delivered`. Status `delivered` tidak dapat diubah kembali.

Peringkat memakai jumlah check-in; seri diurutkan menurut waktu pencapaian lebih awal, lalu ID pengguna. Finalisasi bukan pembayaran otomatis. Nama dan poin peringkat ditujukan bagi jamaah yang login, bukan untuk TV publik.

### Display Masjid

1. Aktifkan `mosque_display` dan pastikan pengelola memiliki permission terkait.
2. Buka `/admin/media-it/mosque-display` untuk mengatur identitas, tema, layout, lokasi jadwal salat, pengumuman, dan playlist.
3. Tambahkan foto atau video YouTube, urutan, durasi, serta rentang tayang bila diperlukan.
4. Periksa draft melalui preview. Layout tersedia: `focus`, `split`, dan `immersive`; media mendukung `contain` atau `cover`.
5. Publish konfigurasi, lalu buka `/display-masjid` pada browser TV. Perubahan draft berikutnya tidak mengubah tampilan publik sebelum dipublikasikan lagi.

Mode visual adzan, hitung mundur iqamah, dan tampilan saat salat memerlukan pengaturan tersendiri. Simulasi mode tersedia pada preview admin, bukan sebagai mode simulasi publik.

Feed publik berada di `/display-masjid/feed` dan belum tersedia sebelum publikasi pertama. YouTube membutuhkan internet; autoplay, audio, dan fullscreen mengikuti izin browser/perangkat. Cache display bukan jaminan semua media dan jadwal tetap tersedia atau valid saat offline. Uji langsung pada TV tujuan sebelum digunakan.

## Konfigurasi Integrasi

### Jadwal Salat

Kelola lokasi dan koordinat melalui panel Operasional. Pengambilan jadwal bulanan memakai Aladhan dengan metode Kemenag (method 20). Jadwal manual dan impor CSV juga tersedia.

Untuk mengambil jadwal bulan tertentu:

```bash
php artisan prayer:fetch-schedules --month=11 --year=2026
```

Sesuaikan bulan/tahun; tambahkan `--location=ID` untuk satu lokasi. Tanpa bulan/tahun, command mengambil bulan berikutnya. Pastikan jadwal bulan berjalan sudah tersedia saat instalasi pertama, serta tinjau koordinat dan timezone lokasi.

### Email dan SMTP

`.env.example` memakai `MAIL_MAILER=log`; email tidak dikirim ke penerima melalui SMTP pada konfigurasi awal tersebut. Super Admin dapat mengelola konfigurasi SMTP dan menguji pengiriman melalui panel.

Konfigurasikan pengirim yang valid sebelum mengandalkan reset password atau notifikasi email. Jangan memasukkan kredensial SMTP ke Git.

### Login Google

1. Siapkan OAuth consent screen dan OAuth client bertipe Web application di [Google Cloud Console](https://console.cloud.google.com/).
2. Gunakan scope identitas `openid`, `email`, dan `profile`. Atur audience dan test users sesuai status aplikasi Google.
3. Daftarkan redirect URI persis sesuai environment, misalnya `https://masjidnusa.test/auth/google/callback` untuk lokal dan `https://domain-anda.com/auth/google/callback` untuk production.
4. Isi variabel berikut pada `.env`, lalu segarkan cache konfigurasi.

```dotenv
GOOGLE_CLIENT_ID=your_client_id
GOOGLE_CLIENT_SECRET=your_client_secret
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
```

```bash
php artisan config:clear
```

Uji login dan pendaftaran jamaah dari `/login`. Jangan memasukkan domain `.test` sebagai authorized domain production di Google. Admin tetap memakai email/password dan 2FA.

### Pembayaran Duitku

1. Siapkan akun merchant Duitku dan aktifkan modul `donasi`.
2. Buka **Admin Keuangan > Konfigurasi Duitku** menggunakan akun berizin. Super Admin dapat mengakses melalui panel tersebut.
3. Isi kode merchant, API key, dan konfigurasi terkait. Nilai environment aplikasi adalah **`sandbox`** atau **`production`**.
4. Konfigurasikan callback `POST /webhook/duitku/callback` dan return `GET /webhook/duitku/return` memakai domain HTTPS yang dapat dijangkau.
5. Uji transaksi sandbox, callback, dan perubahan status sebelum memakai konfigurasi production.

`DuitkuService` membaca konfigurasi aktif dari database melalui `DuitkuConfig`. Variabel `DUITKU_*` masih ada pada `.env.example`, tetapi mengisinya saja bukan pengganti konfigurasi aktif di panel.

Bukti transfer manual adalah dokumen privat. Command `php artisan proofs:privatize` tersedia untuk memindahkan bukti lama dari disk publik; lakukan backup dan tinjau kebutuhan migrasi berkas sebelum menjalankannya pada data berjalan.

### PWA, Push, dan Adzan

1. Gunakan HTTPS dan browser yang mendukung service worker serta Web Push.
2. Generate VAPID keys pada environment tujuan dengan `php artisan webpush:vapid`, lalu pastikan konfigurasi VAPID terisi.
3. Aktifkan modul `push_notification` dan minta izin notifikasi dari pengguna.
4. Pengguna mengatur lokasi dan preferensi di `/user/adzan-settings`; Media IT dapat mengirim broadcast melalui panel notifikasi.
5. Jalankan scheduler dan queue worker sesuai konfigurasi aplikasi.

```dotenv
VAPID_SUBJECT=mailto:admin@domain-anda.com
VAPID_PUBLIC_KEY=your_public_key
VAPID_PRIVATE_KEY=your_private_key
```

Pengaturan adzan mendukung suara `simple`, `makkah`, dan `madinah`, volume, pilihan waktu salat, serta pengingat 0-30 menit sebelumnya. Suara pada browser memerlukan interaksi/izin pengguna dan tidak dijamin berjalan ketika browser ditutup. Push bergantung pada izin, subscription, konfigurasi, dan scheduler.

## Operasional Terjadwal

`routes/console.php` mendefinisikan:

- `adzan:send`: pemeriksaan dan pengiriman pengingat setiap menit.
- `prayer:fetch-schedules`: pengambilan jadwal bulan berikutnya setiap tanggal 25 pukul 02:00, mengikuti timezone scheduler aplikasi.

Development dapat memakai `php artisan schedule:work`. Production memerlukan cron `schedule:run` setiap menit. Queue worker harus dijalankan sebagai proses terkelola bila memakai `QUEUE_CONNECTION=database`.

## Pengujian

```bash
composer validate --no-check-publish
composer check-platform-reqs --lock
composer test
node --test tests/JavaScript/mosque-display.test.cjs
```

PHPUnit dikonfigurasi memakai SQLite `:memory:`. `tests/TestCase.php` menolak database selain database pengujian tersebut untuk mencegah penghapusan data aplikasi.

Jika cache konfigurasi lokal mengganggu pengujian, gunakan path cache terpisah yang belum ada tanpa menonaktifkan pengaman:

```bash
APP_CONFIG_CACHE=/tmp/masjidnusa-tests-config.php vendor/bin/phpunit --do-not-cache-result
```

Cakupan tes meliputi presensi, reward, peringkat jamaah, display, branding, dan settlement donasi. Tes JavaScript display memakai Node test runner tanpa instalasi test framework tambahan.

Regresi mencakup batas input terhadap skema, kelengkapan jadwal salat, provisioning permission, hierarki organisasi, urutan surah, serta penulisan bookmark/progres. Timeline `next_prayer` dan `previous_prayer` display hanya memakai lima waktu salat; Imsak/Terbit tetap tersedia pada footer. Jalankan tes pada versi kode yang akan dirilis; hasil lokal bukan jaminan production.

SQLite tidak membuktikan perilaku locking MySQL/MariaDB. Uji konkurensi check-in/finalisasi, integrasi pembayaran, browser, serta perangkat TV pada staging yang sesuai production.

### Pemeriksaan Integritas Sebelum Constraint Baru

Command berikut hanya membaca database dan menampilkan jumlah kelompok konflik, tanpa menampilkan identitas atau menghapus data:

```bash
php artisan system:check-data-integrity
```

Pemeriksaan mencakup bukti pembayaran ganda per transaksi, bookmark ganda, lebih dari satu posisi bacaan terakhir per pengguna, progres khatam ganda, dan pasangan lokasi e-Quran non-null yang duplikat. Exit code `0` berarti tidak ditemukan konflik pada pemeriksaan tersebut; `1` berarti ada konflik. Hasil bersih bukan bukti seluruh integritas database terjamin atau bebas perubahan bersamaan.

Constraint tambahan belum dipasang otomatis. Jika konflik ditemukan, tinjau data serta keputusan pemulihannya terlebih dahulu; jangan menghapus atau menggabungkan riwayat secara otomatis. Perubahan aplikasi ini tidak membersihkan konflik historis.

## Deployment Production

Panduan rinci: [Panduan Deploy Hostinger](deploy/PANDUAN-DEPLOY.md).

Struktur server yang digunakan skrip deploy:

```text
~/domains/masjid.nusasuite.id/
  app/                  Source Laravel, .env, vendor, dan storage
    public/             Entry point web
  public_html           Symlink ke app/public
```

### Persiapan Wajib

1. Backup database dan media, verifikasi pemulihan, serta catat versi kode/aset sebelum migrasi.
2. Uji staging dengan database setara production dan data tersanitasi.
3. Pastikan PHP web, SSH, Composer, cron, dan queue worker memenuhi **PHP >=8.4.1**. PHP web dan CLI Hostinger dapat berbeda. Helper deploy masih dapat memilih PHP 8.3; jangan mengandalkan deteksi helper sebagai bukti kompatibilitas lockfile.
4. Siapkan `.env` production dengan `APP_ENV=production`, `APP_DEBUG=false`, URL HTTPS, database, dan kredensial integrasi yang benar.
5. Pertahankan `APP_KEY` instalasi yang sudah berjalan. Jangan melakukan regenerasi key sebagai langkah update karena data terenkripsi dapat tidak terbaca.
6. Pastikan web root hanya mengarah ke `public/`; jangan mengekspos source, `.env`, atau storage privat.

### Instalasi Baru

Skrip tersedia di `deploy/hostinger-setup.sh`. Tinjau skrip dan isi direktori server sebelum menjalankannya: skrip dapat memindahkan `public_html` lama sebagai backup dan membuat symlink baru. Jangan menghapus web root berisi data secara manual.

Skrip memasang dependensi, menyiapkan environment/storage, menjalankan migrasi, dan membangun cache. **Skrip tidak membuat akun admin dan data awal melalui seeder global.** Siapkan provisioning akun/peran production secara terkontrol sebelum membuka aplikasi untuk publik; lalu jalankan provisioning permission fitur jika role sudah tersedia.

### Update Instalasi Berjalan

Setelah backup, pengujian, dan persetujuan operator:

```bash
bash ~/domains/masjid.nusasuite.id/app/deploy/hostinger-update.sh
```

Skrip memakai branch `main` secara default; `DOMAIN_DIR`, `BRANCH`, dan `PHP_BIN` dapat disesuaikan. Pastikan `PHP_BIN` dan executable PHP pada `PATH` untuk Composer memakai versi yang kompatibel.

Skrip memperbarui kode/dependensi, menjalankan migrasi, memeriksa aset, dan menyegarkan cache. Skrip tidak menyediakan backup atau rollback otomatis. Jika manifest build sudah ada, helper melewati build npm, sehingga kode sumber dan aset yang dikirim harus sudah cocok.

> Jangan menjalankan `migrate:fresh`, `migrate:refresh`, `db:wipe`, atau seeder global pada database production sebagai langkah update. Migrasi biasa juga tetap perlu ditinjau; perintah `migrate` bukan jaminan bahwa semua perubahan skema selalu aman.

### Aset dan Proses Server

- `public/build/manifest.json` dan `public/build/assets/` dilacak Git. Build frontend di lokal dengan `npm ci` dan `npm run build`, lalu kirim manifest beserta seluruh aset yang dirujuk secara konsisten.
- `deploy/sync-assets.sh` merupakan alternatif sinkronisasi aset. Tinjau target `SSH_HOST`, `SSH_PORT`, dan `REMOTE_APP` sebelum menjalankannya; skrip memakai `rsync --delete` pada direktori build tujuan.
- Jalankan queue worker melalui pengelola proses sesuai fasilitas hosting. Periksa log dan kegagalan job setelah deploy.
- Tambahkan cron setiap menit. Contoh berikut mengasumsikan binary PHP Hostinger sudah diverifikasi memenuhi lockfile:

```cron
* * * * * cd ~/domains/masjid.nusasuite.id/app && /opt/alt/php84/usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

Sesuaikan path PHP dan domain dengan server tujuan. Pada Hostinger, jangan menjalankan wrapper Composer sebagai `php $(which composer)`.

Pertahankan modul baru nonaktif saat smoke test login/2FA, kegiatan, jadwal salat, dan donasi. Aktivasi presensi, reward, dan display dilakukan bertahap setelah persetujuan operator. Saat regresi, utamakan nonaktifkan modul; jangan menghapus riwayat kehadiran atau snapshot hadiah.

## Struktur Repository

```text
app/
  Console/Commands/           Jadwal, adzan, impor Quran, dan provisioning
  Http/Controllers/          Publik, auth, pengguna, admin, dan webhook
  Http/Middleware/           Autentikasi, 2FA, role, permission, dan modul
  Models/                    Model data aplikasi
  Services/                  Jadwal, Quran, Duitku, presensi, reward, display
  Support/                   Helper keamanan, permission, dan feature gate
config/                      Konfigurasi framework dan aplikasi
database/
  migrations/                Skema dan provisioning fitur
  seeders/                   Data awal serta akun demo
deploy/                      Skrip dan panduan Hostinger
docs/                        Dokumen audit terdahulu
public/
  build/                     Manifest dan aset Vite terlacak Git
  js/mosque-display.js       Player display masjid
  sw.js                      Service worker
resources/
  css/                       Sumber style
  js/                        JavaScript aplikasi dan PWA
  views/                     Blade publik, pengguna, admin, dan display
routes/
  web.php                    Route utama dan pemuatan route fitur
  activity-attendance.php    Presensi dan peringkat jamaah
  attendance-rewards.php     Administrasi reward
  mosque-display.php         Display publik dan administrasi
  console.php                Scheduler
  api.php                    Belum berisi definisi endpoint
tests/
  Feature/                   Tes integrasi aplikasi
  Unit/                      Tes unit
  JavaScript/                Tes player display
```

Endpoint berawalan `/api/`, seperti `/api/prayer-today` dan `/api/adzan/schedule-today`, juga didefinisikan melalui route web. Awalan URL tersebut bukan berarti autentikasi token API terpisah; operasi web tetap mengikuti middleware dan aturan CSRF masing-masing.

## Kebijakan File Git

- Jangan commit `.env`, kredensial, private key, database lokal, atau data pengguna.
- `vendor/` dan `node_modules/` diabaikan; pasang dari lockfile.
- Upload, log, session, dan cache runtime di `storage/` diabaikan, selain file penjaga direktori terkait.
- `public/storage` adalah symlink runtime, bukan sumber upload yang disimpan di Git.
- Manifest dan aset `public/build/` **disimpan di Git** untuk deployment yang memakai hasil build.
- `.cursor/`, `.kilo/`, `0. referensi/`, dan metadata OS diabaikan sesuai `.gitignore`.

Folder `0. referensi/` dapat tetap ada di komputer lokal tanpa ikut commit biasa. Aturan ignore tidak menghapus file dari disk atau dari riwayat commit lama.

## Troubleshooting

- **Composer menolak versi PHP:** periksa PHP CLI dan kebutuhan lockfile, bukan hanya versi web pada hosting. Jalankan `composer check-platform-reqs --lock`; jangan melewati platform requirements.
- **Halaman tanpa CSS/JS:** pastikan `public/build/manifest.json` dan semua file yang dirujuk tersedia dari build yang sama. Build ulang dan kirim aset secara lengkap.
- **Upload tidak tampil:** periksa `php artisan storage:link`, lokasi upload publik, dan permission direktori. Bukti pembayaran serta media draft tidak boleh dijadikan publik untuk mengatasi masalah akses.
- **Admin diarahkan ke setup/challenge:** selesaikan 2FA dan periksa role/permission; jangan menonaktifkan middleware.
- **Fitur baru 404:** periksa provisioning modul, aktivasi, permission, dan dependensinya. Untuk display, periksa publikasi pertama.
- **Presensi belum tersedia:** periksa kegiatan terbit, waktu mulai, konfigurasi presensi per kegiatan, sesi operator, serta kelayakan akun jamaah.
- **Jadwal atau adzan kosong:** periksa koordinat, timezone, data bulan berjalan, preferensi pengguna, izin notifikasi, dan scheduler.
- **Login Google gagal:** cocokkan redirect URI persis, `APP_URL`, OAuth credentials, audience/test users, dan cache konfigurasi.
- **Email tidak sampai:** pastikan mailer bukan `log`, konfigurasi SMTP valid, pengirim diizinkan, dan proses queue berjalan bila dibutuhkan.
- **Tes menolak koneksi MySQL:** gunakan konfigurasi pengujian SQLite dalam memori dan path cache terpisah seperti pada bagian pengujian. Jangan melewati pengaman database.

## Lisensi

MasjidNusa menggunakan [Lisensi Atribusi MasjidNusa 1.0](LICENSE), dengan identifier `LicenseRef-MasjidNusa-Attribution`. Ini lisensi khusus, bukan MIT standar atau klaim lisensi open-source yang disetujui OSI.

- Bebas digunakan, disalin, dimodifikasi, dibagikan, dan dipakai untuk layanan komersial tanpa biaya lisensi.
- Nama, logo, warna, domain, dan konten masjid boleh diganti melalui UI maupun kode.
- Kredit **MasjidNusa by natech.id** beserta tautan `https://natech.id` wajib dipertahankan pada footer/area atribusi publik sesuai ketentuan lengkap di `LICENSE`. Kredit tidak boleh dihapus, disembunyikan, atau dibuat tidak terbaca.
- Distribusi salinan dan versi modifikasi wajib menyertakan lisensi serta pemberitahuan terkait. Modifikasi perlu ditandai; publikasi kode sumber modifikasi tidak diwajibkan.
- Dependensi dan materi pihak ketiga tetap mengikuti lisensi masing-masing. Izin versi terdahulu yang sudah diberikan secara sah, termasuk MIT, tidak dicabut oleh lisensi ini.

Kewajiban lisensi tidak berarti footer terkunci secara teknis. Periksa cakupan kredit pada setiap deployment, terutama layout baru dan layar TV. Lisensi ini bukan bukti bahwa seluruh tampilan sudah memenuhi ketentuannya. Tinjauan hukum disarankan sebelum mengandalkannya untuk distribusi komersial atau penegakan hak; teks lengkap `LICENSE` menjadi acuan, bukan ringkasan ini.
