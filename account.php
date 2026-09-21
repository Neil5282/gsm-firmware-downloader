<?php

session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

require __DIR__ . '/db.php';

$pdo = db();

/*
|--------------------------------------------------------------------------
| Get logged-in user ID
|--------------------------------------------------------------------------
*/

$userId = 0;

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
| Find user using email if ID is unavailable
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


/*
|--------------------------------------------------------------------------
| Get user information
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        user_id,
        name,
        email,
        role,
        created_at
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
| Download statistics
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM download_logs
    WHERE user_id = :user_id
");

$stmt->execute([
    ':user_id' => $userId
]);

$totalDownloads = (int)$stmt->fetchColumn();


$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM download_logs
    WHERE user_id = :user_id
    AND status = 'completed'
");

$stmt->execute([
    ':user_id' => $userId
]);

$completedDownloads = (int)$stmt->fetchColumn();


$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM download_logs
    WHERE user_id = :user_id
    AND status = 'failed'
");

$stmt->execute([
    ':user_id' => $userId
]);

$failedDownloads = (int)$stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| Download history
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        download_id,
        device,
        model,
        region,
        version,
        ota_version,
        status,
        started_at,
        completed_at
    FROM download_logs
    WHERE user_id = :user_id
    ORDER BY download_id DESC
    LIMIT 50
");

$stmt->execute([
    ':user_id' => $userId
]);

$downloads = $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| Avatar initial
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
    My Account - GSM Firmware Downloader
</title>

<link
    rel="stylesheet"
    href="assets/style.css"
>


<style>

/* =========================================================
   ACCOUNT PAGE
========================================================= */

.account-container {

    max-width: 1200px;

    margin: 0 auto;

    padding: 35px 20px 60px;

}


/* =========================================================
   PAGE HEADER
========================================================= */

.account-heading {

    margin-bottom: 25px;

}

.account-heading .eyebrow {

    color: #22d3ee;

    font-size: 12px;

    font-weight: 800;

    letter-spacing: 1px;

    margin-bottom: 7px;

}

.account-heading h1 {

    margin: 0;

    font-size: 32px;

}

.account-heading p {

    margin-top: 7px;

    color: #6b7280;

    font-size: 14px;

}


/* =========================================================
   PROFILE CARD
========================================================= */

.profile-card {

    background: #ffffff;

    border: 1px solid #e5e7eb;

    border-radius: 18px;

    padding: 25px;

    margin-bottom: 22px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 20px;

    box-shadow:
        0 8px 25px rgba(0,0,0,0.04);

}


.profile-left {

    display: flex;

    align-items: center;

    gap: 17px;

}


.profile-avatar {

    width: 68px;

    height: 68px;

    min-width: 68px;

    border-radius: 50%;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #111827;

    border: 2px solid #22d3ee;

    color: white;

    font-size: 26px;

    font-weight: 800;

}


.profile-name {

    font-size: 23px;

    font-weight: 800;

    color: #111827;

}


.profile-email {

    margin-top: 4px;

    font-size: 14px;

    color: #6b7280;

}


.role-badge {

    display: inline-block;

    margin-top: 8px;

    padding: 5px 10px;

    border-radius: 999px;

    background: #eef2ff;

    color: #3730a3;

    font-size: 11px;

    font-weight: 800;

    text-transform: uppercase;

}


/* =========================================================
   BUTTONS
========================================================= */

.account-button {

    display: inline-block;

    padding: 10px 15px;

    border-radius: 9px;

    border: none;

    text-decoration: none;

    font-size: 13px;

    font-weight: 700;

    cursor: pointer;

}


.account-button-primary {

    background: #111827;

    color: white;

}


.account-button-primary:hover {

    opacity: .9;

}


.account-button-danger {

    background: #fee2e2;

    color: #991b1b;

}


/* =========================================================
   STATISTICS
========================================================= */

.account-stats {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 18px;

    margin-bottom: 22px;

}


.account-stat {

    background: #ffffff;

    border: 1px solid #e5e7eb;

    border-radius: 16px;

    padding: 20px;

}


.account-stat-label {

    color: #6b7280;

    font-size: 13px;

    margin-bottom: 7px;

}


.account-stat-value {

    color: #111827;

    font-size: 28px;

    font-weight: 800;

}


/* =========================================================
   INFORMATION GRID
========================================================= */

