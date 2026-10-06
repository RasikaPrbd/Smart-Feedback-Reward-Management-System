<?php

/*
 * -------------------------------------------------------
 * Page    : Survey Analytics
 *
 * Purpose : Provides the company with a visual summary of response trends, question ratings, and participation rates across all their surveys.
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

/*
|--------------------------------------------------------------------------
| Statistics
|--------------------------------------------------------------------------
*/

// Only show surveys or rewards that are currently active and publicly visible.
$survey_query = mysqli_query($conn,"
SELECT COUNT(*) AS total_surveys
FROM surveys
WHERE company_id='$company_id' AND status='active'
");

$total_surveys = mysqli_fetch_assoc($survey_query)['total_surveys'];

$response_query = mysqli_query($conn,"
SELECT COUNT(*) AS total_responses
FROM responses
JOIN surveys
ON responses.survey_id = surveys.survey_id
WHERE surveys.company_id='$company_id'
");

$total_responses = mysqli_fetch_assoc($response_query)['total_responses'];

$reward_query = mysqli_query($conn,"
SELECT COUNT(*) AS total_rewards
FROM rewards
WHERE company_id='$company_id'
");

$total_rewards = mysqli_fetch_assoc($reward_query)['total_rewards'];

$redemption_query = mysqli_query($conn,"
SELECT COUNT(*) AS total_redemptions
FROM redemptions
JOIN rewards
ON redemptions.reward_id = rewards.reward_id
WHERE rewards.company_id='$company_id'
");

$total_redemptions = mysqli_fetch_assoc($redemption_query)['total_redemptions'];

/*
|--------------------------------------------------------------------------
| Survey Performance
|--------------------------------------------------------------------------
*/

// Only show surveys or rewards that are currently active and publicly visible.
$performance_query = mysqli_query($conn,"
SELECT
    surveys.title,
    COUNT(responses.response_id) AS response_count
FROM surveys
LEFT JOIN responses
ON surveys.survey_id = responses.survey_id
WHERE surveys.company_id='$company_id' AND surveys.status='active'
GROUP BY surveys.survey_id
ORDER BY response_count DESC
");

/*
|--------------------------------------------------------------------------
| Most Popular Survey
|--------------------------------------------------------------------------
*/

// Only show surveys or rewards that are currently active and publicly visible.
$top_survey_query = mysqli_query($conn,"
SELECT
    surveys.title,
    COUNT(responses.response_id) AS response_count
FROM surveys
LEFT JOIN responses
ON surveys.survey_id = responses.survey_id
WHERE surveys.company_id='$company_id' AND surveys.status='active'
GROUP BY surveys.survey_id
ORDER BY response_count DESC
LIMIT 1
");

$top_survey = mysqli_fetch_assoc($top_survey_query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - <?php echo htmlspecialchars($_SESSION['company_name'] ?? 'Company'); ?></title>
    
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
        
        .dashboard-container {
            padding-top: 2rem;
            padding-bottom: 5rem;
        }
        
        .hero-section {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
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
        
        .kpi-card {
            border: none;
            border-radius: 18px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100%;
        }
        
        .kpi-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12) !important;
        }
        
        .stat-value {
            font-size: 2.8rem;
            font-weight: 800;
            line-height: 1;
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
        
        .performance-table th {
            font-weight: 500;
            color: #64748b;
        }
        
        .chart-wrapper {
            position: relative;
            height: 340px;
            width: 100%;
        }
        
        @media (max-width: 767.98px) {
            .chart-wrapper {
                height: 260px;
            }
        }
        
        .top-survey-card {
            background: linear-gradient(135deg, #f8fafc, white);
            border-left: 6px solid var(--primary);
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
                        <i class="bi bi-graph-up-arrow me-2"></i>
                        COMPANY ANALYTICS
                    </p>
                    <h1 class="display-5 fw-bold mb-3">Performance Overview</h1>
                    <p class="lead opacity-90 mb-0">
                        Track survey engagement, rewards, and audience insights in real-time.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                    <div class="badge bg-white bg-opacity-25 px-4 py-3 fs-6">
                        <i class="bi bi-calendar3 me-2"></i>
                        <?php echo date('F Y'); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI OVERVIEW -->
        <div class="row g-4 mb-5">
            <div class="col-6 col-lg-3 col-md-6">
                <div class="card kpi-card shadow" style="border-top: 5px solid #6366f1;">
                    <div class="card-body p-4 text-center">
                        <i class="bi bi-clipboard-data fs-1 text-primary mb-3"></i>
                        <h5 class="text-muted">Total Surveys</h5>
                        <h2 class="stat-value text-dark"><?php echo $total_surveys; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3 col-md-6">
                <div class="card kpi-card shadow" style="border-top: 5px solid #10b981;">
                    <div class="card-body p-4 text-center">
                        <i class="bi bi-people-fill fs-1 text-success mb-3"></i>
                        <h5 class="text-muted">Total Responses</h5>
                        <h2 class="stat-value text-dark"><?php echo $total_responses; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3 col-md-6">
                <div class="card kpi-card shadow" style="border-top: 5px solid #0ea5e9;">
                    <div class="card-body p-4 text-center">
                        <i class="bi bi-gift fs-1 text-info mb-3"></i>
                        <h5 class="text-muted">Total Rewards</h5>
                        <h2 class="stat-value text-dark"><?php echo $total_rewards; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3 col-md-6">
                <div class="card kpi-card shadow" style="border-top: 5px solid #f59e0b;">
                    <div class="card-body p-4 text-center">
                        <i class="bi bi-ticket-perforated fs-1 text-warning mb-3"></i>
                        <h5 class="text-muted">Redemptions</h5>
                        <h2 class="stat-value text-dark"><?php echo $total_redemptions; ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            
            <!-- SURVEY PERFORMANCE CHART -->
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header bg-white border-0 py-4">
                        <h4 class="section-title mb-0">Survey Performance</h4>
                    </div>
                    <div class="card-body">
                        <div class="chart-wrapper">
                            <canvas id="performanceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- MOST POPULAR SURVEY -->
            <div class="col-lg-4">
                <div class="card shadow h-100 top-survey-card">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <i class="bi bi-trophy-fill fs-2 text-warning"></i>
                            <h4 class="section-title mb-0">Top Performer</h4>
                        </div>
                        
                        <?php if ($top_survey && $top_survey['title']): ?>
                            <h5 class="fw-semibold mb-3"><?php echo htmlspecialchars($top_survey['title']); ?></h5>
                            <div class="mt-auto">
                                <div class="bg-light rounded-3 p-4 text-center">
                                    <p class="text-muted mb-1 small">TOTAL RESPONSES</p>
                                    <h2 class="stat-value text-dark mb-0"><?php echo $top_survey['response_count']; ?></h2>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-bar-chart fs-1 mb-3"></i>
                                <p>No survey data available yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- DETAILED PERFORMANCE TABLE -->
        <div class="card shadow mt-5">
            <div class="card-header bg-white border-0 py-4">
                <h4 class="section-title mb-0">All Survey Performance</h4>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 performance-table">
                        <thead class="table-light">
                            <tr>
                                <th width="70%">Survey Title</th>
                                <th class="text-end">Responses</th>
                                <th class="text-end">Engagement</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            mysqli_data_seek($performance_query, 0); 
                            if (mysqli_num_rows($performance_query) > 0): 
                            ?>
                                <?php while($row = mysqli_fetch_assoc($performance_query)): ?>
                                    <tr>
                                        <td class="fw-medium"><?php echo htmlspecialchars($row['title']); ?></td>
                                        <td class="text-end fw-semibold"><?php echo $row['response_count']; ?></td>
                                        <td class="text-end">
                                            <span class="badge bg-success">
                                                <?php 
                                                    $perc = $total_responses > 0 ? round(($row['response_count'] / $total_responses) * 100) : 0;
                                                    echo $perc . '%';
                                                ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                        No surveys available yet.
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
        document.addEventListener('DOMContentLoaded', function() {
            // Prepare data for chart
            const surveyLabels = [];
            const responseData = [];
            
            <?php 
            mysqli_data_seek($performance_query, 0);
            while($row = mysqli_fetch_assoc($performance_query)): 
            ?>
                surveyLabels.push("<?php echo addslashes($row['title']); ?>");
                responseData.push(<?php echo $row['response_count']; ?>);
            <?php endwhile; ?>
            
            // Performance Bar Chart
            const ctx = document.getElementById('performanceChart');
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: surveyLabels.length ? surveyLabels : ['No Data'],
                    datasets: [{
                        label: 'Responses',
                        data: responseData.length ? responseData : [0],
                        backgroundColor: '#6366f1',
                        borderColor: '#4f46e5',
                        borderWidth: 2,
                        borderRadius: 8,
                        barThickness: 45
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.raw + ' responses';
                                }
                            }
                        }
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
                    card.style.transitionDelay = (index * 100) + 'ms';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 200);
            });
        });
    </script>
    <?php include('../includes/footer.php'); ?>
</body>
</html>