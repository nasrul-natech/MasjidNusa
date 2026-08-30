# MasjidNusa — Sistem Informasi Masjid

Platform manajemen masjid berbasis web yang dibangun dengan **Laravel 13**, mencakup informasi publik, manajemen konten, donasi digital, pembacaan Al-Qur'an, jadwal sholat, doa & zikir, hingga panel administrasi multi-peran.

**Tagline:** Menghubungkan Masjid, Umat & Kebaikan

---

## Repository

| Metode | URL |
|--------|-----|
| **SSH** (disarankan) | `git@github.com:nasrul-natech/MasjidNusa.git` |
| HTTPS | `https://github.com/nasrul-natech/MasjidNusa.git` |

> Gunakan **SSH** untuk push/pull harian tanpa password. Pastikan SSH key sudah terdaftar di GitHub.

### Workflow Git (Singkat)

```bash
# Clone (pertama kali)
git clone git@github.com:nasrul-natech/MasjidNusa.git
cd MasjidNusa

# Setelah ada perubahan
git status
git add .
git commit -m "deskripsi perubahan"
git push origin main
```

### File yang Tidak Di-upload ke Git

Berikut diabaikan otomatis via `.gitignore`:

| Item | Alasan |
|------|--------|
| `.env` | Kredensial & konfigurasi lokal |
| `vendor/`, `node_modules/` | Dependensi (install via Composer/npm) |
| `storage/` (uploads, logs, cache) | Data runtime & file user |
| `public/build/` | Hasil build Vite (jalankan `npm run build`) |
| `.cursor/` | Konfigurasi editor Cursor lokal |
| `0. referensi/` | Dokumen referensi internal |
| `.DS_Store` | Metadata macOS |

Salin `.env.example` → `.env` setelah clone, lalu jalankan setup di bawah.

---

## Daftar Isi

