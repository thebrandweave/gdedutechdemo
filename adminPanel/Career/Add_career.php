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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Career Opening - GD Edu Tech Admin</title>
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
                        <h4 class="fw-bold text-dark mb-0">Add New Job Opening</h4>
                        <span class="text-muted small">Create a new career listing for job applicants</span>
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

                    <!-- Form Card -->
                    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                        <div class="card-header bg-white py-3 px-4 border-bottom">
                            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-plus-circle-fill text-primary me-2"></i>Job Opportunity Details</h6>
                        </div>
                        <div class="card-body p-4">
                            <form action="insert.php" method="POST" class="needs-validation" novalidate>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="jobTitle" class="form-label font-weight-semibold">Job Title <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="jobTitle" name="jobTitle" placeholder="e.g. Full Stack Developer" required>
                                        <div class="invalid-feedback">Please provide a job title.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="company" class="form-label font-weight-semibold">Company Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="company" name="company" value="GD Edu Tech" required>
                                        <div class="invalid-feedback">Please provide a company name.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="location" class="form-label font-weight-semibold">Location <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="location" name="location" placeholder="e.g. Mangalore / Remote" required>
                                        <div class="invalid-feedback">Please provide a location.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="salary" class="form-label font-weight-semibold">Salary / Stipend Range</label>
                                        <input type="text" class="form-control" id="salary" name="salary" placeholder="e.g., ₹25,000 - ₹40,000 / month">
                                    </div>

                                    <div class="col-12">
                                        <label for="description" class="form-label font-weight-semibold">Job Description <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="description" name="description" rows="4" placeholder="Detailed role description and responsibilities..." required></textarea>
                                        <div class="invalid-feedback">Please provide a job description.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="requirements" class="form-label font-weight-semibold">Requirements & Qualifications <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="requirements" name="requirements" rows="4" placeholder="Key skills, degree, experience required..." required></textarea>
                                        <div class="invalid-feedback">Please provide job requirements.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="benefits" class="form-label font-weight-semibold">Perks & Benefits</label>
                                        <textarea class="form-control" id="benefits" name="benefits" rows="4" placeholder="Flexible hours, mentorship, certificate, bonus..."></textarea>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="employmentType" class="form-label font-weight-semibold">Job Type <span class="text-danger">*</span></label>
                                        <select class="form-select" id="employmentType" name="employmentType" required>
                                            <option value="" disabled selected>Select employment type...</option>
                                            <option value="Full-time">Full-time</option>
                                            <option value="Part-time">Part-time</option>
                                            <option value="Contract">Contract</option>
                                            <option value="Internship">Internship</option>
                                        </select>
                                        <div class="invalid-feedback">Please select an employment type.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="applicationDeadline" class="form-label font-weight-semibold">Application Deadline</label>
                                        <input type="date" class="form-control" id="applicationDeadline" name="applicationDeadline">
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                                    <button type="reset" class="btn btn-light px-4">Clear Form</button>
                                    <button type="submit" class="btn btn-primary px-4 fw-bold">Publish Job Opening</button>
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
    <script>
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
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