<?php

/*
 * -------------------------------------------------------
 * Page    : Manage Surveys
 *
 * Purpose : Displays all surveys belonging to the logged-in company with response counts, status, and management actions.
 * -------------------------------------------------------
 */

// Initialize the session to track user authentication state.
session_start();
// Include the database connection configuration to interact with the database.
include("../config/db.php");
// Include the CSRF helper to generate and validate security tokens for forms.
include("../includes/csrf_helper.php");

// Verify that the company is logged in before granting access.
if (!isset($_SESSION['company_id'])) {
    // Redirect the user to the appropriate page after the action completes.
    header("Location: login.php");
    exit();
}

$company_id = $_SESSION['company_id'];

$query = "
SELECT 
    s.*, 
    COUNT(DISTINCT q.question_id) AS question_count,
    COUNT(DISTINCT r.response_id) AS response_count
FROM surveys s
LEFT JOIN questions q ON s.survey_id = q.survey_id
LEFT JOIN responses r ON s.survey_id = r.survey_id
WHERE s.company_id = '$company_id'
GROUP BY s.survey_id
ORDER BY s.created_at DESC
";

$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Surveys</title>
    
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
        
        .survey-card {
            border: none;
            border-radius: 18px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100%;
        }
        
        .survey-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px -5px rgb(0 0 0 / 0.1) !important;
        }
        
        .text-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;  
            overflow: hidden;
        }
        
        .status-badge {
            font-size: 0.8rem;
            padding: 5px 12px;
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
                        <i class="bi bi-clipboard-data me-2"></i>
                        SURVEY MANAGEMENT
                    </p>
                    <h1 class="display-5 fw-bold mb-3">Manage Surveys</h1>
                    <p class="lead opacity-90 mb-0">
                        Create, monitor, and optimize your customer feedback campaigns.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                    <a href="create_survey.php" class="btn btn-light px-5 py-3 fw-semibold shadow-sm">
                        <i class="bi bi-plus-circle me-2"></i>
                        Create New Survey
                    </a>
                </div>
            </div>
        </div>

        <?php if (isset($_SESSION['success_msg'])): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert" id="successAlert">
                <strong class="d-flex align-items-center">
                    <?php echo $_SESSION['success_msg']; ?>
                </strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success_msg']); ?>
        <?php endif; ?>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <!-- STATS -->
            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="card shadow-sm text-center p-4">
                        <div class="text-primary mb-2">
                            <i class="bi bi-clipboard fs-2"></i>
                        </div>
                        <h3 class="fw-bold"><?php echo mysqli_num_rows($result); ?></h3>
                        <p class="text-muted mb-0">Total Surveys</p>
                    </div>
                </div>
            </div>

            <!-- SEARCH -->
            <div class="mb-4 d-flex flex-column flex-md-row gap-3 align-items-md-center justify-content-between">
                <h4 class="section-title mb-0">Your Surveys</h4>
                <input type="text" id="surveySearch" 
                       class="form-control flex-grow-1" 
                       style="max-width: 400px;"
                       placeholder="Search surveys..." 
                       onkeyup="filterSurveys()">
            </div>

            <?php 
            $draft_surveys = [];
            $active_surveys = [];
            $completed_surveys = [];
            mysqli_data_seek($result, 0);
            // Iterate through each row from the MySQLi result set.
            while($row = mysqli_fetch_assoc($result)) {
                if ($row['status'] == 'draft') {
                    $draft_surveys[] = $row;
                } else if (strtotime($row['expiry_date']) < time()) {
                    $completed_surveys[] = $row;
                } else {
                    $active_surveys[] = $row;
                }
            }
            ?>

            <!-- DRAFT SURVEYS -->
            <?php if(!empty($draft_surveys)): ?>
            <h4 class="section-title mb-4 mt-2 d-flex align-items-center">
                Draft Surveys 
                <span class="badge bg-warning bg-opacity-10 text-warning ms-3 fs-6 rounded-pill border border-warning-subtle"><?php echo count($draft_surveys); ?></span>
            </h4>
            <div class="row g-4 mb-5" id="draftSurveysGrid">
                <?php foreach($draft_surveys as $row): ?>
                    <div class="col-lg-4 col-md-6 survey-item">
                        <div class="survey-card card shadow-sm border border-light h-100">
                            <div class="card-body d-flex flex-column p-4 p-xl-4">
                                <!-- Header -->
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="text-muted small fw-medium">#<?php echo $row['survey_id']; ?></div>
                                    <span class="status-badge badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill px-3 py-2">Draft</span>
                                </div>
                                
                                <!-- Title -->
                                <h5 class="card-title fw-bold text-dark mb-2"><?php echo htmlspecialchars($row['title']); ?></h5>
                                
                                <!-- Description -->
                                <p class="text-muted flex-grow-1 small text-clamp-2 mb-4" title="<?php echo htmlspecialchars($row['description']); ?>">
                                    <?php echo htmlspecialchars($row['description']); ?>
                                </p>
                                
                                <!-- Meta (Reward & Expiry) -->
                                <div class="row text-center g-1 mb-4 p-2 bg-light rounded-3 align-items-center">
                                    <div class="col-6 border-end">
                                        <small class="text-muted d-block text-truncate" style="font-size: 0.7rem;">⭐ Reward</small>
                                        <strong class="text-dark" style="font-size: 0.9rem;"><?php echo $row['points_reward']; ?></strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block text-truncate" style="font-size: 0.7rem;">📅 Exp</small>
                                        <strong class="text-dark" style="font-size: 0.85rem;"><?php echo date('M d, Y', strtotime($row['expiry_date'])); ?></strong>
                                    </div>
                                </div>
                                
                                <!-- Progress Section -->
                                <?php 
                                    $q_count = $row['question_count'] ?? 0;
                                    $percent = ($q_count >= 3) ? 100 : round(($q_count / 3) * 100);
                                ?>
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-end mb-2">
                                        <span class="text-dark fw-bold small">Survey Progress</span>
                                        <span class="text-muted" style="font-size: 0.75rem;"><?php echo $q_count; ?> of 3 required questions completed</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo $percent; ?>%" aria-valuenow="<?php echo $percent; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                                
                                <!-- Actions -->
                                <div class="mt-auto d-flex flex-wrap gap-2">
                                    <a href="edit_survey.php?id=<?php echo $row['survey_id']; ?>" 
                                       class="btn btn-outline-primary btn-sm flex-fill fw-medium">
                                        <i class="bi bi-pencil me-1"></i> Edit Survey
                                    </a>
                                    <a href="manage_questions.php?survey_id=<?php echo $row['survey_id']; ?>" 
                                       class="btn btn-warning btn-sm flex-fill fw-medium text-dark">
                                        Continue Building Survey
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- ACTIVE SURVEYS -->
            <h4 class="section-title mb-4 mt-2 d-flex align-items-center">
                Active Surveys 
                <span class="badge bg-primary bg-opacity-10 text-primary ms-3 fs-6 rounded-pill border border-primary-subtle"><?php echo count($active_surveys); ?></span>
            </h4>
            <div class="row g-4 mb-5" id="activeSurveysGrid">
                <?php if(empty($active_surveys)): ?>
                    <div class="col-12"><p class="text-muted">No active surveys found.</p></div>
                <?php else: ?>
                    <?php foreach($active_surveys as $row): ?>
                        <div class="col-lg-4 col-md-6 survey-item">
                            <div class="survey-card card shadow-sm border border-light h-100">
                                <div class="card-body d-flex flex-column p-4 p-xl-4">
                                    <!-- Header -->
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div class="text-muted small fw-medium">#<?php echo $row['survey_id']; ?></div>
                                        <span class="status-badge badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill px-3 py-2">Active</span>
                                    </div>
                                    
                                    <!-- Title -->
                                    <h5 class="card-title fw-bold text-dark mb-2"><?php echo htmlspecialchars($row['title']); ?></h5>
                                    
                                    <!-- Description -->
                                    <p class="text-muted flex-grow-1 small text-clamp-2 mb-4" title="<?php echo htmlspecialchars($row['description']); ?>">
                                        <?php echo htmlspecialchars($row['description']); ?>
                                    </p>
                                    
                                    <!-- Meta -->
                                    <div class="row text-center g-1 mb-4 p-2 bg-light rounded-3 align-items-center">
                                        <div class="col-6 col-md-3 border-end">
                                            <small class="text-muted d-block text-truncate" style="font-size: 0.7rem;">⭐ Reward</small>
                                            <strong class="text-dark" style="font-size: 0.9rem;"><?php echo $row['points_reward']; ?></strong>
                                        </div>
                                        <div class="col-6 col-md-3 border-md-end">
                                            <small class="text-muted d-block text-truncate" style="font-size: 0.7rem;">📋 Qs</small>
                                            <strong class="text-dark" style="font-size: 0.9rem;"><?php echo $row['question_count']; ?></strong>
                                        </div>
                                        <div class="col-6 col-md-3 border-end">
                                            <small class="text-muted d-block text-truncate" style="font-size: 0.7rem;">👥 Res</small>
                                            <strong class="text-dark" style="font-size: 0.9rem;"><?php echo $row['response_count'] ?? 0; ?></strong>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <small class="text-muted d-block text-truncate" style="font-size: 0.7rem;">📅 Exp</small>
                                            <strong class="text-dark" style="font-size: 0.85rem;"><?php echo date('M d, Y', strtotime($row['expiry_date'])); ?></strong>
                                        </div>
                                    </div>
                                    
                                    <!-- Actions -->
                                    <div class="mt-auto d-flex flex-wrap gap-2">
                                        <a href="edit_survey.php?id=<?php echo $row['survey_id']; ?>" 
                                           class="btn btn-outline-primary btn-sm flex-fill fw-medium">
                                            <i class="bi bi-pencil me-1"></i> Edit
                                        </a>
                                        <a href="manage_questions.php?survey_id=<?php echo $row['survey_id']; ?>" 
                                           class="btn btn-outline-success btn-sm flex-fill fw-medium">
                                            <i class="bi bi-list-check me-1"></i> Questions
                                        </a>
                                        <button type="button" 
                                           class="btn btn-outline-danger btn-sm flex-fill fw-medium"
                                           data-bs-toggle="modal" 
                                           data-bs-target="#deleteSurveyModal"
                                           data-survey-id="<?php echo $row['survey_id']; ?>">
                                            <i class="bi bi-trash me-1"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- COMPLETED SURVEYS -->
            <h4 class="section-title mb-4 d-flex align-items-center">
                Completed Surveys 
                <span class="badge bg-secondary bg-opacity-10 text-secondary ms-3 fs-6 rounded-pill border border-secondary-subtle"><?php echo count($completed_surveys); ?></span>
            </h4>
            <div class="row g-4 mb-2" id="completedSurveysGrid">
                <?php if(empty($completed_surveys)): ?>
                    <div class="col-12"><p class="text-muted">No completed surveys found.</p></div>
                <?php else: ?>
                    <?php foreach($completed_surveys as $row): ?>
                        <div class="col-lg-4 col-md-6 survey-item">
                            <div class="survey-card card shadow-sm border border-light h-100">
                                <div class="card-body d-flex flex-column p-4 p-xl-4 opacity-75">
                                    <!-- Header -->
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div class="text-muted small fw-medium">#<?php echo $row['survey_id']; ?></div>
                                        <span class="status-badge badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill px-3 py-2">Completed</span>
                                    </div>
                                    
                                    <!-- Title -->
                                    <h5 class="card-title fw-bold text-dark mb-2"><?php echo htmlspecialchars($row['title']); ?></h5>
                                    
                                    <!-- Description -->
                                    <p class="text-muted flex-grow-1 small text-clamp-2 mb-4" title="<?php echo htmlspecialchars($row['description']); ?>">
                                        <?php echo htmlspecialchars($row['description']); ?>
                                    </p>
                                    
                                    <!-- Meta -->
                                    <div class="row text-center g-1 mb-4 p-2 bg-light rounded-3 align-items-center opacity-75">
                                        <div class="col-6 col-md-3 border-end">
                                            <small class="text-muted d-block text-truncate" style="font-size: 0.7rem;">⭐ Reward</small>
                                            <strong class="text-dark" style="font-size: 0.9rem;"><?php echo $row['points_reward']; ?></strong>
                                        </div>
                                        <div class="col-6 col-md-3 border-md-end">
                                            <small class="text-muted d-block text-truncate" style="font-size: 0.7rem;">📋 Qs</small>
                                            <strong class="text-dark" style="font-size: 0.9rem;"><?php echo $row['question_count']; ?></strong>
                                        </div>
                                        <div class="col-6 col-md-3 border-end">
                                            <small class="text-muted d-block text-truncate" style="font-size: 0.7rem;">👥 Res</small>
                                            <strong class="text-dark" style="font-size: 0.9rem;"><?php echo $row['response_count'] ?? 0; ?></strong>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <small class="text-muted d-block text-truncate" style="font-size: 0.7rem;">📅 Exp</small>
                                            <strong class="text-dark" style="font-size: 0.85rem;"><?php echo date('M d, Y', strtotime($row['expiry_date'])); ?></strong>
                                        </div>
                                    </div>
                                    
                                    <!-- Actions -->
                                    <div class="mt-auto d-flex flex-wrap gap-2">
                                        <button type="button" 
                                           class="btn btn-outline-danger btn-sm flex-fill fw-medium"
                                           data-bs-toggle="modal" 
                                           data-bs-target="#deleteSurveyModal"
                                           data-survey-id="<?php echo $row['survey_id']; ?>">
                                            <i class="bi bi-trash me-1"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- EMPTY STATE -->
            <div class="empty-state">
                <div class="mb-4">
                    <i class="bi bi-clipboard display-1 text-muted"></i>
                </div>
                <h4 class="text-dark">No surveys created yet</h4>
                <p class="text-muted mb-4">Start collecting valuable feedback by creating your first survey.</p>
                <a href="create_survey.php" class="btn btn-primary px-5 py-3">
                    <i class="bi bi-plus-circle me-2"></i>
                    Create Your First Survey
                </a>
            </div>
        <?php endif; ?>

    </div>

    <!-- Delete Survey Modal -->
    <div class="modal fade" id="deleteSurveyModal" tabindex="-1" aria-labelledby="deleteSurveyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark" id="deleteSurveyModalLabel">Delete Survey</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pb-0">
                    <p class="text-dark mb-3">Are you sure you want to permanently delete this survey?</p>
                    <p class="text-muted mb-2">This action will delete:</p>
                    <ul class="text-muted mb-4">
                        <li>Survey</li>
                        <li>Questions</li>
                        <li>Options</li>
                        <li>Responses</li>
                        <li>Answers</li>
                    </ul>
                    <div class="alert alert-danger d-flex align-items-center border-0 bg-danger bg-opacity-10 text-danger" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <div>This action cannot be undone.</div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 flex-wrap gap-2">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="delete_survey.php" id="deleteForm" class="m-0 p-0">
                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                        <input type="hidden" name="id" id="deleteSurveyId" value="">
                        <button type="submit" class="btn btn-danger px-4">Delete Survey</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const deleteSurveyModal = document.getElementById('deleteSurveyModal');
            if (deleteSurveyModal) {
                deleteSurveyModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const surveyId = button.getAttribute('data-survey-id');
                    const inputId = document.getElementById('deleteSurveyId');
                    inputId.value = surveyId;
                });
            }
        });

        // Frontend search/filter
        function filterSurveys() {
            const searchTerm = document.getElementById('surveySearch').value.toLowerCase().trim();
            const cards = document.querySelectorAll('.survey-item');
            
            cards.forEach(card => {
                const title = card.querySelector('.card-title').textContent.toLowerCase();
                if (title.includes(searchTerm)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        }
        
        // Card entrance animation
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.survey-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1)';
                    card.style.transitionDelay = (index * 70) + 'ms';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 100);
            });
            
            // Auto-hide alert after 4 seconds
            const successAlert = document.getElementById('successAlert');
            if (successAlert) {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(successAlert);
                    bsAlert.close();
                }, 4000);
            }
        });
    </script>
    <?php include('../includes/footer.php'); ?>
</body>
</html>