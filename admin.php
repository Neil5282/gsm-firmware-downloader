<?php

session_start();

if (
    !isset($_SESSION['user']) ||
    ($_SESSION['user']['role'] ?? '') !== 'admin'
) {
    http_response_code(403);
    exit('Forbidden');
}

require __DIR__ . '/db.php';

$pdo = db();

/*
|--------------------------------------------------------------------------
| Statistics
|--------------------------------------------------------------------------
*/

// Total users
$totalUsers = (int)$pdo->query("
    SELECT COUNT(*)
    FROM users
")->fetchColumn();


// Total downloads
$totalDownloads = (int)$pdo->query("
    SELECT COUNT(*)
    FROM download_logs
")->fetchColumn();


// Completed downloads
$completedDownloads = (int)$pdo->query("
    SELECT COUNT(*)
    FROM download_logs
    WHERE status = 'completed'
")->fetchColumn();


// Success rate
$successRate = $totalDownloads > 0
    ? round(($completedDownloads / $totalDownloads) * 100, 1)
    : 0;


// Most downloaded device
$mostDownloaded = $pdo->query("
    SELECT device, COUNT(*) AS download_count
    FROM download_logs
    GROUP BY device
    ORDER BY download_count DESC
    LIMIT 1
")->fetch();

$mostDownloadedDevice = $mostDownloaded
    ? $mostDownloaded['device']
    : '—';

$mostDownloadedCount = $mostDownloaded
    ? (int)$mostDownloaded['download_count']
    : 0;


/*
|--------------------------------------------------------------------------
| Users
|--------------------------------------------------------------------------
*/

$users = $pdo->query("
    SELECT
        user_id,
        name,
        email,
        role,
        created_at
    FROM users
    ORDER BY user_id DESC
")->fetchAll();


/*
|--------------------------------------------------------------------------
| Download Logs
|--------------------------------------------------------------------------
*/

$logs = $pdo->query("
    SELECT
        d.*,
        u.name AS user_name
    FROM download_logs d
    LEFT JOIN users u
        ON u.user_id = d.user_id
    ORDER BY d.download_id DESC
    LIMIT 50
")->fetchAll();

?>

<!doctype html>

<html lang="en">

<head>

<meta charset="utf-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Admin Panel - GSM Firmware Downloader</title>

<link rel="stylesheet" href="assets/style.css">

<style>

/* =========================================================
   ADMIN PAGE
========================================================= */

.admin-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 30px 20px 60px;
}


/* =========================================================
   STATISTICS
========================================================= */

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
    margin-bottom: 25px;
}

.stat-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    padding: 22px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.04);
}

.stat-label {
    color: #6b7280;
    font-size: 14px;
    margin-bottom: 10px;
}

.stat-value {
    font-size: 28px;
    font-weight: 800;
    color: #111827;
    word-break: break-word;
}

.stat-sub {
    margin-top: 8px;
    font-size: 13px;
    color: #6b7280;
}


/* =========================================================
   PANELS
========================================================= */

.admin-panel {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    margin-bottom: 25px;
    overflow: hidden;
    box-shadow: 0 8px 25px rgba(0,0,0,0.04);
}

.admin-panel-header {
    padding: 20px 22px;
    border-bottom: 1px solid #e5e7eb;

    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 15px;
}

.admin-panel-header h2 {
    margin: 0;
    font-size: 20px;
}


/* =========================================================
   BUTTONS
========================================================= */

.admin-btn {
    border: none;
    border-radius: 9px;
    padding: 9px 14px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
}

.admin-btn-primary {
    background: #111827;
    color: white;
}

.admin-btn-primary:hover {
    opacity: .9;
}

.admin-btn-edit {
    background: #eef2ff;
    color: #3730a3;
}

.admin-btn-delete {
    background: #fee2e2;
    color: #991b1b;
}


/* =========================================================
   TABLE
========================================================= */

.admin-table-wrap {
    width: 100%;
    overflow-x: auto;
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
}

.admin-table th,
.admin-table td {
    padding: 14px 16px;
    text-align: left;
    border-bottom: 1px solid #f0f0f0;
    white-space: nowrap;
}

.admin-table th {
    background: #f9fafb;
    font-size: 13px;
    color: #6b7280;
}

.admin-table td {
    font-size: 14px;
    color: #374151;
}

.admin-table tr:last-child td {
    border-bottom: none;
}


/* =========================================================
   ROLE / STATUS
========================================================= */

.role-pill,
.status-pill {
    display: inline-block;
    padding: 5px 9px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
}

.role-user {
    background: #eef2ff;
    color: #3730a3;
}

.role-admin {
    background: #fef3c7;
    color: #92400e;
}

.status-completed {
    background: #dcfce7;
    color: #166534;
}

.status-started {
    background: #dbeafe;
    color: #1d4ed8;
}

.status-failed {
    background: #fee2e2;
    color: #991b1b;
}


/* =========================================================
   ACTION BUTTONS
========================================================= */

.user-actions {
    display: flex;
    gap: 7px;
}


/* =========================================================
   MODAL
========================================================= */

.modal-overlay {
    position: fixed;
    inset: 0;

    background: rgba(0,0,0,0.55);

    display: none;
    align-items: center;
    justify-content: center;

    padding: 20px;

    z-index: 9999;
}

.modal-overlay.show {
    display: flex;
}

.modal {
    width: 100%;
    max-width: 500px;

    background: white;
    border-radius: 18px;

    box-shadow: 0 25px 70px rgba(0,0,0,0.25);

    overflow: hidden;
}

.modal-header {
    padding: 20px 22px;

    display: flex;
    justify-content: space-between;
    align-items: center;

    border-bottom: 1px solid #e5e7eb;
}

.modal-header h3 {
    margin: 0;
}

.modal-close {
    border: none;
    background: transparent;

    font-size: 25px;

    cursor: pointer;

    color: #6b7280;
}

.modal-body {
    padding: 22px;
}

.form-group {
    margin-bottom: 17px;
}

.form-group label {
    display: block;

    margin-bottom: 7px;

    font-size: 13px;
    font-weight: 600;

    color: #374151;
}

.form-group input,
.form-group select {
    width: 100%;

    box-sizing: border-box;

    padding: 11px 12px;

    border: 1px solid #d1d5db;

    border-radius: 9px;

    font-size: 14px;

    outline: none;
}

.form-group input:focus,
.form-group select:focus {
    border-color: #111827;
}

.password-note {
    margin-top: 5px;

    font-size: 12px;

    color: #6b7280;
}

.modal-footer {
    padding: 17px 22px;

    border-top: 1px solid #e5e7eb;

    display: flex;
    justify-content: flex-end;

    gap: 10px;
}

.cancel-btn {
    background: #f3f4f6;
    color: #374151;
}


/* =========================================================
   EMPTY
========================================================= */

.empty {
    padding: 35px;

    text-align: center;

    color: #6b7280;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 1100px) {

    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }

}

@media (max-width: 650px) {

    .stats-grid {
        grid-template-columns: 1fr;
    }

    .admin-panel-header {
        align-items: flex-start;
        flex-direction: column;
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

        <span class="brand-mark">GF</span>

        <div>

            <b>GSM Firmware Downloader</b>

            <small>Admin Control Center</small>

        </div>

    </div>


    <nav>

        <a href="dashboard.php">
            Downloader
        </a>

        <a class="active" href="admin.php">
            Admin Panel
        </a>

        <a href="logout.php">
            Logout
        </a>

    </nav>

</header>



<!-- =======================================================
     MAIN
======================================================= -->

<main class="admin-container">


<!-- =======================================================
     HERO
======================================================= -->

<section class="hero">

    <div>

        <div class="eyebrow">
            ADMIN PANEL
        </div>

        <h1>
            System overview
        </h1>

        <p>
            Manage users and review firmware download activity.
        </p>

    </div>

</section>



<!-- =======================================================
     STATISTICS
======================================================= -->

<section class="stats-grid">


    <!-- TOTAL USERS -->

    <div class="stat-card">

        <div class="stat-label">
            Total Users
        </div>

        <div class="stat-value">
            <?= $totalUsers ?>
        </div>

        <div class="stat-sub">
            Registered accounts
        </div>

    </div>



    <!-- TOTAL DOWNLOADS -->

    <div class="stat-card">

        <div class="stat-label">
            Total Downloads
        </div>

        <div class="stat-value">
            <?= $totalDownloads ?>
        </div>

        <div class="stat-sub">
            Download attempts recorded
        </div>

    </div>



    <!-- SUCCESS RATE -->

    <div class="stat-card">

        <div class="stat-label">
            Success Rate
        </div>

        <div class="stat-value">
            <?= $successRate ?>%
        </div>

        <div class="stat-sub">
            Completed downloads
        </div>

    </div>



    <!-- MOST DOWNLOADED DEVICE -->

    <div class="stat-card">

        <div class="stat-label">
            Most Downloaded Device
        </div>

        <div class="stat-value" style="font-size:21px;">

            <?= htmlspecialchars($mostDownloadedDevice) ?>

        </div>

        <div class="stat-sub">

            <?= $mostDownloadedCount ?>
            downloads

        </div>

    </div>


</section>



<!-- =======================================================
     USER MANAGEMENT
======================================================= -->

<section class="admin-panel">


    <div class="admin-panel-header">

        <div>

            <h2>
                User Management
            </h2>

            <div style="font-size:13px;color:#6b7280;margin-top:4px;">

                Add, edit and delete system users.

            </div>

        </div>


        <button
            class="admin-btn admin-btn-primary"
            onclick="openAddUser()"
        >
            + Add User
        </button>

    </div>



    <div class="admin-table-wrap">

        <table class="admin-table">

            <thead>

                <tr>

                    <th>ID</th>

                    <th>Name</th>

                    <th>Email</th>

                    <th>Role</th>

                    <th>Created</th>

                    <th>Actions</th>

                </tr>

            </thead>


            <tbody>


            <?php if (!$users): ?>

                <tr>

                    <td
                        colspan="6"
                        class="empty"
                    >
                        No users found.
                    </td>

                </tr>

            <?php endif; ?>


            <?php foreach ($users as $user): ?>

                <tr>

                    <td>
                        <?= (int)$user['user_id'] ?>
                    </td>


                    <td>

                        <?= htmlspecialchars(
                            $user['name']
                        ) ?>

                    </td>


                    <td>

                        <?= htmlspecialchars(
                            $user['email']
                        ) ?>

                    </td>


                    <td>

                        <?php if ($user['role'] === 'admin'): ?>

                            <span class="role-pill role-admin">
                                Admin
                            </span>

                        <?php else: ?>

                            <span class="role-pill role-user">
                                User
                            </span>

                        <?php endif; ?>

                    </td>


                    <td>

                        <?= htmlspecialchars(
                            $user['created_at']
                        ) ?>

                    </td>


                    <td>

                        <div class="user-actions">


                            <!-- EDIT -->

                            <button
                                class="admin-btn admin-btn-edit"
                                onclick='openEditUser(
                                    <?= json_encode($user, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>
                                )'
                            >
                                Edit
                            </button>


                            <!-- DELETE -->

                            <button
                                class="admin-btn admin-btn-delete"
                                onclick="deleteUser(
                                    <?= (int)$user['user_id'] ?>
                                )"
                            >
                                Delete
                            </button>


                        </div>

                    </td>

                </tr>

            <?php endforeach; ?>


            </tbody>

        </table>

    </div>

</section>



<!-- =======================================================
     DOWNLOAD LOGS
======================================================= -->

<section class="admin-panel">


    <div class="admin-panel-header">

        <div>

            <h2>
                Download Logs
            </h2>

            <div style="font-size:13px;color:#6b7280;margin-top:4px;">

                Latest firmware download activity.

            </div>

        </div>


        <span class="role-pill role-user">

            <?= count($logs) ?>
            recent

        </span>

    </div>



    <div class="admin-table-wrap">

        <table class="admin-table">

            <thead>

                <tr>

                    <th>ID</th>

                    <th>User</th>

                    <th>Device</th>

                    <th>Model</th>

                    <th>Region</th>

                    <th>Version</th>

                    <th>Status</th>

                    <th>Started</th>

                </tr>

            </thead>


            <tbody>


            <?php if (!$logs): ?>

                <tr>

                    <td
                        colspan="8"
                        class="empty"
                    >
                        No download activity yet.
                    </td>

                </tr>

            <?php endif; ?>


            <?php foreach ($logs as $log): ?>

                <tr>

                    <td>
                        <?= (int)$log['download_id'] ?>
                    </td>


                    <td>

                        <?= htmlspecialchars(
                            $log['user_name'] ?? 'Unknown'
                        ) ?>

                    </td>


                    <td>

                        <?= htmlspecialchars(
                            $log['device']
                        ) ?>

                    </td>


                    <td>

                        <?= htmlspecialchars(
                            $log['model'] ?? '-'
                        ) ?>

                    </td>


                    <td>

                        <?= htmlspecialchars(
                            $log['region'] ?? '-'
                        ) ?>

                    </td>


                    <td>

                        <?= htmlspecialchars(
                            $log['version'] ?? '-'
                        ) ?>

                    </td>


                    <td>

                        <?php

                        $status = $log['status'];

                        $statusClass =
                            'status-' . $status;

                        ?>

                        <span
                            class="status-pill <?= $statusClass ?>"
                        >

                            <?= htmlspecialchars(
                                ucfirst($status)
                            ) ?>

                        </span>

                    </td>


                    <td>

                        <?= htmlspecialchars(
                            $log['started_at']
                        ) ?>

                    </td>

                </tr>

            <?php endforeach; ?>


            </tbody>

        </table>

    </div>

</section>


</main>



<!-- =======================================================
     ADD / EDIT USER MODAL
======================================================= -->

<div
    id="userModal"
    class="modal-overlay"
>


    <div class="modal">


        <div class="modal-header">

            <h3 id="modalTitle">
                Add User
            </h3>


            <button
                class="modal-close"
                onclick="closeUserModal()"
            >
                ×
            </button>

        </div>



        <form id="userForm">


            <div class="modal-body">


                <input
                    type="hidden"
                    name="action"
                    id="formAction"
                    value="add"
                >


                <input
                    type="hidden"
                    name="user_id"
                    id="userId"
                    value=""
                >



                <!-- NAME -->

                <div class="form-group">

                    <label>
                        Name
                    </label>

                    <input
                        type="text"
                        name="name"
                        id="userName"
                        required
                        placeholder="Enter full name"
                    >

                </div>



                <!-- EMAIL -->

                <div class="form-group">

                    <label>
                        Email
                    </label>

                    <input
                        type="email"
                        name="email"
                        id="userEmail"
                        required
                        placeholder="Enter email address"
                    >

                </div>



                <!-- PASSWORD -->

                <div class="form-group">

                    <label>
                        Password
                    </label>

                    <input
                        type="password"
                        name="password"
                        id="userPassword"
                        placeholder="Enter password"
                    >

                    <div
                        class="password-note"
                        id="passwordNote"
                    >
                        Minimum 6 characters.
                    </div>

                </div>



                <!-- ROLE -->

                <div class="form-group">

                    <label>
                        Role
                    </label>

                    <select
                        name="role"
                        id="userRole"
                    >

                        <option value="user">
                            User
                        </option>

                        <option value="admin">
                            Admin
                        </option>

                    </select>

                </div>


            </div>



            <div class="modal-footer">

                <button
                    type="button"
                    class="admin-btn cancel-btn"
                    onclick="closeUserModal()"
                >
                    Cancel
                </button>


                <button
                    type="submit"
                    class="admin-btn admin-btn-primary"
                    id="saveUserBtn"
                >
                    Create User
                </button>

            </div>


        </form>

    </div>

</div>



<!-- =======================================================
     JAVASCRIPT
======================================================= -->

<script>

const userModal =
    document.getElementById('userModal');

const userForm =
    document.getElementById('userForm');


/* =========================================================
   OPEN ADD USER
========================================================= */

function openAddUser() {

    userForm.reset();

    document.getElementById('formAction').value =
        'add';

    document.getElementById('userId').value =
        '';

    document.getElementById('modalTitle').textContent =
        'Add User';

    document.getElementById('saveUserBtn').textContent =
        'Create User';

    document.getElementById('userPassword').required =
        true;

    document.getElementById('passwordNote').textContent =
        'Minimum 6 characters.';

    userModal.classList.add('show');

}


/* =========================================================
   OPEN EDIT USER
========================================================= */

function openEditUser(user) {

    document.getElementById('formAction').value =
        'edit';

    document.getElementById('userId').value =
        user.user_id;

    document.getElementById('userName').value =
        user.name;

    document.getElementById('userEmail').value =
        user.email;

    document.getElementById('userRole').value =
        user.role;

    document.getElementById('userPassword').value =
        '';

    document.getElementById('userPassword').required =
        false;

    document.getElementById('modalTitle').textContent =
        'Edit User';

    document.getElementById('saveUserBtn').textContent =
        'Save Changes';

    document.getElementById('passwordNote').textContent =
        'Leave password empty to keep the current password.';

    userModal.classList.add('show');

}


/* =========================================================
   CLOSE MODAL
========================================================= */

function closeUserModal() {

    userModal.classList.remove('show');

}


/* =========================================================
   CLICK OUTSIDE MODAL
========================================================= */

userModal.addEventListener(
    'click',
    function(event) {

        if (event.target === userModal) {

            closeUserModal();

        }

    }
);


/* =========================================================
   SUBMIT ADD / EDIT
========================================================= */

userForm.addEventListener(
    'submit',
    async function(event) {

        event.preventDefault();

        const button =
            document.getElementById('saveUserBtn');

        button.disabled = true;

        button.textContent =
            'Saving...';


        try {

            const formData =
                new FormData(userForm);


            const response =
                await fetch(
                    'user_action.php',
                    {
                        method: 'POST',
                        body: formData
                    }
                );


            const data =
                await response.json();


            if (!data.success) {

                alert(
                    data.error ||
                    'Unable to save user.'
                );

                return;

            }


            alert(data.message);

            closeUserModal();

            location.reload();


        } catch (error) {

            alert(
                'Server error. Please try again.'
            );

        } finally {

            button.disabled = false;

            button.textContent =
                document.getElementById('formAction').value === 'add'
                    ? 'Create User'
                    : 'Save Changes';

        }

    }
);


/* =========================================================
   DELETE USER
========================================================= */

async function deleteUser(userId) {

    if (
        !confirm(
            'Are you sure you want to delete this user?'
        )
    ) {
        return;
    }


    const formData =
        new FormData();

    formData.append(
        'action',
        'delete'
    );

    formData.append(
        'user_id',
        userId
    );


    try {

        const response =
            await fetch(
                'user_action.php',
                {
                    method: 'POST',
                    body: formData
                }
            );


        const data =
            await response.json();


        if (!data.success) {

            alert(
                data.error ||
                'Unable to delete user.'
            );

            return;

        }


        alert(data.message);

        location.reload();


    } catch (error) {

        alert(
            'Server error. Please try again.'
        );

    }

}

</script>


</body>

</html>