.account-grid {

    display: grid;

    grid-template-columns:
        1fr 1fr;

    gap: 22px;

    margin-bottom: 22px;

}


.account-panel {

    background: #ffffff;

    border: 1px solid #e5e7eb;

    border-radius: 16px;

    overflow: hidden;

}


.account-panel-header {

    padding: 18px 20px;

    border-bottom:
        1px solid #e5e7eb;

}


.account-panel-header h2 {

    margin: 0;

    font-size: 18px;

}


.account-panel-body {

    padding: 20px;

}


.info-row {

    display: flex;

    align-items: flex-start;

    justify-content: space-between;

    gap: 20px;

    padding: 12px 0;

    border-bottom:
        1px solid #f3f4f6;

}


.info-row:last-child {

    border-bottom: none;

}


.info-label {

    color: #6b7280;

    font-size: 13px;

}


.info-value {

    color: #111827;

    font-size: 14px;

    font-weight: 600;

    text-align: right;

    word-break: break-word;

}


/* =========================================================
   DOWNLOAD HISTORY
========================================================= */

.history-panel {

    background: #ffffff;

    border: 1px solid #e5e7eb;

    border-radius: 16px;

    overflow: hidden;

}


.history-header {

    padding: 20px;

    border-bottom:
        1px solid #e5e7eb;

}


.history-header h2 {

    margin: 0;

    font-size: 19px;

}


.history-description {

    margin-top: 5px;

    color: #6b7280;

    font-size: 13px;

}


.history-table-wrap {

    width: 100%;

    overflow-x: auto;

}


.history-table {

    width: 100%;

    border-collapse: collapse;

}


.history-table th,
.history-table td {

    padding: 13px 15px;

    text-align: left;

    border-bottom:
        1px solid #f0f0f0;

    white-space: nowrap;

}


.history-table th {

    background: #f9fafb;

    color: #6b7280;

    font-size: 12px;

}


.history-table td {

    color: #374151;

    font-size: 13px;

}


.history-table tr:last-child td {

    border-bottom: none;

}


/* =========================================================
   STATUS
========================================================= */

.download-status {

    display: inline-block;

    padding: 5px 9px;

    border-radius: 999px;

    font-size: 11px;

    font-weight: 800;

}


.download-status.completed {

    background: #dcfce7;

    color: #166534;

}


.download-status.started {

    background: #dbeafe;

    color: #1d4ed8;

}


.download-status.failed {

    background: #fee2e2;

    color: #991b1b;

}


/* =========================================================
   EMPTY HISTORY
========================================================= */

.empty-history {

    padding: 45px 20px;

    text-align: center;

    color: #6b7280;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 800px) {

    .account-grid {

        grid-template-columns: 1fr;

    }

    .account-stats {

        grid-template-columns: 1fr;

    }

    .profile-card {

        align-items: flex-start;

        flex-direction: column;

    }

}


