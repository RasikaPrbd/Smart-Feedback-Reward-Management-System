<?php

/*
 * -------------------------------------------------------
 * Page    : View Response Details
 *
 * Purpose : Displays the complete answer set submitted by a specific user for a specific survey.
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

if (!isset($_GET['id'])) {
    die("Response ID missing.");
}

$response_id = (int)$_GET['id'];
$company_id = $_SESSION['company_id'];

/* Verify that this response belongs to a survey owned by the logged-in company */

$check_stmt = $conn->prepare("
SELECT
    responses.response_id,
    responses.submitted_at,

    CASE
        WHEN users.is_deleted = 1 THEN 'Deleted Account'
        ELSE users.name
    END AS user_name,

    surveys.title AS survey_title

FROM responses

JOIN users
ON responses.user_id = users.user_id

JOIN surveys
ON responses.survey_id = surveys.survey_id

WHERE responses.response_id = ?
AND surveys.company_id = ?
");
$check_stmt->bind_param("ii", $response_id, $company_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$check_stmt->close();

if ($check_result->num_rows == 0) {
    die("Response not found.");
}

$response_info = $check_result->fetch_assoc();

/* Get all answers for this response */

// Prepare the SQL statement to securely handle user input and prevent SQL injection.
$stmt = $conn->prepare("
SELECT
    questions.question_text,
    answers.answer_text,
    questions.question_type
FROM answers
JOIN questions
ON answers.question_id = questions.question_id
WHERE answers.response_id = ?
");
// Bind parameters to the prepared statement.
$stmt->bind_param("i", $response_id);
// Execute the prepared statement against the database.
$stmt->execute();
// Retrieve the result set from the executed prepared statement.
$result = $stmt->get_result();
$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Response Details</title>
    
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
        
        .answer-card {
            border: none;
            border-radius: 18px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }
        
        .question-header {
            background: #f8fafc;
            border-radius: 12px 12px 0 0;
        }
        
        .answer-content {
            background: white;
            border-radius: 0 0 12px 12px;
        }
        
        .section-title {
            font-size: 1.35rem;
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
                        <i class="bi bi-person-check-fill me-2"></i>
                        INDIVIDUAL RESPONSE
                    </p>
                    <h1 class="display-5 fw-bold mb-3">Response Details</h1>
                    <p class="lead opacity-90 mb-0">
                        <?php echo htmlspecialchars($response_info['survey_title']); ?> • 
                        <?php echo htmlspecialchars($response_info['user_name']); ?>
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                    <div class="bg-white bg-opacity-25 rounded-4 p-4 d-inline-block text-center">
                        <small class="opacity-75 d-block">Submitted</small>
                        <strong><?php echo date('M j, Y • g:i A', strtotime($response_info['submitted_at'])); ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- RESPONSE SUMMARY -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card shadow-sm text-center p-4">
                    <i class="bi bi-person-fill text-primary fs-2 mb-3"></i>
                    <h5 class="text-muted">Respondent</h5>
                    <h4 class="fw-semibold"><?php echo htmlspecialchars($response_info['user_name']); ?></h4>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm text-center p-4">
                    <i class="bi bi-clipboard-data text-success fs-2 mb-3"></i>
                    <h5 class="text-muted">Survey</h5>
                    <h4 class="fw-semibold"><?php echo htmlspecialchars($response_info['survey_title']); ?></h4>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm text-center p-4">
                    <i class="bi bi-clock-history text-info fs-2 mb-3"></i>
                    <h5 class="text-muted">Submitted</h5>
                    <h4 class="fw-semibold"><?php echo date('M j, Y', strtotime($response_info['submitted_at'])); ?></h4>
                </div>
            </div>
        </div>

        <!-- QUESTIONS & ANSWERS -->
        <div class="card shadow">
            <div class="card-header bg-white border-0 py-4">
                <h4 class="section-title mb-0">Feedback Summary</h4>
            </div>
            <div class="card-body p-5">
                <?php if (mysqli_num_rows($result) == 0): ?>
                    <div class="alert alert-warning text-center py-5">
                        <i class="bi bi-inbox display-4 d-block mb-3"></i>
                        No answers found for this response.
                    </div>
                <?php else: ?>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <div class="answer-card mb-5">
                            <!-- Question -->
                            <div class="question-header p-4">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="badge bg-primary rounded-pill px-3 py-2">Q</span>
                                    <h5 class="mb-0 fw-semibold"><?php echo htmlspecialchars($row['question_text']); ?></h5>
                                </div>
                            </div>
                            
                            <!-- Answer -->
                            <div class="answer-content p-5">
                                <?php if ($row['question_type'] == 'rating'): ?>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="fs-1 text-warning">
                                            <?php echo str_repeat('★', (int)$row['answer_text']); ?>
                                        </div>
                                        <div>
                                            <span class="badge bg-light text-dark fs-5"><?php echo htmlspecialchars($row['answer_text']); ?>/5</span>
                                        </div>
                                    </div>
                                <?php elseif ($row['question_type'] == 'mcq'): ?>
                                    <div class="d-flex align-items-center gap-3">
                                        <i class="bi bi-check-circle-fill text-success fs-3"></i>
                                        <h5 class="mb-0"><?php echo htmlspecialchars($row['answer_text']); ?></h5>
                                    </div>
                                <?php else: ?>
                                    <div class="bg-light border-start border-4 border-primary p-4 rounded">
                                        <i class="bi bi-quote fs-2 text-primary mb-3 d-block"></i>
                                        <p class="mb-0 lead"><?php echo nl2br(htmlspecialchars($row['answer_text'])); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- NAVIGATION -->
        <div class="mt-5 text-center">
            <a href="view_responses.php" class="btn btn-outline-primary px-5 py-3">
                <i class="bi bi-arrow-left me-2"></i>
                Back to All Responses
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>