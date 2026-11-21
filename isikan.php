<?php

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/validation.php';

header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: no-store');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Kaedah permintaan tidak dibenarkan. Gunakan borang tambah buku pelawat.');
}

try {
    startCsrfSession();
    $hasValidToken = isValidCsrfToken($_POST['csrf_token'] ?? null);
    session_write_close();

    if (!$hasValidToken) {
        http_response_code(403);
        exit('Permintaan tidak sah atau sesi telah tamat. Buka semula borang dan cuba lagi.');
    }

    $entry = validateGuestbookEntry($_POST);

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    require_once __DIR__ . '/dbase.php';
    mysqli_set_charset($conn, 'utf8mb4');
    // Prevent silent truncation or character replacement in non-strict databases.
    mysqli_query($conn, "SET SESSION sql_mode = CONCAT_WS(',', @@SESSION.sql_mode, 'STRICT_ALL_TABLES')");

    $tarikh = date('d-m-Y');
    $masa = date('H:i:s');
    $stmt = mysqli_prepare($conn, 'INSERT INTO book (nama, email, tarikh, masa, komen) VALUES (?, ?, ?, ?, ?)');
    mysqli_stmt_bind_param($stmt, 'sssss', $entry['nama'], $entry['email'], $tarikh, $masa, $entry['komen']);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
} catch (InputValidationException $exception) {
    http_response_code(422);
    exit($exception->getMessage());
} catch (mysqli_sql_exception $exception) {
    error_log('isikan.php: ' . $exception->getMessage());
    if ($exception->getCode() === 1366) {
        http_response_code(422);
        exit('Sebahagian aksara input tidak disokong oleh encoding database semasa.');
    }
    http_response_code(500);
    exit('Rekod tidak dapat disimpan. Sila cuba lagi kemudian.');
} catch (Exception $exception) {
    error_log('isikan.php: ' . $exception->getMessage());
    http_response_code(500);
    exit('Rekod tidak dapat disimpan. Sila cuba lagi kemudian.');
}

header('Location: papar.php', true, 303);
exit;
