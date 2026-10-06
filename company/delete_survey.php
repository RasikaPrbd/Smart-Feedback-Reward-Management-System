<?php

/*
 * -------------------------------------------------------
 * Page    : Delete Survey
 *
 * Purpose : Handles deletion of a survey and all associated questions, options, and responses using a database transaction.
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
    header("Location: manage_surveys.php");
    exit();
}

$survey_id = (int)$_POST['id'];

/*
    Verify ownership
*/

$stmt_check = $conn->prepare("SELECT * FROM surveys WHERE survey_id = ? AND company_id = ?");
$stmt_check->bind_param("ii", $survey_id, $company_id);
$stmt_check->execute();
$check_result = $stmt_check->get_result();

if ($check_result->num_rows == 0) {
    $stmt_check->close();
    die("Survey not found.");
}
$stmt_check->close();

/*
    Delete related records first
*/
try {
    // Begin a database transaction to ensure multiple dependent queries execute safely without partial updates.
    $conn->begin_transaction();

    $stmt_ans = $conn->prepare("
    DELETE answers
    FROM answers
    JOIN responses ON answers.response_id = responses.response_id
    WHERE responses.survey_id = ?
");
$stmt_ans->bind_param("i", $survey_id);
$stmt_ans->execute();
$stmt_ans->close();


$stmt_res = $conn->prepare("DELETE FROM responses WHERE survey_id = ?");
$stmt_res->bind_param("i", $survey_id);
$stmt_res->execute();
$stmt_res->close();


$stmt_opt = $conn->prepare("
    DELETE FROM options
    WHERE question_id IN (
        SELECT question_id
        FROM questions
        WHERE survey_id = ?
    )
");
$stmt_opt->bind_param("i", $survey_id);
$stmt_opt->execute();
$stmt_opt->close();


$stmt_q = $conn->prepare("DELETE FROM questions WHERE survey_id = ?");
$stmt_q->bind_param("i", $survey_id);
$stmt_q->execute();
$stmt_q->close();

/*
    Delete survey
*/

    $stmt_s = $conn->prepare("DELETE FROM surveys WHERE survey_id = ?");
    $stmt_s->bind_param("i", $survey_id);
    $stmt_s->execute();
    $stmt_s->close();

    // Commit the transaction to permanently save the changes to the database.
    $conn->commit();
} catch (Exception $e) {
    // Rollback the transaction if an error occurs to prevent data corruption.
    $conn->rollback();
    die("An error occurred during deletion.");
}

$_SESSION['success_msg'] = "✓ Survey deleted successfully.";
// Redirect the user to the appropriate page after the action completes.
header("Location: manage_surveys.php");
exit();

?>