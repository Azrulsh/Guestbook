<?php

require_once __DIR__ . '/html.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/validation.php';

try {
    $id = validateRecordId($_GET['id'] ?? null);
} catch (InputValidationException $exception) {
    http_response_code(400);
    exit($exception->getMessage());
}

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
} catch (Exception $exception) {
    error_log('ubah.php: ' . $exception->getMessage());
    http_response_code(500);
    exit('Data buku pelawat tidak dapat dimuatkan. Sila cuba lagi kemudian.');
}

if ($entry === null) {
    http_response_code(404);
    exit('Rekod buku pelawat tidak ditemui.');
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <title>Buku Pelawat Saya</title>
</head>
<body>
    <form method="post" action="kemaskini.php">
        <input type="hidden" name="csrf_token" value="<?= escapeHtml($csrfToken) ?>">
        <label for="nama">Nama:</label>
        <input type="text" id="nama" name="nama" size="40" required maxlength="<?= BOOK_NAME_MAX_LENGTH ?>" value="<?= escapeHtml($entry['nama']) ?>">
        <br>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" size="25" required maxlength="<?= BOOK_EMAIL_MAX_LENGTH ?>" value="<?= escapeHtml($entry['email']) ?>">
        <br>
        <label for="komen">Catatan:</label><br>
        <textarea id="komen" name="komen" cols="30" rows="8" required><?= escapeHtml($entry['komen']) ?></textarea>
        <br>
        <input type="hidden" name="id" value="<?= escapeHtml((string) $entry['id']) ?>">
        <input type="submit" value="Ubah">
        <input type="reset" value="Semula">
    </form>
    <hr>
    <div style="text-align: center;">
        [ <a href="papar.php">Balik ke Paparan</a> |
        <a href="index.php">Balik ke Halaman Utama</a> |
        <a href="masuk.php">Tambah Buku Pelawat</a> ]
    </div>
</body>
</html>
