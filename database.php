<?php

declare(strict_types=1);

final class DatabaseConnectionException extends RuntimeException
{
}

/** Missing environment variables use the existing local XAMPP defaults. */
function databaseEnvironmentValue(string $name, string $default): string
{
    $value = getenv($name);
    return $value === false ? $default : $value;
}

/**
 * Passwords are intentionally not trimmed: spaces may be part of a password.
 *
 * @return array{host: string, user: string, password: string, name: string, port: int}
 */
function databaseConfiguration(): array
{
    $configuration = [
        'host' => trim(databaseEnvironmentValue('GUESTBOOK_DB_HOST', 'localhost')),
        'user' => trim(databaseEnvironmentValue('GUESTBOOK_DB_USER', 'root')),
        'password' => databaseEnvironmentValue('GUESTBOOK_DB_PASSWORD', ''),
        'name' => trim(databaseEnvironmentValue('GUESTBOOK_DB_NAME', 'guestbook')),
    ];

    foreach (['host', 'user', 'name'] as $field) {
        if ($configuration[$field] === '') {
            throw new DatabaseConnectionException('Konfigurasi database tidak lengkap: ' . $field . '.');
        }
    }

    $port = filter_var(databaseEnvironmentValue('GUESTBOOK_DB_PORT', '3306'), FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => 65535],
    ]);

    if ($port === false) {
        throw new DatabaseConnectionException('Konfigurasi GUESTBOOK_DB_PORT tidak sah.');
    }

    $configuration['port'] = $port;
    return $configuration;
}

/** Return a fully configured connection or throw; never print or terminate a page. */
function createDatabaseConnection(): mysqli
{
    if (!extension_loaded('mysqli')) {
        throw new DatabaseConnectionException('Sambungan database memerlukan extension mysqli.');
    }

    $configuration = databaseConfiguration();
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $connection = mysqli_init();

    if ($connection === false) {
        throw new DatabaseConnectionException('Sambungan database tidak dapat disediakan.');
    }

    $connected = false;
    $stage = 'connection';

    // Network warnings must not reach HTML even when PHP display_errors is enabled.
    set_error_handler(static function (int $severity, string $message, string $file, int $line): void {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }, E_WARNING);

    try {
        if (!mysqli_options($connection, MYSQLI_OPT_CONNECT_TIMEOUT, 5)) {
            throw new RuntimeException('Unable to configure the connection timeout.');
        }

        if (!mysqli_real_connect(
            $connection,
            $configuration['host'],
            $configuration['user'],
            $configuration['password'],
            $configuration['name'],
            $configuration['port']
        )) {
            throw new RuntimeException('Unable to connect to the database.');
        }

        $connected = true;
        $stage = 'charset';
        if (!mysqli_set_charset($connection, 'utf8mb4')) {
            throw new RuntimeException('Unable to configure the connection charset.');
        }

        $stage = 'sql_mode';
        // Preserve server modes while preventing silent truncation or replacement.
        if (mysqli_query($connection, "SET SESSION sql_mode = CONCAT_WS(',', @@SESSION.sql_mode, 'STRICT_ALL_TABLES')") === false) {
            throw new RuntimeException('Unable to configure strict SQL mode.');
        }

        return $connection;
    } catch (Throwable $exception) {
        if ($connected) {
            mysqli_close($connection);
        }

        // Driver messages can contain account details. Log only the stage and code.
        error_log('Database setup failed at ' . $stage . ' (code ' . $exception->getCode() . ').');
        throw new DatabaseConnectionException('Sambungan database tidak dapat disediakan.', (int) $exception->getCode());
    } finally {
        restore_error_handler();
    }
}
