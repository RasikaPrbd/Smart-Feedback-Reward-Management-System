<?php

/*
 * -------------------------------------------------------
 * Page    : Delete Reward
 *
 * Purpose : Handles the deletion of a reward that belongs to the logged-in company after CSRF and POST validation.
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

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request method");
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    csrf_abort();
}

if (!isset($_POST['id'])) {
    // Redirect the user to the appropriate page after the action completes.
    header("Location: manage_rewards.php");
    exit();
}

$reward_id = (int)$_POST['id'];

/*
    Verify ownership
*/

$stmt_check = $conn->prepare("SELECT * FROM rewards WHERE reward_id = ? AND company_id = ?");
$stmt_check->bind_param("ii", $reward_id, $company_id);
$stmt_check->execute();
$check_result = $stmt_check->get_result();

if($check_result->num_rows == 0)
{
    $stmt_check->close();
    die("Reward not found.");
}
$stmt_check->close();

/*
    Delete related redemptions first
*/

$stmt_redemp = $conn->prepare("DELETE FROM redemptions WHERE reward_id = ?");
$stmt_redemp->bind_param("i", $reward_id);
$stmt_redemp->execute();
$stmt_redemp->close();

/*
    Delete reward
*/

$stmt_rew = $conn->prepare("DELETE FROM rewards WHERE reward_id = ?");
$stmt_rew->bind_param("i", $reward_id);
$stmt_rew->execute();
$stmt_rew->close();

$_SESSION['success_msg'] = "✓ Reward deleted successfully.";
// Redirect the user to the appropriate page after the action completes.
header("Location: manage_rewards.php");
exit();

?>