<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: admin_login.php');
    exit();
}

// Database connection
require_once '../../Configurations/config.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and get form data
    $job_title = mysqli_real_escape_string($conn, $_POST['jobTitle']);
    $company_name = mysqli_real_escape_string($conn, $_POST['company']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $salary_range = mysqli_real_escape_string($conn, $_POST['salary']);
    $job_description = mysqli_real_escape_string($conn, $_POST['description']);
    $requirements = mysqli_real_escape_string($conn, $_POST['requirements']);
    $benefits = mysqli_real_escape_string($conn, $_POST['benefits'] ?? '');
    $application_deadline = mysqli_real_escape_string($conn, $_POST['applicationDeadline']);
    $job_type = mysqli_real_escape_string($conn, $_POST['employmentType']);
    
    // Prepare insert query
    $query = "INSERT INTO Careers (
        job_title,
        company_name,
        location,
        salary_range,
        job_description,
        requirements,
        benefits,
        application_deadline,
        job_type,
        status
    ) VALUES (
        '$job_title',
        '$company_name',
        '$location',
        '$salary_range',
        '$job_description',
        '$requirements',
        '$benefits',
        '$application_deadline',
        '$job_type',
        'Active'
    )";

    // Execute query and handle response
    if (mysqli_query($conn, $query)) {
        $_SESSION['message'] = "Job posting created successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error creating job posting: " . mysqli_error($conn);
        $_SESSION['message_type'] = "danger";
    }

    // Redirect back to index page
    header("Location: index.php");
    exit();
}

// Get admin details from session
$admin_name = $_SESSION['first_name'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Job - GD Edu Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 sidebar">
                <!-- Copy the sidebar content from index.php -->
            </div>

            <!-- Main Content -->
            <div class="col py-3">
                <div class="container-fluid">
                    <!-- Header -->
                    <div class="row mb-4">
                        <div class="col">
                            <h2>Add New Job Posting</h2>
                            <p class="text-muted">Create a new career opportunity</p>
                        </div>
                    </div>

                    <!-- Alert Messages -->
                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                            <?php 
                            echo htmlspecialchars($_SESSION['message']);
                            unset($_SESSION['message']);
                            unset($_SESSION['message_type']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Job Form -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">Job Details</h5>
                        </div>
                        <div class="card-body">
                            <form action="insert.php" method="POST">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="jobTitle" class="form-label">Job Title</label>
                                        <input type="text" class="form-control" id="jobTitle" name="jobTitle" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="company" class="form-label">Company Name</label>
                                        <input type="text" class="form-control" id="company" name="company" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="location" class="form-label">Location</label>
                                        <input type="text" class="form-control" id="location" name="location" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="salary" class="form-label">Salary Range</label>
                                        <input type="text" class="form-control" id="salary" name="salary" placeholder="e.g., $50,000 - $70,000">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Job Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="requirements" class="form-label">Requirements</label>
                                        <textarea class="form-control" id="requirements" name="requirements" rows="3" required></textarea>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="benefits" class="form-label">Benefits</label>
                                        <textarea class="form-control" id="benefits" name="benefits" rows="3"></textarea>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="applicationDeadline" class="form-label">Application Deadline</label>
                                        <input type="date" class="form-control" id="applicationDeadline" name="applicationDeadline">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="jobType" class="form-label">Job Type</label>
                                        <select class="form-select" id="jobType" name="jobType" required>
                                            <option value="">Select Job Type</option>
                                            <option value="Full-time">Full-time</option>
                                            <option value="Part-time">Part-time</option>
                                            <option value="Contract">Contract</option>
                                            <option value="Internship">Internship</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="text-end mt-4">
                                    <a href="index.php" class="btn btn-secondary me-2">Cancel</a>
                                    <button type="submit" class="btn btn-primary">Create Job Posting</button>
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