<?php
session_start();
require_once '../../Configurations/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login_page.php');
    exit();
}

// Get user details from session
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['username'] ?? 'Student';
$first_name = $_SESSION['first_name'] ?? 'Student';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages & Q&A - GD Edu Tech</title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="../../Images/Logos/GD_Only_logo.png">

    <style>
        :root {
            --admin-primary: #0d7298;
            --admin-primary-dark: #065d7d;
            --admin-dark-bg: #0f172a;
            --admin-dark-surface: #1e293b;
            --admin-light-bg: #f8fafc;
            --admin-border-color: #e2e8f0;
        }

        * {
            font-family: 'Poppins', sans-serif;
            box-sizing: border-box;
        }

        body {
            background-color: var(--admin-light-bg);
            color: #0f172a;
            overflow-x: hidden;
        }

        /* Sidebar Styling */
        .sidebar {
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%) !important;
            min-height: 100vh;
            color: #ffffff;
            box-shadow: 4px 0 25px rgba(15, 23, 42, 0.15);
            z-index: 1000;
        }

        .sidebar .nav-link {
            color: #94a3b8 !important;
            font-size: 0.9rem;
            font-weight: 500;
            padding: 12px 18px;
            margin: 3px 10px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.25s ease;
            text-decoration: none !important;
        }

        .sidebar .nav-link i {
            font-size: 1.15rem;
        }

        .sidebar .nav-link:hover {
            color: #ffffff !important;
            background: rgba(255, 255, 255, 0.08) !important;
            transform: translateX(4px);
        }

        .sidebar .nav-link.active {
            color: #ffffff !important;
            background: linear-gradient(90deg, rgba(13, 114, 152, 0.9) 0%, rgba(6, 93, 125, 0.95) 100%) !important;
            box-shadow: 0 8px 20px rgba(13, 114, 152, 0.35);
            font-weight: 700 !important;
        }

        .sidebar .nav-link.active i {
            color: #38bdf8 !important;
        }

        .sidebar .nav-link.text-danger {
            color: #f87171 !important;
            background: rgba(239, 68, 68, 0.1) !important;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .sidebar .nav-link.text-danger:hover {
            background: #ef4444 !important;
            color: #ffffff !important;
        }

        /* Layout bounds */
        .row.flex-nowrap > .col,
        .main-content {
            min-width: 0 !important;
            max-width: 100% !important;
        }

        .content-card {
            border: 1px solid var(--admin-border-color) !important;
            border-radius: 20px !important;
            box-shadow: 0 10px 30px -10px rgba(15, 23, 42, 0.05) !important;
            background: #ffffff;
            overflow: hidden;
        }

        .message-item, .qa-item {
            transition: all 0.2s ease;
        }

        .message-item:hover, .qa-item:hover {
            background-color: rgba(13, 114, 152, 0.02);
        }

        .answer-box {
            border-left: 4px solid #0d7298 !important;
            background-color: #f8fafc !important;
        }

        .form-control-custom {
            border-radius: 12px !important;
            border: 1.5px solid #cbd5e1 !important;
            padding: 10px 14px !important;
        }

        .form-control-custom:focus {
            border-color: #0d7298 !important;
            box-shadow: 0 0 0 4px rgba(13, 114, 152, 0.12) !important;
        }

        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>

<body>
    <div class="container-fluid p-0">
        <div class="row g-0 flex-nowrap">

            <!-- Executive Sidebar -->
            <div class="col-auto col-md-3 col-xl-2 px-0 sidebar sticky-top vh-100 overflow-auto hide-scrollbar d-flex flex-column">
                <div class="p-3 border-bottom border-white border-opacity-10 d-flex align-items-center gap-2">
                    <img height="36" src="../../Images/Logos/GD_Only_logo.png" alt="GD Logo">
                    <div>
                        <div class="fw-bold text-white fs-6">GD Edu Tech</div>
                        <span class="text-info small fw-semibold">● Student Portal</span>
                    </div>
                </div>

                <ul class="nav nav-pills flex-column mb-auto p-2 w-100">
                    <li class="w-100"><a href="../" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                    <li class="w-100"><a href="../MyCourses/" class="nav-link"><i class="bi bi-journal-bookmark"></i> My Courses</a></li>
                    <li class="w-100"><a href="../Categories/" class="nav-link"><i class="bi bi-grid"></i> Categories</a></li>
                    <li class="w-100"><a href="../Schedule/" class="nav-link"><i class="bi bi-calendar-event"></i> Schedule</a></li>
                    <li class="w-100"><a href="./" class="nav-link active"><i class="bi bi-chat-dots"></i> Messages</a></li>
                    <li class="w-100"><a href="../Profile/" class="nav-link"><i class="bi bi-person"></i> Profile</a></li>
                    <li class="w-100"><a href="../Resources/" class="nav-link"><i class="bi bi-file-earmark-text"></i> Resources</a></li>
                </ul>

                <div class="p-3 border-top border-white border-opacity-10 mt-auto">
                    <a href="../../logout.php" class="nav-link text-danger justify-content-center m-0">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="col main-content min-vh-100 d-flex flex-column" style="min-width: 0; overflow-x: hidden;">
                
                <!-- Top Header Bar -->
                <div class="bg-white border-bottom px-4 py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <h4 class="fw-bold text-dark mb-0">Messages & Community Q&A</h4>
                        <span class="text-muted small">Stay updated with platform announcements & ask questions</span>
                    </div>

                    <button class="btn btn-primary rounded-pill px-4 py-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#askQuestionModal">
                        <i class="bi bi-plus-circle me-1.5"></i>Ask a Question
                    </button>
                </div>

                <div class="p-4 flex-grow-1">

                    <div class="row g-4 mb-4">
                        
                        <!-- Important Messages Section -->
                        <div class="col-12 col-xl-6">
                            <div class="card content-card h-100 border-0">
                                <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-megaphone-fill text-primary me-2"></i>Platform Announcements</h6>
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
                                            <div class="p-4 border-bottom message-item">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <h6 class="mb-0 fw-bold text-dark"><?php echo htmlspecialchars($message['title']); ?></h6>
                                                    <span class="badge bg-light text-secondary border font-monospace small">
                                                        <?php echo date('M d, Y', strtotime($message['created_at'])); ?>
                                                    </span>
                                                </div>
                                                <p class="text-secondary small mb-2"><?php echo nl2br(htmlspecialchars($message['content'])); ?></p>
                                                <span class="badge bg-primary bg-opacity-10 text-primary small">
                                                    By <?php echo htmlspecialchars($message['username']); ?> (<?php echo ucfirst($message['role']); ?>)
                                                </span>
                                            </div>
                                        <?php
                                        endwhile;
                                    else:
                                        ?>
                                        <div class="text-center py-5 text-muted">
                                            <i class="bi bi-chat-square-dots fs-1 text-secondary opacity-50 d-block mb-2"></i>
                                            No announcements available yet.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Q&A Forum Section -->
                        <div class="col-12 col-xl-6">
                            <div class="card content-card h-100 border-0">
                                <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-question-circle-fill text-primary me-2"></i>Community Q&A Forum</h6>
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
                                            <div class="p-4 border-bottom qa-item">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <h6 class="mb-0 fw-bold text-dark"><?php echo htmlspecialchars($qa['title']); ?></h6>
                                                    <span class="badge <?php echo $qa['status'] === 'answered' ? 'bg-success bg-opacity-10 text-success border border-success-subtle' : 'bg-warning bg-opacity-20 text-dark border border-warning-subtle'; ?> rounded-pill px-2.5 py-1 small">
                                                        <?php echo ucfirst($qa['status']); ?>
                                                    </span>
                                                </div>
                                                <p class="text-secondary small mb-3"><?php echo nl2br(htmlspecialchars($qa['content'])); ?></p>
                                                
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="text-muted small">
                                                        Asked by <strong><?php echo htmlspecialchars($qa['asker_name']); ?></strong> •
                                                        <?php echo date('M d, Y', strtotime($qa['created_at'])); ?>
                                                    </span>
                                                    <?php if ($qa['user_id'] == $_SESSION['user_id']): ?>
                                                        <button class="btn btn-outline-danger btn-sm rounded-pill px-3 delete-question" data-question-id="<?php echo $qa['question_id']; ?>">
                                                            <i class="bi bi-trash me-1"></i>Delete
                                                        </button>
                                                    <?php endif; ?>
                                                </div>

                                                <?php if ($qa['answer_content']): ?>
                                                    <button class="btn btn-link p-0 text-decoration-none small fw-semibold text-primary mb-2 toggle-answer" type="button">
                                                        <i class="bi bi-chevron-down"></i> Show Answer
                                                    </button>
                                                    <div class="answer-box p-3 rounded-3" style="display: none;">
                                                        <p class="mb-2 text-dark small">
                                                            <i class="bi bi-reply-fill text-primary me-2"></i>
                                                            <?php echo nl2br(htmlspecialchars($qa['answer_content'])); ?>
                                                        </p>
                                                        <span class="text-primary small fw-semibold">
                                                            Answered by <?php echo htmlspecialchars($qa['answerer_name']); ?> (<?php echo ucfirst($qa['answerer_role']); ?>)
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php
                                        endwhile;
                                    else:
                                        ?>
                                        <div class="text-center py-5 text-muted">
                                            <i class="bi bi-chat-right-quote fs-1 text-secondary opacity-50 d-block mb-2"></i>
                                            No questions posted in the forum yet.
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
    <div class="modal fade" id="askQuestionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                <div class="modal-header bg-white border-bottom py-3 px-4">
                    <h5 class="modal-title fw-bold text-dark mb-0"><i class="bi bi-patch-question-fill text-primary me-2"></i>Ask a Question</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="ask_question.php" method="POST">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label font-weight-semibold small text-secondary">Question Title *</label>
                            <input type="text" class="form-control form-control-custom" name="title" placeholder="e.g. How do I access video lecture assignments?" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label font-weight-semibold small text-secondary">Detailed Question *</label>
                            <textarea class="form-control form-control-custom" name="content" rows="4" placeholder="Explain your question in detail so instructors can assist..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top p-3">
                        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">Submit Question</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle answer visibility
            const toggleButtons = document.querySelectorAll('.qa-item .toggle-answer');
            toggleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const answerBox = this.nextElementSibling;
                    const isHidden = answerBox.style.display === 'none';

                    answerBox.style.display = isHidden ? 'block' : 'none';

                    const icon = this.querySelector('i');
                    icon.className = isHidden ? 'bi bi-chevron-up' : 'bi bi-chevron-down';
                    this.innerHTML = icon.outerHTML + (isHidden ? ' Hide Answer' : ' Show Answer');
                });
            });

            // Delete question handler
            const deleteButtons = document.querySelectorAll('.delete-question');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const questionId = this.getAttribute('data-question-id');
                    if (confirm('Are you sure you want to delete this question?')) {
                        window.location.href = 'delete_question.php?id=' + questionId;
                    }
                });
            });
        });
    </script>
</body>
</html>