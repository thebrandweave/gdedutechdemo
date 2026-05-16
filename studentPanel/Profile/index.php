<?php
session_start();
require_once __DIR__ . '../../../vendor/autoload.php';
require_once '../../Configurations/config.php';

// Authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$user_query = "SELECT * FROM Users WHERE user_id = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

// Fetch learning statistics
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM Enrollments WHERE student_id = ?) as total_courses,
    (SELECT COUNT(*) FROM Enrollments WHERE student_id = ? AND completion_status = 'completed') as completed_courses,
    (SELECT COUNT(*) FROM Certificates WHERE student_id = ?) as earned_certificates,
    (SELECT COALESCE(AVG(rating), 0) FROM Reviews WHERE student_id = ?) as avg_rating,
    (SELECT SUM(progress) FROM Enrollments WHERE student_id = ?) as total_progress";
$stats_stmt = $conn->prepare($stats_query);
$stats_stmt->bind_param("iiiii", $user_id, $user_id, $user_id, $user_id, $user_id);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();

// Fetch recent activities
$activities_query = "SELECT * FROM recent_activities 
                    WHERE user_id = ? 
                    ORDER BY activity_timestamp DESC 
                    LIMIT 5";
$activities_stmt = $conn->prepare($activities_query);
$activities_stmt->bind_param("i", $user_id);
$activities_stmt->execute();
$activities_result = $activities_stmt->get_result();

// Fetch certificates
$certificates_query = "SELECT c.*, co.title as course_title 
                      FROM Certificates c
                      JOIN Courses co ON c.course_id = co.course_id
                      WHERE c.student_id = ?
                      ORDER BY c.issue_date DESC";
$certificates_stmt = $conn->prepare($certificates_query);
$certificates_stmt->bind_param("i", $user_id);
$certificates_stmt->execute();
$certificates_result = $certificates_stmt->get_result();

// Fetch enrolled courses
$courses_query = "SELECT 
    c.*, 
    e.progress,
    e.completion_status,
    cat.name as category_name,
    (SELECT COUNT(*) FROM Videos v 
     JOIN Lessons l ON v.lesson_id = l.lesson_id 
     WHERE l.course_id = c.course_id) as total_videos,
    (SELECT COUNT(DISTINCT up.video_id) 
     FROM UserProgress up 
     JOIN Lessons l ON up.lesson_id = l.lesson_id 
     WHERE l.course_id = c.course_id 
     AND up.user_id = ? 
     AND up.completed = 1) as completed_videos
    FROM Courses c
    JOIN Enrollments e ON c.course_id = e.course_id
    LEFT JOIN Categories cat ON c.category_id = cat.category_id
    WHERE e.student_id = ?
    ORDER BY e.created_at DESC";
