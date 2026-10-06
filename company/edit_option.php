<?php
/*
 * -------------------------------------------------------
 * Page    : Edit Option
 *
 * Purpose : Allows a company to update the text of an existing Multiple Choice option.
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

if (!isset($_GET['id']) || !isset($_GET['question_id']) || !isset($_GET['survey_id'])) {
    // Redirect the user to the appropriate page after the action completes.
    header("Location: manage_surveys.php");
    exit();
}

$option_id = (int)$_GET['id'];
$question_id = (int)$_GET['question_id'];
$survey_id = (int)$_GET['survey_id'];
$company_id = $_SESSION['company_id'];

// Verify ownership
    // Enforce ownership by filtering surveys to only those belonging to the logged-in company.
$check_stmt = $conn->prepare("
    SELECT o.option_text, s.expiry_date 
    FROM options o 
    JOIN questions q ON o.question_id = q.question_id 
    JOIN surveys s ON q.survey_id = s.survey_id 
    WHERE o.option_id = ? AND q.question_id = ? AND s.survey_id = ? AND s.company_id = ?
");
$check_stmt->bind_param("iiii", $option_id, $question_id, $survey_id, $company_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    $check_stmt->close();
    die("Option not found or access denied.");
}
$option = $result->fetch_assoc();
$check_stmt->close();

$is_expired = strtotime($option['expiry_date']) < time();
if ($is_expired) {
    $_SESSION['error_msg'] = "Survey is expired. You cannot edit options.";
    // Redirect the user to the appropriate page after the action completes.
    header("Location: manage_options.php?question_id=$question_id&survey_id=$survey_id");
    exit();
}

// Only process the form when the request was sent via POST to prevent accidental GET submissions.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $option_text = trim($_POST['option_text']);
    
    if (empty($option_text)) {
        $_SESSION['error_msg'] = "Option text cannot be empty.";
    } else {
        $update_stmt = $conn->prepare("UPDATE options SET option_text = ? WHERE option_id = ?");
        $update_stmt->bind_param("si", $option_text, $option_id);
        
        if ($update_stmt->execute()) {
            $_SESSION['success_msg'] = "✓ Option updated successfully.";
            $update_stmt->close();
            // Redirect the user to the appropriate page after the action completes.
            header("Location: manage_options.php?question_id=$question_id&survey_id=$survey_id");
            exit();
        } else {
            $_SESSION['error_msg'] = "Error updating option.";
        }
        $update_stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Option</title>
    <!-- Bootstrap 5 -->
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
        .form-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
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
    </style>
</head>
<body>
    <?php include("../includes/company_navbar.php"); ?>

    <div class="container dashboard-container">
        
        <?php if (isset($_SESSION['error_msg'])): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                <strong class="d-flex align-items-center">
                    <?php echo $_SESSION['error_msg']; ?>
                </strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error_msg']); ?>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="form-card card">
                    <div class="card-body p-5">
                        <h4 class="mb-4 d-flex align-items-center">
                            <i class="bi bi-pencil-square text-primary me-2"></i> Edit Option
                        </h4>
                        
                        <form method="POST">
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Option Text</label>
                                <input type="text" name="option_text" class="form-control" value="<?php echo htmlspecialchars($option['option_text']); ?>" required>
                            </div>

                            <div class="d-flex gap-3">
                                <button type="submit" class="btn btn-primary px-4 py-2 flex-fill fw-medium rounded-pill">
                                    <i class="bi bi-save me-1"></i> Save Changes
                                </button>
                                <a href="manage_options.php?question_id=<?php echo $question_id; ?>&survey_id=<?php echo $survey_id; ?>" class="btn btn-outline-secondary px-4 py-2 flex-fill fw-medium rounded-pill">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
