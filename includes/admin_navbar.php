<?php
/*
 * -------------------------------------------------------
 * Page    : Admin Navigation Bar
 *
 * Purpose : Shared navigation bar for the Admin module. Provides links to all admin sections and highlights the current active page.
 * -------------------------------------------------------
 */

$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">

    <div class="container">

        <a class="navbar-brand fw-bold d-flex align-items-center gap-2"
           href="../admin/dashboard.php">
            <i class="bi bi-speedometer2"></i>
            Admin Panel
        </a>

        <button class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#adminNavbar"
                aria-controls="adminNavbar"
                aria-expanded="false"
                aria-label="Toggle navigation">

            <span class="navbar-toggler-icon"></span>

        </button>

        <div class="collapse navbar-collapse" id="adminNavbar">

            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 <?= ($current_page == 'dashboard.php') ? 'active' : ''; ?>"
                       href="../admin/dashboard.php">

                        <i class="bi bi-house-door"></i>
                        Dashboard

                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 <?= ($current_page == 'manage_users.php') ? 'active' : ''; ?>"
                       href="../admin/manage_users.php">

                        <i class="bi bi-people-fill"></i>
                        Manage Users

                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 <?= ($current_page == 'manage_companies.php') ? 'active' : ''; ?>"
                       href="../admin/manage_companies.php">

                        <i class="bi bi-building"></i>
                        Manage Companies

                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 <?= ($current_page == 'manage_surveys.php') ? 'active' : ''; ?>"
                       href="../admin/manage_surveys.php">

                        <i class="bi bi-clipboard-data"></i>
                        Manage Surveys

                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 <?= ($current_page == 'manage_rewards.php') ? 'active' : ''; ?>"
                       href="../admin/manage_rewards.php">

                        <i class="bi bi-gift"></i>
                        Manage Rewards

                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 <?= ($current_page == 'manage_redemptions.php') ? 'active' : ''; ?>"
                       href="../admin/manage_redemptions.php">

                        <i class="bi bi-ticket-perforated"></i>
                        Redemptions

                    </a>
                </li>

            </ul>

            <div class="d-flex align-items-center gap-3">

                <span class="text-white-50 small d-none d-md-inline">

                    <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?>

                </span>

                <a href="../admin/logout.php"
                   class="btn btn-outline-light d-flex align-items-center gap-2">

                    <i class="bi bi-box-arrow-right"></i>
                    Logout

                </a>

            </div>

        </div>

    </div>

</nav>

<style>

.navbar .nav-link {
    padding: 8px 14px;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.navbar .nav-link:hover {
    background-color: rgba(255,255,255,0.08);
}

.navbar .nav-link.active {
    background-color: rgba(255,255,255,0.15);
    color: #ffffff !important;
    font-weight: 600;
}

</style>