<?php
session_start();
require_once '../../Configurations/config.php';

// Verify admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../admin_login.php');
    exit();
}

$admin_name = $_SESSION['username'] ?? 'Admin';

// Check for success/error messages
$alert = '';
if (isset($_SESSION['success'])) {
    $alert = '<div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert"><div class="d-flex align-items-center gap-2"><i class="bi bi-check-circle-fill fs-5"></i><span class="fw-semibold">' . htmlspecialchars($_SESSION['success']) . '</span></div><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    unset($_SESSION['success']);
} elseif (isset($_SESSION['error'])) {
    $alert = '<div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert"><div class="d-flex align-items-center gap-2"><i class="bi bi-exclamation-triangle-fill fs-5"></i><span class="fw-semibold">' . htmlspecialchars($_SESSION['error']) . '</span></div><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    unset($_SESSION['error']);
}

// Fetch all meetings with student and staff details
$meetings_query = "SELECT m.*, 
                    CONCAT(s.first_name, ' ', s.last_name) as student_name,
                    CONCAT(t.first_name, ' ', t.last_name) as staff_name,
                    s.email as student_email,
                    DATE_FORMAT(m.meeting_date, '%d %M %Y') as formatted_date,
                    TIME_FORMAT(m.meeting_time, '%h:%i %p') as formatted_time
                  FROM meeting_schedules m 
                  JOIN Users s ON m.student_id = s.user_id 
                  JOIN Users t ON m.staff_id = t.user_id 
                  ORDER BY 
                    CASE m.status 
                        WHEN 'pending' THEN 1
                        WHEN 'approved' THEN 2
                        WHEN 'completed' THEN 3
                        WHEN 'rejected' THEN 4
                    END,
                    m.meeting_date ASC, 
                    m.meeting_time ASC";

