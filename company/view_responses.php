<?php

/*
 * -------------------------------------------------------
 * Page    : View Responses
 *
 * Purpose : Lists all individual survey responses submitted for a specific survey belonging to the logged-in company.
 * -------------------------------------------------------
 */

// Initialize the session to track user authentication state.
session_start();
// Include the database connection configuration to interact with the database.
include("../config/db.php");

// Verify that the company is logged in before granting access.
if (!isset($_SESSION['company_id'])) {
    // Redirect the user to the appropriate page after the action completes.
    header("Location: login.php");
    exit();
}

$company_id = $_SESSION['company_id'];

// Prepare the SQL statement to securely handle user input and prevent SQL injection.
$stmt = $conn->prepare("
SELECT
    responses.response_id,
    responses.submitted_at,

    CASE
        WHEN users.is_deleted = 1 THEN 'Deleted Account'
        ELSE users.name
    END AS name,

    surveys.title

FROM responses

JOIN users
ON responses.user_id = users.user_id

JOIN surveys
ON responses.survey_id = surveys.survey_id

WHERE surveys.company_id = ?

ORDER BY responses.submitted_at DESC
");
// Bind parameters to the prepared statement.
$stmt->bind_param("i", $company_id);
// Execute the prepared statement against the database.
$stmt->execute();
// Retrieve the result set from the executed prepared statement.
$result = $stmt->get_result();
$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survey Responses</title>
    
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
        
        .response-card {
            border: none;
            border-radius: 18px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .response-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1) !important;
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
    <?php include("../includes/company_navbar.php"); ?>

    <div class="container dashboard-container">
        
        <!-- HERO SECTION -->
        <div class="hero-section">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <p class="mb-2 opacity-90 fs-5">
                        <i class="bi bi-chat-square-text-fill me-2"></i>
                        RESPONSE CENTER
                    </p>
                    <h1 class="display-5 fw-bold mb-3">Survey Responses</h1>
                    <p class="lead opacity-90 mb-0">
                        Review and analyze all customer feedback submissions in one place.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                    <div class="badge bg-white bg-opacity-25 px-4 py-3 fs-6">
                        <i class="bi bi-eye me-2"></i>
                        <?php echo mysqli_num_rows($result); ?> Total Responses
                    </div>
                </div>
            </div>
        </div>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <!-- SEARCH & FILTER -->
            <div class="mb-4 d-flex flex-column flex-md-row gap-3 justify-content-between align-items-md-center">
                <h4 class="section-title mb-0">All Submissions</h4>
                <input type="text" id="responseSearch" 
                       class="form-control w-100 w-md-50" 
                       placeholder="Search by user or survey name..." 
                       onkeyup="filterResponses()">
            </div>

            <!-- RESPONSES TABLE -->
            <div class="card shadow">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="responsesTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Survey</th>
                                    <th>User</th>
                                    <th>Submitted On</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                mysqli_data_seek($result, 0);
                                // Iterate through each row from the MySQLi result set.
                                while($row = mysqli_fetch_assoc($result)): 
                                ?>
                                    <tr class="response-row">
                                        <td class="fw-medium"><?php echo htmlspecialchars($row['title']); ?></td>
                                        <td><?php
                                                if ($row['name'] == 'Deleted Account') {
                                                    echo '<span class="badge bg-secondary">Deleted Account</span>';
                                                } else {
                                                    // Escape output to prevent Cross-Site Scripting (XSS) attacks.
                                                    echo htmlspecialchars($row['name']);
                                                }
                                                ?>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo date('M j, Y • g:i A', strtotime($row['submitted_at'])); ?>
                                            </small>
                                        </td>
                                        <td class="text-end">
                                            <a href="view_response_details.php?id=<?php echo $row['response_id']; ?>" 
                                               class="btn btn-primary btn-sm">
                                                <i class="bi bi-eye me-1"></i>
                                                View Answers
                                            </a>
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
                    <i class="bi bi-inbox display-1 text-muted"></i>
                </div>
                <h4 class="text-dark">No responses yet</h4>
                <p class="text-muted mb-4">Responses from your surveys will appear here once users start submitting them.</p>
                <a href="manage_surveys.php" class="btn btn-primary px-5 py-3">
                    <i class="bi bi-clipboard-data me-2"></i>
                    Manage Your Surveys
                </a>
            </div>
        <?php endif; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Frontend search functionality
        function filterResponses() {
            const searchTerm = document.getElementById('responseSearch').value.toLowerCase().trim();
            const rows = document.querySelectorAll('.response-row');
            
            rows.forEach(row => {
                const surveyText = row.cells[0].textContent.toLowerCase();
                const userText = row.cells[1].textContent.toLowerCase();
                
                if (surveyText.includes(searchTerm) || userText.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
        
        // Card/table row entrance animation
        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('.response-row');
            rows.forEach((row, index) => {
                row.style.opacity = '0';
                row.style.transform = 'translateY(15px)';
                setTimeout(() => {
                    row.style.transition = 'all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1)';
                    row.style.transitionDelay = (index * 40) + 'ms';
                    row.style.opacity = '1';
                    row.style.transform = 'translateY(0)';
                }, 100);
            });
        });
    </script>
</body>
</html>