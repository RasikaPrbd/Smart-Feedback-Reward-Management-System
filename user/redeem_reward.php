<?php

/*
 * -------------------------------------------------------
 * Page    : Redeem Reward
 *
 * Purpose : Processes a reward redemption request. Validates sufficient points, deducts them, and creates a redemption record.
 * -------------------------------------------------------
 */

// Initialize the session to track user authentication state.
session_start();

// Include the database connection configuration to interact with the database.
include("../config/db.php");

// Include the CSRF helper to generate and validate security tokens for forms.
include("../includes/csrf_helper.php");

// Verify that the user is logged in before granting access.
if (!isset($_SESSION['user_id'])) {
    // Redirect the user to the appropriate page after the action completes.
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request method");
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    csrf_abort();
}

if (!isset($_POST['reward_id'])) {
    die("Reward ID missing");
}

$user_id = $_SESSION['user_id'];
$reward_id = $_POST['reward_id'];

/* Get reward details */
$reward_stmt = $conn->prepare("SELECT * FROM rewards WHERE reward_id = ?");
$reward_stmt->bind_param("i", $reward_id);
$reward_stmt->execute();
$reward_result = $reward_stmt->get_result();
$reward = $reward_result->fetch_assoc();
$reward_stmt->close();

if (!$reward) {
    die("Invalid reward");
}

if (isset($reward['status']) && $reward['status'] === 'archived') {
    die("This reward is no longer available.");
}

$points_required = $reward['points_required'];

try {
    // Begin a database transaction to ensure multiple dependent queries execute safely without partial updates.
    $conn->begin_transaction();

    /* Deduct points atomically to prevent race condition */
    $update_stmt = $conn->prepare("UPDATE users SET points = points - ? WHERE user_id = ? AND points >= ?");
    $update_stmt->bind_param("iii", $points_required, $user_id, $points_required);
    $update_stmt->execute();

    if ($update_stmt->affected_rows === 0) {
        $update_stmt->close();
        throw new Exception("Not enough points");
    }
    $update_stmt->close();

    /* Generate coupon code */
    $code = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));

    /* Save redemption */
    $redeem_stmt = $conn->prepare("INSERT INTO redemptions(user_id, reward_id, code) VALUES(?, ?, ?)");
    $redeem_stmt->bind_param("iis", $user_id, $reward_id, $code);
    $redeem_stmt->execute();
    $redeem_stmt->close();

    // Commit the transaction to permanently save the changes to the database.
    $conn->commit();
} catch (Exception $e) {
    // Rollback the transaction if an error occurs to prevent data corruption.
    $conn->rollback();
    die($e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reward Redeemed Successfully</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --primary: #6366f1;
            --success: #10b981;
            --dark: #1e2937;
        }
        
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
        }
        
        .success-container {
            padding-top: 3rem;
            padding-bottom: 5rem;
        }
        
        .success-hero {
            background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
            color: white;
            border-radius: 24px;
            padding: 4rem 2.5rem;
            text-align: center;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(16, 185, 129, 0.3);
        }
        
        .success-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.3) 0%, transparent 70%);
            animation: pulse 8s infinite ease-in-out;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 0.6; }
            50% { opacity: 1; }
        }
        
        .coupon-card {
            border: 3px dashed #10b981;
            border-radius: 20px;
            background: white;
            transition: all 0.3s ease;
        }
        
        .coupon-code {
            font-family: 'Courier New', monospace;
            font-size: 2.25rem;
            font-weight: 700;
            letter-spacing: 4px;
            color: var(--dark);
        }
        
        .reward-summary {
            background: white;
            border-radius: 18px;
        }
        
        .btn-success {
            background: #10b981;
            border: none;
        }
        
        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            animation: fall 4s linear infinite;
        }
    </style>
</head>
<body>
    <?php include("../includes/user_navbar.php"); ?>

    <div class="container success-container">
        
        <!-- SUCCESS HERO -->
        <div class="success-hero mb-5 position-relative">
            <div class="mb-4">
                <i class="bi bi-check-circle-fill display-1"></i>
            </div>
            <h1 class="display-5 fw-bold mb-3">Reward Redeemed Successfully!</h1>
            <p class="lead opacity-90 mb-0">Congratulations! Your reward is now active.</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                
                <!-- COUPON CODE -->
                <div class="coupon-card card shadow mb-5 p-5 text-center">
                    <div class="mb-4">
                        <span class="badge bg-success fs-6 px-4 py-2">YOUR COUPON CODE</span>
                    </div>
                    <div class="coupon-code mb-4" id="couponCode">
                        <?php echo $code; ?>
                    </div>
                    <button onclick="copyCoupon()" 
                            class="btn btn-outline-success px-5 py-3 fw-semibold">
                        <i class="bi bi-clipboard me-2"></i>
                        Copy Code
                    </button>
                    <div id="copyMessage" class="mt-3 text-success fw-medium" style="display: none;">
                        ✓ Coupon code copied to clipboard!
                    </div>
                </div>

                <!-- REWARD SUMMARY -->
                <div class="reward-summary card shadow p-5 mb-5">
                    <h4 class="text-center mb-4">Redemption Summary</h4>
                    <div class="row text-center">
                        <div class="col-md-4">
                            <p class="text-muted mb-1">Reward</p>
                            <h5 class="fw-semibold"><?php echo htmlspecialchars($reward['reward_name']); ?></h5>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted mb-1">Points Used</p>
                            <h5 class="fw-semibold text-danger"><?php echo number_format($points_required); ?></h5>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted mb-1">Redeemed On</p>
                            <h5 class="fw-semibold"><?php echo date('M j, Y'); ?></h5>
                        </div>
                    </div>
                </div>

                <!-- NEXT ACTIONS -->
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                    <a href="view_rewards.php" 
                       class="btn btn-primary px-5 py-3 fw-semibold flex-fill">
                        <i class="bi bi-gift me-2"></i>
                        Browse More Rewards
                    </a>
                    <a href="dashboard.php" 
                       class="btn btn-outline-primary px-5 py-3 fw-semibold flex-fill">
                        <i class="bi bi-house-door me-2"></i>
                        Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function copyCoupon() {
            const code = document.getElementById('couponCode').innerText;
            navigator.clipboard.writeText(code).then(() => {
                const message = document.getElementById('copyMessage');
                message.style.display = 'block';
                
                setTimeout(() => {
                    message.style.display = 'none';
                }, 3000);
            });
        }
        
        // Celebration animation
        document.addEventListener('DOMContentLoaded', function() {
            // Add subtle confetti dots (visual only)
            const hero = document.querySelector('.success-hero');
            for (let i = 0; i < 30; i++) {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                confetti.style.left = Math.random() * 100 + '%';
                confetti.style.backgroundColor = ['#10b981', '#6366f1', '#f59e0b'][Math.floor(Math.random()*3)];
                confetti.style.animationDuration = (Math.random() * 3 + 3) + 's';
                confetti.style.animationDelay = Math.random() * 2 + 's';
                confetti.style.opacity = Math.random() * 0.7 + 0.4;
                hero.appendChild(confetti);
                
                // Remove after animation
                setTimeout(() => confetti.remove(), 8000);
            }
        });
    </script>
</body>
</html>