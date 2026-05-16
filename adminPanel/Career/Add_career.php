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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Career - GD Edu Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
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
                <div class="container-fluid">
                    <!-- Header -->
                    <div class="row mb-4">
                        <div class="col">
                            <h2>Add New Career Opportunity</h2>
                            <p class="text-muted">Create a new job listing</p>
                        </div>
                        <div class="col-auto">
                            <a href="index.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Back to List
                            </a>
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

                    <!-- Career Form -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">Career Details</h5>
                        </div>
                        <div class="card-body">
                            <form action="insert.php" method="POST" class="needs-validation" novalidate>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="jobTitle" class="form-label">Job Title <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="jobTitle" name="jobTitle" required>
                                        <div class="invalid-feedback">Please provide a job title.</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="company" class="form-label">Company Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="company" name="company" required>
                                        <div class="invalid-feedback">Please provide a company name.</div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="location" class="form-label">Location <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="location" name="location" required>
                                        <div class="invalid-feedback">Please provide a location.</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="salary" class="form-label">Salary Range</label>
                                        <input type="text" class="form-control" id="salary" name="salary" placeholder="e.g., $50,000 - $70,000">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Job Description <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                                    <div class="invalid-feedback">Please provide a job description.</div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="requirements" class="form-label">Requirements <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="requirements" name="requirements" rows="3" required></textarea>
                                        <div class="invalid-feedback">Please provide job requirements.</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="benefits" class="form-label">Benefits</label>
                                        <textarea class="form-control" id="benefits" name="benefits" rows="3" placeholder="List the benefits and perks"></textarea>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="employmentType" class="form-label">Employment Type <span class="text-danger">*</span></label>
                                        <select class="form-select" id="employmentType" name="employmentType" required>
                                            <option value="">Select employment type</option>
                                            <option value="Full-time">Full-time</option>
                                            <option value="Part-time">Part-time</option>
                                            <option value="Contract">Contract</option>
                                            <option value="Internship">Internship</option>
                                        </select>
                                        <div class="invalid-feedback">Please select an employment type.</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="applicationDeadline" class="form-label">Application Deadline</label>
                                        <input type="date" class="form-control" id="applicationDeadline" name="applicationDeadline">
                                    </div>
                                </div>

                                <div class="text-end mt-4">
                                    <button type="reset" class="btn btn-secondary me-2">Clear Form</button>
                                    <button type="submit" class="btn btn-primary">Add Career Opportunity</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>
</body>
</html>