$courses_stmt = $conn->prepare($courses_query);
$courses_stmt->bind_param("ii", $user_id, $user_id);
$courses_stmt->execute();
$courses_result = $courses_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile - GD Edu Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/css/student_dashboard.css">
    <link rel="stylesheet" href="../../css/customBoorstrap.css">
            
    <link rel="icon" type="image/png" href="../../Images/Logos/GD_Only_logo.png">
    <link rel="shortcut icon" href="../../Images/Logos/GD_Only_logo.png">
    <style>
        .profile-container {
            padding: 30px;
        }

        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            height: 100%;
            transition: transform 0.3s;
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
                            <a href="../Messages/" class="nav-link text-white">
                                <i class="bi bi-chat-dots me-2"></i> Messages
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="./" class="nav-link active">
                                <i class="bi bi-person me-2"></i> Profile
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Resources/index.php" class="nav-link text-white">
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
            <div class="col py-3">
                <div class="profile-container">
                    <!-- Profile Header -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <img src="<?php echo $user['profile_image'] ?: '../../assets/images/default-avatar.png'; ?>"
                                        alt="Profile"
                                        class="rounded-circle"
                                        style="width: 100px; height: 100px; object-fit: cover;">
                                </div>
                                <div class="col">
                                    <h2><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
                                    <p class="text-muted mb-0">
                                        <i class="bi bi-envelope me-2"></i><?php echo htmlspecialchars($user['email']); ?>
                                    </p>
                                    <p class="text-muted mb-0">
                                        <i class="bi bi-calendar me-2"></i>Joined <?php echo date('F Y', strtotime($user['date_joined'])); ?>
                                    </p>
                                </div>
                                <div class="col-auto">
                                    <a href="edit_profile.php" class="btn btn-primary">
                                        <i class="bi bi-pencil-square me-2"></i>Edit Profile
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="row g-4 mb-4">
                        <div class="col-md-3">
                            <div class="stats-card">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-book me-2" style="font-size: 1.5rem; color: #4CAF50;"></i>
                                    <h3><?php echo $stats['total_courses']; ?></h3>
                                </div>
                                <p class="text-muted mb-0">Enrolled Courses</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-check-circle me-2" style="font-size: 1.5rem; color: #2196F3;"></i>
                                    <h3><?php echo $stats['completed_courses']; ?></h3>
                                </div>
                                <p class="text-muted mb-0">Completed Courses</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-award me-2" style="font-size: 1.5rem; color: #FF9800;"></i>
                                    <h3><?php echo $stats['earned_certificates']; ?></h3>
                                </div>
                                <p class="text-muted mb-0">Certificates Earned</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-star me-2" style="font-size: 1.5rem; color: #9C27B0;"></i>
                                    <h3><?php echo number_format($stats['avg_rating'] ?? 0, 1); ?></h3>
                                </div>
                                <p class="text-muted mb-0">Average Rating</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Course Progress -->
                        <div class="col-md-8">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <h5 class="card-title mb-4">Course Progress</h5>
                                    <?php while ($course = $courses_result->fetch_assoc()): ?>
                                        <div class="course-progress">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h6 class="mb-0"><?php echo htmlspecialchars($course['title']); ?></h6>
                                                <span class="badge bg-<?php echo $course['completion_status'] === 'completed' ? 'success' : 'primary'; ?>">
                                                    <?php echo ucfirst($course['completion_status']); ?>
                                                </span>
                                            </div>
                                            <div class="progress mb-3">
                                                <div class="progress-bar"
                                                    role="progressbar"
                                                    style="width: <?php echo $course['progress']; ?>%"
                                                    aria-valuenow="<?php echo $course['progress']; ?>"
                                                    aria-valuemin="0"
                                                    aria-valuemax="100">
                                                    <?php echo $course['progress']; ?>%
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between text-muted small">
                                                <span><?php echo $course['completed_videos']; ?>/<?php echo $course['total_videos']; ?> videos completed</span>
                                                <span><?php echo htmlspecialchars($course['category_name']); ?></span>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Certificates and Activities -->
                        <div class="col-md-4">
                            <!-- Certificates -->
                            <div class="card mb-4">
                                <div class="card-body">
                                    <h5 class="card-title mb-4">Certificates</h5>
                                    <?php while ($cert = $certificates_result->fetch_assoc()): ?>
                                        <div class="certificate-card">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($cert['course_title']); ?></h6>
                                                    <p class="text-muted small mb-0">
                                                        Issued on <?php echo date('M d, Y', strtotime($cert['issue_date'])); ?>
                                                    </p>
                                                </div>
                                                <a href="<?php echo htmlspecialchars($cert['certificate_url']); ?>"
                                                    class="btn btn-sm btn-outline-primary"
                                                    target="_blank">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>

                            <!-- Recent Activities -->
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title mb-4">Recent Activities</h5>
                                    <?php while ($activity = $activities_result->fetch_assoc()): ?>
                                        <div class="activity-item">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-circle-fill me-2 text-<?php
                                                                                        echo $activity['activity_status'] === 'completed' ? 'success' : 'primary';
                                                                                        ?>" style="font-size: 8px;"></i>
                                                <div>
                                                    <p class="mb-0"><?php echo htmlspecialchars($activity['activity_description']); ?></p>
                                                    <small class="text-muted">
                                                        <?php echo date('M d, Y H:i', strtotime($activity['activity_timestamp'])); ?>
                                                    </small>
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
    </script>
</body>

</html>