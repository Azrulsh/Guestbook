<?php

declare(strict_types=1);

/** Start a cookie-only session before any response output. */
function startCsrfSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_name('GuestbookSession');
    $isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

    // Convert storage warnings to exceptions so pages can log them privately.
    set_error_handler(static function (int $severity, string $message, string $file, int $line): void {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }, E_WARNING);

    try {
        $started = session_start([
            'use_strict_mode' => true,
            'use_only_cookies' => true,
            'use_trans_sid' => false,
            'cookie_httponly' => true,
            'cookie_secure' => $isHttps,
            'cookie_samesite' => 'Lax',
            'cookie_path' => '/',
        ]);
    } finally {
        restore_error_handler();
    }

    if (!$started) {
        throw new RuntimeException('Unable to start the CSRF session.');
    }
}

/** Reuse the session token so forms in other tabs continue to work. */
function csrfToken(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        throw new RuntimeException('An active session is required for CSRF protection.');
    }

    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/** Reject missing tokens and array input without raising a PHP type error. */
function isValidCsrfToken($submittedToken): bool
{
    $expectedToken = $_SESSION['csrf_token'] ?? null;

    return session_status() === PHP_SESSION_ACTIVE
        && is_string($submittedToken)
        && is_string($expectedToken)
        && strlen($submittedToken) === 64
        && hash_equals($expectedToken, $submittedToken);
}
