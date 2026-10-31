# Panduan Deploy MasjidNusa ke Hostinger

## Rilis Check-in, Poin Kehadiran, dan Display Masjid

Fitur baru default nonaktif: `activity_attendance`, `attendance_rewards`, dan
`mosque_display`. Deploy kode tidak berarti izin mengaktifkan fitur produksi.

1. Uji staging memakai DB setara produksi dan salinan data tersanitasi. Uji
   request bersamaan untuk membuka absensi, check-in, dan finalisasi hadiah;
   SQLite tidak membuktikan perilaku locking MySQL/MariaDB.
2. Backup DB dan media, verifikasi pemulihan, serta catat versi kode/aset sebelum
   migrasi. Skrip update tidak menyediakan backup atau rollback otomatis.
3. Gunakan PHP server yang sesuai, jalankan migrasi additive, deploy kode dan
   seluruh `public/build/` yang cocok dengan manifest. Jangan menjalankan
   `migrate:fresh`, `db:wipe`, atau seeder global pada produksi.
4. Jalankan `php artisan features:provision-attendance-display` bila perlu
   menambahkan izin fitur pada peran yang sudah ada. Perintah idempoten ini
   tidak mengganti izin lama dan tidak mengaktifkan modul. Berguna pula untuk
   instalasi baru yang membuat peran setelah migrasi.
5. Smoke test kegiatan publik, CRUD kegiatan, login/2FA, jadwal salat, dan donasi
   dengan semua modul baru tetap nonaktif. Setelah persetujuan operator,
   aktifkan `activity_attendance` melalui Super Admin pada satu kegiatan pilot;
   Media IT masih harus mengaktifkan absensi pada kegiatan tersebut.
6. Aktifkan `attendance_rewards` setelah pilot lulus. Modul ini juga memerlukan
   `activity_attendance` aktif. Simulasikan cut-off di staging, bukan dengan
   poin palsu atau hadiah final di produksi. Aktifkan `mosque_display` terpisah
   setelah uji perangkat TV dan koneksi.
7. Jika terjadi regresi, nonaktifkan modul baru lalu pulihkan kode/aset yang
   kompatibel. Pertahankan tabel kehadiran dan snapshot hadiah; migrasi
   kehadiran/hadiah menolak rollback destructive. Jangan memulihkan seluruh DB
   dari backup tanpa keputusan insiden, karena transaksi baru dapat hilang.

Pengujian lokal bila ada cache konfigurasi aplikasi: gunakan jalur cache
terpisah yang tidak ada, misalnya
`APP_CONFIG_CACHE=/tmp/masjid-tests-config.php php artisan test`. Tes harus
memakai SQLite `:memory:`; jangan melewati pengaman `tests/TestCase.php`.

Check-in awal hanya login dan tombol, bukan bukti hadir fisik. YouTube butuh
internet dan autoplay mengikuti pembatasan browser. Tidak ada pembayaran
hadiah otomatis atau penampilan identitas jamaah pada TV.

Panduan ini untuk **pull/update kode dari GitHub ke server** tanpa salah pakai PHP versi lama.

**Domain production:** https://masjid.nusasuite.id  
**Repo:** `git@github.com:nasrul-natech/MasjidNusa.git`

---

## Struktur Folder di Server

```
~/domains/masjid.nusasuite.id/
├── app/                    ← source Laravel (git clone)
│   ├── artisan
│   ├── public/
│   ├── storage/
│   └── deploy/
└── public_html → app/public   ← symlink web root Hostinger
```

---

## ⚠️ PENTING: PHP Web ≠ PHP SSH

| Perintah | Versi biasanya | Keterangan |
|----------|----------------|------------|
| `php -v` di SSH | **8.2.x** | Default terminal — **JANGAN dipakai untuk artisan** |
| PHP di hPanel (website) | **8.4.x** | Website jalan normal |
| `/opt/alt/php84/usr/bin/php -v` | **8.4.x** | **Pakai ini untuk artisan & cron** |

Jika Anda menjalankan:

```bash
php artisan config:cache   # ❌ SALAH (PHP 8.2)
```

Akan muncul error:

```
Your Composer dependencies require a PHP version ">= 8.4.1".
You are running 8.2.30.
```

**Solusi:** Selalu pakai PHP 8.4 di SSH, atau gunakan script deploy (otomatis deteksi PHP 8.4).

---

## Cara Termudah — Script Otomatis (Disarankan)

SSH ke server:

```bash
ssh -p 65002 u568257859@46.202.136.199
```

Jalankan update:

```bash
cd ~/domains/masjid.nusasuite.id/app
bash deploy/hostinger-update.sh
```

Script ini otomatis:
1. Deteksi PHP 8.4 (`/opt/alt/php84/usr/bin/php`)
2. `git pull origin main`
3. `composer install`
4. `php artisan migrate --force` — hanya menambah tabel/kolom baru. **Tidak** menjalankan `migrate:fresh`, `db:wipe`, atau `db:seed`, jadi data produksi tidak dihapus.
5. `php artisan storage:link`
6. Build assets (jika npm tersedia)
7. Verifikasi file CSS/JS build
8. Clear & cache config, route, view

---

## Cara Manual (Step-by-Step)

### 1. Masuk ke folder aplikasi

```bash
cd ~/domains/masjid.nusasuite.id/app
```

### 2. Set PHP 8.4 untuk sesi SSH ini

```bash
export PATH="/opt/alt/php84/usr/bin:$PATH"
php -v
```

Pastikan output: `PHP 8.4.x` (bukan 8.2).

> **Tip permanen:** Tambahkan ke `~/.bashrc` agar tidak lupa:
>
> ```bash
> echo 'export PATH="/opt/alt/php84/usr/bin:$PATH"' >> ~/.bashrc
> source ~/.bashrc
> ```

