<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

/** Exercise real HTTP handlers without submitting valid writes to the database. */
function requestHttpTest(string $url, string $method = 'GET', array $data = [], string $cookie = ''): array
{
    $headers = ['Content-Type: application/x-www-form-urlencoded'];
    if ($cookie !== '') {
        $headers[] = 'Cookie: ' . $cookie;
    }

    $context = stream_context_create(['http' => [
        'method' => $method,
        'header' => implode("\r\n", $headers),
        'content' => http_build_query($data),
        'ignore_errors' => true,
        'follow_location' => 0,
        'timeout' => 3,
    ]]);
    $body = file_get_contents($url, false, $context);
    if ($body === false) {
        throw new RuntimeException('HTTP request failed.');
    }

    preg_match('/\s(\d{3})\s/', $http_response_header[0], $matches);
    return ['status' => (int) $matches[1], 'headers' => $http_response_header, 'body' => $body];
}

function expectHttpStatus(int $expected, array $response): void
{
    if ($response['status'] !== $expected) {
        throw new RuntimeException('Expected HTTP ' . $expected . ', got ' . $response['status'] . ': ' . substr($response['body'], 0, 800));
    }
}

$projectRoot = dirname(__DIR__);
$testDirectory = sys_get_temp_dir() . '/guestbook-http-' . bin2hex(random_bytes(8));
if (!mkdir($testDirectory, 0700)) {
    throw new RuntimeException('Unable to create isolated test directory.');
}

// The token endpoint exists only in this temporary test router.
$router = $testDirectory . '/router.php';
$csrfPath = var_export($projectRoot . '/csrf.php', true);
$rootPath = var_export($projectRoot . '/', true);
$fixturePath = var_export($testDirectory . '/', true);
foreach (['isikan.php', 'kemaskini.php', 'buang.php', 'ubah.php', 'papar.php', 'csrf.php', 'validation.php', 'html.php'] as $fixtureFile) {
    copy($projectRoot . '/' . $fixtureFile, $testDirectory . '/' . $fixtureFile);
}

