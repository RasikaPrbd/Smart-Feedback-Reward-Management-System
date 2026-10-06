<?php 

/*
 * -------------------------------------------------------
 * Page    : Add Question
 *
 * Purpose : Allows a company to add a new question of type Rating, Multiple Choice, or Text to one of their surveys.
 * -------------------------------------------------------
 */

// Initialize the session to track user authentication state.
session_start();
// Include the database connection configuration to interact with the database.
include("../config/db.php");
include("../includes/survey_helper.php");

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

$message = "";
$messageType = "success";

// Get survey details for header
$company_id = $_SESSION['company_id'];

// Prepare the SQL statement to securely handle user input and prevent SQL injection.
$stmt = $conn->prepare("
SELECT title
FROM surveys
WHERE survey_id = ?
AND company_id = ?
");

// Bind parameters to the prepared statement.
$stmt->bind_param("ii", $survey_id, $company_id);

// Execute the prepared statement against the database.
$stmt->execute();

$survey_info = $stmt->get_result();

if($survey_info->num_rows == 0)
{
    die("Survey not found.");
}

$survey = $survey_info->fetch_assoc();

$stmt->close();

// Only process the form when the request was sent via POST to prevent accidental GET submissions.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $question_text = trim($_POST['question_text']);

    $question_type = trim($_POST['question_type']);

    if (
        $question_type == "mcq" &&
        (
            empty(trim($_POST['option1'])) ||
            empty(trim($_POST['option2']))
        )
    ) {

        $message = "Multiple Choice questions must have at least two answer options.";
        $messageType = "danger";

    } else {

        // Prepare the SQL statement to securely handle user input and prevent SQL injection.
        $stmt = $conn->prepare("
        INSERT INTO questions
        (
            survey_id,
            question_text,
            question_type
        )
        VALUES
        (
            ?, ?, ?
        )
        ");

        // Bind parameters to the prepared statement.
        $stmt->bind_param(
            "iss",
            $survey_id,
            $question_text,
            $question_type
        );

        if ($stmt->execute()) {

            $question_id = $stmt->insert_id;

            if ($question_type == "mcq") {

                $optionStmt = $conn->prepare("
                INSERT INTO options
                (
                    question_id,
                    option_text
                )
                VALUES
                (
                    ?, ?
                )
                ");

                for ($i = 1; $i <= 4; $i++) {

                    if (!empty($_POST["option$i"])) {

                        $option = trim($_POST["option$i"]);

                        $optionStmt->bind_param(
                            "is",
                            $question_id,
                            $option
                        );

                        $optionStmt->execute();
                    }
                }

                $optionStmt->close();
            }

            $_SESSION['success_msg'] = "✓ Question added successfully.";
            updateSurveyStatus($conn, $survey_id);
            header("Location: add_question.php?survey_id=" . $survey_id);
            exit();

        } else {

            $message = "Unable to add question.";
            $messageType = "danger";

        }

        $stmt->close();
    }
}

$questions = mysqli_query(
    $conn,
    "
    SELECT *
    FROM questions
    WHERE survey_id='$survey_id'
    "
);

$count = mysqli_num_rows($questions);

if ($count >= 3 && $count <= 5) {

    $points = 20;

} elseif ($count >= 6 && $count <= 8) {

    $points = 40;

} elseif ($count >= 9 && $count <= 11) {

    $points = 60;

} elseif ($count >= 12) {

    $points = 80;

} else {

    $points = 0;

}

// Prepare the SQL statement to securely handle user input and prevent SQL injection.
$stmt = $conn->prepare("
    UPDATE surveys
    SET points_reward = ?
    WHERE survey_id = ?
");

// Bind parameters to the prepared statement.
$stmt->bind_param("ii", $points, $survey_id);
// Execute the prepared statement against the database.
$stmt->execute();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Questions • <?php echo htmlspecialchars($survey['title'] ?? 'Survey'); ?></title>
    
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
        
        .header-section {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
            border-radius: 20px;
            padding: 2.5rem 2.5rem;
            margin-bottom: 2.5rem;
            box-shadow: 0 15px 35px rgba(99, 102, 241, 0.25);
        }
        
        .form-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }
        
        .question-list {
            max-height: 420px;
            overflow-y: auto;
        }
        
        .form-control, .form-select {
            border-radius: 12px;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(99, 102, 241, 0.15);
        }
        
        .option-input {
            transition: all 0.2s ease;
        }
        
        .option-input:focus {
            border-color: var(--primary);
        }
        
        .submit-btn {
            padding: 14px 40px;
            font-size: 1.1rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <?php include("../includes/company_navbar.php"); ?>

    <div class="container dashboard-container">
        
        <!-- HEADER -->
        <div class="header-section">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
                <div>
                    <p class="mb-1 opacity-90">SURVEY BUILDER</p>
                    <h1 class="display-6 fw-bold mb-2"><?php echo htmlspecialchars($survey['title'] ?? 'New Survey'); ?></h1>
                    <p class="lead opacity-90 mb-0">Add questions to engage your audience</p>
                </div>
                <div class="text-md-end flex-shrink-0">

                    <span class="badge bg-white text-dark px-4 py-3 fs-6 d-block mb-2">
                        <?php echo $count; ?> Questions Added
                    </span>

                    <?php if($points > 0){ ?>

                        <span class="badge bg-warning text-dark px-4 py-3 fs-6">
                            🏆 Reward: <?php echo $points; ?> Points
                        </span>

                    <?php } else { ?>

                        <span class="badge border border-light text-white px-4 py-3 fs-6">
                            Minimum 3 Questions Required
                        </span>

                    <?php } ?>
                    <?php
                    if ($count < 3) {
                    ?>
                        <small class="d-block mt-2 text-white-50">
                            Add <?php echo 3 - $count; ?> more question(s) to unlock rewards.
                        </small>
                    <?php
                    } else {
                    ?>
                        <small class="d-block mt-2 text-white-50">
                            Reward points are calculated automatically based on the number of survey questions.
                        </small>
                    <?php
                    }
                    ?>
                </div>
            </div>
        </div>

        <div class="row g-4">
            
            <!-- QUESTION BUILDER FORM -->
            <div class="col-lg-7">
                <div class="form-card card">
                    <div class="card-body p-5">
                        <h4 class="mb-4">Add New Question</h4>
                        
                        <?php if (isset($_SESSION['success_msg'])): ?>
                            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert" id="successAlert">
                                <strong class="d-flex align-items-center">
                                    <?php echo $_SESSION['success_msg']; ?>
                                </strong>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <?php unset($_SESSION['success_msg']); ?>
                        <?php endif; ?>
                        
                        <?php if ($message != "") { ?>
                            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
                                <?php echo $message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php } ?>

                        <form method="POST" id="questionForm" onsubmit="return validateMCQ();">
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Question Text</label>
                                <textarea 
                                    name="question_text" 
                                    class="form-control" 
                                    rows="4"
                                    placeholder="e.g. How satisfied are you with our service?"
                                    required></textarea>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Question Type</label>
                                <select 
                                    name="question_type" 
                                    id="question_type" 
                                    class="form-select form-select-lg"
                                    onchange="toggleOptions()"
                                    required>
                                    <option value="text">Text Response</option>
                                    <option value="rating">Rating (1-5)</option>
                                    <option value="mcq">Multiple Choice</option>
                                </select>
                            </div>

                            <!-- MCQ Options -->
                            <div id="mcqOptions" style="display: none;">
                                <label class="form-label fw-semibold mb-3">Answer Options</label>
                                <div class="mb-3">
                                    <input type="text" 
                                           name="option1" 
                                           class="form-control option-input" 
                                           placeholder="Option 1 (e.g. Excellent)">
                                </div>
                                <div class="mb-3">
                                    <input type="text" 
                                           name="option2" 
                                           class="form-control option-input" 
                                           placeholder="Option 2">
                                </div>
                                <div class="mb-3">
                                    <input type="text" 
                                           name="option3" 
                                           class="form-control option-input" 
                                           placeholder="Option 3">
                                </div>
                                <div class="mb-4">
                                    <input type="text" 
                                           name="option4" 
                                           class="form-control option-input" 
                                           placeholder="Option 4">
                                </div>
                                <small class="text-muted">Leave empty if you need fewer options</small>
                            </div>

                            <div class="mt-5">
                                <button type="submit" class="btn btn-primary submit-btn w-100 mb-3">
                                    <i class="bi bi-plus-circle me-2"></i>
                                    Add Question
                                </button>
                                
                                <?php if ($count >= 3): ?>
                                    <a href="manage_questions.php?survey_id=<?php echo $survey_id; ?>" class="btn btn-success submit-btn w-100 py-3 d-flex flex-column align-items-center">
                                        <div><i class="bi bi-check2-circle me-2"></i>Done Adding Questions</div>
                                        <small class="text-white-50 mt-1 fw-normal" style="font-size: 0.85rem;">Return to Question Manager</small>
                                    </a>
                                <?php else: ?>
                                    <div class="alert alert-warning text-center mb-0">
                                        <strong>Minimum 3 questions are required before finishing this survey.</strong>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- CURRENT QUESTIONS + TIPS -->
            <div class="col-lg-5">
                <!-- Current Questions -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-4">
                        <h5 class="mb-0">Current Questions <span class="text-muted">(<?php echo $count; ?>)</span></h5>
                    </div>
                    <div class="card-body p-0 question-list">
                        <?php 
                        mysqli_data_seek($questions, 0);
                        if ($count > 0): 
                        ?>
                            <ul class="list-group list-group-flush">
                                <?php while ($q = mysqli_fetch_assoc($questions)): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                        <div class="flex-grow-1">
                                            <div class="fw-medium"><?php echo htmlspecialchars($q['question_text']); ?></div>
                                            <small class="text-muted text-uppercase">
                                                <?php echo htmlspecialchars($q['question_type']); ?>
                                            </small>
                                        </div>
                                        <span class="badge bg-light text-dark">
                                            <?php echo ucfirst($q['question_type']); ?>
                                        </span>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        <?php else: ?>
                            <div class="p-5 text-center text-muted">
                                <i class="bi bi-question-circle display-1 mb-3"></i>
                                <p>No questions added yet.<br>Start building your survey!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Tips -->
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="mb-4">
                            <i class="bi bi-lightbulb-fill text-warning"></i> Best Practices
                        </h5>
                        <div class="small">
                            <div class="mb-3">• Keep questions clear and concise</div>
                            <div class="mb-3">• Use rating scales for satisfaction metrics</div>
                            <div class="mb-3">• Limit MCQ options to 4-5 choices</div>
                            <div class="mb-3">• Mix question types for better engagement</div>
                            <div class="mb-3">• Minimum 3 questions are required</div>
                            <div class="mb-3">• Reward points are calculated automatically based on the number of questions</div>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function toggleOptions() {

            const type = document.getElementById("question_type").value;
            const mcqDiv = document.getElementById("mcqOptions");

            const option1 = document.querySelector('[name="option1"]');
            const option2 = document.querySelector('[name="option2"]');

            if (type === "mcq") {

                mcqDiv.style.display = "block";

                option1.required = true;
                option2.required = true;

            } else {

                mcqDiv.style.display = "none";

                option1.required = false;
                option2.required = false;

            }
        }

        function validateMCQ() {

            const type = document.getElementById("question_type").value;

            if (type === "mcq") {

                const option1 = document.querySelector('[name="option1"]').value.trim();
                const option2 = document.querySelector('[name="option2"]').value.trim();

                if (option1 === "" || option2 === "") {

                    alert("Please enter at least two answer options.");

                    return false;
                }
            }

            return true;
        }
        
        // Initialize on load
        window.onload = function() {
            toggleOptions();
            
            // Auto-focus first input
            const firstInput = document.querySelector('textarea[name="question_text"]');
            if (firstInput) firstInput.focus();
        };
        
        // Form submit visual feedback
        document.getElementById('questionForm').addEventListener('submit', function() {
            const btn = this.querySelector('button[type="submit"]');
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Adding Question...`;
            btn.disabled = true;
        });

        // Auto-hide alert after 4 seconds
        const successAlert = document.getElementById('successAlert');
        if (successAlert) {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(successAlert);
                bsAlert.close();
            }, 4000);
        }
    </script>
</body>
</html>