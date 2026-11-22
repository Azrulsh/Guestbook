# Buku Pelawat

Aplikasi buku pelawat berasaskan PHP dan MySQL/MariaDB untuk kegunaan peribadi. Pengunjung boleh menambah, membaca, mengemas kini dan memadam catatan melalui antaramuka Bahasa Melayu dengan tema **pale lilac `#e4cbff`**.

Projek menggunakan PHP, CSS dan JavaScript biasa tanpa framework, Composer, npm atau proses build. Semua aset antaramuka berada dalam repository ini.

## Fungsi

- Empat halaman: utama, tambah catatan, senarai catatan dan kemas kini catatan.
- Borang nama, email dan catatan dengan validasi pelayar serta validasi pelayan.
- Carian mengikut nama, email atau catatan, serta susunan terkini/terlama berdasarkan ID.
- Pengesahan sebelum padam dan pencegahan penghantaran berulang melalui JavaScript.
- Paparan responsif, label borang, pautan langkau kandungan dan gaya fokus papan kekunci.
- Tarikh dan masa menggunakan zon `Asia/Kuala_Lumpur`.

**Tiada sistem login atau pemilikan rekod.** Sesiapa yang boleh mengakses aplikasi boleh membaca, menambah, mengubah dan memadam semua catatan. Nama, email dan catatan dipaparkan dalam senarai. Ini ialah tingkah laku semasa projek peribadi ini.

## Keperluan

- PHP dengan extension `mysqli`, `mysqlnd` dan `mbstring`, serta sokongan sesi. `mysqlnd` diperlukan untuk `mysqli_stmt_get_result()`.
- MySQL atau MariaDB.
- Apache melalui XAMPP, atau pelayan pembangunan terbina dalam PHP.
- Pelayar moden untuk fungsi CSS dan JavaScript.

PHP CLI yang tersedia semasa dokumentasi disediakan ialah **8.4.27**. Gunakan **PHP 8.4 atau lebih baharu untuk keseluruhan suite ujian** kerana ujian HTTP menggunakan `Dom\HTMLDocument`; extension `dom` juga diperlukan.

## Jalankan melalui XAMPP

