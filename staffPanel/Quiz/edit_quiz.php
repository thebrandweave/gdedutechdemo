<?php
session_start();
require_once '../../Configurations/config.php';

// Check if user is logged in and is staff
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Staff') {
    header('Location: ../staff_login.php');
    exit();
}

$course_id = intval($_GET['course_id']);
$staff_id = $_SESSION['user_id'];
// Get staff details from session
$staff_name = $_SESSION['username'] ?? 'Staff';

// Check if quiz ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['message'] = "No quiz ID provided.";
    $_SESSION['message_type'] = "danger";
    header("Location: ./");
    exit();
}

$quiz_id = intval($_GET['id']);

// Fetch the quiz details
$query = "SELECT * FROM Quizzes WHERE quiz_id = $quiz_id";
$result = mysqli_query($conn, $query);
$quiz = mysqli_fetch_assoc($result);

if (!$quiz) {
    $_SESSION['message'] = "Quiz not found.";
    $_SESSION['message_type'] = "danger";
    header("Location: ./");
    exit();
}

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
// Handle form submission for editing quiz
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $instructions = mysqli_real_escape_string($conn, $_POST['instructions']);
    $total_marks = intval($_POST['total_marks']);
    $course_id = intval($_POST['course_id']);

    // Update quiz details
    $update_query = "UPDATE Quizzes SET title = '$title', instructions = '$instructions', total_marks = $total_marks, course_id = $course_id, updated_at = NOW() WHERE quiz_id = $quiz_id";

    if (mysqli_query($conn, $update_query)) {
        $_SESSION['message'] = "Quiz updated successfully.";
        $_SESSION['message_type'] = "success";
        header("Location: ./");
        exit();
    } else {
        $_SESSION['message'] = "Error updating quiz: " . mysqli_error($conn);
        $_SESSION['message_type'] = "danger";
    }
}

// Fetch available courses for the dropdown
$courses_query = "SELECT * FROM Courses";
$courses_result = mysqli_query($conn, $courses_query);





?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Quiz - GD Edu Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../../adminPanel/css/style.css">
</head>
<body>
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
                <div class="container-fluid">
                    <!-- Header -->
                    <div class="row mb-4">
                        <div class="col">
                            <h2>Edit Quiz</h2>
                            <p class="text-muted">Edit quiz details here</p>
                        </div>
                    </div>

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

                    <!-- Edit Quiz Form -->
                    <form method="POST">
                        <div class="mb-3">
                            <label for="title" class="form-label">Quiz Title</label>
                            <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($quiz['title']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="instructions" class="form-label">Instructions</label>
                            <textarea class="form-control" id="instructions" name="instructions" rows="4"><?php echo htmlspecialchars($quiz['instructions']); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="total_marks" class="form-label">Total Marks</label>
                            <input type="number" class="form-control" id="total_marks" name="total_marks" value="<?php echo htmlspecialchars($quiz['total_marks']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="course_id" class="form-label">Course</label>
                            <select class="form-select" id="course_id" name="course_id" required>
                                <option value="" disabled>Select Course</option>
                                <?php while ($course = mysqli_fetch_assoc($courses_result)): ?>
                                    <option value="<?php echo $course['course_id']; ?>" <?php echo $quiz['course_id'] == $course['course_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($course['title']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
