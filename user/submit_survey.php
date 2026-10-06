<?php

/*
 * -------------------------------------------------------
 * Page    : Submit Survey
 *
 * Purpose : Processes a submitted survey form. Validates answers, stores responses, and awards points to the user.
 * -------------------------------------------------------
 */

// Initialize the session to track user authentication state.
session_start();

// Include the database connection configuration to interact with the database.
include("../config/db.php");

// Include the CSRF helper to generate and validate security tokens for forms.
include("../includes/csrf_helper.php");

// Verify that the user is logged in before granting access.
if (!isset($_SESSION['user_id'])) {
    // Redirect the user to the appropriate page after the action completes.
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
if($_SERVER["REQUEST_METHOD"] != "POST")
{
    // Redirect the user to the appropriate page after the action completes.
    header("Location: view_surveys.php");
    exit();
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    csrf_abort();
}

$survey_id = $_POST['survey_id'];

$answers = $_POST['answers'];

/* Check for duplicate submission */
$check_stmt = $conn->prepare("SELECT response_id FROM responses WHERE user_id = ? AND survey_id = ? LIMIT 1");
$check_stmt->bind_param("ii", $user_id, $survey_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    $check_stmt->close();
    die("Error: You have already submitted a response for this survey.");
}
$check_stmt->close();

try {
    // Begin a database transaction to ensure multiple dependent queries execute safely without partial updates.
    $conn->begin_transaction();

    /* Create response */
    $response_stmt = $conn->prepare("INSERT INTO responses(user_id, survey_id) VALUES(?, ?)");
    $response_stmt->bind_param("ii", $user_id, $survey_id);
    $response_stmt->execute();
    $response_id = $response_stmt->insert_id;
    $response_stmt->close();

    /* Save answers */
    if (is_array($answers)) {
        $answer_stmt = $conn->prepare("INSERT INTO answers(response_id, question_id, answer_text) VALUES(?, ?, ?)");
        foreach($answers as $question_id => $answer_text) {
            $answer_stmt->bind_param("iis", $response_id, $question_id, $answer_text);
            $answer_stmt->execute();
        }
        $answer_stmt->close();
    }

    /* Get survey reward points */
    $survey_stmt = $conn->prepare("SELECT points_reward, title FROM surveys WHERE survey_id = ?");
    $survey_stmt->bind_param("i", $survey_id);
    $survey_stmt->execute();
    $survey_result = $survey_stmt->get_result();
    $survey = $survey_result->fetch_assoc();
    $survey_stmt->close();
    
    if (!$survey) {
        throw new Exception("Invalid survey.");
    }

    $points = $survey['points_reward'];
    
    // Update the user's point balance in the database after completing a survey or redeeming a reward.
    $update_points = $conn->prepare("UPDATE users SET points = points + ? WHERE user_id = ?");
    $update_points->bind_param("ii", $points, $user_id);
    $update_points->execute();
    $update_points->close();

    // Commit the transaction to permanently save the changes to the database.
    $conn->commit();
    
    $survey_title = $survey['title'];

} catch (Exception $e) {
    // Rollback the transaction if an error occurs to prevent data corruption.
    $conn->rollback();
    die("An error occurred while submitting your survey: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survey Completed</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --primary: #6366f1;
            --success: #10b981;
            --dark: #1e2937;
        }
        
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
        }
        
        .success-container {
            padding-top: 3rem;
            padding-bottom: 5rem;
        }
        
        .success-hero {
            background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
            color: white;
            border-radius: 24px;
            padding: 4rem 2.5rem;
            text-align: center;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(16, 185, 129, 0.3);
        }
        
        .points-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            border: 3px solid #10b981;
        }
        
        .next-action-btn {
            transition: all 0.3s ease;
            padding: 14px 32px;
            font-weight: 600;
        }
        
        .next-action-btn:hover {
            transform: translateY(-3px);
        }
    </style>
</head>
<body>
    <?php include("../includes/user_navbar.php"); ?>

    <div class="container success-container">
        
        <!-- SUCCESS HERO -->
        <div class="success-hero mb-5">
            <div class="mb-4">
                <i class="bi bi-check-circle-fill display-1"></i>
            </div>
            <h1 class="display-5 fw-bold mb-3">Survey Completed Successfully!</h1>
            <p class="lead opacity-90 mb-0">Thank you for sharing your valuable feedback.</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-7">
                
                <!-- POINTS EARNED -->
                <div class="points-card p-5 text-center mb-5">
                    <div class="d-flex justify-content-center align-items-center gap-3 mb-3">
                        <i class="bi bi-coin fs-1 text-warning"></i>
                        <h2 class="mb-0">+<?php echo $points; ?> Points</h2>
                    </div>
                    <p class="text-muted fs-5 mb-0">Added to your balance</p>
                </div>

                <!-- SURVEY INFO -->
                <div class="card shadow-sm mb-5">
                    <div class="card-body p-5 text-center">
                        <h5 class="text-muted mb-2">You completed</h5>
                        <h4 class="fw-semibold"><?php echo htmlspecialchars($survey_title); ?></h4>
                    </div>
                </div>

                <!-- THANK YOU MESSAGE -->
                <div class="text-center mb-5">
                    <p class="lead text-muted">
                        Your input helps companies improve their products and services.<br>
                        We truly appreciate your participation!
                    </p>
                </div>

                <!-- NEXT ACTIONS -->
                <div class="d-flex flex-column flex-md-row gap-3 justify-content-center">
                    <a href="view_surveys.php" 
                       class="btn btn-primary next-action-btn flex-fill">
                        <i class="bi bi-list-check me-2"></i>
                        Browse More Surveys
                    </a>
                    <a href="view_rewards.php" 
                       class="btn btn-success next-action-btn flex-fill">
                        <i class="bi bi-gift me-2"></i>
                        Check Rewards
                    </a>
                    <a href="dashboard.php" 
                       class="btn btn-outline-primary next-action-btn flex-fill">
                        <i class="bi bi-house-door me-2"></i>
                        Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Celebration effect
        document.addEventListener('DOMContentLoaded', function() {
            const hero = document.querySelector('.success-hero');
            if (hero) {
                for (let i = 0; i < 25; i++) {
                    const dot = document.createElement('div');
                    dot.style.position = 'absolute';
                    dot.style.width = '8px';
                    dot.style.height = '8px';
                    dot.style.borderRadius = '50%';
                    dot.style.backgroundColor = ['#ffffff', '#fef08c', '#a5f3fc'][Math.floor(Math.random() * 3)];
                    dot.style.left = Math.random() * 100 + '%';
                    dot.style.top = Math.random() * 60 + '%';
                    dot.style.opacity = Math.random() * 0.7 + 0.4;
                    dot.style.animation = `float ${Math.random() * 4 + 3}s linear infinite`;
                    hero.appendChild(dot);
                    
                    setTimeout(() => dot.remove(), 6000);
                }
            }
        });
        
        // Add floating animation
        const style = document.createElement('style');
        style.innerHTML = `
            @keyframes float {
                0% { transform: translateY(0) rotate(0deg); }
                100% { transform: translateY(-120px) rotate(25deg); }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>