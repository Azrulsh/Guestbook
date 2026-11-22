<?php

declare(strict_types=1);

// Keep these limits aligned with database/guestbook.sql: VARCHAR, TEXT, and signed INT.
const BOOK_NAME_MAX_LENGTH = 50;
const BOOK_EMAIL_MAX_LENGTH = 30;
const BOOK_COMMENT_MAX_BYTES = 65535;
const BOOK_MAX_ID = 2147483647;

final class InputValidationException extends InvalidArgumentException
{
}

/** Validate request input before it reaches a query or an HTML template. */
function validateRecordId($value): int
{
    if ((!is_string($value) && !is_int($value)) || !ctype_digit((string) $value)) {
        throw new InputValidationException('ID rekod tidak sah.');
    }

    $id = filter_var($value, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => BOOK_MAX_ID],
    ]);

    if ($id === false) {
        throw new InputValidationException('ID rekod tidak sah.');
    }

    return $id;
}

/**
 * Preserve punctuation and HTML as data; escape HTML only when displaying it.
 *
 * @return array{nama: string, email: string, komen: string}
 * @throws InputValidationException
 */
function validateGuestbookEntry(array $input): array
{
    $labels = ['nama' => 'Nama', 'email' => 'Email', 'komen' => 'Catatan'];
    $values = [];
    $errors = [];

    foreach ($labels as $field => $label) {
        $value = $input[$field] ?? null;

        if (!is_string($value)) {
            $errors[] = $label . ' diperlukan dan mesti berupa teks.';
            continue;
        }

        if (!mb_check_encoding($value, 'UTF-8')) {
            $errors[] = $label . ' mengandungi teks UTF-8 yang tidak sah.';
            continue;
        }

        // Trim surrounding Unicode whitespace, preserving internal comment lines.
        $value = preg_replace('/\A[\s\p{Z}]+|[\s\p{Z}]+\z/u', '', $value);

        if ($value === '') {
            $errors[] = $label . ' tidak boleh kosong.';
            continue;
        }

        $controlPattern = $field === 'komen'
            ? '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/'
            : '/\p{Cc}/u';

        if (preg_match($controlPattern, $value) === 1) {
            $errors[] = $label . ' mengandungi aksara kawalan yang tidak dibenarkan.';
            continue;
        }

        $values[$field] = $value;
    }

    if (isset($values['nama']) && mb_strlen($values['nama'], 'UTF-8') > BOOK_NAME_MAX_LENGTH) {
        $errors[] = 'Nama maksimum ' . BOOK_NAME_MAX_LENGTH . ' aksara.';
    }

    if (isset($values['email'])) {
        if (mb_strlen($values['email'], 'UTF-8') > BOOK_EMAIL_MAX_LENGTH) {
            $errors[] = 'Email maksimum ' . BOOK_EMAIL_MAX_LENGTH . ' aksara.';
        }

        if (filter_var($values['email'], FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = 'Format email tidak sah.';
        }
    }

    // TEXT is limited in bytes. Unicode characters may occupy multiple bytes.
    if (isset($values['komen']) && strlen($values['komen']) > BOOK_COMMENT_MAX_BYTES) {
        $errors[] = 'Catatan maksimum ' . BOOK_COMMENT_MAX_BYTES . ' bait UTF-8.';
    }

    if ($errors !== []) {
        throw new InputValidationException(implode("\n", $errors));
    }

    return $values;
}