- [Repository](#repository)
- [Fitur Utama](#fitur-utama)
- [Teknologi](#teknologi)
- [Struktur Peran & Akses](#struktur-peran--akses)
- [Akun Default (Seeder)](#akun-default-seeder)
- [Instalasi & Setup](#instalasi--setup)
- [Modul Sistem](#modul-sistem)
- [Struktur Direktori Penting](#struktur-direktori-penting)
- [Konfigurasi Pembayaran (Duitku)](#konfigurasi-pembayaran-duitku)
- [Push Notification (PWA)](#push-notification-pwa)
- [Lisensi](#lisensi)

---

## Fitur Utama

### 🌐 Halaman Publik

| Fitur | Deskripsi |
|-------|-----------|
| **Beranda** | Tampilan utama dengan banner, popup kegiatan, informasi ringkas masjid, dan widget jadwal sholat |
| **Jadwal Sholat** | Jadwal sholat harian berbasis lokasi GPS / koordinat masjid (multi-lokasi), termasuk endpoint JSON `/api/prayer-today` |
| **Kegiatan Masjid** | Daftar dan detail kegiatan (kajian, sosial, peringatan hari besar, dll.) dengan galeri dan cerita kegiatan |
| **Dakwah & Artikel** | Artikel islami berkatagori (Dakwah, Fiqih, Motivasi, Sejarah Islam, Info Masjid) lengkap dengan rich content |
| **Donasi** | Daftar kampanye donasi aktif, detail kampanye, dan form donasi |
| **Info Masjid** | Sejarah masjid, sejarah kepengurusan, dan struktur organisasi berjenjang |
| **Doa & Zikir** | Katalog doa berkatagori dengan teks Arab, latin, dan terjemahan Indonesia; fitur simpan favorit |
| **Al-Qur'an Digital** | Baca Al-Qur'an per surah / per juz / per ayat; pencarian ayat; bookmark ayat; catatan last-read; program khatam |
| **Offline Support** | Halaman offline untuk PWA ketika tidak ada koneksi internet |

### 👤 Fitur Jamaah (Login)

| Fitur | Deskripsi |
|-------|-----------|
| **Registrasi & Login** | Daftar akun, masuk, lupa password, dan reset password via email |
| **Profil** | Edit data profil dan ganti password |
| **Donasi** | Quick donate, riwayat donasi, upload bukti transfer manual |
| **Notifikasi** | Pusat notifikasi pribadi, tandai baca / tandai semua baca |
| **Favorit Doa** | Simpan dan kelola doa favorit |
| **Bookmark Al-Qur'an** | Bookmark ayat, lanjutkan bacaan terakhir |
| **Program Khatam** | Mulai program khatam, catat progress per juz/surah |

### 🛠️ Panel Admin Operasional

Diakses melalui `/admin/operasional` — role: `admin_operasional` atau `super_admin`.

| Fitur | Deskripsi |
|-------|-----------|
| **Dashboard** | Ringkasan statistik operasional |
| **Profil Masjid** | Edit data profil dan informasi masjid |
| **Sejarah Masjid** | CRUD riwayat sejarah masjid |
| **Sejarah Kepengurusan** | CRUD periode kepengurusan |
| **Struktur Organisasi** | CRUD struktur organisasi berjenjang (parent-child) |
| **Jadwal Sholat** | CRUD jadwal, manajemen lokasi, impor CSV, fetch otomatis dari API (e-Quran), manajemen per bulan |
| **Doa & Kategori** | CRUD doa (teks Arab, latin, terjemahan) dan kategori doa |

### 💰 Panel Admin Keuangan

Diakses melalui `/admin/keuangan` — role: `admin_keuangan` atau `super_admin`.

| Fitur | Deskripsi |
|-------|-----------|
| **Dashboard** | Ringkasan statistik donasi dan keuangan |
| **Kampanye Donasi** | CRUD kampanye, toggle unggulan, penyesuaian dan sinkronisasi target |
| **Kategori Donasi** | Kelola kategori kampanye donasi |
| **Daftar Donasi** | Lihat semua transaksi donasi dengan detail |
| **Verifikasi Pembayaran** | Approve / reject bukti transfer manual |
| **Rekening Bank** | CRUD rekening bank tujuan donasi |
| **Konfigurasi Pembayaran** | Atur metode pembayaran yang aktif |
| **Konfigurasi Duitku** | Atur merchant key, kode merchant, environment (sandbox/live) |
| **Laporan** | Laporan donasi & pembayaran, ekspor data |

### 📢 Panel Admin Media IT

Diakses melalui `/admin/media-it` — role: `admin_media_it` atau `super_admin`.

| Fitur | Deskripsi |
|-------|-----------|
| **Dashboard** | Ringkasan konten dan aktivitas |
| **Kegiatan** | CRUD kegiatan + galeri + cerita; publish/unpublish |
| **Artikel** | CRUD artikel dengan rich text editor; publish/unpublish |
| **Banner** | CRUD banner halaman beranda (tipe: gambar / carousel) |
| **Popup Iklan** | CRUD popup iklan terhubung ke kegiatan, toggle aktif/nonaktif, pengaturan tipe dan durasi |
| **Kirim Notifikasi** | Broadcast push notification ke semua subscriber |

### ⚙️ Panel Super Admin

Diakses melalui `/super-admin` — role: `super_admin`.

| Fitur | Deskripsi |
|-------|-----------|
| **Dashboard** | Ringkasan keseluruhan sistem |
| **Manajemen Pengguna** | CRUD pengguna, toggle aktif/nonaktif, reset password |
| **Manajemen Peran** | Assign peran ke pengguna |
| **Konfigurasi SMTP** | Atur server email, test kirim email |
| **Template Email** | CRUD template email (selamat datang, reset password, konfirmasi donasi, dll.) dengan placeholder dinamis |
| **Aturan Pembayaran** | Atur aturan global pembayaran (biaya admin, batas minimum, dll.) |
| **Pengaturan Sistem** | Kelola pengaturan global (nama app, tagline, warna utama, dll.) |
| **Modul Sistem** | Toggle aktif/nonaktif modul (Donasi, Push Notification, Popup Iklan, dll.) |
| **Branding** | Ubah nama, logo, warna tema aplikasi |
| **Audit Log** | Riwayat semua aktivitas admin, dengan fitur purge log |

---

## Teknologi

### Backend
- **PHP 8.3+** dengan **Laravel 13**
- **Spatie Laravel Permission** v8 — manajemen peran dan izin berbasis database
- **Intervention Image** v3 — pemrosesan gambar (resize, crop, compress)
- **Laravel WebPush** v12 — push notification via Web Push Protocol
- **Duitku Payment Gateway** — integrasi pembayaran online (via DuitkuService)
- **e-Quran API** — sumber data Al-Qur'an dan jadwal sholat

### Frontend
- **Tailwind CSS 4** + `@tailwindcss/forms` + `@tailwindcss/typography`
- **Alpine.js** — reaktivitas UI ringan
- **Vite** — bundler modern dengan hot-reload
- **Blade Components** — komponen UI modular (sidebar, navbar, modal, dll.)
- **PWA-ready** — service worker, manifest, offline page

### Database
- **MySQL / MariaDB** (rekomendasi production)
- **SQLite** (default `.env.example`, cocok untuk development)

---

## Struktur Peran & Akses

```
super_admin
  └── Akses penuh ke semua panel + konfigurasi sistem

admin_operasional
  └── Panel operasional: profil masjid, jadwal sholat, doa

admin_keuangan
  └── Panel keuangan: kampanye, donasi, pembayaran, laporan

admin_media_it
  └── Panel media IT: kegiatan, artikel, banner, notifikasi

jamaah
  └── Publik + donasi + profil + favorit + bookmark + khatam
```

Setiap peran memiliki set **permissions** Spatie yang terdefinisi di `RolesAndPermissionsSeeder`, dipakai oleh middleware `role` dan `module` di semua route terproteksi.

---

## Akun Default (Seeder)

> ⚠️ **Ganti password segera setelah deploy ke production!**

| Nama | Email | Password | Peran |
|------|-------|----------|-------|
| Super Admin | `superadmin@masjidnusa.id` | `password` | super_admin |
| Admin Operasional | `operasional@masjidnusa.id` | `password` | admin_operasional |
| Admin Keuangan | `keuangan@masjidnusa.id` | `password` | admin_keuangan |
| Admin Media IT | `mediait@masjidnusa.id` | `password` | admin_media_it |
| Jamaah Demo | `jamaah@masjidnusa.id` | `password` | jamaah |

---

## Instalasi & Setup

### Prasyarat

- PHP >= 8.3 (dengan ekstensi: `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `gd`/`imagick`)
- Composer >= 2.x
- Node.js >= 18.x + npm
- MySQL 8+ / MariaDB 10.5+ (atau SQLite untuk dev)

### Langkah Instalasi

```bash
# 1. Clone repository
git clone git@github.com:nasrul-natech/MasjidNusa.git
cd MasjidNusa

# 2. Install dependensi PHP
composer install

# 3. Salin file environment
cp .env.example .env

# 4. Generate app key
php artisan key:generate

# 5. Konfigurasi database di .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=Nusa_Masjid
# DB_USERNAME=root
# DB_PASSWORD=

# 6. Jalankan migrasi & seeder
php artisan migrate --seed

# 7. Buat symlink storage
php artisan storage:link

# 8. Install dependensi frontend
npm install

# 9. Build assets
npm run build
# atau untuk development:
# npm run dev
```

### Menjalankan di Laravel Herd

Aplikasi dikonfigurasi untuk diakses via **https://masjidnusa.test** (Herd Secure).

```bash
# Pastikan site ter-link di Herd, lalu:
php artisan migrate --seed
npm run build
```

### Menjalankan Secara Lokal (alternatif)

```bash
# Jalankan semua service sekaligus (via script composer)
composer run dev

# atau manual:
php artisan serve          # Backend di http://localhost:8000
npm run dev                # Frontend hot-reload
php artisan queue:work     # Queue worker (notifikasi, email)
```

---

## Modul Sistem

Modul dapat diaktifkan/dinonaktifkan oleh Super Admin tanpa mengubah kode:

| Modul | Slug | Inti? |
|-------|------|-------|
| Kegiatan | `kegiatan` | Ya |
| Dakwah & Artikel | `artikel` | Ya |
| Donasi | `donasi` | Tidak |
| Push Notification | `push_notification` | Tidak |
| Popup Iklan | `popup_ads` | Tidak |

Middleware `module` digunakan pada route untuk memeriksa apakah modul aktif sebelum menampilkan halaman.

---

## Konfigurasi Pembayaran (Duitku)

1. Daftar akun merchant di [Duitku](https://www.duitku.com)
2. Masukkan kredensial di **Panel Super Admin → Konfigurasi Duitku** atau via seeder
3. Set environment: `sandbox` (testing) atau `live` (production)
4. Webhook Duitku di-handle oleh `POST /webhook/duitku/callback`
5. Return URL: `GET /webhook/duitku/return`

Variabel `.env` yang dibutuhkan:
```env
DUITKU_MERCHANT_CODE=your_merchant_code
DUITKU_API_KEY=your_api_key
DUITKU_ENV=sandbox  # atau live
```

---

## Push Notification (PWA)

1. Generate VAPID keys:
   ```bash
   php artisan webpush:vapid
   ```
2. Tambahkan ke `.env`:
   ```env
   VAPID_PUBLIC_KEY=your_public_key
   VAPID_PRIVATE_KEY=your_private_key
   ```
3. Jamaah dapat subscribe push notification dari halaman profil
4. Admin Media IT dapat broadcast notifikasi dari panel **Kirim Notifikasi**

---

## Login Google (Jamaah — Tanpa Password)

Jamaah dapat **masuk atau daftar** langsung via akun Gmail tanpa membuat password. Admin tetap wajib login email + password + 2FA.

### Langkah 1 — Buat OAuth Client di Google Cloud

1. Buka [Google Cloud Console](https://console.cloud.google.com/)
2. Buat project baru (atau pilih project existing) → misal: `MasjidNusa`
3. Menu **APIs & Services → OAuth consent screen**
   - User Type: **External** (untuk publik) atau Internal (organisasi)
   - App name: `MasjidNusa`
   - User support email: email admin
   - Authorized domains: `masjidnusa.test` (dev) dan domain production
   - Scopes: cukup `email`, `profile`, `openid` (default)
4. Menu **APIs & Services → Credentials → Create Credentials → OAuth client ID**
   - Application type: **Web application**
   - Name: `MasjidNusa Web`
   - **Authorized redirect URIs** (penting — harus exact match):
     - Dev (Herd): `https://masjidnusa.test/auth/google/callback`
     - Production: `https://domain-anda.com/auth/google/callback`
5. Salin **Client ID** dan **Client Secret**

### Langkah 2 — Konfigurasi `.env`

```env
GOOGLE_CLIENT_ID=123456789-xxxx.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=GOCSPX-xxxxxxxx
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
```

Jalankan:
```bash
php artisan config:clear
```

### Langkah 3 — Verifikasi

1. Buka `https://masjidnusa.test/login`
2. Tombol **"Masuk dengan Google"** harus tampil
3. Klik → redirect ke Google → pilih akun Gmail → kembali ke beranda
4. User baru otomatis terdaftar sebagai role **jamaah**

### Alur Sistem

```
Jamaah klik "Masuk/Daftar dengan Google"
  → Redirect ke Google OAuth
  → Google callback ke /auth/google/callback
  → Jika email baru → buat user jamaah + welcome email
  → Jika email ada (jamaah) → login langsung
  → Jika email admin → ditolak (wajib 2FA)
```

### Catatan Keamanan

- Akun admin **tidak bisa** login via Google
- Jamaah terdaftar via Google yang coba login password → pesan arahkan ke Google
- Avatar Google otomatis disimpan ke storage
- Semua event tercatat di audit log

---

## Struktur Direktori Penting

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/Keuangan/      # Controller panel keuangan
│   │   ├── Admin/MediaIT/       # Controller panel media IT
│   │   ├── Admin/Operasional/   # Controller panel operasional
│   │   ├── Api/                 # Push subscription API
│   │   ├── Auth/                # Login, register, reset password
│   │   ├── Public/              # Halaman publik
│   │   ├── SuperAdmin/          # Controller panel super admin
│   │   ├── User/                # Profil, notifikasi, donasi user
│   │   └── Webhook/             # Duitku webhook handler
│   └── Middleware/
├── Models/                      # 38 Eloquent models
├── Services/
│   ├── DuitkuService.php        # Integrasi payment gateway
│   ├── QuranService.php         # Fetch data Al-Qur'an dari API
│   ├── NotificationService.php  # Push & in-app notification
│   └── PrayerScheduleService.php
database/
├── migrations/                  # 19 migration files
├── seeders/
│   ├── DatabaseSeeder.php       # Entry point seeder
│   ├── UserSeeder.php           # Akun default semua peran
│   ├── RolesAndPermissionsSeeder.php
│   ├── MosqueProfileSeeder.php
│   ├── DonationSeeder.php
│   ├── ContentSeeder.php
│   ├── DoaSeeder.php            # Data doa & kategori
│   └── ZikirPagiPetangSeeder.php
resources/views/
├── public/                      # Halaman publik (beranda, quran, doa, dll.)
├── admin/
│   ├── operasional/
│   ├── keuangan/
│   └── media-it/
├── super-admin/
├── auth/                        # Login, register, reset password
└── components/                  # Shared Blade components
routes/
├── web.php                      # Semua route web (233 baris)
└── api.php                      # Reserved (belum digunakan)
```

---

## Lisensi

Proyek ini dikembangkan untuk **MasjidNusa**. Seluruh hak cipta dilindungi.

---

> Dikembangkan dengan ❤️ untuk kemakmuran masjid dan jamaah.
