<?php
session_start();
require_once '../../Configurations/config.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

// Check if quiz_id is provided
if (!isset($_GET['quiz_id'])) {
    header("Location: my_course.php");
    exit();
}

$quiz_id = intval($_GET['quiz_id']);
$user_id = $_SESSION['user_id'];

// Fetch quiz details
$quiz_query = "SELECT q.*, c.title as course_title, c.course_id 
               FROM Quizzes q 
               JOIN Courses c ON q.course_id = c.course_id 
               WHERE q.quiz_id = ?";
$quiz_stmt = $conn->prepare($quiz_query);
$quiz_stmt->bind_param('i', $quiz_id);
$quiz_stmt->execute();
$quiz = $quiz_stmt->get_result()->fetch_assoc();

if (!$quiz) {
    header("Location: my_course.php");
    exit();
}

// Verify enrollment and course completion
$enrollment_query = "SELECT e.*, 
    (SELECT COUNT(*) FROM UserProgress up 
     JOIN Videos v ON up.video_id = v.video_id 
     JOIN Lessons l ON v.lesson_id = l.lesson_id 
     WHERE l.course_id = e.course_id 
     AND up.user_id = e.student_id 
     AND up.completed = 1) as completed_videos,
    (SELECT COUNT(*) FROM Videos v 
     JOIN Lessons l ON v.lesson_id = l.lesson_id 
     WHERE l.course_id = e.course_id) as total_videos
    FROM Enrollments e 
    WHERE e.student_id = ? AND e.course_id = ?";
$enrollment_stmt = $conn->prepare($enrollment_query);
$enrollment_stmt->bind_param('ii', $user_id, $quiz['course_id']);
$enrollment_stmt->execute();
$enrollment = $enrollment_stmt->get_result()->fetch_assoc();

if (!$enrollment || $enrollment['completed_videos'] < $enrollment['total_videos']) {
    header("Location: course_content.php?id=" . $quiz['course_id']);
    exit();
}

// Fetch questions
$questions_query = "SELECT * FROM Questions WHERE quiz_id = ? ORDER BY RAND()";
$questions_stmt = $conn->prepare($questions_query);
$questions_stmt->bind_param('i', $quiz_id);
$questions_stmt->execute();
$questions_result = $questions_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($quiz['title']); ?> - Assessment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 40px 0;
        }
        .quiz-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            padding: 30px;
        }
        .question-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .option-label {
            display: block;
            padding: 15px;
            margin: 10px 0;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .option-label:hover {
            background: #e9ecef;
        }
        input[type="radio"]:checked + .option-label {
            border-color: #2C3E50;
            background: #edf2f7;
        }
        .quiz-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #edf2f7;
        }
        .timer {
            font-size: 1.2rem;
            font-weight: bold;
            color: #2C3E50;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="quiz-container">
            <div class="quiz-header">
                <h2><?php echo htmlspecialchars($quiz['title']); ?></h2>
                <p class="text-muted"><?php echo htmlspecialchars($quiz['course_title']); ?></p>
                <div class="timer" id="timer">Time Remaining: 30:00</div>
            </div>

            <form id="quizForm" action="submit_quiz.php" method="POST">
                <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
                
                <?php 
                $question_num = 1;
                while ($question = $questions_result->fetch_assoc()): 
                ?>
                    <div class="question-card">
                        <h5>
                            <span class="badge bg-primary me-2"><?php echo $question_num; ?></span>
                            <?php echo htmlspecialchars($question['content']); ?>
                        </h5>
                        
                        <?php
                        $options = [
                            'A' => $question['option_a'],
                            'B' => $question['option_b'],
                            'C' => $question['option_c'],
                            'D' => $question['option_d']
                        ];
                        foreach ($options as $key => $option):
                        ?>
                            <div class="option">
                                <input type="radio" 
                                       id="q<?php echo $question['question_id'] . $key; ?>" 
                                       name="answers[<?php echo $question['question_id']; ?>]" 
                                       value="<?php echo $key; ?>" 
                                       required
                                       class="d-none">
                                <label for="q<?php echo $question['question_id'] . $key; ?>" 
                                       class="option-label">
                                    <strong><?php echo $key; ?>.</strong> 
                                    <?php echo htmlspecialchars($option); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php 
                $question_num++;
                endwhile; 
                ?>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-check-circle me-2"></i>Submit Quiz
                </button>
            </form>
        </div>
    </div>

    <script>
        // Timer functionality
        let timeLeft = 1800; // 30 minutes in seconds
        const timerDisplay = document.getElementById('timer');
        
        const timer = setInterval(() => {
            timeLeft--;
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            timerDisplay.textContent = `Time Remaining: ${minutes}:${seconds.toString().padStart(2, '0')}`;
            
            if (timeLeft <= 0) {
                clearInterval(timer);
                document.getElementById('quizForm').submit();
            }
        }, 1000);

        // Form submission confirmation
        document.getElementById('quizForm').addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to submit the quiz? You cannot change your answers after submission.')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>

