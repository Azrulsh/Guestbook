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
<html>
<head>
<title>Buku Palawat Saya</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>

<body bgcolor="#FFFFFF" text="#000000">
 <form method="post" action="isikan.php">
<input type="hidden" name="csrf_token" value="<?= escapeHtml($csrfToken) ?>">
Nama :
<input type="text" name="nama" size="40" required maxlength="<?= BOOK_NAME_MAX_LENGTH ?>">
<br>
Email:
<input type="email" name="email" size="25" required maxlength="<?= BOOK_EMAIL_MAX_LENGTH ?>">
<br>
Catatan : <br>
<textarea name="komen" cols="30" rows="8" required></textarea>
<br>
<input type="submit" value="Hantar">
<input type="reset" value="Semula">
<br>
</form>
<hr>
<div align="center">[ <a href="papar.php">Balik ke Paparan</a> | 
<a href="index.php">Balik ke Halaman Utaman</a ] </div>
</body>
</html>