// Each fixture request gets its own temporary table, shadowing any real book table.
// No persistent tables or records are created, updated, or deleted by these tests.
file_put_contents($testDirectory . '/dbase.php', <<<'PHP'
<?php
date_default_timezone_set('Asia/Kuala_Lumpur');
require_once __DIR__ . '/database.php';
$conn = createDatabaseConnection();
mysqli_query($conn, 'CREATE TEMPORARY TABLE book (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(50) NOT NULL,
    email VARCHAR(30) NOT NULL,
    tarikh VARCHAR(10) NOT NULL,
    masa VARCHAR(8) NOT NULL,
    komen TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1');
$date = date('d-m-Y');
$time = date('H:i:s');
$fixture = $_GET['fixture'] ?? '';
if ($fixture !== 'empty') {
    $name = $fixture === 'xss' ? '"><svg onload=alert(1)>' : 'Ali';
    $email = $fixture === 'xss' ? '"><svg/onload=alert(1)>' : 'ali@example.com';
    $comment = $fixture === 'xss' ? "Line 1\n</div><script>alert(1)</script>" : 'Catatan';
    $seed = mysqli_prepare($conn, 'INSERT INTO book VALUES (1, ?, ?, ?, ?, ?)');
    mysqli_stmt_bind_param($seed, 'sssss', $name, $email, $date, $time, $comment);
    mysqli_stmt_execute($seed);
    mysqli_stmt_close($seed);
}
register_shutdown_function(static function () use ($conn): void {
    $result = mysqli_query($conn, 'SELECT id, nama, email, komen FROM book ORDER BY id');
    echo "\n__TEST_ROWS__" . json_encode(mysqli_fetch_all($result, MYSQLI_ASSOC), JSON_THROW_ON_ERROR);
});
PHP
);
copy($projectRoot . '/database.php', $testDirectory . '/database.php');
file_put_contents($router, '<?php
if ($_SERVER["REQUEST_URI"] === "/__test_token") {
    require ' . $csrfPath . ';
    startCsrfSession();
    echo csrfToken();
    session_write_close();
    return;
}
$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$parts = explode("/", trim($path, "/"));
if (count($parts) === 3 && $parts[0] === "__db_error"
    && in_array($parts[2], ["papar.php", "ubah.php", "isikan.php", "kemaskini.php", "buang.php"], true)) {
    putenv("GUESTBOOK_DB_HOST=127.0.0.1");
    putenv("GUESTBOOK_DB_NAME=__guestbook_missing_" . ' . var_export(bin2hex(random_bytes(8)), true) . ');
    if ($parts[1] === "port") {
        putenv("GUESTBOOK_DB_PORT=1");
    } elseif ($parts[1] === "config") {
        putenv("GUESTBOOK_DB_PORT=invalid");
    } elseif ($parts[1] === "password") {
        putenv("GUESTBOOK_DB_PASSWORD=__invalid_test_password__");
    }
    require ' . $rootPath . ' . $parts[2];
    return;
}
$allowed = ["isikan.php", "kemaskini.php", "buang.php", "ubah.php", "papar.php"];
if (str_starts_with($path, "/__fixture/") && in_array(basename($path), $allowed, true)) {
    require ' . $fixturePath . ' . basename($path);
    return;
}
return false;
');

$listener = stream_socket_server('tcp://127.0.0.1:0', $errorCode, $errorMessage);
if ($listener === false) {
    throw new RuntimeException($errorMessage);
}
$address = stream_socket_get_name($listener, false);
fclose($listener);

$process = null;
try {
    $process = proc_open([
        PHP_BINARY, '-d', 'session.save_path=' . $testDirectory,
        '-d', 'sys_temp_dir=' . $testDirectory,
        '-d', 'upload_tmp_dir=' . $testDirectory,
        '-S', $address, '-t', $projectRoot, $router,
    ], [
        0 => ['pipe', 'r'],
        1 => ['file', $testDirectory . '/server.log', 'a'],
        2 => ['file', $testDirectory . '/server.log', 'a'],
    ], $pipes);

    if (!is_resource($process)) {
        throw new RuntimeException('Unable to start the test HTTP server.');
    }
    fclose($pipes[0]);

    $ready = false;
    for ($attempt = 0; $attempt < 50; $attempt++) {
        $socket = @stream_socket_client('tcp://' . $address, $errorCode, $errorMessage, 0.1);
        if ($socket !== false) {
            fclose($socket);
            $ready = true;
            break;
        }
        usleep(100000);
    }
    if (!$ready) {
        throw new RuntimeException('The test HTTP server did not become ready.');
    }

    $baseUrl = 'http://' . $address;
    $tokenResponse = requestHttpTest($baseUrl . '/__test_token');
    expectHttpStatus(200, $tokenResponse);
    $token = $tokenResponse['body'];
    $cookie = '';
    foreach ($tokenResponse['headers'] as $header) {
        if (preg_match('/^Set-Cookie: ([^;]+)/i', $header, $matches)) {
            $cookie = $matches[1];
            if (stripos($header, 'HttpOnly') === false || stripos($header, 'SameSite=Lax') === false) {
                throw new RuntimeException('Missing session cookie protections.');
            }
        }
    }
    if ($cookie === '' || strlen($token) !== 64) {
        throw new RuntimeException('Missing session cookie or CSRF token.');
    }

    $endpoint = $baseUrl . '/buang.php';
    $getResponse = requestHttpTest($endpoint . '?id=1');
    expectHttpStatus(405, $getResponse);
    if (!in_array('Allow: POST', $getResponse['headers'], true)) {
        throw new RuntimeException('Missing Allow: POST header.');
    }
    expectHttpStatus(405, requestHttpTest($endpoint, 'PUT'));
    expectHttpStatus(403, requestHttpTest($endpoint, 'POST', ['id' => 1], $cookie));
    expectHttpStatus(403, requestHttpTest($endpoint, 'POST', ['csrf_token' => 'wrong', 'id' => 1], $cookie));
    expectHttpStatus(403, requestHttpTest($endpoint, 'POST', ['csrf_token' => [$token], 'id' => 1], $cookie));
    expectHttpStatus(403, requestHttpTest($endpoint, 'POST', ['csrf_token' => $token, 'id' => 1]));

    foreach ([[], ['id' => 0], ['id' => -1], ['id' => '1 OR 1=1'], ['id' => [1]]] as $invalidId) {
        expectHttpStatus(400, requestHttpTest($endpoint, 'POST', ['csrf_token' => $token] + $invalidId, $cookie));
    }

    foreach (['isikan.php', 'kemaskini.php'] as $handler) {
        $writeUrl = $baseUrl . '/' . $handler;
        expectHttpStatus(405, requestHttpTest($writeUrl));
        expectHttpStatus(403, requestHttpTest($writeUrl, 'POST', ['id' => 1], $cookie));

        $baseInput = ['csrf_token' => $token, 'id' => '1'];
        foreach ([
            [],
            ['nama' => [], 'email' => 'ali@example.com', 'komen' => 'Catatan'],
            ['nama' => str_repeat('a', 51), 'email' => 'ali@example.com', 'komen' => 'Catatan'],
            ['nama' => 'Ali', 'email' => 'bad email', 'komen' => 'Catatan'],
            ['nama' => 'Ali', 'email' => 'ali@example.com', 'komen' => str_repeat('a', 65536)],
        ] as $invalidEntry) {
            expectHttpStatus(422, requestHttpTest($writeUrl, 'POST', $baseInput + $invalidEntry, $cookie));
        }
    }

    $validEntry = ['nama' => 'Ali', 'email' => 'ali@example.com', 'komen' => 'Catatan', 'csrf_token' => $token];
    foreach ([null, [], '0', '-1', '2147483648', '1 OR 1=1'] as $invalidId) {
        expectHttpStatus(400, requestHttpTest($baseUrl . '/kemaskini.php', 'POST', $validEntry + ['id' => $invalidId], $cookie));
        expectHttpStatus(400, requestHttpTest($baseUrl . '/ubah.php?' . http_build_query(['id' => $invalidId])));
        expectHttpStatus(400, requestHttpTest($endpoint, 'POST', ['csrf_token' => $token, 'id' => $invalidId], $cookie));
    }

    $formResponse = requestHttpTest($baseUrl . '/masuk.php', 'GET', [], $cookie);
    expectHttpStatus(200, $formResponse);
    if (strpos($formResponse['body'], 'name="csrf_token" value="' . $token . '"') === false) {
        throw new RuntimeException('The add form must include the session CSRF token.');
    }

    echo "PASS: 43 HTTP rejection checks, form token, cookie flags, and Allow header.\n";

    $databaseAvailable = false;
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    try {
        $probe = mysqli_connect('localhost', 'root', '', 'guestbook');
        mysqli_close($probe);
        $databaseAvailable = true;
    } catch (mysqli_sql_exception $exception) {
        echo "SKIP: Temporary-table integration tests require the local guestbook database (root, no password).\n";
    }

    if ($databaseAvailable) {
        $fixtureUrl = $baseUrl . '/__fixture/';
        $validEntry['id'] = '1';
        $cases = [
            ['isikan.php', 303, array_replace($validEntry, ['nama' => "O'Connor", 'komen' => '<script>alert(1)</script>']), 2],
            ['isikan.php', 303, array_replace($validEntry, ['nama' => str_repeat('a', 50), 'email' => str_repeat('a', 18) . '@example.com', 'komen' => str_repeat('a', 65535)]), 2],
            ['kemaskini.php', 303, array_replace($validEntry, ['nama' => "O'Connor"]), 1],
            ['kemaskini.php', 303, $validEntry, 1],
            ['kemaskini.php', 404, array_replace($validEntry, ['id' => '99']), 1],
            ['buang.php', 303, $validEntry, 0],
            ['buang.php', 404, array_replace($validEntry, ['id' => '99']), 1],
            ['isikan.php', 422, array_replace($validEntry, ['nama' => '你好']), 1],
        ];

        foreach ($cases as [$handler, $expectedStatus, $input, $expectedRows]) {
            $response = requestHttpTest($fixtureUrl . $handler, 'POST', $input, $cookie);
            expectHttpStatus($expectedStatus, $response);
            if ($expectedStatus === 303 && !in_array('Location: papar.php', $response['headers'], true)) {
                throw new RuntimeException('Successful writes must redirect to papar.php.');
            }
            $snapshot = explode("\n__TEST_ROWS__", $response['body'], 2);
            $rows = json_decode($snapshot[1] ?? '', true, 512, JSON_THROW_ON_ERROR);
            if (count($rows) !== $expectedRows) {
                throw new RuntimeException('Unexpected row count after ' . $handler);
            }
            if ($expectedStatus === 303 && $handler !== 'buang.php') {
                $written = $rows[count($rows) - 1];
                foreach (['nama', 'email', 'komen'] as $field) {
                    if ($written[$field] !== $input[$field]) {
                        throw new RuntimeException('Input was changed or truncated during storage: ' . $field);
                    }
                }
            }
        }
        echo "PASS: 8 temporary-table write integration cases. No persistent database mutations.\n";

        foreach (['', 'empty', 'xss'] as $fixture) {
            $response = requestHttpTest($fixtureUrl . 'papar.php?fixture=' . $fixture, 'GET', [], $cookie);
            expectHttpStatus(200, $response);
            $markup = explode("\n__TEST_ROWS__", $response['body'], 2)[0];
            $document = Dom\HTMLDocument::createFromString($markup, LIBXML_NOERROR);
            $expectedCount = $fixture === 'empty' ? 0 : 1;
            if ($document->querySelectorAll('.record-card')->length !== $expectedCount) {
                throw new RuntimeException('Unexpected listing card count.');
            }
            if ($document->querySelectorAll('a[href="masuk.php"]')->length !== 1) {
                throw new RuntimeException('The listing must have a single write action.');
            }
            if ($fixture === 'empty') {
                if ($document->querySelector('#empty-title') === null || $document->querySelector('#list-controls') !== null) {
                    throw new RuntimeException('The empty state must not show inactive list controls.');
                }
                continue;
            }
            $deleteForm = $document->querySelector('form.delete-entry');
            if ($deleteForm === null || $deleteForm->getAttribute('method') !== 'post' || $deleteForm->getAttribute('action') !== 'buang.php') {
                throw new RuntimeException('Deletion must remain a POST form.');
            }
            if ($deleteForm->querySelector('input[name="csrf_token"]')->getAttribute('value') !== $token
                || $deleteForm->querySelector('input[name="id"]')->getAttribute('value') !== '1') {
                throw new RuntimeException('The listing must retain its session token and record ID.');
            }
            if ($document->querySelectorAll('script:not([src]), [onload], [onerror], [onclick], [onsubmit]')->length !== 0) {
                throw new RuntimeException('Unexpected executable inline markup in the listing.');
            }
            if ($fixture === 'xss' && $document->querySelector('.record-name')->textContent !== '"><svg onload=alert(1)>') {
                throw new RuntimeException('User content must be preserved as plain text.');
            }
            if ($document->querySelector('.record-message')->querySelector('script') !== null) {
                throw new RuntimeException('Stored markup must not become an executable script.');
            }
        }
        echo "PASS: Populated, empty and stored-XSS listing states; POST/CSRF and single write action verified.\n";
    }

    foreach (['port', 'config', 'password', 'missing'] as $failure) {
        foreach (['papar.php', 'ubah.php', 'isikan.php', 'kemaskini.php', 'buang.php'] as $handler) {
            $url = $baseUrl . '/__db_error/' . $failure . '/' . $handler;
            $response = in_array($handler, ['papar.php', 'ubah.php'], true)
                ? requestHttpTest($url . '?id=1', 'GET', [], $cookie)
                : requestHttpTest($url, 'POST', $validEntry, $cookie);
            expectHttpStatus(500, $response);
            foreach (['Warning', 'Stack trace', 'mysqli', '127.0.0.1', '__guestbook_missing_', '__invalid_test_password__', $projectRoot] as $privateDetail) {
                if (stripos($response['body'], $privateDetail) !== false) {
                    throw new RuntimeException('Database failure disclosed internal details.');
                }
            }
        }
    }
    expectHttpStatus(404, requestHttpTest($baseUrl . '/dbase.php'));
    if (strpos(file_get_contents($testDirectory . '/server.log'), '__invalid_test_password__') !== false) {
        throw new RuntimeException('Database credentials must not appear in logs.');
    }
    echo "PASS: 20 database failure HTTP cases and direct bootstrap access rejection.\n";
} finally {
    if (is_resource($process)) {
        proc_terminate($process);
        proc_close($process);
    }
    // Remove only the flat, randomly named directory created by this test.
    foreach (new DirectoryIterator($testDirectory) as $file) {
        if ($file->isFile()) {
            unlink($file->getPathname());
        }
    }
    rmdir($testDirectory);
}
