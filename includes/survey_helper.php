<?php

/*
 * -------------------------------------------------------
 * Page    : Survey Helper
 *
 * Purpose : Contains shared helper functions used during the survey submission process.
 * -------------------------------------------------------
 */

function updateSurveyStatus($conn, $survey_id) {
    // Get current status
    $stmt = $conn->prepare("SELECT status FROM surveys WHERE survey_id = ?");
    // Bind parameters to the prepared statement.
    $stmt->bind_param("i", $survey_id);
    // Execute the prepared statement against the database.
    $stmt->execute();
    // Retrieve the result set from the executed prepared statement.
    $result = $stmt->get_result();
    if ($result->num_rows == 0) {
        $stmt->close();
        return;
    }
    $row = $result->fetch_assoc();
    $old_status = $row['status'];
    $stmt->close();

    // Count questions
    $count_stmt = $conn->prepare("SELECT COUNT(*) as q_count FROM questions WHERE survey_id = ?");
    $count_stmt->bind_param("i", $survey_id);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $count_row = $count_result->fetch_assoc();
    $q_count = (int)$count_row['q_count'];
    $count_stmt->close();

    // Determine new status
    $new_status = ($q_count >= 3) ? 'active' : 'draft';

    // Update if changed
    if ($new_status !== $old_status) {
        // Only show surveys or rewards that are currently active and publicly visible.
        $update_stmt = $conn->prepare("UPDATE surveys SET status = ? WHERE survey_id = ?");
        $update_stmt->bind_param("si", $new_status, $survey_id);
        $update_stmt->execute();
        $update_stmt->close();

        if (session_status() == PHP_SESSION_NONE) {
            // Initialize the session to track user authentication state.
            session_start();
        }

        if ($new_status == 'active') {
            $_SESSION['success_msg'] = "✓ Survey published successfully.";
        } else {
            $_SESSION['error_msg'] = "⚠ Survey moved back to Draft because it now has fewer than 3 questions.";
        }
    }
}
?>
