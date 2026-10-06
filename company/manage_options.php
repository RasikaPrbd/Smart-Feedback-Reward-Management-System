<?php
/*
 * -------------------------------------------------------
 * Page    : Manage Options
 *
 * Purpose : Displays all answer options for a specific Multiple Choice question and allows management actions.
 * -------------------------------------------------------
 */

// Initialize the session to track user authentication state.
session_start();
// Include the database connection configuration to interact with the database.
include("../config/db.php");
include("../includes/survey_helper.php");
// Include the CSRF helper to generate and validate security tokens for forms.
include("../includes/csrf_helper.php");

// Verify that the company is logged in before granting access.
if (!isset($_SESSION['company_id'])) {
    // Redirect the user to the appropriate page after the action completes.
    header("Location: login.php");
    exit();
}

if (!isset($_GET['question_id']) || !isset($_GET['survey_id'])) {
    // Redirect the user to the appropriate page after the action completes.
    header("Location: manage_surveys.php");
    exit();
}

$question_id = (int)$_GET['question_id'];
$survey_id = (int)$_GET['survey_id'];
$company_id = $_SESSION['company_id'];

// Verify ownership
    // Enforce ownership by filtering surveys to only those belonging to the logged-in company.
$check_stmt = $conn->prepare("
    SELECT q.question_text, q.question_type, s.title as survey_title, s.expiry_date 
    FROM questions q 
    JOIN surveys s ON q.survey_id = s.survey_id 
    WHERE q.question_id = ? AND s.survey_id = ? AND s.company_id = ?
");
$check_stmt->bind_param("iii", $question_id, $survey_id, $company_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    $check_stmt->close();
    die("Question not found or access denied.");
}
$question = $result->fetch_assoc();
$check_stmt->close();

if ($question['question_type'] !== 'mcq') {
    die("Only Multiple Choice questions have options.");
}

$is_expired = strtotime($question['expiry_date']) < time();

// Handle Add Option on the same page for better UX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_option']) && !$is_expired) {
    $option_text = trim($_POST['option_text']);
    if (!empty($option_text)) {
        $insert_stmt = $conn->prepare("INSERT INTO options (question_id, option_text) VALUES (?, ?)");
        $insert_stmt->bind_param("is", $question_id, $option_text);
        if ($insert_stmt->execute()) {
            $_SESSION['success_msg'] = "✓ Option added successfully.";
        } else {
            $_SESSION['error_msg'] = "Error adding option.";
        }
        $insert_stmt->close();
        // Redirect the user to the appropriate page after the action completes.
        header("Location: manage_options.php?question_id=$question_id&survey_id=$survey_id");
        exit();
    }
}

// Fetch options
$opt_stmt = $conn->prepare("SELECT * FROM options WHERE question_id = ? ORDER BY option_id ASC");
$opt_stmt->bind_param("i", $question_id);
$opt_stmt->execute();
$options = $opt_stmt->get_result();
$total_options = $options->num_rows;
$opt_stmt->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Options</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
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
        .option-card {
            border: none;
            border-radius: 18px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin-bottom: 1rem;
        }
        .option-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px -5px rgb(0 0 0 / 0.1) !important;
        }
    </style>
