<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once './Configurations/config.php';

// Check if job ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: career.php');
    exit();
}

$job_id = mysqli_real_escape_string($conn, $_GET['id']);

// Fetch job details
$query = "SELECT * FROM Careers WHERE job_id = ? AND status = 'Active'";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $job_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$job = mysqli_fetch_assoc($result);

if (!$job) {
    header('Location: career.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $cover_letter = mysqli_real_escape_string($conn, $_POST['cover_letter']);
    $portfolio_url = mysqli_real_escape_string($conn, $_POST['portfolio_url']);

    // Handle resume upload
    $resume_path = '';
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = './Uploads/Resumes/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_extension = strtolower(pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['pdf', 'doc', 'docx'];

        if (in_array($file_extension, $allowed_extensions)) {
            $file_name = uniqid() . '_' . $_FILES['resume']['name'];
            $target_path = $upload_dir . $file_name;

            if (move_uploaded_file($_FILES['resume']['tmp_name'], $target_path)) {
                $resume_path = $file_name;
            }
        }
    }

    if (!empty($resume_path)) {
        // Insert application into database
        $insert_query = "INSERT INTO job_applications (
            job_id, first_name, last_name, email, phone, resume_path, 
            cover_letter, portfolio_url, application_date, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'Pending')";

        $insert_stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($insert_stmt, "isssssss", 
            $job_id, $first_name, $last_name, $email, $phone, 
            $resume_path, $cover_letter, $portfolio_url
        );

        if (mysqli_stmt_execute($insert_stmt)) {
            $_SESSION['message'] = "Your application has been submitted successfully!";
            $_SESSION['message_type'] = "success";
            header('Location: career.php');
            exit();
        } else {
            $error = "Error submitting application: " . mysqli_error($conn);
        }
    } else {
        $error = "Please upload a valid resume file (PDF, DOC, or DOCX)";
    }
}
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
    <!-- Custom CSS -->
    <link rel="stylesheet" href="./css/style.css">
        
    <link rel="icon" type="image/png" href="./Images/Logos/GD_Only_logo.png">
    <link rel="icon" type="image/png" sizes="32x32" href="./Images/Logos/GD_Only_logo.png">
    <link rel="icon" type="image/png" sizes="16x16" href="./Images/Logos/GD_Only_logo.png">
    <link rel="shortcut icon" href="./Images/Logos/GD_Only_logo.png">
    <link rel="apple-touch-icon" href="./Images/Logos/GD_Only_logo.png">
    <meta name="msapplication-TileImage" content="./Images/Logos/GD_Only_logo.png">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <!-- Page Header -->
    <section class="page-header position-relative overflow-hidden">
        <div class="container position-relative py-7">
            <div class="row align-items-center">
                <div class="col-md-7" data-aos="fade-right">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-3">
                            <li class="breadcrumb-item"><a href="index.php" class="text-white-50">Home</a></li>
                            <li class="breadcrumb-item"><a href="career.php" class="text-white-50">Careers</a></li>
                            <li class="breadcrumb-item active text-white" aria-current="page">Apply</li>
                        </ol>
                    </nav>
                    <h1 class="text-white display-5 fw-bold mb-3">Apply for <?php echo htmlspecialchars($job['job_title']); ?></h1>
                    <p class="text-white-50 lead mb-0">Take the first step towards joining our team</p>
                </div>
            </div>
        </div>
        <div class="page-header-shape">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
                <path fill="#ffffff" fill-opacity="1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,112C672,96,768,96,864,112C960,128,1056,160,1152,160C1248,160,1344,128,1392,112L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
            </svg>
        </div>
    </section>

    <!-- Application Form Section -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="premium-card">
                        <div class="card-body p-4">
                            <form action="" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                                        <div class="invalid-feedback">Please provide your first name.</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                                        <div class="invalid-feedback">Please provide your last name.</div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                        <div class="invalid-feedback">Please provide a valid email address.</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control" id="phone" name="phone" required>
                                        <div class="invalid-feedback">Please provide your phone number.</div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="resume" class="form-label">Resume/CV <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" id="resume" name="resume" accept=".pdf,.doc,.docx" required>
                                    <div class="form-text">Accepted formats: PDF, DOC, DOCX (Max size: 5MB)</div>
                                    <div class="invalid-feedback">Please upload your resume.</div>
                                </div>

                                <div class="mb-3">
                                    <label for="cover_letter" class="form-label">Cover Letter</label>
                                    <textarea class="form-control" id="cover_letter" name="cover_letter" rows="4"></textarea>
                                </div>

                                <div class="mb-4">
                                    <label for="portfolio_url" class="form-label">Portfolio URL</label>
                                    <input type="url" class="form-control" id="portfolio_url" name="portfolio_url" placeholder="https://">
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        Submit Application <i class="bi bi-arrow-right ms-2"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
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

    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            easing: 'ease-in-out',
            once: true,
            mirror: false
        });

        // Form validation
        (function() {
            'use strict';
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
</body>
</html> 