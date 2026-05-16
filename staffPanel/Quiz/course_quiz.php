<?php
session_start();

// Check if user is logged in and is staff
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Staff') {
    header('Location: ../staff_login.php');
    exit();
}

// Get staff details from session
$staff_name = $_SESSION['first_name'] ?? 'Staff';
$staff_id = $_SESSION['user_id'];

require_once '../../Configurations/config.php';

if (!isset($_GET['course_id'])) {
    $_SESSION['message'] = "No course selected.";
    $_SESSION['message_type'] = "danger";
    header("Location: ../Courses");
    exit();
}

$course_id = intval($_GET['course_id']);

// Add check to ensure staff can only view their own courses' quizzes
$course_check_query = "SELECT course_id FROM Courses WHERE course_id = ? AND created_by = ?";
$check_stmt = mysqli_prepare($conn, $course_check_query);
mysqli_stmt_bind_param($check_stmt, 'ii', $course_id, $staff_id);
mysqli_stmt_execute($check_stmt);
$course_result = mysqli_stmt_get_result($check_stmt);

if (mysqli_num_rows($course_result) === 0) {
    $_SESSION['message'] = "Access denied.";
    $_SESSION['message_type'] = "danger";
    header("Location: ./");
    exit();
}

// Fetch quizzes for the selected course
$query = "SELECT * FROM Quizzes WHERE course_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $course_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    die('Error fetching quizzes: ' . mysqli_error($conn));
}

if (mysqli_num_rows($result) == 0) {
    echo "No quizzes found for this course.";
}

$questions = [];
while ($quiz = mysqli_fetch_assoc($result)) {
    $quiz_id = $quiz['quiz_id'];
    $questions_query = "SELECT * FROM Questions WHERE quiz_id = ?";
    $questions_stmt = mysqli_prepare($conn, $questions_query);
    mysqli_stmt_bind_param($questions_stmt, 'i', $quiz_id);
    mysqli_stmt_execute($questions_stmt);
    $questions_result = mysqli_stmt_get_result($questions_stmt);

    if (!$questions_result) {
        die('Error fetching questions: ' . mysqli_error($conn));
    }

    if (mysqli_num_rows($questions_result) == 0) {
        echo "No questions found for quiz ID: $quiz_id";
    }

    while ($question = mysqli_fetch_assoc($questions_result)) {
        $questions[$quiz_id][] = $question;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Quiz - GD Edu Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .correct-option {
            background-color: green;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Add the same sidebar as quiz.php -->
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 sidebar">
                <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 min-vh-100">
                    <a href="#" class="d-flex align-items-center pb-3 mb-md-1 mt-md-3 me-md-auto text-white text-decoration-none">
                        <span class="fs-5 fw-bolder" style="display: flex;align-items:center;color:black;">
                            <img height="35px" src="./images/edutechLogo.png" alt="">&nbsp; GD Edu Tech
                        </span>
                    </a>
                    <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100" id="menu">
                        <li class="w-100">
                            <a href="../" class="nav-link ">
                                <i class="bi bi-speedometer2 me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Courses/" class="nav-link">
                                <i class="bi bi-book me-2 "></i> Courses
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="./" class="nav-link active">
                                <i class="bi bi-lightbulb me-2"></i> Quiz
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Messages/index.php" class="nav-link">
                                <i class="bi bi-chat-dots me-2"></i> Messages
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
            <div class="col py-3">
                <div class="container my-4">
                    <h2>Quiz Questions</h2>
                    <div class="accordion" id="quizAccordion">
                        <?php foreach ($questions as $quiz_id => $quiz_questions): ?>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading<?php echo $quiz_id; ?>">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $quiz_id; ?>" aria-expanded="true" aria-controls="collapse<?php echo $quiz_id; ?>">
                                        Quiz <?php echo $quiz_id; ?>
                                    </button>
                                </h2>
                                <div id="collapse<?php echo $quiz_id; ?>" class="accordion-collapse collapse" aria-labelledby="heading<?php echo $quiz_id; ?>" data-bs-parent="#quizAccordion">
                                    <div class="accordion-body">
                                        <?php foreach ($quiz_questions as $question): ?>
                                            <div class="mb-3">
                                                <strong>Question: </strong> <?php echo htmlspecialchars($question['content']); ?>
                                                <ul class="list-unstyled">
                                                    <li><?php echo htmlspecialchars($question['option_a']); ?></li>
                                                    <li><?php echo htmlspecialchars($question['option_b']); ?></li>
                                                    <li><?php echo htmlspecialchars($question['option_c']); ?></li>
                                                    <li><?php echo htmlspecialchars($question['option_d']); ?></li>
                                                </ul>
                                                <strong>Correct Answer: </strong> <?php echo $question['correct_option']; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
