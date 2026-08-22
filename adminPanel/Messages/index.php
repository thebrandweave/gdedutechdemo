<?php
session_start();
require_once '../../Configurations/config.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../admin_login.php');
    exit();
}

$admin_name = $_SESSION['username'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages & Q&A Management - GD Edu Tech Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="../../Images/Logos/GD_Only_logo.png">
    <link rel="stylesheet" href="../css/style.css">

    <style>
        .message-item, .qa-item {
            transition: all 0.25s ease;
        }
        .qa-item:hover {
            background-color: #f8fafc;
        }
        .answer-box {
            border-left: 3px solid #0d7298;
            background-color: #f1f5f9;
        }
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
                      
                    </div>
                </div>

                <ul class="nav nav-pills flex-column mb-auto p-2 w-100" id="menu">
                    <li class="w-100"><a href="../" class="nav-link"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
                    <li class="w-100"><a href="../Categories/" class="nav-link"><i class="bi bi-grid me-2"></i> Categories</a></li>
                    <li class="w-100"><a href="../Admissions/" class="nav-link"><i class="bi bi-person-plus me-2"></i> Student Admission</a></li>
                    <li class="w-100"><a href="../Courses/" class="nav-link"><i class="bi bi-book me-2"></i> Courses</a></li>
                    <li class="w-100"><a href="../Applications/" class="nav-link"><i class="bi bi-journal-text me-2"></i> Scholarships</a></li>
                    <li class="w-100"><a href="../Events/" class="nav-link"><i class="bi bi-calendar2-event me-2"></i> Events</a></li>
                    <li class="w-100"><a href="../social_links.php" class="nav-link"><i class="bi bi-link-45deg me-2"></i> Social Links</a></li>
                    <li class="w-100"><a href="../Schedule/index.php" class="nav-link"><i class="bi bi-calendar-event me-2"></i> Schedule</a></li>
                    <li class="w-100"><a href="../feedback/feedback.php" class="nav-link"><i class="bi bi-chat-square-heart me-2"></i> Feedback</a></li>
                    <li class="w-100"><a href="./" class="nav-link active"><i class="bi bi-chat-dots me-2"></i> Messages</a></li>
                    <li class="w-100"><a href="../FAQ/" class="nav-link"><i class="bi bi-question-circle me-2"></i> FAQ</a></li>
                    <li class="w-100"><a href="../Users/" class="nav-link"><i class="bi bi-people me-2"></i> Users</a></li>
                    <li class="w-100"><a href="../manage_qr.php" class="nav-link"><i class="bi bi-qr-code me-2"></i> Payment QR</a></li>
                    <li class="w-100"><a href="../pending_payments.php" class="nav-link"><i class="bi bi-credit-card me-2"></i> Pending Payments</a></li>
                </ul>

                <div class="p-3 border-top border-white border-opacity-10 mt-auto">
                    <a href="../logout.php" class="nav-link text-danger justify-content-center m-0">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </a>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="col main-content min-vh-100 d-flex flex-column" style="min-width: 0; overflow-x: hidden;">
                
                <!-- Top Header Bar -->
                <div class="bg-white border-bottom px-4 py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="fw-bold text-dark mb-0">Messages & Q&A Management</h4>
                        <span class="text-muted small">Broadcast announcements and answer student queries</span>
                    </div>

                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newMessageModal">
                        <i class="bi bi-plus-circle me-1.5"></i>Post New Message
                    </button>
                </div>

                <div class="p-4 flex-grow-1">

                    <!-- Messages Section Card -->
                    <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
                        <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-megaphone-fill text-primary me-2"></i>Important Broadcast Messages</h6>
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
                            ?>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Content Snippet</th>
                                                <th>Posted By</th>
                                                <th>Date Posted</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($message = mysqli_fetch_assoc($messages_result)): ?>
                                                <tr>
                                                    <td><strong class="text-dark"><?php echo htmlspecialchars($message['title']); ?></strong></td>
                                                    <td><span class="text-secondary"><?php echo htmlspecialchars(substr($message['content'], 0, 90)) . (strlen($message['content']) > 90 ? '...' : ''); ?></span></td>
                                                    <td>
                                                        <span class="badge bg-light text-dark border px-2.5 py-1 rounded-pill">
                                                            <i class="bi bi-person me-1"></i><?php echo htmlspecialchars($message['username']); ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-muted small"><?php echo date('M d, Y', strtotime($message['created_at'])); ?></td>
                                                    <td class="text-center">
                                                        <button class="action-icon text-danger border-0 bg-transparent" onclick="deleteMessage(<?php echo $message['message_id']; ?>)" title="Delete Message">
                                                            <i class="bi bi-trash-fill"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5 text-muted">
                                    <i class="bi bi-chat-square-text fs-1 text-secondary opacity-50 d-block mb-2"></i>
                                    No broadcast messages posted yet.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Q&A Section Card -->
                    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                        <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-question-square-fill text-primary me-2"></i>Student Questions & Answers</h6>
                        </div>
                        <div class="card-body p-0">
                            <?php
                            $questions_query = "
                                SELECT 
                                    q.*,
                                    u.username as asker_name,
                                    sa.content as answer_content,
                                    au.username as answerer_name,
                                    au.role as answerer_role
                                FROM StudentQuestions q
                                LEFT JOIN Users u ON q.user_id = u.user_id
                                LEFT JOIN StudentAnswers sa ON q.question_id = sa.question_id
                                LEFT JOIN Users au ON sa.user_id = au.user_id
                                ORDER BY q.created_at DESC
                            ";
                            $questions_result = mysqli_query($conn, $questions_query);
                            
                            if ($questions_result && mysqli_num_rows($questions_result) > 0):
                                while ($qa = mysqli_fetch_assoc($questions_result)):
                            ?>
                                <div class="qa-item p-4 border-bottom">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($qa['title']); ?></h6>
                                        <span class="badge <?php echo strtolower($qa['status'] ?? '') === 'answered' ? 'bg-success bg-opacity-10 text-success border border-success-subtle' : 'bg-warning bg-opacity-20 text-dark border border-warning-subtle'; ?> px-2.5 py-1 rounded-pill">
                                            <?php echo ucfirst($qa['status'] ?? 'pending'); ?>
                                        </span>
                                    </div>
                                    <p class="text-secondary mb-2 small"><?php echo nl2br(htmlspecialchars($qa['content'])); ?></p>
                                    
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="text-muted small">
                                            <i class="bi bi-person-circle me-1"></i>Asked by <strong><?php echo htmlspecialchars($qa['asker_name'] ?? 'Student'); ?></strong>
                                        </span>
                                        <span class="text-muted small">·</span>
                                        <span class="text-muted small">
                                            <i class="bi bi-clock me-1"></i><?php echo date('M d, Y', strtotime($qa['created_at'])); ?>
                                        </span>
                                    </div>
                                    
                                    <?php if ($qa['answer_content']): ?>
                                        <div class="answer-box mt-3 p-3 rounded-3">
                                            <p class="mb-2 text-dark small">
                                                <i class="bi bi-reply-fill me-2 text-primary"></i>
                                                <?php echo nl2br(htmlspecialchars($qa['answer_content'])); ?>
                                            </p>
                                            <span class="text-primary small fw-semibold">
                                                Answered by <?php echo htmlspecialchars($qa['answerer_name']); ?> 
                                                (<?php echo ucfirst($qa['answerer_role']); ?>)
                                            </span>
                                        </div>
                                    <?php else: ?>
                                        <button class="btn btn-outline-primary btn-sm rounded-pill px-3 mt-2" 
                                                onclick="showAnswerModal(<?php echo $qa['question_id']; ?>)">
                                            <i class="bi bi-reply me-1"></i>Answer Question
                                        </button>
                                    <?php endif; ?>
                                </div>
                            <?php 
                                endwhile;
                            else:
                            ?>
                                <div class="text-center py-5 text-muted">
                                    <i class="bi bi-chat-square-dots fs-1 text-secondary opacity-50 d-block mb-2"></i>
                                    No student questions submitted yet.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- New Message Modal -->
    <div class="modal fade" id="newMessageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title fw-bold text-dark"><i class="bi bi-megaphone me-2 text-primary"></i>Post Broadcast Message</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="post_message.php" method="POST" autocomplete="off">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label font-weight-semibold">Message Title</label>
                            <input type="text" class="form-control" name="title" required placeholder="Announcement title">
                        </div>
                        <div class="mb-3">
                            <label class="form-label font-weight-semibold">Message Content</label>
                            <textarea class="form-control" name="content" rows="4" required placeholder="Write message details..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top py-3">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-bold px-4">Post Message</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Answer Question Modal -->
    <div class="modal fade" id="answerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title fw-bold text-dark"><i class="bi bi-reply-all me-2 text-primary"></i>Answer Student Question</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="post_answer.php" method="POST" autocomplete="off">
                    <input type="hidden" name="question_id" id="question_id">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label font-weight-semibold">Your Official Response</label>
                            <textarea class="form-control" name="content" rows="4" required placeholder="Type answer for student..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top py-3">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-bold px-4">Post Answer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showAnswerModal(questionId) {
            document.getElementById('question_id').value = questionId;
            new bootstrap.Modal(document.getElementById('answerModal')).show();
        }

        function deleteMessage(messageId) {
            if (confirm('Are you sure you want to delete this message?')) {
                window.location.href = `delete_message.php?id=${messageId}`;
            }
        }
    </script>
</body>
</html>