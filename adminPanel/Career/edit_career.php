<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../admin_login.php');
    exit();
}

// Database connection
require_once '../../Configurations/config.php';

// Get job ID and fetch details
if (!isset($_GET['id'])) {
    $_SESSION['message'] = "Invalid job ID";
    $_SESSION['message_type'] = "danger";
    header('Location: index.php');
    exit();
}

$job_id = mysqli_real_escape_string($conn, $_GET['id']);
$query = "SELECT * FROM Careers WHERE job_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $job_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$career = mysqli_fetch_assoc($result);

if (!$career) {
    $_SESSION['message'] = "Career listing not found";
    $_SESSION['message_type'] = "danger";
    header('Location: index.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $job_title = mysqli_real_escape_string($conn, $_POST['job_title']);
    $company_name = mysqli_real_escape_string($conn, $_POST['company_name']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $salary_range = mysqli_real_escape_string($conn, $_POST['salary_range']);
    $job_type = mysqli_real_escape_string($conn, $_POST['job_type']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $job_description = mysqli_real_escape_string($conn, $_POST['job_description']);
    $requirements = mysqli_real_escape_string($conn, $_POST['requirements']);
    $benefits = mysqli_real_escape_string($conn, $_POST['benefits']);
    $application_deadline = mysqli_real_escape_string($conn, $_POST['application_deadline']);

    $update_query = "UPDATE Careers SET 
        job_title = ?,
        company_name = ?,
        location = ?,
        salary_range = ?,
        job_type = ?,
        status = ?,
        job_description = ?,
        requirements = ?,
        benefits = ?,
        application_deadline = ?
        WHERE job_id = ?";

    $update_stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($update_stmt, "ssssssssssi", 
        $job_title, $company_name, $location, $salary_range, $job_type, $status,
        $job_description, $requirements, $benefits, $application_deadline, $job_id
    );

    if (mysqli_stmt_execute($update_stmt)) {
        $_SESSION['message'] = "Career listing updated successfully!";
        $_SESSION['message_type'] = "success";
        header('Location: index.php');
        exit();
    } else {
        $_SESSION['message'] = "Error updating career listing: " . mysqli_error($conn);
        $_SESSION['message_type'] = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Career Listing - GD Edu Tech Admin</title>
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
                        <h4 class="fw-bold text-dark mb-0">Edit Career Opportunity</h4>
                        <span class="text-muted small">Update details for Job Listing #<?php echo $career['job_id']; ?></span>
                    </div>
                    <a href="index.php" class="btn btn-outline-secondary rounded-pill">
                        <i class="bi bi-arrow-left me-1.5"></i> Back to Careers
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

                    <!-- Edit Form Card -->
                    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                        <div class="card-header bg-white py-3 px-4 border-bottom">
                            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-pencil-square text-warning me-2"></i>Edit Listing Details</h6>
                        </div>
                        <div class="card-body p-4">
                            <form action="" method="POST">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="job_title" class="form-label font-weight-semibold">Job Title <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="job_title" name="job_title" 
                                               value="<?php echo htmlspecialchars($career['job_title']); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="company_name" class="form-label font-weight-semibold">Company Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="company_name" name="company_name" 
                                               value="<?php echo htmlspecialchars($career['company_name']); ?>" required>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="location" class="form-label font-weight-semibold">Location <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="location" name="location" 
                                               value="<?php echo htmlspecialchars($career['location']); ?>" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="salary_range" class="form-label font-weight-semibold">Salary Range</label>
                                        <input type="text" class="form-control" id="salary_range" name="salary_range" 
                                               value="<?php echo htmlspecialchars($career['salary_range'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <label for="job_type" class="form-label font-weight-semibold">Job Type</label>
                                        <select class="form-select" id="job_type" name="job_type" required>
                                            <option value="Full-time" <?php echo $career['job_type'] === 'Full-time' ? 'selected' : ''; ?>>Full-time</option>
                                            <option value="Part-time" <?php echo $career['job_type'] === 'Part-time' ? 'selected' : ''; ?>>Part-time</option>
                                            <option value="Contract" <?php echo $career['job_type'] === 'Contract' ? 'selected' : ''; ?>>Contract</option>
                                            <option value="Internship" <?php echo $career['job_type'] === 'Internship' ? 'selected' : ''; ?>>Internship</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="status" class="form-label font-weight-semibold">Status</label>
                                        <select class="form-select" id="status" name="status" required>
                                            <option value="Active" <?php echo $career['status'] === 'Active' ? 'selected' : ''; ?>>Active</option>
                                            <option value="Closed" <?php echo $career['status'] === 'Closed' ? 'selected' : ''; ?>>Closed</option>
                                            <option value="Draft" <?php echo $career['status'] === 'Draft' ? 'selected' : ''; ?>>Draft</option>
                                        </select>
                                    </div>

                                    <div class="col-12">
                                        <label for="job_description" class="form-label font-weight-semibold">Job Description <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="job_description" name="job_description" rows="4" required><?php echo htmlspecialchars($career['job_description']); ?></textarea>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="requirements" class="form-label font-weight-semibold">Requirements <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="requirements" name="requirements" rows="4" required><?php echo htmlspecialchars($career['requirements']); ?></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="benefits" class="form-label font-weight-semibold">Benefits</label>
                                        <textarea class="form-control" id="benefits" name="benefits" rows="4"><?php echo htmlspecialchars($career['benefits']); ?></textarea>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="application_deadline" class="form-label font-weight-semibold">Application Deadline</label>
                                        <input type="date" class="form-control" id="application_deadline" name="application_deadline" 
                                               value="<?php echo htmlspecialchars($career['application_deadline']); ?>">
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                                    <a href="index.php" class="btn btn-light px-4">Cancel</a>
                                    <button type="submit" class="btn btn-warning px-4 fw-bold text-dark">
                                        <i class="bi bi-save me-1.5"></i> Save Changes
                                    </button>
                                </div>
                            </form>
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