<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once './Configurations/config.php';

// Get job_id from URL (supports both job_id and id parameters)
$job_id = isset($_GET['job_id']) ? intval($_GET['job_id']) : (isset($_GET['id']) ? intval($_GET['id']) : 0);

// Fetch job details
$job = null;
if ($job_id > 0) {
    $query = "SELECT * FROM Careers WHERE job_id = ? AND status = 'Active'";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $job_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $job = mysqli_fetch_assoc($result);
}

// If job not found, redirect to careers page
if (!$job) {
    header("Location: career.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    if (empty($_POST['first_name']) || empty($_POST['last_name']) || empty($_POST['email']) || 
        empty($_POST['phone']) || empty($_POST['cover_letter']) || !isset($_FILES['resume'])) {
        $_SESSION['error'] = "Please fill in all required fields.";
    } elseif (strlen(trim($_POST['cover_letter'])) < 100) {
        $_SESSION['error'] = "Cover letter must be at least 100 characters long.";
    } else {
        $first_name = mysqli_real_escape_string($conn, trim($_POST['first_name']));
        $last_name = mysqli_real_escape_string($conn, trim($_POST['last_name']));
        $email = mysqli_real_escape_string($conn, trim($_POST['email']));
        $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
        $cover_letter = mysqli_real_escape_string($conn, trim($_POST['cover_letter']));
        $portfolio_url = !empty($_POST['portfolio']) ? mysqli_real_escape_string($conn, trim($_POST['portfolio'])) : (!empty($_POST['portfolio_url']) ? mysqli_real_escape_string($conn, trim($_POST['portfolio_url'])) : NULL);
        
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Please enter a valid email address.";
        } else {
            // Handle file upload for resume
            $resume_path = '';
            $upload_error = false;
            
            if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = './uploads/resumes/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                // Validate file type
                $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                $file_type = $_FILES['resume']['type'];
                $file_size = $_FILES['resume']['size'];
                
                // Check file size (5MB max)
                if ($file_size > 5 * 1024 * 1024) {
                    $_SESSION['error'] = "Resume file size must be less than 5MB.";
                    $upload_error = true;
                } elseif (!in_array($file_type, $allowed_types)) {
                    $_SESSION['error'] = "Only PDF, DOC, and DOCX files are allowed for resume.";
                    $upload_error = true;
                } else {
                    $file_extension = pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION);
                    $new_filename = 'resume_' . $job_id . '_' . time() . '_' . uniqid() . '.' . $file_extension;
                    $resume_path = $upload_dir . $new_filename;
                    
                    if (!move_uploaded_file($_FILES['resume']['tmp_name'], $resume_path)) {
                        $_SESSION['error'] = "Failed to upload resume. Please try again.";
                        $upload_error = true;
                    }
                }
            } else {
                $_SESSION['error'] = "Please upload your resume.";
                $upload_error = true;
            }
            
            // Insert application into database if no upload errors
            if (!$upload_error && !empty($resume_path)) {
                $insert_query = "INSERT INTO job_applications (job_id, first_name, last_name, email, phone, resume_path, cover_letter, portfolio_url, application_date, status) 
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'Pending')";
                $stmt = mysqli_prepare($conn, $insert_query);
                mysqli_stmt_bind_param($stmt, "isssssss", $job_id, $first_name, $last_name, $email, $phone, $resume_path, $cover_letter, $portfolio_url);
                
                if (mysqli_stmt_execute($stmt)) {
                    // Set success flag in session
                    $_SESSION['show_toast'] = true;
                    $_SESSION['success'] = "Your application has been submitted successfully! We'll review your application and get back to you soon.";
                    header("Location: career.php");
                    exit();
                } else {
                    $_SESSION['error'] = "Failed to submit application. Please try again. Error: " . mysqli_error($conn);
                    // Delete uploaded file if database insert fails
                    if (file_exists($resume_path)) {
                        unlink($resume_path);
                    }
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// Format deadline
$deadline = new DateTime($job['application_deadline']);
$formatted_deadline = $deadline->format('M d, Y');
$today = new DateTime();
$interval = $today->diff($deadline);
$days_remaining = $interval->days;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for <?php echo htmlspecialchars($job['job_title']); ?> - GD Edu Tech</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AOS Animation Library -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="./css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Custom JavaScript -->
    <script src="./js/main.js" defer></script>
        
    <link rel="icon" type="image/png" href="./Images/Logos/GD_Only_logo.png">
    <link rel="icon" type="image/png" sizes="32x32" href="./Images/Logos/GD_Only_logo.png">
    <link rel="icon" type="image/png" sizes="16x16" href="./Images/Logos/GD_Only_logo.png">
    <link rel="shortcut icon" href="./Images/Logos/GD_Only_logo.png">

    <style>
        body {
            background: #f8fafc;
            color: #0f172a;
            font-family: 'Poppins', sans-serif;
        }

        /* Glassmorphic Executive Cards */
        .apply-job-card {
            background: #ffffff;
            border-radius: 24px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 15px 35px -10px rgba(15, 23, 42, 0.08);
            transition: all 0.3s ease;
        }

        .apply-job-card:hover {
            box-shadow: 0 20px 45px -10px rgba(13, 114, 152, 0.12);
        }

        .info-pill-item {
            background: rgba(13, 114, 152, 0.06);
            border: 1px solid rgba(13, 114, 152, 0.15);
            border-radius: 16px;
            padding: 14px 18px;
        }

        .info-icon-box {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: #0d7298;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }

        /* Form Custom Input Fields */
        .form-section-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #0f172a;
            border-left: 4px solid #0d7298;
            padding-left: 12px;
        }

        .custom-form-input {
            border: 1.5px solid #cbd5e1;
            border-radius: 14px;
            padding: 12px 18px;
            font-size: 0.95rem;
            color: #0f172a;
            background: #ffffff;
            transition: all 0.3s ease;
        }

        .custom-form-input:focus {
            border-color: #0d7298;
            box-shadow: 0 0 0 4px rgba(13, 114, 152, 0.12);
            outline: none;
        }

        /* Custom File Upload Box */
        .upload-drop-zone {
            border: 2px dashed #0d7298;
            border-radius: 18px;
            background: rgba(13, 114, 152, 0.03);
            padding: 28px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .upload-drop-zone:hover {
            background: rgba(13, 114, 152, 0.08);
            border-color: #065d7d;
        }

        .upload-drop-zone input[type="file"] {
            position: absolute;
            inset: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .upload-icon-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: rgba(13, 114, 152, 0.12);
            color: #0d7298;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            margin: 0 auto 12px auto;
        }

        /* Gradient Submit Button */
        .btn-apply-submit {
            background: linear-gradient(135deg, #0d7298 0%, #065d7d 100%);
            color: #ffffff;
            font-size: 1.05rem;
            font-weight: 700;
            border: none;
            border-radius: 50px;
            padding: 14px 40px;
            box-shadow: 0 8px 24px rgba(13, 114, 152, 0.28);
            transition: all 0.3s ease;
        }

        .btn-apply-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(13, 114, 152, 0.38);
            color: #ffffff;
        }

        .btn-cancel-back {
            border: 2px solid #94a3b8;
            color: #475569;
            font-size: 0.95rem;
            font-weight: 600;
            border-radius: 50px;
            padding: 12px 30px;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-cancel-back:hover {
            background: #94a3b8;
            color: #ffffff;
        }

        .requirements-list-styled {
            list-style: none;
            padding-left: 0;
        }

        .requirements-list-styled li {
            position: relative;
            padding-left: 28px;
            margin-bottom: 10px;
            font-size: 0.93rem;
            color: #334155;
            line-height: 1.6;
        }

        .requirements-list-styled li::before {
            content: "\f26a";
            font-family: "bootstrap-icons";
            position: absolute;
            left: 0;
            top: 2px;
            color: #10b981;
            font-size: 1.05rem;
        }
    </style>
</head>

<body>
    <?php include 'navbar.php'; ?>

    <!-- Redesigned Executive Hero Banner -->
    <section class="about-page-header position-relative overflow-hidden w-100 my-0">
        <div class="about-header-glow-1"></div>
        <div class="about-header-glow-2"></div>
        <div class="about-header-pattern"></div>

        <div class="container position-relative z-2 py-4">
            <div class="row align-items-center text-start g-4">
                <div class="col-lg-7" data-aos="fade-right">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb about-breadcrumb px-3 py-1.5 rounded-pill mb-3 d-inline-flex">
                            <li class="breadcrumb-item"><a href="index.php" class="text-black text-decoration-none"><i class="bi bi-house-door-fill me-1"></i> Home</a></li>
                            <li class="breadcrumb-item"><a href="career.php" class="text-black text-decoration-none">Careers</a></li>
                            <li class="breadcrumb-item active text-black" aria-current="page">Application</li>
                        </ol>
                    </nav>

                    <span class="badge bg-warning bg-opacity-20 text-dark border border-warning-subtle px-3 py-1.5 rounded-pill mb-2 fw-semibold">
                        <i class="bi bi-briefcase-fill me-1 text-warning"></i> <?php echo htmlspecialchars($job['job_type']); ?>
                    </span>

                    <h1 class="display-5 fw-bold text-black mb-3">
                        Apply For <span class="cta-gold-text"><?php echo htmlspecialchars($job['job_title']); ?></span>
                    </h1>

                    <p class="lead text-black-50 mb-0" style="max-width: 650px;">
                        <i class="bi bi-geo-alt-fill text-danger me-1"></i> <?php echo htmlspecialchars($job['location']); ?>
                        <span class="mx-3">|</span>
                        <i class="bi bi-cash-stack text-success me-1"></i> <?php echo htmlspecialchars($job['salary_range']); ?> / annum
                    </p>
                </div>

                <div class="col-lg-5 text-center text-lg-end" data-aos="fade-left" data-aos-delay="200">
                    <div class="about-header-card text-start">
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <div class="rounded-circle bg-opacity-20 p-3 text-warning">
                                <i class="bi bi-rocket-takeoff-fill fs-3"></i>
                            </div>
                            <div>
                                <h6 class="text-black fw-bold mb-0">Fast-Track Hiring</h6>
                                <span class="text-black-50 small">Direct Application Portal</span>
                            </div>
                        </div>
                        <p class="text-black-50 small mb-0" style="line-height: 1.5;">
                            Submit your application and resume to connect directly with GD Edu Tech recruitment team.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-header-shape position-absolute bottom-0 start-0 w-100">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 120" preserveAspectRatio="none" style="height: 40px; display: block; width: 100%;">
                <path fill="#f8fafc" fill-opacity="1" d="M0,32L48,42.7C96,53,192,75,288,80C384,85,480,75,576,58.7C672,43,768,21,864,21.3C960,21,1056,43,1152,53.3C1248,64,1344,64,1392,64L1440,64L1440,120L1392,120C1344,120,1056,120,960,120C1056,120,960,120,864,120C768,120,672,120,576,120C480,120,384,120,288,120C192,120,96,120,48,120L0,120Z"></path>
            </svg>
        </div>
    </section>

    <!-- Main Content Section -->
    <section class="py-5">
        <div class="container py-2">

            <!-- Floating Alert Messages -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4 p-3" role="alert" data-aos="fade-down">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle-fill fs-4 me-3 text-danger"></i>
                        <div>
                            <strong class="d-block">Submission Error</strong>
                            <span><?php echo htmlspecialchars($_SESSION['error']); ?></span>
                        </div>
                    </div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="row g-4">
                
                <!-- Left Sidebar: Job Summary Card -->
                <div class="col-lg-5">
                    <div class="sticky-top" style="top: 100px; z-index: 10;">
                        <div class="apply-job-card p-4 mb-4" data-aos="fade-right">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-3 py-1.5 rounded-pill fw-semibold">
                                    <i class="bi bi-briefcase-fill me-1"></i> <?php echo htmlspecialchars($job['job_type']); ?>
                                </span>
                             
                            </div>

                            <h3 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($job['job_title']); ?></h3>
                            <?php if (!empty($job['company_name'])): ?>
                                <p class="text-muted small mb-3"><i class="bi bi-building me-1"></i> <?php echo htmlspecialchars($job['company_name']); ?></p>
                            <?php endif; ?>

                            <div class="d-flex flex-column gap-2 mb-4">
                                <div class="info-pill-item d-flex align-items-center gap-3">
                                    <div class="info-icon-box">
                                        <i class="bi bi-geo-alt-fill"></i>
                                    </div>
                                    <div>
                                        <span class="text-muted small d-block">Work Location</span>
                                        <strong class="text-dark"><?php echo htmlspecialchars($job['location']); ?></strong>
                                    </div>
                                </div>

                                <div class="info-pill-item d-flex align-items-center gap-3">
                                    <div class="info-icon-box" style="background: #10b981;">
                                        <i class="bi bi-cash-stack"></i>
                                    </div>
                                    <div>
                                        <span class="text-muted small d-block">Compensation</span>
                                        <strong class="text-dark"><?php echo htmlspecialchars($job['salary_range']); ?> / annum</strong>
                                    </div>
                                </div>

                                <div class="info-pill-item d-flex align-items-center gap-3">
                                    <div class="info-icon-box" style="background: #f59e0b;">
                                        <i class="bi bi-calendar-event-fill"></i>
                                    </div>
                                    <div>
                                        <span class="text-muted small d-block">Deadline</span>
                                        <strong class="text-dark"><?php echo $formatted_deadline; ?></strong>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-3 text-secondary opacity-25">

                            <!-- Job Description Accordion -->
                            <div class="mb-3">
                                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-file-text-fill text-primary me-2"></i>Job Description</h6>
                                <p class="text-secondary small mb-0" style="line-height: 1.7;">
                                    <?php echo nl2br(htmlspecialchars($job['job_description'])); ?>
                                </p>
                            </div>

                            <?php if (!empty(trim($job['requirements']))): ?>
                                <div class="mb-3">
                                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-list-check text-primary me-2"></i>Requirements</h6>
                                    <ul class="requirements-list-styled mb-0">
                                        <?php
                                        $requirements = explode("\n", $job['requirements']);
                                        foreach ($requirements as $req) {
                                            if (!empty(trim($req))) {
                                                echo '<li>' . htmlspecialchars(trim($req)) . '</li>';
                                            }
                                        }
                                        ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty(trim($job['benefits']))): ?>
                                <div>
                                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-gift-fill text-warning me-2"></i>Benefits</h6>
                                    <ul class="requirements-list-styled mb-0">
                                        <?php
                                        $benefits = explode("\n", $job['benefits']);
                                        foreach ($benefits as $ben) {
                                            if (!empty(trim($ben))) {
                                                echo '<li>' . htmlspecialchars(trim($ben)) . '</li>';
                                            }
                                        }
                                        ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- HR Support Contact Card -->
                        <div class="apply-job-card p-4" data-aos="fade-right" data-aos-delay="100">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary">
                                    <i class="bi bi-headset fs-3"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">Need Assistance?</h6>
                                    <p class="text-muted small mb-0">Contact our HR desk at <a href="mailto:gdedutech24@gmail.com" class="text-primary text-decoration-none fw-semibold">gdedutech24@gmail.com</a></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Side: Application Form Card -->
                <div class="col-lg-7">
                    <div class="apply-job-card p-4 p-lg-5" data-aos="fade-left">
                        <div class="border-bottom pb-3 mb-4">
                            <h3 class="fw-bold text-dark mb-1">Submit Your Application</h3>
                            <p class="text-muted small mb-0">Complete all required fields below to submit your job application.</p>
                        </div>

                        <form method="POST" enctype="multipart/form-data" id="applicationForm" class="needs-validation" novalidate>
                            
                            <!-- 1. Personal Information Section -->
                            <div class="mb-4">
                                <h5 class="form-section-title mb-3">1. Personal Details</h5>
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="first_name" class="form-label fw-semibold text-secondary small mb-1">First Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control custom-form-input" id="first_name" name="first_name" placeholder="John" required>
                                        <div class="invalid-feedback">Please enter your first name.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="last_name" class="form-label fw-semibold text-secondary small mb-1">Last Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control custom-form-input" id="last_name" name="last_name" placeholder="Doe" required>
                                        <div class="invalid-feedback">Please enter your last name.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email" class="form-label fw-semibold text-secondary small mb-1">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control custom-form-input" id="email" name="email" placeholder="john.doe@example.com" required>
                                        <div class="invalid-feedback">Please enter a valid email address.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label fw-semibold text-secondary small mb-1">Phone Number <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control custom-form-input" id="phone" name="phone" placeholder="10-digit mobile number" pattern="[0-9]{10}" title="Please enter a 10-digit phone number" required>
                                        <div class="invalid-feedback">Please enter a valid 10-digit phone number.</div>
                                    </div>
                                    <div class="col-12">
                                        <label for="portfolio" class="form-label fw-semibold text-secondary small mb-1">Portfolio / LinkedIn / GitHub URL (Optional)</label>
                                        <input type="url" class="form-control custom-form-input" id="portfolio" name="portfolio" placeholder="https://linkedin.com/in/yourprofile">
                                    </div>
                                </div>
                            </div>

                            <!-- 2. Resume Upload Section -->
                            <div class="mb-4">
                                <h5 class="form-section-title mb-3">2. Resume / CV Upload</h5>
                                
                                <div class="upload-drop-zone" id="dropZone">
                                    <div class="upload-icon-circle">
                                        <i class="bi bi-cloud-arrow-up-fill"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1" id="fileLabel">Click or Drag & Drop Resume File</h6>
                                    <p class="text-muted small mb-0">Supported Formats: <strong>PDF, DOC, DOCX</strong> (Max 5MB)</p>
                                    <input type="file" id="resume" name="resume" accept=".pdf,.doc,.docx" required>
                                </div>
                                <div id="resumeFeedback" class="invalid-feedback d-none text-danger mt-1 small">Please select a valid PDF, DOC, or DOCX resume file under 5MB.</div>
                            </div>

                            <!-- 3. Cover Letter Section -->
                            <div class="mb-4">
                                <h5 class="form-section-title mb-3">3. Cover Letter</h5>
                                <label for="cover_letter" class="form-label fw-semibold text-secondary small mb-1">Why are you interested in this position? <span class="text-danger">*</span></label>
                                <textarea class="form-control custom-form-input" id="cover_letter" name="cover_letter" rows="5" placeholder="Share your relevant experience, key skills, and enthusiasm for this role..." required minlength="100"></textarea>
                                
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <small class="text-muted small">Minimum 100 characters required</small>
                                    <small id="charCount" class="fw-bold text-muted">0 / 100</small>
                                </div>
                                <div class="progress mt-1" style="height: 6px; border-radius: 10px; background: #e2e8f0;">
                                    <div id="charProgress" class="progress-bar bg-primary" role="progressbar" style="width: 0%; border-radius: 10px;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div id="coverLetterFeedback" class="text-danger small mt-1" style="display: none;">Cover letter must be at least 100 characters long.</div>
                            </div>

                            <!-- 4. Declaration Checkbox -->
                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms" required style="width: 18px; height: 18px;">
                                    <label class="form-check-label text-secondary small ms-2" for="terms">
                                        I certify that the information provided is true and complete to the best of my knowledge. <span class="text-danger">*</span>
                                    </label>
                                    <div class="invalid-feedback">You must agree before submitting.</div>
                                </div>
                            </div>

                            <!-- Submit Action Row -->
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pt-3 border-top">
                                <a href="career.php" class="btn-cancel-back">
                                    <i class="bi bi-arrow-left me-1"></i> Back to Careers
                                </a>
                                <button type="submit" class="btn-apply-submit">
                                    <span>SUBMIT APPLICATION</span>
                                    <i class="bi bi-send-fill ms-2"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 900,
            easing: 'ease-in-out',
            once: true
        });

        // Interactive File Upload & Drag-Drop Preview
        document.addEventListener('DOMContentLoaded', function() {
            const resumeInput = document.getElementById('resume');
            const fileLabel = document.getElementById('fileLabel');
            const resumeFeedback = document.getElementById('resumeFeedback');

            if (resumeInput) {
                resumeInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const fileSizeMB = file.size / 1024 / 1024;
                        const allowedExts = ['pdf', 'doc', 'docx'];
                        const ext = file.name.split('.').pop().toLowerCase();

                        if (fileSizeMB > 5) {
                            alert('Resume file size must be less than 5MB.');
                            resumeInput.value = '';
                            fileLabel.textContent = 'Click or Drag & Drop Resume File';
                            return;
                        }

                        if (!allowedExts.includes(ext)) {
                            alert('Only PDF, DOC, and DOCX files are permitted.');
                            resumeInput.value = '';
                            fileLabel.textContent = 'Click or Drag & Drop Resume File';
                            return;
                        }

                        fileLabel.innerHTML = `<span class="text-success"><i class="bi bi-file-earmark-check-fill me-1"></i> Selected: ${file.name} (${fileSizeMB.toFixed(2)} MB)</span>`;
                    }
                });
            }

            // Cover Letter Live Character Count & Progress
            const coverLetter = document.getElementById('cover_letter');
            const charCount = document.getElementById('charCount');
            const charProgress = document.getElementById('charProgress');
            const coverLetterFeedback = document.getElementById('coverLetterFeedback');
            const form = document.getElementById('applicationForm');

            function updateProgress() {
                if (!coverLetter) return;
                const len = coverLetter.value.trim().length;
                const min = 100;
                const pct = Math.min(100, Math.floor((len / min) * 100));

                charCount.textContent = `${len} / ${min}`;
                charProgress.style.width = `${pct}%`;

                if (len < min) {
                    charProgress.classList.remove('bg-success');
                    charProgress.classList.add('bg-primary');
                    coverLetterFeedback.style.display = (len > 0) ? 'block' : 'none';
                } else {
                    charProgress.classList.remove('bg-primary');
                    charProgress.classList.add('bg-success');
                    coverLetterFeedback.style.display = 'none';
                }
            }

            if (coverLetter) {
                coverLetter.addEventListener('input', updateProgress);
                updateProgress();
            }

            // Form Submit Validation
            if (form) {
                form.addEventListener('submit', function(event) {
                    const len = coverLetter ? coverLetter.value.trim().length : 0;
                    if (len < 100) {
                        event.preventDefault();
                        event.stopPropagation();
                        if (coverLetterFeedback) coverLetterFeedback.style.display = 'block';
                        if (coverLetter) coverLetter.focus();
                    }
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            }
        });
    </script>
</body>
</html>