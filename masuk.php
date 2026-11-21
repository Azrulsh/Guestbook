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

            <div class="entry-card">
                <div class="form-heading">
                    <span class="form-heading-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m15 5 4 4M4 20l4.5-1 12-12a2.8 2.8 0 0 0-4-4l-12 12L4 20Z"/>
                        </svg>
                    </span>
                    <div>
                        <h2 id="form-title">Catatan baharu</h2>
                        <p id="form-instructions">Semua medan perlu diisi.</p>
                    </div>
                </div>

                <form id="entry-form" class="entry-form" method="post" action="isikan.php" aria-labelledby="form-title" aria-describedby="form-instructions" accept-charset="UTF-8">
                    <input type="hidden" name="csrf_token" value="<?= escapeHtml($csrfToken) ?>">

                    <div class="form-field">
                        <label for="nama">Nama</label>
                        <input class="field-control" type="text" id="nama" name="nama" placeholder="Nama yang ingin dikenali" autocomplete="name" required maxlength="<?= BOOK_NAME_MAX_LENGTH ?>" aria-describedby="nama-hint nama-error">
                        <p class="field-hint" id="nama-hint">Maksimum <?= BOOK_NAME_MAX_LENGTH ?> aksara.</p>
                        <p class="field-error" id="nama-error" hidden></p>
                    </div>

                    <div class="form-field">
                        <label for="email">Email</label>
                        <input class="field-control" type="email" id="email" name="email" placeholder="nama@contoh.com" autocomplete="email" inputmode="email" autocapitalize="none" spellcheck="false" required maxlength="<?= BOOK_EMAIL_MAX_LENGTH ?>" aria-describedby="email-hint email-error">
                        <p class="field-hint" id="email-hint">Email ini turut dipaparkan bersama catatan. Maksimum <?= BOOK_EMAIL_MAX_LENGTH ?> aksara.</p>
                        <p class="field-error" id="email-error" hidden></p>
                    </div>

                    <div class="form-field">
                        <label for="komen">Catatan</label>
                        <textarea class="field-control" id="komen" name="komen" rows="6" placeholder="Apa yang ingin anda kongsikan hari ini?" required data-max-bytes="<?= BOOK_COMMENT_MAX_BYTES ?>" aria-describedby="komen-hint komen-error"></textarea>
                        <div class="field-meta">
                            <p class="field-hint" id="komen-hint">Ucapan, pesanan atau sekadar kata hai.</p>
                            <span class="character-count" id="comment-count" hidden>0 aksara</span>
                        </div>
                        <p class="field-error" id="komen-error" hidden></p>
                    </div>

                    <div class="form-actions">
                        <div class="form-action-buttons">
                            <button class="button button-primary submit-entry" type="submit" id="submit-entry">
                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M7 3h10l4 4v14H3V3h4Zm0 0v6h10V3M7 21v-7h10v7"/>
                                </svg>
                                <span id="submit-label">Simpan catatan</span>
                            </button>
                            <button class="button button-secondary reset-entry" type="reset" id="reset-entry">Semula</button>
                        </div>
                        <p class="publish-note">Catatan anda akan muncul dalam buku pelawat.</p>
                    </div>
                </form>
            </div>
        </main>

        <footer class="site-footer">
            <p>Ruang untuk kata-kata yang ingin diingati.</p>
            <span>Buku Pelawat <span aria-hidden="true">&middot;</span> <?= escapeHtml($year) ?></span>
        </footer>
    </div>
</body>
</html>
