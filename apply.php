<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once './Configurations/config.php';

// Get job_id from URL
$job_id = isset($_GET['job_id']) ? intval($_GET['job_id']) : 0;

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
        $portfolio_url = !empty($_POST['portfolio_url']) ? mysqli_real_escape_string($conn, trim($_POST['portfolio_url'])) : NULL;
        
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
                    // Set success flag in session and add JavaScript to show toast
                    $_SESSION['show_toast'] = true;
                    $_SESSION['success'] = "Your application has been submitted successfully! We'll get back to you soon.";
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
    <title>Apply - <?php echo htmlspecialchars($job['job_title']); ?> - GD Edu Tech</title>
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
    <!-- Custom JavaScript -->
    <script src="./js/main.js" defer></script>
        
    <link rel="icon" type="image/png" href="./Images/Logos/GD_Only_logo.png">
    <link rel="icon" type="image/png" sizes="32x32" href="./Images/Logos/GD_Only_logo.png">
    <link rel="icon" type="image/png" sizes="16x16" href="./Images/Logos/GD_Only_logo.png">
    <link rel="shortcut icon" href="./Images/Logos/GD_Only_logo.png">
    <link rel="apple-touch-icon" href="./Images/Logos/GD_Only_logo.png">
    <meta name="msapplication-TileImage" content="./Images/Logos/GD_Only_logo.png">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <!-- Alert Messages -->
    <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert" style="margin-bottom: 0; border-radius: 0; background-color: #28a745; color: white; border: none; padding: 15px 20px; font-weight: 500;">
        <div class="container d-flex align-items-center">
            <i class="bi bi-check-circle-fill me-2" style="font-size: 1.2rem;"></i>
            <div><?php echo $_SESSION['success']; ?></div>
            <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="margin-bottom: 0; border-radius: 0; background-color: #dc3545; color: white; border: none; padding: 15px 20px; font-weight: 500;">
        <div class="container d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill me-2" style="font-size: 1.2rem;"></i>
            <div><?php echo $_SESSION['error']; ?></div>
            <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Page Header -->
    <section class="page-header position-relative overflow-hidden">
        <div class="container position-relative py-5">
            <div class="row align-items-center">
                <div class="col-12" data-aos="fade-right">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-3">
                            <li class="breadcrumb-item"><a href="index.php" class="text-white-50">Home</a></li>
                            <li class="breadcrumb-item"><a href="careers.php" class="text-white-50">Careers</a></li>
                            <li class="breadcrumb-item active text-white" aria-current="page">Apply</li>
                        </ol>
                    </nav>
                    <h1 class="text-white display-6 fw-bold mb-2"><?php echo htmlspecialchars($job['job_title']); ?></h1>
                    <p class="text-white-50 mb-0">
                        <i class="bi bi-geo-alt me-2"></i><?php echo htmlspecialchars($job['location']); ?>
                        <span class="mx-3">|</span>
                        <i class="bi bi-briefcase me-2"></i><?php echo htmlspecialchars($job['job_type']); ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="page-header-shape">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
                <path fill="#ffffff" fill-opacity="1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,112C672,96,768,96,864,112C960,128,1056,160,1152,160C1248,160,1344,128,1392,112L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
            </svg>
        </div>
    </section>

    <!-- Application Section -->
    <section class="py-5">
        <div class="container">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert" data-aos="fade-down">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row g-5">
                <!-- Job Details Section -->
                <div class="col-lg-5">
                    <div class="sticky-top" style="top: 100px;">
                        <div class="premium-card p-4 mb-4" data-aos="fade-right">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <span class="badge bg-primary rounded-pill px-3 py-2">
                                    <?php echo htmlspecialchars($job['job_type']); ?>
                                </span>
                                <span class="text-muted">
                                    <i class="bi bi-calendar-event me-1"></i>
                                    <?php echo $days_remaining; ?> days left
                                </span>
                            </div>

                            <h3 class="fw-bold mb-3"><?php echo htmlspecialchars($job['job_title']); ?></h3>

                            <div class="mb-4">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="contact-icon me-3">
                                        <i class="bi bi-geo-alt"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Location</small>
                                        <strong><?php echo htmlspecialchars($job['location']); ?></strong>
                                    </div>
                                </div>
                            

                                <div class="d-flex align-items-center mb-3">
                                    <div class="contact-icon me-3">
                                        <i class="bi bi-cash-stack"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Salary Range</small>
                                        <strong><?php echo htmlspecialchars($job['salary_range']); ?></strong>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center">
                                    <div class="contact-icon me-3">
                                        <i class="bi bi-calendar-check"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Application Deadline</small>
                                        <strong><?php echo $formatted_deadline; ?></strong>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="mb-4">
                                <h5 class="fw-bold mb-3">Job Description</h5>
                                <p class="text-muted" style="line-height: 1.8;">
                                    <?php echo nl2br(htmlspecialchars($job['job_description'])); ?>
                                </p>
                            </div>

                            <?php if (!empty($job['requirements'])): ?>
                            <div class="mb-4">
                                <h5 class="fw-bold mb-3">Requirements</h5>
                                <p class="text-muted" style="line-height: 1.8;">
                                    <?php echo nl2br(htmlspecialchars($job['requirements'])); ?>
                                </p>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($job['responsibilities'])): ?>
                            <div>
                                <h5 class="fw-bold mb-3">Responsibilities</h5>
                                <p class="text-muted" style="line-height: 1.8;">
                                    <?php echo nl2br(htmlspecialchars($job['responsibilities'])); ?>
                                </p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="premium-card p-4" data-aos="fade-right" data-aos-delay="100">
                            <h5 class="fw-bold mb-3">Need Help?</h5>
                            <p class="text-muted mb-3">Have questions about this position? Feel free to reach out to our HR team.</p>
                            <a href="contact.php" class="btn btn-outline-primary w-100">
                                <i class="bi bi-envelope me-2"></i>Contact HR
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Application Form Section -->
                <div class="col-lg-7">
                    <div class="premium-card p-4 p-lg-5" data-aos="fade-left">
                        <h3 class="fw-bold mb-2">Submit Your Application</h3>
                        <p class="text-muted mb-4">Fill in your details below and we'll get back to you as soon as possible.</p>

                        <form method="POST" enctype="multipart/form-data" id="applicationForm">
                            <!-- Personal Information -->
                            <div class="mb-4">
                                <h5 class="fw-bold mb-3">Personal Information</h5>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-lg" id="first_name" name="first_name" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-lg" id="last_name" name="last_name" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control form-control-lg" id="email" name="email" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control form-control-lg" id="phone" name="phone" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="portfolio" class="form-label">Portfolio/Website (Optional)</label>
                                    <input type="url" class="form-control form-control-lg" id="portfolio" name="portfolio" placeholder="https://yourportfolio.com">
                                </div>
                            </div>

                            <!-- Resume Upload -->
                            <div class="mb-4">
                                <h5 class="fw-bold mb-3">Resume/CV</h5>
                                <label for="resume" class="form-label">Upload Resume <span class="text-danger">*</span></label>
                                <input type="file" class="form-control form-control-lg" id="resume" name="resume" accept=".pdf,.doc,.docx" required>
                                <small class="text-muted">Accepted formats: PDF, DOC, DOCX (Max 5MB)</small>
                            </div>

                            <!-- Cover Letter -->
                            <div class="mb-4">
                                <h5 class="fw-bold mb-3">Cover Letter</h5>
                                <label for="cover_letter" class="form-label">Why are you interested in this position? <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="cover_letter" name="cover_letter" rows="6" required placeholder="Tell us about yourself and why you're a great fit for this role..." minlength="100"></textarea>
                                <div class="d-flex justify-content-between mt-2">
                                    <small class="text-muted">Minimum 100 characters</small>
                                    <small id="charCount" class="text-muted">0/100 characters</small>
                                </div>
                                <div class="progress mt-2" style="height: 5px;">
                                    <div id="charProgress" class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div id="coverLetterFeedback" class="invalid-feedback">Cover letter must be at least 100 characters long.</div>
                            </div>

                            <!-- Terms and Conditions -->
                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms" required>
                                    <label class="form-check-label" for="terms">
                                        I agree to the <a href="#" class="text-primary">Terms and Conditions</a> and <a href="#" class="text-primary">Privacy Policy</a> <span class="text-danger">*</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill">
                                    <i class="bi bi-send me-2"></i>Submit Application
                                </button>
                                <a href="careers.php" class="btn btn-outline-secondary btn-lg rounded-pill">
                                    <i class="bi bi-arrow-left me-2"></i>Back to Careers
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Include footer -->
    <?php include 'footer.php'; ?>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <!-- Back to Top Button -->
    <script src="js/back-to-top.js"></script>
    
    <!-- Toast Notification -->
    <div id="successToast" class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999; display: none;">
        <div class="toast show bg-success text-white" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-success text-white">
                <i class="bi bi-check-circle-fill me-2"></i>
                <strong class="me-auto">Success</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close" onclick="hideToast()"></button>
            </div>
            <div class="toast-body">
                <p class="mb-0">Your application has been submitted successfully! We'll get back to you soon.</p>
            </div>
        </div>
    </div>
    
    <!-- Toast Notification Script -->
    <script>
        // Function to show success toast
        function showSuccessToast() {
            const toast = document.getElementById('successToast');
            toast.style.display = 'block';
            
            // Automatically hide after 5 seconds
            setTimeout(function() {
                hideToast();
            }, 5000);
        }
        
        // Function to hide toast
        function hideToast() {
            const toast = document.getElementById('successToast');
            toast.style.display = 'none';
        }
        
        // Check if form was just submitted successfully
        <?php if(isset($_POST['submit']) && !isset($_SESSION['error'])): ?>
        document.addEventListener('DOMContentLoaded', function() {
            showSuccessToast();
        });
        <?php endif; ?>
    </script>
    
    <!-- Cover Letter Validation Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const coverLetter = document.getElementById('cover_letter');
            const charCount = document.getElementById('charCount');
            const charProgress = document.getElementById('charProgress');
            const coverLetterFeedback = document.getElementById('coverLetterFeedback');
            const form = document.getElementById('applicationForm');
            
            // Update character count and progress bar
            function updateCharCount() {
                const length = coverLetter.value.length;
                const minLength = 100;
                const percentage = Math.min(100, Math.floor((length / minLength) * 100));
                
                charCount.textContent = length + '/' + minLength + ' characters';
                charProgress.style.width = percentage + '%';
                charProgress.setAttribute('aria-valuenow', percentage);
                
                // Change progress bar color based on length
                if (length < minLength) {
                    charProgress.classList.remove('bg-success');
                    charProgress.classList.add('bg-primary');
                    coverLetter.classList.add('is-invalid');
                    coverLetterFeedback.style.display = 'block';
                } else {
                    charProgress.classList.remove('bg-primary');
                    charProgress.classList.add('bg-success');
                    coverLetter.classList.remove('is-invalid');
                    coverLetterFeedback.style.display = 'none';
                }
            }
            
            // Initial update
            updateCharCount();
            
            // Update on input
            coverLetter.addEventListener('input', updateCharCount);
            
            // Form validation
            form.addEventListener('submit', function(event) {
                if (coverLetter.value.length < 100) {
                    event.preventDefault();
                    coverLetter.classList.add('is-invalid');
                    coverLetterFeedback.style.display = 'block';
                    coverLetter.focus();
                }
            });
        });
    </script>
    
    <style>
        .icon-box-small {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            font-size: 1.25rem;
            transition: all 0.3s ease;
        }

        .icon-box-small:hover {
            transform: scale(1.1);
        }
        
        .contact-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 40px;
            width: 40px;
            background-color: #f8f9fa;
            border-radius: 50%;
            color: #0078a8;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .contact-icon:hover {
            background-color: #0078a8;
            color: #ffffff;
            transform: translateY(-3px);
        }

        .badge {
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: 0.03em;
            padding: 0.5rem 1rem;
            background-color: #0078a8 !important;
            color: #fff;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(var(--bs-primary-rgb), 0.15);
        }

        .form-control,
        .form-select {
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .form-control-lg,
        .form-select-lg {
            border-radius: 0.5rem;
        }

        .form-floating > label {
            color: #64748b;
        }

        .form-floating > .form-control:focus ~ label,
        .form-floating > .form-control:not(:placeholder-shown) ~ label,
        .form-floating > .form-select ~ label {
            color: var(--primary);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 150px;
            border-radius: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, #0056b3 100%);
            border: none;
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            box-shadow: 0 4px 15px rgba(var(--bs-primary-rgb), 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(var(--bs-primary-rgb), 0.4);
        }

        .btn-outline-primary {
            border: 2px solid var(--primary);
            color: var(--primary);
            transition: all 0.3s ease;
        }

        .btn-outline-primary:hover {
            background: var(--primary);
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(var(--bs-primary-rgb), 0.3);
        }

        .btn-outline-secondary {
            border: 2px solid #6c757d;
            transition: all 0.3s ease;
        }

        .btn-outline-secondary:hover {
            transform: translateY(-2px);
        }

        .sticky-top {
            transition: all 0.3s ease;
        }

        @media (max-width: 991.98px) {
            .sticky-top {
                position: relative !important;
                top: 0 !important;
            }
        }

        .premium-card {
            border: none;
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        .premium-card:hover {
            transform: translateY(-5px);
        }

        /* Form validation styles */
        .was-validated .form-control:valid,
        .was-validated .form-select:valid {
            border-color: #28a745;
        }

        .was-validated .form-control:invalid,
        .was-validated .form-select:invalid {
            border-color: #dc3545;
        }

        /* File upload styling */
        input[type="file"]::file-selector-button {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-right: 1rem;
        }

        input[type="file"]::file-selector-button:hover {
            background-color: #0078a8;
        }
    </style>

    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });

        // Form validation
        (function () {
            'use strict'
            const forms = document.querySelectorAll('.needs-validation')
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()

        // File upload validation
        document.getElementById('resume').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const fileSize = file.size / 1024 / 1024; // in MB
                const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                
                if (fileSize > 5) {
                    alert('File size must be less than 5MB');
                    e.target.value = '';
                    return;
                }
                
                if (!allowedTypes.includes(file.type)) {
                    alert('Only PDF, DOC, and DOCX files are allowed');
                    e.target.value = '';
                    return;
                }
            }
        });

        // Cover letter character count
        const coverLetter = document.getElementById('cover_letter');
        const minChars = 100;
        
        coverLetter.addEventListener('input', function() {
            const charCount = this.value.length;
            const small = this.nextElementSibling;
            
            if (charCount < minChars) {
                small.textContent = `${charCount}/${minChars} characters (minimum required)`;
                small.classList.add('text-danger');
                small.classList.remove('text-success');
            } else {
                small.textContent = `${charCount} characters`;
                small.classList.remove('text-danger');
                small.classList.add('text-success');
            }
        });

        // Form submission
        document.getElementById('applicationForm').addEventListener('submit', function(e) {
            const coverLetterValue = coverLetter.value.trim();
            
            if (coverLetterValue.length < minChars) {
                e.preventDefault();
                alert(`Cover letter must be at least ${minChars} characters long.`);
                coverLetter.focus();
                return false;
            }
        });
    </script>
</body>
</html>