### 3. Pull kode terbaru dari GitHub

```bash
git fetch origin
git checkout main
git pull origin main
```

### 4. Install dependency PHP

```bash
composer install --no-dev --optimize-autoloader --no-interaction
```

> **Jangan** jalankan: `php $(which composer) install` — di Hostinger, `composer` adalah wrapper bash.

### 5. Migrate database (jika ada migration baru)

Perintah ini hanya menambah tabel atau kolom yang belum ada. Jangan pakai `migrate:fresh`, `migrate:refresh`, `db:wipe`, atau `db:seed` di server produksi.

```bash
php artisan migrate --force
```

### 6. Storage link (upload gambar/file user)

```bash
php artisan storage:link --force
```

### 7. Clear & cache

```bash
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Frontend Assets (CSS/JS)

Folder `public/build/` berisi Tailwind CSS & JavaScript hasil build Vite.

### Opsi A — Sudah di-commit ke Git (disarankan)

Setelah `git pull`, file CSS/JS ikut ter-download. Script `hostinger-update.sh` akan mengecek kelengkapan file.

### Opsi B — Build & upload dari komputer lokal

Jika di server **tidak ada npm**, lakukan di komputer lokal:

```bash
# Di komputer lokal (folder project)
npm run build
git add -f public/build/
git commit -m "Update build assets"
git push origin main
```

Lalu di server: `git pull` + `bash deploy/hostinger-update.sh`

**Atau** upload langsung tanpa commit:

```bash
# Di komputer lokal
npm run build
bash deploy/sync-assets.sh
```

### Gejala asset hilang

- Halaman tampil tanpa styling (HTML polos)
- Konten mobile & desktop muncul dobel
- Error deploy: `Asset hilang: public/build/assets/...`

---

## Workflow Lengkap: Lokal → Production

### Di komputer lokal (setelah coding)

```bash
# 1. Build frontend (jika ada perubahan CSS/JS)
npm run build

# 2. Commit & push
git add .
git add -f public/build/          # pastikan CSS ikut
git commit -m "Deskripsi perubahan"
git push origin main
```

### Di server Hostinger

```bash
ssh -p 65002 u568257859@46.202.136.199
cd ~/domains/masjid.nusasuite.id/app
bash deploy/hostinger-update.sh
```

### Verifikasi

Buka https://masjid.nusasuite.id dan cek:
- [ ] Halaman tampil dengan styling (bukan HTML polos)
- [ ] Upload gambar berfungsi
- [ ] Login admin berfungsi

---

## Cron Job (Wajib)

Di **hPanel → Advanced → Cron Jobs**, tambahkan:

```cron
* * * * * cd ~/domains/masjid.nusasuite.id/app && /opt/alt/php84/usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

> Gunakan path PHP 8.4 penuh — **bukan** `php artisan`.

---

## Troubleshooting

### Error: PHP >= 8.4.1 required, running 8.2.30

```bash
export PATH="/opt/alt/php84/usr/bin:$PATH"
php -v   # harus 8.4.x
```

Lalu ulangi perintah artisan.

---

### Error: Asset CSS/JS tidak lengkap

```bash
# Di lokal
npm run build
git add -f public/build/
git commit -m "Fix build assets"
git push

# Di server
git pull origin main
bash deploy/hostinger-update.sh
```

---

### Error: Permission denied storage

```bash
cd ~/domains/masjid.nusasuite.id/app
chmod -R ug+rwx storage bootstrap/cache
```

---

### Gambar upload tidak muncul

```bash
php artisan storage:link --force
ls -la public/storage   # harus symlink ke ../storage/app/public
```

---

### Symlink public_html rusak

```bash
cd ~/domains/masjid.nusasuite.id
rm -rf public_html
ln -sfn app/public public_html
ls -la public_html   # harus → app/public
```

---

### Git pull minta username/password

Gunakan **Personal Access Token** GitHub sebagai password, atau setup SSH key di server.

---

## Checklist Cepat Setiap Deploy

```
[ ] Push kode dari lokal ke GitHub
[ ] npm run build (jika ada perubahan CSS/JS)
[ ] Commit public/build/ (jika ada perubahan frontend)
[ ] SSH ke server
[ ] bash deploy/hostinger-update.sh
[ ] Cek website di browser
```

---

## Perintah Artisan — Referensi Cepat

Semua perintah di bawah **wajib** pakai PHP 8.4:

```bash
export PATH="/opt/alt/php84/usr/bin:$PATH"
cd ~/domains/masjid.nusasuite.id/app

# Cache
php artisan config:clear && php artisan config:cache
php artisan route:clear  && php artisan route:cache
php artisan view:clear   && php artisan view:cache

# Maintenance
php artisan down    # mode maintenance ON
php artisan up      # mode maintenance OFF

# Debug (hanya sementara, APP_DEBUG harus false di production)
php artisan optimize:clear
```

---

## Ringkasan: Yang BENAR vs SALAH

| ❌ Salah | ✅ Benar |
|---------|---------|
| `php artisan migrate` | `export PATH="/opt/alt/php84/usr/bin:$PATH"` lalu `php artisan migrate` |
| `php $(which composer) install` | `composer install --no-dev --optimize-autoloader` |
| Copy manual folder ke `public_html` | Symlink: `public_html → app/public` |
| Pull tanpa cek `public/build/` | Pastikan CSS/JS ada setelah pull |
| Cron pakai `php artisan` | Cron pakai `/opt/alt/php84/usr/bin/php artisan` |

---

*Terakhir diperbarui: Agustus 2026 — MasjidNusa by natech.id*
