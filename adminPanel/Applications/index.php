<?php
session_start();

// Admin auth check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../admin_login.php');
    exit();
}

require_once '../../Configurations/config.php';

// Fetch applications
$result = mysqli_query($conn, "SELECT * FROM applications ORDER BY id DESC");
$admin_name = $_SESSION['username'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scholarship Applications - GD Edu Tech Admin</title>
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
                    <li class="w-100"><a href="../Applications/" class="nav-link active"><i class="bi bi-journal-text me-2"></i> Scholarships</a></li>
                    <li class="w-100"><a href="../Events/" class="nav-link"><i class="bi bi-calendar2-event me-2"></i> Events</a></li>
                    <li class="w-100"><a href="../Career/" class="nav-link"><i class="bi bi-briefcase me-2"></i> Careers</a></li>
                    <li class="w-100"><a href="../social_links.php" class="nav-link"><i class="bi bi-link-45deg me-2"></i> Social Links</a></li>
                    <li class="w-100"><a href="../Schedule/index.php" class="nav-link"><i class="bi bi-calendar-event me-2"></i> Schedule</a></li>
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
                        <h4 class="fw-bold text-dark mb-0">Scholarship Applications</h4>
                        <span class="text-muted small">Review submitted student scholarship application forms and attached documents</span>
                    </div>
                </div>

                <div class="p-4 flex-grow-1">
                    
                    <!-- Applications Card -->
                    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                        <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-journal-check text-primary me-2"></i>Application Submissions Queue</h6>
                            <span class="badge bg-primary bg-opacity-10 text-primary border px-3 py-1.5 rounded-pill fw-semibold">
                                <?php echo mysqli_num_rows($result); ?> Applications
                            </span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Applicant Photo</th>
                                        <th>Full Name</th>
                                        <th>Phone Number</th>
                                        <th>Course Applied</th>
                                        <th>Medium</th>
                                        <th>Languages</th>
                                        <th>School / College</th>
                                        <th>Attachment Document</th>
                                        <th>Applied Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                                            <tr>
                                                <td>
                                                    <?php if (!empty($row['photo'])): ?>
                                                        <a href="../../uploads/<?php echo htmlspecialchars($row['photo']); ?>" target="_blank">
                                                            <img src="../../uploads/<?php echo htmlspecialchars($row['photo']); ?>" alt="Photo" class="rounded-3 border object-fit-cover" style="width: 44px; height: 44px;">
                                                        </a>
                                                    <?php else: ?>
                                                        <div class="rounded-circle bg-light border text-muted d-flex align-items-center justify-content-center fw-bold" style="width: 44px; height: 44px;">
                                                            <i class="bi bi-person fs-5"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <strong class="text-dark fs-6"><?php echo htmlspecialchars($row['first_name'] . " " . $row['last_name']); ?></strong>
                                                </td>
                                                <td>
                                                    <span class="font-monospace text-dark"><?php echo htmlspecialchars($row['phone']); ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2.5 py-1 rounded-pill">
                                                        <?php echo htmlspecialchars($row['course']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($row['medium'] ?? '-'); ?></td>
                                                <td><?php echo htmlspecialchars($row['languages'] ?? '-'); ?></td>
                                                <td>
                                                    <span class="text-secondary small"><?php echo htmlspecialchars($row['school'] ?? '-'); ?></span>
                                                </td>
                                                <td>
                                                    <?php if (!empty($row['document'])): ?>
                                                        <a href="../../uploads/<?php echo htmlspecialchars($row['document']); ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 font-weight-semibold">
                                                            <i class="bi bi-file-earmark-pdf me-1"></i> View Document
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted small">No Document</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-muted small">
                                                    <?php echo !empty($row['created_at']) ? date('M d, Y', strtotime($row['created_at'])) : '-'; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9" class="text-center py-4 text-muted">No applications submitted yet.</td>
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