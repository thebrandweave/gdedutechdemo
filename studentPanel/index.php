<?php
session_start();

require_once  '../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Database connection
require_once '../Configurations/config.php';
require_once '../Configurations/functions.php';

$jwtSecretKey = "your_secret_key_here";

// Check if user is logged in and is a student via session
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    // If no session, check for JWT token
    if (!isset($_COOKIE['auth_token'])) {
        header("Location: login.php");
        exit();
    }

    try {
        $jwt = $_COOKIE['auth_token'];
        $decoded = JWT::decode($jwt, new Key($jwtSecretKey, 'HS256'));

        // Recreate session from JWT token
        $_SESSION['user_id'] = $decoded->user_id;
        $_SESSION['username'] = $decoded->username;
        $_SESSION['role'] = $decoded->role;
    } catch (Exception $e) {
        // Clear any invalid cookie
        setcookie('auth_token', '', time() - 3600, '/');
        session_destroy();
        header("Location: login.php");
        exit();
    }
}

// Get student details from session
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Additional security check
if ($role !== 'student') {
    header("Location: login.php");
    exit();
}

// Optional: Periodic session regeneration for security
if (!isset($_SESSION['last_regeneration']) || (time() - $_SESSION['last_regeneration']) > 300) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Fetch student details from the users table
$user_query = "SELECT email, first_name, last_name FROM Users WHERE user_id = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();

if ($user_result->num_rows > 0) {
    $user_data = $user_result->fetch_assoc();
    // Store email, first name, and last name in session
    $_SESSION['email'] = $user_data['email'];
    $_SESSION['first_name'] = $user_data['first_name'];
    $_SESSION['last_name'] = $user_data['last_name'];
} else {
    // Handle case where user data is not found
    // You might want to redirect to an error page or logout
}

// Fetch user statistics
$stats_query = "
    SELECT 
        (SELECT COUNT(*) FROM Enrollments WHERE student_id = ?) AS enrolled_courses,
        (SELECT SUM(progress) FROM Enrollments WHERE student_id = ?) AS total_learning_hours,
        (SELECT COUNT(*) FROM Enrollments WHERE student_id = ? AND completion_status = 'pending') AS pending_assignments,
        (SELECT COUNT(*) FROM Certificates WHERE student_id = ?) AS certificates
";
$stats_stmt = $conn->prepare($stats_query);
$stats_stmt->bind_param("iiii", $user_id, $user_id, $user_id, $user_id);
$stats_stmt->execute();
$stats_result = $stats_stmt->get_result()->fetch_assoc();

// Fetch ongoing courses
$ongoing_courses_query = "
    SELECT 
        c.course_id,
        c.title, 
        c.thumbnail,
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
    WHERE e.student_id = ? 
    LIMIT 2
";
$ongoing_courses_stmt = $conn->prepare($ongoing_courses_query);
$ongoing_courses_stmt->bind_param("ii", $user_id, $user_id);
$ongoing_courses_stmt->execute();
$ongoing_courses_result = $ongoing_courses_stmt->get_result();

// Fetch recommended courses
$recommended_courses_query = "
    SELECT 
        c.course_id,
        c.title, 
        c.description,
        c.thumbnail,
        c.course_type,
        (SELECT name FROM Categories cat WHERE cat.category_id = c.category_id) AS category
    FROM Courses c
    WHERE c.status = 'published' 
    AND c.course_id NOT IN (
        SELECT course_id FROM Enrollments WHERE student_id = ?
    )
 
