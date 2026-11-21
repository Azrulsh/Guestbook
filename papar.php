<?php

require_once __DIR__ . '/html.php';
require_once __DIR__ . '/csrf.php';

$entries = [];
$hasLoadError = false;

try {
    startCsrfSession();
    $csrfToken = csrfToken();
    session_write_close();

    require_once __DIR__ . '/dbase.php';

    $result = mysqli_query($conn, 'SELECT id, nama, email, tarikh, masa, komen FROM book ORDER BY id DESC');

    if ($result === false) {
        throw new RuntimeException(mysqli_error($conn));
    }

    $entries = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_free_result($result);
} catch (Exception $exception) {
    error_log('papar.php: ' . $exception->getMessage());
    http_response_code(500);
    $hasLoadError = true;
}

$entryCount = count($entries);
$year = (new DateTimeImmutable('now', new DateTimeZone('Asia/Kuala_Lumpur')))->format('Y');
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Selak ucapan, pesanan dan cerita daripada pelawat yang sudah singgah.">
    <meta name="theme-color" content="#e4cbff">
    <title>Catatan pelawat — Buku Pelawat</title>
    <link rel="stylesheet" href="assets/css/site.css">
    <link rel="stylesheet" href="assets/css/list.css">
    <script src="assets/js/list.js" defer></script>
</head>
<body class="listing-page">
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
        </header>

        <main class="listing-main" id="kandungan" tabindex="-1">
            <section class="listing-intro" aria-labelledby="page-title">
                <div>
                    <p class="eyebrow"><span aria-hidden="true"></span> BUKU PELAWAT KITA</p>
                    <h1 id="page-title">Cerita yang <span>ditinggalkan.</span></h1>
                    <p class="listing-description">Setiap pesanan ada maknanya. Selak cerita daripada mereka yang sudah singgah.</p>
                </div>
                <a class="button button-primary write-entry" href="masuk.php">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="m15 5 4 4M4 20l4.5-1 12-12a2.8 2.8 0 0 0-4-4l-12 12L4 20Z"/></svg>
                    Tulis catatan
                </a>
            </section>

            <?php if ($hasLoadError): ?>
                <section class="empty-state error-state" aria-labelledby="load-error-title">
                    <span class="state-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v6m0 4h.01"/></svg>
                    </span>
                    <h2 id="load-error-title">Catatan belum dapat dimuatkan.</h2>
                    <p>Sila cuba lagi sebentar nanti. Jika masalah berterusan, kembali semula kemudian.</p>
                    <a class="button button-secondary" href="papar.php">Cuba lagi</a>
                </section>
            <?php elseif ($entries === []): ?>
                <section class="empty-state" aria-labelledby="empty-title">
                    <span class="state-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5c-3-2-6-2-9-1v15c3-1 6-1 9 1 3-2 6-2 9-1V4c-3-1-6-1-9 1Zm0 0v15"/></svg>
                    </span>
                    <h2 id="empty-title">Halaman pertama masih menanti.</h2>
                    <p>Belum ada catatan di sini. Mulakan dengan satu ucapan melalui butang “Tulis catatan”.</p>
                </section>
            <?php else: ?>
                <div class="list-toolbar" id="list-controls" hidden>
                    <div class="search-field">
                        <label class="visually-hidden" for="entry-search">Cari mengikut nama, email atau catatan</label>
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></svg>
                        <input type="search" id="entry-search" placeholder="Cari nama, email atau catatan…" autocomplete="off" aria-controls="entries-list">
                    </div>
                    <div class="sort-field">
                        <label class="visually-hidden" for="entry-sort">Susunan catatan</label>
                        <select id="entry-sort" aria-controls="entries-list">
                            <option value="newest">Terbaharu dahulu</option>
                            <option value="oldest">Terlama dahulu</option>
                        </select>
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="m7 10 5 5 5-5"/></svg>
                    </div>
                </div>

                <p class="list-summary" id="list-summary" role="status" aria-live="polite" aria-atomic="true"><?= $entryCount ?> catatan</p>

                <ul class="records-grid" id="entries-list" aria-label="Catatan pelawat">
                    <?php foreach ($entries as $entry): ?>
                        <?php
                        $entryId = (int) $entry['id'];
                        $initial = mb_strtoupper(mb_substr(trim($entry['nama']), 0, 1, 'UTF-8'), 'UTF-8');
                        ?>
                        <li class="record-card" data-entry-id="<?= $entryId ?>">
                            <article aria-labelledby="record-title-<?= $entryId ?>">
                                <header class="record-heading">
                                    <span class="record-avatar" aria-hidden="true"><?= escapeHtml($initial !== '' ? $initial : '?') ?></span>
                                    <div class="record-person">
                                        <h2 class="record-name" id="record-title-<?= $entryId ?>"><bdi><?= escapeHtml($entry['nama']) ?></bdi></h2>
                                        <p class="record-email"><bdi><?= escapeHtml($entry['email']) ?></bdi></p>
                                    </div>
                                </header>
                                <div class="record-message" dir="auto"><?= nl2br(escapeHtml($entry['komen'])) ?></div>
                                <footer class="record-footer">
                                    <p class="record-date"><span class="visually-hidden">Tarikh dan masa catatan: </span><?= escapeHtml($entry['tarikh']) ?> <span class="date-divider" aria-hidden="true">&middot;</span> <?= escapeHtml(substr($entry['masa'], 0, 5)) ?> MYT</p>
                                    <div class="record-actions">
                                        <a class="record-action edit-action" href="ubah.php?id=<?= $entryId ?>" aria-label="Ubah catatan oleh <?= escapeHtml($entry['nama']) ?>">
                                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="m15 5 4 4M4 20l4.5-1 12-12a2.8 2.8 0 0 0-4-4l-12 12L4 20Z"/></svg>
                                            Ubah
                                        </a>
                                        <form class="delete-entry" method="post" action="buang.php">
                                            <input type="hidden" name="id" value="<?= $entryId ?>">
                                            <input type="hidden" name="csrf_token" value="<?= escapeHtml($csrfToken) ?>">
                                            <button class="record-action delete-action" type="submit" aria-label="Padam catatan oleh <?= escapeHtml($entry['nama']) ?>">
                                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M9 6V3h6v3M5 6l1 15h12l1-15M10 10v7m4-7v7"/></svg>
                                                Padam
                                            </button>
                                        </form>
                                    </div>
                                </footer>
                            </article>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <section class="empty-state no-results" id="no-results" aria-labelledby="no-results-title" hidden>
                    <span class="state-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></svg>
                    </span>
                    <h2 id="no-results-title">Tiada catatan yang sepadan.</h2>
                    <p>Cuba nama, email atau kata kunci lain.</p>
                </section>
            <?php endif; ?>
        </main>

        <footer class="site-footer">
            <p>Ruang untuk kata-kata yang ingin diingati.</p>
            <span>Buku Pelawat <span aria-hidden="true">&middot;</span> <?= escapeHtml($year) ?></span>
        </footer>
    </div>
</body>
</html>
