<?php

/*
 * -------------------------------------------------------
 * Page    : Available Surveys
 *
 * Purpose : Displays all active surveys from all companies that the logged-in user has not yet completed.
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
        $order_by = "ORDER BY surveys.created_at ASC";
        break;
    case 'expiring':
        $order_by = "ORDER BY surveys.expiry_date ASC";
        break;
    case 'highest_reward':
        $order_by = "ORDER BY surveys.points_reward DESC";
        break;
    case 'lowest_reward':
        $order_by = "ORDER BY surveys.points_reward ASC";
        break;
    case 'a_z':
        $order_by = "ORDER BY surveys.title ASC";
        break;
    case 'z_a':
        $order_by = "ORDER BY surveys.title DESC";
        break;
    case 'newest':
    default:
        $order_by = "ORDER BY surveys.created_at DESC";
        break;
}

/* Get all active surveys */
// Only show surveys or rewards that are currently active and publicly visible.
$sql = "
SELECT surveys.*, companies.company_name, COUNT(questions.question_id) AS question_count
FROM surveys
JOIN companies ON surveys.company_id = companies.company_id
LEFT JOIN questions ON surveys.survey_id = questions.survey_id
WHERE surveys.status = 'active'
AND surveys.expiry_date >= CURDATE()
AND surveys.survey_id NOT IN
(
    SELECT survey_id
    FROM responses
    WHERE user_id = '$user_id'
)
GROUP BY surveys.survey_id
$order_by
";

$result = $conn->query($sql);

$total_surveys = $result->num_rows;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Surveys</title>
    
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
        
        .survey-card {
            border: none;
            border-radius: 18px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            height: 100%;
            overflow: hidden;
        }
        
        .survey-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
        }

        .text-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;  
            overflow: hidden;
        }
        
        .reward-badge {
            background: linear-gradient(135deg, #10b981, #34d399);
            color: white;
            font-weight: 700;
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 1.05rem;
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
            padding: 5rem 2rem;
        }
    </style>
</head>
<body>
    <?php include("../includes/user_navbar.php"); ?>

    <div class="container dashboard-container">
        
        <!-- HERO SECTION -->
        <div class="hero-section">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <p class="mb-2 opacity-90 fs-5">
                        <i class="bi bi-clipboard-check me-2"></i>
                        OPPORTUNITIES
                    </p>
                    <h1 class="display-5 fw-bold mb-3">Available Surveys</h1>
                    <p class="lead opacity-90 mb-0">
                        Share your opinion and earn reward points. New surveys added regularly.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                    <div class="d-inline-flex align-items-center gap-3 bg-white bg-opacity-20 rounded-4 px-4 py-3">
                        <i class="bi bi-list-check fs-1"></i>

                        <div>
                            <h3 class="mb-0 text-dark fw-bold"><?php echo $total_surveys; ?></h3>
                        </div>
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
                            placeholder="Search surveys or companies..."
                            // Escape output to prevent Cross-Site Scripting (XSS) attacks.
                            value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                    </div>

                    <select name="sort" class="form-select form-select-lg border-0 shadow-sm" style="max-width: 200px;" onchange="document.getElementById('sortForm').submit();">
                        <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="oldest" <?php echo $sort == 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                        <option value="expiring" <?php echo $sort == 'expiring' ? 'selected' : ''; ?>>Expiring Soon</option>
                        <option value="highest_reward" <?php echo $sort == 'highest_reward' ? 'selected' : ''; ?>>Highest Reward</option>
                        <option value="lowest_reward" <?php echo $sort == 'lowest_reward' ? 'selected' : ''; ?>>Lowest Reward</option>
                        <option value="a_z" <?php echo $sort == 'a_z' ? 'selected' : ''; ?>>A–Z</option>
                        <option value="z_a" <?php echo $sort == 'z_a' ? 'selected' : ''; ?>>Z–A</option>
                    </select>

                    <a href="view_surveys.php" class="btn btn-outline-secondary d-flex align-items-center justify-content-center px-4">
                        <i class="bi bi-arrow-clockwise me-2"></i> Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- SURVEYS LIST -->
        <div class="mb-4">
            <h4 class="section-title">Active Surveys</h4>
            <p class="text-muted">Complete surveys before they expire and earn rewards</p>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <div class="row g-4">
                <?php while($survey = $result->fetch_assoc()): ?>
                    <div class="col-12 col-md-6 col-lg-4 survey-card-item">
                        <div class="survey-card card shadow-sm border-0 rounded-4 h-100">
                            <div class="card-body d-flex flex-column p-4 p-xl-4">
                                <!-- Header -->
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="text-muted small fw-medium">#<?php echo $survey['survey_id']; ?></div>
                                    <span class="status-badge badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill px-3 py-2">Active</span>
                                </div>
                                
                                <!-- Title -->
                                <h5 class="card-title fw-bold text-dark mb-2"><?php echo htmlspecialchars($survey['title']); ?></h5>
                                
                                <!-- Company & Description -->
                                <div class="text-muted small mb-2"><i class="bi bi-building me-1"></i> <?php echo htmlspecialchars($survey['company_name']); ?></div>
                                <p class="text-muted flex-grow-1 small text-clamp-2 mb-4" title="<?php echo htmlspecialchars($survey['description']); ?>">
                                    <?php echo htmlspecialchars($survey['description']); ?>
                                </p>
                                
                                <!-- Meta -->
                                <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-light rounded-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 p-2 rounded me-2 text-primary">
                                            <i class="bi bi-gift-fill"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block" style="font-size: 0.75rem;">Reward</small>
                                            <strong class="text-dark"><?php echo $survey['points_reward']; ?> pts</strong>
                                        </div>
                                    </div>
                                    <div class="text-center border-start border-end px-2 flex-fill mx-2">
                                        <small class="text-muted d-block" style="font-size: 0.75rem;"><i class="bi bi-clipboard me-1 text-muted"></i>Questions</small>
                                        <strong class="text-dark"><?php echo $survey['question_count']; ?></strong>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted d-block" style="font-size: 0.75rem;">Expires</small>
                                        <strong class="text-dark">
                                            <i class="bi bi-calendar2-event me-1 text-muted"></i><?php echo date('M d, Y', strtotime($survey['expiry_date'])); ?>
                                        </strong>
                                    </div>
                                </div>
                                
                                <!-- Action -->
                                <a href="take_survey.php?survey_id=<?php echo $survey['survey_id']; ?>" 
                                   class="btn btn-primary w-100 py-3 fw-semibold mt-auto rounded-pill">
                                    <i class="bi bi-arrow-right-circle me-2"></i> Take Survey
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <!-- EMPTY STATE -->
            <div class="empty-state">
                <div class="mb-4">
                    <i class="bi bi-inbox display-1 text-muted"></i>
                </div>
                <h4 class="text-dark">No surveys available right now</h4>
                <p class="text-muted mb-4">Check back later for new opportunities to earn points!</p>
                <a href="dashboard.php" class="btn btn-outline-primary">
                    Back to Dashboard
                </a>
            </div>
        <?php endif; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Subtle card entrance animation
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.survey-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1)';
                    card.style.transitionDelay = (index * 80) + 'ms';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 150);
            });
        });

        function applySearch() {
            let value = document.getElementById("searchInput").value.toLowerCase();
            let cards = document.querySelectorAll(".survey-card-item");
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