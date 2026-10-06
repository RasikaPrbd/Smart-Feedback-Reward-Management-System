<?php

/*
 * -------------------------------------------------------
 * Page    : Create Survey
 *
 * Purpose : Allows a company to create a new survey with a title, description, reward points, expiry date, and initial status.
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

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $expiry_date = $_POST['expiry_date'] ?? '';
    if($expiry_date < date("Y-m-d"))
    {
        $message = "Expiry date cannot be in the past.";
    }
    else{

        // Prepare the SQL statement to securely handle user input and prevent SQL injection.
        $stmt = $conn->prepare("
            INSERT INTO surveys
            (
                company_id,
                title,
                description,
                expiry_date
            )
            VALUES
            (
                ?, ?, ?, ?
            )
        ");

        // Bind parameters to the prepared statement.
        $stmt->bind_param(
            "isss",
            $company_id,
            $title,
            $description,
            $expiry_date
        );

        if($stmt->execute())
        {

            $survey_id = $stmt->insert_id;
            
            $_SESSION['success_msg'] = "✓ Survey created as Draft. Add at least 3 questions before publishing.";
            header("Location: add_question.php?survey_id=".$survey_id);
            exit();

        }
        else
        {

            $message = "Unable to create survey.";

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
    <title>Create New Survey</title>
    
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
        
        .create-hero {
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
        
        .form-label {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.5rem;
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
        
        .tips-card {
            background: white;
            border-radius: 18px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06);
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
        <div class="create-hero">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <p class="mb-2 opacity-90 fs-5">
                        <i class="bi bi-plus-circle me-2"></i>
                        NEW CAMPAIGN
                    </p>
                    <h1 class="display-5 fw-bold mb-3">Create New Survey</h1>
                    <p class="lead opacity-90 mb-0">
                        Design engaging surveys to collect valuable insights from your audience.
                    </p>
                </div>
            </div>
        </div>

        <div class="row g-4">
            
            <!-- MAIN FORM -->
            <div class="col-lg-8">
                <div class="form-card card">
                    <div class="card-body p-5">
                        <h3 class="mb-4 section-title">Survey Details</h3>
                        
                        <?php if ($message != "") { ?>
                            <div class="alert alert-danger">
                                <?php echo $message; ?>
                            </div>
                        <?php } ?>

                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                            <div class="mb-4">
                                <label class="form-label">Survey Title</label>
                                <input type="text" 
                                       name="title" 
                                       class="form-control form-control-lg" 
                                       placeholder="e.g. Customer Satisfaction Survey 2026"
                                       required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Description</label>
                                <textarea name="description" 
                                          class="form-control" 
                                          rows="5"
                                          placeholder="Provide a clear description of what this survey is about..."></textarea>
                            </div>

                            <div class="row g-4">
                                
                                <div class="col-md-6">
                                    <label class="form-label">Expiry Date</label>
                                    <input type="date"
                                        name="expiry_date"
                                        class="form-control form-control-lg"
                                        min="<?php echo date('Y-m-d'); ?>"
                                        required>
                                </div>
                            </div>

                            <hr class="my-5">
                            
                            <div class="d-grid d-md-flex justify-content-md-end">
                                <button type="submit" 
                                        class="btn btn-primary submit-btn px-5">
                                    <i class="bi bi-plus-lg me-2"></i>
                                    Create Survey &amp; Add Questions
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- SIDE TIPS -->
            <div class="col-lg-4">
                <div class="tips-card card h-100 p-4">
                    <h5 class="mb-4">
                        <i class="bi bi-lightbulb text-warning"></i> 
                        Pro Tips
                    </h5>
                    <ul class="list-unstyled">
                        <li class="d-flex gap-3 mb-3">
                            <span class="text-success">✓</span>
                            <div>
                                <strong>Clear Title</strong><br>
                                <small class="text-muted">Make it specific and engaging</small>
                            </div>
                        </li>
                        <li class="d-flex gap-3 mb-3">
                            <span class="text-success">✓</span>
                            <div>
                                <strong>Compelling Description</strong><br>
                                <small class="text-muted">Explain the purpose and value</small>
                            </div>
                        </li>
                        <li class="d-flex gap-3 mb-3">
                            <span class="text-success">✓</span>
                            <div>
                                <strong>Automatic Reward Points</strong><br>
                                <small class="text-muted">Reward points are assigned automatically based on the number of questions.</small>
                            </div>
                        </li>
                        <li class="d-flex gap-3">
                            <span class="text-success">✓</span>
                            <div>
                                <strong>Strategic Expiry</strong><br>
                                <small class="text-muted">Give users enough time to respond</small>
                            </div>
                        </li>
                    </ul>
                    
                    <div class="mt-auto pt-4 border-top">
                        <small class="text-muted">
                            After creating the survey, you'll be redirected to add questions.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>