<?php
session_start();

require_once '../vendor/autoload.php';

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
        header("Location: login_page.php");
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
        header("Location: login_page.php");
        exit();
    }
}

// Get student details from session
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Additional security check
if ($role !== 'student') {
    header("Location: login_page.php");
    exit();
}

// Optional: Periodic session regeneration for security
if (!isset($_SESSION['last_regeneration']) || (time() - $_SESSION['last_regeneration']) > 300) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Fetch student details from the users table
$user_query = "SELECT email, first_name, last_name, profile_image FROM Users WHERE user_id = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();

$profile_image = null;
if ($user_result->num_rows > 0) {
    $user_data = $user_result->fetch_assoc();
    $_SESSION['email'] = $user_data['email'];
    $_SESSION['first_name'] = $user_data['first_name'];
    $_SESSION['last_name'] = $user_data['last_name'];
    $profile_image = $user_data['profile_image'];
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

$first_name = $_SESSION['first_name'] ?? 'Student';
$last_name = $_SESSION['last_name'] ?? '';
$initial = strtoupper(substr($first_name, 0, 1));
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - GD Edu Tech</title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="../Images/Logos/GD_Only_logo.png">

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

        /* Metric Cards */
        .stats-card {
            border: 1px solid var(--admin-border-color) !important;
            border-radius: 20px !important;
            box-shadow: 0 10px 30px -10px rgba(15, 23, 42, 0.05) !important;
            background: #ffffff;
            transition: all 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 14px 35px -10px rgba(15, 23, 42, 0.1) !important;
        }

        .stats-icon-box {
            width: 54px;
            height: 54px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        /* Course Cards */
        .course-card {
            border: 1px solid var(--admin-border-color) !important;
            border-radius: 20px !important;
            box-shadow: 0 10px 30px -10px rgba(15, 23, 42, 0.05) !important;
            background: #ffffff;
            transition: all 0.3s ease;
            overflow: hidden;
            height: 100%;
        }

        .course-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 14px 35px -10px rgba(15, 23, 42, 0.12) !important;
        }

        .course-card-img {
            height: 180px;
            object-fit: cover;
            width: 100%;
        }

        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>

<body>
    <div class="container-fluid p-0">
        <div class="row g-0 flex-nowrap">

            <!-- Executive Sidebar -->
            <div class="col-auto col-md-3 col-xl-2 px-0 sidebar sticky-top vh-100 overflow-auto hide-scrollbar d-flex flex-column" id="studentSidebar">
                <div class="p-3 border-bottom border-white border-opacity-10 d-flex align-items-center gap-2">
                    <img height="36" src="../Images/Logos/GD_Only_logo.png" alt="GD Logo">
                    <div>
                        <div class="fw-bold text-white fs-6">GD Edu Tech</div>
                        <span class="text-info small fw-semibold">● Student Portal</span>
                    </div>
                </div>

                <ul class="nav nav-pills flex-column mb-auto p-2 w-100">
                    <li class="w-100"><a href="./" class="nav-link active"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                    <li class="w-100"><a href="./MyCourses/" class="nav-link"><i class="bi bi-journal-bookmark"></i> My Courses</a></li>
                    <li class="w-100"><a href="./Categories/" class="nav-link"><i class="bi bi-grid"></i> Categories</a></li>
                    <li class="w-100"><a href="./Schedule/" class="nav-link"><i class="bi bi-calendar-event"></i> Schedule</a></li>
                    <li class="w-100"><a href="./Messages/" class="nav-link"><i class="bi bi-chat-dots"></i> Messages</a></li>
                    <li class="w-100"><a href="./Profile/" class="nav-link"><i class="bi bi-person"></i> Profile</a></li>
                    <li class="w-100"><a href="./Resources/" class="nav-link"><i class="bi bi-file-earmark-text"></i> Resources</a></li>
                    <!-- <li class="w-100"><a href="./shop.php" class="nav-link"><i class="bi bi-shop"></i> Shop</a></li> -->
                </ul>

                <div class="p-3 border-top border-white border-opacity-10 mt-auto">
                    <a href="../logout.php" class="nav-link text-danger justify-content-center m-0">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="col main-content min-vh-100 d-flex flex-column" style="min-width: 0; overflow-x: hidden;">
                
                <!-- Top Header Bar -->
                <div class="bg-white border-bottom px-4 py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <button class="btn btn-light d-md-none border" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar">
                            <i class="bi bi-list fs-5"></i>
                        </button>
                        <div>
                            <h4 class="fw-bold text-dark mb-0">Welcome back, <?php echo htmlspecialchars($first_name . ' ' . $last_name); ?>! 👋</h4>
                            <span class="text-muted small">Here is an overview of your active learning progress</span>
                        </div>
                    </div>

                    <!-- Right User Badge -->
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center gap-2 bg-light border px-3 py-1.5 rounded-pill">
                            <?php if ($profile_image): ?>
                                <img src="../uploads/profiles/<?php echo htmlspecialchars($profile_image); ?>" class="rounded-circle object-fit-cover" style="width: 32px; height: 32px;" alt="Profile" onerror="this.src='../images/default-course.png';">
                            <?php else: ?>
                                <div class="rounded-circle bg-primary text-white fw-bold d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.85rem;">
                                    <?php echo $initial; ?>
                                </div>
                            <?php endif; ?>
                            <span class="fw-semibold text-dark small"><?php echo htmlspecialchars($first_name); ?></span>
                        </div>
                    </div>
                </div>

                <div class="p-4 flex-grow-1">

                    <!-- Metric Cards Row -->
                    <div class="row g-4 mb-4">
                        <!-- Enrolled Courses -->
                        <div class="col-12 col-sm-6 col-xl-4">
                            <div class="card stats-card p-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <span class="text-muted small fw-semibold d-block mb-1">Enrolled Courses</span>
                                        <h2 class="fw-bold text-dark mb-0"><?php echo intval($stats_result['enrolled_courses'] ?? 0); ?></h2>
                                        <span class="text-success small fw-semibold"><i class="bi bi-arrow-up-right me-1"></i>Active Learning</span>
                                    </div>
                                    <div class="stats-icon-box bg-primary bg-opacity-10 text-primary border border-primary-subtle">
                                        <i class="bi bi-journal-bookmark-fill"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pending Assignments -->
                        <div class="col-12 col-sm-6 col-xl-4">
                            <div class="card stats-card p-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <span class="text-muted small fw-semibold d-block mb-1">Pending Tasks</span>
                                        <h2 class="fw-bold text-dark mb-0"><?php echo intval($stats_result['pending_assignments'] ?? 0); ?></h2>
                                        <span class="text-warning small fw-semibold"><i class="bi bi-clock-history me-1"></i>Assignments Pending</span>
                                    </div>
                                    <div class="stats-icon-box bg-warning bg-opacity-10 text-warning border border-warning-subtle">
                                        <i class="bi bi-file-earmark-check-fill"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Certificates Earned -->
                        <div class="col-12 col-sm-6 col-xl-4">
                            <div class="card stats-card p-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <span class="text-muted small fw-semibold d-block mb-1">Certificates Earned</span>
                                        <h2 class="fw-bold text-dark mb-0"><?php echo intval($stats_result['certificates'] ?? 0); ?></h2>
                                        <span class="text-info small fw-semibold"><i class="bi bi-patch-check-fill me-1"></i>Verified Accomplishments</span>
                                    </div>
                                    <div class="stats-icon-box bg-info bg-opacity-10 text-info border border-info-subtle">
                                        <i class="bi bi-award-fill"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Continue Learning Section Card -->
                    <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
                        <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-play-circle-fill text-primary me-2"></i>Continue Learning</h6>
                            <a href="./MyCourses/" class="text-primary small text-decoration-none fw-semibold">View All Courses <i class="bi bi-arrow-right"></i></a>
                        </div>
                        <div class="card-body p-4">
                            <?php if ($ongoing_courses_result && $ongoing_courses_result->num_rows > 0): ?>
                                <div class="row g-4">
                                    <?php while ($course = $ongoing_courses_result->fetch_assoc()): ?>
                                        <?php 
                                            $totalVids = intval($course['total_videos'] ?? 0);
                                            $completedVids = intval($course['completed_videos'] ?? 0);
                                            $progress = $totalVids > 0 ? round(($completedVids / $totalVids) * 100) : 0;
                                        ?>
                                        <div class="col-12 col-lg-6">
                                            <div class="p-3 border rounded-4 bg-light bg-opacity-50 d-flex flex-column flex-sm-row align-items-sm-center gap-3">
                                                <img 
                                                    src="../uploads/course_uploads/thumbnails/<?php echo htmlspecialchars($course['thumbnail']); ?>"
                                                    class="rounded-3 object-fit-cover flex-shrink-0"
                                                    style="width: 100px; height: 80px;"
                                                    alt="<?php echo htmlspecialchars($course['title']); ?>"
                                                    onerror="this.src='../images/default-course.png';"
                                                >
                                                <div class="flex-grow-1 min-w-0">
                                                    <h6 class="fw-bold text-dark mb-1 text-truncate"><?php echo htmlspecialchars($course['title']); ?></h6>
                                                    <span class="text-muted small d-block mb-2">
                                                        <i class="bi bi-film me-1"></i><?php echo $completedVids; ?> of <?php echo $totalVids; ?> videos watched
                                                    </span>
                                                    
                                                    <div class="progress rounded-pill mb-2" style="height: 8px;">
                                                        <div class="progress-bar bg-success rounded-pill" style="width: <?php echo $progress; ?>%"></div>
                                                    </div>

                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <span class="fw-semibold text-success small"><?php echo $progress; ?>% Completed</span>
                                                        <a href="./MyCourses/course_content.php?id=<?php echo $course['course_id']; ?>" class="btn btn-primary btn-sm rounded-pill px-3">
                                                            <i class="bi bi-play-fill me-1"></i>Continue
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4 text-muted">
                                    <i class="bi bi-journal-x fs-1 text-secondary opacity-50 d-block mb-2"></i>
                                    You are not currently enrolled in any ongoing course.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Recommended Courses Section -->
                    <div class="mb-3 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold text-dark mb-0"><i class="bi bi-stars text-warning me-2"></i>Recommended For You</h5>
                        <a href="./Categories/" class="text-primary small text-decoration-none fw-semibold">Browse Catalog <i class="bi bi-arrow-right"></i></a>
                    </div>

                    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                        <?php if ($recommended_courses_result && $recommended_courses_result->num_rows > 0): ?>
                            <?php while ($course = $recommended_courses_result->fetch_assoc()): ?>
                                <div class="col">
                                    <div class="card course-card border-0 d-flex flex-column">
                                        <img 
                                            src="../uploads/course_uploads/thumbnails/<?php echo htmlspecialchars($course['thumbnail']); ?>" 
                                            class="course-card-img" 
                                            alt="<?php echo htmlspecialchars($course['title']); ?>"
                                            onerror="this.src='../images/default-course.png';"
                                        >
                                        <div class="card-body p-4 d-flex flex-column flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="badge bg-primary bg-opacity-10 text-primary border px-2.5 py-1 rounded-pill small">
                                                    <?php echo htmlspecialchars($course['category'] ?: 'General'); ?>
                                                </span>
                                                <span class="badge bg-light text-secondary border px-2 py-1 small font-monospace">
                                                    <?php echo htmlspecialchars($course['course_type'] ?: 'Online'); ?>
                                                </span>
                                            </div>

                                            <h6 class="fw-bold text-dark mb-2"><?php echo htmlspecialchars($course['title']); ?></h6>
                                            <p class="text-secondary small flex-grow-1 mb-3" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                                <?php echo htmlspecialchars($course['description']); ?>
                                            </p>

                                            <div class="pt-3 border-top d-flex align-items-center justify-content-between mt-auto">
                                                <span class="text-muted small"><i class="bi bi-mortarboard me-1"></i>Verified Certificate</span>
                                                <a href="./MyCourses/course.php?id=<?php echo $course['course_id']; ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold">
                                                    Enroll Now
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="col-12 text-center py-5 text-muted">
                                <i class="bi bi-check2-all fs-1 text-success opacity-50 d-block mb-2"></i>
                                You have enrolled in all available courses!
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>