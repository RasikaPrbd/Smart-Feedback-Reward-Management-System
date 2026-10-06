<?php
/*
 * -------------------------------------------------------
 * Page    : My Rewards
 *
 * Purpose : Displays the redemption history of the logged-in user, including voucher codes and redemption dates.
 * -------------------------------------------------------
 */

// Initialize the session to track user authentication state.
session_start();
// Include the database connection configuration to interact with the database.
include("../config/db.php");

// Verify that the user is logged in before granting access.
if (!isset($_SESSION['user_id'])) {
    // Redirect the user to the appropriate page after the action completes.
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$sort = $_GET['sort'] ?? 'newest';

switch ($sort) {
    case 'oldest':
        $order_by = "ORDER BY redemptions.redeemed_at ASC";
        break;
    case 'highest_points':
        $order_by = "ORDER BY rewards.points_required DESC";
        break;
    case 'lowest_points':
        $order_by = "ORDER BY rewards.points_required ASC";
        break;
    case 'a_z':
        $order_by = "ORDER BY rewards.reward_name ASC";
        break;
    case 'z_a':
        $order_by = "ORDER BY rewards.reward_name DESC";
        break;
    case 'newest':
    default:
        $order_by = "ORDER BY redemptions.redeemed_at DESC";
        break;
}

$query = "
    SELECT 
        redemptions.redemption_id,
        rewards.reward_id,
        rewards.reward_name,
        rewards.points_required,
        companies.company_name,
        redemptions.code,
        redemptions.status,
        redemptions.redeemed_at
    FROM redemptions
    JOIN rewards ON redemptions.reward_id = rewards.reward_id
    JOIN companies ON rewards.company_id = companies.company_id
    WHERE redemptions.user_id = ?
    $order_by";

// Prepare the SQL statement to securely handle user input and prevent SQL injection.
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$total_redeemed = $result->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Rewards</title>
    
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
                <div class="col-lg-12">
                    <p class="mb-2 opacity-90 fs-5">
                        <i class="bi bi-award-fill me-2"></i>
                        MY REWARDS
                    </p>
                    <h1 class="display-5 fw-bold mb-3 d-flex align-items-center flex-wrap gap-3">
                        Reward History 
                        <span class="badge bg-white text-primary fs-5 rounded-pill px-4 py-2 shadow-sm">My Rewards (<?php echo $total_redeemed; ?>)</span>
                    </h1>
                    <p class="lead opacity-90 mb-0">
                        View and manage all the rewards you have successfully redeemed.
                    </p>
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
                            placeholder="Search by reward or company name..."
                            // Escape output to prevent Cross-Site Scripting (XSS) attacks.
                            value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                    </div>

                    <select name="sort" class="form-select form-select-lg border-0 shadow-sm" style="max-width: 200px;" onchange="document.getElementById('sortForm').submit();">
                        <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="oldest" <?php echo $sort == 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                        <option value="highest_points" <?php echo $sort == 'highest_points' ? 'selected' : ''; ?>>Highest Points</option>
                        <option value="lowest_points" <?php echo $sort == 'lowest_points' ? 'selected' : ''; ?>>Lowest Points</option>
                        <option value="a_z" <?php echo $sort == 'a_z' ? 'selected' : ''; ?>>A–Z</option>
                        <option value="z_a" <?php echo $sort == 'z_a' ? 'selected' : ''; ?>>Z–A</option>
                    </select>

                    <a href="my_rewards.php" class="btn btn-outline-secondary d-flex align-items-center justify-content-center px-4">
                        <i class="bi bi-arrow-clockwise me-2"></i> Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- REWARDS HISTORY -->
        <div class="mb-4">
            <h4 class="section-title">Redeemed Rewards</h4>
            <p class="text-muted">Your reward codes are available below.</p>
        </div>

        <?php if ($total_redeemed > 0): ?>
            <div class="row g-4" id="rewardsGrid">
                <?php while($reward = $result->fetch_assoc()): ?>
                    <div class="col-12 col-md-6 col-lg-4 reward-card-item">
                        <div class="reward-card card shadow-sm border-0 rounded-4 h-100">
                            <div class="card-body p-4 p-xl-4 d-flex flex-column">
                                <!-- Header -->
                                <div class="mb-3 d-flex justify-content-between align-items-start">
                                    <div class="text-muted small fw-medium">#<?php echo $reward['reward_id']; ?></div>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill px-3 py-2">
                                        <i class="bi bi-check-circle-fill me-1"></i> Redeemed
                                    </span>
                                </div>

                                <!-- Title -->
                                <h5 class="card-title fw-bold text-dark mb-2"><?php echo htmlspecialchars($reward['reward_name']); ?></h5>
                                
                                <!-- Company -->
                                <div class="text-muted small flex-grow-1 mb-4"><i class="bi bi-building me-1"></i> <?php echo htmlspecialchars($reward['company_name']); ?></div>
                                
                                <!-- Meta Columns -->
                                <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-light rounded-3 text-center">
                                    <div class="flex-fill">
                                        <small class="text-muted d-block" style="font-size: 0.7rem;"><i class="bi bi-star-fill text-warning me-1"></i>Points Used</small>
                                        <strong class="text-dark fs-6"><?php echo number_format($reward['points_required']); ?></strong>
                                    </div>
                                    <div class="border-start px-2 flex-fill mx-2">
                                        <small class="text-muted d-block" style="font-size: 0.7rem;"><i class="bi bi-calendar-check-fill text-primary me-1"></i>Redeemed Date</small>
                                        <strong class="text-dark" style="font-size: 0.85rem;"><?php echo date('M d, Y', strtotime($reward['redeemed_at'])); ?></strong>
                                    </div>
                                </div>
                                
                                <!-- Code Block instead of Action Button -->
                                <div class="bg-success bg-opacity-10 text-success text-center py-3 rounded-pill fw-bold border border-success-subtle d-flex align-items-center justify-content-center gap-2">
                                    <i class="bi bi-check-circle-fill"></i> Successfully Redeemed
                                </div>
                                
                                <div class="mt-3">
                                    <small class="text-muted d-block mb-1 text-center">Your Code:</small>
                                    <div class="d-flex flex-wrap justify-content-between align-items-center bg-light border py-2 px-3 rounded-3 gap-2">
                                        <div class="font-monospace fw-bold text-dark fs-5 user-select-all flex-grow-1 text-center" id="reward-code-<?php echo $reward['redemption_id']; ?>">
                                            <?php echo htmlspecialchars($reward['code']); ?>
                                        </div>
                                        <i class="bi bi-copy text-muted fs-5 ms-2 copy-icon" 
                                           style="cursor: pointer;" 
                                           data-bs-toggle="tooltip" 
                                           data-bs-placement="top" 
                                           title="Copy Reward Code"
                                           onclick="copyRewardCode(this, '<?php echo $reward['redemption_id']; ?>')"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <!-- EMPTY STATE -->
            <div class="empty-state">
                <div class="mb-4">
                    <i class="bi bi-award display-1 text-muted"></i>
                </div>
                <h4 class="text-dark">No Rewards Redeemed Yet</h4>
                <p class="text-muted mb-4">You haven't claimed any rewards yet. Browse the marketplace to find exciting offers!</p>
                <a href="view_rewards.php" class="btn btn-primary px-4 py-2">
                    Browse Rewards
                </a>
            </div>
        <?php endif; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Card entrance animation and tooltips
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Bootstrap tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });

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

        function copyRewardCode(iconElement, id) {
            const codeElement = document.getElementById('reward-code-' + id);
            const code = codeElement.innerText.trim();
            
            navigator.clipboard.writeText(code).then(() => {
                // Change icon
                iconElement.classList.remove('bi-copy');
                iconElement.classList.add('bi-check2', 'text-success');
                iconElement.classList.remove('text-muted');
                
                // Update tooltip
                let tooltip = bootstrap.Tooltip.getInstance(iconElement);
                iconElement.setAttribute('data-bs-original-title', 'Copied!');
                tooltip.show();
                
                // Reset after 2 seconds
                setTimeout(() => {
                    iconElement.classList.remove('bi-check2', 'text-success');
                    iconElement.classList.add('bi-copy', 'text-muted');
                    iconElement.setAttribute('data-bs-original-title', 'Copy Reward Code');
                    tooltip.hide();
                }, 2000);
            });
        }

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
    </script>
    <?php include('../includes/footer.php'); ?>
</body>
</html>