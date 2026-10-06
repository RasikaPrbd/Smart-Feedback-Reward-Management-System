<?php

/*
 * -------------------------------------------------------
 * Page    : Admin Dashboard
 *
 * Purpose : Central overview for administrators. Displays system-wide statistics including users, companies, surveys, and recent activity.
 * -------------------------------------------------------
 */

// Initialize the session to track user authentication state.
session_start();

// Include the database connection configuration to interact with the database.
include("../config/db.php");

// Verify that the admin is logged in before granting access.
if (!isset($_SESSION['admin_id'])) {
    // Redirect the user to the appropriate page after the action completes.
    header("Location: login.php");
    exit();
}

/* Total users */
$user_sql = "
SELECT COUNT(*) AS total_users
FROM users
WHERE is_deleted = 0
";

$user_result = $conn->query($user_sql);

$user_data = $user_result->fetch_assoc();

/* Total companies */
$company_sql = "
SELECT COUNT(*) AS total_companies
FROM companies
";

$company_result = $conn->query($company_sql);

$company_data = $company_result->fetch_assoc();

/* Total surveys */
$survey_sql = "
SELECT COUNT(*) AS total_surveys
FROM surveys
";

$survey_result = $conn->query($survey_sql);

$survey_data = $survey_result->fetch_assoc();

/* Total responses */
$response_sql = "
SELECT COUNT(*) AS total_responses
FROM responses
";

$response_result = $conn->query($response_sql);

$response_data = $response_result->fetch_assoc();

/* Total rewards */
$reward_sql = "
SELECT COUNT(*) AS total_rewards
FROM rewards
";

$reward_result = $conn->query($reward_sql);

$reward_data = $reward_result->fetch_assoc();

/* Recent Surveys */
$recent = mysqli_query(
    $conn,
    "
    SELECT
        surveys.title,
        companies.company_name
    FROM surveys
    JOIN companies
    ON surveys.company_id = companies.company_id
    ORDER BY surveys.survey_id DESC
    LIMIT 5
    "
);

$redemption_sql = "
SELECT COUNT(*) AS total_redemptions
FROM redemptions
";

$redemption_result = mysqli_query($conn, $redemption_sql);
$redemption_data = mysqli_fetch_assoc($redemption_result);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Control Center</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --success: #10b981;
            --info: #0ea5e9;
            --warning: #f59e0b;
            --danger: #ef4444;
            --dark: #1e2937;
        }
        
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
        }
        
        .dashboard-container {
            padding-top: 2rem;
            padding-bottom: 5rem;
        }
        
        .hero-section {
            background: linear-gradient(135deg, #1e2937 0%, #334155 100%);
            color: white;
            border-radius: 20px;
            padding: 3rem 2.5rem;
            margin-bottom: 2.5rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(30, 41, 55, 0.3);
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: -40%;
            right: -15%;
            width: 550px;
            height: 550px;
            background: rgba(99, 102, 241, 0.15);
            border-radius: 50%;
        }
        
        .welcome-text {
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1.1;
        }
        
        .kpi-card {
            border: none;
            border-radius: 18px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100%;
            overflow: hidden;
        }
        
        .kpi-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.15) !important;
        }
        
        .kpi-icon {
            font-size: 2.75rem;
            opacity: 0.9;
        }
        
        .stat-value {
            font-size: 2.85rem;
            font-weight: 800;
            line-height: 1;
        }
        
        .action-card {
            border: none;
            border-radius: 16px;
            height: 100%;
            transition: all 0.3s ease;
        }
        
        .action-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        }
        
        .section-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--dark);
            position: relative;
            display: inline-block;
        }
        
        .section-title:after {
            content: '';
            position: absolute;
            bottom: -7px;
            left: 0;
            width: 55px;
            height: 3px;
            background: var(--primary);
            border-radius: 3px;
        }
        
        .recent-table th {
            font-weight: 500;
            color: #64748b;
            border-top: none;
        }

        .chart-wrapper {
            position: relative;
            height: 320px;
            width: 100%;
        }
        
        .table-hover tbody tr:hover {
            background-color: #f8fafc;
        }
    </style>
