<?php

session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

require __DIR__ . '/db.php';

$pdo = db();

$userId = 0;

/*
|--------------------------------------------------------------------------
| Get logged-in user ID
|--------------------------------------------------------------------------
*/

if (
    isset($_SESSION['user']['user_id']) &&
    is_numeric($_SESSION['user']['user_id'])
) {
    $userId = (int)$_SESSION['user']['user_id'];

} elseif (
    isset($_SESSION['user']['id']) &&
    is_numeric($_SESSION['user']['id'])
) {
    $userId = (int)$_SESSION['user']['id'];
}


/*
|--------------------------------------------------------------------------
| Fallback: find user by email
|--------------------------------------------------------------------------
*/

if (
    $userId <= 0 &&
    isset($_SESSION['user']['email'])
) {

    $stmt = $pdo->prepare("
        SELECT user_id
        FROM users
        WHERE email = :email
        LIMIT 1
    ");

    $stmt->execute([
        ':email' => $_SESSION['user']['email']
    ]);

    $foundId = $stmt->fetchColumn();

    if ($foundId) {
        $userId = (int)$foundId;
    }
}


if ($userId <= 0) {
    session_destroy();
    header('Location: login.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Get current user
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        user_id,
        name,
        email,
        password_hash,
        role
    FROM users
    WHERE user_id = :user_id
    LIMIT 1
");

$stmt->execute([
    ':user_id' => $userId
]);

$user = $stmt->fetch();


if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Handle password change
|--------------------------------------------------------------------------
*/

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $currentPassword =
        $_POST['current_password'] ?? '';

    $newPassword =
        $_POST['new_password'] ?? '';

    $confirmPassword =
        $_POST['confirm_password'] ?? '';


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if (
        $currentPassword === '' ||
        $newPassword === '' ||
        $confirmPassword === ''
    ) {

        $error =
            'Please fill in all password fields.';

    } elseif (
        !password_verify(
            $currentPassword,
            $user['password_hash']
        )
    ) {

        $error =
            'Current password is incorrect.';

    } elseif (
        strlen($newPassword) < 6
    ) {

        $error =
            'New password must contain at least 6 characters.';

    } elseif (
        $newPassword !== $confirmPassword
    ) {

        $error =
            'New password and confirmation do not match.';

    } elseif (
        password_verify(
            $newPassword,
            $user['password_hash']
        )
    ) {

        $error =
            'New password must be different from your current password.';

    } else {

        /*
        |--------------------------------------------------------------------------
        | Hash new password
        |--------------------------------------------------------------------------
        */

        $newPasswordHash =
            password_hash(
                $newPassword,
                PASSWORD_DEFAULT
            );


        /*
        |--------------------------------------------------------------------------
        | Update database
        |--------------------------------------------------------------------------
        */

        $stmt = $pdo->prepare("
            UPDATE users
            SET password_hash = :password_hash
            WHERE user_id = :user_id
            LIMIT 1
        ");

        $stmt->execute([
            ':password_hash' => $newPasswordHash,
            ':user_id' => $userId
        ]);


        $message =
            'Password changed successfully.';

    }

}


/*
|--------------------------------------------------------------------------
| User initial
|--------------------------------------------------------------------------
*/

$initial = strtoupper(
    substr(
        trim($user['name']),
        0,
        1
    )
);

?>

<!doctype html>

<html lang="en">

<head>

<meta charset="utf-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1"
>

<title>
    Change Password - GSM Firmware Downloader
</title>

<link
    rel="stylesheet"
    href="assets/style.css"
>


<style>

/* =========================================================
   CHANGE PASSWORD PAGE
========================================================= */

.password-container {

    max-width: 700px;

    margin: 0 auto;

    padding: 45px 20px 70px;

}


/* =========================================================
   HEADING
========================================================= */

.password-heading {

    margin-bottom: 25px;

}

.password-eyebrow {

    color: #22d3ee;

    font-size: 12px;

    font-weight: 800;

    letter-spacing: 1px;

    margin-bottom: 7px;

}

.password-heading h1 {

    margin: 0;

    font-size: 32px;

}

.password-heading p {

    margin-top: 8px;

    color: #6b7280;

    font-size: 14px;

}


/* =========================================================
   CARD
========================================================= */

.password-card {

    background: #ffffff;

    border: 1px solid #e5e7eb;

    border-radius: 18px;

    padding: 30px;

    box-shadow:
        0 10px 30px rgba(0,0,0,0.05);

}


/* =========================================================
   USER HEADER
========================================================= */

.password-user {

    display: flex;

    align-items: center;

    gap: 14px;

    padding-bottom: 22px;

    margin-bottom: 22px;

    border-bottom:
        1px solid #e5e7eb;

}


.password-avatar {

    width: 48px;

    height: 48px;

    min-width: 48px;

    border-radius: 50%;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #111827;

    border: 1px solid #22d3ee;

    color: #ffffff;

    font-size: 18px;

    font-weight: 800;

}


.password-user-name {

    font-size: 16px;

    font-weight: 800;

    color: #111827;

}


.password-user-email {

    margin-top: 3px;

    font-size: 13px;

    color: #6b7280;

}


/* =========================================================
   ALERTS
========================================================= */

.password-success {

    padding: 12px 14px;

    margin-bottom: 20px;

    border-radius: 9px;

    background: #dcfce7;

    border: 1px solid #bbf7d0;

    color: #166534;

    font-size: 13px;

    font-weight: 600;

}


.password-error {

    padding: 12px 14px;

    margin-bottom: 20px;

    border-radius: 9px;

    background: #fee2e2;

    border: 1px solid #fecaca;

    color: #991b1b;

    font-size: 13px;

    font-weight: 600;

}


/* =========================================================
   FORM
========================================================= */

.password-form {

    display: flex;

    flex-direction: column;

    gap: 18px;

}


.password-field {

    display: flex;

    flex-direction: column;

    gap: 7px;

}


.password-field label {

    color: #374151;

    font-size: 13px;

    font-weight: 700;

}


.password-field input {

    width: 100%;

    box-sizing: border-box;

    padding: 12px 13px;

    border: 1px solid #d1d5db;

    border-radius: 9px;

    outline: none;

    font-family: inherit;

    font-size: 14px;

    color: #111827;

    background: #ffffff;

}


.password-field input:focus {

    border-color: #22d3ee;

    box-shadow:
        0 0 0 3px rgba(34,211,238,0.12);

}


.password-help {

    color: #6b7280;

    font-size: 12px;

}


/* =========================================================
   BUTTONS
========================================================= */

.password-actions {

    display: flex;

    align-items: center;

    gap: 10px;

    margin-top: 5px;

}


.password-submit {

    border: none;

    border-radius: 9px;

    padding: 12px 18px;

    background: #111827;

    color: #ffffff;

    font-family: inherit;

    font-size: 13px;

    font-weight: 700;

    cursor: pointer;

}


.password-submit:hover {

    opacity: .9;

}


.password-cancel {

    padding: 12px 18px;

    border-radius: 9px;

    background: #f3f4f6;

    color: #374151;

    text-decoration: none;

    font-size: 13px;

    font-weight: 700;

}


.password-cancel:hover {

    background: #e5e7eb;

}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 600px) {

    .password-card {

        padding: 22px;

    }

    .password-actions {

        flex-direction: column;

        align-items: stretch;

    }

    .password-submit,
    .password-cancel {

        text-align: center;

    }

}

</style>

</head>


<body>


<!-- =======================================================
     HEADER
======================================================= -->

<header class="top">


    <div class="brand">

        <span class="brand-mark">
            GF
        </span>

        <div>

            <b>
                GSM Firmware Downloader
            </b>

            <small>
                Firmware Management & Download System
            </small>

        </div>

    </div>


    <nav>

        <a href="dashboard.php">
            HOME
        </a>

        <a href="dashboard.php#services">
            SERVICES
        </a>

        <a href="dashboard.php#faq">
            FAQs
        </a>

        <a
            href="account.php"
            class="active"
        >
            ACCOUNT
        </a>


        <?php if (
            ($user['role'] ?? '') === 'admin'
        ): ?>

            <a href="admin.php">
                ADMIN PANEL
            </a>

        <?php endif; ?>


        <a href="logout.php">
            LOGOUT
        </a>

    </nav>

</header>



<!-- =======================================================
     MAIN
======================================================= -->

<main class="password-container">


    <section class="password-heading">

        <div class="password-eyebrow">
            ACCOUNT SECURITY
        </div>

        <h1>
            Change Password
        </h1>

        <p>
            Update your password to keep your account secure.
        </p>

    </section>



    <section class="password-card">


        <!-- USER -->

        <div class="password-user">


            <div class="password-avatar">

                <?= htmlspecialchars($initial) ?>

            </div>


            <div>

                <div class="password-user-name">

                    <?= htmlspecialchars(
                        $user['name']
                    ) ?>

                </div>

                <div class="password-user-email">

                    <?= htmlspecialchars(
                        $user['email']
                    ) ?>

                </div>

            </div>


        </div>



        <!-- SUCCESS -->

        <?php if ($message !== ''): ?>

            <div class="password-success">

                <?= htmlspecialchars($message) ?>

            </div>

        <?php endif; ?>



        <!-- ERROR -->

        <?php if ($error !== ''): ?>

            <div class="password-error">

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>



        <!-- FORM -->

        <form
            method="POST"
            class="password-form"
        >


            <div class="password-field">

                <label for="current_password">
                    Current Password
                </label>

                <input
                    type="password"
                    id="current_password"
                    name="current_password"
                    autocomplete="current-password"
                    required
                >

            </div>



            <div class="password-field">

                <label for="new_password">
                    New Password
                </label>

                <input
                    type="password"
                    id="new_password"
                    name="new_password"
                    autocomplete="new-password"
                    minlength="6"
                    required
                >

                <span class="password-help">
                    Password must contain at least 6 characters.
                </span>

            </div>



            <div class="password-field">

                <label for="confirm_password">
                    Confirm New Password
                </label>

                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    autocomplete="new-password"
                    minlength="6"
                    required
                >

            </div>



            <div class="password-actions">


                <button
                    type="submit"
                    class="password-submit"
                >
                    Change Password
                </button>


                <a
                    href="account.php"
                    class="password-cancel"
                >
                    Cancel
                </a>


            </div>


        </form>


    </section>


</main>


</body>

</html>