<?php
$host = $_SERVER['HTTP_HOST'];

if (strpos($host, 'staff.gdedutech.com') !== false) {
    header("Location: https://gdedutech.com/staffPanel/");
}
?>
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

require_once '../Configurations/config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - GD Edu Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../adminPanel/css/style.css">
</head>

<body>
    <div class="container-fluid">
        <div class="row">
          <!-- Sidebar -->
<div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 sidebar">
    <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 min-vh-100">
        <a href="#" class="d-flex align-items-center pb-3 mb-md-1 mt-md-3 me-md-auto text-white text-decoration-none">
            <span class="fs-5 fw-bolder" style="display: flex;align-items:center;color:black;">
                <img height="35px" src="../../adminPanel/images/edutechLogo.png" alt="">&nbsp; GD Edu Tech
            </span>
        </a>
        <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100" id="menu">
            <li class="w-100">
                <a href="./index.php" class="nav-link active">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>
           
            <li class="w-100">
                <a href="./Courses/" class="nav-link ">
                    <i class="bi bi-book me-2"></i> Courses
                </a>
            </li>
            <li class="w-100">
                <a href="./Quiz/" class="nav-link">
                    <i class="bi bi-lightbulb me-2"></i> Quiz
                </a>
            </li>
            <li class="w-100">
                            <a href="./Messages/index.php" class="nav-link">
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
                <!-- Header -->
                <div class="container-fluid">
                    <div class="row mb-4">
                        <div class="col">
                            <h2>Welcome, <?php echo htmlspecialchars($staff_name); ?>!</h2>
                            <p class="text-muted">Manage your courses and track student progress</p>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row g-4 mb-4">
                        <?php
                        // Get assigned courses count
                        $coursesQuery = "
                            SELECT 
                                COUNT(DISTINCT sa.course_id) as assigned_courses,
                                COUNT(DISTINCT e.student_id) as total_students,
                                ROUND(AVG(e.progress), 2) as avg_progress
                            FROM StaffAssignments sa
                            LEFT JOIN Enrollments e ON sa.course_id = e.course_id
                            WHERE sa.staff_id = ?";
                        
                        $stmt = $conn->prepare($coursesQuery);
                        $stmt->bind_param("i", $staff_id);
                        $stmt->execute();
                        $stats = $stmt->get_result()->fetch_assoc();
                        ?>

                        <!-- Assigned Courses Card -->
                        <div class="col-md-4">
                            <div class="card stats-card">
                                <div class="card-body">
                                    <h6 class="card-title">Assigned Courses</h6>
                                    <h2><?php echo $stats['assigned_courses']; ?></h2>
                                    <p class="mb-0">
                                        <i class="bi bi-book"></i> Active Courses
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Total Students Card -->
                        <div class="col-md-4">
                            <div class="card stats-card">
                                <div class="card-body">
                                    <h6 class="card-title">Total Students</h6>
                                    <h2><?php echo $stats['total_students']; ?></h2>
                                    <p class="mb-0">
                                        <i class="bi bi-people"></i> Enrolled Students
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Average Progress Card -->
                        <div class="col-md-4">
                            <div class="card stats-card">
                                <div class="card-body">
                                    <h6 class="card-title">Average Progress</h6>
                                    <h2><?php echo $stats['avg_progress'] ?? 0; ?>%</h2>
                                    <p class="mb-0">
                                        <i class="bi bi-graph-up"></i> Course Completion
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activities & Course Management -->
                    <div class="row mb-4">
                        <!-- Recent Student Activities -->
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">Recent Student Activities</h5>
                                    <a href="./student_activities.php" class="btn btn-sm btn-outline-primary">View All</a>
                                </div>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Student</th>
                                                <th>Course</th>
                                                <th>Activity</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $activitiesQuery = "
                                                SELECT 
                                                    u.username,
                                                    c.title as course_title,
                                                    ra.activity_description,
                                                    ra.activity_timestamp
                                                FROM recent_activities ra
                                                JOIN Users u ON ra.user_id = u.user_id
                                                JOIN Enrollments e ON ra.user_id = e.student_id
                                                JOIN Courses c ON e.course_id = c.course_id
                                                JOIN StaffAssignments sa ON c.course_id = sa.course_id
                                                WHERE sa.staff_id = ?
                                                ORDER BY ra.activity_timestamp DESC
                                                LIMIT 5";
                                            
                                            $stmt = $conn->prepare($activitiesQuery);
                                            $stmt->bind_param("i", $staff_id);
                                            $stmt->execute();
                                            $activities = $stmt->get_result();

                                            while ($activity = $activities->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($activity['username']); ?></td>
                                                    <td><?php echo htmlspecialchars($activity['course_title']); ?></td>
                                                    <td><?php echo htmlspecialchars($activity['activity_description']); ?></td>
                                                    <td><?php echo date('M d, Y', strtotime($activity['activity_timestamp'])); ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0">Quick Actions</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <a href="./Courses/add_course.php" class="btn btn-primary">
                                            <i class="bi bi-plus-circle me-2"></i>Add New Course
                                        </a>
                                        <a href="./Quiz/add_quiz.php" class="btn btn-outline-primary">
                                            <i class="bi bi-question-circle me-2"></i>Create Quiz
                                        </a>
                                        <a href="./view_progress.php" class="btn btn-outline-primary">
                                            <i class="bi bi-graph-up me-2"></i>View Progress Reports
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Notifications -->
                            <div class="card mt-4">
                                <div class="card-header bg-white">
                                    <h5 class="card-title mb-0">Recent Notifications</h5>
                                </div>
                                <div class="card-body">
                                    <?php
                                    $notificationsQuery = "
                                        SELECT message, date_sent 
                                        FROM Notifications 
                                        WHERE user_id = ? 
                                        ORDER BY date_sent DESC 
                                        LIMIT 3";
                                    
                                    $stmt = $conn->prepare($notificationsQuery);
                                    $stmt->bind_param("i", $staff_id);
                                    $stmt->execute();
                                    $notifications = $stmt->get_result();

                                    while ($notification = $notifications->fetch_assoc()): ?>
                                        <div class="notification-item mb-3">
                                            <p class="mb-1"><?php echo htmlspecialchars($notification['message']); ?></p>
                                            <small class="text-muted">
                                                <?php echo date('M d, Y H:i', strtotime($notification['date_sent'])); ?>
                                            </small>
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
</body>
</html> 