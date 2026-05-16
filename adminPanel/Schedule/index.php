<?php
session_start();
require_once '../../Configurations/config.php';

// Verify admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../admin_login.php');
    exit();
}


// Check for success/error messages
$alert = '';
if (isset($_SESSION['success'])) {
    $alert = '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
    unset($_SESSION['success']);
} elseif (isset($_SESSION['error'])) {
    $alert = '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
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
    <title>Manage Meeting Schedules</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .meeting-details {
            font-size: 0.9rem;
        }
        .status-badge {
            min-width: 100px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 sidebar">
                <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 min-vh-100">
                    <a href="#" class="d-flex align-items-center pb-3 mb-md-1 mt-md-3 me-md-auto text-white text-decoration-none">
                        <span class="fs-5 fw-bolder" style="display: flex;align-items:center;color:black;">
                            <img height="35px" src="../../Images/Logos/edutechLogo.png" alt="">&nbsp; GD Edu Tech
                        </span>
                    </a>
                    <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100" id="menu">
                        <li class="w-100">
                            <a href="../" class="nav-link">
                                <i class="bi bi-speedometer2 me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Categories/" class="nav-link">
                                <i class="bi bi-grid me-2"></i> Categories
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Courses/" class="nav-link">
                                <i class="bi bi-book me-2"></i> Courses
                            </a>
                        </li>
                        <li class="w-100">
                          <a href="../Applications/" class="nav-link">
                                <i class="bi bi-journal-text me-2"></i> Scholarship Applications
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Events/" class="nav-link">
                                <i class="bi bi-calendar2-event me-2"></i> Events
                            </a>
                        </li>
                        <li class="w-100 dropdown">
                            <a href="#" class="nav-link dropdown-toggle" id="quizDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-lightbulb me-2"></i> Quick Links
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="quizDropdown">
                                <li><a class="dropdown-item" href="../Career/index.php">Career portal</a></li>
                                <li><a class="dropdown-item" href="../Shop/shop.php">Shop</a></li>
                            </ul>
                        </li>
                        <li class="w-100">
                            <a href="../Schedule/" class="nav-link active">
                                <i class="bi bi-calendar-event me-2"></i> Schedule
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Messages/" class="nav-link">
                                <i class="bi bi-chat-dots me-2"></i> Messages
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../FAQ/" class="nav-link">
                                <i class="bi bi-question-circle me-2"></i> FAQ
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Users/" class="nav-link">
                                <i class="bi bi-people me-2"></i> Users
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../manage_qr.php" class="nav-link">
                                <i class="bi bi-qr-code me-2"></i> Payment QR
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../pending_payments.php" class="nav-link">
                                <i class="bi bi-credit-card me-2"></i> Pending Payments
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
            <div class="col-md-10">
                <div class="container mt-5">
                    <?php echo $alert; ?>
                    <div class="row justify-content-center">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Meeting Requests</h5>
                                </div>
                                <div class="card-body">
                                    <?php if (mysqli_num_rows($meetings_result) > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Student</th>
                                                        <th>Staff</th>
                                                        <th>Subject</th>
                                                        <th>Date & Time</th>
                                                        <th>Status</th>
                                                        <th>Meeting Link</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php while ($meeting = mysqli_fetch_assoc($meetings_result)): ?>
                                                        <tr>
                                                            <td>
                                                                <?php echo htmlspecialchars($meeting['student_name']); ?>
                                                                <br>
                                                                <small class="text-muted"><?php echo htmlspecialchars($meeting['student_email']); ?></small>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($meeting['staff_name']); ?></td>
                                                            <td>
                                                                <?php echo htmlspecialchars($meeting['subject']); ?>
                                                                <button class="btn btn-sm btn-link" 
                                                                        data-bs-toggle="tooltip" 
                                                                        title="<?php echo htmlspecialchars($meeting['description']); ?>">
                                                                    <i class="bi bi-info-circle"></i>
                                                                </button>
                                                            </td>
                                                            <td>
                                                                <?php echo $meeting['formatted_date']; ?><br>
                                                                <?php echo $meeting['formatted_time']; ?>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-<?php 
                                                                    echo match($meeting['status']) {
                                                                        'pending' => 'warning',
                                                                        'approved' => 'success',
                                                                        'rejected' => 'danger',
                                                                        'completed' => 'info',
                                                                        default => 'secondary'
                                                                    };
                                                                ?> status-badge">
                                                                    <?php echo ucfirst($meeting['status']); ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <?php if ($meeting['meeting_link']): ?>
                                                                    <a href="<?php echo htmlspecialchars($meeting['meeting_link']); ?>" 
                                                                       target="_blank" 
                                                                       class="btn btn-sm btn-primary">
                                                                        <i class="bi bi-link-45deg"></i> Join
                                                                    </a>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <?php if ($meeting['status'] === 'pending'): ?>
                                                                    <div class="btn-group">
                                                                        <a href="update_status.php?id=<?php echo $meeting['id']; ?>&status=approved" 
                                                                           class="btn btn-sm btn-success">
                                                                            <i class="bi bi-check-lg"></i> Approve
                                                                        </a>
                                                                        <a href="update_status.php?id=<?php echo $meeting['id']; ?>&status=rejected" 
                                                                           class="btn btn-sm btn-danger">
                                                                            <i class="bi bi-x-lg"></i> Reject
                                                                        </a>
                                                                    </div>
                                                                <?php elseif ($meeting['status'] === 'approved' && !$meeting['meeting_link']): ?>
                                                                    <button type="button" 
                                                                            class="btn btn-sm btn-primary" 
                                                                            data-bs-toggle="modal" 
                                                                            data-bs-target="#addLinkModal<?php echo $meeting['id']; ?>">
                                                                        <i class="bi bi-link-45deg"></i> Add Link
                                                                    </button>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-info">
                                            No meeting requests found.
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    </script>
</body>
</html>
