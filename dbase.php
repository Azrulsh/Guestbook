<?php

// This bootstrap is included by page handlers; it is not a diagnostic endpoint.
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/database.php';

date_default_timezone_set('Asia/Kuala_Lumpur');
$conn = createDatabaseConnection();
