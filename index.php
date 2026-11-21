<?php

require_once __DIR__ . '/html.php';

$now = new DateTimeImmutable('now', new DateTimeZone('Asia/Kuala_Lumpur'));
$months = [1 => 'Januari', 'Februari', 'Mac', 'April', 'Mei', 'Jun', 'Julai', 'Ogos', 'September', 'Oktober', 'November', 'Disember'];
$displayDate = $now->format('j') . ' ' . $months[(int) $now->format('n')] . ' ' . $now->format('Y');
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Setiap kunjungan ada ceritanya. Tinggalkan pesanan dan baca catatan dalam buku pelawat kita.">
    <meta name="theme-color" content="#e4cbff">
    <title>Buku Pelawat — Setiap kunjungan ada ceritanya</title>
    <link rel="stylesheet" href="assets/css/home.css">
    <script src="assets/js/home.js" defer></script>
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
                <a class="nav-link" href="index.php" aria-current="page">Utama</a>
            </nav>
        </header>

        <main id="kandungan" tabindex="-1">
            <section class="hero" aria-labelledby="hero-title">
                <div class="hero-content">
                    <p class="eyebrow"><span aria-hidden="true"></span> RUANG KECIL UNTUK KENANGAN</p>
                    <h1 id="hero-title">Setiap kunjungan,<br><span>ada ceritanya.</span></h1>
                    <p class="hero-description">Ada pesanan, ucapan atau cerita untuk dikongsi? Tinggalkan sedikit kata. Jadikan kunjungan anda sebahagian daripada buku ini.</p>

                    <div class="hero-actions">
                        <a class="button button-primary" href="masuk.php">
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m15 5 4 4M4 20l4.5-1 12-12a2.8 2.8 0 0 0-4-4l-12 12L4 20Z"/>
                            </svg>
                            Tinggalkan catatan
                        </a>
                        <a class="button button-secondary" href="papar.php">Lihat buku pelawat</a>
                    </div>

                    <div class="visit-date">
                        <span class="date-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="4" y="5" width="16" height="16" rx="3"/>
                                <path d="M8 3v4m8-4v4M4 11h16m-12 4h2m4 0h2"/>
                            </svg>
                        </span>
                        <div>
                            <span class="date-label">HARI INI DI BUKU KITA</span>
                            <div class="date-value">
                                <time id="visit-date" datetime="<?= escapeHtml($now->format('Y-m-d')) ?>"><?= escapeHtml($displayDate) ?></time>
                                <span class="date-separator" aria-hidden="true">/</span>
                                <span class="time-group"><time id="visit-time" datetime="<?= escapeHtml($now->format(DateTimeInterface::ATOM)) ?>"><?= escapeHtml($now->format('H:i')) ?></time> <abbr title="Waktu Malaysia">MYT</abbr></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="book-scene" aria-hidden="true">
                    <span class="scene-caption">CATATAN KECIL. KENANGAN BESAR.</span>
                    <svg class="scene-star star-one" viewBox="0 0 40 40" fill="none"><path d="M20 2c0 12-6 18-18 18 12 0 18 6 18 18 0-12 6-18 18-18C26 20 20 14 20 2Z" stroke="currentColor" stroke-width="1.5"/></svg>
                    <svg class="scene-star star-two" viewBox="0 0 40 40" fill="none"><path d="M20 2c0 12-6 18-18 18 12 0 18 6 18 18 0-12 6-18 18-18C26 20 20 14 20 2Z" stroke="currentColor" stroke-width="1.5"/></svg>
                    <div class="scene-orbit"></div>

                    <div class="illustrated-book">
                        <div class="book-pages"></div>
                        <div class="book-cover">
                            <div class="book-binding"></div>
                            <div class="book-cover-content">
                                <span class="book-edition">BUKU PELAWAT</span>
                                <div class="book-flower">
                                    <svg viewBox="0 0 96 96" fill="none" stroke="currentColor" stroke-width="1.4">
                                        <path d="M48 36c-24-35-38-10-15 9-40-1-25 28 4 15-16 36 17 38 20 8 19 30 41 8 14-13 36-13 14-38-10-17C71 2 38 4 48 36Z"/>
                                        <circle cx="49" cy="49" r="8"/>
                                    </svg>
                                </div>
                                <p class="book-title">Tempat cerita<br>bertemu.</p>
                                <span class="book-rule"></span>
                                <p class="book-subtitle">Terima kasih<br>kerana singgah.</p>
                                <span class="book-bottom">SEBUAH RUANG UNTUK KITA</span>
                            </div>
                        </div>
                    </div>

                    <div class="scene-note">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20.5 5.5a5 5 0 0 0-7 0L12 7l-1.5-1.5a5 5 0 0 0-7 7L12 21l8.5-8.5a5 5 0 0 0 0-7Z"/></svg>
                        Satu kunjungan, satu kenangan.
                    </div>
                </div>
            </section>

            <section class="welcome-guide" aria-labelledby="guide-title">
                <div class="guide-intro">
                    <p class="eyebrow">MUDAH &amp; BERMAKNA</p>
                    <h2 id="guide-title">Sedikit kata.<br>Banyak makna.</h2>
                </div>
                <div class="guide-item">
                    <span class="guide-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="3.5"/><path d="M5 21v-2a7 7 0 0 1 14 0v2"/></svg>
                    </span>
                    <h3>Mulakan dengan nama</h3>
                    <p>Perkenalkan diri supaya kami tahu siapa yang singgah.</p>
                </div>
                <div class="guide-item">
                    <span class="guide-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.5 8.5 0 0 1-8.5 8.5H4l-2 2v-10a9.5 9.5 0 0 1 19-.5Z"/><path d="M7 10h9M7 14h6"/></svg>
                    </span>
                    <h3>Tulis dari hati</h3>
                    <p>Satu ucapan, cerita ringkas atau sekadar kata terima kasih.</p>
                </div>
                <div class="guide-item">
                    <span class="guide-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5c-3-2-6-2-9-1v15c3-1 6-1 9 1 3-2 6-2 9-1V4c-3-1-6-1-9 1Zm0 0v15"/></svg>
                    </span>
                    <h3>Selak cerita yang lain</h3>
                    <p>Kenali sedikit cerita daripada mereka yang sudah berkunjung.</p>
                </div>
            </section>
        </main>

        <footer class="site-footer">
            <p>Ruang untuk kata-kata yang ingin diingati.</p>
            <span>Buku Pelawat <span aria-hidden="true">&middot;</span> <?= escapeHtml($now->format('Y')) ?></span>
        </footer>
    </div>
</body>
</html>
