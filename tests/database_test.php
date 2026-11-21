<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../database.php';

$variables = ['GUESTBOOK_DB_HOST', 'GUESTBOOK_DB_USER', 'GUESTBOOK_DB_PASSWORD', 'GUESTBOOK_DB_NAME', 'GUESTBOOK_DB_PORT'];
$originalValues = [];
$checks = 0;

function expectDatabase(bool $condition, string $description): void
{
    global $checks;
    if (!$condition) {
        throw new RuntimeException('FAIL: ' . $description);
    }
    $checks++;
}

try {
    foreach ($variables as $variable) {
        $originalValues[$variable] = getenv($variable);
        putenv($variable);
    }

    expectDatabase(databaseConfiguration() === [
        'host' => 'localhost', 'user' => 'root', 'password' => '', 'name' => 'guestbook', 'port' => 3306,
    ], 'Use existing XAMPP defaults when environment variables are absent.');

    putenv('GUESTBOOK_DB_PASSWORD= example password ');
    putenv('GUESTBOOK_DB_PORT=3307');
    $configuration = databaseConfiguration();
    expectDatabase($configuration['password'] === ' example password ', 'Preserve password whitespace.');
    expectDatabase($configuration['port'] === 3307, 'Support a custom database port.');

    foreach (['', '0', '-1', '65536', 'invalid', '3306.5'] as $port) {
        putenv('GUESTBOOK_DB_PORT=' . $port);
        try {
            databaseConfiguration();
            throw new RuntimeException('Invalid port was accepted.');
        } catch (DatabaseConnectionException $exception) {
            expectDatabase(strpos($exception->getMessage(), 'example password') === false, 'Reject invalid port without disclosing credentials.');
        }
    }
    putenv('GUESTBOOK_DB_PORT');

    foreach (['GUESTBOOK_DB_HOST', 'GUESTBOOK_DB_USER', 'GUESTBOOK_DB_NAME'] as $variable) {
        putenv($variable . '= ');
        try {
            databaseConfiguration();
            throw new RuntimeException('Blank required configuration was accepted.');
        } catch (DatabaseConnectionException $exception) {
            expectDatabase($exception->getMessage() !== '', 'Reject blank required configuration.');
        }
        putenv($variable);
    }
    putenv('GUESTBOOK_DB_PASSWORD');

    // A successful connection must not print anything or leave error handlers installed.
    $handler = static function (): void {};
    set_error_handler($handler);
    ob_start();
    try {
        $connection = createDatabaseConnection();
        $output = ob_get_contents();
        expectDatabase($output === '', 'Database setup must not print output.');
        expectDatabase(mysqli_character_set_name($connection) === 'utf8mb4', 'Use an explicit UTF-8 connection charset.');
        $result = mysqli_query($connection, 'SELECT @@SESSION.sql_mode AS mode');
        $mode = mysqli_fetch_assoc($result)['mode'];
        expectDatabase(in_array('STRICT_ALL_TABLES', explode(',', $mode), true), 'Enable strict SQL mode.');
        mysqli_free_result($result);
        mysqli_close($connection);
        $previousHandler = set_error_handler($handler);
        expectDatabase($previousHandler === $handler, 'Restore the caller error handler.');
        restore_error_handler();
    } finally {
        ob_end_clean();
        restore_error_handler();
    }

    putenv('GUESTBOOK_DB_NAME=__guestbook_missing_' . bin2hex(random_bytes(8)));
    set_error_handler($handler);
    ob_start();
    try {
        try {
            createDatabaseConnection();
            throw new RuntimeException('A nonexistent database was accepted.');
        } catch (DatabaseConnectionException $exception) {
            expectDatabase($exception->getCode() === 1049, 'Preserve the driver error code for diagnostics.');
            expectDatabase($exception->getPrevious() === null, 'Do not expose raw driver errors in exception chains.');
            expectDatabase(strpos($exception->getMessage(), '__guestbook_missing_') === false, 'Keep database names out of public exceptions.');
            expectDatabase(ob_get_contents() === '', 'Connection failures must not print warnings.');
            $previousHandler = set_error_handler($handler);
            expectDatabase($previousHandler === $handler, 'Restore the caller error handler after failure.');
            restore_error_handler();
        }
    } finally {
        ob_end_clean();
        restore_error_handler();
    }

    echo 'PASS: ' . $checks . " database configuration and connection checks. No database mutations.\n";
} finally {
    foreach ($originalValues as $variable => $value) {
        putenv($value === false ? $variable : $variable . '=' . $value);
    }
}
