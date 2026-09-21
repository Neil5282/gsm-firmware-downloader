<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

if (
    !isset($_SESSION['user']) ||
    ($_SESSION['user']['role'] ?? '') !== 'admin'
) {
    http_response_code(403);

    echo json_encode([
        'success' => false,
        'error' => 'Admin access required.'
    ]);

    exit;
}

require __DIR__ . '/db.php';

$pdo = db();

$action = $_POST['action'] ?? '';


try {

    /*
    |--------------------------------------------------------------------------
    | ADD USER
    |--------------------------------------------------------------------------
    */

    if ($action === 'add') {

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'user';


        if ($name === '' || $email === '' || $password === '') {

            throw new Exception(
                'Name, email and password are required.'
            );

        }


        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            throw new Exception(
                'Please enter a valid email address.'
            );

        }


        if (strlen($password) < 6) {

            throw new Exception(
                'Password must contain at least 6 characters.'
            );

        }


        if (!in_array($role, ['user', 'admin'], true)) {

            throw new Exception(
                'Invalid user role.'
            );

        }


        /*
         * Check duplicate email
         */

        $stmt = $pdo->prepare("
            SELECT user_id
            FROM users
            WHERE email = :email
            LIMIT 1
        ");

        $stmt->execute([
            ':email' => $email
        ]);


        if ($stmt->fetch()) {

            throw new Exception(
                'A user with this email already exists.'
            );

        }


        /*
         * Hash password
         */

        $passwordHash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );


        /*
         * Insert user
         */

        $stmt = $pdo->prepare("
            INSERT INTO users
            (
                name,
                email,
                password_hash,
                role
            )
            VALUES
            (
                :name,
                :email,
                :password_hash,
                :role
            )
        ");

        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':password_hash' => $passwordHash,
            ':role' => $role
        ]);


        echo json_encode([
            'success' => true,
            'message' => 'User created successfully.'
        ]);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | EDIT USER
    |--------------------------------------------------------------------------
    */

    if ($action === 'edit') {

        $userId = (int)($_POST['user_id'] ?? 0);

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        $password = $_POST['password'] ?? '';

        $role = $_POST['role'] ?? 'user';


        if ($userId <= 0) {

            throw new Exception(
                'Invalid user ID.'
            );

        }


        if ($name === '' || $email === '') {

            throw new Exception(
                'Name and email are required.'
            );

        }


        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            throw new Exception(
                'Please enter a valid email address.'
            );

        }


        if (!in_array($role, ['user', 'admin'], true)) {

            throw new Exception(
                'Invalid user role.'
            );

        }


        /*
         * Check email belongs to another user
         */

        $stmt = $pdo->prepare("
            SELECT user_id
            FROM users
            WHERE email = :email
            AND user_id != :user_id
            LIMIT 1
        ");

        $stmt->execute([
            ':email' => $email,
            ':user_id' => $userId
        ]);


        if ($stmt->fetch()) {

            throw new Exception(
                'Another user already uses this email.'
            );

        }


        /*
         * Update user
         */

        if ($password !== '') {

            if (strlen($password) < 6) {

                throw new Exception(
                    'Password must contain at least 6 characters.'
                );

            }


            $passwordHash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );


            $stmt = $pdo->prepare("
                UPDATE users
                SET
                    name = :name,
                    email = :email,
                    password_hash = :password_hash,
                    role = :role
                WHERE user_id = :user_id
                LIMIT 1
            ");

            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':password_hash' => $passwordHash,
                ':role' => $role,
                ':user_id' => $userId
            ]);

        } else {

            $stmt = $pdo->prepare("
                UPDATE users
                SET
                    name = :name,
                    email = :email,
                    role = :role
                WHERE user_id = :user_id
                LIMIT 1
            ");

            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':role' => $role,
                ':user_id' => $userId
            ]);

        }


        echo json_encode([
            'success' => true,
            'message' => 'User updated successfully.'
        ]);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE USER
    |--------------------------------------------------------------------------
    */

    if ($action === 'delete') {

        $userId = (int)($_POST['user_id'] ?? 0);


        if ($userId <= 0) {

            throw new Exception(
                'Invalid user ID.'
            );

        }


        /*
         * Prevent deleting yourself
         */

        $currentUserId = 0;

        if (
            isset($_SESSION['user']['user_id']) &&
            is_numeric($_SESSION['user']['user_id'])
        ) {

            $currentUserId =
                (int)$_SESSION['user']['user_id'];

        }


        if ($userId === $currentUserId) {

            throw new Exception(
                'You cannot delete the currently logged-in admin.'
            );

        }


        /*
         * Check whether user exists
         */

   $stmt = $pdo->prepare("
    SELECT
        user_id,
        name,
        is_owner
    FROM users
    WHERE user_id = :user_id
    LIMIT 1
");

$stmt->execute([
    ':user_id' => $userId
]);

$targetUser = $stmt->fetch();

if (!$targetUser) {
    throw new Exception(
        'User not found.'
    );
}

if ((int)$targetUser['is_owner'] === 1) {
    throw new Exception(
        'The main owner account cannot be deleted.'
    );
}


        /*
         * Delete user
         */

        $stmt = $pdo->prepare("
            DELETE FROM users
            WHERE user_id = :user_id
            LIMIT 1
        ");

        $stmt->execute([
            ':user_id' => $userId
        ]);


        echo json_encode([
            'success' => true,
            'message' => 'User deleted successfully.'
        ]);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | INVALID ACTION
    |--------------------------------------------------------------------------
    */

    throw new Exception(
        'Invalid user action.'
    );


} catch (Throwable $e) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);

    exit;
}