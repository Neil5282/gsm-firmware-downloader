<?php

header('Content-Type: application/json; charset=utf-8');

$baseUrl = 'https://roms.danielspringer.at/api/ota.php';

/*
|--------------------------------------------------------------------------
| Build API URL
|--------------------------------------------------------------------------
*/

$params = [];

if (isset($_GET['device']) && $_GET['device'] !== '') {
    $params['device'] = $_GET['device'];
}

if (isset($_GET['region']) && $_GET['region'] !== '') {
    $params['region'] = $_GET['region'];
}

if (isset($_GET['model']) && $_GET['model'] !== '') {
    $params['model'] = $_GET['model'];
}

if (isset($_GET['id']) && $_GET['id'] !== '') {
    $params['id'] = $_GET['id'];
}

if (isset($_GET['latest']) && $_GET['latest'] !== '') {
    $params['latest'] = $_GET['latest'];
}

$url = $baseUrl;

if (!empty($params)) {
    $url .= '?' . http_build_query($params);
}


/*
|--------------------------------------------------------------------------
| Request remote OTA API
|--------------------------------------------------------------------------
*/

$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_CONNECTTIMEOUT => 15,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
    CURLOPT_USERAGENT => 'GSM-Firmware-Downloader/1.0',
    CURLOPT_HTTPHEADER => [
        'Accept: application/json'
    ]
]);

$response = curl_exec($ch);

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);


/*
|--------------------------------------------------------------------------
| Error handling
|--------------------------------------------------------------------------
*/

if ($response === false || $error) {

    http_response_code(502);

    echo json_encode([
        'success' => false,
        'error' => 'Unable to connect to OTA catalog.',
        'details' => $error
    ]);

    exit;
}

if ($httpCode < 200 || $httpCode >= 300) {

    http_response_code(502);

    echo json_encode([
        'success' => false,
        'error' => 'OTA catalog returned HTTP ' . $httpCode
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| Validate JSON
|--------------------------------------------------------------------------
*/

$data = json_decode($response, true);

if (!is_array($data)) {

    http_response_code(502);

    echo json_encode([
        'success' => false,
        'error' => 'Invalid response received from OTA catalog.'
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| Return catalog
|--------------------------------------------------------------------------
*/

echo json_encode([
    'success' => true,
    'data' => $data
], JSON_UNESCAPED_SLASHES);