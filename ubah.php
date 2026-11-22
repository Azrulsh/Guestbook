<?php

require_once __DIR__ . '/html.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/validation.php';

$entry = null;
$pageError = null;

try {
    $id = validateRecordId($_GET['id'] ?? null);
} catch (InputValidationException $exception) {
    http_response_code(400);
    $pageError = [
        'title' => 'Pautan catatan tidak sah.',
        'message' => 'Pilih semula catatan daripada buku pelawat untuk mengemas kini.',
    ];
}

if ($pageError === null) {
    try {
        startCsrfSession();
        $csrfToken = csrfToken();
        session_write_close();

        require_once __DIR__ . '/dbase.php';

        $stmt = mysqli_prepare($conn, 'SELECT id, nama, email, komen FROM book WHERE id = ?');
        if ($stmt === false) {
            throw new RuntimeException(mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, 'i', $id);
        if (!mysqli_stmt_execute($stmt)) {
            throw new RuntimeException(mysqli_stmt_error($stmt));
        }

        $result = mysqli_stmt_get_result($stmt);
        if ($result === false) {
            throw new RuntimeException(mysqli_stmt_error($stmt));
        }

        $entry = mysqli_fetch_assoc($result);
        mysqli_free_result($result);
        mysqli_stmt_close($stmt);

        if ($entry === null) {
            http_response_code(404);
            $pageError = [
                'title' => 'Catatan tidak ditemui.',
                'message' => 'Rekod ini mungkin telah dipadam. Kembali ke buku pelawat untuk memilih catatan lain.',
            ];
        } elseif ($entry === false) {
            throw new RuntimeException('Unable to read the guestbook entry.');
        }
    } catch (Exception $exception) {
        error_log('ubah.php: ' . $exception->getMessage());
        http_response_code(500);
        $pageError = [
            'title' => 'Catatan belum dapat dimuatkan.',
            'message' => 'Sila cuba lagi sebentar nanti. Jika masalah berterusan, kembali semula kemudian.',
        ];
    }
}

$year = (new DateTimeImmutable('now', new DateTimeZone('Asia/Kuala_Lumpur')))->format('Y');
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Kemas kini butiran dan pesanan anda dalam buku pelawat.">
    <meta name="theme-color" content="#e4cbff">
    <title>Kemas kini catatan — Buku Pelawat</title>
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
            <?php if ($pageError === null): ?>
                <nav class="main-nav" aria-label="Navigasi utama">
                    <a class="nav-link" href="papar.php">Kembali ke buku pelawat</a>
                </nav>
            <?php endif; ?>
        </header>

        <main class="entry-layout" id="kandungan" tabindex="-1">
            <?php if ($pageError !== null): ?>
                <section class="entry-card entry-error" aria-labelledby="error-title">
                    <span class="form-heading-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v6m0 4h.01"/></svg>
                    </span>
                    <h1 id="error-title"><?= escapeHtml($pageError['title']) ?></h1>
                    <p><?= escapeHtml($pageError['message']) ?></p>
                    <a class="button button-secondary" href="papar.php">Kembali ke buku pelawat</a>
                </section>
            <?php else: ?>
                <section class="entry-intro" aria-labelledby="entry-title">
                    <p class="eyebrow"><span aria-hidden="true"></span> SEMPURNAKAN PESANAN ANDA</p>
                    <h1 id="entry-title">Sentuhan baharu,<br><span>cerita yang sama.</span></h1>
                    <p class="entry-description">Betulkan butiran atau kemaskan pesanan anda. Perubahan hanya disimpan apabila anda menekan “Simpan perubahan”.</p>

                    <div class="writing-note">
                        <svg class="note-flower" aria-hidden="true" viewBox="0 0 96 96" fill="none" stroke="currentColor" stroke-width="1.4">
                            <path d="M48 36c-24-35-38-10-15 9-40-1-25 28 4 15-16 36 17 38 20 8 19 30 41 8 14-13 36-13 14-38-10-17C71 2 38 4 48 36Z"/>
                            <circle cx="49" cy="49" r="8"/>
                        </svg>
                        <p>Masih cerita anda.<br>Cuma lebih sempurna.</p>
                        <span>Semak semula pesanan anda sebelum menyimpannya.</span>
                    </div>
                </section>

                <?php
                $entryForm = [
                    'action' => 'kemaskini.php',
                    'title' => 'Kemas kini catatan',
                    'submitLabel' => 'Simpan perubahan',
                    'note' => 'Semula akan memulihkan nilai asal rekod.',
                    'csrfToken' => $csrfToken,
                    'id' => (int) $entry['id'],
                    'values' => $entry,
                ];
                require __DIR__ . '/entry-form.php';
                ?>
            <?php endif; ?>
        </main>

        <footer class="site-footer">
            <p>Ruang untuk kata-kata yang ingin diingati.</p>
            <span>Buku Pelawat <span aria-hidden="true">&middot;</span> <?= escapeHtml($year) ?></span>
        </footer>
    </div>
</body>
</html>
