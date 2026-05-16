<?php
session_start();

// Check if user is logged in and is staff
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Staff') {
    header('Location: ./staff_login.php');
    exit();
}

// Get staff details from session
$staff_id = $_SESSION['user_id'];
$staff_name = $_SESSION['username'] ?? 'Staff';

require_once '../../Configurations/config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages & Q&A Management - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/customBoorstrap.css">
    <link rel="stylesheet" href="../../adminPanel/css/style.css">
    <style>
        .message-item, .qa-item {
            transition: all 0.3s ease;
        }
        .message-item:hover, .qa-item:hover {
            background-color: rgba(0,0,0,.02);
        }
        .answer-box {
            border-left: 3px solid var(--bs-primary);
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
                        <span class="fs-5 fw-bolder" style="display: flex;align-items:center;color:black;"><img height="35px" src="../../images/edutechLogo.png" alt="">&nbsp; GD Edu Tech</span>
                    </a>
                    <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100" id="menu">
                        <li class="w-100">
                            <a href="../" class="nav-link">
                                <i class="bi bi-speedometer2 me-2"></i> Dashboard
                            </a>
                        </li>
                       
                        <li class="w-100">
                            <a href="../Courses/" class="nav-link">
                                <i class="bi bi-book me-2"></i> Courses
                            </a>
                        </li>

                        <li class="w-100">
                            <a href="../Quiz/" class="nav-link">
                                <i class="bi bi-lightbulb me-2"></i> Quiz
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="./" class="nav-link active">
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
                            <h2>Messages & Q&A Management</h2>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="../">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Messages & Q&A</li>
                                </ol>
                            </nav>
                        </div>
                    </div>

                    <!-- Messages Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">Important Messages</h5>
                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newMessageModal">
                                        <i class="bi bi-plus-circle me-1"></i>New Message
                                    </button>
                                </div>
                                <div class="card-body">
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
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Title</th>
                                                        <th>Content</th>
                                                        <th>Posted By</th>
                                                        <th>Date</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php while ($message = mysqli_fetch_assoc($messages_result)): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($message['title']); ?></td>
                                                            <td><?php echo htmlspecialchars(substr($message['content'], 0, 100)) . '...'; ?></td>
                                                            <td><?php echo htmlspecialchars($message['username']); ?></td>
                                                            <td><?php echo date('M d, Y', strtotime($message['created_at'])); ?></td>
                                                            <td>
                                                                <button class="btn btn-sm btn-danger" onclick="deleteMessage(<?php echo $message['message_id']; ?>)">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-4">
                                            <i class="bi bi-chat-square-text fs-1 text-muted"></i>
                                            <p class="text-muted mt-2">No messages found</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Q&A Section -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0">Student Questions</h5>
                                </div>
                                <div class="card-body">
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
                                        WHERE q.status = 'open'
                                        ORDER BY q.created_at DESC
                                    ";
                                    $questions_result = mysqli_query($conn, $questions_query);
                                    
                                    if ($questions_result && mysqli_num_rows($questions_result) > 0):
                                        while ($qa = mysqli_fetch_assoc($questions_result)):
                                    ?>
                                        <div class="qa-item p-3 border-bottom">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="mb-0"><?php echo htmlspecialchars($qa['title']); ?></h6>
                                                <span class="badge <?php echo $qa['status'] === 'answered' ? 'bg-success' : 'bg-warning'; ?>">
                                                    <?php echo ucfirst($qa['status']); ?>
                                                </span>
                                            </div>
                                            <p class="mb-2"><?php echo nl2br(htmlspecialchars($qa['content'])); ?></p>
                                            <small class="text-muted d-block mb-2">
                                                Asked by <?php echo htmlspecialchars($qa['asker_name']); ?> • 
                                                <?php echo date('M d, Y', strtotime($qa['created_at'])); ?>
                                            </small>
                                            
                                            <?php if ($qa['answer_content']): ?>
                                                <!-- Remove this entire block since we don't want to show answered questions -->
                                            <?php else: ?>
                                                <button class="btn btn-primary btn-sm mt-2" 
                                                        onclick="showAnswerModal(<?php echo $qa['question_id']; ?>)">
                                                    <i class="bi bi-reply me-1"></i>Answer Question
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    <?php 
                                        endwhile;
                                    else:
                                    ?>
                                        <div class="text-center py-4">
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

    <!-- New Message Modal -->
    <div class="modal fade" id="newMessageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Post New Message</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="./post_message.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Message Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message Content</label>
                            <textarea class="form-control" name="content" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Post Message</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Answer Question Modal -->
    <div class="modal fade" id="answerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Answer Question</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="post_answer.php" method="POST">
                    <input type="hidden" name="question_id" id="question_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Your Answer</label>
                            <textarea class="form-control" name="content" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Post Answer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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