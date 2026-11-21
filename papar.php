<?php

require_once __DIR__ . '/html.php';
require_once __DIR__ . '/csrf.php';

try {
    startCsrfSession();
    $csrfToken = csrfToken();
    session_write_close();

    require_once __DIR__ . '/dbase.php';

    $result = mysqli_query($conn, 'SELECT id, nama, email, tarikh, masa, komen FROM book');

    if ($result === false) {
        throw new RuntimeException(mysqli_error($conn));
    }

    $entries = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_free_result($result);
} catch (Exception $exception) {
    error_log('papar.php: ' . $exception->getMessage());
    http_response_code(500);
    exit('Data buku pelawat tidak dapat dimuatkan. Sila cuba lagi kemudian.');
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <title>Paparan Buku Pelawat Saya</title>
    <style>
        .delete-link {
            background: none;
            border: 0;
            margin: 0;
            padding: 0;
            color: #0000ee;
            font: inherit;
            text-decoration: underline;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <?php if ($entries === []): ?>
        <p>Tiada rekod buku pelawat.</p>
    <?php else: ?>
        <ol>
            <?php foreach ($entries as $entry): ?>
                <li>
                    Nama: <?= escapeHtml($entry['nama']) ?><br>
                    Email: <?= escapeHtml($entry['email']) ?><br>
                    Tarikh / Masa: <?= escapeHtml($entry['tarikh']) ?> / <?= escapeHtml($entry['masa']) ?><br>
                    Catatan: <?= nl2br(escapeHtml($entry['komen'])) ?><br>
                    Tindakan:
                    <a href="ubah.php?id=<?= escapeHtml(rawurlencode((string) $entry['id'])) ?>">Ubah</a> /
                    <form method="post" action="buang.php" style="display: inline;"
                          onsubmit="return confirm('Padam rekod ini? Tindakan ini tidak boleh dibatalkan.');">
                        <input type="hidden" name="id" value="<?= escapeHtml((string) $entry['id']) ?>">
                        <input type="hidden" name="csrf_token" value="<?= escapeHtml($csrfToken) ?>">
                        <button type="submit" class="delete-link">Padam</button>
                    </form>
                    <br>
                </li>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>
    <div style="text-align: center;">
        [ <a href="index.php">Balik ke Halaman Utama</a> |
        <a href="masuk.php">Tambah Buku Pelawat</a> ]
    </div>
</body>
</html>
