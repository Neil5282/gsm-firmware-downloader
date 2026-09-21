<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$u = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>GSM Firmware Downloader</title>

    <link rel="stylesheet" href="assets/style.css">

</head>


<body>


<!-- =====================================================
     HEADER
===================================================== -->

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

    <style>

/* =========================================================
   USER ACCOUNT DROPDOWN - DASHBOARD
========================================================= */

.top nav .user-menu {
    position: relative !important;
    display: inline-block !important;
    height: auto !important;
}


/* User button */

.top nav .user-menu-button {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 8px !important;

    height: 42px !important;
    padding: 5px 14px !important;

    margin: 0 !important;

    border: 0 !important;
    border-radius: 9px !important;

    background: transparent !important;

    color: #dbe7ec !important;

    font-family: inherit !important;
    font-size: 13px !important;
    font-weight: 600 !important;

    cursor: pointer !important;

    white-space: nowrap !important;
}


/* Circle */

.top nav .user-menu-button .user-circle {
    display: flex !important;

    width: 32px !important;
    height: 32px !important;
    min-width: 32px !important;

    align-items: center !important;
    justify-content: center !important;

    border-radius: 50% !important;

    background: #111827 !important;
    border: 1px solid #22d3ee !important;

    color: white !important;

    font-size: 13px !important;
    font-weight: 800 !important;
}


/* Name */

.top nav .user-menu-button .user-menu-name {
    display: inline-block !important;

    max-width: 130px !important;

    overflow: hidden !important;
    text-overflow: ellipsis !important;
    white-space: nowrap !important;

    color: inherit !important;
}


/* Arrow */

.top nav .user-menu-button .user-menu-arrow {
    display: inline-block !important;

    color: #9ca3af !important;

    font-size: 11px !important;

    transition: transform .2s ease !important;
}

.top nav .user-menu.open .user-menu-arrow {
    transform: rotate(180deg) !important;
}


/* =========================================================
   DROPDOWN BOX
========================================================= */

.top nav .user-menu .user-dropdown {

    position: absolute !important;

    top: calc(100% + 6px) !important;
    right: 0 !important;
    left: auto !important;

    display: none !important;

    width: 245px !important;
    min-width: 245px !important;

    height: auto !important;

    padding: 7px !important;
    margin: 0 !important;

    box-sizing: border-box !important;

    flex-direction: column !important;
    align-items: stretch !important;

    background: #ffffff !important;

    border: 1px solid #dbe3e8 !important;
    border-radius: 12px !important;

    box-shadow:
        0 18px 45px rgba(0,0,0,.25) !important;

    z-index: 999999 !important;
}


/* Open */

.top nav .user-menu.open .user-dropdown {
    display: flex !important;
}


/* =========================================================
   DROPDOWN ITEMS
========================================================= */

.top nav .user-menu .user-dropdown a {

    display: flex !important;

    position: static !important;

    width: 100% !important;
    height: auto !important;

    box-sizing: border-box !important;

    align-items: center !important;
    justify-content: flex-start !important;

    gap: 0 !important;

    padding: 11px 12px !important;
    margin: 0 !important;

    border: 0 !important;

    border-radius: 8px !important;

    background: transparent !important;

    color: #374151 !important;

    text-decoration: none !important;

    font-size: 13px !important;
    font-weight: 500 !important;

    white-space: nowrap !important;
}


/* Hover */

.top nav .user-menu .user-dropdown a:hover {

    background: #f3f4f6 !important;

    color: #111827 !important;

}


/* Icons */

.top nav .user-menu .user-dropdown .dropdown-icon {

    display: inline-flex !important;

    width: 28px !important;
    min-width: 28px !important;

    align-items: center !important;
    justify-content: center !important;

    margin-right: 5px !important;

    font-size: 15px !important;
}


/* Divider */

.top nav .user-menu .user-dropdown .dropdown-divider {

    display: block !important;

    width: calc(100% - 8px) !important;

    height: 1px !important;
    min-height: 1px !important;

    margin: 6px 4px !important;
    padding: 0 !important;

    background: #e5e7eb !important;
}


