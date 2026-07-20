# Panduan Penyebaran (Deployment Guide) ke Railway

Dokumen ini menjelaskan langkah-langkah untuk menyebarkan aplikasi Laravel **Toko Tika** ke [Railway](https://railway.app/).

Aplikasi ini menggunakan **Nixpacks** untuk membangun dan menjalankan aplikasi secara otomatis (mendeteksi PHP, Composer, Node.js, dan NPM).

---

## 1. Konfigurasi Awal di Railway

Setelah Anda membuat proyek baru di Railway dan menghubungkannya dengan repositori GitHub Anda, Anda perlu menambahkan beberapa **Environment Variables** penting di tab **Variables** pada dasbor Railway Anda.

### Variabel Lingkungan Wajib (Required Variables)

| Nama Variabel | Nilai (Value) | Keterangan |
| :--- | :--- | :--- |
| `APP_NAME` | `Toko Tika` | Nama aplikasi Anda |
| `APP_ENV` | `production` | Lingkungan aplikasi (produksi) |
| `APP_DEBUG` | `false` | Matikan mode debug demi keamanan |
| `APP_KEY` | `base64:xxxx...` | Jalankan `php artisan key:generate --show` di lokal dan salin hasilnya di sini |
| `APP_URL` | `https://toko-tika-production.up.railway.app` | URL domain Railway Anda |
| `NIXPACKS_PHP_ROOT_DIR` | `/app/public` | **SANGAT PENTING:** Mengarahkan Nginx untuk melayani folder `public/` Laravel |

---

## 2. Konfigurasi Database (MySQL / PostgreSQL)

Karena Railway menggunakan sistem file sementara (*ephemeral*), database SQLite lokal (`database.sqlite`) akan terhapus setiap kali aplikasi melakukan deploy ulang atau restart.

Oleh karena itu, **Anda wajib menggunakan database MySQL atau PostgreSQL** yang disediakan oleh Railway:

1. Di dasbor Railway Anda, klik **+ Add** -> **Database** -> **Add MySQL** (atau PostgreSQL).
2. Setelah database terbuat, Railway akan otomatis mengisi variabel database atau Anda dapat menyalin nilainya ke variabel berikut:

| Nama Variabel | Nilai (Value) | Keterangan |
| :--- | :--- | :--- |
| `DB_CONNECTION` | `mysql` | Driver database |
| `DB_HOST` | `${{MYSQLPORT}}` atau host dari Railway | Host database |
| `DB_PORT` | `3306` | Port database |
| `DB_DATABASE` | `${{MYSQLDATABASE}}` | Nama database |
| `DB_USERNAME` | `${{MYSQLUSER}}` | Username database |
| `DB_PASSWORD` | `${{MYSQLPASSWORD}}` | Password database |

---

## 3. Otomatisasi Migrasi Database

Aplikasi ini sudah dikonfigurasi dengan file `railway.json` yang akan menjalankan perintah:
```bash
php artisan migrate --force
```
Secara otomatis sebelum kontainer web Anda aktif (`preDeployCommand`). Jadi Anda tidak perlu menjalankan migrasi secara manual setiap ada perubahan struktur database.

---

## 4. Kompilasi Aset CSS & JS (Vite)

Karena folder `public/build` sudah dimasukkan ke dalam `.gitignore`, Railway akan melakukan instalasi paket NPM (`npm install`) dan mengompilasi aset (`npm run build`) secara otomatis saat build pipeline berjalan. Ini membuat ukuran repositori GitHub Anda tetap kecil dan rapi.

---

## 5. Media Upload (Gambar Produk & Avatar)

> [!WARNING]
> Karena kontainer Railway bersifat ephemeral, semua file gambar produk atau bukti transfer yang diunggah ke folder lokal `storage/app/public` akan **hilang** jika aplikasi mengalami restart atau redeploy.
> 
> **Rekomendasi Produksi:**
> Untuk jangka panjang di lingkungan produksi, ubah `FILESYSTEM_DISK` ke penyedia penyimpanan eksternal (cloud storage) seperti AWS S3, Cloudflare R2, atau Cloudinary.
