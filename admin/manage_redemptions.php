<?php

/*
 * -------------------------------------------------------
 * Page    : Manage Redemptions
 *
 * Purpose : Allows the administrator to view all reward redemptions made by users across the entire platform.
 * -------------------------------------------------------
 */

// Initialize the session to track user authentication state.
session_start();
// Include the database connection configuration to interact with the database.
include("../config/db.php");

// Verify that the admin is logged in before granting access.
if (!isset($_SESSION['admin_id'])) {
    // Redirect the user to the appropriate page after the action completes.
    header("Location: login.php");
    exit();
}

$query = "
SELECT
    redemptions.redemption_id,
    users.name,
    rewards.reward_name,
    redemptions.code,
    redemptions.status,
    redemptions.redeemed_at
FROM redemptions
JOIN users
ON redemptions.user_id = users.user_id
JOIN rewards
ON redemptions.reward_id = rewards.reward_id
ORDER BY redemptions.redemption_id DESC
";

$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Redemptions</title>
    
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
        
        .status-unused {
            background-color: #10b981;
            color: white;
        }
        
        .status-used {
            background-color: #64748b;
            color: white;
        }
        
        .table th {
            font-weight: 500;
            color: #64748b;
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
                        <i class="bi bi-ticket-perforated me-2"></i>
                        REDEMPTION DETAILS
                    </p>
                    <h1 class="display-5 fw-bold mb-3">View Redemptions</h1>
                    <p class="lead opacity-90 mb-0">
                        Track and monitor all reward redemptions across the platform.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                    <div class="bg-white bg-opacity-10 rounded-4 px-5 py-4 d-inline-block text-center">
                        <div class="fs-2 fw-bold"><?php echo mysqli_num_rows($result); ?></div>
                        <small class="opacity-75">Total Redemptions</small>
                    </div>
                </div>
            </div>
        </div>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <!-- SEARCH -->
            <div class="mb-4">
                <div class="input-group">
                    <span class="input-group-text bg-white">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" 
                           id="searchInput" 
                           class="form-control form-control-lg border-0 shadow-sm" 
                           placeholder="Search by user, reward, or coupon code...">
                </div>
            </div>

            <!-- REDEMPTIONS TABLE -->
            <div class="card shadow">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="redemptionsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Reward</th>
                                    <th>Coupon Code</th>
                                    <th>Redeemed At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                mysqli_data_seek($result, 0);
                                // Iterate through each row from the MySQLi result set.
                                while($row = mysqli_fetch_assoc($result)): 
                                ?>
                                    <tr class="redemption-row">
                                        <td class="text-muted">#<?php echo $row['redemption_id']; ?></td>
                                        <td class="fw-medium"><?php echo htmlspecialchars($row['name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['reward_name']); ?></td>
                                        <td>
                                            <code class="bg-light px-3 py-1 rounded"><?php echo htmlspecialchars($row['code']); ?></code>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo date('M j, Y • g:i A', strtotime($row['redeemed_at'])); ?>
                                            </small>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- EMPTY STATE -->
            <div class="empty-state">
                <div class="mb-4">
                    <i class="bi bi-ticket-perforated display-1 text-muted"></i>
                </div>
                <h4 class="text-dark">No redemptions yet</h4>
                <p class="text-muted">Reward redemptions will appear here once users start claiming rewards.</p>
            </div>
        <?php endif; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Enhanced search
        document.getElementById("searchInput").addEventListener("keyup", function() {
            let value = this.value.toLowerCase();
            let rows = document.querySelectorAll("#redemptionsTable tbody tr");
            
            rows.forEach(function(row) {
                let rowText = row.textContent.toLowerCase();
                row.style.display = rowText.includes(value) ? "" : "none";
            });
        });
        
        // Row animation
        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('.redemption-row');
            rows.forEach((row, index) => {
                row.style.opacity = '0';
                row.style.transform = 'translateY(15px)';
                setTimeout(() => {
                    row.style.transition = 'all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1)';
                    row.style.transitionDelay = (index * 40) + 'ms';
                    row.style.opacity = '1';
                    row.style.transform = 'translateY(0)';
                }, 80);
            });
        });
    </script>
    <?php include('../includes/footer.php'); ?>
</body>
</html>