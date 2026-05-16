<?php
session_start();
require_once '../../Configurations/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Get user details from session
$user_name = $_SESSION['username'] ?? 'Student';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages & Q&A - Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/css/student_dashboard.css">
    <link rel="stylesheet" href="../../css/customBootstrap.css">

            
    <link rel="icon" type="image/png" href="../../Images/Logos/GD_Only_logo.png">
    <link rel="shortcut icon" href="../../Images/Logos/GD_Only_logo.png">
    <style>
        .sidebar {
            background-color: #2c3e50;
            min-height: 100vh;
        }

        .sidebar .nav-link {
            color: #fff;
            opacity: 0.85;
        }

        .sidebar .nav-link:hover {
            opacity: 1;
        }

        .sidebar .nav-link.active {
            background-color: #34495e;
        }

        @media (max-width: 768px) {

            /* Styles for the fixed sidebar (mobile only) */
            #sidebar {
                position: fixed;
                top: 0;
                left: 0;
                width: 70vw;
                /* Sidebar width */
                height: 100vh;
                /* Full height */
                background-color: #2c3e50;
                /* Sidebar background color */
                z-index: 1000;
                /* Ensure sidebar is above other content */
                transform: translateX(-100%);
                /* Initially hidden */
                transition: transform 0.3s ease;
                /* Smooth transition */
            }

            #sidebar.show {
                transform: translateX(0);
                /* Show sidebar */
            }

            .main-content.hidden {
                display: none;
                /* Hide main content when sidebar is open */
            }

        }

        .message-item,
        .qa-item {
            transition: background-color 0.2s ease;
        }

        .message-item:hover,
        .qa-item:hover {
            background-color: rgba(0, 0, 0, .02);
        }

        .answer-box {
            border-left: 3px solid var(--bs-primary);
        }

        @media (max-width: 767.98px) {
            .card-body {
                padding: 0.5rem !important;
            }

            .message-item,
            .qa-item {
                padding: 1rem !important;
            }
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar for mobile -->
            <div class="col-auto d-md-none">
                <button class="btn btn-primary" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
            </div>
            <div class="col-auto sidebar" id="sidebar">
                <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 min-vh-100">
                    <a href="#" class="d-flex align-items-center pb-3 mb-md-1 mt-md-3 me-md-auto text-white text-decoration-none">
                        <span class="fs-5 fw-bolder" style="display: flex;align-items:center;">
                            <img height="35px" src="../../Images/Logos/edutechLogo.png" alt="">&nbsp; GD Edu Tech
                        </span>
                    </a>
                    <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100" id="menu">
                        <li class="w-100">
                            <a href="../" class="nav-link text-white">
                                <i class="bi bi-speedometer2 me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../MyCourses/" class="nav-link text-white">
                                <i class="bi bi-book me-2"></i> My Courses
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Categories/" class="nav-link text-white">
                                <i class="bi bi-grid me-2"></i> Categories
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Schedule/" class="nav-link text-white">
                                <i class="bi bi-calendar-event me-2"></i> Schedule
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="./" class="nav-link active">
                                <i class="bi bi-chat-dots me-2"></i> Messages
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Profile/" class="nav-link text-white">
                                <i class="bi bi-person me-2"></i> Profile
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Resources/" class="nav-link text-white">
                                <i class="bi bi-file-earmark-text me-2"></i> Resources
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../shop.php" class="nav-link text-white">
                                <i class="bi bi-shop me-2"></i> Shop
                            </a>
                        </li>
                        <li class="w-100 mt-auto">
                            <a href="../../logout.php" class="nav-link text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col py-3 mainContent">
                <div class="container-fluid">
                    <!-- Breadcrumb -->
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../">Dashboard</a></li>
                            <li class="breadcrumb-item active">Messages & Q&A</li>
                        </ol>
                    </nav>

                    <div class="row">
                        <!-- Messages Section -->
                        <div class="col-12 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-megaphone me-2"></i>Important Messages
                                    </h5>
                                </div>
                                <div class="card-body p-0">
                                    <?php
                                    $messages_query = "
                                        SELECT m.*, u.username, u.role 
                                        FROM Messages m
                                        JOIN Users u ON m.created_by = u.user_id
                                        ORDER BY m.created_at DESC
                                    ";
                                    $messages_result = mysqli_query($conn, $messages_query);

                                    if ($messages_result && mysqli_num_rows($messages_result) > 0):
                                        while ($message = mysqli_fetch_assoc($messages_result)):
                                    ?>
                                            <div class="p-3 border-bottom message-item">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($message['title']); ?></h6>
                                                    <small class="text-muted">
                                                        <?php echo date('M d, Y', strtotime($message['created_at'])); ?>
                                                    </small>
                                                </div>
                                                <p class="mb-2"><?php echo nl2br(htmlspecialchars($message['content'])); ?></p>
                                                <small class="text-primary">
                                                    By <?php echo htmlspecialchars($message['username']); ?>
                                                    (<?php echo ucfirst($message['role']); ?>)
                                                </small>
                                            </div>
                                        <?php
                                        endwhile;
                                    else:
                                        ?>
                                        <div class="text-center py-5">
                                            <i class="bi bi-chat-square-dots fs-1 text-muted"></i>
                                            <p class="text-muted mt-2">No messages found</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Q&A Section -->
                        <div class="col-12 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-question-circle me-2"></i>Q&A Forum
                                    </h5>
                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#askQuestionModal">
                                        <i class="bi bi-plus-circle me-1"></i>Ask Question
                                    </button>
                                </div>
                                <div class="card-body p-0">
                                    <?php
                                    $questions_query = "
                                        SELECT 
                                            sq.*,
                                            u.username as asker_name,
                                            sa.content as answer_content,
                                            au.username as answerer_name,
                                            au.role as answerer_role
                                        FROM StudentQuestions sq
                                        LEFT JOIN Users u ON sq.user_id = u.user_id
                                        LEFT JOIN StudentAnswers sa ON sq.question_id = sa.question_id
                                        LEFT JOIN Users au ON sa.user_id = au.user_id
                                        ORDER BY sq.created_at DESC
                                    ";
                                    $questions_result = mysqli_query($conn, $questions_query);

                                    if ($questions_result && mysqli_num_rows($questions_result) > 0):
                                        while ($qa = mysqli_fetch_assoc($questions_result)):
                                    ?>
                                            <div class="p-3 border-bottom qa-item">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($qa['title']); ?></h6>
                                                    <span class="badge <?php echo $qa['status'] === 'answered' ? 'bg-success' : 'bg-warning'; ?>">
                                                        <?php echo ucfirst($qa['status']); ?>
                                                    </span>
                                                </div>
                                                <p class="mb-2"><?php echo nl2br(htmlspecialchars($qa['content'])); ?></p>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <small class="text-muted">
                                                        Asked by <?php echo htmlspecialchars($qa['asker_name']); ?> •
                                                        <?php echo date('M d, Y', strtotime($qa['created_at'])); ?>
                                                    </small>
                                                    <?php if ($qa['user_id'] == $_SESSION['user_id']): ?>
                                                        <button class="btn btn-danger btn-sm delete-question"
                                                            data-question-id="<?php echo $qa['question_id']; ?>">
                                                            <i class="bi bi-trash"></i> Delete
                                                        </button>
                                                    <?php endif; ?>
                                                </div>

                                                <?php if ($qa['answer_content']): ?>
                                                    <button class="btn btn-link p-0 text-decoration-none mb-2 toggle-answer" type="button">
                                                        <i class="bi bi-chevron-down"></i> Show Answer
                                                    </button>
                                                    <div class="answer-box mt-2 p-3 bg-light rounded" style="display: none;">
                                                        <p class="mb-2">
                                                            <i class="bi bi-reply me-2"></i>
                                                            <?php echo nl2br(htmlspecialchars($qa['answer_content'])); ?>
                                                        </p>
                                                        <small class="text-primary">
                                                            Answered by <?php echo htmlspecialchars($qa['answerer_name']); ?>
                                                            (<?php echo ucfirst($qa['answerer_role']); ?>)
                                                        </small>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php
                                        endwhile;
                                    else:
                                        ?>
                                        <div class="text-center py-5">
                                            <i class="bi bi-chat-square-dots fs-1 text-muted"></i>
                                            <p class="text-muted mt-2">No questions found</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ask Question Modal -->
    <div class="modal fade" id="askQuestionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ask a Question</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="ask_question.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Question Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Your Question</label>
                            <textarea class="form-control" name="content" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit Question</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar toggle functionality for mobile
        const sidebar = document.getElementById('sidebar');
        const toggleButton = document.getElementById('sidebarToggle');

        toggleButton.addEventListener('click', function() {
            sidebar.classList.toggle('show'); // Toggle sidebar visibility
        });

        // Close sidebar when clicking outside of it
        document.addEventListener('click', function(event) {
            if (!sidebar.contains(event.target) && !toggleButton.contains(event.target) && sidebar.classList.contains('show')) {
                sidebar.classList.remove('show'); // Hide sidebar
            }
        });

        // Toggle answer visibility
        document.addEventListener('DOMContentLoaded', function() {
            const toggleButtons = document.querySelectorAll('.qa-item .toggle-answer');

            toggleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const answerBox = this.nextElementSibling;
                    const isHidden = answerBox.style.display === 'none';

                    // Toggle answer visibility
                    answerBox.style.display = isHidden ? 'block' : 'none';

                    // Update button text and icon
                    const icon = this.querySelector('i');
                    icon.className = isHidden ? 'bi bi-chevron-up' : 'bi bi-chevron-down';
                    this.innerHTML = icon.outerHTML + (isHidden ? ' Hide Answer' : ' Show Answer');
                });
            });
        });
    </script>
</body>

</html>