$meetings_result = mysqli_query($conn, $meetings_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Meeting Schedules - GD Edu Tech Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="../../Images/Logos/GD_Only_logo.png">
    <link rel="stylesheet" href="../css/style.css">
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
                      
                    </div>
                </div>

                <ul class="nav nav-pills flex-column mb-auto p-2 w-100" id="menu">
                    <li class="w-100"><a href="../" class="nav-link"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
                    <li class="w-100"><a href="../Categories/" class="nav-link"><i class="bi bi-grid me-2"></i> Categories</a></li>
                    <li class="w-100"><a href="../Admissions/" class="nav-link"><i class="bi bi-person-plus me-2"></i> Student Admission</a></li>
                    <li class="w-100"><a href="../Courses/" class="nav-link"><i class="bi bi-book me-2"></i> Courses</a></li>
                    <li class="w-100"><a href="../Applications/" class="nav-link"><i class="bi bi-journal-text me-2"></i> Scholarships</a></li>
                    <li class="w-100"><a href="../Events/" class="nav-link"><i class="bi bi-calendar2-event me-2"></i> Events</a></li>
                    <li class="w-100"><a href="../social_links.php" class="nav-link"><i class="bi bi-link-45deg me-2"></i> Social Links</a></li>
                    <li class="w-100"><a href="../Schedule/index.php" class="nav-link active"><i class="bi bi-calendar-event me-2"></i> Schedule</a></li>
                    <li class="w-100"><a href="../feedback/feedback.php" class="nav-link"><i class="bi bi-chat-square-heart me-2"></i> Feedback</a></li>
                    <li class="w-100"><a href="../Messages/index.php" class="nav-link"><i class="bi bi-chat-dots me-2"></i> Messages</a></li>
                    <li class="w-100"><a href="../FAQ/" class="nav-link"><i class="bi bi-question-circle me-2"></i> FAQ</a></li>
                    <li class="w-100"><a href="../Users/" class="nav-link"><i class="bi bi-people me-2"></i> Users</a></li>
                    <li class="w-100"><a href="../manage_qr.php" class="nav-link"><i class="bi bi-qr-code me-2"></i> Payment QR</a></li>
                    <li class="w-100"><a href="../pending_payments.php" class="nav-link"><i class="bi bi-credit-card me-2"></i> Pending Payments</a></li>
                </ul>

                <div class="p-3 border-top border-white border-opacity-10 mt-auto">
                    <a href="../logout.php" class="nav-link text-danger justify-content-center m-0">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </a>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="col min-vh-100 d-flex flex-column">
                
                <!-- Top Header -->
                <div class="bg-white border-bottom px-4 py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="fw-bold text-dark mb-0">Meeting Schedule Center</h4>
                        <span class="text-muted small">Manage 1-on-1 student-staff mentorship sessions and status approvals</span>
                    </div>
                </div>

                <div class="p-4 flex-grow-1">
                    
                    <?php echo $alert; ?>

                    <!-- Meetings Table Card -->
                    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                        <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-calendar-check-fill text-primary me-2"></i>Scheduled Meetings Directory</h6>
                            <span class="badge bg-primary bg-opacity-10 text-primary border px-3 py-1.5 rounded-pill fw-semibold">
                                Total: <?php echo mysqli_num_rows($meetings_result); ?> Sessions
                            </span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Assigned Staff</th>
                                        <th>Topic</th>
                                        <th>Date & Time</th>
                                        <th>Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($meetings_result && mysqli_num_rows($meetings_result) > 0): ?>
                                        <?php while ($meeting = mysqli_fetch_assoc($meetings_result)): ?>
                                            <?php
                                                $statusClass = 'bg-secondary';
                                                if ($meeting['status'] === 'pending') $statusClass = 'bg-warning text-dark';
                                                elseif ($meeting['status'] === 'approved') $statusClass = 'bg-primary';
                                                elseif ($meeting['status'] === 'completed') $statusClass = 'bg-success';
                                                elseif ($meeting['status'] === 'rejected') $statusClass = 'bg-danger';
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="rounded-circle bg-light border text-dark d-flex align-items-center justify-content-center fw-bold small" style="width: 34px; height: 34px;">
                                                            <?php echo strtoupper(substr($meeting['student_name'] ?? 'S', 0, 1)); ?>
                                                        </div>
                                                        <div>
                                                            <strong class="text-dark d-block"><?php echo htmlspecialchars($meeting['student_name']); ?></strong>
                                                            <span class="text-muted small"><?php echo htmlspecialchars($meeting['student_email'] ?? ''); ?></span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="fw-semibold text-dark"><?php echo htmlspecialchars($meeting['staff_name']); ?></span>
                                                </td>
                                                <td>
                                                    <span class="text-secondary"><?php echo htmlspecialchars($meeting['topic'] ?? 'Mentorship Session'); ?></span>
                                                </td>
                                                <td>
                                                    <span class="text-dark small d-block"><i class="bi bi-calendar3 me-1 text-primary"></i><?php echo $meeting['formatted_date']; ?></span>
                                                    <span class="text-muted small"><i class="bi bi-clock me-1 text-warning"></i><?php echo $meeting['formatted_time']; ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge <?php echo $statusClass; ?> px-2.5 py-1 rounded-pill text-uppercase">
                                                        <?php echo htmlspecialchars($meeting['status']); ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <form method="POST" action="update_status.php" class="d-inline-flex gap-1">
                                                        <input type="hidden" name="meeting_id" value="<?php echo $meeting['id']; ?>">
                                                        
                                                        <?php if ($meeting['status'] === 'pending'): ?>
                                                            <button type="submit" name="status" value="approved" class="btn btn-sm btn-success rounded-pill px-2.5 py-1" title="Approve Meeting">
                                                                <i class="bi bi-check-circle me-1"></i> Approve
                                                            </button>
                                                            <button type="submit" name="status" value="rejected" class="btn btn-sm btn-danger rounded-pill px-2.5 py-1" title="Reject Meeting">
                                                                <i class="bi bi-x-circle me-1"></i> Reject
                                                            </button>
                                                        <?php elseif ($meeting['status'] === 'approved'): ?>
                                                            <button type="submit" name="status" value="completed" class="btn btn-sm btn-primary rounded-pill px-2.5 py-1" title="Mark Completed">
                                                                <i class="bi bi-check-all me-1"></i> Complete
                                                            </button>
                                                        <?php else: ?>
                                                            <span class="text-muted small">No action</span>
                                                        <?php endif; ?>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">No scheduled meetings found.</td>
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

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
