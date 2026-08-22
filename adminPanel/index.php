<?php
$host = $_SERVER['HTTP_HOST'];

if (strpos($host, 'admin.gdedutech.com') !== false) {
    header("Location: https://gdedutech.com/adminPanel/");
    exit();
}

session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: admin_login.php');
    exit();
}

// Get admin details from session
$admin_name = $_SESSION['username'] ?? 'Admin';
require_once '../Configurations/config.php';

// --- DATA QUERIES ---

// 1. Total Users Query
$totalUsers = 0;
$newUsersThisMonth = 0;
$percentageIncrease = 0;

$totalUsersQuery = "
    SELECT 
        COUNT(*) AS total_users,
        SUM(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN 1 ELSE 0 END) AS new_users_this_month
    FROM Users
";
$totalUsersResult = mysqli_query($conn, $totalUsersQuery);
if ($totalUsersResult) {
    $data = mysqli_fetch_assoc($totalUsersResult);
    $totalUsers = $data['total_users'] ?? 0;
    $newUsersThisMonth = $data['new_users_this_month'] ?? 0;
    mysqli_free_result($totalUsersResult);
}

$previousMonthUsersQuery = "
    SELECT COUNT(*) AS previous_month_users
    FROM Users
    WHERE MONTH(created_at) = MONTH(CURDATE()) - 1 AND YEAR(created_at) = YEAR(CURDATE())
";
$previousMonthResult = mysqli_query($conn, $previousMonthUsersQuery);
if ($previousMonthResult) {
    $previousMonthUsers = mysqli_fetch_assoc($previousMonthResult)['previous_month_users'] ?? 0;
    $percentageIncrease = $previousMonthUsers > 0
        ? round(($newUsersThisMonth / $previousMonthUsers) * 100)
        : 0;
    mysqli_free_result($previousMonthResult);
}

// 2. Active Courses Query
$activeCourses = 0;
$newCoursesThisWeek = 0;
$activeCoursesQuery = "
    SELECT 
        COUNT(*) AS active_courses,
        SUM(CASE WHEN DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) AS new_courses
    FROM Courses
    WHERE status = 'published'
";
$activeCoursesResult = mysqli_query($conn, $activeCoursesQuery);
if ($activeCoursesResult) {
    $data = mysqli_fetch_assoc($activeCoursesResult);
    $activeCourses = $data['active_courses'] ?? 0;
    $newCoursesThisWeek = $data['new_courses'] ?? 0;
    mysqli_free_result($activeCoursesResult);
}

