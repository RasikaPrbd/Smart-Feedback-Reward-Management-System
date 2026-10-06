<?php

/*
 * -------------------------------------------------------
 * Page    : View Redemptions
 *
 * Purpose : Displays all redemption records for a specific reward, showing which users redeemed it and when.
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

if (!isset($_GET['reward_id'])) {
    // Redirect the user to the appropriate page after the action completes.
    header("Location: manage_rewards.php");
    exit();
}

$reward_id = (int)$_GET['reward_id'];

/* Validate ownership */
// Prepare the SQL statement to securely handle user input and prevent SQL injection.
$stmt = $conn->prepare("SELECT reward_name, points_required FROM rewards WHERE reward_id = ? AND company_id = ?");
// Bind parameters to the prepared statement.
$stmt->bind_param("ii", $reward_id, $company_id);
// Execute the prepared statement against the database.
$stmt->execute();
$reward_res = $stmt->get_result();

if ($reward_res->num_rows === 0) {
    $stmt->close();
    // Redirect the user to the appropriate page after the action completes.
    header("Location: manage_rewards.php");
    exit();
}

$reward = $reward_res->fetch_assoc();
$stmt->close();

/* Get redemptions with users in a single JOIN */
$red_stmt = $conn->prepare("
    SELECT r.code, r.redeemed_at, u.name, u.email
    FROM redemptions r
    JOIN users u ON r.user_id = u.user_id
    WHERE r.reward_id = ?
    ORDER BY r.redeemed_at DESC
");
$red_stmt->bind_param("i", $reward_id);
$red_stmt->execute();
$redemptions = $red_stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Redemptions</title>
    
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
        
        .empty-state {
            text-align: center;
            padding: 6rem 2rem;
            background: white;
            border-radius: 18px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
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
                        <a href="manage_rewards.php" class="text-white text-decoration-none"><i class="bi bi-arrow-left me-2"></i>Back to Rewards</a>
                    </p>
                    <h1 class="display-5 fw-bold mb-3"><?php echo htmlspecialchars($reward['reward_name']); ?></h1>
                    <p class="lead opacity-90 mb-0">
                        <i class="bi bi-star-fill text-warning me-2"></i> <?php echo number_format($reward['points_required']); ?> pts required per redemption
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                    <div class="bg-white bg-opacity-10 rounded-4 p-4 d-inline-block text-center shadow-sm">
                        <h2 class="display-4 fw-bold mb-0 text-white"><?php echo $redemptions->num_rows; ?></h2>
                        <small class="text-white text-opacity-75 text-uppercase fw-semibold">Total Redemptions</small>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($redemptions->num_rows > 0): ?>
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4 py-3 text-uppercase fs-7 fw-semibold text-muted">User Name</th>
                                    <th class="py-3 text-uppercase fs-7 fw-semibold text-muted">User Email</th>
                                    <th class="py-3 text-uppercase fs-7 fw-semibold text-muted">Points Used</th>
                                    <th class="pe-4 py-3 text-end text-uppercase fs-7 fw-semibold text-muted">Redeemed Date & Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $redemptions->fetch_assoc()): ?>
                                    <tr>
                                        <td class="ps-4 py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                    <i class="bi bi-person-fill fs-5"></i>
                                                </div>
                                                <span class="fw-bold text-dark"><?php echo htmlspecialchars($row['name']); ?></span>
                                            </div>
                                        </td>
                                        <td class="py-3 text-muted">
                                            <?php echo htmlspecialchars($row['email']); ?>
                                        </td>
                                        <td class="py-3">
                                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill px-3 py-2 fw-medium">
                                                -<?php echo number_format($reward['points_required']); ?> pts
                                            </span>
                                        </td>
                                        <td class="pe-4 py-3 text-end text-muted small">
                                            <?php echo date("M j, Y, g:i a", strtotime($row['redeemed_at'])); ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="mb-4">
                    <i class="bi bi-people display-1 text-muted"></i>
                </div>
                <h4 class="text-dark">No users have redeemed this reward yet.</h4>
                <p class="text-muted mb-0">Once users redeem this reward, their details will appear here.</p>
            </div>
        <?php endif; ?>

    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php 
$red_stmt->close();
$conn->close();
?>