/* Logout */

.top nav .user-menu .user-dropdown a.dropdown-logout {

    color: #b91c1c !important;

}

.top nav .user-menu .user-dropdown a.dropdown-logout:hover {

    background: #fef2f2 !important;

    color: #991b1b !important;

}


/* Mobile */

@media (max-width: 700px) {

    .top nav .user-menu-button .user-menu-name {
        display: none !important;
    }

    .top nav .user-menu .user-dropdown {
        width: 235px !important;
        min-width: 235px !important;
        right: 0 !important;
    }

}

</style>


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


    <!-- USER ACCOUNT -->

    <div class="user-menu">


        <button
            type="button"
            class="user-menu-button"
            onclick="toggleUserMenu(event)"
        >

            <span class="user-circle">

                <?php

                $displayName =
                    $_SESSION['user']['name']
                    ?? 'User';

                echo htmlspecialchars(
                    strtoupper(
                        substr($displayName, 0, 1)
                    )
                );

                ?>

            </span>


            <span class="user-menu-name">

                <?= htmlspecialchars($displayName) ?>

            </span>


            <span class="user-menu-arrow">
                ▾
            </span>

        </button>


        <!-- DROPDOWN -->

        <div class="user-dropdown">


            <a href="account.php">

                <span class="dropdown-icon">
                    👤
                </span>

                <span>
                    Profile Information
                </span>

            </a>


            <a href="change_password.php">

                <span class="dropdown-icon">
                    🔐
                </span>

                <span>
                    Change Password
                </span>

            </a>


            <a href="account.php#download-history">

                <span class="dropdown-icon">
                    📥
                </span>

                <span>
                    Download History
                </span>

            </a>


            <div class="dropdown-divider"></div>


            <a
                href="logout.php"
                class="dropdown-logout"
            >

                <span class="dropdown-icon">
                    🚪
                </span>

                <span>
                    Logout
                </span>

            </a>


        </div>

    </div>


    <?php if (
        ($_SESSION['user']['role'] ?? '') === 'admin'
    ): ?>

        <a href="admin.php">
            ADMIN PANEL
        </a>

    <?php endif; ?>


</nav>

      

</header>



<!-- =====================================================
     MAIN CONTENT
===================================================== -->