1. Letakkan projek dalam `C:\xampp\htdocs\Guestbook`.
2. Buka XAMPP Control Panel dan hidupkan **Apache** serta **MySQL**.
3. Buka [phpMyAdmin](http://localhost/phpmyadmin/) dan cipta database bernama `guestbook`.
4. Pilih database tersebut, buka tab **Import**, kemudian import [database/guestbook.sql](database/guestbook.sql).
5. Buka [http://localhost/Guestbook/](http://localhost/Guestbook/).

Fail SQL menyediakan jadual `book` tanpa rekod contoh. Ia tidak mencipta atau memilih database secara automatik, jadi pilih database sebelum import. Gunakan database baharu yang belum mempunyai jadual `book` untuk pemasangan awal.

Jika Apache menggunakan port selain `80`, sesuaikan URL, contohnya `http://localhost:8080/Guestbook/`. Port Apache berbeza daripada port database.

Buka aplikasi melalui HTTP. Membuka fail PHP secara terus atau menggunakan pelayan statik seperti Live Server tidak menjalankan kod PHP.

## Konfigurasi database

[database.php](database.php) membaca environment variable berikut melalui `getenv()`. Jika pemboleh ubah tidak ditetapkan, nilai lalai digunakan.

| Environment variable | Nilai lalai | Kegunaan |
| --- | --- | --- |
| `GUESTBOOK_DB_HOST` | `localhost` | Host database |
| `GUESTBOOK_DB_USER` | `root` | Pengguna database |
| `GUESTBOOK_DB_PASSWORD` | Kosong | Kata laluan database |
| `GUESTBOOK_DB_NAME` | `guestbook` | Nama database |
| `GUESTBOOK_DB_PORT` | `3306` | Port database, antara `1` hingga `65535` |

Projek **tidak memuatkan fail `.env` secara automatik**. Environment variable mesti tersedia kepada proses PHP yang menjalankan aplikasi. Untuk Apache, tetapkan konfigurasi persekitaran Apache/service yang berkaitan dan mulakan semula Apache selepas perubahan.

Sebagai alternatif untuk pembangunan setempat, jalankan arahan PowerShell berikut dari folder projek:

```powershell
Set-Location C:\xampp\htdocs\Guestbook
& C:\xampp\php\php.exe -S 127.0.0.1:8000 -t .
```

Buka [http://127.0.0.1:8000/](http://127.0.0.1:8000/). MySQL masih perlu berjalan; Apache tidak diperlukan untuk pilihan ini. Hentikan pelayan dengan `Ctrl+C`.

Untuk menggunakan konfigurasi lain, tetapkan pemboleh ubah sebelum memulakan pelayan tersebut. Contohnya, jika MySQL menggunakan port `3307`:

```powershell
$env:GUESTBOOK_DB_PORT = '3307'
& C:\xampp\php\php.exe -S 127.0.0.1:8000 -t .
```

Pemboleh ubah `$env:` dalam terminal ini diwarisi oleh proses PHP yang dimulakan daripadanya. Ia tidak mengubah persekitaran Apache yang sudah berjalan. Nilai `root` tanpa kata laluan ialah lalai pembangunan setempat dalam kod.

## Penggunaan

| Halaman | Tujuan |
| --- | --- |
| `index.php` | Halaman utama dan pautan ke borang serta senarai |
| `masuk.php` | Borang catatan baharu |
| `papar.php` | Senarai, carian, susunan dan tindakan rekod |
| `ubah.php?id=ID` | Borang kemas kini untuk ID rekod yang sah |

Pilih **Tinggalkan catatan**, isi semua medan dan tekan **Simpan catatan**. Selepas simpan, aplikasi membawa pengguna ke senarai. Dari senarai, pilih tindakan ubah atau padam pada rekod berkenaan.

Butang **Semula** mengosongkan borang tambah. Pada borang kemas kini, ia mengembalikan nilai asal yang dimuatkan ketika halaman dibuka.

Carian, pertukaran susunan, pengesahan padam dan maklum balas borang tambahan memerlukan JavaScript. Tanpa JavaScript, senarai masih dipaparkan mengikut ID menurun dan borang tambah, kemas kini serta padam masih dihantar melalui HTTP biasa dengan validasi pelayan.

## Data dan validasi

Jadual `book` ditakrifkan dalam [database/guestbook.sql](database/guestbook.sql):

| Medan | Jenis | Peraturan aplikasi |
| --- | --- | --- |
| `id` | `INT`, primary key, auto increment | ID positif, maksimum `2147483647` |
| `nama` | `VARCHAR(50)` | Wajib, maksimum 50 aksara |
| `email` | `VARCHAR(30)` | Wajib, format email sah, maksimum 30 aksara |
| `tarikh` | `VARCHAR(10)` | Ditetapkan pelayan dalam format `d-m-Y` |
| `masa` | `VARCHAR(8)` | Ditetapkan pelayan dalam format `H:i:s` |
| `komen` | `TEXT` | Wajib, maksimum 65,535 bait UTF-8 pada peringkat validasi |

Validasi pelayan dikongsi melalui [validation.php](validation.php). Input mesti berupa teks UTF-8 yang sah; ruang kosong di awal dan akhir dibuang, dan aksara kawalan yang tidak dibenarkan ditolak. Baris baharu dalam catatan dikekalkan.

Tarikh dan masa ditetapkan semasa tambah **dan dikemas kini semula apabila rekod diubah**. Medan ini bukan rekod tarikh penciptaan yang kekal. Susunan terkini/terlama menggunakan ID, bukan tarikh kemas kini.

### Had encoding semasa

Sambungan database menggunakan `utf8mb4`, tetapi jadual dalam fail SQL masih menggunakan **`latin1`**. Oleh itu, storan belum menyokong semua aksara Unicode. Emoji dan sesetengah tulisan seperti aksara Cina boleh ditolak dengan HTTP `422`.

Sokongan Unicode penuh memerlukan migrasi charset jadual dan semakan data sedia ada. Migrasi tersebut belum disertakan dalam projek ini.

## Perlindungan dan pengendalian ralat

- Query yang menerima input pengguna menggunakan prepared statement dan parameter terikat.
- Output pengguna di-escape melalui [html.php](html.php) sebelum dipaparkan dalam HTML. Kandungan seperti tag HTML dipaparkan sebagai teks.
- Tambah, kemas kini dan padam hanya menerima `POST` dengan token CSRF yang sah bagi sesi pengguna.
- Cookie sesi menggunakan `HttpOnly`, `SameSite=Lax` dan `Secure` apabila permintaan dikesan melalui HTTPS.
- Sambungan database menetapkan timeout dan SQL strict mode untuk mengelakkan pemotongan data secara senyap.
- Ralat sambungan/query dikendalikan dengan mesej awam yang ringkas dan diagnostik dalam log pelayan.
- Operasi yang berjaya menggunakan redirect `303` ke `papar.php`.

| Status HTTP | Keadaan utama |
| --- | --- |
| `400` | ID rekod tidak sah |
| `403` | Token CSRF tiada atau tidak sah |
| `404` | Rekod tidak ditemui |
| `405` | Kaedah selain `POST` pada endpoint tambah, kemas kini atau padam |
| `422` | Input tidak sah atau aksara tidak disokong oleh encoding database |
| `500` | Kegagalan dalaman, sesi atau database |

Perlindungan CSRF tidak memberikan kawalan akses atau hak milik rekod. Projek masih menggunakan model tanpa login seperti diterangkan di atas. Senarai juga memuatkan semua rekod sekali gus; pagination pelayan belum dilaksanakan.

## Struktur kod

| Fail / direktori | Tanggungjawab |
| --- | --- |
| `index.php`, `masuk.php`, `papar.php`, `ubah.php` | Halaman antaramuka |
| `entry-form.php` | Template borang yang dikongsi oleh halaman tambah dan kemas kini |
| `isikan.php` | Pengendali tambah catatan |
| `kemaskini.php` | Pengendali kemas kini catatan |
| `buang.php` | Pengendali padam catatan |
| `database.php` | Konfigurasi dan pembinaan sambungan database |
| `dbase.php` | Bootstrap sambungan untuk halaman/pengendali |
| `validation.php` | Validasi ID dan kandungan catatan |
| `csrf.php` | Sesi serta penjanaan dan semakan token CSRF |
| `html.php` | Helper escaping HTML |
| `assets/css/` | Gaya bersama dan gaya halaman utama, borang serta senarai |
| `assets/js/` | Jam halaman utama, interaksi borang dan interaksi senarai |
| `database/guestbook.sql` | Skema awal jadual `book` |
| `tests/` | Skrip ujian PHP melalui CLI |

## Jalankan ujian

Jalankan dari folder projek dalam PowerShell:

```powershell
& C:\xampp\php\php.exe tests/html_escape_test.php
& C:\xampp\php\php.exe tests/validation_test.php
& C:\xampp\php\php.exe tests/csrf_test.php
& C:\xampp\php\php.exe tests/database_test.php
& C:\xampp\php\php.exe tests/request_http_test.php
```

Jika `php` sudah berada dalam `PATH`, laluan penuh executable boleh diganti dengan `php`.

| Ujian | Liputan |
| --- | --- |
| `html_escape_test.php` | Escaping HTML, petikan, input UTF-8 dan baris baharu |
| `validation_test.php` | Medan wajib, format, panjang, jenis input, aksara kawalan dan ID |
| `csrf_test.php` | Penjanaan, penggunaan semula dan penolakan token CSRF |
| `database_test.php` | Konfigurasi, sambungan, charset, SQL mode dan sanitasi ralat sambungan |
| `request_http_test.php` | Status HTTP, CSRF, borang, escaping paparan, operasi rekod dan ralat database |

Tiga ujian pertama tidak memerlukan database. Ujian sesi memerlukan `session.save_path` yang boleh ditulis.

Untuk liputan database penuh, sediakan konfigurasi pembangunan lalai: `localhost:3306`, pengguna `root`, kata laluan kosong dan database `guestbook`. `database_test.php` menetapkan semula environment variable dalam proses ujian kepada nilai lalai sebelum menguji sambungan. Ujian HTTP juga mempunyai semakan ketersediaan database menggunakan akaun lalai; jalankan suite ini dalam persekitaran lalai tersebut tanpa override `GUESTBOOK_DB_*`.

Ujian HTTP memulakan pelayan PHP sementara di loopback, jadi `proc_open`, sambungan rangkaian setempat, `allow_url_fopen` dan direktori sementara yang boleh ditulis diperlukan. Kes operasi rekod menggunakan **jadual sementara** yang membayangi `book`, tanpa mengubah rekod kekal. Akaun ujian perlu mempunyai hak `CREATE TEMPORARY TABLES`.

Jika database lalai tidak tersedia, sebahagian ujian integrasi HTTP memaparkan `SKIP`. Output ini bermaksud bahagian tersebut belum diuji. Sesetengah ujian sengaja mencetuskan kegagalan database, jadi mesej diagnostik berkaitan boleh muncul bersama keputusan `PASS`.

## Penyelesaian masalah

| Masalah | Semakan |
| --- | --- |
| `localhost` tidak boleh dibuka | Pastikan Apache berjalan dan port dalam URL betul |
| Ralat database / HTTP `500` | Pastikan MySQL berjalan, database dan jadual telah diimport, serta konfigurasi sepadan; semak log PHP/Apache |
| HTTP `403` selepas hantar borang | Buka semula borang, benarkan cookie dan pastikan storan sesi PHP boleh ditulis |
| HTTP `422` untuk email | Semak format dan had 30 aksara mengikut skema semasa |
| HTTP `422` untuk emoji atau aksara tertentu | Semak had charset `latin1` yang diterangkan di atas |
| Gaya lama masih kelihatan | Muat semula tanpa cache dengan `Ctrl+F5` |
| Arahan `php` tidak dikenali | Gunakan laluan penuh `C:\xampp\php\php.exe` |
