<?php

session_start();

header('Content-Type: application/json; charset=utf-8');


// =====================================================
// CHECK LOGIN
// =====================================================

if (!isset($_SESSION['user'])) {

    http_response_code(401);

    echo json_encode([
        'success' => false,
        'error' => 'You must be logged in.'
    ]);

    exit;
}


// =====================================================
// LOAD DATABASE FUNCTION
// =====================================================

require_once __DIR__ . '/db.php';


// =====================================================
// CREATE PDO CONNECTION
// =====================================================

try {

    $pdo = db();

} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'error' => 'Unable to connect to database.'
    ]);

    exit;
}


// =====================================================
// GET LOGGED-IN USER
// =====================================================

$user = $_SESSION['user'];

$userId = 0;


// Check common ID names
if (isset($user['id']) && is_numeric($user['id'])) {

    $userId = (int)$user['id'];

}

elseif (isset($user['user_id']) && is_numeric($user['user_id'])) {

    $userId = (int)$user['user_id'];

}


// =====================================================
// IF ID IS NOT IN SESSION
// FIND USER BY EMAIL
// =====================================================

if ($userId <= 0) {

    $email = trim(
        (string)($user['email'] ?? '')
    );


    if ($email === '') {

        http_response_code(401);

        echo json_encode([
            'success' => false,
            'error' => 'Invalid user session.'
        ]);

        exit;
    }


    try {

        $stmt = $pdo->prepare(
            "SELECT id FROM users WHERE email = :email LIMIT 1"
        );

        $stmt->execute([
            ':email' => $email
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);


        if ($row) {

            $userId = (int)$row['id'];

        }

    } catch (PDOException $e) {

        http_response_code(500);

        echo json_encode([
            'success' => false,
            'error' => 'Unable to identify user.'
        ]);

        exit;
    }
}


// =====================================================
// FINAL USER CHECK
// =====================================================

if ($userId <= 0) {

    http_response_code(401);

    echo json_encode([
        'success' => false,
        'error' => 'Unable to identify the logged-in user.'
    ]);

    exit;
}


// =====================================================
// READ JSON REQUEST
// =====================================================

$input = json_decode(
    file_get_contents('php://input'),
    true
);


if (!is_array($input)) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'error' => 'Invalid request.'
    ]);

    exit;
}


// =====================================================
// FIRMWARE INFORMATION
// =====================================================

$firmwareId = trim(
    (string)($input['firmware_id'] ?? '')
);

$device = trim(
    (string)($input['device'] ?? '')
);

$model = trim(
    (string)($input['model'] ?? '')
);

$region = trim(
    (string)($input['region'] ?? '')
);

$version = trim(
    (string)($input['version'] ?? '')
);

$otaVersion = trim(
    (string)($input['ota_version'] ?? '')
);

$sizeBytes = isset($input['size_bytes'])
    ? (int)$input['size_bytes']
    : 0;

$sourceUrl = trim(
    (string)($input['source_url'] ?? '')
);


// =====================================================
// VALIDATION
// =====================================================

if (
    $firmwareId === '' ||
    $device === '' ||
    $sourceUrl === ''
) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'error' => 'Required firmware information is missing.'
    ]);

    exit;
}


// =====================================================
// INSERT DOWNLOAD LOG
// =====================================================

try {

    $sql = "
        INSERT INTO download_logs
        (
            user_id,
            firmware_id,
            device,
            model,
            region,
            version,
            ota_version,
            size_bytes,
            source_url,
            status
        )
        VALUES
        (
            :user_id,
            :firmware_id,
            :device,
            :model,
            :region,
            :version,
            :ota_version,
            :size_bytes,
            :source_url,
            'started'
        )
    ";


    $stmt = $pdo->prepare($sql);


    $stmt->execute([

        ':user_id' =>
            $userId,

        ':firmware_id' =>
            $firmwareId,

        ':device' =>
            $device,

        ':model' =>
            $model !== ''
                ? $model
                : null,

        ':region' =>
            $region !== ''
                ? $region
                : null,

        ':version' =>
            $version !== ''
                ? $version
                : null,

        ':ota_version' =>
            $otaVersion !== ''
                ? $otaVersion
                : null,

        ':size_bytes' =>
            $sizeBytes > 0
                ? $sizeBytes
                : null,

        ':source_url' =>
            $sourceUrl

    ]);


// =====================================================
// SUCCESS
// =====================================================

echo json_encode([
    'success' => true,
    'message' => 'Download activity recorded.',
    'download_id' => $pdo->lastInsertId(),
    'source_url' => $sourceUrl
]);

    exit;


} catch (PDOException $e) {


// =====================================================
// DATABASE ERROR
// =====================================================

    http_response_code(500);

    echo json_encode([

        'success' => false,

        'error' =>
            'Unable to record download.'

    ]);

    exit;
}