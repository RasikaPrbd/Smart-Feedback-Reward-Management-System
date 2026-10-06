<?php

/*
 * -------------------------------------------------------
 * Page    : Survey Reports
 *
 * Purpose : Generates a printable or reviewable report for a selected survey, showing all responses and aggregated results.
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

// Only show surveys or rewards that are currently active and publicly visible.
$sql = "
SELECT *
FROM surveys
WHERE company_id = '$company_id' AND status = 'active'
ORDER BY created_at DESC
";

$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survey Reports</title>
    
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
            margin-bottom: 3rem;
            box-shadow: 0 15px 35px rgba(99, 102, 241, 0.25);
        }
        
        .report-card {
            border: none;
            border-radius: 18px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100%;
        }
        
        .report-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.15) !important;
        }
        
        .response-count {
            font-size: 2.2rem;
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
        
        .empty-state {
            text-align: center;
            padding: 6rem 2rem;
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
                        <i class="bi bi-bar-chart-fill me-2"></i>
                        REPORTS CENTER
                    </p>
                    <h1 class="display-5 fw-bold mb-3">Survey Reports</h1>
                    <p class="lead opacity-90 mb-0">
                        Track performance, analyze responses, and gain insights from all your surveys.
                    </p>
                </div>
            </div>
        </div>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <!-- STATS -->
            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="card shadow-sm text-center p-4">
                        <div class="text-primary mb-2">
                            <i class="bi bi-clipboard-data fs-2"></i>
                        </div>
                        <h3 class="fw-bold"><?php echo mysqli_num_rows($result); ?></h3>
                        <p class="text-muted mb-0">Total Surveys</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm text-center p-4">
                        <div class="text-success mb-2">
                            <i class="bi bi-people-fill fs-2"></i>
                        </div>
                        <h3 class="fw-bold" id="totalResponses">0</h3>
                        <p class="text-muted mb-0">Total Responses</p>
                    </div>
                </div>
            </div>

            <!-- SEARCH -->
            <div class="mb-4 d-flex flex-column flex-md-row gap-3 align-items-md-center justify-content-between">
                <h4 class="section-title mb-0">All Reports</h4>
                <input type="text" id="reportSearch" 
                       class="form-control flex-grow-1" 
                       style="max-width: 400px;"
                       placeholder="Search surveys..." 
                       onkeyup="filterReports()">
            </div>

            <!-- PREPARE DATA -->
            <?php 
            $totalResponses = 0;
            $completed_surveys = [];
            $active_surveys = [];
            
            mysqli_data_seek($result, 0);
            while($survey = mysqli_fetch_assoc($result)) {
                $survey_id = $survey['survey_id'];
                $count_sql = "SELECT COUNT(*) AS total FROM responses WHERE survey_id = '$survey_id'";
                $count_result = mysqli_query($conn, $count_sql);
                $count_row = mysqli_fetch_assoc($count_result);
                $responses = $count_row['total'];
                $totalResponses += $responses;
                
                $isCompleted = strtotime($survey['expiry_date']) < time();
                $survey['responses'] = $responses;
                
                if ($isCompleted) {
                    $completed_surveys[] = $survey;
                } else {
                    $active_surveys[] = $survey;
                }
            }
            ?>

            <!-- COMPLETED SURVEYS SECTION -->
            <div class="mb-5">
                <div class="d-flex align-items-center mb-2">
                    <h4 class="section-title mb-0 me-3">Completed Surveys</h4>
                    <span class="badge bg-secondary rounded-pill px-3 py-2 fs-6 shadow-sm"><?php echo count($completed_surveys); ?></span>
                </div>
                <p class="text-muted mb-4">View reports and response summaries from completed surveys.</p>
                <div class="row g-4 reports-container">
                    <?php foreach($completed_surveys as $survey): ?>
                        <div class="col-lg-4 col-md-6 report-item">
                            <div class="report-card card shadow h-100">
                                <div class="card-body d-flex flex-column p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <span class="badge bg-secondary">Completed</span>
                                        <small class="text-muted">#<?php echo $survey['survey_id']; ?></small>
                                    </div>
                                    
                                    <h5 class="card-title fw-semibold mb-3"><?php echo htmlspecialchars($survey['title']); ?></h5>
                                    
                                    <?php if (!empty($survey['description'])): ?>
                                        <p class="text-muted flex-grow-1 small">
                                            <?php echo htmlspecialchars(substr($survey['description'], 0, 95)); ?>...
                                        </p>
                                    <?php endif; ?>
                                    
                                    <div class="mt-auto">
                                        <div class="d-flex justify-content-between align-items-end mb-4">
                                            <div>
                                                <small class="text-muted">Responses</small><br>
                                                <span class="response-count text-dark"><?php echo $survey['responses']; ?></span>
                                            </div>
                                            <div class="text-end">
                                                <small class="text-muted">Reward</small><br>
                                                <strong class="text-primary"><?php echo $survey['points_reward']; ?> pts</strong>
                                            </div>
                                        </div>
                                        
                                        <a href="analytics_details.php?survey_id=<?php echo $survey['survey_id']; ?>" 
                                           class="btn btn-primary w-100 py-3">
                                            <i class="bi bi-graph-up-arrow me-2"></i>
                                            View Detailed Analytics
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (count($completed_surveys) == 0): ?>
                        <div class="col-12"><div class="alert alert-light border border-secondary text-muted text-center py-4">No completed surveys found.</div></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ACTIVE SURVEYS SECTION -->
            <div class="mb-5">
                <div class="d-flex align-items-center mb-2">
                    <h4 class="section-title mb-0 me-3">Active Surveys</h4>
                    <span class="badge bg-success rounded-pill px-3 py-2 fs-6 shadow-sm"><?php echo count($active_surveys); ?></span>
                </div>
                <p class="text-muted mb-4">Reports for currently running surveys.</p>
                <div class="row g-4 reports-container">
                    <?php foreach($active_surveys as $survey): ?>
                        <div class="col-lg-4 col-md-6 report-item">
                            <div class="report-card card shadow h-100">
                                <div class="card-body d-flex flex-column p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <span class="badge bg-success">Active</span>
                                        <small class="text-muted">#<?php echo $survey['survey_id']; ?></small>
                                    </div>
                                    
                                    <h5 class="card-title fw-semibold mb-3"><?php echo htmlspecialchars($survey['title']); ?></h5>
                                    
                                    <?php if (!empty($survey['description'])): ?>
                                        <p class="text-muted flex-grow-1 small">
                                            <?php echo htmlspecialchars(substr($survey['description'], 0, 95)); ?>...
                                        </p>
                                    <?php endif; ?>
                                    
                                    <div class="mt-auto">
                                        <div class="d-flex justify-content-between align-items-end mb-4">
                                            <div>
                                                <small class="text-muted">Responses</small><br>
                                                <span class="response-count text-dark"><?php echo $survey['responses']; ?></span>
                                            </div>
                                            <div class="text-end">
                                                <small class="text-muted">Reward</small><br>
                                                <strong class="text-primary"><?php echo $survey['points_reward']; ?> pts</strong>
                                            </div>
                                        </div>
                                        
                                        <a href="analytics_details.php?survey_id=<?php echo $survey['survey_id']; ?>" 
                                           class="btn btn-primary w-100 py-3">
                                            <i class="bi bi-graph-up-arrow me-2"></i>
                                            View Detailed Analytics
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (count($active_surveys) == 0): ?>
                        <div class="col-12"><div class="alert alert-light border border-success text-muted text-center py-4">No active surveys found.</div></div>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <!-- EMPTY STATE -->
            <div class="empty-state">
                <div class="mb-4">
                    <i class="bi bi-clipboard-check display-1 text-muted"></i>
                </div>
                <h4 class="text-dark">No survey reports yet</h4>
                <p class="text-muted mb-4">Create and publish surveys to start seeing performance data here.</p>
                <a href="create_survey.php" class="btn btn-primary px-5 py-3">
                    <i class="bi bi-plus-circle me-2"></i>
                    Create Your First Survey
                </a>
            </div>
        <?php endif; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Display total responses
        document.addEventListener('DOMContentLoaded', function() {
            const totalEl = document.getElementById('totalResponses');
            if (totalEl) {
                totalEl.textContent = '<?php echo $totalResponses; ?>';
            }
            
            // Card animations
            const cards = document.querySelectorAll('.report-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1)';
                    card.style.transitionDelay = (index * 80) + 'ms';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 150);
            });
        });
        
        // Frontend search
        function filterReports() {
            const term = document.getElementById('reportSearch').value.toLowerCase().trim();
            const items = document.querySelectorAll('.report-item');
            
            items.forEach(item => {
                const title = item.querySelector('.card-title').textContent.toLowerCase();
                item.style.display = title.includes(term) ? '' : 'none';
            });
        }
    </script>
    <?php include('../includes/footer.php'); ?>
</body>
</html>