<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: admin_login.php');
    exit();
}

// Database connection
require_once '../../Configurations/config.php';

// Get admin details from session
$admin_name = $_SESSION['first_name'] ?? 'Admin';

// Add resume base path
$resume_base_path = '../../uploads/resumes/';
if (!is_dir($resume_base_path)) {
    // Log error or create directory if needed
    error_log("Resume directory not found: " . $resume_base_path);
}

// Fetch all careers with error handling
$query = "SELECT ja.*, c.job_title, c.company_name 
          FROM job_applications ja
          JOIN Careers c ON ja.job_id = c.job_id 
          ORDER BY ja.application_date DESC";
$result = mysqli_query($conn, $query);

// Handle delete request
if (isset($_POST['delete_application'])) {
    $application_id = mysqli_real_escape_string($conn, $_POST['application_id']);
    
    // First get the resume path
    $query = "SELECT resume_path FROM job_applications WHERE application_id = '$application_id'";
    $result = mysqli_query($conn, $query);
    $application = mysqli_fetch_assoc($result);
    
    // Delete the resume file if it exists
    if ($application && $application['resume_path']) {
        $resume_file = $resume_base_path . basename($application['resume_path']);
        if (file_exists($resume_file)) {
            unlink($resume_file);
        }
    }
    
    // Delete the database record
    $delete_query = "DELETE FROM job_applications WHERE application_id = '$application_id'";
    mysqli_query($conn, $delete_query);
    
    // Redirect to refresh the page
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Career Management - GD Edu Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row flex-nowrap">
            <!-- Sidebar -->
            <div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 sidebar">
                <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 min-vh-100">
                    <a href="#" class="d-flex align-items-center pb-3 mb-md-1 mt-md-3 me-md-auto text-white text-decoration-none">
                        <span class="fs-5 fw-bolder" style="display: flex;align-items:center;color:black;"><img height="35px" src="../images/edutechLogo.png" alt="">&nbsp; GD Edu Tech</span>
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
                        <li class="w-100 dropdown">
                            <a href="#" class="nav-link dropdown-toggle active" id="quizDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-lightbulb me-2"></i> Quick Links
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="quizDropdown">
                                <li><a class="dropdown-item" href="../index.php">Career portal</a></li>
                                <li><a class="dropdown-item" href="./Shop/shop.php">Shop</a></li>
                            </ul>
                        </li>
                        <li class="w-100">
                            <a href="../Schedule/" class="nav-link">
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
            <div class="col py-3">
                <div class="container-fluid">
                    <!-- Header -->
                    <div class="row mb-4">
                        <div class="col">
                            <h2>Job Candidates</h2>
                            <p class="text-muted">View and manage all job applications</p>
                        </div>
                        <div class="col-auto">
                            <a href="index.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Back to Jobs
                            </a>
                        </div>
                    </div>

                    <!-- Candidates Table -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">All Applications</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th class="py-3 px-4">ID</th>
                                            <th class="py-3 px-4">Job Title</th>
                                            <th class="py-3 px-4">Company</th>
                                            <th class="py-3 px-4">Candidate Name</th>
                                            <th class="py-3 px-4">Email</th>
                                            <th class="py-3 px-4">Phone</th>
                                            <th class="py-3 px-4">Application Date</th>
                                            <th class="py-3 px-4 text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                                            <tr>
                                                <td class="py-3 px-4"><?php echo htmlspecialchars($row['application_id']); ?></td>
                                                <td class="py-3 px-4"><?php echo htmlspecialchars($row['job_title']); ?></td>
                                                <td class="py-3 px-4"><?php echo htmlspecialchars($row['company_name']); ?></td>
                                                <td class="py-3 px-4"><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                                <td class="py-3 px-4"><?php echo htmlspecialchars($row['email']); ?></td>
                                                <td class="py-3 px-4"><?php echo htmlspecialchars($row['phone']); ?></td>
                                                <td class="py-3 px-4"><?php echo date('M d, Y', strtotime($row['application_date'])); ?></td>
                                                <td class="py-3 px-4 text-center">
                                                    <div class="btn-group" role="group">
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-primary me-1" 
                                                                onclick="viewApplication(<?php echo $row['application_id']; ?>)"
                                                                title="View Details">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                        <a href="<?php echo $resume_base_path . basename($row['resume_path']); ?>" 
                                                           class="btn btn-sm btn-outline-secondary me-1" 
                                                           target="_blank"
                                                           title="View Resume">
                                                            <i class="bi bi-file-earmark-text"></i>
                                                        </a>
                                                        <form method="POST" style="display: inline;" 
                                                              onsubmit="return confirm('Are you sure you want to delete this application?');">
                                                            <input type="hidden" name="application_id" 
                                                                   value="<?php echo $row['application_id']; ?>">
                                                            <button type="submit" name="delete_application" 
                                                                    class="btn btn-sm btn-outline-danger"
                                                                    title="Delete Application">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                        <?php if (mysqli_num_rows($result) == 0): ?>
                                            <tr>
                                                <td colspan="8" class="text-center py-4">No applications found</td>
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

    <!-- Add this modal HTML before the closing body tag -->
    <div class="modal fade" id="applicationModal" tabindex="-1" aria-labelledby="applicationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="applicationModalLabel">Application Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="applicationModalBody">
                    Loading...
                </div>
            </div>
        </div>
    </div>

    <!-- Add this JavaScript before the closing body tag -->
    <script>
    function viewApplication(applicationId) {
        fetch(`view_application.php?id=${applicationId}&modal=true`)
            .then(response => response.text())
            .then(data => {
                document.getElementById('applicationModalBody').innerHTML = data;
                new bootstrap.Modal(document.getElementById('applicationModal')).show();
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('applicationModalBody').innerHTML = 'Error loading application details.';
            });
    }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
