<?php

/*
 * -------------------------------------------------------
 * Page    : User Registration
 *
 * Purpose : Allows a new user to create an account. Validates input, checks for duplicate email, and stores the hashed password.
 * -------------------------------------------------------
 */

// Initialize the session to track user authentication state.
session_start();

// Include the database connection configuration to interact with the database.
include("../config/db.php");

$message = "";

// Only process the form when the request was sent via POST to prevent accidental GET submissions.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $raw_password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    if (empty($name)) {
        $message = "Name cannot be empty.";
    } elseif (empty($email)) {
        $message = "Email cannot be empty.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
    } elseif (strlen($raw_password) < 6) {
        $message = "Password must be at least 6 characters.";
    } elseif ($raw_password !== $confirm_password) {
        $message = "Passwords do not match.";
    } else {
        $password = password_hash(
            $raw_password,
            PASSWORD_DEFAULT
        );

        /* Check if email already exists */

        $check = $conn->prepare(
            "SELECT user_id FROM users WHERE email = ?"
        );

        $check->bind_param("s", $email);

        $check->execute();

        $check_result = $check->get_result();

        if ($check_result->num_rows > 0) {

            $message = "Email already registered";

        } else {

            // Prepare the SQL statement to securely handle user input and prevent SQL injection.
            $stmt = $conn->prepare(
                "INSERT INTO users
                (name, email, password)
                VALUES (?, ?, ?)"
            );

            // Bind parameters to the prepared statement.
            $stmt->bind_param(
                "sss",
                $name,
                $email,
                $password
            );

            if ($stmt->execute()) {

                // Redirect the user to the appropriate page after the action completes.
                header("Location: login.php");
                exit();

            } else {

                $message = "Registration failed";

            }

            $stmt->close();
        }

        $check->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    
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
        
        .register-card {
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
        
        .btn-register {
            padding: 14px 32px;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6 col-lg-5">
                
                <div class="register-card card">
                    
                    <!-- HEADER -->
                    <div class="card-header">
                        <i class="bi bi-person-plus-fill fs-1 mb-3"></i>
                        <h2 class="fw-bold mb-2">Create Account</h2>
                        <p class="opacity-90 mb-0">Join and start earning rewards</p>
                    </div>
                    
                    <div class="card-body p-4 p-sm-5">
                        
                        <?php if($message != "") { ?>
                            <div class="alert alert-danger">
                                <?php echo $message; ?>
                            </div>
                        <?php } ?>
                        
                        <form method="POST" id="registerForm">
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Full Name</label>
                                <input type="text" 
                                       name="name" 
                                       class="form-control form-control-lg" 
                                       placeholder="John Doe" 
                                       required>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Email Address</label>
                                <input type="email" 
                                       name="email" 
                                       class="form-control form-control-lg" 
                                       placeholder="you@email.com" 
                                       required>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Password</label>
                                <div class="input-group input-group-lg">
                                    <input type="password" 
                                           name="password" 
                                           id="passwordField"
                                           class="form-control" 
                                           required>
                                    <button type="button" 
                                            class="btn btn-outline-secondary border-0"
                                            onclick="togglePassword('passwordField', 'toggleIcon')">
                                        <i class="bi bi-eye" id="toggleIcon"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-5">
                                <label class="form-label fw-semibold">Confirm Password</label>
                                <div class="input-group input-group-lg">
                                    <input type="password" 
                                           name="confirm_password" 
                                           id="confirmPasswordField"
                                           class="form-control" 
                                           required>
                                    <button type="button" 
                                            class="btn btn-outline-secondary border-0"
                                            onclick="togglePassword('confirmPasswordField', 'toggleConfirmIcon')">
                                        <i class="bi bi-eye" id="toggleConfirmIcon"></i>
                                    </button>
                                </div>
                                <div id="passwordError" class="text-danger mt-2 fw-medium" style="display: none;">Passwords do not match.</div>
                            </div>
                            
                            <button type="submit" 
                                    class="btn btn-primary btn-register w-100 mb-4">
                                Create Account
                            </button>
                        </form>
                        
                        <div class="text-center">
                            <a href="login.php" 
                               class="text-decoration-none text-muted small">
                                Already have an account? <strong class="text-primary">Sign In</strong>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function togglePassword(fieldId, iconId) {
            const passwordField = document.getElementById(fieldId);
            const toggleIcon = document.getElementById(iconId);
            
            if (passwordField.type === "password") {
                passwordField.type = "text";
                toggleIcon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                passwordField.type = "password";
                toggleIcon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        }
        
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('passwordField').value;
            const confirmPassword = document.getElementById('confirmPasswordField').value;
            const errorDiv = document.getElementById('passwordError');
            
            if (password !== confirmPassword) {
                e.preventDefault();
                errorDiv.style.display = 'block';
            } else {
                errorDiv.style.display = 'none';
            }
        });
        
        // Auto-focus first field
        document.addEventListener('DOMContentLoaded', function() {
            const firstField = document.querySelector('input[name="name"]');
            if (firstField) firstField.focus();
        });
    </script>
</body>
</html>