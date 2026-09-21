<?php

session_start();

require_once __DIR__ . '/db.php';

$url = trim($_GET['url'] ?? '');
$downloadId = isset($_GET['download_id'])
    ? (int)$_GET['download_id']
    : 0;

if ($url === '') {
    http_response_code(400);
    exit('Missing download URL.');
}

$parts = parse_url($url);

if (!$parts || empty($parts['host'])) {
    http_response_code(400);
    exit('Invalid download URL.');
}

$host = strtolower($parts['host']);

$allowedHosts = [
    'allawnos.com',
    'allawntech.com',
    'allawnfs.com'
];

$allowed = false;

foreach ($allowedHosts as $allowedHost) {
    if (
        $host === $allowedHost ||
        str_ends_with($host, '.' . $allowedHost)
    ) {
        $allowed = true;
        break;
    }
}

if (!$allowed) {
    http_response_code(403);
    exit('Download host is not allowed.');
}


/*
 * Resolve Allawn downloadCheck URL
 */
function resolveUrl(string $url): array
{
    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_NOBODY => true,

        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_TIMEOUT => 30,

        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,

        CURLOPT_USERAGENT =>
            'GSM-Firmware-Downloader/1.0',

        CURLOPT_HTTPHEADER => [
            'Accept: */*',
            'userid: oplus-ota|'
        ]
    ]);

    curl_exec($ch);

    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

    curl_close($ch);

    return [
        'http_code' => $httpCode,
        'final_url' => $finalUrl,
        'error' => $error
    ];
}


$result = resolveUrl($url);


/*
 * Some servers don't respond correctly to HEAD.
 * Try a small GET request.
 */
if (
    empty($result['final_url']) ||
    $result['final_url'] === $url ||
    $result['http_code'] >= 400
) {

    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,

        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_TIMEOUT => 30,

        CURLOPT_RANGE => '0-0',

        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,

        CURLOPT_USERAGENT =>
            'GSM-Firmware-Downloader/1.0',

        CURLOPT_HTTPHEADER => [
            'Accept: */*',
            'userid: oplus-ota|'
        ]
    ]);

    curl_exec($ch);

    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

    curl_close($ch);

    if (!empty($finalUrl)) {
        $result['final_url'] = $finalUrl;
        $result['http_code'] = $httpCode;
        $result['error'] = $error;
    }
}


/*
 * SUCCESS
 */
if (
    !empty($result['final_url']) &&
    $result['final_url'] !== $url &&
    $result['http_code'] >= 200 &&
    $result['http_code'] < 400
) {

    /*
     * Update download log
     */
    if ($downloadId > 0) {
        try {
            $pdo = db();

            $stmt = $pdo->prepare("
                UPDATE download_logs
                SET
                    status = 'completed',
                    completed_at = CURRENT_TIMESTAMP
                WHERE download_id = :download_id
                LIMIT 1
            ");

            $stmt->execute([
                ':download_id' => $downloadId
            ]);

        } catch (Throwable $e) {
            // Do not stop the actual download if logging update fails.
        }
    }


    /*
     * Redirect to actual firmware CDN
     */
    header(
        'Location: ' . $result['final_url'],
        true,
        302
    );

    exit;
}


/*
 * FAILED
 */
if ($downloadId > 0) {
    try {
        $pdo = db();

        $stmt = $pdo->prepare("
            UPDATE download_logs
            SET
                status = 'failed'
            WHERE download_id = :download_id
            LIMIT 1
        ");

        $stmt->execute([
            ':download_id' => $downloadId
        ]);

    } catch (Throwable $e) {
        // Ignore logging failure.
    }
}


http_response_code(502);

header('Content-Type: application/json; charset=utf-8');

echo json_encode([
    'success' => false,
    'error' => 'Unable to resolve firmware download link.',
    'http_code' => $result['http_code'] ?? 0,
    'details' => $result['error'] ?? ''
], JSON_UNESCAPED_SLASHES);