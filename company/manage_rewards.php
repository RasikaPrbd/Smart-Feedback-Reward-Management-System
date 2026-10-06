<?php

/*
 * -------------------------------------------------------
 * Page    : Manage Rewards
 *
 * Purpose : Displays all rewards created by the logged-in company, including redemption counts and status management.
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

$query = "
SELECT rewards.*, COUNT(redemptions.redemption_id) AS redemption_count
FROM rewards
LEFT JOIN redemptions ON rewards.reward_id = redemptions.reward_id
WHERE rewards.company_id = '$company_id'
GROUP BY rewards.reward_id
ORDER BY rewards.reward_id DESC
";

$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Rewards</title>
    
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
        
        .reward-card {
            border: none;
            border-radius: 18px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100%;
        }
        
        .reward-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12) !important;
        }
        
        .points-badge {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: white;
            font-weight: 700;
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
    <?php include("../includes/company_navbar.php"); ?>

    <div class="container dashboard-container">
        
        <!-- HERO SECTION -->
        <div class="hero-section">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <p class="mb-2 opacity-90 fs-5">
                        <i class="bi bi-gift-fill me-2"></i>
                        REWARDS MANAGEMENT
                    </p>
                    <h1 class="display-5 fw-bold mb-3">Manage Rewards</h1>
                    <p class="lead opacity-90 mb-0">
                        Create and manage incentives to drive user engagement.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                    <a href="create_reward.php" class="btn btn-light px-5 py-3 fw-semibold">
                        <i class="bi bi-plus-circle me-2"></i>
                        Create New Reward
                    </a>
                </div>
            </div>
        </div>

        <?php if (isset($_SESSION['success_msg'])): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert" id="successAlert">
                <strong class="d-flex align-items-center">
                    <?php echo $_SESSION['success_msg']; ?>
                </strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success_msg']); ?>
        <?php endif; ?>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <!-- STATS -->
            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="card shadow-sm text-center p-4">
                        <div class="text-primary mb-2">
                            <i class="bi bi-gift fs-2"></i>
                        </div>
                        <h3 class="fw-bold"><?php echo mysqli_num_rows($result); ?></h3>
                        <p class="text-muted mb-0">Total Rewards</p>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <div class="input-group">
                    <span class="input-group-text bg-white">
                        <i class="bi bi-search"></i>
                    </span>

                    <input
                        type="text"
                        id="searchInput"
                        class="form-control form-control-lg border-0 shadow-sm"
                        placeholder="Search rewards...">
                </div>
            </div>

            <!-- REWARDS GRID -->
            <div class="mb-4">
                <h4 class="section-title">Your Rewards <span class="badge bg-primary ms-2 fs-6 rounded-pill shadow-sm"><?php echo mysqli_num_rows($result); ?></span></h4>
            </div>

            <div class="row g-4" id="rewardsGrid">
                <?php 
                mysqli_data_seek($result, 0);
                // Iterate through each row from the MySQLi result set.
                while($row = mysqli_fetch_assoc($result)): 
                ?>
                    <div class="col-lg-4 col-md-6 reward-item">
                        <div class="reward-card card shadow-sm border border-light h-100 rounded-4">
                            <div class="card-body d-flex flex-column p-4">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="d-flex flex-wrap gap-2">
                                        <span class="badge points-badge rounded-pill px-3 py-2 fw-medium shadow-sm">
                                            <i class="bi bi-star-fill text-warning me-1"></i> <?php echo number_format($row['points_required']); ?> pts
                                        </span>
                                        <?php if (isset($row['status']) && strtolower($row['status']) === 'archived'): ?>
                                            <span class="badge bg-warning text-dark rounded-pill px-3 py-2 fw-medium shadow-sm">Archived by Admin</span>
                                        <?php else: ?>
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill px-3 py-2 fw-medium">Available</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-muted small fw-medium">#<?php echo $row['reward_id']; ?></div>
                                </div>
                                
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-2 me-3 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                        <i class="bi bi-gift fs-4"></i>
                                    </div>
                                    <h5 class="card-title fw-bold mb-0 text-dark"><?php echo htmlspecialchars($row['reward_name']); ?></h5>
                                </div>
                                
                                <?php if (!empty($row['description'])): ?>
                                    <p class="text-muted flex-grow-1 small lh-base" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                        <?php echo htmlspecialchars($row['description']); ?>
                                    </p>
                                <?php endif; ?>
                                
                                <div class="mb-3 mt-auto">
                                    <span class="badge bg-light text-secondary border px-2 py-1 fs-7 fw-medium">
                                        <i class="bi bi-people-fill me-1"></i> Redeemed <?php echo (int)$row['redemption_count']; ?> Times
                                    </span>
                                    
                                    <?php if (isset($row['status']) && strtolower($row['status']) === 'archived'): ?>
                                        <div class="alert alert-warning py-2 px-3 mt-3 mb-0 small rounded-3">
                                            This reward has been archived by the administrator.<br>
                                            Users cannot redeem this reward until it is restored.
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Actions -->
                                <div class="mt-auto pt-3 d-flex flex-wrap gap-2">
                                    <?php if (isset($row['status']) && strtolower($row['status']) === 'archived'): ?>
                                        <button class="btn btn-outline-primary btn-sm flex-fill fw-medium disabled" disabled>
                                            <i class="bi bi-pencil me-1"></i> Edit
                                        </button>
                                    <?php else: ?>
                                        <a href="edit_reward.php?id=<?php echo $row['reward_id']; ?>" 
                                           class="btn btn-outline-primary btn-sm flex-fill fw-medium">
                                            <i class="bi bi-pencil me-1"></i> Edit
                                        </a>
                                    <?php endif; ?>
                                    <button type="button" 
                                       class="btn btn-outline-danger btn-sm flex-fill fw-medium"
                                       data-bs-toggle="modal" 
                                       data-bs-target="#deleteRewardModal"
                                       data-reward-id="<?php echo $row['reward_id']; ?>"
                                       // Escape output to prevent Cross-Site Scripting (XSS) attacks.
                                       data-reward-name="<?php echo htmlspecialchars($row['reward_name'], ENT_QUOTES); ?>">
                                        <i class="bi bi-trash me-1"></i> Delete
                                    </button>
                                    <a href="view_redemptions.php?reward_id=<?php echo $row['reward_id']; ?>"
                                       class="btn btn-outline-secondary btn-sm w-100 fw-medium">
                                        <i class="bi bi-people me-1"></i> View Redemptions
                                    </a>
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
                    <i class="bi bi-gift display-1 text-muted"></i>
                </div>
                <h4 class="text-dark">No rewards created yet</h4>
                <p class="text-muted mb-4">Start by creating your first reward to incentivize survey participation.</p>
                <a href="create_reward.php" class="btn btn-primary px-5 py-3">
                    <i class="bi bi-plus-circle me-2"></i>
                    Create Your First Reward
                </a>
            </div>
        <?php endif; ?>

    </div>

    <!-- Delete Reward Modal -->
    <div class="modal fade" id="deleteRewardModal" tabindex="-1" aria-labelledby="deleteRewardModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark" id="deleteRewardModalLabel">Delete Reward</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pb-0">
                    <p class="text-dark mb-3">Are you sure you want to permanently delete</p>
                    <p class="fw-bold fs-5 text-dark text-center py-2 bg-light rounded" id="deleteRewardName">"Reward Name"</p>
                    <p class="text-muted mb-4">This action will permanently remove this reward.</p>
                    <div class="alert alert-danger d-flex align-items-center border-0 bg-danger bg-opacity-10 text-danger" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <div>This action cannot be undone.</div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 flex-wrap gap-2">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="delete_reward.php" id="deleteRewardForm" class="m-0 p-0">
                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                        <input type="hidden" name="id" id="deleteRewardId" value="">
                        <button type="submit" class="btn btn-danger px-4">Delete Reward</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const deleteRewardModal = document.getElementById('deleteRewardModal');
            if (deleteRewardModal) {
                deleteRewardModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const rewardId = button.getAttribute('data-reward-id');
                    const rewardName = button.getAttribute('data-reward-name');
                    
                    const rewardNameDisplay = deleteRewardModal.querySelector('#deleteRewardName');
                    const inputId = document.getElementById('deleteRewardId');
                    
                    inputId.value = rewardId;
                    rewardNameDisplay.textContent = '"' + rewardName + '"';
                });
            }
        });

        // Simple frontend search/filter
        document.getElementById("searchInput").addEventListener("keyup", function () {

            let value = this.value.toLowerCase();

            document.querySelectorAll(".reward-item").forEach(function(card){

                let rewardName = card.querySelector(".card-title").textContent.toLowerCase();

                if(rewardName.includes(value))
                {
                    card.style.display = "";
                }
                else
                {
                    card.style.display = "none";
                }

            });

        });
        
        // Add subtle animation to cards
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.reward-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1)';
                    card.style.transitionDelay = (index * 60) + 'ms';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 100);
            });
            
            // Auto-hide alert after 4 seconds
            const successAlert = document.getElementById('successAlert');
            if (successAlert) {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(successAlert);
                    bsAlert.close();
                }, 4000);
            }
        });
    </script>
    <?php include('../includes/footer.php'); ?>
</body>
</html>