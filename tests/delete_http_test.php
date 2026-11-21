<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

/** Test the real endpoint without submitting any valid record ID to the database. */
function requestDeleteTest(string $url, string $method = 'GET', array $data = [], string $cookie = ''): array
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

function expectDeleteStatus(int $expected, array $response): void
{
    if ($response['status'] !== $expected) {
        throw new RuntimeException('Expected HTTP ' . $expected . ', got ' . $response['status']);
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
file_put_contents($router, '<?php
if ($_SERVER["REQUEST_URI"] === "/__test_token") {
    require ' . $csrfPath . ';
    startCsrfSession();
    echo csrfToken();
    session_write_close();
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
    $tokenResponse = requestDeleteTest($baseUrl . '/__test_token');
    expectDeleteStatus(200, $tokenResponse);
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
    $getResponse = requestDeleteTest($endpoint . '?id=1');
    expectDeleteStatus(405, $getResponse);
    if (!in_array('Allow: POST', $getResponse['headers'], true)) {
        throw new RuntimeException('Missing Allow: POST header.');
    }
    expectDeleteStatus(405, requestDeleteTest($endpoint, 'PUT'));
    expectDeleteStatus(403, requestDeleteTest($endpoint, 'POST', ['id' => 1], $cookie));
    expectDeleteStatus(403, requestDeleteTest($endpoint, 'POST', ['csrf_token' => 'wrong', 'id' => 1], $cookie));
    expectDeleteStatus(403, requestDeleteTest($endpoint, 'POST', ['csrf_token' => [$token], 'id' => 1], $cookie));
    expectDeleteStatus(403, requestDeleteTest($endpoint, 'POST', ['csrf_token' => $token, 'id' => 1]));

    foreach ([[], ['id' => 0], ['id' => -1], ['id' => '1 OR 1=1'], ['id' => [1]]] as $invalidId) {
        expectDeleteStatus(400, requestDeleteTest($endpoint, 'POST', ['csrf_token' => $token] + $invalidId, $cookie));
    }

    echo "PASS: 11 HTTP rejection checks, session cookie flags, and Allow header. No database mutations.\n";
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
