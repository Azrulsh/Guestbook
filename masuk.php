<?php

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/html.php';
require_once __DIR__ . '/validation.php';

try {
    startCsrfSession();
    $csrfToken = csrfToken();
    session_write_close();
} catch (Exception $exception) {
    error_log('masuk.php: ' . $exception->getMessage());
    http_response_code(500);
    exit('Borang tidak dapat dimuatkan. Sila cuba lagi kemudian.');
}

$year = (new DateTimeImmutable('now', new DateTimeZone('Asia/Kuala_Lumpur')))->format('Y');
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Tinggalkan ucapan, pesanan atau cerita ringkas dalam buku pelawat kita.">
    <meta name="theme-color" content="#e4cbff">
    <title>Tulis catatan — Buku Pelawat</title>
    <link rel="stylesheet" href="assets/css/site.css">
    <link rel="stylesheet" href="assets/css/entry.css">
    <script src="assets/js/entry.js" defer></script>
</head>
<body>
    <a class="skip-link" href="#kandungan">Langkau ke kandungan</a>

    <div class="page-shell">
        <header class="site-header">
            <a class="brand" href="index.php" aria-label="Buku Pelawat — halaman utama">
                <span class="brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 5.5C9 3.7 5.8 3.6 3 4.8v14c2.8-1.2 6-1.1 9 .7 3-1.8 6.2-1.9 9-.7v-14c-2.8-1.2-6-1.1-9 .7Z"/>
                        <path d="M12 5.5v14M6 8l3 .5M6 11l3 .5m6-3 3-.5m-3 3.5 3-.5"/>
                    </svg>
                </span>
                <span>Buku<span class="brand-light">Pelawat</span><span class="brand-period">.</span></span>
            </a>
            <nav class="main-nav" aria-label="Navigasi utama">
                <a class="nav-link" href="papar.php">Lihat buku pelawat</a>
            </nav>
        </header>

        <main class="entry-layout" id="kandungan" tabindex="-1">
            <section class="entry-intro" aria-labelledby="entry-title">
                <p class="eyebrow"><span aria-hidden="true"></span> SEHELAI CERITA BAHARU</p>
                <h1 id="entry-title">Sedikit kata,<br><span>banyak makna.</span></h1>
                <p class="entry-description">Ruang ini milik setiap yang singgah. Perkenalkan diri dan tinggalkan satu pesanan untuk diingati.</p>

                <div class="writing-note">
                    <svg class="note-flower" aria-hidden="true" viewBox="0 0 96 96" fill="none" stroke="currentColor" stroke-width="1.4">
                        <path d="M48 36c-24-35-38-10-15 9-40-1-25 28 4 15-16 36 17 38 20 8 19 30 41 8 14-13 36-13 14-38-10-17C71 2 38 4 48 36Z"/>
                        <circle cx="49" cy="49" r="8"/>
                    </svg>
                    <p>Tak perlu panjang.<br>Asalkan dari hati.</p>
                    <span>Satu ucapan terima kasih pun sudah bermakna.</span>
                </div>
            </section>

            <?php
            $entryForm = [
                'action' => 'isikan.php',
                'title' => 'Catatan baharu',
                'submitLabel' => 'Simpan catatan',
                'note' => 'Catatan anda akan muncul dalam buku pelawat.',
                'csrfToken' => $csrfToken,
                'id' => null,
                'values' => ['nama' => '', 'email' => '', 'komen' => ''],
            ];
            require __DIR__ . '/entry-form.php';
            ?>
        </main>

        <footer class="site-footer">
            <p>Ruang untuk kata-kata yang ingin diingati.</p>
            <span>Buku Pelawat <span aria-hidden="true">&middot;</span> <?= escapeHtml($year) ?></span>
        </footer>
    </div>
</body>
</html>
