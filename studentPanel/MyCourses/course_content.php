<?php
session_start();
require_once __DIR__ . '../../../vendor/autoload.php';
require_once '../../Configurations/config.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    if (!isset($_COOKIE['auth_token'])) {
        header("Location: ../login.php");
        exit();
    }
    try {
        $jwt = $_COOKIE['auth_token'];
        $decoded = JWT::decode($jwt, new Key($jwtSecretKey, 'HS256'));
        $_SESSION['user_id'] = $decoded->user_id;
        $_SESSION['username'] = $decoded->username;
        $_SESSION['role'] = $decoded->role;
    } catch (Exception $e) {
        setcookie('auth_token', '', time() - 3600, '/');
        session_destroy();
        header("Location: ../login.php");
        exit();
    }
}

if (!isset($_GET['id'])) {
    header("Location: ./");
    exit();
}

$course_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Verify enrollment
$enrollment_check = "SELECT * FROM Enrollments WHERE student_id = ? AND course_id = ? AND access_status = 'active'";
$check_stmt = $conn->prepare($enrollment_check);
$check_stmt->bind_param("ii", $user_id, $course_id);
$check_stmt->execute();
if ($check_stmt->get_result()->num_rows === 0) {
    header("Location: ./");
    exit();
}

// Fetch course details
$course_query = "SELECT c.*, cat.name AS category_name 
                 FROM Courses c 
                 JOIN Categories cat ON c.category_id = cat.category_id 
                 WHERE c.course_id = ?";
$course_stmt = $conn->prepare($course_query);
$course_stmt->bind_param('i', $course_id);
$course_stmt->execute();
$course = $course_stmt->get_result()->fetch_assoc();

// Fetch lessons with video count
$lessons_query = "SELECT l.*, 
                         (SELECT COUNT(*) FROM Videos v WHERE v.lesson_id = l.lesson_id) as video_count
                  FROM Lessons l 
                  WHERE l.course_id = ? 
                  ORDER BY l.lesson_order";
$lessons_stmt = $conn->prepare($lessons_query);
$lessons_stmt->bind_param('i', $course_id);
$lessons_stmt->execute();
$lessons_result = $lessons_stmt->get_result();

// Fetch user's progress
$progress_query = "SELECT lesson_id, video_id, completed 
                  FROM UserProgress 
                  WHERE user_id = ? AND course_id = ?";
$progress_stmt = $conn->prepare($progress_query);
$progress_stmt->bind_param('ii', $user_id, $course_id);
$progress_stmt->execute();
$progress_result = $progress_stmt->get_result();

$user_progress = [];
while ($progress = $progress_result->fetch_assoc()) {
    $user_progress[$progress['lesson_id']][$progress['video_id']] = $progress['completed'];
}

// Function to check if previous lesson is completed
function isPreviousLessonCompleted($lesson_index, $user_progress, $lessons_array) {
    if ($lesson_index === 0) return true;
    
    $previous_lesson = $lessons_array[$lesson_index - 1];
    $previous_lesson_id = $previous_lesson['lesson_id'];
    
    if (!isset($user_progress[$previous_lesson_id])) return false;
    
    foreach ($user_progress[$previous_lesson_id] as $completed) {
        if (!$completed) return false;
    }
    
    return true;
}

