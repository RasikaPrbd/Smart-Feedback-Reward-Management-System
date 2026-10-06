<?php

/*
 * -------------------------------------------------------
 * Page    : Company Dashboard
 *
 * Purpose : Central overview for the logged-in company. Shows statistics for their surveys, questions, responses, and rewards.
 * -------------------------------------------------------
 */

// Initialize the session to track user authentication state.
session_start();

// Include the database connection configuration to interact with the database.
include("../config/db.php");

// Verify that the company is logged in before granting access.
if (!isset($_SESSION['company_id'])) {
    // Redirect the user to the appropriate page after the action completes.
    header("Location: login.php");
    exit();
}

$company_id = $_SESSION['company_id'];

/* Total surveys */
$survey_sql = "
SELECT COUNT(*) AS total_surveys
FROM surveys
WHERE company_id='$company_id'
";

$survey_result = $conn->query($survey_sql);

$survey_data = $survey_result->fetch_assoc();

/* Total responses */
$response_sql = "
SELECT COUNT(*) AS total_responses
FROM responses
JOIN surveys
ON responses.survey_id = surveys.survey_id
WHERE surveys.company_id='$company_id'
";

$response_result = $conn->query($response_sql);

$response_data = $response_result->fetch_assoc();

/* Total rewards */
$reward_sql = "
SELECT COUNT(*) AS total_rewards
FROM rewards
WHERE company_id='$company_id'
";

$reward_result = $conn->query($reward_sql);

$reward_data = $reward_result->fetch_assoc();

/* Chart Data - Responses per Survey */

$chart_query = "
SELECT
    surveys.title,
    COUNT(responses.response_id) AS total_responses
FROM surveys
LEFT JOIN responses
ON surveys.survey_id = responses.survey_id
WHERE surveys.company_id = '$company_id'
GROUP BY surveys.survey_id
ORDER BY surveys.survey_id
";

$chart_result = mysqli_query($conn, $chart_query);

$labels = [];
$data = [];

while($row = mysqli_fetch_assoc($chart_result))
{
    $labels[] = $row['title'];
    $data[] = $row['total_responses'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo htmlspecialchars($_SESSION['company_name']); ?></title>
    
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
            --dark: #1e2937;
        }
        
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
        }
        
        .navbar {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            backdrop-filter: blur(12px);
        }
        
        .dashboard-container {
            padding-top: 2rem;
            padding-bottom: 5rem;
        }
        
        .hero-section {
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            color: white;
            border-radius: 20px;
            padding: 3rem 2.5rem;
            margin-bottom: 2.5rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(99, 102, 241, 0.25);
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: -30%;
            right: -20%;
            width: 500px;
            height: 500px;
            background: rgba(255,255,255,0.15);
            border-radius: 50%;
            z-index: 1;
        }
        
        .welcome-text {
            font-size: 2.4rem;
            font-weight: 700;
            line-height: 1.1;
        }
        
        .kpi-card {
            border: none;
            border-radius: 18px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100%;
        }
        
        .kpi-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.15) !important;
        }
        
        .kpi-icon {
            font-size: 2.8rem;
            opacity: 0.9;
        }
        
        .stat-value {
            font-size: 3rem;
            font-weight: 800;
            line-height: 1;
        }
        
        .action-card {
            border: none;
            border-radius: 16px;
            height: 100%;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .action-card:hover {
            transform: translateY(-6px);
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
            bottom: -6px;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--primary);
            border-radius: 3px;
        }
        
        .quick-actions {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.07);
        }
        
        .chart-container {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.07);
            height: 420px;
        }
        
        @media (max-width: 767.98px) {
            .chart-container {
                height: 280px;
            }
            .position-relative[style*="height: 350px"] {
                height: 260px !important;
            }
        }
    </style>
