<?php
session_start();
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
}
require_once '../../Configurations/config.php';

// Authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login_page.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$user_query = "SELECT * FROM Users WHERE user_id = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

$first_name = $user['first_name'] ?? $_SESSION['first_name'] ?? 'Student';
$last_name = $user['last_name'] ?? $_SESSION['last_name'] ?? '';
$initial = strtoupper(substr($first_name, 0, 1));
$profile_image = $user['profile_image'] ?? null;

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

        /* Metric Cards */
        .stats-card {
            border: 1px solid var(--admin-border-color) !important;
            border-radius: 20px !important;
            box-shadow: 0 10px 30px -10px rgba(15, 23, 42, 0.05) !important;
            background: #ffffff;
            transition: all 0.3s ease;
            height: 100%;
        }

        .stats-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 14px 35px -10px rgba(15, 23, 42, 0.1) !important;
        }

        .stats-icon-box {
            width: 50px;
            height: 50px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        /* Profile Banner Card */
        .profile-banner-card {
            border: 1px solid var(--admin-border-color) !important;
            border-radius: 24px !important;
            background: #ffffff;
            box-shadow: 0 10px 30px -10px rgba(15, 23, 42, 0.06) !important;
            overflow: hidden;
        }

        .profile-avatar-xl {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #ffffff;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.12);
        }

        .avatar-initial-xl {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0d7298 0%, #065d7d 100%);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            font-weight: 700;
            border: 4px solid #ffffff;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.12);
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
                    <li class="w-100"><a href="../Messages/" class="nav-link"><i class="bi bi-chat-dots"></i> Messages</a></li>
                    <li class="w-100"><a href="./" class="nav-link active"><i class="bi bi-person"></i> Profile</a></li>
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
                        <h4 class="fw-bold text-dark mb-0">Student Profile & Progress</h4>
                        <span class="text-muted small">Manage your account details and review your learning analytics</span>
                    </div>

                    <a href="edit_profile.php" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold">
                        <i class="bi bi-pencil-square me-1.5"></i>Edit Profile
                    </a>
                </div>

                <div class="p-4 flex-grow-1">

                    <!-- Profile Banner Card -->
                    <div class="card profile-banner-card p-4 mb-4">
                        <div class="d-flex align-items-center flex-wrap gap-4">
                            <?php if (!empty($profile_image)): ?>
                                <img src="../../uploads/profiles/<?php echo htmlspecialchars($profile_image); ?>" class="profile-avatar-xl" alt="Profile Avatar" onerror="this.src='../../assets/images/default-avatar.png';">
                            <?php else: ?>
                                <div class="avatar-initial-xl"><?php echo $initial; ?></div>
                            <?php endif; ?>

                            <div class="flex-grow-1">
                                <h3 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($first_name . ' ' . $last_name); ?></h3>
                                <div class="d-flex align-items-center flex-wrap gap-3 text-muted small">
                                    <span><i class="bi bi-envelope-fill me-1 text-primary"></i><?php echo htmlspecialchars($user['email'] ?? 'Not provided'); ?></span>
                                    <span><i class="bi bi-calendar-check-fill me-1 text-primary"></i>Joined <?php echo !empty($user['date_joined']) ? date('F Y', strtotime($user['date_joined'])) : 'Member'; ?></span>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill"><i class="bi bi-patch-check-fill me-1"></i>Verified Student</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Cards Grid -->
                    <div class="row g-4 mb-4">
                        <div class="col-12 col-sm-6 col-xl-3">
                            <div class="card stats-card p-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <span class="text-muted small fw-semibold d-block mb-1">Enrolled Courses</span>
                                        <h3 class="fw-bold text-dark mb-0"><?php echo intval($stats['total_courses'] ?? 0); ?></h3>
                                    </div>
                                    <div class="stats-icon-box bg-primary bg-opacity-10 text-primary border border-primary-subtle">
                                        <i class="bi bi-journal-bookmark-fill"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-3">
                            <div class="card stats-card p-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <span class="text-muted small fw-semibold d-block mb-1">Completed Courses</span>
                                        <h3 class="fw-bold text-dark mb-0"><?php echo intval($stats['completed_courses'] ?? 0); ?></h3>
                                    </div>
                                    <div class="stats-icon-box bg-success bg-opacity-10 text-success border border-success-subtle">
                                        <i class="bi bi-check-circle-fill"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-3">
                            <div class="card stats-card p-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <span class="text-muted small fw-semibold d-block mb-1">Certificates Earned</span>
                                        <h3 class="fw-bold text-dark mb-0"><?php echo intval($stats['earned_certificates'] ?? 0); ?></h3>
                                    </div>
                                    <div class="stats-icon-box bg-warning bg-opacity-10 text-warning border border-warning-subtle">
                                        <i class="bi bi-award-fill"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-3">
                            <div class="card stats-card p-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <span class="text-muted small fw-semibold d-block mb-1">Average Rating</span>
                                        <h3 class="fw-bold text-dark mb-0"><?php echo number_format($stats['avg_rating'] ?? 0, 1); ?></h3>
                                    </div>
                                    <div class="stats-icon-box bg-info bg-opacity-10 text-info border border-info-subtle">
                                        <i class="bi bi-star-fill"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lower Content Area: Course Progress + Sidebar Widgets -->
                    <div class="row g-4 mb-4">
                        
                        <!-- Left Column: Course Progress -->
                        <div class="col-12 col-xl-8">
                            <div class="card border-0 rounded-4 shadow-sm h-100">
                                <div class="card-header bg-white py-3 px-4 border-bottom">
                                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-bar-chart-line-fill text-primary me-2"></i>Course Progress & Analytics</h6>
                                </div>
                                <div class="card-body p-4">
                                    <?php if ($courses_result && $courses_result->num_rows > 0): ?>
                                        <div class="d-flex flex-column gap-4">
                                            <?php while ($course = $courses_result->fetch_assoc()): ?>
                                                <?php
                                                    $isCompleted = ($course['completion_status'] === 'completed');
                                                    $prog = intval($course['progress'] ?? 0);
                                                ?>
                                                <div class="p-3 border rounded-4 bg-light bg-opacity-50">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <h6 class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($course['title']); ?></h6>
                                                        <span class="badge <?php echo $isCompleted ? 'bg-success bg-opacity-10 text-success border border-success-subtle' : 'bg-primary bg-opacity-10 text-primary border border-primary-subtle'; ?> rounded-pill px-2.5 py-1 small">
                                                            <?php echo ucfirst($course['completion_status'] ?? 'Ongoing'); ?>
                                                        </span>
                                                    </div>

                                                    <div class="progress rounded-pill mb-2" style="height: 8px;">
                                                        <div class="progress-bar <?php echo $isCompleted ? 'bg-success' : 'bg-primary'; ?> rounded-pill" style="width: <?php echo $prog; ?>%"></div>
                                                    </div>

                                                    <div class="d-flex justify-content-between text-muted small">
                                                        <span><i class="bi bi-film me-1"></i><?php echo intval($course['completed_videos']); ?> of <?php echo intval($course['total_videos']); ?> videos completed</span>
                                                        <span class="fw-semibold text-secondary"><?php echo htmlspecialchars($course['category_name'] ?: 'General'); ?></span>
                                                    </div>
                                                </div>
                                            <?php endwhile; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-5 text-muted">
                                            <i class="bi bi-journal-x fs-1 text-secondary opacity-50 d-block mb-2"></i>
                                            No course progress records available.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Certificates & Recent Activity -->
                        <div class="col-12 col-xl-4 d-flex flex-column gap-4">
                            
                            <!-- Certificates Widget -->
                            <div class="card border-0 rounded-4 shadow-sm">
                                <div class="card-header bg-white py-3 px-4 border-bottom">
                                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-award-fill text-warning me-2"></i>My Certificates</h6>
                                </div>
                                <div class="card-body p-4">
                                    <?php if ($certificates_result && $certificates_result->num_rows > 0): ?>
                                        <div class="d-flex flex-column gap-3">
                                            <?php while ($cert = $certificates_result->fetch_assoc()): ?>
                                                <div class="p-3 border rounded-3 bg-white d-flex align-items-center justify-content-between">
                                                    <div>
                                                        <h6 class="fw-bold text-dark mb-1 small"><?php echo htmlspecialchars($cert['course_title']); ?></h6>
                                                        <span class="text-muted small d-block">Issued <?php echo date('M d, Y', strtotime($cert['issue_date'])); ?></span>
                                                    </div>
                                                    <a href="<?php echo htmlspecialchars($cert['certificate_url']); ?>" class="btn btn-outline-primary btn-sm rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;" target="_blank" title="Download Certificate">
                                                        <i class="bi bi-download"></i>
                                                    </a>
                                                </div>
                                            <?php endwhile; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-4 text-muted small">
                                            <i class="bi bi-patch-minus fs-2 text-secondary opacity-50 d-block mb-2"></i>
                                            No certificates earned yet. Complete courses to unlock.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Recent Activity Widget -->
                            <div class="card border-0 rounded-4 shadow-sm">
                                <div class="card-header bg-white py-3 px-4 border-bottom">
                                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-clock-history text-primary me-2"></i>Recent Activity</h6>
                                </div>
                                <div class="card-body p-4">
                                    <?php if ($activities_result && $activities_result->num_rows > 0): ?>
                                        <div class="d-flex flex-column gap-3">
                                            <?php while ($activity = $activities_result->fetch_assoc()): ?>
                                                <div class="d-flex align-items-start gap-2.5">
                                                    <i class="bi bi-circle-fill text-primary mt-1" style="font-size: 8px;"></i>
                                                    <div>
                                                        <p class="text-dark small mb-0 fw-medium"><?php echo htmlspecialchars($activity['activity_description']); ?></p>
                                                        <span class="text-muted" style="font-size: 11px;"><?php echo date('M d, Y H:i', strtotime($activity['activity_timestamp'])); ?></span>
                                                    </div>
                                                </div>
                                            <?php endwhile; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-4 text-muted small">
                                            <i class="bi bi-hourglass-top fs-2 text-secondary opacity-50 d-block mb-2"></i>
                                            No recent learning activity logged.
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

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>