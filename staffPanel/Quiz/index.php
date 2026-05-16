<?php
session_start();
require_once '../../Configurations/config.php';

// Check if user is logged in and is staff
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Staff') {
    header('Location: ../staff_login.php');
    exit();
}

// Get staff details from session
$staff_name = $_SESSION['username'] ?? 'Staff';

// Handle quiz deletion
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $quiz_id = intval($_GET['id']);
    
    // First, delete associated questions
    $delete_questions_query = "DELETE FROM Questions WHERE quiz_id = $quiz_id";
    mysqli_query($conn, $delete_questions_query);
    
    // Then delete the quiz
    $delete_quiz_query = "DELETE FROM Quizzes WHERE quiz_id = $quiz_id";
    if (mysqli_query($conn, $delete_quiz_query)) {
        $_SESSION['message'] = "Quiz deleted successfully.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error deleting quiz: " . mysqli_error($conn);
        $_SESSION['message_type'] = "danger";
    }
    header("Location: ./");
    exit();
}

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 9;
$offset = ($page - 1) * $limit;

// Fetch total number of quizzes
$total_quizzes_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM Quizzes");
$total_quizzes_row = mysqli_fetch_assoc($total_quizzes_query);
$total_quizzes = $total_quizzes_row['count'];
$total_pages = ceil($total_quizzes / $limit);

// Fetch quizzes with course information and question count
$query = "SELECT 
            q.*, 
            c.title as course_title, 
            COUNT(qs.question_id) as question_count
          FROM Quizzes q
          LEFT JOIN Courses c ON q.course_id = c.course_id
          LEFT JOIN Questions qs ON q.quiz_id = qs.quiz_id
          GROUP BY q.quiz_id
          ORDER BY q.created_at DESC 
          LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Management - GD Edu Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../../adminPanel/css/style.css">
    <style>
        .quiz-card {
            transition: all 0.3s ease;
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .quiz-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 20px rgba(0,0,0,0.15);
        }
        .quiz-card .card-body {
            display: flex;
            flex-direction: column;
        }
        .quiz-instructions {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            color: #6c757d;
            margin-bottom: 15px;
        }
        .quiz-badge {
            position: absolute;
            top: 10px;
            right: 10px;
        }
    </style>
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
                            <h2>Quiz Management</h2>
                            <p class="text-muted">Create, manage, and organize quizzes</p>
                        </div>
                        <div class="col-auto">
                            <a href="add_quiz.php" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-2"></i>Create New Quiz
                            </a>
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

                    <!-- Quizzes Grid -->
                    <div class="row row-cols-1 row-cols-md-3 g-4">
                        <?php while ($quiz = mysqli_fetch_assoc($result)): ?>
                            <div class="col">
                            <a href="course_quiz.php?course_id=<?php echo $quiz['course_id']; ?>" class="text-decoration-none">
                                <div class="card quiz-card h-100 position-relative" >
                                    
                                    <span class="badge bg-info quiz-badge">
                                        <?php echo $quiz['total_marks']; ?> Marks
                                    </span>
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <?php echo htmlspecialchars($quiz['title']); ?>
                                        </h5>

                                        <p class="text-muted mb-2">
                                            <i class="bi bi-book me-2"></i>
                                            <?php echo htmlspecialchars($quiz['course_title'] ?? 'Unassigned'); ?>
                                        </p>

                                        <p class="quiz-instructions">
                                            <?php 
                                            $instructions = $quiz['instructions'] ?? 'No instructions provided.';
                                            echo htmlspecialchars(substr($instructions, 0, 200) . (strlen($instructions) > 200 ? '...' : '')); 
                                            ?>
                                        </p>

                                        <div class="d-flex justify-content-between align-items-center mt-auto">
                                            <div class="text-muted">
                                                <i class="bi bi-question-circle me-1"></i>
                                                <?php echo $quiz['question_count']; ?> Questions
                                            </div>
                                            
                                            <div class="btn-group" role="group">
                                                <a href="add_question.php?quiz_id=<?php echo $quiz['quiz_id']; ?>&course_id=<?php echo $quiz['course_id']; ?>" 
                                                   class="btn btn-sm btn-outline-success" 
                                                   title="Manage Questions">
                                                    <i class="bi bi-plus-circle"></i>
                                                </a>
                                                <a href="edit_quiz.php?id=<?php echo $quiz['quiz_id']; ?>&course_id=<?php echo $quiz['course_id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary" 
                                                   title="Edit Quiz">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="./index.php?delete=1&id=<?php echo $quiz['quiz_id']; ?>" 
                                                   class="btn btn-sm btn-outline-danger" 
                                                   onclick="return confirm('Are you sure? This will delete the quiz and all its questions.');" 
                                                   title="Delete Quiz">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer text-muted d-flex justify-content-between">
                                        <small>
                                            <i class="bi bi-calendar me-1"></i>
                                            <?php echo date('M d, Y', strtotime($quiz['created_at'])); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <!-- Pagination -->
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="quiz.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
