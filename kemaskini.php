<?php

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/validation.php';

header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: no-store');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Kaedah permintaan tidak dibenarkan. Gunakan borang kemas kini buku pelawat.');
}

try {
    startCsrfSession();
    $hasValidToken = isValidCsrfToken($_POST['csrf_token'] ?? null);
    session_write_close();

    if (!$hasValidToken) {
        http_response_code(403);
        exit('Permintaan tidak sah atau sesi telah tamat. Buka semula borang dan cuba lagi.');
    }

    try {
        $id = validateRecordId($_POST['id'] ?? null);
    } catch (InputValidationException $exception) {
        http_response_code(400);
        exit($exception->getMessage());
    }

    $entry = validateGuestbookEntry($_POST);

    require_once __DIR__ . '/dbase.php';

    $tarikh = date('d-m-Y');
    $masa = date('H:i:s');
    $stmt = mysqli_prepare($conn, 'UPDATE book SET nama = ?, email = ?, tarikh = ?, masa = ?, komen = ? WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'sssssi', $entry['nama'], $entry['email'], $tarikh, $masa, $entry['komen'], $id);
    mysqli_stmt_execute($stmt);
    $updatedCount = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);

    // Zero affected rows can mean either an unchanged record or a missing record.
    if ($updatedCount === 0) {
        $stmt = mysqli_prepare($conn, 'SELECT id FROM book WHERE id = ?');
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $exists = mysqli_fetch_assoc($result) !== null;
        mysqli_free_result($result);
        mysqli_stmt_close($stmt);

        if (!$exists) {
            http_response_code(404);
            exit('Rekod buku pelawat tidak ditemui atau telah dipadam.');
        }
    }
} catch (InputValidationException $exception) {
    http_response_code(422);
    exit($exception->getMessage());
} catch (mysqli_sql_exception $exception) {
    error_log('kemaskini.php: ' . $exception->getMessage());
    if ($exception->getCode() === 1366) {
        http_response_code(422);
        exit('Sebahagian aksara input tidak disokong oleh encoding database semasa.');
    }
    http_response_code(500);
    exit('Rekod tidak dapat dikemas kini. Sila cuba lagi kemudian.');
} catch (Exception $exception) {
    error_log('kemaskini.php: ' . $exception->getMessage());
    http_response_code(500);
    exit('Rekod tidak dapat dikemas kini. Sila cuba lagi kemudian.');
}

header('Location: papar.php', true, 303);
exit;
