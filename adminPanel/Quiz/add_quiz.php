<?php
session_start();
require_once '../../Configurations/config.php';

// Fetch courses for dropdown
$courses_query = mysqli_query($conn, "SELECT course_id, title FROM Courses ORDER BY title");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize inputs
    $course_id = intval($_POST['course_id']);
    $title = mysqli_real_escape_string($conn, trim($_POST['title']));
    $instructions = mysqli_real_escape_string($conn, trim($_POST['instructions']));
    $total_marks = intval($_POST['total_marks']);

    // Validation
    $errors = [];
    if (empty($title)) $errors[] = "Quiz title is required.";
    if ($course_id <= 0) $errors[] = "Please select a valid course.";
    if ($total_marks <= 0) $errors[] = "Total marks must be a positive number.";

    // If no errors, insert quiz
    if (empty($errors)) {
        $insert_query = "INSERT INTO Quizzes (course_id, title, instructions, total_marks) 
                         VALUES ($course_id, '$title', '$instructions', $total_marks)";
        
        if (mysqli_query($conn, $insert_query)) {
            $new_quiz_id = mysqli_insert_id($conn);
            $_SESSION['message'] = "Quiz created successfully. Now add questions.";
            $_SESSION['message_type'] = "success";
            header("Location: add_question.php?quiz_id=$new_quiz_id");
            exit();
        } else {
            $errors[] = "Database error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Quiz - GD Edu Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
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
            <div class="col py-3">
                <div class="container">
                    <h2>Add New Quiz</h2>
                    <!-- Form for adding a new quiz -->
                    <form action="add_quiz.php" method="post">
                        <!-- Form fields for quiz details -->
                        <!-- Error Handling -->
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <?php foreach ($errors as $error): ?>
                                    <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="course_id" class="form-label">Select Course</label>
                            <select name="course_id" id="course_id" class="form-select" required>
                                <option value="">Choose a Course</option>
                                <?php while ($course = mysqli_fetch_assoc($courses_query)): ?>
                                    <option value="<?php echo $course['course_id']; ?>">
                                        <?php echo htmlspecialchars($course['title']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="title" class="form-label">Quiz Title</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   placeholder="Enter quiz title" required
                                   value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
                        </div>

                        <div class="mb-3">
                            <label for="instructions" class="form-label">Quiz Instructions</label>
                            <textarea class="form-control" id="instructions" name="instructions" 
                                      rows="4" placeholder="Provide quiz instructions"><?php 
                                          echo isset($_POST['instructions']) ? htmlspecialchars($_POST['instructions']) : ''; 
                                      ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="total_marks" class="form-label">Total Marks</label>
                            <input type="number" class="form-control" id="total_marks" name="total_marks" 
                                   placeholder="Enter total marks" required min="1"
                                   value="<?php echo isset($_POST['total_marks']) ? intval($_POST['total_marks']) : '10'; ?>">
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="quiz.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Quiz</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>