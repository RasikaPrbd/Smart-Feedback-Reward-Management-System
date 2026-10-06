<?php

/*
 * -------------------------------------------------------
 * Page    : Edit Reward
 *
 * Purpose : Allows a company to update the name and point requirement of an existing reward.
 * -------------------------------------------------------
 */

// Initialize the session to track user authentication state.
session_start();
// Include the database connection configuration to interact with the database.
include("../config/db.php");

// Verify that the company is logged in before granting access.
if (!isset($_SESSION['company_id'])) {
    // Redirect the user to the appropriate page after the action completes.
    header("Location: login.php");
    exit();
}

$company_id = $_SESSION['company_id'];

if (!isset($_GET['id'])) {
    // Redirect the user to the appropriate page after the action completes.
    header("Location: manage_rewards.php");
    exit();
}

$reward_id = $_GET['id'];

// Prepare the SQL statement to securely handle user input and prevent SQL injection.
$stmt = $conn->prepare("SELECT * FROM rewards WHERE reward_id = ? AND company_id = ?");
// Bind parameters to the prepared statement.
$stmt->bind_param("ii", $reward_id, $company_id);
// Execute the prepared statement against the database.
$stmt->execute();
// Retrieve the result set from the executed prepared statement.
$result = $stmt->get_result();

if($result->num_rows == 0)
{
    $stmt->close();
    die("Reward not found.");
}

$reward = $result->fetch_assoc();
$stmt->close();

// Only process the form when the request was sent via POST to prevent accidental GET submissions.
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $reward_name = $_POST['reward_name'];
    $points_required = $_POST['points_required'];

    $update_stmt = $conn->prepare("UPDATE rewards SET reward_name = ?, points_required = ? WHERE reward_id = ?");
    $update_stmt->bind_param("sii", $reward_name, $points_required, $reward_id);

    if($update_stmt->execute())
    {
        $update_stmt->close();
        $_SESSION['success_msg'] = "✓ Reward updated successfully.";
        // Redirect the user to the appropriate page after the action completes.
        header("Location: manage_rewards.php");
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Reward</title>
    
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
        
        .form-control {
            border-radius: 12px;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
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
                        MANAGE REWARDS
                    </p>
                    <h1 class="display-5 fw-bold mb-3">Edit Reward</h1>
                    <p class="lead opacity-90 mb-0">
                        Update reward details to better incentivize user participation.
                    </p>
                </div>
            </div>
        </div>

        <div class="row g-4">
            
            <!-- MAIN EDIT FORM -->
            <div class="col-lg-7">
                <div class="form-card card">
                    <div class="card-body p-5">
                        <h4 class="mb-4">Update Reward Information</h4>
                        
                        <form method="POST" id="rewardForm">
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Reward Name</label>
                                <input type="text" 
                                       name="reward_name" 
                                       id="reward_name"
                                       class="form-control form-control-lg" 
                                       // Escape output to prevent Cross-Site Scripting (XSS) attacks.
                                       value="<?php echo htmlspecialchars($reward['reward_name']); ?>"
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
                                           value="<?php echo $reward['points_required']; ?>"
                                           min="1"
                                           onkeyup="updatePreview()"
                                           required>
                                </div>
                            </div>

                            <div class="d-flex gap-3">
                                <button type="submit" 
                                        name="update"
                                        class="btn btn-primary submit-btn flex-fill">
                                    <i class="bi bi-save me-2"></i>
                                    Update Reward
                                </button>
                                <a href="manage_rewards.php" 
                                   class="btn btn-outline-secondary submit-btn">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- LIVE PREVIEW + INFO -->
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
                            <h5 id="previewName" class="fw-semibold mb-2"><?php echo htmlspecialchars($reward['reward_name']); ?></h5>
                            <div class="fs-3 fw-bold text-primary mb-3" id="previewPoints"><?php echo number_format($reward['points_required']); ?> pts</div>
                            <small class="text-muted">Users will see this reward in the marketplace</small>
                        </div>
                    </div>
                </div>
                
                <!-- Current Info -->
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="mb-4 text-muted">Current Details</h5>
                        <div class="border-bottom pb-3 mb-3">
                            <small class="text-muted">Reward ID</small>
                            <div class="fw-medium">#<?php echo $reward_id; ?></div>
                        </div>
                        <div>
                            <small class="text-muted">Created By</small>
                            <div class="fw-medium">Your Company</div>
                        </div>
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
            }
        }
        
        // Initialize
        window.onload = function() {
            updatePreview();
        };
        
        // Form feedback
        document.getElementById('rewardForm').addEventListener('submit', function() {
            const btn = this.querySelector('button[name="update"]');
            if (btn) {
                btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Updating...`;
                btn.disabled = true;
            }
        });
    </script>
</body>
</html>