";
$recommended_courses_stmt = $conn->prepare($recommended_courses_query);
$recommended_courses_stmt->bind_param("i", $user_id);
$recommended_courses_stmt->execute();
$recommended_courses_result = $recommended_courses_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - GD Edu Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/student_dashboard.css">
        
    <link rel="icon" type="image/png" href="../Images/Logos/GD_Only_logo.png">
    <link rel="shortcut icon" href="../Images/Logos/GD_Only_logo.png">
    <style>
        /* Custom styles for the sidebar */
        .sidebar {
            transition: all 0.3s;
            position: fixed;
            z-index: 1050;
            /* Adjusted z-index for mobile */
            height: 100vh;
            left: -250px;
            /* Hide sidebar off-screen */
            background-color: #2c3e50;
            /* Dark background for sidebar */
        }

        .sidebar.show {
            left: 0;
            /* Show sidebar */
        }

        .topbar {
            position: fixed;
            width: 100%;
            z-index: 1001;
            display: block;
            background-color: #f8f9fa;
            /* Light background for the top bar */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .container-fluid {
            margin-top: 56px;
            /* Adjust for the height of the top bar */
            padding: 20px;
            /* Add some padding for content */
            transition: margin-left 0.3s ease;
            /* Smooth transition for margin */
        }

        .container-fluid.shifted {
            margin-left: 250px;
            /* Shift content when sidebar is open */
        }

        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                /* Keep sidebar fixed */
                top: 0;
                /* Align to the top */
                left: -250px;
                /* Hide sidebar off-screen */
                width: 250px;
                /* Fixed width for sidebar */
                height: 100vh;
                /* Full height */
                z-index: 1050;
                /* Ensure it overlays content */
                background-color: #343a40;
                /* Dark background for sidebar */
                transition: left 0.3s ease;
                /* Smooth transition */
            }

            .sidebar.show {
                left: 0;
                /* Show sidebar */
            }

            .sidebar-overlay {
                display: none;
                /* Initially hidden */
                position: fixed;
                /* Fixed position for overlay */
                top: 0;
                /* Align to the top */
                left: 0;
                /* Align to the left */
                width: 100%;
                /* Full width */
                height: 100%;
                /* Full height */
                background: rgba(0, 0, 0, 0.5);
                /* Semi-transparent background */
                z-index: 1040;
                /* Ensure it overlays content */
            }

            .sidebar-overlay.show {
                display: block;
                /* Show overlay when sidebar is open */
            }

            .content {
                margin-left: 0;
                /* Reset margin for mobile */
                transition: margin-left 0.3s ease;
                /* Smooth transition for margin */
            }
        }

        @media (min-width: 769px) {
            .sidebar-toggle {
                display: none;
                /* Hide toggle button on desktop */
            }

            .sidebar {
                position: static;
                /* Keep sidebar in its original position on desktop */
                z-index: auto;
                /* Reset z-index */


            }

            .sideBarInner {
                position: fixed !important;
                bottom: 0 !important;
                background-color: #2c3e50 !important;
                width: 250px;
                left: 0;
            }
        }

        /* Flexbox layout for sidebar and content */
        .layout {
            display: flex;
        }

        .sidebar {
            width: 250px;
            /* Fixed width for sidebar */
        }

        .content {
            flex-grow: 1;
            /* Allow content to take remaining space */
        }

        .course-card .card-text {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            min-height: 4.5em;
            /* Approximately 3 lines of text */
        }

        .course-card {
            height: 100%;
            transition: transform 0.2s;
        }

        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .course-card .card-img-top {
            height: 200px;
            object-fit: cover;
        }

        @media (min-width:800px) {
            .topbar {
                display: none !important;
            }

        }
    </style>
</head>