<main>


    <!-- =================================================
         HERO
    ================================================== -->

    <section class="hero">

        <div class="eyebrow">
            GSM FIRMWARE ARCHIVE
        </div>


        <h1>
            Find the firmware
            <br>
            <span>you need.</span>
        </h1>


        <p>
            Search the live OTA firmware catalog by device,
            region and firmware version.
        </p>

    </section>



    <!-- =================================================
         FIRMWARE SEARCH
    ================================================== -->

    <section
        class="panel"
        id="services"
    >


        <!-- PANEL HEADER -->

        <div class="panel-head">

            <div>

                <div class="eyebrow">
                    LIVE OTA CATALOG
                </div>


                <h2>
                    Firmware Search
                </h2>


                <p>
                    Select your device, region and firmware
                    version to find the available release.
                </p>

            </div>


            <button
                class="ghost"
                type="button"
                onclick="resetFilters()"
            >
                RESET
            </button>

        </div>



        <!-- =================================================
             THREE SELECTORS
             
             IMPORTANT:
             Device
             Region
             Version
             
             Same layout as original API
        ================================================== -->

        <div class="filters">


            <!-- DEVICE -->

            <label>

                <span>
                    Device
                </span>


                <select id="device">

                    <option value="">
                        Loading devices...
                    </option>

                </select>

            </label>



            <!-- REGION -->

            <label>

                <span>
                    Region
                </span>


                <select
                    id="region"
                    disabled
                >

                    <option value="">
                        Choose a device first...
                    </option>

                </select>

            </label>



            <!-- VERSION -->

            <label>

                <span>
                    Version
                </span>


                <select
                    id="version"
                    disabled
                >

                    <option value="">
                        Choose a region first...
                    </option>

                </select>

            </label>


        </div>



        <!-- =================================================
             RESOLVE BUTTON
        ================================================== -->

        <div class="resolve-area">

            <button
                id="resolveBtn"
                class="primary"
                type="button"
            >

                Resolve OTA Link

            </button>

        </div>



        <!-- =================================================
             RESULTS
        ================================================== -->

        <div id="results">


            <div class="empty-state">

                <div class="empty-icon">
                    ↓
                </div>


                <h3>
                    No firmware selected
                </h3>


                <p>
                    Choose a device, region and version above
                    to view firmware information.
                </p>

            </div>


        </div>


    </section>



    <!-- =================================================
         INFORMATION CARDS
    ================================================== -->

    <section class="info-grid">


        <!-- OTA -->

        <div class="info-card">

            <span class="info-number">
                01
            </span>


            <h3>
                OTA Catalog
            </h3>


            <p>
                Firmware information is retrieved from the
                connected live OTA catalog.
            </p>

        </div>



        <!-- DOWNLOAD -->

        <div class="info-card">

            <span class="info-number">
                02
            </span>


            <h3>
                Direct Downloads
            </h3>


            <p>
                Download firmware using the original source
                provided by the OTA catalog.
            </p>

        </div>



        <!-- HISTORY -->

        <div class="info-card">

            <span class="info-number">
                03
            </span>


            <h3>
                Download History
            </h3>


            <p>
                Firmware download activity can be recorded
                in the system database.
            </p>

        </div>


    </section>



    <!-- =================================================
         FAQ
    ================================================== -->

    <section
        class="content-section"
        id="faq"
    >


        <div class="eyebrow">
            HELP
        </div>


        <h2>
            Frequently Asked Questions
        </h2>



        <div class="faq-list">


            <details>

                <summary>
                    Where does the firmware come from?
                </summary>


                <p>
                    Firmware information is retrieved from the
                    connected live OTA firmware catalog.
                </p>

            </details>



            <details>

                <summary>
                    Do I need to upload firmware to this website?
                </summary>


                <p>
                    No. The system uses the live OTA catalog and
                    redirects users to the available firmware source.
                </p>

            </details>



            <details>

                <summary>
                    Can I see different firmware regions?
                </summary>


                <p>
                    Yes. After selecting a device, the available
                    firmware regions are loaded automatically.
                </p>

            </details>



            <details>

                <summary>
                    Can I select different firmware versions?
                </summary>


                <p>
                    Yes. After selecting a region, all available
                    firmware releases for that device and region
                    are displayed.
                </p>

            </details>


        </div>


    </section>



    <!-- =================================================
         ACCOUNT
    ================================================== -->

    <section
        class="account-section"
        id="account"
    >


        <div>

            <div class="eyebrow">
                ACCOUNT
            </div>


            <h2>

                Welcome,
                <?= htmlspecialchars($u['name']) ?>

            </h2>


            <p>

                <?= htmlspecialchars($u['email']) ?>

            </p>

        </div>



        <div class="account-role">

            <?= htmlspecialchars(
                strtoupper($u['role'])
            ) ?>

        </div>


    </section>


</main>



<!-- =====================================================
     JAVASCRIPT
===================================================== -->

<script src="assets/app.js"></script>



<script>

function toggleUserMenu(event) {

    event.stopPropagation();

    const menu =
        document.querySelector('.user-menu');

    if (!menu) {
        return;
    }

    menu.classList.toggle('open');

}


/* Close dropdown when clicking elsewhere */

document.addEventListener(
    'click',
    function(event) {

        const menu =
            document.querySelector('.user-menu');

        if (!menu) {
            return;
        }

        if (!menu.contains(event.target)) {

            menu.classList.remove('open');

        }

    }
);


/* Close with Escape */

document.addEventListener(
    'keydown',
    function(event) {

        if (event.key === 'Escape') {

            const menu =
                document.querySelector('.user-menu');

            if (menu) {

                menu.classList.remove('open');

            }

        }

    }
);

</script>


</body>

</html>