</head>
<body>
    <?php include("../includes/admin_navbar.php"); ?>

    <div class="container dashboard-container">
        
        <!-- HERO SECTION -->
        <div class="hero-section">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <p class="mb-2 opacity-75 fs-5">
                        <i class="bi bi-shield-lock me-2"></i>
                        ADMIN CONTROL CENTER
                    </p>
                    <h1 class="welcome-text mb-3">
                        Welcome back,<br>
                        <?php echo htmlspecialchars($_SESSION['admin_name']); ?>!
                    </h1>
                    <p class="lead opacity-90 mb-0">
                        Monitor platform performance and manage all operations from one place.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                    <div class="d-inline-flex align-items-center gap-3 bg-white bg-opacity-10 rounded-3 px-4 py-3">
                        <div>
                            <small class="opacity-75">Today</small><br>
                            <strong><?php echo date('F j, Y'); ?></strong>
                        </div>
                        <i class="bi bi-calendar3 fs-1"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI STATISTICS -->
        <div class="row g-4 mb-5">
            <!-- Total Users -->
            <div class="col-xl-2 col-lg-4 col-md-6">
                <a href="manage_users.php" class="text-decoration-none">
                    <div class="card kpi-card shadow" style="border-top: 5px solid #6366f1;">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="text-muted mb-1">Total Users</p>
                                    <h2 class="stat-value text-dark"><?php echo $user_data['total_users']; ?></h2>
                                </div>
                                <div class="kpi-icon text-primary">
                                    <i class="bi bi-people-fill"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            
            <!-- Total Companies -->
            <div class="col-xl-2 col-lg-4 col-md-6">
                <a href="manage_companies.php" class="text-decoration-none">
                    <div class="card kpi-card shadow" style="border-top: 5px solid #10b981;">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="text-muted mb-1">Total Companies</p>
                                    <h2 class="stat-value text-dark"><?php echo $company_data['total_companies']; ?></h2>
                                </div>
                                <div class="kpi-icon text-success">
                                    <i class="bi bi-building"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            
            <!-- Total Surveys -->
            <div class="col-xl-2 col-lg-4 col-md-6">
                <a href="manage_surveys.php" class="text-decoration-none">
                    <div class="card kpi-card shadow" style="border-top: 5px solid #f59e0b;">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="text-muted mb-1">Total Surveys</p>
                                    <h2 class="stat-value text-dark"><?php echo $survey_data['total_surveys']; ?></h2>
                                </div>
                                <div class="kpi-icon text-warning">
                                    <i class="bi bi-clipboard-data"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            
            <!-- Total Responses -->
            <div class="col-xl-2 col-lg-4 col-md-6">
                <div class="card kpi-card shadow" style="border-top: 5px solid #0ea5e9;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1">Total Responses</p>
                                <h2 class="stat-value text-dark"><?php echo $response_data['total_responses']; ?></h2>
                            </div>
                            <div class="kpi-icon text-info">
                                <i class="bi bi-chat-square-text"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Total Rewards -->
            <div class="col-xl-2 col-lg-4 col-md-6">
                <a href="manage_rewards.php" class="text-decoration-none">
                    <div class="card kpi-card shadow" style="border-top: 5px solid #ec4899;">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="text-muted mb-1">Total Rewards</p>
                                    <h2 class="stat-value text-dark"><?php echo $reward_data['total_rewards']; ?></h2>
                                </div>
                                <div class="kpi-icon text-pink">
                                    <i class="bi bi-gift"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            
            <!-- Total Redemptions -->
            <div class="col-xl-2 col-lg-4 col-md-6">
                <div class="card kpi-card shadow" style="border-top: 5px solid #8b5cf6;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1">Redemptions</p>
                                <h2 class="stat-value text-dark"><?php echo $redemption_data['total_redemptions']; ?></h2>
                            </div>
                            <div class="kpi-icon text-purple">
                                <i class="bi bi-ticket-perforated"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            
            <!-- QUICK ACTIONS -->
            <div class="col-lg-5">
                <div class="card shadow h-100" style="background: white; border-radius: 20px; padding: 2.5rem;">
                    <h4 class="section-title mb-4">Quick Actions</h4>
                    
                    <div class="row g-3">
                        <div class="col-6">
                            <a href="manage_users.php" class="action-card text-decoration-none">
                                <div class="card h-100 border-0 shadow-sm p-4 text-center">
                                    <i class="bi bi-person-gear fs-1 text-primary mb-3"></i>
                                    <h6 class="fw-semibold">Manage Users</h6>
                                </div>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="manage_companies.php" class="action-card text-decoration-none">
                                <div class="card h-100 border-0 shadow-sm p-4 text-center">
                                    <i class="bi bi-building-gear fs-1 text-success mb-3"></i>
                                    <h6 class="fw-semibold">Manage Companies</h6>
                                </div>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="manage_surveys.php" class="action-card text-decoration-none">
                                <div class="card h-100 border-0 shadow-sm p-4 text-center">
                                    <i class="bi bi-clipboard-check fs-1 text-warning mb-3"></i>
                                    <h6 class="fw-semibold">Manage Surveys</h6>
                                </div>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="manage_rewards.php" class="action-card text-decoration-none">
                                <div class="card h-100 border-0 shadow-sm p-4 text-center">
                                    <i class="bi bi-gift fs-1 text-info mb-3"></i>
                                    <h6 class="fw-semibold">Manage Rewards</h6>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- PLATFORM OVERVIEW CHART -->
            <div class="col-lg-7">
                <div class="card shadow h-100" style="border-radius: 20px;">
                    <div class="card-body p-4">
                        <h4 class="section-title mb-4">Platform Overview</h4>
                        <div class="chart-wrapper">
                            <canvas id="platformChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RECENT SURVEYS -->
        <div class="card shadow mt-5">
            <div class="card-header bg-white border-0 py-4">
                <h4 class="section-title mb-0">Recent Surveys</h4>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 recent-table">
                        <thead class="table-light">
                            <tr>
                                <th width="55%">Survey Title</th>
                                <th>Company</th>
                                <th class="text-end">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($recent) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($recent)): ?>
                                    <tr>
                                        <td class="fw-medium"><?php echo htmlspecialchars($row['title']); ?></td>
                                        <td><?php echo htmlspecialchars($row['company_name']); ?></td>
                                        <td class="text-end">
                                            <span class="badge bg-success">Active</span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 mb-3 d-block"></i>
                                        No surveys found yet.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Platform Overview Chart
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('platformChart');
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Users', 'Companies', 'Surveys', 'Responses'],
                    datasets: [{
                        label: 'Current Count',
                        data: [
                            <?php echo $user_data['total_users']; ?>,
                            <?php echo $company_data['total_companies']; ?>,
                            <?php echo $survey_data['total_surveys']; ?>,
                            <?php echo $response_data['total_responses']; ?>
                        ],
                        backgroundColor: [
                            '#6366f1', '#10b981', '#f59e0b', '#0ea5e9'
                        ],
                        borderRadius: 8,
                        barThickness: 45
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: false },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#e2e8f0' }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });
            
            // Animate KPI cards
            const kpiCards = document.querySelectorAll('.kpi-card');
            kpiCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1)';
                    card.style.transitionDelay = (index * 80) + 'ms';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 300);
            });
        });
    </script>
    <?php include('../includes/footer.php'); ?>
</body>
</html>