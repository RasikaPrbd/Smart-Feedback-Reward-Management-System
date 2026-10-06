<?php

/*
 * -------------------------------------------------------
 * Page    : Analytics Details
 *
 * Purpose : Displays a detailed breakdown of responses for a specific survey, including per-question answer distributions.
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

if (!isset($_GET['survey_id'])) {
    die("Survey ID missing");
}

$survey_id = (int)$_GET['survey_id'];

$company_id = $_SESSION['company_id'];

// Enforce ownership by filtering surveys to only those belonging to the logged-in company.
$survey_stmt = $conn->prepare("SELECT * FROM surveys WHERE survey_id = ? AND company_id = ? AND status = 'active'");
$survey_stmt->bind_param("ii", $survey_id, $company_id);
$survey_stmt->execute();
$survey_result = $survey_stmt->get_result();
$survey = $survey_result ? $survey_result->fetch_assoc() : null;
$survey_stmt->close();

if (!$survey) {
    die("Survey not found or access denied");
}

$question_stmt = $conn->prepare("SELECT * FROM questions WHERE survey_id = ?");
$question_stmt->bind_param("i", $survey_id);
$question_stmt->execute();
$questions = $question_stmt->get_result();
$question_stmt->close();

/* Total Responses */

$count_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM responses WHERE survey_id = ?");
$count_stmt->bind_param("i", $survey_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$count_row = $count_result ? $count_result->fetch_assoc() : null;
if (!$count_row) {
    $count_row = ['total' => 0];
}
$count_stmt->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics • <?php echo htmlspecialchars($survey['title']); ?></title>
    
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
        }
        
        .question-card {
            border: none;
            border-radius: 18px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }
        
        .question-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
        }
        
        .kpi-card {
            border: none;
            border-radius: 16px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .stat-value {
            font-size: 2.6rem;
            font-weight: 800;
            line-height: 1;
        }
        
        .section-title {
            font-size: 1.35rem;
            font-weight: 600;
            color: var(--dark);
            position: relative;
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
        
        .chart-wrapper {
            position: relative;
            height: 320px;
            width: 100%;
            margin: 1.5rem 0;
        }
        
        @media (max-width: 767.98px) {
            .chart-wrapper {
                height: 220px;
            }
        }
        
        .text-response-card {
            background: #f8fafc;
            border-left: 4px solid var(--primary);
        }
        
        @media print {
            .btn, .navbar, nav, .no-print {
                display: none !important;
            }
            body {
                background: white !important;
            }
            .question-card {
                border: 1px solid #ddd !important;
                box-shadow: none !important;
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
                        <i class="bi bi-bar-chart-fill me-2"></i>
                        SURVEY ANALYTICS
                    </p>
                    <h1 class="display-5 fw-bold mb-3"><?php echo htmlspecialchars($survey['title']); ?></h1>
                    <?php if (!empty($survey['description'])): ?>
                        <p class="lead opacity-90 mb-0"><?php echo htmlspecialchars($survey['description']); ?></p>
                    <?php endif; ?>
                </div>
                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                    <div class="bg-white bg-opacity-25 rounded-4 p-4 d-inline-block">
                        <div class="text-center">
                            <small class="opacity-75 d-block">TOTAL RESPONSES</small>
                            <h2 class="stat-value mb-0"><?php echo $count_row['total']; ?></h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- QUESTION SECTIONS -->
        <?php 
        mysqli_data_seek($questions, 0);
        while($question = mysqli_fetch_assoc($questions)): 
            $question_id = $question['question_id'];
        ?>
            <div class="question-card card mb-5">
                <div class="card-body p-5">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <span class="badge bg-primary mb-2">
                                <?php echo strtoupper($question['question_type']); ?>
                            </span>
                            <h4 class="section-title mb-1"><?php echo htmlspecialchars($question['question_text']); ?></h4>
                        </div>
                        <?php 
                        $response_count_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM answers WHERE question_id = ?");
                        $response_count_stmt->bind_param("i", $question_id);
                        $response_count_stmt->execute();
                        $response_count_result = $response_count_stmt->get_result();
                        $response_count_row = $response_count_result ? $response_count_result->fetch_assoc() : null;
                        $response_count = $response_count_row ? $response_count_row['total'] : 0;
                        $response_count_stmt->close();
                        ?>
                        <div class="text-end">
                            <small class="text-muted">Responses</small>
                            <h3 class="mb-0 fw-semibold"><?php echo $response_count; ?></h3>
                        </div>
                    </div>
                    <hr>

                    <?php if($question['question_type'] == 'rating'): ?>
                        <!-- RATING QUESTION -->
                        <?php
                        $avg_stmt = $conn->prepare("SELECT ROUND(AVG(answer_text),2) AS average_rating FROM answers WHERE question_id = ?");
                        $avg_stmt->bind_param("i", $question_id);
                        $avg_stmt->execute();
                        $avg_result = $avg_stmt->get_result();
                        $avg_row = $avg_result ? $avg_result->fetch_assoc() : null;
                        $avg_stmt->close();
                        $avg_rating = $avg_row['average_rating'] ?? 0;
                        ?>
                        
                        <div class="row g-3 mb-4">
                            <div class="col-sm-6">
                                <div class="card shadow-sm border-0 rounded-4 p-3 d-flex flex-row align-items-center">
                                    <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3 text-primary">
                                        <i class="bi bi-chat-square-text-fill fs-4"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted text-uppercase fw-bold">Responses</small>
                                        <h4 class="mb-0 fw-bold"><?php echo $response_count; ?></h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="card shadow-sm border-0 rounded-4 p-3 d-flex flex-row align-items-center">
                                    <div class="bg-warning bg-opacity-10 p-3 rounded-3 me-3 text-warning">
                                        <i class="bi bi-star-fill fs-4"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted text-uppercase fw-bold">Average Rating</small>
                                        <h4 class="mb-0 fw-bold"><?php echo $avg_rating; ?> / 5</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php
                        $rating_stmt = $conn->prepare("SELECT answer_text, COUNT(*) AS total FROM answers WHERE question_id = ? GROUP BY answer_text ORDER BY answer_text ASC");
                        $rating_stmt->bind_param("i", $question_id);
                        $rating_stmt->execute();
                        $rating_result = $rating_stmt->get_result();
                        $rating_stmt->close();
                        
                        $ratings = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
                        while($row = mysqli_fetch_assoc($rating_result)) {
                            $ratings[(int)$row['answer_text']] = $row['total'];
                        }
                        $labels = array_keys($ratings);
                        $data = array_values($ratings);
                        ?>

                        <div class="row g-4">
                            <div class="col-lg-7">
                                <div class="card shadow-sm border-0 rounded-4 p-4 h-100">
                                    <div class="chart-wrapper h-100 mt-0">
                                        <canvas id="rating_<?php echo $question_id; ?>"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <div class="card shadow-sm border-0 rounded-4 p-4 h-100">
                                    <h6 class="mb-3 text-muted fw-bold text-uppercase">Rating Breakdown</h6>
                                    <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th>Rating</th>
                                                <th class="text-end">Count</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($ratings as $star => $count): ?>
                                            <tr>
                                                <td class="py-2"><strong><?php echo $star; ?> ★</strong></td>
                                                <td class="py-2 text-end fw-medium"><?php echo $count; ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php elseif($question['question_type'] == 'mcq'): ?>
                        <!-- MCQ QUESTION -->
                        <?php
                        $mcq_stmt = $conn->prepare("SELECT answer_text, COUNT(*) AS total FROM answers WHERE question_id = ? GROUP BY answer_text");
                        $mcq_stmt->bind_param("i", $question_id);
                        $mcq_stmt->execute();
                        $mcq_result = $mcq_stmt->get_result();
                        $mcq_stmt->close();
                        
                        $total_stmt = $conn->prepare("SELECT COUNT(*) AS total_responses FROM answers WHERE question_id = ?");
                        $total_stmt->bind_param("i", $question_id);
                        $total_stmt->execute();
                        $total_result = $total_stmt->get_result();
                        $total_row = $total_result ? $total_result->fetch_assoc() : null;
                        $total_answers = $total_row['total_responses'] ?? 1;
                        if ($total_answers == 0) $total_answers = 1; // Prevent division by zero
                        $total_stmt->close();
                        
                        $most_selected_stmt = $conn->prepare("SELECT answer_text FROM answers WHERE question_id = ? GROUP BY answer_text ORDER BY COUNT(*) DESC LIMIT 1");
                        $most_selected_stmt->bind_param("i", $question_id);
                        $most_selected_stmt->execute();
                        $most_selected_result = $most_selected_stmt->get_result();
                        $most_selected_row = $most_selected_result->fetch_assoc();
                        $most_selected = $most_selected_row ? $most_selected_row['answer_text'] : "N/A";
                        $most_selected_stmt->close();
                        ?>

                        <div class="row g-3 mb-4">
                            <div class="col-sm-6">
                                <div class="card shadow-sm border-0 rounded-4 p-3 d-flex flex-row align-items-center">
                                    <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3 text-primary">
                                        <i class="bi bi-chat-square-text-fill fs-4"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted text-uppercase fw-bold">Responses</small>
                                        <h4 class="mb-0 fw-bold"><?php echo $response_count; ?></h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="card shadow-sm border-0 rounded-4 p-3 d-flex flex-row align-items-center">
                                    <div class="bg-success bg-opacity-10 p-3 rounded-3 me-3 text-success">
                                        <i class="bi bi-trophy-fill fs-4"></i>
                                    </div>
                                    <div style="min-width: 0;">
                                        <small class="text-muted text-uppercase fw-bold d-block">Most Selected</small>
                                        <h4 class="mb-0 fw-bold text-truncate" style="max-width: 180px;" title="<?php echo htmlspecialchars($most_selected); ?>">
                                            <?php echo htmlspecialchars($most_selected); ?>
                                        </h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-lg-7">
                                <div class="card shadow-sm border-0 rounded-4 p-4 h-100">
                                    <div class="chart-wrapper h-100 mt-0">
                                        <canvas id="chart_<?php echo $question_id; ?>"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <div class="card shadow-sm border-0 rounded-4 p-4 h-100">
                                    <h6 class="mb-3 text-muted fw-bold text-uppercase">Option Breakdown</h6>
                                    <div class="list-group list-group-flush border-top">
                                        <?php 
                                        mysqli_data_seek($mcq_result, 0);
                                        while($row = mysqli_fetch_assoc($mcq_result)): 
                                            $percentage = round(($row['total'] / $total_answers) * 100, 1);
                                        ?>
                                            <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                                <span class="text-truncate me-3"><?php echo htmlspecialchars($row['answer_text']); ?></span>
                                                <div class="text-end text-nowrap">
                                                    <span class="badge bg-primary rounded-pill"><?php echo $row['total']; ?></span>
                                                    <small class="text-muted ms-2 fw-medium"><?php echo $percentage; ?>%</small>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php elseif($question['question_type'] == 'text'): ?>
                        <!-- TEXT QUESTION -->
                        <?php
                        $text_stmt = $conn->prepare("SELECT answer_text FROM answers WHERE question_id = ?");
                        $text_stmt->bind_param("i", $question_id);
                        $text_stmt->execute();
                        $text_result = $text_stmt->get_result();
                        $text_stmt->close();
                        ?>
                        <h6 class="text-muted mb-3">Participant Feedback</h6>
                        <?php if(mysqli_num_rows($text_result) > 0): ?>
                            <div class="row g-3">
                                <?php while($row = mysqli_fetch_assoc($text_result)): ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card text-response-card h-100 p-4">
                                            <i class="bi bi-quote fs-2 text-primary mb-3"></i>
                                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($row['answer_text'])); ?></p>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">No text responses yet.</div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>

        <!-- ACTION BUTTONS -->
        <div class="no-print d-flex flex-column flex-sm-row gap-3 justify-content-center mt-5">
            <a href="survey_reports.php" class="btn btn-outline-secondary px-5 py-3">
                <i class="bi bi-arrow-left me-2"></i> Back to Reports
            </a>
            <button onclick="window.print()" class="btn btn-success px-5 py-3">
                <i class="bi bi-printer me-2"></i> Print Report
            </button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Rating Charts
            <?php 
            mysqli_data_seek($questions, 0);
            while($question = mysqli_fetch_assoc($questions)): 
                if($question['question_type'] == 'rating'):
                    $question_id = $question['question_id'];
                    $rating_stmt = $conn->prepare("SELECT answer_text, COUNT(*) AS total FROM answers WHERE question_id = ? GROUP BY answer_text ORDER BY answer_text ASC");
                    $rating_stmt->bind_param("i", $question_id);
                    $rating_stmt->execute();
                    $rating_result = $rating_stmt->get_result();
                    $rating_stmt->close();
                    $ratings = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
                    while($row = mysqli_fetch_assoc($rating_result)) {
                        $ratings[(int)$row['answer_text']] = $row['total'];
                    }
                    $labels = json_encode(array_keys($ratings));
                    $data = json_encode(array_values($ratings));
            ?>
                var ctx_rating_<?php echo $question_id; ?> = document.getElementById('rating_<?php echo $question_id; ?>').getContext('2d');
                var grad_rating_<?php echo $question_id; ?> = ctx_rating_<?php echo $question_id; ?>.createLinearGradient(0, 0, 0, 320);
                grad_rating_<?php echo $question_id; ?>.addColorStop(0, 'rgba(99, 102, 241, 0.9)');
                grad_rating_<?php echo $question_id; ?>.addColorStop(1, 'rgba(79, 70, 229, 0.2)');

                new Chart(ctx_rating_<?php echo $question_id; ?>, {
                    type: 'bar',
                    data: {
                        labels: <?php echo $labels; ?>,
                        datasets: [{
                            label: 'Responses',
                            data: <?php echo $data; ?>,
                            backgroundColor: grad_rating_<?php echo $question_id; ?>,
                            borderColor: '#4f46e5',
                            borderWidth: 2,
                            borderRadius: 10,
                            borderSkipped: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: {
                            duration: 1200,
                            easing: 'easeOutQuart'
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: 'rgba(30, 41, 55, 0.95)',
                                padding: 12,
                                cornerRadius: 8,
                                titleFont: { family: "'Segoe UI', sans-serif", size: 14, weight: 'bold' },
                                bodyFont: { family: "'Segoe UI', sans-serif", size: 13 },
                                boxPadding: 6
                            }
                        },
                        scales: {
                            y: { 
                                beginAtZero: true, 
                                grid: { color: 'rgba(226, 232, 240, 0.5)', drawBorder: false },
                                ticks: { font: { family: "'Segoe UI', sans-serif" }, padding: 10 }
                            },
                            x: { 
                                grid: { display: false, drawBorder: false },
                                ticks: { font: { family: "'Segoe UI', sans-serif" }, padding: 10 }
                            }
                        }
                    }
                });
            <?php 
                endif;
            endwhile; 
            ?>
            
            // MCQ Doughnut Charts
            <?php 
            mysqli_data_seek($questions, 0);
            while($question = mysqli_fetch_assoc($questions)): 
                if($question['question_type'] == 'mcq'):
                    $question_id = $question['question_id'];
                    $mcq_stmt = $conn->prepare("SELECT answer_text, COUNT(*) AS total FROM answers WHERE question_id = ? GROUP BY answer_text");
                    $mcq_stmt->bind_param("i", $question_id);
                    $mcq_stmt->execute();
                    $mcq_result = $mcq_stmt->get_result();
                    $mcq_stmt->close();
                    $labels = [];
                    $data_vals = [];
                    while($row = mysqli_fetch_assoc($mcq_result)) {
                        $labels[] = addslashes($row['answer_text']);
                        $data_vals[] = $row['total'];
                    }
            ?>
                new Chart(document.getElementById('chart_<?php echo $question_id; ?>'), {
                    type: 'doughnut',
                    data: {
                        labels: <?php echo json_encode($labels); ?>,
                        datasets: [{
                            data: <?php echo json_encode($data_vals); ?>,
                            backgroundColor: [
                                '#6366f1', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6'
                            ],
                            borderWidth: 3,
                            borderColor: '#ffffff',
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: {
                            duration: 1200,
                            easing: 'easeOutQuart'
                        },
                        plugins: {
                            legend: { 
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    font: { family: "'Segoe UI', sans-serif", size: 13 },
                                    usePointStyle: true,
                                    pointStyle: 'circle'
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(30, 41, 55, 0.95)',
                                padding: 12,
                                cornerRadius: 8,
                                titleFont: { family: "'Segoe UI', sans-serif", size: 14, weight: 'bold' },
                                bodyFont: { family: "'Segoe UI', sans-serif", size: 13 },
                                boxPadding: 6,
                                callbacks: {
                                    label: function(context) {
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const perc = ((context.raw / total) * 100).toFixed(1);
                                        return `${context.label}: ${context.raw} (${perc}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            <?php 
                endif;
            endwhile; 
            ?>
        });
    </script>
</body>
</html>