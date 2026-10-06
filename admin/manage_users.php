<?php

/*
 * -------------------------------------------------------
 * Page    : Manage Users
 *
 * Purpose : Allows the administrator to view and manage all registered user accounts, including activation and deactivation.
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

$result = mysqli_query(
    $conn,
    "SELECT * FROM users WHERE is_deleted = 0"
);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    
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
        
        .table th {
            font-weight: 500;
            color: #64748b;
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
                        <i class="bi bi-people-fill me-2"></i>
                        USER ADMINISTRATION
                    </p>
                    <h1 class="display-5 fw-bold mb-3">Manage Users</h1>
                    <p class="lead opacity-90 mb-0">
                        Monitor, manage and deactivate user accounts while preserving survey history.                    </p>
                </div>
                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                    <div class="bg-white bg-opacity-10 rounded-4 px-5 py-4 d-inline-block text-center">
                        <div class="fs-2 fw-bold"><?php echo mysqli_num_rows($result); ?></div>
                        <small class="opacity-75">Total Users</small>
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
            <!-- SEARCH -->
            <div class="mb-4">
                <div class="input-group">
                    <span class="input-group-text bg-white">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" 
                           id="searchInput" 
                           class="form-control form-control-lg border-0 shadow-sm" 
                           placeholder="Search by name or email...">
                </div>
            </div>

            <!-- USERS TABLE -->
            <div class="card shadow">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="usersTable">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th class="text-center">Points</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                mysqli_data_seek($result, 0);
                                // Iterate through each row from the MySQLi result set.
                                while($row = mysqli_fetch_assoc($result)): 
                                ?>
                                    <tr class="user-row">
                                        <td class="text-muted">#<?php echo $row['user_id']; ?></td>
                                        <td class="fw-medium"><?php echo htmlspecialchars($row['name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td>
                                            <?php if($row['status'] === 'active' || empty($row['status'])): ?>
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill px-3">🟢 Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle rounded-pill px-3">🔴 Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge points-badge px-3 py-2">
                                                <?php echo number_format($row['points']); ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <?php if($row['status'] === 'active' || empty($row['status'])): ?>
                                                <button type="button" 
                                                   class="btn btn-outline-warning btn-sm fw-medium"
                                                   data-bs-toggle="modal" 
                                                   data-bs-target="#deactivateModal"
                                                   data-user-id="<?php echo $row['user_id']; ?>">
                                                    <i class="bi bi-person-x me-1"></i> Deactivate
                                                </button>
                                            <?php else: ?>
                                                <button type="button" 
                                                   class="btn btn-outline-success btn-sm fw-medium"
                                                   data-bs-toggle="modal" 
                                                   data-bs-target="#activateModal"
                                                   data-user-id="<?php echo $row['user_id']; ?>">
                                                    <i class="bi bi-person-check me-1"></i> Activate
                                                </button>
                                            <?php endif; ?>
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
                    <i class="bi bi-people display-1 text-muted"></i>
                </div>
                <h4 class="text-dark">No users found</h4>
                <p class="text-muted">Registered users will appear here.</p>
            </div>
        <?php endif; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Deactivate Modal -->
    <div class="modal fade" id="deactivateModal" tabindex="-1" aria-labelledby="deactivateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark" id="deactivateModalLabel">Deactivate User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <p class="text-muted mb-3">This user account will no longer be able to log in.</p>
                    <p class="text-muted mb-3">Survey responses, reward history and account information will remain in the system.</p>
                    <p class="text-muted mb-0">This action can be reversed at any time.</p>
                </div>
                <div class="modal-footer border-top-0 pt-0 flex-wrap gap-2">
                    <button type="button" class="btn btn-light fw-medium" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="toggle_user_status.php" id="deactivateForm" class="m-0 p-0">
                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                        <input type="hidden" name="action" value="deactivate">
                        <input type="hidden" name="id" id="deactivateUserId" value="">
                        <button type="submit" class="btn btn-warning fw-semibold px-4 text-dark">
                            Yes, Deactivate
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Activate Modal -->
    <div class="modal fade" id="activateModal" tabindex="-1" aria-labelledby="activateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark" id="activateModalLabel">Activate User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <p class="text-muted mb-3">This user account will be able to log in again.</p>
                    <p class="text-muted mb-0">Do you want to continue?</p>
                </div>
                <div class="modal-footer border-top-0 pt-0 flex-wrap gap-2">
                    <button type="button" class="btn btn-light fw-medium" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="toggle_user_status.php" id="activateForm" class="m-0 p-0">
                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                        <input type="hidden" name="action" value="activate">
                        <input type="hidden" name="id" id="activateUserId" value="">
                        <button type="submit" class="btn btn-success fw-semibold px-4 text-white">
                            Yes, Activate
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Enhanced search functionality
        document.getElementById("searchInput").addEventListener("keyup", function() {
            let value = this.value.toLowerCase();
            let rows = document.querySelectorAll("#usersTable tbody tr");
            
            rows.forEach(function(row) {
                let rowText = row.textContent.toLowerCase();
                row.style.display = rowText.includes(value) ? "" : "none";
            });
        });
        
        // Row entrance animation
        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('.user-row');
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

        // Modal integration
        const deactivateModal = document.getElementById('deactivateModal');
        if (deactivateModal) {
            deactivateModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const userId = button.getAttribute('data-user-id');
                const inputId = document.getElementById('deactivateUserId');
                inputId.value = userId;
            });
        }

        const activateModal = document.getElementById('activateModal');
        if (activateModal) {
            activateModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const userId = button.getAttribute('data-user-id');
                const inputId = document.getElementById('activateUserId');
                inputId.value = userId;
            });
        }
    </script>
    <?php include('../includes/footer.php'); ?>
</body>
</html>