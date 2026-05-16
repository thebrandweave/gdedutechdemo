<?php
session_start();
require_once '../../Configurations/config.php';

// Verify student is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../../login.php');
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
    $alert = '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
    unset($_SESSION['success']);
} elseif (isset($_SESSION['error'])) {
    $alert = '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Meeting</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/css/student_dashboard.css">

    <link rel="icon" type="image/png" href="../../Images/Logos/GD_Only_logo.png">
    <link rel="shortcut icon" href="../../Images/Logos/GD_Only_logo.png">
    <style>
        .meeting-card {
            transition: transform 0.2s;
        }

        .meeting-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .status-badge {
            font-size: 0.85em;
            padding: 5px 10px;
        }

        /* New styles for form */
        .form-card {
            border: none;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
        }

        .form-card .card-header {
            background: linear-gradient(135deg, #2C3E50, #3498DB);
            color: white;
            border-radius: 15px 15px 0 0;
            border: none;
            padding: 1.5rem;
        }

        .form-card .form-control:focus {
            border-color: #3498DB;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .form-card .btn-primary {
            background: linear-gradient(135deg, #2C3E50, #3498DB);
            border: none;
            padding: 0.8rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }

        .form-card .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .form-label {
            font-weight: 500;
            color: #2C3E50;
        }

        .form-select:focus {
            border-color: #3498DB;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
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

            .main-content.hidden {
                display: none;
                /* Hide main content when sidebar is open */
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
                            <a href="../Schedule" class="nav-link active">
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

            <!-- Main Content -->
            <div class="col py-3 mainContent">
                <div class="container">
                    <?php echo $alert; ?>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="card form-card shadow-sm">
                                <div class="card-header">
                                    <h4 class="mb-0">Schedule New Meeting</h4>
                                </div>
                                <div class="card-body">
                                    <form action="schedule_meeting.php" method="POST" id="scheduleForm">
                                        <div class="mb-3">
                                            <label for="staff" class="form-label">Select Staff</label>
                                            <select class="form-select" name="staff_id" required>
                                                <option value="">Choose staff member</option>
                                                <?php while ($staff = mysqli_fetch_assoc($staff_result)): ?>
                                                    <option value="<?php echo $staff['user_id']; ?>">
                                                        <?php echo htmlspecialchars($staff['name']); ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="subject" class="form-label">Subject</label>
                                            <input type="text" class="form-control" name="subject"
                                                maxlength="255" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="description" class="form-label">Description</label>
                                            <textarea class="form-control" name="description"
                                                rows="3" required></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label for="meeting_date" class="form-label">Date</label>
                                            <input type="date" class="form-control" name="meeting_date"
                                                min="<?php echo date('Y-m-d'); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="meeting_time" class="form-label">Time</label>
                                            <input type="time" class="form-control" name="meeting_time" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="meeting_link" class="form-label">Meeting Link</label>
                                            <input type="url" class="form-control" name="meeting_link"
                                                placeholder="https://example.com/meeting" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary w-100">
                                            Schedule Meeting
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <h3 class="mb-4">My Scheduled Meetings</h3>
                            <?php
                            $student_id = $_SESSION['user_id'];
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

                            if (mysqli_num_rows($meetings_result) > 0):
                            ?>
                                <div class="row">
                                    <?php while ($meeting = mysqli_fetch_assoc($meetings_result)): ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="card meeting-card">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <h5 class="card-title"><?php echo htmlspecialchars($meeting['subject']); ?></h5>
                                                        <span class="badge bg-<?php
                                                                                echo match ($meeting['status']) {
                                                                                    'pending' => 'warning',
                                                                                    'approved' => 'success',
                                                                                    'rejected' => 'danger',
                                                                                    'completed' => 'info',
                                                                                    default => 'secondary'
                                                                                };
                                                                                ?> status-badge">
                                                            <?php echo ucfirst($meeting['status']); ?>
                                                        </span>
                                                    </div>
                                                    <p class="card-text">
                                                        <small class="text-muted">
                                                            With: <?php echo htmlspecialchars($meeting['staff_name']); ?>
                                                        </small>
                                                    </p>
                                                    <p class="card-text">
                                                        <i class="bi bi-calendar"></i> <?php echo $meeting['formatted_date']; ?>
                                                        <br>
                                                        <i class="bi bi-clock"></i> <?php echo $meeting['formatted_time']; ?>
                                                    </p>
                                                    <?php if (!empty($meeting['description'])): ?>
                                                        <p class="card-text">
                                                            <?php echo nl2br(htmlspecialchars($meeting['description'])); ?>
                                                        </p>
                                                    <?php endif; ?>
                                                    <?php if ($meeting['meeting_link'] && $meeting['status'] === 'approved'): ?>
                                                        <a href="<?php echo htmlspecialchars($meeting['meeting_link']); ?>"
                                                            target="_blank"
                                                            class="btn btn-primary btn-sm">
                                                            Join Meeting
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    You haven't scheduled any meetings yet.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Prevent scheduling meetings in the past
        document.getElementById('scheduleForm').addEventListener('submit', function(e) {
            const dateInput = document.querySelector('input[name="meeting_date"]');
            const timeInput = document.querySelector('input[name="meeting_time"]');

            const selectedDateTime = new Date(dateInput.value + 'T' + timeInput.value);
            const now = new Date();

            if (selectedDateTime < now) {
                e.preventDefault();
                alert('Please select a future date and time for the meeting.');
            }
        });

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