<?php

/*
 * -------------------------------------------------------
 * Page    : View Rewards
 *
 * Purpose : Displays all active rewards available for redemption. Shows the user's current points and eligibility for each reward.
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

$user_id = $_SESSION['user_id'];

/* Get user points */
$user_stmt = $conn->prepare("SELECT points FROM users WHERE user_id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
$user_points = $user['points'] ?? 0;
$user_stmt->close();

/* Get rewards */
$sort = $_GET['sort'] ?? 'lowest_points';

switch ($sort) {
    case 'highest_points':
        $order_by = "ORDER BY rewards.points_required DESC";
        break;
    case 'a_z':
        $order_by = "ORDER BY rewards.reward_name ASC";
        break;
    case 'z_a':
        $order_by = "ORDER BY rewards.reward_name DESC";
        break;
    case 'newest':
        $order_by = "ORDER BY rewards.reward_id DESC";
        break;
    case 'oldest':
        $order_by = "ORDER BY rewards.reward_id ASC";
        break;
    case 'lowest_points':
    default:
        $order_by = "ORDER BY rewards.points_required ASC";
        break;
}

$sql = "
SELECT rewards.*, companies.company_name
FROM rewards
JOIN companies
ON rewards.company_id = companies.company_id
WHERE rewards.reward_id NOT IN
(
    SELECT reward_id
    FROM redemptions
    WHERE user_id = ?
)
AND (rewards.status = 'available' OR rewards.status IS NULL)
$order_by
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rewards Marketplace</title>
    
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
            background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%);
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
            position: relative;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(99, 102, 241, 0.25);
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: -30%;
            right: -20%;
            width: 500px;
            height: 500px;
            background: rgba(255,255,255,0.15);
            border-radius: 50%;
        }
        
        .points-balance {
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255,255,255,0.3);
        }
        
        .reward-card {
            border: none;
            border-radius: 18px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            height: 100%;
            overflow: hidden;
        }
        
        .reward-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
        }

        .text-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;  
            overflow: hidden;
        }
        
        .reward-card .card-body {
            display: flex;
            flex-direction: column;
        }
        
        .points-required {
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--primary);
        }
        
        .redeem-btn {
            transition: all 0.3s ease;
        }
        
        .section-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--dark);
            position: relative;
            display: inline-block;
        }
        
        .section-title:after {
            content: '';
            position: absolute;
            bottom: -7px;
            left: 0;
            width: 55px;
            height: 3px;
            background: var(--primary);
            border-radius: 3px;
        }
        
        .empty-state {
            text-align: center;
            padding: 6rem 2rem;
        }
    </style>
