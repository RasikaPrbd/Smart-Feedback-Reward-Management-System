<?php
/*
 * -------------------------------------------------------
 * Page    : Manage Questions
 *
 * Purpose : Displays all questions for a specific survey and allows the company to add, edit, or delete them.
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

if (!isset($_GET['survey_id'])) {
    // Redirect the user to the appropriate page after the action completes.
    header("Location: manage_surveys.php");
    exit();
}

$survey_id = (int)$_GET['survey_id'];
$company_id = $_SESSION['company_id'];

// 1. Fetch survey and verify ownership
$survey_stmt = $conn->prepare("SELECT * FROM surveys WHERE survey_id = ? AND company_id = ?");
$survey_stmt->bind_param("ii", $survey_id, $company_id);
$survey_stmt->execute();
$survey_result = $survey_stmt->get_result();

if ($survey_result->num_rows === 0) {
    $survey_stmt->close();
    die("Survey not found or access denied.");
}
$survey = $survey_result->fetch_assoc();
$survey_stmt->close();

$is_expired = strtotime($survey['expiry_date']) < time();

// 2. Fetch questions with option counts
$q_stmt = $conn->prepare("
    SELECT q.*, COUNT(o.option_id) as option_count 
    FROM questions q 
    LEFT JOIN options o ON q.question_id = o.question_id 
    WHERE q.survey_id = ? 
    GROUP BY q.question_id
    ORDER BY q.question_id ASC
");
$q_stmt->bind_param("i", $survey_id);
$q_stmt->execute();
$questions = $q_stmt->get_result();
$total_questions = $questions->num_rows;
$q_stmt->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Questions</title>
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
        .question-card {
            border: none;
            border-radius: 18px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100%;
        }
        .question-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px -5px rgb(0 0 0 / 0.1) !important;
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
                    <p class="mb-0">This survey has expired. Modifying questions and options is disabled to preserve data integrity.</p>
                </div>
            </div>
        <?php endif; ?>

        <!-- HERO SECTION -->
        <div class="hero-section position-relative">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <p class="mb-2 opacity-90 fs-5">
                        <i class="bi bi-list-check me-2"></i>
                        QUESTION MANAGEMENT
                    </p>
                    <h1 class="display-5 fw-bold mb-3"><?php echo htmlspecialchars($survey['title']); ?></h1>
                    
                    <div class="d-flex flex-wrap gap-3 opacity-90 mt-3">
                        <span class="badge bg-white bg-opacity-25 fs-6 px-3 py-2 rounded-pill">
                            <i class="bi bi-gift-fill me-1"></i> <?php echo $survey['points_reward']; ?> pts
                        </span>
                        <span class="badge bg-white bg-opacity-25 fs-6 px-3 py-2 rounded-pill">
                            <i class="bi bi-calendar2-event me-1"></i> Expires: <?php echo date('M d, Y', strtotime($survey['expiry_date'])); ?>
                        </span>
                        <span class="badge <?php echo $is_expired ? 'bg-danger' : 'bg-success'; ?> bg-opacity-75 fs-6 px-3 py-2 rounded-pill">
                            <?php echo $is_expired ? 'Expired' : 'Active'; ?>
                        </span>
                        <span class="badge bg-white bg-opacity-25 fs-6 px-3 py-2 rounded-pill">
                            <i class="bi bi-question-circle-fill me-1"></i> <?php echo $total_questions; ?> Questions
                        </span>
                    </div>
                </div>
                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                    <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                    <a href="manage_surveys.php" class="btn btn-outline-light px-4 py-2 fw-medium rounded-pill">
                        <i class="bi bi-arrow-left me-1"></i> Back to Surveys
                    </a>
                    <?php if (!$is_expired): ?>
                    <a href="add_question.php?survey_id=<?php echo $survey_id; ?>" class="btn btn-light px-4 py-2 fw-bold text-primary rounded-pill shadow-sm">
                        <i class="bi bi-plus-circle me-1"></i> Add Question
                    </a>
                    <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-4 d-flex align-items-center">
            <h4 class="section-title mb-0">Survey Questions</h4>
            <span class="badge bg-primary ms-3 fs-6 rounded-pill shadow-sm"><?php echo $total_questions; ?></span>
        </div>

        <div class="row g-4">
            <?php if ($total_questions === 0): ?>
                <div class="col-12 text-center py-5">
                    <i class="bi bi-question-square display-1 text-muted opacity-50 mb-3 d-block"></i>
                    <h4 class="text-dark">No questions found</h4>
                    <p class="text-muted mb-4">This survey doesn't have any questions yet.</p>
                    <?php if (!$is_expired): ?>
                        <a href="add_question.php?survey_id=<?php echo $survey_id; ?>" class="btn btn-primary px-4 py-2">Add First Question</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php 
                $counter = 1;
                while ($q = $questions->fetch_assoc()): 
                    $type_badge = '';
                    $type_label = '';
                    switch ($q['question_type']) {
                        case 'mcq':
                            $type_badge = 'bg-info bg-opacity-10 text-info border-info-subtle';
                            $type_label = 'Multiple Choice';
                            break;
                        case 'rating':
                            $type_badge = 'bg-warning bg-opacity-10 text-warning border-warning-subtle';
                            $type_label = 'Rating (1-5)';
                            break;
                        case 'text':
                            $type_badge = 'bg-secondary bg-opacity-10 text-secondary border-secondary-subtle';
                            $type_label = 'Text Response';
                            break;
                    }
                ?>
                <div class="col-12">
                    <div class="question-card card shadow-sm border border-light rounded-4 <?php echo $is_expired ? 'opacity-75' : ''; ?>">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge bg-primary rounded-pill px-3 py-2 shadow-sm fs-6">#<?php echo $counter; ?></span>
                                <span class="badge border rounded-pill px-3 py-2 <?php echo $type_badge; ?>"><?php echo $type_label; ?></span>
                            </div>
                            
                            <h5 class="fw-bold text-dark mb-4 lh-base fs-4"><?php echo htmlspecialchars($q['question_text']); ?></h5>
                            
                            <?php if ($q['question_type'] === 'mcq'): ?>
                                <div class="mb-4">
                                    <span class="badge bg-light text-dark border px-3 py-2">
                                        <i class="bi bi-list-ul me-1"></i> <?php echo $q['option_count']; ?> Options
                                    </span>
                                </div>
                            <?php endif; ?>
                            
                            <hr class="text-muted opacity-25">
                            
                            <div class="d-flex flex-wrap gap-2 justify-content-end">
                                <?php if ($q['question_type'] === 'mcq'): ?>
                                    <a href="<?php echo $is_expired ? '#' : 'manage_options.php?question_id='.$q['question_id'].'&survey_id='.$survey_id; ?>" 
                                       class="btn btn-outline-info btn-sm fw-medium px-4 rounded-pill <?php echo $is_expired ? 'disabled' : ''; ?>">
                                        <i class="bi bi-list-task me-1"></i> Manage Options
                                    </a>
                                <?php endif; ?>
                                
                                <a href="<?php echo $is_expired ? '#' : 'edit_question.php?id='.$q['question_id'].'&survey_id='.$survey_id; ?>" 
                                   class="btn btn-outline-primary btn-sm fw-medium px-4 rounded-pill <?php echo $is_expired ? 'disabled' : ''; ?>">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </a>
                                
                                <button type="button" 
                                   class="btn btn-outline-danger btn-sm fw-medium px-4 rounded-pill <?php echo $is_expired ? 'disabled' : ''; ?>"
                                   <?php if (!$is_expired): ?>
                                       data-bs-toggle="modal" 
                                       data-bs-target="#deleteQuestionModal"
                                       data-question-id="<?php echo $q['question_id']; ?>"
                                       data-survey-id="<?php echo $survey_id; ?>"
                                   <?php endif; ?>>
                                    <i class="bi bi-trash me-1"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php 
                $counter++;
                endwhile; 
                ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Delete Question Modal -->
    <div class="modal fade" id="deleteQuestionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark">Delete Question</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pb-0">
                    <p class="text-dark mb-3">Are you sure you want to permanently delete this question?</p>
                    <p class="text-muted mb-4">Any existing options or responses related to this question will also be deleted.</p>
                    <div class="alert alert-danger d-flex align-items-center border-0 bg-danger bg-opacity-10 text-danger" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <div>This action cannot be undone.</div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 flex-wrap gap-2">
                    <button type="button" class="btn btn-secondary px-4 rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="delete_question.php" id="deleteQuestionForm" class="m-0 p-0">
                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                        <input type="hidden" name="id" id="deleteQuestionId" value="">
                        <input type="hidden" name="survey_id" id="deleteQuestionSurveyId" value="">
                        <button type="submit" class="btn btn-danger px-4 rounded-pill">Delete Question</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Auto-hide alert after 4 seconds
            const alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(alertEl => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alertEl);
                    bsAlert.close();
                }, 4000);
            });

            // Delete Modal
            const deleteModal = document.getElementById('deleteQuestionModal');
            if (deleteModal) {
                deleteModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const questionId = button.getAttribute('data-question-id');
                    const surveyId = button.getAttribute('data-survey-id');
                    const inputId = document.getElementById('deleteQuestionId');
                    const inputSurveyId = document.getElementById('deleteQuestionSurveyId');
                    inputId.value = questionId;
                    inputSurveyId.value = surveyId;
                });
            }
            
            // Subtle animation
            const cards = document.querySelectorAll('.question-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1)';
                    card.style.transitionDelay = (index * 50) + 'ms';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 100);
            });
        });
    </script>
    <?php include('../includes/footer.php'); ?>
</body>
</html>
