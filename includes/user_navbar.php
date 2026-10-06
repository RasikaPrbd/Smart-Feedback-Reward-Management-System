<?php
/*
 * -------------------------------------------------------
 * Page    : User Navigation Bar
 *
 * Purpose : Shared navigation bar for the User module. Provides links to surveys, rewards, and user history.
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
            <i class="bi bi-chat-square-text"></i>
            Feedback System
        </a>

        <!-- Mobile Toggler -->
        <button class="navbar-toggler" 
                type="button" 
                data-bs-toggle="collapse" 
                data-bs-target="#userNavbar"
                aria-controls="userNavbar" 
                aria-expanded="false" 
                aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Content -->
        <div class="collapse navbar-collapse" id="userNavbar">

            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                        <i class="bi bi-house-door"></i>
                        Dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 <?php echo $current_page == 'view_surveys.php' ? 'active' : ''; ?>" href="view_surveys.php">
                        <i class="bi bi-list-check"></i>
                        Surveys
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 <?php echo ($current_page == 'my_surveys.php' || $current_page == 'view_survey_response.php') ? 'active' : ''; ?>" href="my_surveys.php">
                        <i class="bi bi-file-earmark-check"></i>
                        My Surveys
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 <?php echo $current_page == 'view_rewards.php' ? 'active' : ''; ?>" href="view_rewards.php">
                        <i class="bi bi-gift"></i>
                        Rewards
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 <?php echo $current_page == 'my_rewards.php' ? 'active' : ''; ?>" href="my_rewards.php">
                        <i class="bi bi-gift"></i>
                        My Rewards
                    </a>
                </li>

            </ul>

            <!-- Right Side -->
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown">
                        <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?>
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