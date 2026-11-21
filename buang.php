<?php

require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/validation.php';

header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: no-store');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Kaedah permintaan tidak dibenarkan. Gunakan borang Padam pada halaman paparan.');
}

try {
    startCsrfSession();
    $hasValidToken = isValidCsrfToken($_POST['csrf_token'] ?? null);
    session_write_close();

    if (!$hasValidToken) {
        http_response_code(403);
        exit('Permintaan tidak sah atau sesi telah tamat. Buka semula halaman paparan dan cuba lagi.');
    }

    try {
        $id = validateRecordId($_POST['id'] ?? null);
    } catch (InputValidationException $exception) {
        http_response_code(400);
        exit($exception->getMessage());
    }

    // Validate the request before opening the database connection.
    require_once __DIR__ . '/dbase.php';

    $stmt = mysqli_prepare($conn, 'DELETE FROM book WHERE id = ?');

    if ($stmt === false) {
        throw new RuntimeException(mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, 'i', $id);

    if (!mysqli_stmt_execute($stmt)) {
        throw new RuntimeException(mysqli_stmt_error($stmt));
    }

    $deletedCount = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);

    if ($deletedCount === 0) {
        http_response_code(404);
        exit('Rekod buku pelawat tidak ditemui atau telah dipadam.');
    }
} catch (Exception $exception) {
    error_log('buang.php: ' . $exception->getMessage());
    http_response_code(500);
    exit('Rekod tidak dapat dipadam. Sila cuba lagi kemudian.');
}

// Post/Redirect/Get prevents a refresh from resubmitting the delete request.
header('Location: papar.php', true, 303);
exit;
