<?php
session_start();
require_once '../../Configurations/config.php';

// Ensure user is logged in and has admin privileges
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch quiz details
$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;
if ($quiz_id === 0) {
    header("Location: quiz.php");
    exit();
}

// Check if the quiz exists
$quiz_query = mysqli_query($conn, "SELECT * FROM Quizzes WHERE quiz_id = $quiz_id");
if (mysqli_num_rows($quiz_query) == 0) {
    header("Location: quiz.php");
    exit();
}

// Handle form submission for adding a question
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = [];
    $content = mysqli_real_escape_string($conn, trim($_POST['content']));
    $option_a = mysqli_real_escape_string($conn, trim($_POST['option_a']));
    $option_b = mysqli_real_escape_string($conn, trim($_POST['option_b']));
    $option_c = mysqli_real_escape_string($conn, trim($_POST['option_c']));
    $option_d = mysqli_real_escape_string($conn, trim($_POST['option_d']));
    $correct_option = mysqli_real_escape_string($conn, $_POST['correct_option']);

    // Validate inputs
    if (empty($content)) $errors[] = "Question content is required.";
    if (empty($option_a) || empty($option_b) || empty($option_c) || empty($option_d)) {
        $errors[] = "All options are required.";
    }
    if (!in_array($correct_option, ['A', 'B', 'C', 'D'])) {
        $errors[] = "Valid correct option must be selected (A, B, C, or D).";
    }

    // If no errors, insert the question into the database
    if (empty($errors)) {
        $insert_query = "INSERT INTO Questions (quiz_id, content, option_a, option_b, option_c, option_d, correct_option)
                         VALUES ($quiz_id, '$content', '$option_a', '$option_b', '$option_c', '$option_d', '$correct_option')";
                         
        if (mysqli_query($conn, $insert_query)) {
            $_SESSION['message'] = "Question added successfully.";
            $_SESSION['message_type'] = "success";
            header("Location: add_question.php?quiz_id=$quiz_id");
            exit();
        } else {
            $_SESSION['message'] = "Error adding question: " . mysqli_error($conn);
            $_SESSION['message_type'] = "danger";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Question - GD Edu Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 sidebar">
                <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 min-vh-100">
                    <a href="#" class="d-flex align-items-center pb-3 mb-md-1 mt-md-3 me-md-auto text-white text-decoration-none">
                        <span class="fs-5 fw-bolder" style="display: flex;align-items:center;color:black;">
                            <img height="35px" src="../images/edutechLogo.png" alt="">&nbsp; GD Edu Tech
                        </span>
                    </a>
                    <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100" id="menu">
                        <li class="w-100">
                            <a href="../" class="nav-link">
                                <i class="bi bi-speedometer2 me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Categories/" class="nav-link">
                                <i class="bi bi-grid me-2"></i> Categories
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Courses/" class="nav-link">
                                <i class="bi bi-book me-2"></i> Courses
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Quiz/" class="nav-link active">
                                <i class="bi bi-lightbulb me-2"></i> Quiz
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Schedule/" class="nav-link">
                                <i class="bi bi-calendar-event me-2"></i> Schedule
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Messages/" class="nav-link">
                                <i class="bi bi-chat-dots me-2"></i> Messages
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../FAQ/" class="nav-link">
                                <i class="bi bi-question-circle me-2"></i> FAQ
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Users/" class="nav-link">
                                <i class="bi bi-people me-2"></i> Users
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../manage_qr.php" class="nav-link">
                                <i class="bi bi-qr-code me-2"></i> Payment QR
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../pending_payments.php" class="nav-link">
                                <i class="bi bi-credit-card me-2"></i> Pending Payments
                            </a>
                        </li>
                        <li class="w-100 mt-auto">
                            <a href="../logout.php" class="nav-link text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-3">
                <h2>Add Question to Quiz</h2>
                <p class="text-muted">Add questions for the selected quiz.</p>

                <!-- Alert Messages -->
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                        <?php
                        echo htmlspecialchars($_SESSION['message']);
                        unset($_SESSION['message']);
                        unset($_SESSION['message_type']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Add Question Form -->
                <form action="add_question.php?quiz_id=<?php echo $quiz_id; ?>" method="POST">
                    <div class="mb-3">
                        <label for="content" class="form-label">Question Content</label>
                        <textarea class="form-control" id="content" name="content" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="option_a" class="form-label">Option A</label>
                        <input type="text" class="form-control" id="option_a" name="option_a" required>
                    </div>
                    <div class="mb-3">
                        <label for="option_b" class="form-label">Option B</label>
                        <input type="text" class="form-control" id="option_b" name="option_b" required>
                    </div>
                    <div class="mb-3">
                        <label for="option_c" class="form-label">Option C</label>
                        <input type="text" class="form-control" id="option_c" name="option_c" required>
                    </div>
                    <div class="mb-3">
                        <label for="option_d" class="form-label">Option D</label>
                        <input type="text" class="form-control" id="option_d" name="option_d" required>
                    </div>
                    <div class="mb-3">
                        <label for="correct_option" class="form-label">Correct Option</label>
                        <select class="form-select" id="correct_option" name="correct_option" required>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Question</button>
                </form>

                <!-- Back to Quiz List -->
                <a href="quiz.php" class="btn btn-secondary mt-3">Back to Quiz List</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