// 3. Course Completion Query
$courseCompletion = 0;
$completedCourses = 0;
$activeEnrollmentsCount = 0;
try {
    $activeResult = $conn->query("SELECT COUNT(*) AS active_courses FROM Enrollments WHERE access_status = 'active'");
    if ($activeResult && $activeRow = $activeResult->fetch_assoc()) {
        $activeEnrollmentsCount = (int)$activeRow['active_courses'];
    }

    $completedResult = $conn->query("SELECT COUNT(*) AS completed_courses FROM Enrollments WHERE completion_status = 'completed'");
    if ($completedResult && $completedRow = $completedResult->fetch_assoc()) {
        $completedCourses = (int)$completedRow['completed_courses'];
    }

    if ($activeEnrollmentsCount > 0) {
        $courseCompletion = round(($completedCourses / $activeEnrollmentsCount) * 100, 1);
    }
} catch (Exception $e) {
    // Quiet fallback
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - GD Edu Tech</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="../Images/Logos/GD_Only_logo.png">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f1f5f9;
            color: #0f172a;
            overflow-x: hidden;
        }

        /* Sidebar Executive Styling */
        .admin-sidebar {
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            box-shadow: 4px 0 25px rgba(15, 23, 42, 0.15);
            z-index: 100;
        }

        .sidebar-brand {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-brand img {
            height: 38px;
            width: auto;
        }

        .sidebar-brand-title {
            color: #ffffff;
            font-size: 1.1rem;
            font-weight: 800;
            letter-spacing: -0.3px;
        }

        .status-dot-online {
            width: 8px;
            height: 8px;
            background: #10b981;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 10px #10b981;
        }

        .nav-menu-label {
            color: #64748b;
            font-size: 0.72rem;
            font-weight: 700;
            text-uppercase: uppercase;
            letter-spacing: 0.8px;
            padding: 16px 20px 6px 20px;
        }

        .admin-sidebar .nav-link {
            color: #94a3b8;
            font-size: 0.9rem;
            font-weight: 500;
            padding: 12px 20px;
            border-radius: 12px;
            margin: 3px 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.25s ease;
            text-decoration: none !important;
        }

        .admin-sidebar .nav-link i {
            font-size: 1.15rem;
            transition: transform 0.2s ease;
        }

        .admin-sidebar .nav-link:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.08);
            transform: translateX(4px);
        }

        .admin-sidebar .nav-link.active {
            color: #ffffff;
            background: linear-gradient(90deg, rgba(13, 114, 152, 0.9) 0%, rgba(6, 93, 125, 0.95) 100%);
            box-shadow: 0 8px 20px rgba(13, 114, 152, 0.35);
            font-weight: 700;
        }

        .admin-sidebar .nav-link.active i {
            color: #38bdf8;
        }

        .logout-link {
            color: #f87171 !important;
            background: rgba(239, 68, 68, 0.1) !important;
            border: 1px solid rgba(239, 68, 68, 0.2);
            margin-top: auto !important;
        }

        .logout-link:hover {
            background: #ef4444 !important;
            color: #ffffff !important;
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.35);
        }

        /* Executive Header */
        .top-navbar {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 16px 30px;
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.03);
        }

        .admin-avatar-box {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0d7298 0%, #065d7d 100%);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
            box-shadow: 0 4px 12px rgba(13, 114, 152, 0.25);
        }

        /* Dashboard Stat Cards */
        .dashboard-stat-card {
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            padding: 24px;
            box-shadow: 0 10px 30px -10px rgba(15, 23, 42, 0.05);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .dashboard-stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px -15px rgba(13, 114, 152, 0.15);
            border-color: #0d7298;
        }

        .stat-icon-wrapper {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            flex-shrink: 0;
        }

        .icon-blue { background: rgba(13, 114, 152, 0.1); color: #0d7298; }
        .icon-green { background: rgba(16, 185, 129, 0.1); color: #10b981; }
        .icon-amber { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
        .icon-purple { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; }

        .stat-val {
            font-size: 1.85rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.2;
        }

        .stat-label {
            font-size: 0.85rem;
            color: #64748b;
            font-weight: 600;
        }

        .trend-pill {
            font-size: 0.75rem;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .trend-up { background: rgba(16, 185, 129, 0.12); color: #059669; }
        .trend-neutral { background: rgba(100, 116, 139, 0.12); color: #475569; }

        /* Dashboard Content Cards & Tables */
        .dashboard-content-card {
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 30px -10px rgba(15, 23, 42, 0.05);
            overflow: hidden;
            height: 100%;
        }

        .dashboard-card-header {
            padding: 20px 24px;
            background: #ffffff;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .dashboard-card-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .custom-table {
            margin: 0;
        }

        .custom-table th {
            background: #f8fafc;
            color: #475569;
            font-size: 0.78rem;
            font-weight: 700;
            text-uppercase: uppercase;
            letter-spacing: 0.5px;
            padding: 14px 24px;
            border-bottom: 1px solid #e2e8f0;
        }

        .custom-table td {
            padding: 14px 24px;
            vertical-align: middle;
            font-size: 0.88rem;
            color: #334155;
            border-bottom: 1px solid #f1f5f9;
        }

        .custom-table tr:last-child td {
            border-bottom: none;
        }

        .custom-table tr:hover td {
            background: rgba(13, 114, 152, 0.02);
        }

        .action-btn-sm {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            background: #f1f5f9;
            transition: all 0.2s ease;
            text-decoration: none !important;
        }

        .action-btn-sm:hover {
            background: #0d7298;
            color: #ffffff;
        }

        .btn-action-gradient {
            background: linear-gradient(135deg, #0d7298 0%, #065d7d 100%);
            color: #ffffff;
            font-weight: 600;
            font-size: 0.9rem;
            border: none;
            border-radius: 12px;
            padding: 12px 20px;
            box-shadow: 0 6px 18px rgba(13, 114, 152, 0.25);
            transition: all 0.3s ease;
            text-decoration: none !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-action-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(13, 114, 152, 0.35);
            color: #ffffff;
        }

        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>

<body>
    <div class="container-fluid p-0">
        <div class="row g-0 flex-nowrap">
            
            <!-- Sidebar -->
            <div class="col-auto col-md-3 col-xl-2 px-0 admin-sidebar sticky-top vh-100 overflow-auto hide-scrollbar d-flex flex-column">
                
                <!-- Brand Header -->
                <div class="sidebar-brand">
                    <img src="./images/edutechLogo.png" onerror="this.onerror=null; this.src='../Images/Logos/GD_Only_logo.png';" alt="GD Logo">
                    <div>
                        <div class="sidebar-brand-title">GD Edu Tech</div>
                        <span class="text-white-50 small d-flex align-items-center gap-1.5" style="font-size: 0.72rem;">
                            <span class="status-dot-online"></span> System Online
                        </span>
                    </div>
                </div>

                <!-- Navigation Links -->
                <div class="py-2 flex-grow-1">
                    <div class="nav-menu-label">Main Navigation</div>
                    
                    <a href="./" class="nav-link active">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>

                    <a href="./Categories/" class="nav-link">
                        <i class="bi bi-grid"></i>
                        <span>Categories</span>
                    </a>

                    <a href="./Admissions/" class="nav-link">
                        <i class="bi bi-person-plus"></i>
                        <span>Student Admission</span>
                    </a>

                    <a href="./Courses/" class="nav-link">
                        <i class="bi bi-book"></i>
                        <span>Courses</span>
                    </a>

                    <a href="./Applications/" class="nav-link">
                        <i class="bi bi-journal-text"></i>
                        <span>Scholarships</span>
                    </a>

                    <a href="./Events/" class="nav-link">
                        <i class="bi bi-calendar2-event"></i>
                        <span>Events</span>
                    </a>

                    <div class="nav-menu-label">Engagement</div>

                    <a href="./social_links.php" class="nav-link">
                        <i class="bi bi-link-45deg"></i>
                        <span>Social Links</span>
                    </a>

                    <a href="./Schedule/index.php" class="nav-link">
                        <i class="bi bi-calendar-event"></i>
                        <span>Schedule</span>
                    </a>

                    <a href="./feedback/feedback.php" class="nav-link">
                        <i class="bi bi-chat-square-heart"></i>
                        <span>Feedback</span>
                    </a>

                    <a href="./Messages/index.php" class="nav-link">
                        <i class="bi bi-chat-dots"></i>
                        <span>Messages</span>
                    </a>

                    <div class="nav-menu-label">System & Users</div>

                    <a href="./FAQ/" class="nav-link">
                        <i class="bi bi-question-circle"></i>
                        <span>FAQ Directory</span>
                    </a>

                    <a href="./Users/" class="nav-link">
                        <i class="bi bi-people"></i>
                        <span>User Management</span>
                    </a>

                    <a href="./manage_qr.php" class="nav-link">
                        <i class="bi bi-qr-code"></i>
                        <span>Payment QR</span>
                    </a>

                    <a href="./pending_payments.php" class="nav-link">
                        <i class="bi bi-credit-card"></i>
                        <span>Pending Payments</span>
                    </a>
                </div>

                <!-- Footer Logout Button -->
                <div class="p-3 border-top border-white border-opacity-10 mt-auto">
                    <a href="./logout.php" class="nav-link logout-link justify-content-center m-0">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Logout Account</span>
                    </a>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="col min-vh-100 d-flex flex-column">
                
                <!-- Top Navbar -->
                <header class="top-navbar d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="fw-bold mb-0 text-dark">Dashboard Overview</h4>
                        <span class="text-muted small">Welcome back, <strong><?php echo htmlspecialchars($admin_name); ?></strong>!</span>
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <span class="badge bg-light text-dark border px-3 py-2 rounded-pill font-monospace small">
                            <i class="bi bi-calendar3 text-primary me-1"></i> <?php echo date('l, F j, Y'); ?>
                        </span>

                        <div class="dropdown">
                            <a href="#" class="d-flex align-items-center gap-2 text-decoration-none dropdown-toggle" id="adminUserDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="admin-avatar-box">
                                    <?php echo strtoupper(substr($admin_name, 0, 1)); ?>
                                </div>
                                <div class="d-none d-md-block text-start">
                                    <strong class="text-dark d-block lead fs-6 mb-0" style="line-height: 1.2;"><?php echo htmlspecialchars($admin_name); ?></strong>
                                    <span class="text-muted small">Super Administrator</span>
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-2 mt-2" aria-labelledby="adminUserDropdown">
                                <li><a class="dropdown-item rounded-3" href="./Users/"><i class="bi bi-person me-2"></i>My Profile</a></li>
                                <li><a class="dropdown-item rounded-3" href="./Users/Messages"><i class="bi bi-envelope me-2"></i>Messages</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item rounded-3 text-danger" href="./logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </header>

                <!-- Page Content Grid -->
                <div class="p-4 flex-grow-1">
                    
                    <!-- 4 Metric Cards Row -->
                    <div class="row g-4 mb-4">
                        
                        <!-- Card 1: Total Users -->
                        <div class="col-sm-6 col-xl-3">
                            <div class="dashboard-stat-card">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="stat-icon-wrapper icon-blue">
                                        <i class="bi bi-people-fill"></i>
                                    </div>
                                    <span class="trend-pill trend-up">
                                        <i class="bi bi-arrow-up-short fs-6"></i> +<?php echo $percentageIncrease; ?>%
                                    </span>
                                </div>
                                <div class="stat-val"><?php echo number_format($totalUsers); ?></div>
                                <div class="stat-label">Total Registered Users</div>
                            </div>
                        </div>

                        <!-- Card 2: Active Courses -->
                        <div class="col-sm-6 col-xl-3">
                            <div class="dashboard-stat-card">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="stat-icon-wrapper icon-green">
                                        <i class="bi bi-journal-bookmark-fill"></i>
                                    </div>
                                    <span class="trend-pill trend-up">
                                        <i class="bi bi-plus-lg me-0.5"></i> <?php echo $newCoursesThisWeek; ?> this week
                                    </span>
                                </div>
                                <div class="stat-val"><?php echo number_format($activeCourses); ?></div>
                                <div class="stat-label">Active Published Courses</div>
                            </div>
                        </div>

                        <!-- Card 3: Total Revenue -->
                        <div class="col-sm-6 col-xl-3">
                            <div class="dashboard-stat-card">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="stat-icon-wrapper icon-amber">
                                        <i class="bi bi-currency-rupee"></i>
                                    </div>
                                    <span class="trend-pill trend-up">
                                        <i class="bi bi-graph-up-arrow me-1"></i> +8% growth
                                    </span>
                                </div>
                                <div class="stat-val">₹ 0</div>
                                <div class="stat-label">Platform Gross Revenue</div>
                            </div>
                        </div>

                        <!-- Card 4: Course Completion -->
                        <div class="col-sm-6 col-xl-3">
                            <div class="dashboard-stat-card">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="stat-icon-wrapper icon-purple">
                                        <i class="bi bi-trophy-fill"></i>
                                    </div>
                                    <span class="trend-pill trend-neutral">
                                        <?php echo $completedCourses; ?> / <?php echo $activeEnrollmentsCount; ?> Ratio
                                    </span>
                                </div>
                                <div class="stat-val"><?php echo $courseCompletion; ?>%</div>
                                <div class="stat-label">Course Completion Rate</div>
                            </div>
                        </div>

                    </div>

                    <!-- Row 2: Recent Activity & Quick Actions -->
                    <div class="row g-4 mb-4">
                        
                        <!-- Recent Activity Log (8 Columns) -->
                        <div class="col-lg-8">
                            <div class="dashboard-content-card">
                                <div class="dashboard-card-header">
                                    <h5 class="dashboard-card-title">
                                        <i class="bi bi-activity text-primary"></i> Recent Platform Activity
                                    </h5>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-3 py-1.5 rounded-pill fw-semibold">Real-time Log</span>
                                </div>

                                <div class="table-responsive">
                                    <table class="table custom-table align-middle">
                                        <thead>
                                            <tr>
                                                <th>User</th>
                                                <th>Activity Description</th>
                                                <th>Event Type</th>
                                                <th>Timestamp</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $activity_query = "
                                                SELECT 
                                                    a.*,
                                                    u.username,
                                                    u.first_name,
                                                    u.last_name,
                                                    u.profile_image
                                                FROM ActivityLog a
                                                LEFT JOIN Users u ON a.user_id = u.user_id
                                                ORDER BY a.created_at DESC
                                                LIMIT 7
                                            ";
                                            $activity_result = mysqli_query($conn, $activity_query);

                                            if ($activity_result && mysqli_num_rows($activity_result) > 0):
                                                while ($activity = mysqli_fetch_assoc($activity_result)):
                                                    $badge_class = 'bg-secondary bg-opacity-10 text-secondary border-secondary-subtle';
                                                    $icon_class = 'bi-clock';
                                                    
                                                    switch ($activity['activity_type']) {
                                                        case 'course_created':
                                                            $badge_class = 'bg-success bg-opacity-10 text-success border-success-subtle';
                                                            $icon_class = 'bi-book';
                                                            break;
                                                        case 'course_enrolled':
                                                            $badge_class = 'bg-primary bg-opacity-10 text-primary border-primary-subtle';
                                                            $icon_class = 'bi-person-check';
                                                            break;
                                                        case 'quiz_completed':
                                                            $badge_class = 'bg-info bg-opacity-10 text-info border-info-subtle';
                                                            $icon_class = 'bi-check-circle';
                                                            break;
                                                        case 'payment_made':
                                                            $badge_class = 'bg-warning bg-opacity-10 text-warning border-warning-subtle';
                                                            $icon_class = 'bi-credit-card';
                                                            break;
                                                        case 'user_registered':
                                                            $badge_class = 'bg-indigo bg-opacity-10 text-indigo border-indigo-subtle';
                                                            $icon_class = 'bi-person-plus';
                                                            break;
                                                    }

                                                    $time_ago = time() - strtotime($activity['created_at']);
                                                    $time_str = 'Just now';
                                                    if ($time_ago >= 60 && $time_ago < 3600) {
                                                        $time_str = floor($time_ago / 60) . 'm ago';
                                                    } elseif ($time_ago >= 3600 && $time_ago < 86400) {
                                                        $time_str = floor($time_ago / 3600) . 'h ago';
                                                    } elseif ($time_ago >= 86400) {
                                                        $time_str = date('M d, Y', strtotime($activity['created_at']));
                                                    }
                                            ?>
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center gap-2">
                                                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold small" style="width: 34px; height: 34px;">
                                                                    <?php echo strtoupper(substr($activity['username'] ?? 'U', 0, 1)); ?>
                                                                </div>
                                                                <strong class="text-dark"><?php echo htmlspecialchars($activity['username'] ?? 'System User'); ?></strong>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="text-secondary"><?php echo htmlspecialchars($activity['activity_description']); ?></span>
                                                        </td>
                                                        <td>
                                                            <span class="badge border px-2.5 py-1 rounded-pill fw-semibold <?php echo $badge_class; ?>">
                                                                <i class="bi <?php echo $icon_class; ?> me-1"></i>
                                                                <?php echo str_replace('_', ' ', ucfirst($activity['activity_type'])); ?>
                                                            </span>
                                                        </td>
                                                        <td class="text-muted small">
                                                            <?php echo $time_str; ?>
                                                        </td>
                                                    </tr>
                                                <?php endwhile;
                                            else: ?>
                                                <tr>
                                                    <td colspan="4" class="text-center py-4 text-muted">
                                                        <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                                        No recent activity records found.
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions Side Card (4 Columns) -->
                        <div class="col-lg-4">
                            <div class="dashboard-content-card p-4 d-flex flex-column gap-3">
                                <h5 class="dashboard-card-title mb-2">
                                    <i class="bi bi-lightning-charge-fill text-warning"></i> Quick Management
                                </h5>

                                <a href="./Users/Messages" class="btn-action-gradient w-100">
                                    <i class="bi bi-megaphone-fill"></i>
                                    <span>Broadcast Announcement</span>
                                </a>

                                <a href="./Users/add_user.php" class="btn btn-outline-dark rounded-4 py-2.5 fw-semibold d-flex align-items-center justify-content-center gap-2" style="border-radius: 12px !important;">
                                    <i class="bi bi-person-plus-fill text-primary"></i>
                                    <span>Create New User</span>
                                </a>

                                <a href="./pending_payments.php" class="btn btn-outline-dark rounded-4 py-2.5 fw-semibold d-flex align-items-center justify-content-center gap-2" style="border-radius: 12px !important;">
                                    <i class="bi bi-credit-card-2-front-fill text-warning"></i>
                                    <span>Review Pending Payments</span>
                                </a>

                                <hr class="my-2 text-secondary opacity-25">

                                <!-- System Status Gauge Widget -->
                                <div class="bg-light p-3 rounded-4 border">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-bold small text-dark"><i class="bi bi-hdd-stack-fill me-1.5 text-primary"></i> Storage Utilization</span>
                                        <span class="badge bg-primary text-white font-monospace">75%</span>
                                    </div>
                                    <div class="progress mb-3" style="height: 8px; border-radius: 50px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: 75%"></div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-bold small text-dark"><i class="bi bi-cpu-fill me-1.5 text-success"></i> Bandwidth Health</span>
                                        <span class="badge bg-success text-white font-monospace">99.8%</span>
                                    </div>
                                    <div class="progress" style="height: 8px; border-radius: 50px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 99.8%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Row 3: Recent Users & Popular Courses -->
                    <div class="row g-4">
                        
                        <!-- Recent Users Card -->
                        <div class="col-lg-6">
                            <div class="dashboard-content-card">
                                <div class="dashboard-card-header">
                                    <h5 class="dashboard-card-title">
                                        <i class="bi bi-person-lines-fill text-primary"></i> Recent Users
                                    </h5>
                                    <a href="./Users/" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">View All</a>
                                </div>

                                <?php
                                $users_query = "SELECT username, role FROM Users ORDER BY date_joined DESC LIMIT 6";
                                $users_res = mysqli_query($conn, $users_query);
                                ?>

                                <div class="table-responsive">
                                    <table class="table custom-table align-middle">
                                        <thead>
                                            <tr>
                                                <th>User</th>
                                                <th>Access Role</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($users_res && mysqli_num_rows($users_res) > 0): ?>
                                                <?php while ($row = mysqli_fetch_assoc($users_res)): ?>
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center gap-2">
                                                                <div class="rounded-circle bg-light border text-dark d-flex align-items-center justify-content-center fw-bold small" style="width: 32px; height: 32px;">
                                                                    <?php echo strtoupper(substr($row['username'], 0, 1)); ?>
                                                                </div>
                                                                <strong class="text-dark"><?php echo htmlspecialchars($row['username']); ?></strong>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-light text-dark border px-2.5 py-1 rounded-pill font-monospace">
                                                                <?php echo ucfirst(htmlspecialchars($row['role'])); ?>
                                                            </span>
                                                        </td>
                                                        <td class="text-end">
                                                            <a href="./Users/" class="action-btn-sm me-1" title="Edit User"><i class="bi bi-pencil-fill"></i></a>
                                                            <a href="./Users/" class="action-btn-sm text-danger" title="Manage Access"><i class="bi bi-shield-lock-fill"></i></a>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                                <?php mysqli_free_result($users_res); ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="3" class="text-center py-3 text-muted">No users registered yet.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Popular Courses Card -->
                        <div class="col-lg-6">
                            <div class="dashboard-content-card">
                                <div class="dashboard-card-header">
                                    <h5 class="dashboard-card-title">
                                        <i class="bi bi-star-fill text-warning"></i> Popular Courses
                                    </h5>
                                    <a href="./Courses/" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">View All</a>
                                </div>

                                <?php
                                $courses_query = "SELECT title FROM Courses WHERE isPopular = 'yes' LIMIT 6";
                                $courses_res = mysqli_query($conn, $courses_query);
                                ?>

                                <div class="table-responsive">
                                    <table class="table custom-table align-middle">
                                        <thead>
                                            <tr>
                                                <th>Course Title</th>
                                                <th>Enrollments</th>
                                                <th class="text-end">Rating</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($courses_res && mysqli_num_rows($courses_res) > 0): ?>
                                                <?php while ($row = mysqli_fetch_assoc($courses_res)): ?>
                                                    <tr>
                                                        <td>
                                                            <strong class="text-dark"><?php echo htmlspecialchars($row['title']); ?></strong>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-2.5 py-1 rounded-pill">
                                                                <i class="bi bi-people-fill me-1"></i> Active Students
                                                            </span>
                                                        </td>
                                                        <td class="text-end">
                                                            <span class="text-warning fw-bold">
                                                                <i class="bi bi-star-fill"></i> 4.9
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                                <?php mysqli_free_result($courses_res); ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="3" class="text-center py-3 text-muted">No featured popular courses configured.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
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