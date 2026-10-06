<?php
/*
 * -------------------------------------------------------
 * Page    : My Surveys
 *
 * Purpose : Displays all surveys the logged-in user has completed, sorted by most recent, with earned points.
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

$query = "
    SELECT 
        r.response_id, 
        r.submitted_at, 
        s.title, 
        s.points_reward,
        c.company_name
    FROM responses r
    JOIN surveys s ON r.survey_id = s.survey_id
    JOIN companies c ON s.company_id = c.company_id
    WHERE r.user_id = ?
    ORDER BY r.submitted_at DESC
";

// Prepare the SQL statement to securely handle user input and prevent SQL injection.
$stmt = $conn->prepare($query);
// Bind parameters to the prepared statement.
$stmt->bind_param("i", $user_id);
// Execute the prepared statement against the database.
$stmt->execute();
// Retrieve the result set from the executed prepared statement.
$result = $stmt->get_result();
$total_surveys = $result->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Surveys</title>
    
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
            background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%);
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

        .survey-card {
            border: none;
            border-radius: 18px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            height: 100%;
            overflow: hidden;
        }
        
        .survey-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
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
    <?php include("../includes/user_navbar.php"); ?>

    <div class="container dashboard-container">
        
        <!-- HERO SECTION -->
        <div class="hero-section">
            <div class="row align-items-center position-relative" style="z-index: 1;">
                <div class="col-lg-8">
                    <p class="mb-2 opacity-90 fs-5">
                        <i class="bi bi-file-earmark-check-fill me-2"></i>
                        SURVEY HISTORY
                    </p>
                    <h1 class="display-5 fw-bold mb-3">My Surveys</h1>
                    <p class="lead opacity-90 mb-0">
                        View the surveys you have completed and the points you've earned.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                    <div class="bg-white bg-opacity-10 rounded-4 p-4 d-inline-block text-center shadow-sm" style="backdrop-filter: blur(10px);">
                        <h2 class="display-4 fw-bold mb-0 text-white"><?php echo $total_surveys; ?></h2>
                        <small class="text-white text-opacity-75 text-uppercase fw-semibold">Completed Surveys</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- LIST SECTION -->
        <div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h4 class="section-title mb-0">Completed Surveys</h4>
            </div>
        </div>

        <?php if ($total_surveys > 0): ?>
            <div class="row g-4">
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="survey-card card shadow-sm">
                            <div class="card-body p-4 d-flex flex-column">
                                
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                        <i class="bi bi-check-circle-fill fs-4"></i>
                                    </div>
                                    <span class="badge bg-warning text-dark rounded-pill px-3 py-2 fw-bold shadow-sm">
                                        <i class="bi bi-star-fill me-1"></i> +<?php echo number_format($row['points_reward']); ?> pts
                                    </span>
                                </div>
                                
                                <h5 class="card-title fw-bold text-dark mb-1"><?php echo htmlspecialchars($row['title']); ?></h5>
                                <p class="text-muted small mb-3">
                                    <i class="bi bi-building me-1"></i> <?php echo htmlspecialchars($row['company_name']); ?>
                                </p>
                                
                                <div class="mt-auto pt-3 border-top">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="text-muted small">
                                            <i class="bi bi-calendar-check me-1"></i>
                                            <?php echo date("M j, Y", strtotime($row['submitted_at'])); ?>
                                        </span>
                                    </div>
                                    
                                    <a href="view_survey_response.php?response_id=<?php echo $row['response_id']; ?>" class="btn btn-outline-primary w-100 fw-semibold rounded-pill">
                                        <i class="bi bi-eye me-1"></i> View Answers
                                    </a>
                                </div>

                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <!-- EMPTY STATE -->
            <div class="empty-state bg-white rounded-4 shadow-sm border border-light">
                <div class="mb-4">
                    <i class="bi bi-file-earmark-x display-1 text-muted"></i>
                </div>
                <h4 class="text-dark fw-bold">You have not completed any surveys yet.</h4>
                <p class="text-muted mb-4">Complete surveys to earn points and redeem them for exciting rewards!</p>
                <a href="view_surveys.php" class="btn btn-primary px-5 py-3 rounded-pill fw-semibold">
                    <i class="bi bi-search me-2"></i> Browse Surveys
                </a>
            </div>
        <?php endif; ?>

    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <?php include('../includes/footer.php'); ?>
</body>
</html>
<?php 
$stmt->close();
$conn->close();
?>
