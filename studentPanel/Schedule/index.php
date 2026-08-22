<?php
session_start();
require_once '../../Configurations/config.php';

// Verify student is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../login_page.php');
    exit();
}

// Fetch all staff members for the dropdown
$staff_query = "SELECT user_id, CONCAT(first_name, ' ', last_name) as name 
                FROM Users 
                WHERE role = 'Staff' AND status = 'active'";
$staff_result = mysqli_query($conn, $staff_query);

// Check for success/error messages
$alert = '';
if (isset($_SESSION['success'])) {
    $alert = '<div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert"><i class="bi bi-check-circle-fill me-2"></i>' . htmlspecialchars($_SESSION['success']) . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    unset($_SESSION['success']);
} elseif (isset($_SESSION['error'])) {
    $alert = '<div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert"><i class="bi bi-exclamation-triangle-fill me-2"></i>' . htmlspecialchars($_SESSION['error']) . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    unset($_SESSION['error']);
}

$student_id = $_SESSION['user_id'];
$first_name = $_SESSION['first_name'] ?? 'Student';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Meeting - GD Edu Tech</title>
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

        /* Form Card */
        .form-card {
            border: 1px solid var(--admin-border-color) !important;
            border-radius: 20px !important;
            box-shadow: 0 10px 30px -10px rgba(15, 23, 42, 0.05) !important;
            background: #ffffff;
            overflow: hidden;
        }

        .form-control-custom, .form-select-custom {
            border-radius: 12px !important;
            border: 1.5px solid #cbd5e1 !important;
            padding: 10px 14px !important;
            font-size: 0.88rem !important;
        }

        .form-control-custom:focus, .form-select-custom:focus {
            border-color: #0d7298 !important;
            box-shadow: 0 0 0 4px rgba(13, 114, 152, 0.12) !important;
        }

        /* Meeting Card */
        .meeting-card {
            border: 1px solid var(--admin-border-color) !important;
            border-radius: 20px !important;
            box-shadow: 0 10px 30px -10px rgba(15, 23, 42, 0.05) !important;
            background: #ffffff;
            transition: all 0.3s ease;
            height: 100%;
        }

        .meeting-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 14px 35px -10px rgba(15, 23, 42, 0.12) !important;
        }

        .status-badge {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 5px 12px;
            border-radius: 20px;
        }

        .btn-schedule {
            background: linear-gradient(135deg, #0d7298 0%, #065d7d 100%);
            color: #ffffff;
            border: none;
            border-radius: 50px;
            padding: 12px;
            font-weight: 700;
            box-shadow: 0 8px 20px rgba(13, 114, 152, 0.28);
            transition: all 0.3s ease;
        }

        .btn-schedule:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(13, 114, 152, 0.38);
            color: #ffffff;
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
                    <li class="w-100"><a href="./" class="nav-link active"><i class="bi bi-calendar-event"></i> Schedule</a></li>
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
                        <h4 class="fw-bold text-dark mb-0">Mentorship & Meeting Schedule</h4>
                        <span class="text-muted small">Book 1-on-1 guidance sessions with staff instructors</span>
                    </div>
                </div>

                <div class="p-4 flex-grow-1">
                    
                    <?php echo $alert; ?>

                    <div class="row g-4">
                        
                        <!-- Left Form Column -->
                        <div class="col-12 col-lg-5 col-xl-4">
                            <div class="card form-card border-0">
                                <div class="card-header bg-white border-bottom py-3 px-4">
                                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-calendar-plus-fill text-primary me-2"></i>Schedule New Meeting</h6>
                                </div>
                                <div class="card-body p-4">
                                    <form action="schedule_meeting.php" method="POST" id="scheduleForm">
                                        <!-- Select Staff -->
                                        <div class="mb-3">
                                            <label class="form-label font-weight-semibold small text-secondary">Select Staff / Instructor *</label>
                                            <select class="form-select form-select-custom" name="staff_id" required>
                                                <option value="">Choose staff member</option>
                                                <?php if ($staff_result && mysqli_num_rows($staff_result) > 0): ?>
                                                    <?php while ($staff = mysqli_fetch_assoc($staff_result)): ?>
                                                        <option value="<?php echo $staff['user_id']; ?>">
                                                            <?php echo htmlspecialchars($staff['name']); ?>
                                                        </option>
                                                    <?php endwhile; ?>
                                                <?php endif; ?>
                                            </select>
                                        </div>

                                        <!-- Subject -->
                                        <div class="mb-3">
                                            <label class="form-label font-weight-semibold small text-secondary">Meeting Subject *</label>
                                            <input type="text" class="form-control form-control-custom" name="subject" placeholder="e.g. Project Review & Guidance" maxlength="255" required>
                                        </div>

                                        <!-- Description -->
                                        <div class="mb-3">
                                            <label class="form-label font-weight-semibold small text-secondary">Description / Agenda *</label>
                                            <textarea class="form-control form-control-custom" name="description" rows="3" placeholder="Briefly describe what you'd like to discuss..." required></textarea>
                                        </div>

                                        <!-- Date & Time Row -->
                                        <div class="row g-2 mb-3">
                                            <div class="col-6">
                                                <label class="form-label font-weight-semibold small text-secondary">Meeting Date *</label>
                                                <input type="date" class="form-control form-control-custom" name="meeting_date" min="<?php echo date('Y-m-d'); ?>" required>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label font-weight-semibold small text-secondary">Meeting Time *</label>
                                                <input type="time" class="form-control form-control-custom" name="meeting_time" required>
                                            </div>
                                        </div>

                                        <!-- Meeting Link -->
                                        <div class="mb-4">
                                            <label class="form-label font-weight-semibold small text-secondary">Meeting Link (Google Meet / Zoom) *</label>
                                            <input type="url" class="form-control form-control-custom" name="meeting_link" placeholder="https://meet.google.com/xyz-abc" required>
                                        </div>

                                        <button type="submit" class="btn btn-schedule w-100">
                                            <i class="bi bi-calendar-check me-1.5"></i> Schedule Meeting
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Right Scheduled Meetings Grid -->
                        <div class="col-12 col-lg-7 col-xl-8">
                            <div class="card border-0 rounded-4 shadow-sm p-4 mb-4">
                                <h6 class="fw-bold text-dark mb-4"><i class="bi bi-clock-history text-primary me-2"></i>My Scheduled Meetings</h6>

                                <?php
                                $meetings_query = "SELECT m.*, 
                                                 CONCAT(u.first_name, ' ', u.last_name) as staff_name,
                                                 DATE_FORMAT(m.meeting_date, '%d %M %Y') as formatted_date,
                                                 TIME_FORMAT(m.meeting_time, '%h:%i %p') as formatted_time
                                                 FROM meeting_schedules m 
                                                 JOIN Users u ON m.staff_id = u.user_id 
                                                 WHERE m.student_id = ? 
                                                 ORDER BY 
                                                    CASE m.status 
                                                        WHEN 'pending' THEN 1
                                                        WHEN 'approved' THEN 2
                                                        WHEN 'completed' THEN 3
                                                        WHEN 'rejected' THEN 4
                                                    END,
                                                    m.meeting_date ASC, 
                                                    m.meeting_time ASC";

                                $stmt = mysqli_prepare($conn, $meetings_query);
                                mysqli_stmt_bind_param($stmt, 'i', $student_id);
                                mysqli_stmt_execute($stmt);
                                $meetings_result = mysqli_stmt_get_result($stmt);

                                if ($meetings_result && mysqli_num_rows($meetings_result) > 0):
                                ?>
                                    <div class="row row-cols-1 row-cols-md-2 g-3">
                                        <?php while ($meeting = mysqli_fetch_assoc($meetings_result)): ?>
                                            <div class="col">
                                                <div class="card meeting-card border-0 p-3.5 d-flex flex-column">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <h6 class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($meeting['subject']); ?></h6>
                                                        <?php
                                                            $st = strtolower($meeting['status']);
                                                            if ($st === 'approved') {
                                                                echo '<span class="badge bg-success bg-opacity-10 text-success border border-success-subtle status-badge">Approved</span>';
                                                            } elseif ($st === 'rejected') {
                                                                echo '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle status-badge">Rejected</span>';
                                                            } elseif ($st === 'completed') {
                                                                echo '<span class="badge bg-info bg-opacity-10 text-info border border-info-subtle status-badge">Completed</span>';
                                                            } else {
                                                                echo '<span class="badge bg-warning bg-opacity-20 text-dark border border-warning-subtle status-badge">Pending</span>';
                                                            }
                                                        ?>
                                                    </div>

                                                    <span class="text-muted small mb-2 d-block">
                                                        <i class="bi bi-person me-1 text-primary"></i>With: <strong><?php echo htmlspecialchars($meeting['staff_name']); ?></strong>
                                                    </span>

                                                    <div class="bg-light p-2.5 rounded-3 mb-3 small text-secondary">
                                                        <div><i class="bi bi-calendar3 me-1.5 text-primary"></i><?php echo $meeting['formatted_date']; ?></div>
                                                        <div><i class="bi bi-clock me-1.5 text-primary"></i><?php echo $meeting['formatted_time']; ?></div>
                                                    </div>

                                                    <?php if (!empty($meeting['description'])): ?>
                                                        <p class="text-secondary small flex-grow-1 mb-3">
                                                            "<?php echo nl2br(htmlspecialchars($meeting['description'])); ?>"
                                                        </p>
                                                    <?php endif; ?>

                                                    <?php if (!empty($meeting['meeting_link']) && strtolower($meeting['status']) === 'approved'): ?>
                                                        <a href="<?php echo htmlspecialchars($meeting['meeting_link']); ?>" target="_blank" class="btn btn-success btn-sm w-100 rounded-pill py-2 font-weight-semibold mt-auto">
                                                            <i class="bi bi-camera-video-fill me-1.5"></i>Join Meeting
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-5 text-muted">
                                        <i class="bi bi-calendar-x fs-1 text-secondary opacity-50 d-block mb-2"></i>
                                        You haven't scheduled any mentorship meetings yet.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Prevent scheduling meetings in the past
        const scheduleForm = document.getElementById('scheduleForm');
        if (scheduleForm) {
            scheduleForm.addEventListener('submit', function(e) {
                const dateInput = document.querySelector('input[name="meeting_date"]');
                const timeInput = document.querySelector('input[name="meeting_time"]');

                if (dateInput && timeInput && dateInput.value && timeInput.value) {
                    const selectedDateTime = new Date(dateInput.value + 'T' + timeInput.value);
                    const now = new Date();

                    if (selectedDateTime < now) {
                        e.preventDefault();
                        alert('Please select a future date and time for the meeting.');
                    }
                }
            });
        }
    </script>
</body>
</html>