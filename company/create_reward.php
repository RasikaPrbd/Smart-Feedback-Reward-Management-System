<?php

/*
 * -------------------------------------------------------
 * Page    : Create Reward
 *
 * Purpose : Allows a company to add a new redeemable reward with a name and required point threshold.
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

$message = "";

// Only process the form when the request was sent via POST to prevent accidental GET submissions.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        csrf_abort();
    }

    $company_id = $_SESSION['company_id'];

    $reward_name = trim($_POST['reward_name']);
    $points_required = $_POST['points_required'];

    if (empty($reward_name)) {
        $message = "Error: Reward name cannot be empty.";
    } elseif (!is_numeric($points_required) || $points_required <= 0) {
        $message = "Error: Points required must be a positive integer greater than zero.";
    } else {
        $points_required = (int)$points_required;
        // Prepare the SQL statement to securely handle user input and prevent SQL injection.
        $stmt = $conn->prepare("INSERT INTO rewards(company_id, reward_name, points_required) VALUES(?, ?, ?)");
        // Bind parameters to the prepared statement.
        $stmt->bind_param("isi", $company_id, $reward_name, $points_required);

        if ($stmt->execute()) {
            $_SESSION['success_msg'] = "✓ Reward created successfully.";
            // Redirect the user to the appropriate page after the action completes.
            header("Location: manage_rewards.php");
            exit();
        } else {
            $message = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Reward</title>
    
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
                        <i class="bi bi-gift-fill me-2"></i>
                        REWARDS PROGRAM
                    </p>
                    <h1 class="display-5 fw-bold mb-3">Create New Reward</h1>
                    <p class="lead opacity-90 mb-0">
                        Design attractive incentives to boost user engagement and survey participation.
                    </p>
                </div>
            </div>
        </div>

        <div class="row g-4">
            
            <!-- MAIN FORM -->
            <div class="col-lg-7">
                <div class="form-card card">
                    <div class="card-body p-5">
                        <h4 class="mb-4">Reward Details</h4>
                        
                        <?php if ($message != "") { ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <?php echo $message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php } ?>

                        <form method="POST" id="rewardForm">
                            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Reward Name</label>
                                <input type="text" 
                                       name="reward_name" 
                                       id="reward_name"
                                       class="form-control form-control-lg" 
                                       placeholder="e.g. $10 Amazon Gift Card"
                                       onkeyup="updatePreview()"
                                       required>
                            </div>

                            <div class="mb-5">
                                <label class="form-label fw-semibold">Points Required</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light">⭐</span>
                                    <input type="number" 
                                           name="points_required" 
                                           id="points_required"
                                           class="form-control" 
                                           placeholder="250"
                                           min="1"
                                           onkeyup="updatePreview()"
                                           required>
                                </div>
                                <small class="text-muted">Points users must accumulate to redeem this reward</small>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary submit-btn">
                                    <i class="bi bi-plus-circle me-2"></i>
                                    Create Reward
                                </button>
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
                        <div id="rewardPreview" class="preview-card p-4 text-center">
                            <div class="mb-4">
                                <i class="bi bi-gift display-1 text-primary"></i>
                            </div>
                            <h5 id="previewName" class="fw-semibold mb-2">Reward Name</h5>
                            <div class="fs-3 fw-bold text-primary mb-3" id="previewPoints">250 pts</div>
                            <small class="text-muted">Redeem this reward in the user portal</small>
                        </div>
                    </div>
                </div>
                
                <!-- Tips -->
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="mb-4">
                            <i class="bi bi-lightbulb text-warning"></i> Tips for Success
                        </h5>
                        <ul class="list-unstyled small">
                            <li class="d-flex gap-3 mb-3">
                                <span class="text-success">✓</span>
                                <div>Use compelling, specific reward names</div>
                            </li>
                            <li class="d-flex gap-3 mb-3">
                                <span class="text-success">✓</span>
                                <div>Balance point value with reward appeal</div>
                            </li>
                            <li class="d-flex gap-3 mb-3">
                                <span class="text-success">✓</span>
                                <div>Popular rewards drive more survey completions</div>
                            </li>
                            <li class="d-flex gap-3">
                                <span class="text-success">✓</span>
                                <div>Start with low-point rewards to encourage participation</div>
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
            const nameInput = document.getElementById('reward_name').value.trim();
            const pointsInput = document.getElementById('points_required').value.trim();
            
            document.getElementById('previewName').textContent = nameInput || "Reward Name";
            
            const pointsEl = document.getElementById('previewPoints');
            if (pointsInput) {
                pointsEl.textContent = parseInt(pointsInput).toLocaleString() + " pts";
            } else {
                pointsEl.textContent = "250 pts";
            }
        }
        
        // Initialize preview
        window.onload = function() {
            updatePreview();
            
            // Auto-focus first field
            const firstField = document.getElementById('reward_name');
            if (firstField) firstField.focus();
        };
        
        // Form submit feedback
        document.getElementById('rewardForm').addEventListener('submit', function() {
            const btn = this.querySelector('button[type="submit"]');
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Creating Reward...`;
            btn.disabled = true;
        });
    </script>
</body>
</html>