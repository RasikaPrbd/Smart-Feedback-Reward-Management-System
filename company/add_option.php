<?php

/*
 * -------------------------------------------------------
 * Page    : Add Option
 *
 * Purpose : Allows a company to add a new answer option to a Multiple Choice question.
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

$message = "";

$company_id = $_SESSION['company_id'];

// Prepare the SQL statement to securely handle user input and prevent SQL injection.
$stmt = $conn->prepare("
SELECT questions.question_id, questions.question_text
FROM questions
JOIN surveys
ON questions.survey_id = surveys.survey_id
WHERE surveys.company_id = ?
AND questions.question_type = 'mcq'
");
// Bind parameters to the prepared statement.
$stmt->bind_param("i", $company_id);
// Execute the prepared statement against the database.
$stmt->execute();
$question_result = $stmt->get_result();
$stmt->close();

// Only process the form when the request was sent via POST to prevent accidental GET submissions.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $question_id = $_POST['question_id'];
    $option_text = trim($_POST['option_text']);

    if (empty($option_text)) {
        $message = "Error: Option text cannot be empty.";
    } else {
        // Verify ownership
        $check_stmt = $conn->prepare("
        SELECT questions.question_id 
        FROM questions 
        JOIN surveys ON questions.survey_id = surveys.survey_id 
        WHERE questions.question_id = ? AND surveys.company_id = ?
        ");
        $check_stmt->bind_param("ii", $question_id, $company_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $check_stmt->close();

        if ($check_result->num_rows === 0) {
            die("Invalid question or access denied.");
        }

        $insert_stmt = $conn->prepare("INSERT INTO options(question_id, option_text) VALUES(?, ?)");
        $insert_stmt->bind_param("is", $question_id, $option_text);

        if ($insert_stmt->execute()) {
            $message = "Option added successfully";
        } else {
            $message = "Error: " . $insert_stmt->error;
        }
        $insert_stmt->close();
    }
}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Add MCQ Option</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<?php include("../includes/company_navbar.php"); ?>

<div class="container mt-5">

    <div class="row justify-content-center">

        <div class="col-md-8">

            <div class="card shadow">

                <div class="card-body">

                    <h2 class="mb-4">
                        Add MCQ Option
                    </h2>

                    <?php if($message != "") { ?>

                        <div class="alert alert-success">

                            <?php echo $message; ?>

                        </div>

                    <?php } ?>

                    <form method="POST">

                        <div class="mb-3">

                            <label class="form-label">
                                Select Question
                            </label>

                            <select name="question_id"
                                    class="form-select"
                                    required>

                                <?php while($question = $question_result->fetch_assoc()) { ?>

                                    <option value="<?php echo $question['question_id']; ?>">

                                        <?php echo htmlspecialchars($question['question_text']); ?>

                                    </option>

                                <?php } ?>

                            </select>

                        </div>

                        <div class="mb-3">

                            <label class="form-label">
                                Option Text
                            </label>

                            <input type="text"
                                   name="option_text"
                                   class="form-control"
                                   required>

                        </div>

                        <button type="submit"
                                class="btn btn-warning w-100">

                            Add Option

                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>