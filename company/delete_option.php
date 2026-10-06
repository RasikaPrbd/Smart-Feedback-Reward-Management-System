<?php
/*
 * -------------------------------------------------------
 * Page    : Delete Option
 *
 * Purpose : Handles the deletion of a specific answer option from a Multiple Choice question.
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

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request method");
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    csrf_abort();
}

if (!isset($_POST['id']) || !isset($_POST['question_id']) || !isset($_POST['survey_id'])) {
    // Redirect the user to the appropriate page after the action completes.
    header("Location: manage_surveys.php");
    exit();
}

$option_id = (int)$_POST['id'];
$question_id = (int)$_POST['question_id'];
$survey_id = (int)$_POST['survey_id'];
$company_id = $_SESSION['company_id'];

// Verify ownership and check expiration
    // Enforce ownership by filtering surveys to only those belonging to the logged-in company.
$check_stmt = $conn->prepare("
    SELECT o.option_id, s.expiry_date 
    FROM options o 
    JOIN questions q ON o.question_id = q.question_id 
    JOIN surveys s ON q.survey_id = s.survey_id 
    WHERE o.option_id = ? AND q.question_id = ? AND s.survey_id = ? AND s.company_id = ?
");
$check_stmt->bind_param("iiii", $option_id, $question_id, $survey_id, $company_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    $check_stmt->close();
    die("Option not found or access denied.");
}
$option = $result->fetch_assoc();
$check_stmt->close();

$is_expired = strtotime($option['expiry_date']) < time();
if ($is_expired) {
    $_SESSION['error_msg'] = "Survey is expired. You cannot delete options.";
    // Redirect the user to the appropriate page after the action completes.
    header("Location: manage_options.php?question_id=$question_id&survey_id=$survey_id");
    exit();
}

$del_stmt = $conn->prepare("DELETE FROM options WHERE option_id = ?");
$del_stmt->bind_param("i", $option_id);

if ($del_stmt->execute()) {
    $_SESSION['success_msg'] = "✓ Option deleted successfully.";
} else {
    $_SESSION['error_msg'] = "Error deleting option: " . $conn->error;
}
$del_stmt->close();

// Redirect the user to the appropriate page after the action completes.
header("Location: manage_options.php?question_id=$question_id&survey_id=$survey_id");
exit();
?>
