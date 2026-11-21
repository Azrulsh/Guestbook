<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../csrf.php';

function checkCsrf(bool $condition, string $description): void
{
    if (!$condition) {
        throw new RuntimeException('FAIL: ' . $description);
    }
}

startCsrfSession();
$_SESSION = [];

checkCsrf(!isValidCsrfToken(str_repeat('a', 64)), 'Reject tokens when no session token exists.');
$token = csrfToken();
checkCsrf(strlen($token) === 64 && ctype_xdigit($token), 'Generate a 256-bit hexadecimal token.');
checkCsrf(csrfToken() === $token, 'Keep existing forms valid across tabs.');
checkCsrf(isValidCsrfToken($token), 'Accept the matching token.');
checkCsrf(!isValidCsrfToken(null), 'Reject missing tokens.');
checkCsrf(!isValidCsrfToken(''), 'Reject empty tokens.');
checkCsrf(!isValidCsrfToken([$token]), 'Reject array input without a type error.');
checkCsrf(!isValidCsrfToken(str_repeat('x', 64)), 'Reject mismatched tokens.');

$_SESSION = [];
$otherToken = csrfToken();
checkCsrf($otherToken !== $token, 'Generate a different token for a new session.');
checkCsrf(!isValidCsrfToken($token), 'Reject tokens from another session.');
session_destroy();

echo "PASS: 10 CSRF regression checks.\n";