// Store all lessons for progress checking
$all_lessons = [];
while ($lesson = $lessons_result->fetch_assoc()) {
    $all_lessons[] = $lesson;
}
$lessons_result->data_seek(0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['title']); ?> - Course Content</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/plyr@3.7.8/dist/plyr.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/css/student_dashboard.css">
    <!-- <link rel="stylesheet" href="../../css/style.css"> -->
    <link rel="stylesheet" href="../../css/customBoorstrap.css">

    <style>
        .lesson-locked {
            background-color: #f8f9fa;
            cursor: not-allowed;
        }
        .lesson-locked .accordion-button {
            pointer-events: none;
            color: #6c757d;
        }
        .lock-icon {
            margin-left: auto;
            margin-right: 1rem;
        }
        .progress-badge {
            margin-left: auto;
            margin-right: 1rem;
        }
        .course-video {
            width: 100%;
            border-radius: 8px;
        }
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
        .quiz-info {
            padding: 15px;
            border-radius: 8px;
            background-color: #f8f9fa;
            margin-top: 15px;
        }
        .quiz-info h5 {
            color: #2C3E50;
            margin-bottom: 10px;
        }
        .quiz-info .badge {
            font-size: 0.85rem;
            padding: 8px 12px;
        }
        .card.bg-light {
            opacity: 0.8;
        }
        .plyr--video .plyr__controls {
            padding-bottom: 40px; /* Make room for quality selector */
        }

        .plyr__menu__container {
            min-width: 200px;
        }

        .plyr__menu__container .plyr__control {
            padding: 8px 12px;
        }

        .plyr__menu__container [data-plyr='quality'] {
            font-weight: bold;
        }

        .plyr--full-ui input[type=range] {
            color: #3498db;
        }
        @media (max-width: 768px) {

        /* Styles for the fixed sidebar */
        #sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 70vw;
            height: 100vh;
            background-color: #2c3e50; /* Sidebar background color */
            z-index: 1000; /* Ensure sidebar is above other content */
            transform: translateX(-100%); /* Initially hidden */
            transition: transform 0.3s ease; /* Smooth transition */
        }
        #sidebar.show {
            transform: translateX(0); /* Show sidebar */
        }
        .main-content.hidden {
            display: none; /* Hide main content when sidebar is open */
        }
    }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-auto sidebar" id="sidebar">
                <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 min-vh-100">
                    <a href="#" class="d-flex align-items-center pb-3 mb-md-1 mt-md-3 me-md-auto text-white text-decoration-none">
                    <span class="fs-5 fw-bolder" style="display: flex;align-items:center;"><img height="35px" src="../../Images/Logos/edutechLogo.png" alt="">&nbsp; GD Edu Tech</span>
                    </a>
                    <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100" id="menu">
                        <li class="w-100">
                            <a href="../" class="nav-link text-white">
                                <i class="bi bi-speedometer2 me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="./" class="nav-link  active">
                                <i class="bi bi-book me-2"></i> My Courses
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Categories/" class="nav-link text-white">
                                <i class="bi bi-grid me-2"></i> Categories
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Schedule" class="nav-link text-white">
                                <i class="bi bi-calendar-event me-2"></i> Schedule
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Messages" class="nav-link text-white">
                                <i class="bi bi-chat-dots me-2"></i> Messages
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Profile/" class="nav-link text-white">
                                <i class="bi bi-person me-2"></i> Profile
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
            <div class="col-auto d-md-none">
                <button class="btn btn-primary" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
            </div>

            <!-- Main Content -->
            <div class="col py-3">
                <div class="container-fluid">
                    <!-- Breadcrumb -->
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="./">My Courses</a></li>
                            <li class="breadcrumb-item active"><?php echo htmlspecialchars($course['title']); ?></li>
                        </ol>
                    </nav>

                    <div class="row">
                        <!-- Course Info -->
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <img src="../../uploads/course_uploads/thumbnails/<?php echo htmlspecialchars($course['thumbnail']); ?>" 
                                     class="card-img-top" alt="Course Thumbnail">
                                <div class="card-body">
                                    <h4 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h4>
                                    <p class="card-text"><?php echo htmlspecialchars($course['description']); ?></p>
                                    <div class="progress mb-3">
                                        <?php
                                        // Calculate overall course progress
                                        $total_completed = 0;
                                        $total_videos = 0;
                                        foreach ($all_lessons as $lesson) {
                                            $total_videos += $lesson['video_count'];
                                            if (isset($user_progress[$lesson['lesson_id']])) {
                                                $total_completed += array_sum($user_progress[$lesson['lesson_id']]);
                                            }
                                        }
                                        $overall_progress = $total_videos > 0 ? ($total_completed / $total_videos) * 100 : 0;
                                        ?>
                                        <div class="progress-bar bg-success" 
                                             role="progressbar" 
                                             style="width: <?php echo $overall_progress; ?>%"
                                             aria-valuenow="<?php echo $overall_progress; ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                            <?php echo round($overall_progress); ?>%
                                        </div>
                                    </div>
                                    <ul class="list-unstyled">
                                        <li><i class="bi bi-folder me-2"></i><?php echo htmlspecialchars($course['category_name']); ?></li>
                                        <li><i class="bi bi-translate me-2"></i><?php echo htmlspecialchars($course['language']); ?></li>
                                        <li><i class="bi bi-bar-chart me-2"></i><?php echo ucfirst(htmlspecialchars($course['level'])); ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Course Content -->
                        <div class="col-md-8">
                            <div class="accordion" id="courseCurriculum">
                                <?php 
                                $lesson_index = 0;
                                while ($lesson = $lessons_result->fetch_assoc()): 
                                    $lesson_index++;
                                    $is_unlocked = isPreviousLessonCompleted($lesson_index - 1, $user_progress, $all_lessons);
                                    
                                    // Fetch videos for this lesson
                                    $videos_query = "SELECT * FROM Videos WHERE lesson_id = ? ORDER BY video_order";
                                    $videos_stmt = $conn->prepare($videos_query);
                                    $videos_stmt->bind_param('i', $lesson['lesson_id']);
                                    $videos_stmt->execute();
                                    $videos_result = $videos_stmt->get_result();
                                    
                                    // Calculate lesson progress
                                    $total_videos = $videos_result->num_rows;
                                    $completed_videos = isset($user_progress[$lesson['lesson_id']]) ? 
                                        array_sum($user_progress[$lesson['lesson_id']]) : 0;
                                    $progress_percentage = $total_videos > 0 ? 
                                        round(($completed_videos / $total_videos) * 100) : 0;
                                ?>
                                    <div class="accordion-item <?php echo !$is_unlocked ? 'lesson-locked' : ''; ?>">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button <?php echo $lesson_index > 1 ? 'collapsed' : ''; ?>" 
                                                    type="button" 
                                                    data-bs-toggle="collapse" 
                                                    data-bs-target="#lesson<?php echo $lesson['lesson_id']; ?>"
                                                    <?php echo !$is_unlocked ? 'disabled' : ''; ?>>
                                                Lesson <?php echo $lesson_index; ?>: <?php echo htmlspecialchars($lesson['title']); ?>
                                                <?php if (!$is_unlocked): ?>
                                                    <span class="lock-icon"><i class="bi bi-lock"></i></span>
                                                <?php else: ?>
                                                    <span class="progress-badge badge bg-<?php echo $progress_percentage == 100 ? 'success' : 'primary'; ?>">
                                                        <?php echo $progress_percentage; ?>% Complete
                                                    </span>
                                                <?php endif; ?>
                                            </button>
                                        </h2>
                                        <div id="lesson<?php echo $lesson['lesson_id']; ?>" 
                                             class="accordion-collapse collapse <?php echo $lesson_index == 1 ? 'show' : ''; ?>">
                                            <div class="accordion-body">
                                                <p><?php echo htmlspecialchars($lesson['description']); ?></p>
                                                
                                                <?php while ($video = $videos_result->fetch_assoc()): ?>
                                                <div class="mb-4 video-container" 
                                                     data-video-id="<?php echo $video['video_id']; ?>"
                                                     data-lesson-id="<?php echo $lesson['lesson_id']; ?>">
                                                    <h5><?php echo htmlspecialchars($video['title']); ?></h5>
                                                    <p class="text-muted"><?php echo $video['description']; ?></p>
                                                    <video controls crossorigin playsinline
                                                           class="course-video"
                                                           <?php echo !$is_unlocked ? 'disabled' : ''; ?>>
                                                        <?php
                                                        // Add multiple quality sources
                                                        $video_path = '../../uploads/course_uploads/course_videos/';
                                                        $video_filename = pathinfo($video['video_url'], PATHINFO_FILENAME);
                                                        $qualities = ['1080', '720', '480', '360'];
                                                        
                                                        foreach ($qualities as $quality) {
                                                            $quality_file = $video_path . $video_filename . '_' . $quality . 'p.mp4';
                                                            if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $quality_file)) {
                                                                echo "<source src=\"$quality_file\" size=\"$quality\" type=\"video/mp4\" />\n";
                                                            }
                                                        }
                                                        // Fallback to original video if no converted qualities exist
                                                        echo "<source src=\"" . $video_path . $video['video_url'] . "\" type=\"video/mp4\" />\n";
                                                        ?>
                                                    </video>
                                                    <?php if (isset($user_progress[$lesson['lesson_id']][$video['video_id']]) && 
                                                              $user_progress[$lesson['lesson_id']][$video['video_id']]): ?>
                                                        <div class="text-end mt-2">
                                                            <span class="badge bg-success">
                                                                <i class="bi bi-check-circle"></i> Completed
                                                            </span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <?php endwhile; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                            <div class="mt-4">
                                <div class="card <?php echo $overall_progress < 100 ? 'bg-light' : ''; ?>">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <h4 class="card-title mb-0">
                                                <i class="bi bi-journal-check me-2"></i>Course Assessment
                                            </h4>
                                            <?php if ($overall_progress < 100): ?>
                                                <span class="badge bg-warning">
                                                    <i class="bi bi-lock-fill me-1"></i>Locked
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-success">
                                                    <i class="bi bi-unlock-fill me-1"></i>Unlocked
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <?php if ($overall_progress < 100): ?>
                                            <p class="text-muted mb-0">
                                                Complete all lessons to unlock the course assessment.
                                                Current progress: <?php echo round($overall_progress); ?>%
                                            </p>
                                        <?php else: ?>
                                            <?php
                                            // Fetch quiz details and enrollment status
                                            $quiz_query = "SELECT q.*, 
                                                         (SELECT COUNT(*) FROM Questions WHERE quiz_id = q.quiz_id) as question_count,
                                                         e.assessment_status
                                                         FROM Quizzes q 
                                                         JOIN Enrollments e ON q.course_id = e.course_id
                                                         WHERE q.course_id = ? AND e.student_id = ?";
                                            $quiz_stmt = $conn->prepare($quiz_query);
                                            $quiz_stmt->bind_param('ii', $course_id, $user_id);
                                            $quiz_stmt->execute();
                                            $quiz_result = $quiz_stmt->get_result();
                                            ?>
                                            
                                            <?php if ($quiz_result->num_rows > 0): ?>
                                                <?php while ($quiz = $quiz_result->fetch_assoc()): ?>
                                                    <div class="quiz-info">
                                                        <h5><?php echo htmlspecialchars($quiz['title']); ?></h5>
                                                        <p class="text-muted"><?php echo htmlspecialchars($quiz['instructions']); ?></p>
                                                        <div class="d-flex align-items-center gap-3">
                                                            <span class="badge bg-info">
                                                                <i class="bi bi-question-circle me-1"></i>
                                                                <?php echo $quiz['question_count']; ?> Questions
                                                            </span>
                                                            <span class="badge bg-primary">
                                                                <i class="bi bi-award me-1"></i>
                                                                <?php echo $quiz['total_marks']; ?> Marks
                                                            </span>
                                                            <?php if ($quiz['assessment_status'] === 'completed'): ?>
                                                                <span class="badge bg-success">
                                                                    <i class="bi bi-check-circle me-1"></i>Completed
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <?php if ($quiz['assessment_status'] === 'completed'): ?>
                                                            <a href="view_certificate.php?course_id=<?php echo $course_id; ?>" 
                                                               class="btn btn-success mt-3">
                                                                <i class="bi bi-award me-2"></i>View Certificate
                                                            </a>
                                                        <?php else: ?>
                                                            <a href="take_quiz.php?quiz_id=<?php echo $quiz['quiz_id']; ?>" 
                                                               class="btn btn-primary mt-3">
                                                                <i class="bi bi-pencil-square me-2"></i>Start Assessment
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <p class="text-muted mb-0">No assessment available for this course yet.</p>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/plyr@3.7.8/dist/plyr.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const players = Plyr.setup('.course-video', {
                controls: [
                    'play-large',
                    'play',
                    'progress',
                    'current-time',
                    'mute',
                    'volume',
                    'settings',
                    'fullscreen'
                ],
                settings: ['quality', 'speed'],
                quality: {
                    default: 720,
                    options: [1080, 720, 480, 360],
                    forced: true,
                    onChange: (quality) => {
                        console.log('Quality changed to:', quality);
                    }
                }
            });
            
            players.forEach(player => {
                const container = player.elements.container.closest('.video-container');
                const videoId = container.dataset.videoId;
                const lessonId = container.dataset.lessonId;
                
                player.on('ended', () => {
                    fetch('update_progress.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            video_id: videoId,
                            lesson_id: lessonId,
                            course_id: <?php echo $course_id; ?>,
                            completed: true
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                        }
                    });
                });
            });

            const sidebar = document.getElementById('sidebar');
            const toggleButton = document.getElementById('sidebarToggle');
            const mainContent = document.getElementById('mainContent');

            toggleButton.addEventListener('click', function() {
                sidebar.classList.toggle('show'); // Toggle sidebar visibility
                mainContent.classList.toggle('hidden'); // Hide main content when sidebar is open
            });

            // Close sidebar when clicking outside of it
            document.addEventListener('click', function(event) {
                if (!sidebar.contains(event.target) && !toggleButton.contains(event.target) && sidebar.classList.contains('show')) {
                    sidebar.classList.remove('show'); // Hide sidebar
                    mainContent.classList.remove('hidden'); // Show main content
                }
            });
        });
    </script>
</body>
</html>
