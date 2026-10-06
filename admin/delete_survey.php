<?php

/*
 * -------------------------------------------------------
 * Page    : Delete Survey
 *
 * Purpose : Handles the server-side deletion of a survey along with its cascading related data. Validates CSRF and POST method before execution.
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

if (!isset($_POST['id'])) {
    // Redirect the user to the appropriate page after the action completes.
    header("Location: manage_surveys.php");
    exit();
}

$survey_id = (int)$_POST['id'];

$stmt1 = $conn->prepare("
    DELETE answers
    FROM answers
    JOIN responses
    ON answers.response_id = responses.response_id
    WHERE responses.survey_id = ?
");
$stmt1->bind_param("i", $survey_id);
$stmt1->execute();
$stmt1->close();

$stmt2 = $conn->prepare("DELETE FROM responses WHERE survey_id = ?");
$stmt2->bind_param("i", $survey_id);
$stmt2->execute();
$stmt2->close();

$stmt3 = $conn->prepare("
    DELETE FROM options
    WHERE question_id IN (
        SELECT question_id
        FROM questions
        WHERE survey_id = ?
    )
");
$stmt3->bind_param("i", $survey_id);
$stmt3->execute();
$stmt3->close();

$stmt4 = $conn->prepare("DELETE FROM questions WHERE survey_id = ?");
$stmt4->bind_param("i", $survey_id);
$stmt4->execute();
$stmt4->close();

$stmt5 = $conn->prepare("DELETE FROM surveys WHERE survey_id = ?");
$stmt5->bind_param("i", $survey_id);
$stmt5->execute();
$stmt5->close();

// Redirect the user to the appropriate page after the action completes.
header("Location: manage_surveys.php");
exit();

?>