<body>
    <div class="topbar d-flex justify-content-between align-items-center p-2">
        <button class="btn btn-outline-secondary sidebar-toggle" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        <span class="fw-bold"></span>
    </div>

    <div class="layout">
        <div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 sidebar" id="sidebar">
            <div class="sideBarInner bg-dark">
                <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 min-vh-100">
                    <div class="d-flex w-100 justify-content-between align-items-center">
                        <a href="#" class="d-flex align-items-center pb-3 mb-md-1 mt-md-3 me-md-auto text-white text-decoration-none">
                            <span class="fs-5 fw-bolder" style="display: flex;align-items:center;">
                                <img height="35px" src="../Images/Logos/edutechLogo.png" alt="">&nbsp; GD Edu Tech
                            </span>
                        </a>

                    </div>
                    <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100 sidebar-menu" id="menu">
                        <li class="w-100">
                            <a href="./" class="nav-link active">
                                <i class="bi bi-speedometer2 me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="./MyCourses/" class="nav-link text-white">
                                <i class="bi bi-book me-2"></i> My Courses
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="./Categories/" class="nav-link text-white">
                                <i class="bi bi-grid me-2"></i> Categories
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="./Schedule" class="nav-link text-white">
                                <i class="bi bi-calendar-event me-2"></i> Schedule
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="./Messages" class="nav-link text-white">
                                <i class="bi bi-chat-dots me-2"></i> Messages
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="./Profile/" class="nav-link text-white">
                                <i class="bi bi-person me-2"></i> Profile
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="./Resources/" class="nav-link text-white">
                                <i class="bi bi-file-earmark-text me-2"></i> Resources
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="./shop.php" class="nav-link text-white">
                                <i class="bi bi-shop me-2"></i> Shop
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
        </div>

        <div class="sidebar-overlay" id="sidebar-overlay"></div>

        <div class="content">
            <div class="container-fluid">
                <!-- Header -->
                <div class="row mb-4">
                    <div class="col">
                        <h2>Welcome back, <?php echo htmlspecialchars($_SESSION['first_name'] . " " . $_SESSION['last_name']); ?>! 👋</h2>
                        <p class="text-muted">Here's what's happening with your learning journey.</p>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="card stats-card">
                            <div class="card-body">
                                <h5 class="card-title">Enrolled Courses</h5>
                                <h2><?php echo $stats_result['enrolled_courses']; ?></h2>
                                <p class="mb-0"><i class="bi bi-arrow-up"></i> New courses this month</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card stats-card">
                            <div class="card-body">
                                <h5 class="card-title">Assignments</h5>
                                <h2><?php echo $stats_result['pending_assignments']; ?></h2>
                                <p class="mb-0">Pending</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card stats-card">
                            <div class="card-body">
                                <h5 class="card-title">Certificates</h5>
                                <h2><?php echo $stats_result['certificates']; ?></h2>
                                <p class="mb-0">Earned</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Current Progress -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-4">Continue Learning</h5>
                                <div class="row g-4">
                                    <?php while ($course = $ongoing_courses_result->fetch_assoc()): ?>
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center mb-3">
                                                <img src="../uploads/course_uploads/thumbnails/<?php echo htmlspecialchars($course['thumbnail']); ?>"
                                                    class="rounded me-3" style="width: 80px; height: 80px; object-fit: cover;"
                                                    alt="Course">
                                                <div>
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($course['title']); ?></h6>
                                                    <p class="text-muted mb-0">
                                                        <?php echo $course['completed_videos']; ?>/<?php echo $course['total_videos']; ?> videos completed
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="progress mb-3">
                                                <div class="progress-bar bg-success"
                                                    style="width: <?php
                                                                    $progress = $course['total_videos'] > 0 ?
                                                                        ($course['completed_videos'] / $course['total_videos']) * 100 : 0;
                                                                    echo number_format($progress, 0);
                                                                    ?>%">
                                                    <?php echo number_format($progress, 0); ?>%
                                                </div>
                                            </div>
                                            <div class="mt-2">
                                                <a href="./MyCourses/course_content.php?id=<?php echo $course['course_id']; ?>"
                                                    class="btn btn-warning">Continue Learning</a>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recommended Courses -->
                <div class="row">
                    <div class="col-12">
                        <h5 class="mb-4">Recommended Courses</h5>
                    </div>
                    <?php while ($course = $recommended_courses_result->fetch_assoc()): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card course-card">
                                <img src="../uploads/course_uploads/thumbnails/<?php echo htmlspecialchars($course['thumbnail'] ?: './Courses/thumbnails'); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($course['title']); ?>">
                                <div class="card-body">
                                    <span class="badge bg-success mb-2"><?php echo htmlspecialchars($course['category'] ?: 'General'); ?></span>
                                    <h5 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h5>
                                    <p class="card-text text-muted"><?php echo htmlspecialchars($course['description']); ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span><i class="bi bi-clock"></i> Course Type: <?php echo htmlspecialchars($course['course_type'] ?: 'Undefined'); ?></span>
                                        <a href="./MyCourses/course.php?id=<?php echo $course['course_id'] . "&" . rand(10000000, 99999999) . chr(rand(65, 90)); ?>" class="btn btn-primary">Enroll Now</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                <div class="mb-4 d-md-none"></div>
            </div>
        </div>


    </div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.querySelector('.sidebar-toggle');
            const sidebarOverlay = document.getElementById('sidebar-overlay');

            // Only add event listeners if elements exist
            if (sidebarToggle && sidebar && sidebarOverlay) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                    sidebarOverlay.classList.toggle('show');
                    // Prevent content from shifting
                    document.body.style.overflow = sidebar.classList.contains('show') ? 'hidden' : 'auto';
                });

                sidebarOverlay.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    sidebarOverlay.classList.remove('show');
                    // Reset body overflow
                    document.body.style.overflow = 'auto';
                });

                // Close sidebar when a menu item is clicked
                const menuItems = document.querySelectorAll('.sidebar-menu .nav-link');
                menuItems.forEach(item => {
                    item.addEventListener('click', function() {
                        sidebar.classList.remove('show');
                        sidebarOverlay.classList.remove('show');
                        // Reset body overflow
                        document.body.style.overflow = 'auto';
                    });
                });
            }
        });
    </script>
</body>

</html>