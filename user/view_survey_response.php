<?php
/*
 * -------------------------------------------------------
 * Page    : View Survey Response
 *
 * Purpose : Shows a read-only view of the logged-in user's submitted answers for a specific completed survey.
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

if (!isset($_GET['response_id'])) {
    // Redirect the user to the appropriate page after the action completes.
    header("Location: my_surveys.php");
    exit();
}

$response_id = (int)$_GET['response_id'];

    // Ensure the response belongs to the logged-in user to prevent unauthorized access.
$query = "
    SELECT 
        s.title, 
        s.description, 
        s.points_reward,
        r.submitted_at,
        q.question_text, 
        q.question_type,
        a.answer_text
    FROM responses r
    JOIN surveys s ON r.survey_id = s.survey_id
    JOIN answers a ON r.response_id = a.response_id
    JOIN questions q ON a.question_id = q.question_id
    WHERE r.response_id = ? AND r.user_id = ?
    ORDER BY q.question_id ASC
";

// Prepare the SQL statement to securely handle user input and prevent SQL injection.
$stmt = $conn->prepare($query);
// Bind parameters to the prepared statement.
$stmt->bind_param("ii", $response_id, $user_id);
// Execute the prepared statement against the database.
$stmt->execute();
// Retrieve the result set from the executed prepared statement.
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Response not found or doesn't belong to this user
    $stmt->close();
    // Redirect the user to the appropriate page after the action completes.
    header("Location: my_surveys.php");
    exit();
}

// Fetch all rows into an array
$answers = [];
// Iterate through the fetched records to display them in the UI.
while ($row = $result->fetch_assoc()) {
    $answers[] = $row;
}
$stmt->close();

// The survey details are the same for every row, so we extract from the first row
$survey = $answers[0];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Survey Response</title>
    
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

        .question-card {
            border: none;
            border-radius: 18px;
            background: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
        }
        
        .question-badge {
            background: var(--primary);
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .answer-block {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1.25rem;
            border-left: 4px solid var(--primary);
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
                        <a href="my_surveys.php" class="text-white text-decoration-none"><i class="bi bi-arrow-left me-2"></i>Back to My Surveys</a>
                    </p>
                    <h1 class="display-5 fw-bold mb-2"><?php echo htmlspecialchars($survey['title']); ?></h1>
                    <?php if (!empty($survey['description'])): ?>
                        <p class="lead opacity-90 mb-3"><?php echo htmlspecialchars($survey['description']); ?></p>
                    <?php endif; ?>
                    
                    <div class="d-flex align-items-center gap-3 mt-4">
                        <span class="badge bg-white text-dark rounded-pill px-3 py-2 fw-bold shadow-sm">
                            <i class="bi bi-calendar-check me-1 text-primary"></i> 
                            Completed: <?php echo date("M j, Y, g:i a", strtotime($survey['submitted_at'])); ?>
                        </span>
                        <span class="badge bg-warning text-dark rounded-pill px-3 py-2 fw-bold shadow-sm">
                            <i class="bi bi-star-fill me-1"></i> Earned: +<?php echo number_format($survey['points_reward']); ?> pts
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ANSWERS SECTION -->
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="mb-4 d-flex align-items-center">
                    <i class="bi bi-card-checklist fs-3 text-primary me-3"></i>
                    <h4 class="mb-0 fw-bold text-dark">Your Responses</h4>
                </div>

                <?php 
                $q_num = 1;
                foreach ($answers as $ans): 
                ?>
                    <div class="question-card p-4">
                        <div class="d-flex align-items-start mb-3">
                            <div class="question-badge me-3 shadow-sm flex-shrink-0">
                                <?php echo $q_num++; ?>
                            </div>
                            <h5 class="fw-semibold text-dark mb-0 pt-1 lh-base">
                                <?php echo htmlspecialchars($ans['question_text']); ?>
                            </h5>
                        </div>
                        
                        <div class="ms-5">
                            <div class="answer-block">
                                <?php if ($ans['question_type'] === 'rating'): ?>
                                    <div class="text-warning fs-5">
                                        <?php 
                                            $rating = (int)$ans['answer_text'];
                                            for($i = 1; $i <= 5; $i++) {
                                                if($i <= $rating) {
                                                    echo '<i class="bi bi-star-fill me-1"></i>';
                                                } else {
                                                    echo '<i class="bi bi-star me-1 text-muted opacity-50"></i>';
                                                }
                                            }
                                        ?>
                                        <span class="text-dark ms-2 fw-bold fs-6">(<?php echo $rating; ?>/5)</span>
                                    </div>
                                <?php elseif ($ans['question_type'] === 'mcq'): ?>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-check2-circle text-primary fs-5 me-2"></i>
                                        <span class="fw-medium text-dark"><?php echo htmlspecialchars($ans['answer_text']); ?></span>
                                    </div>
                                <?php else: ?>
                                    <p class="mb-0 text-dark">
                                        <?php echo nl2br(htmlspecialchars($ans['answer_text'])); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="text-center mt-5">
                    <a href="my_surveys.php" class="btn btn-outline-primary px-4 py-2 rounded-pill fw-semibold">
                        <i class="bi bi-arrow-left me-2"></i> Return to My Surveys
                    </a>
                </div>
            </div>
        </div>

    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <?php include('../includes/footer.php'); ?>
</body>
</html>
<?php 
$conn->close();
?>