@media (max-width: 500px) {

    .profile-name {

        font-size: 20px;

    }

    .profile-avatar {

        width: 58px;

        height: 58px;

        min-width: 58px;

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
            class="active"
            href="account.php"
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

<main class="account-container">


    <!-- PAGE HEADING -->

    <section class="account-heading">

        <div class="eyebrow">
            ACCOUNT
        </div>

        <h1>
            My Account
        </h1>

        <p>
            Manage your profile and view your firmware download activity.
        </p>

    </section>



    <!-- ===================================================
         PROFILE
    ==================================================== -->

    <section class="profile-card">


        <div class="profile-left">


            <div class="profile-avatar">

                <?= htmlspecialchars($initial) ?>

            </div>


            <div>

                <div class="profile-name">

                    <?= htmlspecialchars(
                        $user['name']
                    ) ?>

                </div>


                <div class="profile-email">

                    <?= htmlspecialchars(
                        $user['email']
                    ) ?>

                </div>


                <span class="role-badge">

                    <?= htmlspecialchars(
                        $user['role']
                    ) ?>

                </span>

            </div>


        </div>


        <div>

            <a
                href="change_password.php"
                class="account-button account-button-primary"
            >
                Change Password
            </a>

        </div>


    </section>



    <!-- ===================================================
         STATISTICS
    ==================================================== -->

    <section class="account-stats">


        <div class="account-stat">

            <div class="account-stat-label">
                Total Downloads
            </div>

            <div class="account-stat-value">
                <?= $totalDownloads ?>
            </div>

        </div>


        <div class="account-stat">

            <div class="account-stat-label">
                Successful Downloads
            </div>

            <div class="account-stat-value">
                <?= $completedDownloads ?>
            </div>

        </div>


        <div class="account-stat">

            <div class="account-stat-label">
                Failed Downloads
            </div>

            <div class="account-stat-value">
                <?= $failedDownloads ?>
            </div>

        </div>


    </section>



    <!-- ===================================================
         INFORMATION
    ==================================================== -->

    <section class="account-grid">


        <!-- ACCOUNT INFORMATION -->

        <div class="account-panel">


            <div class="account-panel-header">

                <h2>
                    Profile Information
                </h2>

            </div>


            <div class="account-panel-body">


                <div class="info-row">

                    <span class="info-label">
                        User ID
                    </span>

                    <span class="info-value">

                        #<?= (int)$user['user_id'] ?>

                    </span>

                </div>


                <div class="info-row">

                    <span class="info-label">
                        Full Name
                    </span>

                    <span class="info-value">

                        <?= htmlspecialchars(
                            $user['name']
                        ) ?>

                    </span>

                </div>


                <div class="info-row">

                    <span class="info-label">
                        Email
                    </span>

                    <span class="info-value">

                        <?= htmlspecialchars(
                            $user['email']
                        ) ?>

                    </span>

                </div>


                <div class="info-row">

                    <span class="info-label">
                        Account Type
                    </span>

                    <span class="info-value">

                        <?= ucfirst(
                            htmlspecialchars(
                                $user['role']
                            )
                        ) ?>

                    </span>

                </div>


                <div class="info-row">

                    <span class="info-label">
                        Member Since
                    </span>

                    <span class="info-value">

                        <?= htmlspecialchars(
                            $user['created_at']
                        ) ?>

                    </span>

                </div>


            </div>

        </div>



        <!-- SECURITY -->

        <div class="account-panel">


            <div class="account-panel-header">

                <h2>
                    Security
                </h2>

            </div>


            <div class="account-panel-body">


                <div class="info-row">

                    <span class="info-label">
                        Password
                    </span>

                    <span class="info-value">
                        ••••••••••
                    </span>

                </div>


                <div class="info-row">

                    <span class="info-label">
                        Account Status
                    </span>

                    <span
                        class="download-status completed"
                    >
                        Active
                    </span>

                </div>


                <div style="margin-top:18px;">

                    <a
                        href="change_password.php"
                        class="account-button account-button-primary"
                    >
                        Change Password
                    </a>

                </div>


            </div>

        </div>


    </section>



    <!-- ===================================================
         DOWNLOAD HISTORY
    ==================================================== -->

    <section
        class="history-panel"
        id="download-history"
    >


        <div class="history-header">

            <h2>
                Download History
            </h2>

            <div class="history-description">

                Your latest firmware download activity.

            </div>

        </div>


        <div class="history-table-wrap">


            <?php if (!$downloads): ?>


                <div class="empty-history">

                    You haven't downloaded any firmware yet.

                </div>


            <?php else: ?>


                <table class="history-table">


                    <thead>

                        <tr>

                            <th>
                                ID
                            </th>

                            <th>
                                Device
                            </th>

                            <th>
                                Model
                            </th>

                            <th>
                                Region
                            </th>

                            <th>
                                Version
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Date
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php foreach (
                        $downloads
                        as $download
                    ): ?>


                        <tr>


                            <td>

                                #<?= (int)$download['download_id'] ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $download['device']
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $download['model'] ?? '-'
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $download['region'] ?? '-'
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $download['version'] ?? '-'
                                ) ?>

                            </td>


                            <td>


                                <span
                                    class="
                                        download-status
                                        <?= htmlspecialchars(
                                            $download['status']
                                        ) ?>
                                    "
                                >

                                    <?= htmlspecialchars(
                                        ucfirst(
                                            $download['status']
                                        )
                                    ) ?>

                                </span>


                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $download['started_at']
                                ) ?>

                            </td>


                        </tr>


                    <?php endforeach; ?>


                    </tbody>

                </table>


            <?php endif; ?>


        </div>


    </section>


</main>


</body>

</html>