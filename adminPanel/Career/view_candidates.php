<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../admin_login.php');
    exit();
}

// Database connection
require_once '../../Configurations/config.php';

// Get admin details from session
$admin_name = $_SESSION['username'] ?? 'Admin';

// Add resume base path
$resume_base_path = '../../uploads/resumes/';
if (!is_dir($resume_base_path)) {
    mkdir($resume_base_path, 0777, true);
}

// Fetch all applications
$query = "SELECT ja.*, c.job_title, c.company_name 
          FROM job_applications ja
          JOIN Careers c ON ja.job_id = c.job_id 
          ORDER BY ja.application_date DESC";
$result = mysqli_query($conn, $query);

// Handle delete request
if (isset($_POST['delete_application'])) {
    $application_id = mysqli_real_escape_string($conn, $_POST['application_id']);
    
    // First get the resume path
    $get_res_query = "SELECT resume_path FROM job_applications WHERE application_id = '$application_id'";
    $res_result = mysqli_query($conn, $get_res_query);
    $application = mysqli_fetch_assoc($res_result);
    
    // Delete the resume file if it exists
    if ($application && $application['resume_path']) {
        $resume_file = $resume_base_path . basename($application['resume_path']);
        if (file_exists($resume_file)) {
            @unlink($resume_file);
        }
    }
    
    // Delete the database record
    $delete_query = "DELETE FROM job_applications WHERE application_id = '$application_id'";
    if (mysqli_query($conn, $delete_query)) {
        $_SESSION['message'] = "Candidate application deleted successfully.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error deleting application: " . mysqli_error($conn);
        $_SESSION['message_type'] = "danger";
    }
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidate Applications - GD Edu Tech Admin</title>
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
                    <li class="w-100"><a href="./" class="nav-link active"><i class="bi bi-briefcase me-2"></i> Careers</a></li>
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
                
                <!-- Top Header Bar -->
                <div class="bg-white border-bottom px-4 py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="fw-bold text-dark mb-0">Job Candidates & Applications</h4>
                        <span class="text-muted small">Review submitted candidate resumes, cover letters, and application details</span>
                    </div>
                    <a href="index.php" class="btn btn-outline-secondary rounded-pill">
                        <i class="bi bi-arrow-left me-1.5"></i> Back to Job Openings
                    </a>
                </div>

                <div class="p-4 flex-grow-1">
                    
                    <!-- Alert Messages -->
                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-info-circle-fill fs-5"></i>
                                <span class="fw-semibold"><?php echo htmlspecialchars($_SESSION['message']); ?></span>
                            </div>
                            <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Candidates Table Card -->
                    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                        <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-people-fill text-primary me-2"></i>Applicant Submissions</h6>
                            <span class="badge bg-primary bg-opacity-10 text-primary border px-3 py-1.5 rounded-pill fw-semibold">
                                Total: <?php echo mysqli_num_rows($result); ?> Applications
                            </span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                                <thead>
                                    <tr>
                                        <th>App ID</th>
                                        <th>Job Title</th>
                                        <th>Candidate Name</th>
                                        <th>Email Address</th>
                                        <th>Phone</th>
                                        <th>Applied Date</th>
                                        <th>Resume</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-light text-dark border px-2.5 py-1 rounded-pill font-monospace">
                                                        #<?php echo $row['application_id']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <strong class="text-primary d-block"><?php echo htmlspecialchars($row['job_title']); ?></strong>
                                                    <span class="text-muted small"><?php echo htmlspecialchars($row['company_name']); ?></span>
                                                </td>
                                                <td>
                                                    <strong class="text-dark d-block"><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></strong>
                                                </td>
                                                <td>
                                                    <span class="text-dark small"><i class="bi bi-envelope-fill text-primary me-1"></i><?php echo htmlspecialchars($row['email']); ?></span>
                                                </td>
                                                <td>
                                                    <span class="text-dark small"><i class="bi bi-telephone-fill text-success me-1"></i><?php echo htmlspecialchars($row['phone']); ?></span>
                                                </td>
                                                <td class="text-muted small">
                                                    <?php echo date('M d, Y', strtotime($row['application_date'])); ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($row['resume_path'])): ?>
                                                        <a href="<?php echo $resume_base_path . basename($row['resume_path']); ?>" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-2.5 py-0.5" style="font-size: 0.75rem;">
                                                            <i class="bi bi-file-earmark-pdf-fill me-1"></i>Resume
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="badge bg-light text-muted border px-2 py-0.5">No File</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-flex justify-content-center gap-1.5">
                                                        <button type="button" 
                                                                class="action-icon btn border-0 p-0 text-info" 
                                                                onclick="viewApplication(<?php echo $row['application_id']; ?>)"
                                                                title="View Candidate Application">
                                                            <i class="bi bi-eye-fill fs-6"></i>
                                                        </button>
                                                        <form method="POST" style="display: inline;" 
                                                              onsubmit="return confirm('Are you sure you want to delete this application?');">
                                                            <input type="hidden" name="application_id" value="<?php echo $row['application_id']; ?>">
                                                            <button type="submit" name="delete_application" class="action-icon btn border-0 p-0 text-danger" title="Delete Application">
                                                                <i class="bi bi-trash-fill fs-6"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-4 text-muted">No candidate applications submitted yet.</td>
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

    <!-- Application Details Modal -->
    <div class="modal fade" id="applicationModal" tabindex="-1" aria-labelledby="applicationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                <div class="modal-header bg-dark text-white p-4">
                    <h5 class="modal-title fw-bold" id="applicationModalLabel"><i class="bi bi-person-bounding-box text-info me-2"></i>Candidate Application Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" id="applicationModalBody">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function viewApplication(applicationId) {
        const modalBody = document.getElementById('applicationModalBody');
        modalBody.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading application...</span>
                </div>
                <p class="text-muted small mt-2">Fetching candidate details...</p>
            </div>
        `;
        const modal = new bootstrap.Modal(document.getElementById('applicationModal'));
        modal.show();

        fetch(`view_application.php?id=${applicationId}`)
            .then(response => response.text())
            .then(data => {
                modalBody.innerHTML = data;
            })
            .catch(error => {
                console.error('Error:', error);
                modalBody.innerHTML = '<div class="alert alert-danger mb-0">Error loading candidate application details.</div>';
            });
    }
    </script>
</body>
</html>
