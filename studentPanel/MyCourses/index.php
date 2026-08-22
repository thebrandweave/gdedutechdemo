<?php
session_start();
require_once '../../Configurations/config.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login_page.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$first_name = $_SESSION['first_name'] ?? 'Student';
$initial = strtoupper(substr($first_name, 0, 1));

// Fetch enrolled courses with payment status
$courses_query = "
    SELECT DISTINCT
        c.course_id,
        c.title,
        c.thumbnail,
        c.description,
        e.progress,
        e.payment_status,
        e.access_status,
        t.status as transaction_status,
        (SELECT COUNT(*) FROM Videos v 
         JOIN Lessons l ON v.lesson_id = l.lesson_id 
         WHERE l.course_id = c.course_id) as total_videos,
        (SELECT COUNT(*) FROM UserProgress up 
         JOIN Lessons l ON up.lesson_id = l.lesson_id 
         WHERE l.course_id = c.course_id 
         AND up.user_id = e.student_id 
         AND up.completed = 1) as completed_videos
    FROM Enrollments e
    JOIN Courses c ON e.course_id = c.course_id
    LEFT JOIN Transactions t ON e.course_id = t.course_id 
        AND e.student_id = t.student_id
        AND t.status = (
            SELECT status 
            FROM Transactions 
            WHERE course_id = c.course_id 
            AND student_id = e.student_id 
            ORDER BY payment_date DESC 
            LIMIT 1
        )
    WHERE e.student_id = ?
    GROUP BY c.course_id
    ORDER BY e.enrollment_id DESC
";

$courses_stmt = $conn->prepare($courses_query);
$courses_stmt->bind_param("i", $user_id);
$courses_stmt->execute();
$courses_result = $courses_stmt->get_result();
$enrolled_count = $courses_result ? $courses_result->num_rows : 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - GD Edu Tech</title>
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

        /* Course Cards */
        .course-card {
            border: 1px solid var(--admin-border-color) !important;
            border-radius: 20px !important;
            box-shadow: 0 10px 30px -10px rgba(15, 23, 42, 0.05) !important;
            background: #ffffff;
            transition: all 0.3s ease;
            overflow: hidden;
            height: 100%;
            position: relative;
        }

        .course-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 14px 35px -10px rgba(15, 23, 42, 0.12) !important;
        }

        .course-card-img {
            height: 190px;
            object-fit: cover;
            width: 100%;
        }

        .pending-overlay {
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(4px);
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            z-index: 10;
            padding: 20px;
            text-align: center;
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
                    <li class="w-100"><a href="./" class="nav-link active"><i class="bi bi-journal-bookmark"></i> My Courses</a></li>
                    <li class="w-100"><a href="../Categories/" class="nav-link"><i class="bi bi-grid"></i> Categories</a></li>
                    <li class="w-100"><a href="../Schedule/" class="nav-link"><i class="bi bi-calendar-event"></i> Schedule</a></li>
                    <li class="w-100"><a href="../Messages/" class="nav-link"><i class="bi bi-chat-dots"></i> Messages</a></li>
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
                        <h4 class="fw-bold text-dark mb-0">My Enrolled Courses</h4>
                        <span class="text-muted small">Access your active learning materials, lectures, and progress</span>
                    </div>

                    <span class="badge bg-primary bg-opacity-10 text-primary border px-3 py-2 rounded-pill font-monospace">
                        <i class="bi bi-journal-check me-1.5"></i><?php echo $enrolled_count; ?> Courses Enrolled
                    </span>
                </div>

                <div class="p-4 flex-grow-1">

                    <!-- Courses Grid -->
                    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4 mb-4">
                        <?php if ($courses_result && $courses_result->num_rows > 0): ?>
                            <?php while ($course = $courses_result->fetch_assoc()): ?>
                                <?php 
                                    $isPending = ($course['payment_status'] === 'pending' || $course['transaction_status'] === 'pending');
                                    $totalVids = intval($course['total_videos'] ?? 0);
                                    $completedVids = intval($course['completed_videos'] ?? 0);
                                    $progress = $totalVids > 0 ? round(($completedVids / $totalVids) * 100) : 0;
                                ?>
                                <div class="col">
                                    <div class="card course-card border-0 d-flex flex-column">
                                        
                                        <!-- Thumbnail Image -->
                                        <div class="position-relative">
                                            <img 
                                                src="../../uploads/course_uploads/thumbnails/<?php echo htmlspecialchars($course['thumbnail']); ?>" 
                                                class="course-card-img"
                                                alt="<?php echo htmlspecialchars($course['title']); ?>"
                                                onerror="this.src='../../images/default-course.png';"
                                            >

                                            <?php if ($isPending): ?>
                                                <div class="pending-overlay">
                                                    <div>
                                                        <i class="bi bi-hourglass-split fs-1 text-warning mb-2 d-block"></i>
                                                        <h6 class="fw-bold text-white mb-1">Approval Pending</h6>
                                                        <span class="small text-white-50">Payment verification in progress</span>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="card-body p-4 d-flex flex-column flex-grow-1">
                                            <h6 class="fw-bold text-dark mb-2"><?php echo htmlspecialchars($course['title']); ?></h6>
                                            <p class="text-secondary small flex-grow-1 mb-3" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                                <?php echo htmlspecialchars($course['description']); ?>
                                            </p>

                                            <?php if ($isPending): ?>
                                                <div class="alert alert-warning border-0 bg-warning bg-opacity-10 text-warning-emphasis small py-2 px-3 rounded-3 mb-0 mt-auto">
                                                    <i class="bi bi-exclamation-triangle-fill me-1.5"></i> Awaiting Admin Verification
                                                </div>
                                            <?php else: ?>
                                                <div class="mt-auto pt-3 border-top">
                                                    <div class="d-flex align-items-center justify-content-between mb-1 text-muted small">
                                                        <span><i class="bi bi-play-circle me-1"></i><?php echo $completedVids; ?> / <?php echo $totalVids; ?> Videos</span>
                                                        <span class="fw-semibold text-success"><?php echo $progress; ?>%</span>
                                                    </div>

                                                    <div class="progress rounded-pill mb-3" style="height: 7px;">
                                                        <div class="progress-bar bg-success rounded-pill" style="width: <?php echo $progress; ?>%"></div>
                                                    </div>

                                                    <a href="course_content.php?id=<?php echo $course['course_id']; ?>" class="btn btn-primary w-100 rounded-pill fw-semibold py-2">
                                                        <i class="bi bi-play-fill me-1"></i>Continue Learning
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="col-12 text-center py-5">
                                <div class="card border-0 shadow-sm rounded-4 p-5">
                                    <i class="bi bi-journal-x text-secondary opacity-50 display-1 mb-3"></i>
                                    <h4 class="fw-bold text-dark mb-2">No Courses Enrolled Yet</h4>
                                    <p class="text-muted mb-4">Explore our course catalog and start learning today!</p>
                                    <div>
                                        <a href="../Categories/" class="btn btn-primary rounded-pill px-4 py-2.5 fw-semibold">
                                            <i class="bi bi-search me-2"></i>Browse Courses Catalog
                                        </a>
                                    </div>
                                </div>
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