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
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <title>Buku Pelawat Saya</title>
</head>
<body style="background-color: #ffffff; color: #000000;">
    <form method="post" action="isikan.php">
        <input type="hidden" name="csrf_token" value="<?= escapeHtml($csrfToken) ?>">
        <label for="nama">Nama :</label>
        <input type="text" id="nama" name="nama" size="40" required maxlength="<?= BOOK_NAME_MAX_LENGTH ?>">
        <br>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" size="25" required maxlength="<?= BOOK_EMAIL_MAX_LENGTH ?>">
        <br>
        <label for="komen">Catatan :</label><br>
        <textarea id="komen" name="komen" cols="30" rows="8" required></textarea>
        <br>
        <input type="submit" value="Hantar">
        <input type="reset" value="Semula">
        <br>
    </form>
    <hr>
    <div style="text-align: center;">
        [ <a href="papar.php">Balik ke Paparan</a> |
        <a href="index.php">Balik ke Halaman Utama</a> ]
    </div>
</body>
</html>