</head>
<body>
    <?php include("../includes/user_navbar.php"); ?>

    <div class="container dashboard-container">
        
        <!-- HERO SECTION -->
        <div class="hero-section">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <p class="mb-2 opacity-90 fs-5">
                        <i class="bi bi-gift-fill me-2"></i>
                        REWARDS MARKETPLACE
                    </p>
                    <h1 class="display-5 fw-bold mb-3">Unlock Amazing Rewards</h1>
                    <p class="lead opacity-90 mb-0">
                        Redeem your hard-earned points for exclusive rewards and perks.
                    </p>
                </div>
                
                <!-- Points Balance -->
                <div class="col-lg-5 text-lg-end mt-4 mt-lg-0">
                    <div class="points-balance rounded-4 p-5 d-inline-block text-center">
                        <div class="d-flex align-items-center justify-content-center gap-3 mb-3">
                            <i class="bi bi-coin fs-1"></i>
                            <div>
                                <small class="opacity-75 d-block">YOUR BALANCE</small>
                                <h2 class="mb-0 display-5 fw-bold"><?php echo number_format($user_points); ?></h2>
                            </div>
                        </div>
                        <span class="badge bg-white text-dark px-4 py-2 fs-6">Points</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <form method="GET" id="sortForm">
                <div class="d-flex flex-column flex-md-row gap-3 align-items-stretch align-items-md-center">
                    <div class="input-group flex-grow-1">
                        <span class="input-group-text bg-white">
                            <i class="bi bi-search"></i>
                        </span>
                        <input
                            type="text"
                            name="search"
                            id="searchInput"
                            class="form-control form-control-lg border-0 shadow-sm"
                            placeholder="Search rewards..."
                            // Escape output to prevent Cross-Site Scripting (XSS) attacks.
                            value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                    </div>

                    <select name="sort" class="form-select form-select-lg border-0 shadow-sm" style="max-width: 200px;" onchange="document.getElementById('sortForm').submit();">
                        <option value="lowest_points" <?php echo $sort == 'lowest_points' ? 'selected' : ''; ?>>Lowest Points</option>
                        <option value="highest_points" <?php echo $sort == 'highest_points' ? 'selected' : ''; ?>>Highest Points</option>
                        <option value="a_z" <?php echo $sort == 'a_z' ? 'selected' : ''; ?>>A–Z</option>
                        <option value="z_a" <?php echo $sort == 'z_a' ? 'selected' : ''; ?>>Z–A</option>
                        <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="oldest" <?php echo $sort == 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                    </select>

                    <a href="view_rewards.php" class="btn btn-outline-secondary d-flex align-items-center justify-content-center px-4">
                        <i class="bi bi-arrow-clockwise me-2"></i> Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- REWARDS CATALOG -->
        <div class="mb-4">
            <h4 class="section-title">Available Rewards</h4>
            <p class="text-muted">Redeem points for exciting rewards from our partner companies</p>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <div class="row g-4">
                <?php while($reward = $result->fetch_assoc()): ?>
                    <div class="col-12 col-md-6 col-lg-4 reward-card-item">
                        <div class="reward-card card shadow-sm border-0 rounded-4 h-100">
                            <div class="card-body p-4 p-xl-4 d-flex flex-column">
                                <!-- Header -->
                                <div class="mb-3">
                                    <div class="text-muted small fw-medium">#<?php echo $reward['reward_id']; ?></div>
                                </div>

                                <!-- Title -->
                                <h5 class="card-title fw-bold text-dark mb-2"><?php echo htmlspecialchars($reward['reward_name']); ?></h5>
                                
                                <!-- Company -->
                                <div class="text-muted small flex-grow-1 mb-4"><i class="bi bi-building me-1"></i> <?php echo htmlspecialchars($reward['company_name']); ?></div>
                                
                                <!-- Meta Columns -->
                                <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-light rounded-3 text-center">
                                    <div class="flex-fill">
                                        <small class="text-muted d-block" style="font-size: 0.7rem;"><i class="bi bi-star-fill text-warning me-1"></i>Required Points</small>
                                        <strong class="text-dark fs-6"><?php echo number_format($reward['points_required']); ?></strong>
                                    </div>
                                    <div class="border-start border-end px-2 flex-fill mx-2">
                                        <small class="text-muted d-block" style="font-size: 0.7rem;"><i class="bi bi-person-fill text-primary me-1"></i>Your Points</small>
                                        <strong class="text-<?php echo ($user_points >= $reward['points_required']) ? 'success' : 'danger'; ?> fs-6"><?php echo number_format($user_points); ?></strong>
                                    </div>
                                    <div class="flex-fill">
                                        <small class="text-muted d-block" style="font-size: 0.7rem;"><i class="bi bi-bar-chart-fill text-info me-1"></i>Status</small>
                                        <div class="fw-bold" style="font-size: 0.75rem;">
                                            <?php if($user_points >= $reward['points_required']): ?>
                                                <span class="text-success">🟢 Redeemable</span>
                                            <?php else: ?>
                                                <span class="text-warning">🟠 Need <?php echo $reward['points_required'] - $user_points; ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Action -->
                                <?php if($user_points >= $reward['points_required']): ?>
                                    <button type="button" class="btn btn-success w-100 py-3 fw-semibold redeem-btn rounded-pill"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#redeemModal"
                                            data-reward-id="<?php echo $reward['reward_id']; ?>"
                                            // Escape output to prevent Cross-Site Scripting (XSS) attacks.
                                            data-reward-name="<?php echo htmlspecialchars($reward['reward_name'], ENT_QUOTES); ?>"
                                            data-reward-points="<?php echo number_format($reward['points_required']); ?>">
                                        Redeem
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-secondary w-100 py-3 fw-semibold rounded-pill" disabled>
                                        Need <?php echo $reward['points_required'] - $user_points; ?> More Points
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <!-- EMPTY STATE -->
            <div class="empty-state">
                <div class="mb-4">
                    <i class="bi bi-gift display-1 text-muted"></i>
                </div>
                <h4 class="text-dark">No rewards available yet</h4>
                <p class="text-muted mb-4">Complete more surveys to unlock exciting rewards!</p>
                <a href="view_surveys.php" class="btn btn-primary">
                    Browse Surveys
                </a>
            </div>
        <?php endif; ?>

    </div>

    <!-- Redeem Modal -->
    <div class="modal fade" id="redeemModal" tabindex="-1" aria-labelledby="redeemModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark" id="redeemModalLabel">Redeem Reward</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Are you sure you want to redeem this reward?</p>
                    <ul class="list-unstyled bg-light p-3 rounded-3 mb-3">
                        <li class="mb-2"><strong>Reward:</strong> <span id="modalRewardName"></span></li>
                        <li><strong>Points Required:</strong> <span id="modalRewardPoints"></span> pts</li>
                    </ul>
                    <div class="alert alert-warning mb-0 border-0">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> This action cannot be undone.
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="redeem_reward.php" id="redeemForm" class="m-0 p-0">
                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                        <input type="hidden" name="reward_id" id="modalRewardId" value="">
                        <button type="submit" class="btn btn-primary">Redeem Reward</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Card entrance animation
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.reward-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1)';
                    card.style.transitionDelay = (index * 70) + 'ms';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 100);
            });
        });

        function applySearch() {
            let value = document.getElementById("searchInput").value.toLowerCase();
            let cards = document.querySelectorAll(".reward-card-item");
            cards.forEach(function(card){
                let text = card.textContent.toLowerCase();
                if(text.includes(value)) {
                    card.style.display = "";
                } else {
                    card.style.display = "none";
                }
            });
        }

        document.getElementById("searchInput").addEventListener("keyup", applySearch);
        
        // Apply search on load to preserve filter visually
        applySearch();

        // Populate modal data
        const redeemModal = document.getElementById('redeemModal');
        if (redeemModal) {
            redeemModal.addEventListener('show.bs.modal', event => {
                const button = event.relatedTarget;
                const rewardId = button.getAttribute('data-reward-id');
                const rewardName = button.getAttribute('data-reward-name');
                const rewardPoints = button.getAttribute('data-reward-points');

                redeemModal.querySelector('#modalRewardId').value = rewardId;
                redeemModal.querySelector('#modalRewardName').textContent = rewardName;
                redeemModal.querySelector('#modalRewardPoints').textContent = rewardPoints;
            });
        }
    </script>
    <?php include('../includes/footer.php'); ?>
</body>
</html>