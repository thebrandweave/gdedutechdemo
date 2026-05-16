<?php
session_start();

// Check if user is logged in and is staff
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Staff') {
    header('Location: ../staff_login.php');
    exit();
}

// Get staff details from session
$staff_name = $_SESSION['username'] ?? 'Staff';

require_once '../../Configurations/config.php';

if (!isset($_GET['course_id'])) {
    header("Location: ./");
    exit();
}

$course_id = intval($_GET['course_id']);
$user_id = $_SESSION['user_id'];

// Add check to ensure staff can only view their own courses
$course_query = "SELECT c.*, cat.name AS category_name 
                 FROM Courses c 
                 JOIN Categories cat ON c.category_id = cat.category_id 
                 WHERE c.course_id = ? AND c.created_by = ?";
$course_stmt = mysqli_prepare($conn, $course_query);
mysqli_stmt_bind_param($course_stmt, 'ii', $course_id, $user_id);
mysqli_stmt_execute($course_stmt);
$course_result = mysqli_stmt_get_result($course_stmt);
$course = mysqli_fetch_assoc($course_result);

if (!$course) {
    // If course doesn't exist or doesn't belong to the staff member
    header("Location: ./");
    exit();
}

// Fetch lessons with their videos
$lessons_query = "SELECT l.*, 
                         (SELECT COUNT(*) FROM Videos v WHERE v.lesson_id = l.lesson_id) as video_count
                  FROM Lessons l 
                  WHERE l.course_id = ? 
                  ORDER BY l.lesson_order";
$lessons_stmt = mysqli_prepare($conn, $lessons_query);
mysqli_stmt_bind_param($lessons_stmt, 'i', $course_id);
mysqli_stmt_execute($lessons_stmt);
$lessons_result = mysqli_stmt_get_result($lessons_stmt);

// Fetch user's progress for this course
$progress_query = "SELECT lesson_id, video_id, completed 
                  FROM UserProgress 
                  WHERE user_id = ? AND course_id = ?";
$progress_stmt = mysqli_prepare($conn, $progress_query);
mysqli_stmt_bind_param($progress_stmt, 'ii', $user_id, $course_id);
mysqli_stmt_execute($progress_stmt);
$progress_result = mysqli_stmt_get_result($progress_stmt);

$user_progress = [];
while ($progress = mysqli_fetch_assoc($progress_result)) {
    $user_progress[$progress['lesson_id']][$progress['video_id']] = $progress['completed'];
}

// Function to check if previous lesson is completed
function isPreviousLessonCompleted($lesson_index, $user_progress, $lessons_array) {
    if ($lesson_index === 0) return true;
    
    $previous_lesson = $lessons_array[$lesson_index - 1];
    $previous_lesson_id = $previous_lesson['lesson_id'];
    
    if (!isset($user_progress[$previous_lesson_id])) return false;
    
    // Check if all videos in previous lesson are completed
    foreach ($user_progress[$previous_lesson_id] as $completed) {
        if (!$completed) return false;
    }
    
    return true;
}

// Store all lessons in array for progress checking
$all_lessons = [];
mysqli_data_seek($lessons_result, 0);
while ($lesson = mysqli_fetch_assoc($lessons_result)) {
    $all_lessons[] = $lesson;
}

// Reset result pointer
mysqli_data_seek($lessons_result, 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($course['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/plyr@3.7.8/dist/plyr.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../../adminPanel/css/style.css">
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
                        <img height="35px" src="../../staffPanel/images/edutechLogo.png" alt="">&nbsp; GD Edu Tech
                    </span>
                </a>
                <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100" id="menu">
                    <li class="w-100">
                        <a href="../index.php" class="nav-link ">
                            <i class="bi bi-speedometer2 me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="w-100">
                        <a href="./" class="nav-link active">
                            <i class="bi bi-book me-2"></i> Courses
                        </a>
                    </li>
                    <li class="w-100">
                        <a href="../Quiz/" class="nav-link">
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
            <div class="container mt-5">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <img src="../../uploads/course_uploads/thumbnails/<?php echo htmlspecialchars($course['thumbnail']); ?>" class="card-img-top" alt="Course Thumbnail">
                            <div class="card-body">
                                <h1 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h1>
                                <p class="card-text"><?php echo htmlspecialchars($course['description']); ?></p>
                                <ul class="list-unstyled">
                                    <li><strong>Category:</strong> <?php echo htmlspecialchars($course['category_name']); ?></li>
                                    <li><strong>Language:</strong> <?php echo htmlspecialchars($course['language']); ?></li>
                                    <li><strong>Level:</strong> <?php echo ucfirst(htmlspecialchars($course['level'])); ?></li>
                                    <li><strong>Price:</strong> ₹<?php echo number_format($course['price'], 2); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <h2>Course Curriculum</h2>

                        <div class="accordion" id="courseCurriculum">
                            <?php 
                            $lesson_index = 0;
                            while ($lesson = mysqli_fetch_assoc($lessons_result)): 
                                $lesson_index++;
                                $is_unlocked = isPreviousLessonCompleted($lesson_index - 1, $user_progress, $all_lessons);
                                
                                // Fetch videos for this lesson
                                $videos_query = "SELECT * FROM Videos WHERE lesson_id = ? ORDER BY video_order";
                                $videos_stmt = mysqli_prepare($conn, $videos_query);
                                mysqli_stmt_bind_param($videos_stmt, 'i', $lesson['lesson_id']);
                                mysqli_stmt_execute($videos_stmt);
                                $videos_result = mysqli_stmt_get_result($videos_stmt);
                                
                                // Calculate lesson progress
                                $total_videos = mysqli_num_rows($videos_result);
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
                                            <span class="lock-icon">🔒</span>
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
                                        
                                        <?php while ($video = mysqli_fetch_assoc($videos_result)): ?>
                                        <div class="mb-3 video-container" 
                                             data-video-id="<?php echo $video['video_id']; ?>"
                                             data-lesson-id="<?php echo $lesson['lesson_id']; ?>">
                                            <h5><?php echo htmlspecialchars($video['title']); ?></h5>
                                            <p><?php echo htmlspecialchars($video['description']); ?></p>
                                            <video controls crossorigin playsinline
                                                   class="course-video"
                                                   <?php echo !$is_unlocked ? 'disabled' : ''; ?>>
                                                <source src="../../uploads/course_uploads/course_videos/<?php echo htmlspecialchars($video['video_url']); ?>" type="video/mp4">
                                                <?php if (!empty($video['subtitle_url'])): ?>
                                                <track kind="subtitles" 
                                                       label="English" 
                                                       src="../../uploads/course_uploads/course_subtitles/<?php echo htmlspecialchars($video['subtitle_url']); ?>" 
                                                       srclang="en" 
                                                       default>
                                                <?php endif; ?>
                                            </video>
                                        </div>
                                        <?php endwhile; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Rest of the HTML -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/plyr@3.7.8/dist/plyr.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Initialize Plyr video players
    const players = Plyr.setup('.course-video');
    
    // Track video progress
    players.forEach(player => {
        const container = player.elements.container.closest('.video-container');
        const videoId = container.dataset.videoId;
        const lessonId = container.dataset.lessonId;
        
        player.on('ended', () => {
            // Mark video as completed
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
                    // Refresh page to update progress and unlock next lesson if applicable
                    window.location.reload();
                }
            });
        });
    });
});
</script>
</body>
</html>