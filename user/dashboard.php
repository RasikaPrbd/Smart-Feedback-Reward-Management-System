<?php

/*
 * -------------------------------------------------------
 * Page    : User Dashboard
 *
 * Purpose : Central overview for the logged-in user. Shows earned points, completed surveys, redeemed rewards, and available surveys.
 * -------------------------------------------------------
 */

// Initialize the session to track user authentication state.
session_start();

// Include the database connection configuration to interact with the database.
include("../config/db.php");

// Verify that the user is logged in before granting access.
if (!isset($_SESSION['user_id'])) {
    // Redirect the user to the appropriate page after the action completes.
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* Get user details */
$user_sql = "
SELECT *
FROM users
WHERE user_id='$user_id'
";
$user_result = $conn->query($user_sql);
$user = $user_result->fetch_assoc();

/* Count completed surveys */
$response_sql = "
SELECT COUNT(*) AS total_surveys
FROM responses
WHERE user_id='$user_id'
";
$response_result = $conn->query($response_sql);
$response_data = $response_result->fetch_assoc();

/* Count redeemed rewards */
$reward_sql = "
SELECT COUNT(*) AS total_rewards
FROM redemptions
WHERE user_id='$user_id'
";
$reward_result = $conn->query($reward_sql);
$reward_data = $reward_result->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SurveyRewards</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --success: #10b981;
            --dark: #1e2937;
            --light-bg: #f8fafc;
        }
        
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
        }
        
        .navbar {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }
        
        .dashboard-container {
            padding-top: 2rem;
            padding-bottom: 4rem;
        }
        
        .hero-section {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.15);
            padding: 3.5rem 2.5rem;
            margin-bottom: 2.5rem;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 450px;
            height: 450px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.12) 0%, transparent 70%);
            border-radius: 50%;
            z-index: 0;
        }
        
        .welcome-text {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
        }
        
        .user-avatar {
            width: 85px;
            height: 85px;
            background: linear-gradient(135deg, #6366f1, #a5b4fc);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2.5rem;
            font-weight: 700;
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.3);
        }
        
        .stat-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            border-radius: 18px;
            overflow: hidden;
            height: 100%;
        }
        
        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12) !important;
        }
        
        .points-card {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: white;
        }
        
        .points-card .card-body {
            padding: 2rem 1.5rem;
        }
        
        .points-value {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1;
        }
        
        .stat-icon-wrapper {
            width: 54px;
            height: 54px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .stat-icon {
            font-size: 1.75rem;
        }

        .step-card {
            text-align: center;
            padding: 1.5rem;
            height: 100%;
        }
        
        .step-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        
        .step-arrow {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #adb5bd;
            font-size: 1.5rem;
        }
        
        @media (max-width: 991.98px) {
            .step-arrow {
                transform: rotate(90deg);
                height: auto;
                padding: 1rem 0;
            }
        }
        
        @media (max-width: 768px) {
            .welcome-text { font-size: 1.85rem; }
            .hero-section { padding: 2rem 1.5rem; }
        }
    </style>
</head>
<body>
    <?php include("../includes/user_navbar.php"); ?>

    <div class="container dashboard-container">
        
        <!-- HERO WELCOME SECTION -->
        <div class="hero-section">
            <div class="row align-items-center position-relative" style="z-index: 1;">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center flex-wrap gap-3">
                        <div class="user-avatar">
                            <?php 
                                $initial = strtoupper(substr($user['name'], 0, 1)); 
                                echo $initial;
                            ?>
                        </div>
                        <div>
                            <p class="text-primary fw-medium mb-1 fs-5">Good to see you again,</p>
                            <h1 class="welcome-text">Welcome back, <?php echo htmlspecialchars($user['name']); ?>!</h1>
                            <p class="text-muted mb-0 fs-6">Here's what's happening with your account today.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                    <span class="badge bg-light text-dark px-4 py-2 fs-6 shadow-sm border">
                        <i class="bi bi-calendar3 text-primary me-2"></i>
                        <?php echo date('F j, Y'); ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- STATS GRID -->
        <div class="row g-4 mb-5">
            
            <!-- POINTS CARD -->
            <div class="col-12 col-lg-4">
                <div class="card stat-card points-card shadow-lg">
                    <div class="card-body d-flex flex-column justify-content-center text-center">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="bg-white bg-opacity-25 rounded-circle p-2 d-inline-flex">
                                <i class="bi bi-coin stat-icon"></i>
                            </div>
                            <span class="badge bg-white text-primary px-3 py-1 fw-semibold shadow-sm">Balance</span>
                        </div>
                        
                        <h2 class="points-value mb-1"><?php echo number_format($user['points']); ?></h2>
                        <p class="mb-0 opacity-90 fw-medium">Available Points</p>
                    </div>
                </div>
            </div>
            
            <!-- COMPLETED SURVEYS -->
            <div class="col-lg-4 col-md-6">
                <div class="card stat-card shadow-sm border-0 bg-white">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div>
                                <p class="text-muted fw-semibold mb-1 text-uppercase" style="font-size: 0.85rem; letter-spacing: 0.5px;">Surveys Completed</p>
                                <h2 class="display-5 fw-bold text-dark mb-0">
                                    <?php echo $response_data['total_surveys']; ?>
                                </h2>
                            </div>
                            <div class="stat-icon-wrapper bg-success bg-opacity-10 text-success">
                                <i class="bi bi-clipboard-check-fill stat-icon"></i>
                            </div>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: <?php echo min(100, $response_data['total_surveys'] * 5); ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- REDEEMED REWARDS -->
            <div class="col-lg-4 col-md-6">
                <div class="card stat-card shadow-sm border-0 bg-white">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div>
                                <p class="text-muted fw-semibold mb-1 text-uppercase" style="font-size: 0.85rem; letter-spacing: 0.5px;">Rewards Claimed</p>
                                <h2 class="display-5 fw-bold text-dark mb-0">
                                    <?php echo $reward_data['total_rewards']; ?>
                                </h2>
                            </div>
                            <div class="stat-icon-wrapper bg-warning bg-opacity-10 text-warning">
                                <i class="bi bi-trophy-fill stat-icon"></i>
                            </div>
                        </div>
                        <div class="mt-4 d-flex align-items-center gap-2 text-muted small fw-medium">
                            <i class="bi bi-arrow-up-circle text-warning"></i>
                            <span>Keep going to unlock more!</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- HOW IT WORKS SECTION -->
        <div class="card rounded-4 shadow-sm border-0 mb-4 bg-white">
            <div class="card-body p-5">
                <div class="text-center mb-5">
                    <h3 class="fw-bold"><i class="bi bi-lightbulb text-warning me-2"></i>How It Works</h3>
                    <p class="text-muted">Follow these simple steps to earn rewards.</p>
                </div>
                
                <div class="row g-4 align-items-center justify-content-center">
                    
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="step-card">
                            <div class="step-icon bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-pencil-square"></i>
                            </div>
                            <h6 class="fw-bold mb-1">Step 1</h6>
                            <p class="text-muted small mb-0">Browse Surveys</p>
                        </div>
                    </div>
                    
                    <div class="col-auto d-none d-lg-flex">
                        <div class="step-arrow">
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </div>
                    
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="step-card">
                            <div class="step-icon bg-info bg-opacity-10 text-info">
                                <i class="bi bi-check2-circle"></i>
                            </div>
                            <h6 class="fw-bold mb-1">Step 2</h6>
                            <p class="text-muted small mb-0">Complete Surveys</p>
                        </div>
                    </div>
                    
                    <div class="col-auto d-none d-lg-flex">
                        <div class="step-arrow">
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </div>
                    
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="step-card">
                            <div class="step-icon bg-success bg-opacity-10 text-success">
                                <i class="bi bi-star-fill"></i>
                            </div>
                            <h6 class="fw-bold mb-1">Step 3</h6>
                            <p class="text-muted small mb-0">Earn Reward Points</p>
                        </div>
                    </div>
                    
                    <div class="col-auto d-none d-lg-flex">
                        <div class="step-arrow">
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </div>
                    
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="step-card">
                            <div class="step-icon bg-warning bg-opacity-10 text-warning">
                                <i class="bi bi-gift-fill"></i>
                            </div>
                            <h6 class="fw-bold mb-1">Step 4</h6>
                            <p class="text-muted small mb-0">Redeem Rewards</p>
                        </div>
                    </div>
                    
                </div>
                
                <div class="text-center mt-5 pt-4 border-top">
                    <p class="text-muted small mb-0">Complete more surveys to earn additional reward points and unlock exciting rewards.</p>
                </div>
            </div>
        </div>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Subtle entrance animation for elements
        document.addEventListener('DOMContentLoaded', function() {
            const animateElements = document.querySelectorAll('.stat-card, .step-card, .step-arrow');
            animateElements.forEach((el, index) => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(15px)';
                
                setTimeout(() => {
                    el.style.transition = 'all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1)';
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }, 50 * index);
            });
        });
    </script>
    <?php include('../includes/footer.php'); ?>
</body>
</html>