</head>
<body>
    <?php include("../includes/company_navbar.php"); ?>

    <div class="container dashboard-container">
        
        <!-- HERO SECTION -->
        <div class="hero-section">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <p class="mb-2 opacity-90 fs-5">
                        <i class="bi bi-building me-2"></i>
                        Company Portal
                    </p>
                    <h1 class="welcome-text mb-3">
                        Welcome back, <br>
                        <?php echo htmlspecialchars($_SESSION['company_name']); ?>!
                    </h1>
                    <p class="lead opacity-90 mb-0">
                        Track your survey performance and engagement metrics.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                    <div class="d-inline-flex align-items-center gap-3 bg-white bg-opacity-10 rounded-3 px-4 py-3">
                        <div class="text-end">
                            <small class="opacity-75">Today</small><br>
                            <strong><?php echo date('F j, Y'); ?></strong>
                        </div>
                        <i class="bi bi-calendar3 fs-1"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI STATS -->
        <div class="row g-4 mb-5">
            <!-- Total Surveys -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card kpi-card shadow h-100" style="border-top: 5px solid #6366f1;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1">Total Surveys</p>
                                <h2 class="stat-value text-dark"><?php echo $survey_data['total_surveys']; ?></h2>
                            </div>
                            <div class="kpi-icon text-primary">
                                <i class="bi bi-clipboard-data"></i>
                            </div>
                        </div>
                        <div class="mt-4 text-success small fw-medium">
                            <i class="bi bi-trend-up"></i> Active campaigns
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Total Responses -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card kpi-card shadow h-100" style="border-top: 5px solid #10b981;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1">Total Responses</p>
                                <h2 class="stat-value text-dark"><?php echo $response_data['total_responses']; ?></h2>
                            </div>
                            <div class="kpi-icon text-success">
                                <i class="bi bi-people-fill"></i>
                            </div>
                        </div>
                        <div class="mt-4 text-muted small">
                            Insights from your audience
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Total Rewards -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card kpi-card shadow h-100" style="border-top: 5px solid #0ea5e9;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1">Rewards Created</p>
                                <h2 class="stat-value text-dark"><?php echo $reward_data['total_rewards']; ?></h2>
                            </div>
                            <div class="kpi-icon text-info">
                                <i class="bi bi-gift-fill"></i>
                            </div>
                        </div>
                        <div class="mt-4 text-muted small">
                            Incentive programs live
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            
            <!-- PERFORMANCE CHART -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-4">
                    <h4 class="section-title mb-1">Responses by Survey</h4>
                    <p class="text-muted mb-4">Compare response counts across all your surveys.</p>
                    <div class="position-relative w-100" style="height: 350px;">
                        <canvas id="engagementChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- QUICK ACTIONS -->
            <div class="col-lg-5">
                <div class="quick-actions h-100">
                    <h4 class="section-title mb-4">Quick Actions</h4>
                    
                    <div class="row g-3">
                        <div class="col-6">
                            <a href="create_survey.php" class="action-card text-decoration-none">
                                <div class="card h-100 border-0 shadow-sm text-center p-4">
                                    <i class="bi bi-plus-circle fs-1 text-primary mb-3"></i>
                                    <h6 class="fw-semibold">Create Survey</h6>
                                    <small class="text-muted">New campaign</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="view_responses.php" class="action-card text-decoration-none">
                                <div class="card h-100 border-0 shadow-sm text-center p-4">
                                    <i class="bi bi-eye fs-1 text-success mb-3"></i>
                                    <h6 class="fw-semibold">View Responses</h6>
                                    <small class="text-muted">Live data</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="manage_surveys.php" class="action-card text-decoration-none">
                                <div class="card h-100 border-0 shadow-sm text-center p-4">
                                    <i class="bi bi-clipboard-data fs-1 text-warning mb-3"></i>
                                    <h6 class="fw-semibold">Manage Surveys</h6>
                                    <small class="text-muted">View & edit surveys</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="create_reward.php" class="action-card text-decoration-none">
                                <div class="card h-100 border-0 shadow-sm text-center p-4">
                                    <i class="bi bi-gift fs-1 text-info mb-3"></i>
                                    <h6 class="fw-semibold">Create Reward</h6>
                                    <small class="text-muted">New incentive</small>
                                </div>
                            </a>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>


    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        
        document.addEventListener('DOMContentLoaded', function(){

    const ctx = document.getElementById('engagementChart').getContext('2d');
    const grad = ctx.createLinearGradient(0, 0, 0, 400);
    grad.addColorStop(0, '#6366f1'); // Indigo
    grad.addColorStop(1, '#a855f7'); // Purple

    const labels = <?php echo json_encode($labels); ?>;
    const data = <?php echo json_encode($data); ?>;

    const valuePlugin = {
        id: 'valuePlugin',
        afterDatasetsDraw: (chart, args, options) => {
            const { ctx } = chart;
            ctx.save();
            chart.data.datasets.forEach((dataset, i) => {
                const meta = chart.getDatasetMeta(i);
                meta.data.forEach((bar, index) => {
                    const val = dataset.data[index];
                    if (val > 0) {
                        ctx.fillStyle = '#64748b';
                        ctx.font = 'bold 12px "Segoe UI", sans-serif';
                        ctx.textAlign = 'center';
                        ctx.fillText(val, bar.x, bar.y - 8);
                    }
                });
            });
            ctx.restore();
        }
    };

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Responses',
                data: data,
                backgroundColor: grad,
                borderRadius: 10,
                borderSkipped: false,
                barPercentage: 0.6
            }]
        },
        plugins: [valuePlugin],
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 1200,
                easing: 'easeOutQuart'
            },
            layout: {
                padding: { top: 25 }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(30, 41, 55, 0.95)',
                    padding: 12,
                    cornerRadius: 8,
                    titleFont: { family: "'Segoe UI', sans-serif", size: 14, weight: 'bold' },
                    bodyFont: { family: "'Segoe UI', sans-serif", size: 14, weight: 'bold' },
                    boxPadding: 6,
                    callbacks: {
                        title: function(context) {
                            return context[0].label;
                        },
                        label: function(context) {
                            return ' Responses: ' + context.raw;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(226, 232, 240, 0.6)', drawBorder: false },
                    ticks: {
                        precision: 0,
                        font: { family: "'Segoe UI', sans-serif", size: 12 },
                        color: '#64748b',
                        padding: 10
                    },
                    title: { display: false }
                },
                x: {
                    grid: { display: false, drawBorder: false },
                    ticks: {
                        font: { family: "'Segoe UI', sans-serif", size: 12 },
                        color: '#64748b',
                        padding: 10,
                        maxRotation: 45,
                        minRotation: 0,
                        callback: function(value) {
                            let label = this.getLabelForValue(value);
                            return label.length > 15 ? label.substring(0, 15) + '...' : label;
                        }
                    },
                    title: { display: false }
                }
            }
        }
    });

    const kpiCards=document.querySelectorAll('.kpi-card');

    kpiCards.forEach((card,index)=>{

        card.style.opacity='0';
        card.style.transform='translateY(30px)';

        setTimeout(()=>{

            card.style.transition='all .6s cubic-bezier(.34,1.56,.64,1)';
            card.style.transitionDelay=(index*100)+'ms';

            card.style.opacity='1';
            card.style.transform='translateY(0)';

        },400);

    });

});
        
    </script>
    <?php include('../includes/footer.php'); ?>
</body>
</html>