<?php

/*
 * -------------------------------------------------------
 * Page    : Take Survey
 *
 * Purpose : Renders a survey form with all its questions for the logged-in user to complete and submit.
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

if (!isset($_GET['survey_id'])) {
    die("Survey ID missing");
}

$survey_id = (int)$_GET['survey_id'];

$user_id = $_SESSION['user_id'];

/* Duplicate check */
$check_stmt = $conn->prepare("SELECT * FROM responses WHERE user_id = ? AND survey_id = ?");
$check_stmt->bind_param("ii", $user_id, $survey_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$check_stmt->close();

if ($check_result->num_rows > 0) {
    die("You already submitted this survey");
}

/* Get survey */
/* Get survey */
$survey_stmt = $conn->prepare("SELECT * FROM surveys WHERE survey_id = ? AND expiry_date >= CURDATE() AND status = 'active'");
$survey_stmt->bind_param("i", $survey_id);
$survey_stmt->execute();
$survey_result = $survey_stmt->get_result();
$survey_stmt->close();

if($survey_result->num_rows == 0)
{
    die("Survey not found or has expired.");
}

$survey = $survey_result->fetch_assoc();

/* Get questions */
$question_stmt = $conn->prepare("SELECT * FROM questions WHERE survey_id = ?");
$question_stmt->bind_param("i", $survey_id);
$question_stmt->execute();
$question_result = $question_stmt->get_result();
$question_stmt->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($survey['title']); ?></title>
    
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
        
        .survey-container {
            padding-top: 2rem;
            padding-bottom: 5rem;
        }
        
        .survey-hero {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
            border-radius: 20px;
            padding: 3rem 2.5rem;
            margin-bottom: 3rem;
            box-shadow: 0 15px 35px rgba(99, 102, 241, 0.25);
        }
        
        .question-card {
            border: none;
            border-radius: 18px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }
        
        .question-card:hover {
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
        }
        
        .option-card {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .option-card:hover {
            border-color: var(--primary);
            background: #f8fafc;
        }
        
        .option-card.selected {
            border-color: var(--primary);
            background: #f0f0ff;
        }
        
        .rating-btn {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            border: 2px solid #e2e8f0;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .rating-btn:hover {
            border-color: var(--primary);
            transform: scale(1.08);
        }
        
        .rating-btn.selected {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(99, 102, 241, 0.15);
        }
        
        .submit-btn {
            padding: 16px 40px;
            font-size: 1.15rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <?php include("../includes/user_navbar.php"); ?>

    <div class="container survey-container">
        
        <!-- SURVEY HERO -->
        <div class="survey-hero">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-5 fw-bold mb-3"><?php echo htmlspecialchars($survey['title']); ?></h1>
                    <?php if (!empty($survey['description'])): ?>
                        <p class="lead opacity-90 mb-0"><?php echo htmlspecialchars($survey['description']); ?></p>
                    <?php endif; ?>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <div class="badge bg-white bg-opacity-25 px-4 py-3 fs-6">
                        <i class="bi bi-clock me-2"></i>
                        Takes ~5 minutes
                    </div>
                </div>
            </div>
        </div>

        <form method="POST" action="submit_survey.php" id="surveyForm">
            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
            <input type="hidden" name="survey_id" value="<?php echo $survey_id; ?>">

            <?php 
            $question_number = 1;
            mysqli_data_seek($question_result, 0);
            while($question = $question_result->fetch_assoc()): 
                $question_id = $question['question_id'];
            ?>
                <div class="question-card card mb-5">
                    <div class="card-body p-4 p-md-5">
                        <!-- Question Header -->
                        <div class="d-flex align-items-start gap-3 mb-4">
                            <span class="badge bg-primary rounded-pill px-3 py-2 fs-6">
                                Q<?php echo $question_number; ?>
                            </span>
                            <h4 class="fw-semibold mb-0"><?php echo htmlspecialchars($question['question_text']); ?></h4>
                        </div>

                        <?php if($question['question_type'] == 'text'): ?>
                            <!-- TEXT QUESTION -->
                            <textarea 
                                name="answers[<?php echo $question_id; ?>]" 
                                class="form-control form-control-lg" 
                                rows="4" 
                                placeholder="Type your answer here..."
                                required></textarea>

                        <?php elseif($question['question_type'] == 'rating'): ?>
                            <!-- RATING QUESTION -->
                            <div class="d-flex gap-3 justify-content-center flex-wrap" id="rating-group-<?php echo $question_id; ?>">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <div class="rating-btn" 
                                         data-value="<?php echo $i; ?>"
                                         onclick="selectRating(this, <?php echo $question_id; ?>)">
                                        <?php echo $i; ?>
                                    </div>
                                <?php endfor; ?>
                            </div>
                            <input type="hidden" 
                                   name="answers[<?php echo $question_id; ?>]" 
                                   id="rating-input-<?php echo $question_id; ?>" 
                                   required>

                        <?php elseif($question['question_type'] == 'mcq'): ?>
                            <!-- MCQ QUESTION -->
                            <?php
                            $option_stmt = $conn->prepare("SELECT * FROM options WHERE question_id = ?");
                            $option_stmt->bind_param("i", $question_id);
                            $option_stmt->execute();
                            $option_result = $option_stmt->get_result();
                            $option_stmt->close();
                            ?>
                            <div class="row g-3" id="mcq-group-<?php echo $question_id; ?>">
                                <?php while($option = $option_result->fetch_assoc()): ?>
                                    <div class="col-12">
                                        <div class="option-card" 
                                             onclick="selectMCQ(this, <?php echo $question_id; ?>)"
                                             // Escape output to prevent Cross-Site Scripting (XSS) attacks.
                                             data-value="<?php echo htmlspecialchars($option['option_text']); ?>">
                                            <input type="radio" 
                                                   name="answers[<?php echo $question_id; ?>]" 
                                                   // Escape output to prevent Cross-Site Scripting (XSS) attacks.
                                                   value="<?php echo htmlspecialchars($option['option_text']); ?>"
                                                   class="d-none">
                                            <div class="fw-medium">
                                                <?php echo htmlspecialchars($option['option_text']); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php 
                $question_number++;
            endwhile; ?>

            <!-- SUBMIT SECTION -->
            <div class="text-center mt-5">
                <div class="d-grid d-md-block">
                    <button type="submit" 
                            class="btn btn-success submit-btn px-5">
                        <i class="bi bi-check-circle me-2"></i>
                        Submit Survey
                    </button>
                </div>
                <p class="text-muted mt-4 small">
                    Your feedback helps improve products and services
                </p>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // MCQ Selection
        function selectMCQ(el, qid) {
            const group = document.getElementById('mcq-group-' + qid);
            group.querySelectorAll('.option-card').forEach(card => {
                card.classList.remove('selected');
            });
            el.classList.add('selected');
            
            // Set the radio input
            const radio = el.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
        }
        
        // Rating Selection
        function selectRating(el, qid) {
            const group = document.getElementById('rating-group-' + qid);
            group.querySelectorAll('.rating-btn').forEach(btn => {
                btn.classList.remove('selected');
            });
            el.classList.add('selected');
            
            // Set hidden input
            const input = document.getElementById('rating-input-' + qid);
            if (input) input.value = el.getAttribute('data-value');
        }
        
        // Form validation feedback
        document.getElementById('surveyForm').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = `
                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                Submitting...
            `;
            submitBtn.disabled = true;
        });
        
        // Keyboard support for ratings
        document.addEventListener('keydown', function(e) {
            if (e.key >= '1' && e.key <= '5') {
                const activeElement = document.activeElement;
                if (activeElement && activeElement.closest('.question-card')) {
                    const ratingGroup = activeElement.closest('.question-card').querySelector('.rating-btn');
                    if (ratingGroup) {
                        // This is simplified - full implementation would need more logic
                    }
                }
            }
        });
    </script>
</body>
</html>