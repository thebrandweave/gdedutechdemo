<?php
session_start();
require_once '../../Configurations/config.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - GD Edu Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/css/student_dashboard.css">
    <link rel="stylesheet" href="../../css/customBootstrap.css">
        
    <link rel="icon" type="image/png" href="../../Images/Logos/GD_Only_logo.png">
    <link rel="shortcut icon" href="../../Images/Logos/GD_Only_logo.png">

    <style>
        .course-card {
            transition: transform 0.3s ease;
            height: 100%;
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .course-card:hover {
            transform: translateY(-5px);
        }

        .pending-overlay {
            background: rgba(0, 0, 0, 0.7);
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            border-radius: 15px;
        }

        /* Sidebar styles */
        .sidebar {
            transition: transform 0.3s ease;
            transform: translateX(-100%);
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: 250px;
            background-color: #2c3e50;
            /* Sidebar background color */
            /* Dark background */
            z-index: 1000;
        }

        .sidebar.show {
            transform: translateX(0);
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .sidebar-overlay.show {
            display: block;
        }

        /* Media Queries */
        @media (min-width: 768px) {
            .sidebar {
                transform: translateX(0);
                /* Always show sidebar on larger screens */
            }

            .sidebar-overlay {
                display: none;
                /* Hide overlay on larger screens */
            }

            .main-content {
                margin-left: 250px;
                /* Adjust main content margin for sidebar */
            }
        }

        @media (max-width: 767.98px) {
            .main-content {
                margin-left: 0;
                /* Reset margin for mobile view */
            }
        }
    </style>
</head>

<body>

    <div class="container-fluid">
        <div class="row">
            <!-- Hamburger Button -->
            <div class="topbar d-flex justify-content-between align-items-center p-2">
                <button class="btn btn-outline-secondary sidebar-toggle" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
                <span class="fw-bold"></span>
            </div>

            <!-- Sidebar -->
            <div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 sidebar">
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
                            <a href="./" class="nav-link active">
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

            <div class="sidebar-overlay" id="sidebar-overlay"></div>

            <!-- Main Content -->
            <div class="col py-3 main-content">
                <h3 class="mb-4">My Courses</h3>

                <div class="row g-4">
                    <?php if ($courses_result->num_rows > 0): ?>
                        <?php while ($course = $courses_result->fetch_assoc()): ?>
                            <div class="col-md-4">
                                <div class="card course-card position-relative">
                                    <img src="../../uploads/course_uploads/thumbnails/<?php echo htmlspecialchars($course['thumbnail']); ?>"
                                        class="card-img-top"
                                        alt="<?php echo htmlspecialchars($course['title']); ?>">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h5>
                                        <p class="card-text text-muted" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                            <?php echo htmlspecialchars($course['description']); ?>
                                        </p>

                                        <?php if ($course['payment_status'] === 'pending' || $course['transaction_status'] === 'pending'): ?>
                                            <div class="pending-overlay">
                                                <div class="text-center">
                                                    <i class="bi bi-hourglass-split fs-1 mb-2"></i>
                                                    <h5>Confirmation Pending</h5>
                                                    <p class="mb-0">Awaiting approval</p>
                                                </div>
                                            </div>
                                            <div class="alert alert-warning mt-3" role="alert">
                                                <i class="bi bi-exclamation-triangle me-2"></i>
                                                Payment verification in progress
                                            </div>
                                        <?php else: ?>
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
                                            <a href="course_content.php?id=<?php echo $course['course_id']; ?>"
                                                class="btn btn-primary w-100">
                                                Continue Learning
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center py-5">
                                    <i class="bi bi-journal-x text-muted" style="font-size: 4rem;"></i>
                                    <h4 class="mt-4">No Courses Enrolled Yet</h4>
                                    <p class="text-muted mb-4">Explore our course catalog and start your learning journey today!</p>
                                    <a href="course_category.php" class="btn btn-primary">
                                        <i class="bi bi-search me-2"></i>Browse Courses
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebarOverlay = document.getElementById('sidebar-overlay');

            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('show');
                sidebarOverlay.classList.toggle('show');
                document.body.style.overflow = sidebar.classList.contains('show') ? 'hidden' : 'auto';
            });

            sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
                document.body.style.overflow = 'auto';
            });

            // Close sidebar when a menu item is clicked
            const menuItems = document.querySelectorAll('.sidebar-menu .nav-link');
            menuItems.forEach(item => {
                item.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    sidebarOverlay.classList.remove('show');
                    document.body.style.overflow = 'auto';
                });
            });
        });
    </script>
</body>

</html>