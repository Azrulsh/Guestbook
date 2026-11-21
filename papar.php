<?php

require_once __DIR__ . '/html.php';

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    require_once __DIR__ . '/dbase.php';

    $result = mysqli_query($conn, 'SELECT id, nama, email, tarikh, masa, komen FROM book');

    if ($result === false) {
        throw new RuntimeException(mysqli_error($conn));
    }

    $entries = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_free_result($result);
} catch (mysqli_sql_exception | RuntimeException $exception) {
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
                    <a href="buang.php?id=<?= escapeHtml(rawurlencode((string) $entry['id'])) ?>">Padam</a><br>
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
