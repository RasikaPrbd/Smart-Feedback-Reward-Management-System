<?php

/*
 * -------------------------------------------------------
 * Page    : Manage Rewards
 *
 * Purpose : Provides the administrator with a read-only overview of all rewards across all companies.
 * -------------------------------------------------------
 */

// Initialize the session to track user authentication state.
session_start();
// Include the database connection configuration to interact with the database.
include("../config/db.php");
// Include the CSRF helper to generate and validate security tokens for forms.
include("../includes/csrf_helper.php");

// Verify that the admin is logged in before granting access.
if (!isset($_SESSION['admin_id'])) {
    // Redirect the user to the appropriate page after the action completes.
    header("Location: login.php");
    exit();
}

$query = "
SELECT
    rewards.reward_id,
    rewards.reward_name,
    rewards.points_required,
    rewards.status,
    companies.company_name,
    COUNT(redemptions.redemption_id) AS redeem_count
FROM rewards
JOIN companies ON rewards.company_id = companies.company_id
LEFT JOIN redemptions ON rewards.reward_id = redemptions.reward_id
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
            background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
        }
        
        .dashboard-container {
            padding-top: 2rem;
            padding-bottom: 5rem;
        }
        
        .hero-section {
            background: linear-gradient(135deg, #1e2937 0%, #334155 100%);
            color: white;
            border-radius: 20px;
            padding: 3rem 2.5rem;
            margin-bottom: 3rem;
            box-shadow: 0 15px 35px rgba(30, 41, 55, 0.3);
        }
        
        .reward-card {
            border: none;
            border-radius: 18px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100%;
        }
        
        .reward-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.15) !important;
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
    <?php include("../includes/admin_navbar.php"); ?>

    <div class="container dashboard-container">
        
        <!-- HERO SECTION -->
        <div class="hero-section">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <p class="mb-2 opacity-90 fs-5">
                        <i class="bi bi-gift-fill me-2"></i>
                        REWARDS ADMINISTRATION
                    </p>
                    <h1 class="display-5 fw-bold mb-3">Manage Rewards</h1>
                    <p class="lead opacity-90 mb-0">
                        Oversee all rewards offered by companies on the platform.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                    <div class="bg-white bg-opacity-10 rounded-4 px-5 py-4 d-inline-block text-center">
                        <div class="fs-2 fw-bold"><?php echo mysqli_num_rows($result); ?></div>
                        <small class="opacity-75">Total Rewards</small>
                    </div>
                </div>
            </div>
        </div>

        <?php if (isset($_SESSION['success_msg'])): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 rounded-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?php 
                    // Escape output to prevent Cross-Site Scripting (XSS) attacks.
                    echo htmlspecialchars($_SESSION['success_msg']); 
                    unset($_SESSION['success_msg']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <!-- SEARCH AND FILTER -->
            <div class="mb-4 d-flex flex-column flex-md-row gap-3">
                <div class="input-group flex-grow-1">
                    <span class="input-group-text bg-white">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" 
                           id="searchInput" 
                           class="form-control form-control-lg border-0 shadow-sm" 
                           placeholder="Search by reward or company name...">
                </div>
                <select id="statusFilter" class="form-select form-select-lg border-0 shadow-sm w-auto" style="min-width: 150px;">
                    <option value="all">All Statuses</option>
                    <option value="available">Available</option>
                    <option value="archived">Archived</option>
                </select>
            </div>

            <!-- REWARDS GRID -->
            <div class="row g-4" id="rewardsGrid">
                <?php 
                mysqli_data_seek($result, 0);
                // Iterate through each row from the MySQLi result set.
                while($row = mysqli_fetch_assoc($result)): 
                ?>
                    <div class="col-lg-4 col-md-6 reward-item">
                        <div class="reward-card card shadow-sm border-0 h-100 rounded-4">
                            <div class="card-body d-flex flex-column p-4">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2 fw-medium shadow-sm">
                                        Redeemed <?php echo $row['redeem_count']; ?> Times
                                    </span>
                                    <div class="text-muted small fw-medium">#<?php echo $row['reward_id']; ?></div>
                                </div>
                                
                                <div class="d-flex align-items-center mb-4">
                                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-2 me-3 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                        <i class="bi bi-gift fs-4"></i>
                                    </div>
                                    <h5 class="card-title fw-bold mb-0 text-dark"><?php echo htmlspecialchars($row['reward_name']); ?></h5>
                                </div>
                                
                                <div class="row text-center g-2 mb-4 mt-auto">
                                    <div class="col-4">
                                        <div class="p-2 rounded-3 bg-light h-100 d-flex flex-column justify-content-center">
                                            <small class="text-muted d-block mb-1" style="font-size: 0.75rem;">⭐ Points</small>
                                            <span class="fw-bold text-dark"><?php echo number_format($row['points_required']); ?></span>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="p-2 rounded-3 bg-light h-100 d-flex flex-column justify-content-center">
                                            <small class="text-muted d-block mb-1" style="font-size: 0.75rem;">🏢 Company</small>
                                            <span class="fw-bold text-dark text-truncate px-1" title="<?php echo htmlspecialchars($row['company_name']); ?>"><?php echo htmlspecialchars($row['company_name']); ?></span>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="p-2 rounded-3 bg-light h-100 d-flex flex-column justify-content-center">
                                            <small class="text-muted d-block mb-1" style="font-size: 0.75rem;">📊 Status</small>
                                            <?php 
                                                $status = $row['status'] ?? 'available';
                                                if($status === 'available'): 
                                            ?>
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill">🟢 Available</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill">⚪ Archived</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-auto d-flex flex-wrap gap-2">
                                    <?php 
                                        $status = $row['status'] ?? 'available';
                                        if($status === 'available'): 
                                    ?>
                                        <button type="button" 
                                           class="btn btn-outline-warning w-100 fw-medium"
                                           data-bs-toggle="modal" 
                                           data-bs-target="#archiveModal"
                                           data-reward-id="<?php echo $row['reward_id']; ?>">
                                            <i class="bi bi-archive me-2"></i> Archive
                                        </button>
                                    <?php else: ?>
                                        <button type="button" 
                                           class="btn btn-outline-success w-100 fw-medium"
                                           data-bs-toggle="modal" 
                                           data-bs-target="#restoreModal"
                                           data-reward-id="<?php echo $row['reward_id']; ?>">
                                            <i class="bi bi-arrow-counterclockwise me-2"></i> Restore
                                        </button>
                                    <?php endif; ?>
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
                <h4 class="text-dark">No rewards found</h4>
                <p class="text-muted">Rewards created by companies will appear here.</p>
            </div>
        <?php endif; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Archive Modal -->
    <div class="modal fade" id="archiveModal" tabindex="-1" aria-labelledby="archiveModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark" id="archiveModalLabel">Archive Reward</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <p class="text-muted mb-3">This reward will be archived and hidden from users.</p>
                    <p class="text-muted mb-0">Existing redemptions will not be affected. You can restore this reward later.</p>
                </div>
                <div class="modal-footer border-top-0 pt-0 flex-wrap gap-2">
                    <button type="button" class="btn btn-light fw-medium" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="toggle_reward_status.php" id="archiveForm" class="m-0 p-0">
                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                        <input type="hidden" name="action" value="archive">
                        <input type="hidden" name="id" id="archiveRewardId" value="">
                        <button type="submit" class="btn btn-warning fw-semibold px-4 text-dark">
                            Yes, Archive
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Restore Modal -->
    <div class="modal fade" id="restoreModal" tabindex="-1" aria-labelledby="restoreModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark" id="restoreModalLabel">Restore Reward</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <p class="text-muted mb-0">This reward will become available for users to redeem again. Do you want to continue?</p>
                </div>
                <div class="modal-footer border-top-0 pt-0 flex-wrap gap-2">
                    <button type="button" class="btn btn-light fw-medium" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="toggle_reward_status.php" id="restoreForm" class="m-0 p-0">
                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                        <input type="hidden" name="action" value="restore">
                        <input type="hidden" name="id" id="restoreRewardId" value="">
                        <button type="submit" class="btn btn-success fw-semibold px-4 text-white">
                            Yes, Restore
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Frontend search and filter
        function filterRewards() {
            let searchValue = document.getElementById("searchInput").value.toLowerCase();
            let statusValue = document.getElementById("statusFilter").value.toLowerCase();
            let items = document.querySelectorAll('.reward-item');
            
            items.forEach(item => {
                let text = item.textContent.toLowerCase();
                let matchesSearch = text.includes(searchValue);
                
                let matchesStatus = true;
                if(statusValue === 'available') {
                    matchesStatus = text.includes('🟢 available');
                } else if(statusValue === 'archived') {
                    matchesStatus = text.includes('⚪ archived');
                }
                
                item.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
            });
        }

        document.getElementById("searchInput").addEventListener("keyup", filterRewards);
        document.getElementById("statusFilter").addEventListener("change", filterRewards);
        
        // Modal logic
        const archiveModal = document.getElementById('archiveModal');
        if (archiveModal) {
            archiveModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const rewardId = button.getAttribute('data-reward-id');
                const inputId = document.getElementById('archiveRewardId');
                inputId.value = rewardId;
            });
        }

        const restoreModal = document.getElementById('restoreModal');
        if (restoreModal) {
            restoreModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const rewardId = button.getAttribute('data-reward-id');
                const inputId = document.getElementById('restoreRewardId');
                inputId.value = rewardId;
            });
        }
        
        // Card animation
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
    </script>
    <?php include('../includes/footer.php'); ?>
</body>
</html>