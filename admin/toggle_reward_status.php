<?php
/*
 * -------------------------------------------------------
 * Page    : Toggle Reward Status
 *
 * Purpose : Activates or archives a reward. Only accessible by administrators via POST request.
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

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request method");
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    csrf_abort();
}

if (isset($_POST['id']) && isset($_POST['action'])) {
    $id = intval($_POST['id']);
    $action = $_POST['action'];
    $new_status = ($action === 'archive') ? 'archived' : 'available';
    
    // Prepare the SQL statement to securely handle user input and prevent SQL injection.
    $stmt = $conn->prepare("UPDATE rewards SET status = ? WHERE reward_id = ?");
    // Bind parameters to the prepared statement.
    $stmt->bind_param("si", $new_status, $id);
    
    if ($stmt->execute()) {
        $_SESSION['success_msg'] = "Reward successfully " . ($action === 'archive' ? 'archived' : 'restored') . ".";
    }
    
    $stmt->close();
}

// Redirect the user to the appropriate page after the action completes.
header("Location: manage_rewards.php");
exit();
?>
