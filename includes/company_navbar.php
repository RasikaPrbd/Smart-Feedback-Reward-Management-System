<?php
/*
 * -------------------------------------------------------
 * Page    : Company Navigation Bar
 *
 * Purpose : Shared navigation bar for the Company module. Provides links to all company management sections.
 * -------------------------------------------------------
 */

if(session_status() == PHP_SESSION_NONE)
{
    // Initialize the session to track user authentication state.
    session_start();
}

// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar navbar-expand-lg navbar-dark shadow-sm" style="background: #1e2937;">
    <div class="container">

        <!-- Brand -->
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="dashboard.php">
            <i class="bi bi-building"></i>
            Company Portal
        </a>

        <!-- Mobile Toggler -->
        <button class="navbar-toggler" 
                type="button" 
                data-bs-toggle="collapse" 
                data-bs-target="#companyNavbar"
                aria-controls="companyNavbar" 
                aria-expanded="false" 
                aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Content -->
        <div class="collapse navbar-collapse" id="companyNavbar">

            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                        <i class="bi bi-house-door"></i>
                        Dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 <?php echo $current_page == 'create_survey.php' ? 'active' : ''; ?>" href="create_survey.php">
                        <i class="bi bi-plus-circle"></i>
                        Create Survey
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 <?php echo $current_page == 'manage_surveys.php' ? 'active' : ''; ?>" href="manage_surveys.php">
                        <i class="bi bi-clipboard-data"></i>
                        Manage Surveys
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 <?php echo $current_page == 'create_reward.php' ? 'active' : ''; ?>" href="create_reward.php">
                        <i class="bi bi-gift"></i>
                        Create Reward
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 <?php echo $current_page == 'manage_rewards.php' ? 'active' : ''; ?>" href="manage_rewards.php">
                        <i class="bi bi-gift-fill"></i>
                        Manage Rewards
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 <?php echo $current_page == 'analytics.php' ? 'active' : ''; ?>" href="analytics.php">
                        <i class="bi bi-graph-up-arrow"></i>
                        Analytics
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 <?php echo $current_page == 'survey_reports.php' ? 'active' : ''; ?>" href="survey_reports.php">
                        <i class="bi bi-file-earmark-bar-graph"></i>
                        Reports
                    </a>
                </li>

            </ul>

            <!-- Right Side -->
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown">
                        <?php echo htmlspecialchars($_SESSION['company_name'] ?? 'Company'); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item text-danger d-flex align-items-center gap-2" href="logout.php">
                                <i class="bi bi-box-arrow-right"></i>
                                Logout
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>

        </div>

    </div>
</nav>