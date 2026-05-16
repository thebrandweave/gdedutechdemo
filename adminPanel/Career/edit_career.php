<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: admin_login.php');
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
    $_SESSION['message'] = "Career not found";
    $_SESSION['message_type'] = "danger";
    header('Location: index.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $job_title = mysqli_real_escape_string($conn, $_POST['job_title']);
    $company_name = mysqli_real_escape_string($conn, $_POST['company_name']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $job_type = mysqli_real_escape_string($conn, $_POST['job_type']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $job_description = mysqli_real_escape_string($conn, $_POST['job_description']);
    $requirements = mysqli_real_escape_string($conn, $_POST['requirements']);
    $benefits = mysqli_real_escape_string($conn, $_POST['benefits']);
    $application_deadline = mysqli_real_escape_string($conn, $_POST['application_deadline']);

    // Update query
    $update_query = "UPDATE Careers SET 
        job_title = ?,
        company_name = ?,
        location = ?,
        job_type = ?,
        status = ?,
        job_description = ?,
        requirements = ?,
        benefits = ?,
        application_deadline = ?
        WHERE job_id = ?";

    $update_stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($update_stmt, "sssssssssi", 
        $job_title, $company_name, $location, $job_type, $status,
        $job_description, $requirements, $benefits, $application_deadline, $job_id
    );

    if (mysqli_stmt_execute($update_stmt)) {
        $_SESSION['message'] = "Career updated successfully";
        $_SESSION['message_type'] = "success";
        header('Location: index.php');
        exit();
    } else {
        $_SESSION['message'] = "Error updating career: " . mysqli_error($conn);
        $_SESSION['message_type'] = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Career - GD Edu Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
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
                <a href="../Categories/" class="nav-link ">
                    <i class="bi bi-grid me-2"></i> Categories
                </a>
            </li>
            <li class="w-100">
                <a href="../Courses/" class="nav-link">
                    <i class="bi bi-book me-2"></i> Courses
                </a>
            </li>
            <li class="w-100 dropdown">
                            <a href="#" class="nav-link dropdown-toggle" id="quizDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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
                <div class="container">
                    <div class="row mb-4">
                        <div class="col">
                            <h2>Edit Career Listing</h2>
                            <p class="text-muted">Update career opportunity details</p>
                        </div>
                        <div class="col-auto">
                            <a href="index.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Back to Careers
                            </a>
                        </div>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-body">
                            <form action="" method="POST">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="job_title" class="form-label">Job Title</label>
                                        <input type="text" class="form-control" id="job_title" name="job_title" 
                                               value="<?php echo htmlspecialchars($career['job_title']); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="company_name" class="form-label">Company Name</label>
                                        <input type="text" class="form-control" id="company_name" name="company_name" 
                                               value="<?php echo htmlspecialchars($career['company_name']); ?>" required>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="location" class="form-label">Location</label>
                                        <input type="text" class="form-control" id="location" name="location" 
                                               value="<?php echo htmlspecialchars($career['location']); ?>" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="job_type" class="form-label">Job Type</label>
                                        <select class="form-select" id="job_type" name="job_type" required>
                                            <option value="Full-time" <?php echo $career['job_type'] === 'Full-time' ? 'selected' : ''; ?>>Full-time</option>
                                            <option value="Part-time" <?php echo $career['job_type'] === 'Part-time' ? 'selected' : ''; ?>>Part-time</option>
                                            <option value="Contract" <?php echo $career['job_type'] === 'Contract' ? 'selected' : ''; ?>>Contract</option>
                                            <option value="Internship" <?php echo $career['job_type'] === 'Internship' ? 'selected' : ''; ?>>Internship</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status" required>
                                            <option value="Active" <?php echo $career['status'] === 'Active' ? 'selected' : ''; ?>>Active</option>
                                            <option value="Pending" <?php echo $career['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="Closed" <?php echo $career['status'] === 'Closed' ? 'selected' : ''; ?>>Closed</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="job_description" class="form-label">Job Description</label>
                                    <textarea class="form-control" id="job_description" name="job_description" rows="4" required><?php echo htmlspecialchars($career['job_description']); ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="requirements" class="form-label">Requirements</label>
                                    <textarea class="form-control" id="requirements" name="requirements" rows="4" required><?php echo htmlspecialchars($career['requirements']); ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="benefits" class="form-label">Benefits</label>
                                    <textarea class="form-control" id="benefits" name="benefits" rows="4" required><?php echo htmlspecialchars($career['benefits']); ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="application_deadline" class="form-label">Application Deadline</label>
                                    <input type="date" class="form-control" id="application_deadline" name="application_deadline" 
                                           value="<?php echo htmlspecialchars($career['application_deadline']); ?>" required>
                                </div>

                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save me-2"></i>Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 