</head>
<body>
    <?php include("../includes/company_navbar.php"); ?>

    <div class="container dashboard-container">
        
        <?php if (isset($_SESSION['success_msg'])): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                <strong class="d-flex align-items-center">
                    <?php echo $_SESSION['success_msg']; ?>
                </strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success_msg']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_msg'])): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                <strong class="d-flex align-items-center">
                    <?php echo $_SESSION['error_msg']; ?>
                </strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error_msg']); ?>
        <?php endif; ?>

        <?php if ($is_expired): ?>
            <div class="alert alert-warning shadow-sm border-0 mb-4 d-flex align-items-center" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-4 me-3 text-warning"></i>
                <div>
                    <h5 class="alert-heading mb-1 fw-bold">Survey Expired</h5>
                    <p class="mb-0">This survey has expired. Modifying options is disabled.</p>
                </div>
            </div>
        <?php endif; ?>

        <div class="hero-section position-relative">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <p class="mb-2 opacity-90 fs-5">
                        <i class="bi bi-list-ul me-2"></i>
                        MANAGE OPTIONS
                    </p>
                    <h2 class="display-6 fw-bold mb-3"><?php echo htmlspecialchars($question['question_text']); ?></h2>
                    <p class="mb-0 opacity-90">Survey: <?php echo htmlspecialchars($question['survey_title']); ?></p>
                </div>
                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                    <a href="manage_questions.php?survey_id=<?php echo $survey_id; ?>" class="btn btn-outline-light px-4 py-2 fw-medium rounded-pill">
                        <i class="bi bi-arrow-left me-1"></i> Back to Questions
                    </a>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <h4 class="mb-4">Current Options (<?php echo $total_options; ?>)</h4>
                
                <?php if ($total_options === 0): ?>
                    <div class="alert alert-info border-0 shadow-sm">No options added yet.</div>
                <?php else: ?>
                    <?php $counter = 1; while ($opt = $options->fetch_assoc()): ?>
                        <div class="option-card card shadow-sm border border-light <?php echo $is_expired ? 'opacity-75' : ''; ?>">
                            <div class="card-body p-3 d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-primary rounded-pill me-3 px-3 py-2">Option <?php echo $counter++; ?></span>
                                    <span class="fw-medium fs-5 text-dark"><?php echo htmlspecialchars($opt['option_text']); ?></span>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="<?php echo $is_expired ? '#' : 'edit_option.php?id='.$opt['option_id'].'&survey_id='.$survey_id.'&question_id='.$question_id; ?>" 
                                       class="btn btn-outline-primary btn-sm rounded-pill px-3 <?php echo $is_expired ? 'disabled' : ''; ?>">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" 
                                       class="btn btn-outline-danger btn-sm rounded-pill px-3 <?php echo $is_expired ? 'disabled' : ''; ?>"
                                       <?php if (!$is_expired): ?>
                                           data-bs-toggle="modal" 
                                           data-bs-target="#deleteOptionModal"
                                           data-option-id="<?php echo $opt['option_id']; ?>"
                                           data-survey-id="<?php echo $survey_id; ?>"
                                           data-question-id="<?php echo $question_id; ?>"
                                       <?php endif; ?>>
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
            
            <div class="col-lg-4">
                <?php if (!$is_expired): ?>
                    <div class="card shadow-sm border-0 rounded-4 position-sticky" style="top: 2rem;">
                        <div class="card-body p-4">
                            <h5 class="mb-4 d-flex align-items-center">
                                <i class="bi bi-plus-circle text-primary me-2"></i> Add New Option
                            </h5>
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label text-muted small fw-bold">Option Text</label>
                                    <input type="text" name="option_text" class="form-control form-control-lg bg-light border-0" required placeholder="e.g. Strongly Agree">
                                </div>
                                <button type="submit" name="add_option" class="btn btn-primary w-100 rounded-pill py-2 fw-medium shadow-sm">
                                    Add Option
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Delete Option Modal -->
    <div class="modal fade" id="deleteOptionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark">Delete Option</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pb-0">
                    <p class="text-dark mb-3">Are you sure you want to permanently delete this option?</p>
                    <div class="alert alert-danger d-flex align-items-center border-0 bg-danger bg-opacity-10 text-danger" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <div>This action cannot be undone.</div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-secondary px-4 rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="delete_option.php" id="deleteOptionForm" class="m-0 p-0">
                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                        <input type="hidden" name="id" id="deleteOptionId" value="">
                        <input type="hidden" name="question_id" id="deleteOptionQuestionId" value="">
                        <input type="hidden" name="survey_id" id="deleteOptionSurveyId" value="">
                        <button type="submit" class="btn btn-danger px-4 rounded-pill">Delete Option</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(alertEl => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alertEl);
                    bsAlert.close();
                }, 4000);
            });

            const deleteModal = document.getElementById('deleteOptionModal');
            if (deleteModal) {
                deleteModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const optionId = button.getAttribute('data-option-id');
                    const questionId = button.getAttribute('data-question-id');
                    const surveyId = button.getAttribute('data-survey-id');
                    const inputId = document.getElementById('deleteOptionId');
                    const inputQuestionId = document.getElementById('deleteOptionQuestionId');
                    const inputSurveyId = document.getElementById('deleteOptionSurveyId');
                    inputId.value = optionId;
                    inputQuestionId.value = questionId;
                    inputSurveyId.value = surveyId;
                });
            }
        });
    </script>
</body>
</html>
