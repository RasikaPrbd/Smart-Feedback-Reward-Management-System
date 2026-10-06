<?php

/*
 * -------------------------------------------------------
 * Page    : User Login
 *
 * Purpose : Handles user authentication. Validates credentials against the database and creates a secure user session.
 * -------------------------------------------------------
 */

// Initialize the session to track user authentication state.
session_start();

if(isset($_SESSION['user_id']))
{
    // Redirect the user to the appropriate page after the action completes.
    header("Location: dashboard.php");
    exit();
}

// Include the database connection configuration to interact with the database.
include("../config/db.php");

$message = "";

// Only process the form when the request was sent via POST to prevent accidental GET submissions.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Prepare the SQL statement to securely handle user input and prevent SQL injection.
    $stmt = $conn->prepare(
    "SELECT * FROM users WHERE email = ?"
    );

    // Bind parameters to the prepared statement.
    $stmt->bind_param("s", $email);

    // Execute the prepared statement against the database.
    $stmt->execute();

// Retrieve the result set from the executed prepared statement.
$result = $stmt->get_result();

if ($result->num_rows > 0) {

    $user = $result->fetch_assoc();

    if (isset($user['status']) && $user['status'] === 'inactive') {

        $message = "Your account has been deactivated. Please contact the administrator.";

    } elseif (password_verify($password, $user['password'])) {

        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_name'] = $user['name'];

        // Redirect the user to the appropriate page after the action completes.
        header("Location: dashboard.php");
        exit();

    } else {

        $message = "Invalid email or password.";

    }

} else {

    $message = "Invalid email or password.";

}

$stmt->close();

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    
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
            display: flex;
            align-items: center;
        }
        
        .login-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            max-width: 420px;
            width: 100%;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border-radius: 20px 20px 0 0 !important;
            padding: 2.5rem 2rem 2rem;
            text-align: center;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6 col-lg-5">
                
                <div class="login-card card">
                    
                    <!-- HEADER -->
                    <div class="card-header">
                        <i class="bi bi-person-circle fs-1 mb-3"></i>
                        <h2 class="fw-bold mb-2">User Login</h2>
                        <p class="opacity-90 mb-0">Sign in to continue</p>
                    </div>
                    
                    <div class="card-body p-4 p-sm-5">
                        
                        <?php if($message != "") { ?>
                            <div class="alert alert-danger">
                                <?php echo $message; ?>
                            </div>
                        <?php } ?>
                        
                        <form method="POST">
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Email Address</label>
                                <input type="email" 
                                       name="email" 
                                       class="form-control form-control-lg" 
                                       placeholder="you@email.com" 
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
                                Sign In
                            </button>
                        </form>
                        
                        <div class="text-center">
                            <a href="register.php" 
                               class="text-decoration-none text-muted small">
                                Don't have an account? <strong class="text-primary">Register</strong>
                            </a>
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