<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../validation.php';

$checks = 0;

function expectValidation(bool $condition, string $description): void
{
    global $checks;
    if (!$condition) {
        throw new RuntimeException('FAIL: ' . $description);
    }
    $checks++;
}

function expectInvalidInput(callable $operation, string $description): void
{
    try {
        $operation();
    } catch (InputValidationException $exception) {
        expectValidation($exception->getMessage() !== '', $description);
        return;
    }
    throw new RuntimeException('FAIL: ' . $description);
}

$valid = ['nama' => "O'Connor", 'email' => 'ali@example.com', 'komen' => "Baris 1\nBaris 2"];
expectValidation(validateGuestbookEntry($valid) === $valid, 'Preserve apostrophes and comment lines.');
$trimmed = validateGuestbookEntry(['nama' => ' Ali ', 'email' => ' ali@example.com ', 'komen' => "\n Catatan \n"]);
expectValidation($trimmed === ['nama' => 'Ali', 'email' => 'ali@example.com', 'komen' => 'Catatan'], 'Trim surrounding whitespace.');

foreach (['nama', 'email', 'komen'] as $field) {
    $missing = $valid;
    unset($missing[$field]);
    expectInvalidInput(fn() => validateGuestbookEntry($missing), 'Reject missing ' . $field);
    foreach ([null, [], ['nested' => 'text'], true, 12, '', '   ', "\u{2003}", "bad\xFF", "bad\0text"] as $bad) {
        $input = array_replace($valid, [$field => $bad]);
        expectInvalidInput(fn() => validateGuestbookEntry($input), 'Reject malformed ' . $field);
    }
}

foreach (['nama' => str_repeat('a', 50), 'email' => str_repeat('a', 18) . '@example.com', 'komen' => str_repeat('a', 65535)] as $field => $boundary) {
    $input = array_replace($valid, [$field => $boundary]);
    expectValidation(validateGuestbookEntry($input)[$field] === $boundary, 'Accept exact limit for ' . $field);
    $input[$field] .= 'a';
    expectInvalidInput(fn() => validateGuestbookEntry($input), 'Reject over limit for ' . $field);
}

$unicodeName = array_replace($valid, ['nama' => str_repeat('你', 50)]);
expectValidation(validateGuestbookEntry($unicodeName)['nama'] === $unicodeName['nama'], 'Count name characters, not UTF-8 bytes.');
$unicodeName['nama'] .= '你';
expectInvalidInput(fn() => validateGuestbookEntry($unicodeName), 'Reject 51 Unicode name characters.');
$unicodeComment = array_replace($valid, ['komen' => str_repeat('你', 21845)]);
expectValidation(strlen(validateGuestbookEntry($unicodeComment)['komen']) === 65535, 'Accept TEXT byte boundary.');
$unicodeComment['komen'] .= '你';
expectInvalidInput(fn() => validateGuestbookEntry($unicodeComment), 'Reject excess TEXT bytes.');

foreach (['invalid', 'a@', '@example.com', "ali\r\n@example.com"] as $email) {
    expectInvalidInput(fn() => validateGuestbookEntry(array_replace($valid, ['email' => $email])), 'Reject invalid email.');
}

foreach (['1', (string) BOOK_MAX_ID, 1] as $id) {
    expectValidation(validateRecordId($id) === (int) $id, 'Accept valid ID.');
}
foreach ([null, [], ['1'], true, 1.5, '', '0', '-1', '2147483648', '999999999999999999999', '1 OR 1=1', '1.5', '1e2', ' 1', '+1'] as $id) {
    expectInvalidInput(fn() => validateRecordId($id), 'Reject invalid ID.');
}

$html = array_replace($valid, ['komen' => '</textarea><script>alert(1)</script>']);
expectValidation(validateGuestbookEntry($html)['komen'] === $html['komen'], 'Preserve HTML as data for output escaping.');
expectValidation(validateGuestbookEntry($valid + ['unexpected' => 'ignored']) === $valid, 'Ignore unrecognized fields.');

echo 'PASS: ' . $checks . " input validation checks.\n";
