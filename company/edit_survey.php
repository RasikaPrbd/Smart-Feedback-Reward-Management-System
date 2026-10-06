<?php

/*
 * -------------------------------------------------------
 * Page    : Edit Survey
 *
 * Purpose : Allows a company to update the details of an existing survey that belongs to them.
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

if (!isset($_GET['id'])) {
    // Redirect the user to the appropriate page after the action completes.
    header("Location: manage_surveys.php");
    exit();
}

$survey_id = $_GET['id'];

// Prepare the SQL statement to securely handle user input and prevent SQL injection.
$stmt = $conn->prepare("SELECT * FROM surveys WHERE survey_id = ? AND company_id = ?");
// Bind parameters to the prepared statement.
$stmt->bind_param("ii", $survey_id, $company_id);
// Execute the prepared statement against the database.
$stmt->execute();
// Retrieve the result set from the executed prepared statement.
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $stmt->close();
    die("Survey not found.");
}

$survey = $result->fetch_assoc();
$stmt->close();

// Only process the form when the request was sent via POST to prevent accidental GET submissions.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        csrf_abort();
    }

    $title = $_POST['title'];
    $description = $_POST['description'];
    $expiry_date = $_POST['expiry_date'];

    $update_stmt = $conn->prepare("UPDATE surveys SET title = ?, description = ?, expiry_date = ? WHERE survey_id = ?");
    $update_stmt->bind_param("sssi", $title, $description, $expiry_date, $survey_id);

    if ($update_stmt->execute()) {
        $update_stmt->close();
        
        $_SESSION['success_msg'] = "✓ Survey updated successfully.";

        // Redirect the user to the appropriate page after the action completes.
        header("Location: manage_surveys.php");
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Survey</title>
    
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
        
        .form-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }
        
        .preview-card {
            border: 2px dashed #e2e8f0;
            border-radius: 18px;
            transition: all 0.3s ease;
        }
        
        .form-control, .form-select {
            border-radius: 12px;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(99, 102, 241, 0.15);
        }
        
        .submit-btn {
            padding: 14px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 12px;
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
                        <i class="bi bi-pencil-square me-2"></i>
                        SURVEY MANAGEMENT
                    </p>
                    <h1 class="display-5 fw-bold mb-3">Edit Survey</h1>
                    <p class="lead opacity-90 mb-0">
                        Update survey details to improve participation and engagement.
                    </p>
                </div>
            </div>
        </div>

        <div class="row g-4">
            
            <!-- MAIN EDIT FORM -->
            <div class="col-lg-7">
                <div class="form-card card">
                    <div class="card-body p-5">
                        <h4 class="mb-4">Update Survey Details</h4>
                        
                        <form method="POST" id="surveyForm">
                            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Survey Title</label>
                                <input type="text" 
                                       name="title" 
                                       id="survey_title"
                                       class="form-control form-control-lg" 
                                       // Escape output to prevent Cross-Site Scripting (XSS) attacks.
                                       value="<?php echo htmlspecialchars($survey['title']); ?>"
                                       onkeyup="updatePreview()"
                                       required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" 
                                          id="survey_description"
                                          class="form-control" 
                                          rows="5"
                                          onkeyup="updatePreview()"
                                          // Escape output to prevent Cross-Site Scripting (XSS) attacks.
                                          required><?php echo htmlspecialchars($survey['description']); ?></textarea>
                            </div>

                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Reward Points</label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-light text-muted">⭐</span>
                                        <input type="number" 
                                               id="points_reward"
                                               class="form-control bg-light text-muted" 
                                               value="<?php echo $survey['points_reward']; ?>"
                                               readonly>
                                    </div>
                                    <div class="form-text mt-2 text-muted small">
                                        Reward points cannot be changed after the survey is created.
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Expiry Date</label>
                                    <input type="date" 
                                           name="expiry_date" 
                                           class="form-control form-control-lg"
                                           value="<?php echo $survey['expiry_date']; ?>"
                                           required>
                                </div>
                            </div>

                            <hr class="my-5">
                            
                            <div class="d-flex flex-column flex-sm-row gap-3">
                                <button type="submit" 
                                        name="update"
                                        class="btn btn-primary submit-btn flex-fill">
                                    <i class="bi bi-save me-2"></i>
                                    Update Survey
                                </button>
                                <a href="manage_surveys.php" 
                                   class="btn btn-outline-secondary submit-btn">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- LIVE PREVIEW + TIPS -->
            <div class="col-lg-5">
                <!-- Live Preview -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-4">
                        <h5 class="mb-0">Live Preview</h5>
                    </div>
                    <div class="card-body p-4">
                        <div id="surveyPreview" class="preview-card p-4">
                            <div class="mb-3">
                                <span class="badge bg-primary">Active Survey</span>
                            </div>
                            <h5 id="previewTitle" class="fw-semibold mb-3"><?php echo htmlspecialchars($survey['title']); ?></h5>
                            <p id="previewDesc" class="text-muted small mb-4">
                                <?php echo htmlspecialchars(substr($survey['description'], 0, 120)); ?>...
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted">Reward</small><br>
                                    <strong id="previewPoints" class="text-primary"><?php echo $survey['points_reward']; ?> pts</strong>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted">Expires</small><br>
                                    <strong><?php echo $survey['expiry_date']; ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tips -->
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="mb-4">
                            <i class="bi bi-lightbulb text-warning"></i> Editing Tips
                        </h5>
                        <ul class="list-unstyled small">
                            <li class="d-flex gap-3 mb-3">
                                <span class="text-success">✓</span>
                                <div>Clear titles attract more participants</div>
                            </li>
                            <li class="d-flex gap-3 mb-3">
                                <span class="text-success">✓</span>
                                <div>Update description to reflect current goals</div>
                            </li>
                            <li class="d-flex gap-3 mb-3">
                                <span class="text-success">✓</span>
                                <div>Adjust points based on survey length</div>
                            </li>
                            <li class="d-flex gap-3">
                                <span class="text-success">✓</span>
                                <div>Keep expiry dates realistic</div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function updatePreview() {
            const title = document.getElementById('survey_title').value.trim();
            const desc = document.getElementById('survey_description').value.trim();
            const points = document.getElementById('points_reward').value.trim();
            
            document.getElementById('previewTitle').textContent = title || "Survey Title";
            
            const descEl = document.getElementById('previewDesc');
            descEl.textContent = desc ? desc.substring(0, 120) + (desc.length > 120 ? '...' : '') : "Survey description will appear here...";
            
            if (points) {
                document.getElementById('previewPoints').textContent = parseInt(points) + " pts";
            }
        }
        
        // Initialize
        window.onload = function() {
            updatePreview();
        };
        
        // Form feedback
        document.getElementById('surveyForm').addEventListener('submit', function() {
            const btn = this.querySelector('button[name="update"]');
            if (btn) {
                btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Updating Survey...`;
                btn.disabled = true;
            }
        });
    </script>
</body>
</html>