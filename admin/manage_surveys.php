<?php

/*
 * -------------------------------------------------------
 * Page    : Manage Surveys
 *
 * Purpose : Allows the administrator to view all surveys created by companies and delete them if required.
 * -------------------------------------------------------
 */

// Initialize the session to track user authentication state.
session_start();
// Include the database connection configuration to interact with the database.
include("../config/db.php");
// Include the CSRF helper to generate and validate security tokens for forms.
include("../includes/csrf_helper.php");

// Verify that the admin is logged in before granting access.
if (!isset($_SESSION['admin_id'])) {
    // Redirect the user to the appropriate page after the action completes.
    header("Location: login.php");
    exit();
}

$query = "
SELECT
    surveys.survey_id,
    surveys.title,
    surveys.expiry_date,
    surveys.status,
    companies.company_name
FROM surveys
JOIN companies
ON surveys.company_id = companies.company_id
ORDER BY surveys.survey_id DESC
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
            background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
        }
        
        .dashboard-container {
            padding-top: 2rem;
            padding-bottom: 5rem;
        }
        
        .hero-section {
            background: linear-gradient(135deg, #1e2937 0%, #334155 100%);
            color: white;
            border-radius: 20px;
            padding: 3rem 2.5rem;
            margin-bottom: 3rem;
            box-shadow: 0 15px 35px rgba(30, 41, 55, 0.3);
        }
        
        .table th {
            font-weight: 500;
            color: #64748b;
        }
        
        .status-active {
            background-color: #10b981;
            color: white;
        }
        
        .status-expired {
            background-color: #64748b;
            color: white;
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
    <?php include("../includes/admin_navbar.php"); ?>

    <div class="container dashboard-container">
        
        <!-- HERO SECTION -->
        <div class="hero-section">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <p class="mb-2 opacity-90 fs-5">
                        <i class="bi bi-clipboard-data me-2"></i>
                        PLATFORM ADMINISTRATION
                    </p>
                    <h1 class="display-5 fw-bold mb-3">Manage Surveys</h1>
                    <p class="lead opacity-90 mb-0">
                        Oversee all surveys created by companies on the platform.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                    <div class="bg-white bg-opacity-10 rounded-4 px-5 py-4 d-inline-block text-center">
                        <div class="fs-2 fw-bold"><?php echo mysqli_num_rows($result); ?></div>
                        <small class="opacity-75">Total Surveys</small>
                    </div>
                </div>
            </div>
        </div>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <!-- SEARCH -->
            <div class="mb-4">
                <div class="input-group">
                    <span class="input-group-text bg-white">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" 
                           id="searchInput" 
                           class="form-control form-control-lg border-0 shadow-sm" 
                           placeholder="Search by survey title or company...">
                </div>
            </div>

            <!-- SURVEYS TABLE -->
            <div class="card shadow">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="surveysTable">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Survey Title</th>
                                    <th>Company</th>
                                    <th>Responses</th>
                                    <th>Expiry Date</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                mysqli_data_seek($result, 0);
                                // Iterate through each row from the MySQLi result set.
                                while($row = mysqli_fetch_assoc($result)): 
                                    $survey_id = $row['survey_id'];
                                    $count_sql = "SELECT COUNT(*) AS total FROM responses WHERE survey_id='$survey_id'";
                                    $count_result = mysqli_query($conn, $count_sql);
                                    $count_row = mysqli_fetch_assoc($count_result);
                                    $isExpired = strtotime($row['expiry_date']) < time();
                                ?>
                                    <tr class="survey-row">
                                        <td class="text-muted">#<?php echo $row['survey_id']; ?></td>
                                        <td class="fw-semibold"><?php echo htmlspecialchars($row['title']); ?></td>
                                        <td><?php echo htmlspecialchars($row['company_name']); ?></td>
                                        <td class="text-center fw-medium"><?php echo $count_row['total']; ?></td>
                                        <td><?php echo $row['expiry_date']; ?></td>
                                        <td>
                                            <?php if($row['status'] == 'draft'): ?>
                                                <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 border border-warning-subtle rounded-pill">Draft</span>
                                            <?php elseif($isExpired): ?>
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 border border-secondary-subtle rounded-pill">Completed</span>
                                            <?php else: ?>
                                                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 border border-success-subtle rounded-pill">Active</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-outline-danger btn-sm"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deleteSurveyModal"
                                                    data-survey-id="<?php echo $row['survey_id']; ?>"
                                                    // Escape output to prevent Cross-Site Scripting (XSS) attacks.
                                                    data-survey-title="<?php echo htmlspecialchars($row['title'], ENT_QUOTES); ?>">
                                                <i class="bi bi-trash me-1"></i> Delete
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- EMPTY STATE -->
            <div class="empty-state">
                <div class="mb-4">
                    <i class="bi bi-clipboard display-1 text-muted"></i>
                </div>
                <h4 class="text-dark">No surveys found</h4>
                <p class="text-muted">Surveys created by companies will appear here.</p>
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
                <div class="modal-body">
                    <p class="mb-3">Are you sure you want to delete this survey?</p>
                    <div class="bg-light p-3 rounded-3 mb-3 fw-semibold text-dark">
                        <span id="modalSurveyTitle"></span>
                    </div>
                    <div class="alert alert-danger mb-0 border-0">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> This action cannot be undone. All related survey questions, options, and responses may also be removed.
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="delete_survey.php" id="deleteSurveyForm" class="m-0 p-0">
                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                        <input type="hidden" name="id" id="modalSurveyId" value="">
                        <button type="submit" class="btn btn-danger">Delete Survey</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Enhanced search functionality
        document.getElementById("searchInput").addEventListener("keyup", function() {
            let value = this.value.toLowerCase();
            let rows = document.querySelectorAll("#surveysTable tbody tr");
            
            rows.forEach(function(row) {
                let rowText = row.textContent.toLowerCase();
                row.style.display = rowText.includes(value) ? "" : "none";
            });
        });
        
        // Row entrance animation
        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('.survey-row');
            rows.forEach((row, index) => {
                row.style.opacity = '0';
                row.style.transform = 'translateY(15px)';
                setTimeout(() => {
                    row.style.transition = 'all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1)';
                    row.style.transitionDelay = (index * 40) + 'ms';
                    row.style.opacity = '1';
                    row.style.transform = 'translateY(0)';
                }, 80);
            });
        });

        // Populate modal data
        const deleteModal = document.getElementById('deleteSurveyModal');
        if (deleteModal) {
            deleteModal.addEventListener('show.bs.modal', event => {
                const button = event.relatedTarget;
                const surveyId = button.getAttribute('data-survey-id');
                const surveyTitle = button.getAttribute('data-survey-title');

                deleteModal.querySelector('#modalSurveyId').value = surveyId;
                deleteModal.querySelector('#modalSurveyTitle').textContent = surveyTitle;
            });
        }
    </script>
    <?php include('../includes/footer.php'); ?>
</body>
</html>