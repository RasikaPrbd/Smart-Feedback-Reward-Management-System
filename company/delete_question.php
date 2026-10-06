<?php
/*
 * -------------------------------------------------------
 * Page    : Delete Question
 *
 * Purpose : Handles the deletion of a question and its associated options using a database transaction.
 * -------------------------------------------------------
 */

// Initialize the session to track user authentication state.
session_start();
// Include the database connection configuration to interact with the database.
include("../config/db.php");
include("../includes/survey_helper.php");

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

if (!isset($_POST['id']) || !isset($_POST['survey_id'])) {
    // Redirect the user to the appropriate page after the action completes.
    header("Location: manage_surveys.php");
    exit();
}

$question_id = (int)$_POST['id'];
$survey_id = (int)$_POST['survey_id'];
$company_id = $_SESSION['company_id'];

// Verify ownership and check expiration
    // Enforce ownership by filtering surveys to only those belonging to the logged-in company.
$check_stmt = $conn->prepare("
    SELECT q.question_id, s.expiry_date 
    FROM questions q 
    JOIN surveys s ON q.survey_id = s.survey_id 
    WHERE q.question_id = ? AND s.survey_id = ? AND s.company_id = ?
");
$check_stmt->bind_param("iii", $question_id, $survey_id, $company_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    $check_stmt->close();
    die("Question not found or access denied.");
}
$question = $result->fetch_assoc();
$check_stmt->close();

$is_expired = strtotime($question['expiry_date']) < time();
if ($is_expired) {
    $_SESSION['error_msg'] = "Survey is expired. You cannot delete questions.";
    header("Location: manage_questions.php?survey_id=" . $survey_id);
    exit();
}

try {
    // Begin a database transaction to ensure multiple dependent queries execute safely without partial updates.
    $conn->begin_transaction();

    // Delete question (Foreign key ON DELETE CASCADE will handle related options and responses)
    $del_stmt = $conn->prepare("DELETE FROM questions WHERE question_id = ?");
    $del_stmt->bind_param("i", $question_id);

    if ($del_stmt->execute()) {
        $_SESSION['success_msg'] = "✓ Question deleted successfully.";
    } else {
        throw new Exception("Error deleting question: " . $conn->error);
    }
    $del_stmt->close();

    updateSurveyStatus($conn, $survey_id);

    // Commit the transaction to permanently save the changes to the database.
    $conn->commit();
} catch (Exception $e) {
    // Rollback the transaction if an error occurs to prevent data corruption.
    $conn->rollback();
    $_SESSION['error_msg'] = $e->getMessage();
}

header("Location: manage_questions.php?survey_id=" . $survey_id);
exit();
?>
