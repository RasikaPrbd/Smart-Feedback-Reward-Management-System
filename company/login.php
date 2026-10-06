<?php

/*
 * -------------------------------------------------------
 * Page    : Company Login
 *
 * Purpose : Handles company authentication. Verifies company credentials and initiates a company session on success.
 * -------------------------------------------------------
 */

// Initialize the session to track user authentication state.
session_start();

if (isset($_SESSION['company_id'])) {
    // Redirect the user to the appropriate page after the action completes.
    header("Location: dashboard.php");
    exit();
}

// Include the database connection configuration to interact with the database.
include("../config/db.php");

$message = "";

// Only process the form when the request was sent via POST to prevent accidental GET submissions.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = strtolower(trim($_POST['email']));
    $password = trim($_POST['password']);

    // Prepare the SQL statement to securely handle user input and prevent SQL injection.
    $stmt = $conn->prepare(
        "SELECT * FROM companies WHERE email = ?"
    );

    // Bind parameters to the prepared statement.
    $stmt->bind_param("s", $email);

    // Execute the prepared statement against the database.
    $stmt->execute();

    // Retrieve the result set from the executed prepared statement.
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $company = $result->fetch_assoc();

        if (isset($company['status']) && $company['status'] === 'inactive') {
            
            $message = "Your account has been deactivated. Please contact the administrator.";
            
        } elseif (password_verify($password, $company['password'])) {

            session_regenerate_id(true);

            $_SESSION['company_id'] = $company['company_id'];
            $_SESSION['company_name'] = $company['company_name'];

            // Redirect the user to the appropriate page after the action completes.
            header("Location: dashboard.php");
            exit();

        } else {

            $message = "Invalid email or password";

        }

    } else {

        $message = "Invalid email or password";

    }

    $stmt->close();

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Login</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --dark: #1e2937;
        }
        
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
        }
        
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        
        .left-panel {
            background: linear-gradient(135deg, var(--dark) 0%, #334155 100%);
            color: white;
            padding: 5rem 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .login-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            max-width: 420px;
        }
        
        .form-control {
            border-radius: 12px;
            padding: 14px 18px;
            border: 2px solid #e2e8f0;
            font-size: 1.05rem;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(99, 102, 241, 0.15);
        }
        
        .btn-login {
            padding: 14px 32px;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
        }
        
        .register-link {
            transition: all 0.3s ease;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="container">
            <div class="row">
                
                <!-- LEFT BRANDING PANEL -->
                <div class="col-lg-6 d-none d-lg-flex left-panel">
                    <div>
                        <div class="d-flex align-items-center gap-3 mb-5">
                            <i class="bi bi-graph-up-arrow fs-1"></i>
                            <h2 class="fw-bold mb-0">Company Portal</h2>
                        </div>
                        
                        <h1 class="display-5 fw-bold mb-4">Welcome Back</h1>
                        
                        <p class="lead opacity-75 mb-5">
                            Manage your surveys, track responses, and reward your customers — all in one place.
                        </p>
                        
                        <div class="d-flex gap-5 text-white-50 small">
                            <div><i class="bi bi-check-circle me-2"></i>Surveys</div>
                            <div><i class="bi bi-check-circle me-2"></i>Analytics</div>
                            <div><i class="bi bi-check-circle me-2"></i>Rewards</div>
                        </div>
                    </div>
                </div>
                
                <!-- LOGIN FORM -->
                <div class="col-lg-6 d-flex align-items-center justify-content-center">
                    <div class="login-card card w-100">
                        <div class="card-body p-5">
                            <div class="text-center mb-5">
                                <i class="bi bi-building text-primary display-4 mb-3"></i>
                                <h3 class="fw-bold">Company Login</h3>
                                <p class="text-muted">Sign in to access your business dashboard</p>
                            </div>
                            
                            <?php if($message != "") { ?>
                                <div class="alert alert-danger text-center">
                                    <?php echo $message; ?>
                                </div>
                            <?php } ?>
                            
                            <form method="POST">
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Email Address</label>
                                    <input type="email" 
                                           name="email" 
                                           class="form-control form-control-lg" 
                                           placeholder="company@business.com" 
                                           required>
                                </div>
                                
                                <div class="mb-5">
                                    <label class="form-label fw-semibold">Password</label>
                                    <div class="input-group input-group-lg">
                                        <input type="password" 
                                               name="password" 
                                               id="passwordField"
                                               class="form-control" 
                                               required>
                                        <button type="button" 
                                                class="btn btn-outline-secondary border-0"
                                                onclick="togglePassword()">
                                            <i class="bi bi-eye" id="toggleIcon"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <button type="submit" 
                                        class="btn btn-primary btn-login w-100 mb-4">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>
                                    Sign In
                                </button>
                            </form>
                            
                            <div class="text-center">
                                <a href="register.php" 
                                   class="btn btn-outline-success register-link w-100">
                                    <i class="bi bi-person-plus me-2"></i>
                                    Register New Company
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function togglePassword() {
            const passwordField = document.getElementById('passwordField');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordField.type === "password") {
                passwordField.type = "text";
                toggleIcon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                passwordField.type = "password";
                toggleIcon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        }
        
        // Auto-focus email field
        document.addEventListener('DOMContentLoaded', function() {
            const emailField = document.querySelector('input[name="email"]');
            if (emailField) emailField.focus();
        });
    </script>
</body>
</html>