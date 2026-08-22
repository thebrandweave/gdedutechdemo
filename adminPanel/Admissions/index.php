<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Prevent browser caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../admin_login.php');
    exit();
}

// Database connection
require_once '../../Configurations/config.php';

// Get admin details from session
$admin_name = $_SESSION['username'] ?? 'Admin';

// Handle deletion
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $query = "DELETE FROM student_admissions WHERE id = $id";
    if (mysqli_query($conn, $query)) {
        $_SESSION['message'] = "Admission record deleted successfully.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error deleting record: " . mysqli_error($conn);
        $_SESSION['message_type'] = "danger";
    }
    header("Location: index.php");
    exit();
}

// Handle Add Admission
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['add_admission'])) {
    $student_name = mysqli_real_escape_string($conn, trim($_POST['student_name']));
    $college = mysqli_real_escape_string($conn, trim($_POST['college']));
    $phone_number = mysqli_real_escape_string($conn, trim($_POST['phone_number']));
    $email_id = mysqli_real_escape_string($conn, trim($_POST['email_id']));
    $course_applied = mysqli_real_escape_string($conn, trim($_POST['course_applied']));
    $internship = mysqli_real_escape_string($conn, trim($_POST['internship']));
    $start_date = mysqli_real_escape_string($conn, trim($_POST['start_date']));
    $end_date = mysqli_real_escape_string($conn, trim($_POST['end_date']));
    $key_skills = mysqli_real_escape_string($conn, trim($_POST['key_skills']));

    if (empty($student_name) || empty($phone_number) || empty($email_id) || empty($course_applied) || empty($start_date) || empty($end_date) || empty($key_skills)) {
        $_SESSION['message'] = "Required fields are missing.";
        $_SESSION['message_type'] = "danger";
    } else {
        
        // Handle Image Upload using Absolute Path
        $profile_image = NULL;
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = rtrim(__DIR__, '/\\') . '/../../uploads/profiles/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $file_ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
            if(in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $new_filename = 'profile_' . time() . '_' . rand(1000, 9999) . '.' . $file_ext;
                $destination = $upload_dir . $new_filename;
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $destination)) {
                    $profile_image = $new_filename;
                }
            }
        }

        // Handle Certificate Upload using Absolute Path
        $certificate_file = NULL;
        if (isset($_FILES['certificate_file']) && $_FILES['certificate_file']['error'] === UPLOAD_ERR_OK) {
            $cert_upload_dir = rtrim(__DIR__, '/\\') . '/../../uploads/certificates/';
            if (!is_dir($cert_upload_dir)) {
                mkdir($cert_upload_dir, 0777, true);
            }
            $cert_ext = strtolower(pathinfo($_FILES['certificate_file']['name'], PATHINFO_EXTENSION));
            if(in_array($cert_ext, ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $new_cert_name = 'cert_' . time() . '_' . rand(1000, 9999) . '.' . $cert_ext;
                $cert_destination = $cert_upload_dir . $new_cert_name;
                if (move_uploaded_file($_FILES['certificate_file']['tmp_name'], $cert_destination)) {
                    $certificate_file = $new_cert_name;
                }
            }
        }

        // Begin transaction to ensure safe ID generation
        mysqli_begin_transaction($conn);
        
        $query = "SELECT student_id FROM student_admissions ORDER BY id DESC LIMIT 1 FOR UPDATE";
        $res = mysqli_query($conn, $query);
        $next_num = 1001; // default starting number
        if ($res && mysqli_num_rows($res) > 0) {
            $row = mysqli_fetch_assoc($res);
            $last_id = $row['student_id'];
            $num_part = substr($last_id, 5);
            if (is_numeric($num_part)) {
                $next_num = intval($num_part) + 1;
            }
        }
        
        $student_id = "GDEDU" . str_pad($next_num, 4, "0", STR_PAD_LEFT);
        
        // Insert record
        $insert_query = "INSERT INTO student_admissions (student_id, profile_image, certificate_file, student_name, college, phone_number, email_id, course_applied, internship, start_date, end_date, key_skills) 
                         VALUES ('$student_id', " . ($profile_image ? "'$profile_image'" : "NULL") . ", " . ($certificate_file ? "'$certificate_file'" : "NULL") . ", '$student_name', '$college', '$phone_number', '$email_id', '$course_applied', '$internship', '$start_date', '$end_date', '$key_skills')";
        
        if (mysqli_query($conn, $insert_query)) {
            mysqli_commit($conn);
            $_SESSION['message'] = "Student admitted successfully! ID: " . $student_id;
            $_SESSION['message_type'] = "success";
        } else {
            mysqli_rollback($conn);
            $_SESSION['message'] = "Error adding admission: " . mysqli_error($conn);
            $_SESSION['message_type'] = "danger";
        }
        
        header("Location: index.php");
        exit();
    }
}

// Handle Edit Admission
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['edit_admission'])) {
    $id = intval($_POST['id']);
    $student_name = mysqli_real_escape_string($conn, trim($_POST['student_name']));
    $college = mysqli_real_escape_string($conn, trim($_POST['college']));
    $phone_number = mysqli_real_escape_string($conn, trim($_POST['phone_number']));
    $email_id = mysqli_real_escape_string($conn, trim($_POST['email_id']));
    $course_applied = mysqli_real_escape_string($conn, trim($_POST['course_applied']));
    $internship = mysqli_real_escape_string($conn, trim($_POST['internship']));
    $start_date = mysqli_real_escape_string($conn, trim($_POST['start_date']));
    $end_date = mysqli_real_escape_string($conn, trim($_POST['end_date']));
    $key_skills = mysqli_real_escape_string($conn, trim($_POST['key_skills']));

    if (empty($student_name) || empty($phone_number) || empty($email_id) || empty($course_applied) || empty($start_date) || empty($end_date) || empty($key_skills)) {
        $_SESSION['message'] = "Required fields are missing.";
        $_SESSION['message_type'] = "danger";
    } else {
        // Handle Image Upload for Edit
        $image_update_query = "";
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
           $upload_dir = rtrim(__DIR__, '/\\') . '/../../uploads/profiles/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            
            $file_ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
            if(in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $new_filename = 'profile_' . time() . '_' . rand(1000, 9999) . '.' . $file_ext;
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_dir . $new_filename)) {
                    $image_update_query = "profile_image = '$new_filename', ";
                }
            }
        }

        // Handle Certificate Upload for Edit
        $cert_update_query = "";
        if (isset($_FILES['certificate_file']) && $_FILES['certificate_file']['error'] === UPLOAD_ERR_OK) {
            $cert_upload_dir = rtrim(__DIR__, '/\\') . '/../../uploads/certificates/';
            if (!is_dir($cert_upload_dir)) mkdir($cert_upload_dir, 0777, true);
            
            $cert_ext = strtolower(pathinfo($_FILES['certificate_file']['name'], PATHINFO_EXTENSION));
            if(in_array($cert_ext, ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $new_cert_filename = 'cert_' . time() . '_' . rand(1000, 9999) . '.' . $cert_ext;
                if (move_uploaded_file($_FILES['certificate_file']['tmp_name'], $cert_upload_dir . $new_cert_filename)) {
                    $cert_update_query = "certificate_file = '$new_cert_filename', ";
                }
            }
        }
        
        $update_query = "UPDATE student_admissions SET 
                         $image_update_query
                         $cert_update_query
                         student_name = '$student_name', 
                         college = '$college', 
                         phone_number = '$phone_number', 
                         email_id = '$email_id', 
                         course_applied = '$course_applied', 
                         internship = '$internship',
                         start_date = '$start_date',
                         end_date = '$end_date',
                         key_skills = '$key_skills'
                         WHERE id = $id";
        
        if (mysqli_query($conn, $update_query)) {
            $_SESSION['message'] = "Admission record updated successfully.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error updating record: " . mysqli_error($conn);
            $_SESSION['message_type'] = "danger";
        }
        header("Location: index.php");
        exit();
    }
}

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Fetch total records
$total_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM student_admissions");
if (!$total_query) {
    die("Database Error (fetching count): " . mysqli_error($conn));
}
$total_row = mysqli_fetch_assoc($total_query);
$total_records = $total_row['count'];
$total_pages = ceil($total_records / $limit);

// Fetch records with pagination
$query = "SELECT * FROM student_admissions ORDER BY id DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);
if (!$result) {
    die("Database Error (fetching admissions): " . mysqli_error($conn));
}

// Fetch all courses for the dropdown select menu
try {
    $courses_query = mysqli_query($conn, "SELECT course_id, title FROM Courses ORDER BY title ASC");
} catch (mysqli_sql_exception $e) {
    $courses_query = false; 
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Admissions & Certifications - GD Edu Tech Admin</title>
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
    <style>
        html, body {
            overflow-x: hidden !important;
            max-width: 100vw;
        }
        .main-content {
            max-width: 100%;
            overflow-x: hidden !important;
        }
    </style>
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
                    <li class="w-100"><a href="../Admissions/" class="nav-link active"><i class="bi bi-person-plus me-2"></i> Student Admission</a></li>
                    <li class="w-100"><a href="../Courses/" class="nav-link"><i class="bi bi-book me-2"></i> Courses</a></li>
                    <li class="w-100"><a href="../Applications/" class="nav-link"><i class="bi bi-journal-text me-2"></i> Scholarships</a></li>
                    <li class="w-100"><a href="../Events/" class="nav-link"><i class="bi bi-calendar2-event me-2"></i> Events</a></li>
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
            <div class="col main-content min-vh-100 d-flex flex-column" style="min-width: 0; overflow-x: hidden;">
                
                <!-- Top Header Bar -->
                <div class="bg-white border-bottom px-4 py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <h4 class="fw-bold text-dark mb-0">Student Admissions & Certification IDs</h4>
                        <span class="text-muted small">Manage admitted students, issue verification QR codes, and generate certificates</span>
                    </div>

                    <button type="button" class="btn btn-primary text-nowrap" data-bs-toggle="modal" data-bs-target="#addAdmissionModal">
                        <i class="bi bi-person-plus-fill me-2"></i>New Student Admission
                    </button>
                </div>

                <div class="p-4 flex-grow-1">
                    
                    <!-- Alert Messages -->
                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-info-circle-fill fs-5"></i>
                                <span class="fw-semibold"><?php echo htmlspecialchars($_SESSION['message']); ?></span>
                            </div>
                            <?php
                            unset($_SESSION['message']);
                            unset($_SESSION['message_type']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Admissions Table Card -->
                    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                        <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-patch-check-fill text-primary me-2"></i>Admitted Students Registry</h6>
                            <span class="badge bg-primary bg-opacity-10 text-primary border px-3 py-1.5 rounded-pill fw-semibold">
                                Total Admissions: <?php echo $total_records; ?>
                            </span>
                        </div>

                        <div class="table-responsive" style="max-width: 100%; overflow-x: auto;">
                            <table class="table table-hover align-middle mb-0" style="font-size: 0.86rem;">
                                <thead>
                                    <tr>
                                        <th>Student ID</th>
                                        <th class="text-center">Verification QR</th>
                                        <th class="text-center">Certificate</th>
                                        <th>Student Info</th>
                                        <th>College / Campus</th>
                                        <th>Course Applied</th>
                                        <th>Internship</th>
                                        <th>Duration</th>
                                        <th>Key Skills</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($result) > 0): ?>
                                        <?php while ($admission = mysqli_fetch_assoc($result)): ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-primary bg-opacity-10 text-black border border-primary-subtle px-2.5 py-1 rounded-pill font-monospace fw-bold fs-6">
                                                        <?php echo htmlspecialchars($admission['student_id']); ?>
                                                    </span>
                                                </td>

                                                <td class="text-center">
                                                    <?php 
                                                    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? '') == 443) ? "https://" : "http://";
                                                    $domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
                                                    $path = (strpos($domain, 'gdedutech.com') !== false) ? "/verify_certificate.php" : "/gdedutechdemo/verify_certificate.php";
                                                    $verify_url = $protocol . $domain . $path . "?student_id=" . $admission['student_id'];
                                                    $qr_api_url = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($verify_url);
                                                    ?>
                                                    <div class="d-flex flex-column align-items-center gap-1">
                                                        <a href="<?php echo $verify_url; ?>" target="_blank" title="Verify Certificate (Opens live tab)">
                                                            <img src="<?php echo $qr_api_url; ?>" alt="QR Code" class="rounded-2 border p-1 bg-white shadow-sm" style="width: 60px; height: 60px; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.2)';" onmouseout="this.style.transform='scale(1)';">
                                                        </a>
                                                        <a href="download_qr.php?student_id=<?php echo urlencode($admission['student_id']); ?>" class="btn btn-sm btn-outline-secondary py-1 px-4 rounded-pill" style="font-size: 0.62rem;" title="Download QR Image">
                                                            <i class="bi bi-download "></i> QR
                                                        </a>
                                                    </div>
                                                </td>

                                                <td class="text-center">
                                                    <?php if (!empty($admission['certificate_file'])): ?>
                                                        <a href="../../uploads/certificates/<?php echo htmlspecialchars($admission['certificate_file']); ?>" target="_blank" class="btn btn-sm btn-outline-success py-1 px-2.5 rounded-pill font-monospace fw-semibold shadow-sm" style="font-size: 0.72rem;" title="View Uploaded Certificate">
                                                            <i class="bi bi-file-earmark-pdf-fill me-1"></i>Cert
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="badge bg-light text-muted border px-2 py-1 rounded-pill" style="font-size: 0.68rem;">None</span>
                                                    <?php endif; ?>
                                                </td>

                                                <td style="max-width: 170px;">
                                                    <strong class="text-dark d-block text-truncate"><?php echo htmlspecialchars($admission['student_name']); ?></strong>
                                                    <span class="text-muted small d-block"><i class="bi bi-telephone-fill text-success me-1"></i><?php echo htmlspecialchars($admission['phone_number']); ?></span>
                                                    <span class="text-muted small d-block text-truncate"><i class="bi bi-envelope-fill text-primary me-1"></i><?php echo htmlspecialchars($admission['email_id']); ?></span>
                                                </td>

                                                <td style="max-width: 140px;">
                                                    <span class="badge bg-light text-dark border px-2 py-1 rounded-pill text-wrap">
                                                        <?php echo htmlspecialchars($admission['college'] ?: 'Independent'); ?>
                                                    </span>
                                                </td>

                                                <td style="max-width: 150px;">
                                                    <span class="fw-semibold text-primary d-block text-truncate" title="<?php echo htmlspecialchars($admission['course_applied']); ?>"><?php echo htmlspecialchars($admission['course_applied']); ?></span>
                                                </td>

                                                <td>
                                                    <?php if (!empty($admission['internship']) && strtolower($admission['internship']) !== 'none'): ?>
                                                        <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-2 py-1 rounded-pill" style="font-size: 0.72rem;">
                                                            <i class="bi bi-briefcase me-1"></i><?php echo htmlspecialchars($admission['internship']); ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle px-2 py-1 rounded-pill" style="font-size: 0.72rem;">None</span>
                                                    <?php endif; ?>
                                                </td>

                                                <td class="text-muted small text-nowrap">
                                                    <?php echo $admission['start_date'] ? date('M d, Y', strtotime($admission['start_date'])) : '-'; ?> <br>
                                                    <span class="text-secondary">to <?php echo $admission['end_date'] ? date('M d, Y', strtotime($admission['end_date'])) : '-'; ?></span>
                                                </td>

                                                <td style="max-width: 130px;">
                                                    <span class="text-secondary small d-block text-truncate" title="<?php echo htmlspecialchars($admission['key_skills']); ?>">
                                                        <?php echo htmlspecialchars($admission['key_skills'] ?? '-'); ?>
                                                    </span>
                                                </td>

                                                <td class="text-center">
                                                    <div class="d-flex justify-content-center gap-1.5">
                                                        <a href="javascript:void(0)" class="action-icon view-btn text-info" 
                                                           data-id="<?php echo $admission['id']; ?>"
                                                           data-student-id="<?php echo htmlspecialchars($admission['student_id']); ?>"
                                                           data-profile="<?php echo htmlspecialchars($admission['profile_image'] ?? ''); ?>"
                                                           data-name="<?php echo htmlspecialchars($admission['student_name']); ?>"
                                                           data-college="<?php echo htmlspecialchars($admission['college']); ?>"
                                                           data-phone="<?php echo htmlspecialchars($admission['phone_number']); ?>"
                                                           data-email="<?php echo htmlspecialchars($admission['email_id']); ?>"
                                                           data-course="<?php echo htmlspecialchars($admission['course_applied']); ?>"
                                                           data-internship="<?php echo htmlspecialchars($admission['internship']); ?>"
                                                           data-start="<?php echo htmlspecialchars($admission['start_date']); ?>"
                                                           data-end="<?php echo htmlspecialchars($admission['end_date']); ?>"
                                                           data-skills="<?php echo htmlspecialchars($admission['key_skills']); ?>"
                                                           data-certificate="<?php echo htmlspecialchars($admission['certificate_file'] ?? ''); ?>"
                                                           data-verify-url="<?php echo htmlspecialchars($verify_url); ?>"
                                                           title="View Student Details & Certificate">
                                                            <i class="bi bi-eye-fill text-info fs-6"></i>
                                                        </a>
                                                        <a href="javascript:void(0)" class="action-icon edit-btn" 
                                                           data-id="<?php echo $admission['id']; ?>"
                                                           data-student-id="<?php echo htmlspecialchars($admission['student_id']); ?>"
                                                           data-profile="<?php echo htmlspecialchars($admission['profile_image'] ?? ''); ?>"
                                                           data-name="<?php echo htmlspecialchars($admission['student_name']); ?>"
                                                           data-college="<?php echo htmlspecialchars($admission['college']); ?>"
                                                           data-phone="<?php echo htmlspecialchars($admission['phone_number']); ?>"
                                                           data-email="<?php echo htmlspecialchars($admission['email_id']); ?>"
                                                           data-course="<?php echo htmlspecialchars($admission['course_applied']); ?>"
                                                           data-internship="<?php echo htmlspecialchars($admission['internship']); ?>"
                                                           data-start="<?php echo htmlspecialchars($admission['start_date']); ?>"
                                                           data-end="<?php echo htmlspecialchars($admission['end_date']); ?>"
                                                           data-skills="<?php echo htmlspecialchars($admission['key_skills']); ?>"
                                                           data-certificate="<?php echo htmlspecialchars($admission['certificate_file'] ?? ''); ?>"
                                                           title="Edit Student Admission">
                                                            <i class="bi bi-pencil-fill text-warning fs-6"></i>
                                                        </a>
                                                        <a href="index.php?delete=1&id=<?php echo $admission['id']; ?>" class="action-icon text-danger" onclick="return confirm('Are you sure you want to delete this admission record?')" title="Delete Record">
                                                            <i class="bi bi-trash-fill fs-6"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="11" class="text-center py-4 text-muted">No student admission records found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link rounded-3 mx-1" href="?page=<?php echo $i; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>

                </div>
            </div>

        </div>
    </div>

    <!-- Add Admission Modal -->
    <div class="modal fade" id="addAdmissionModal" tabindex="-1" aria-labelledby="addAdmissionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                <form action="index.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-header bg-dark text-white p-4">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-person-plus-fill text-primary fs-4"></i>
                            <h5 class="modal-title fw-bold" id="addAdmissionModalLabel">New Student Admission</h5>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4">
                        
                        <div class="row g-3">
                            
                            <div class="col-12">
                                <label for="profile_image" class="form-label font-weight-semibold">Profile Photo (Optional)</label>
                                <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                            </div>

                            <div class="col-12">
                                <label for="certificate_file" class="form-label font-weight-semibold"><i class="bi bi-file-earmark-pdf-fill text-danger me-1"></i>Certificate Document (PDF / Image - Optional)</label>
                                <input type="file" class="form-control" id="certificate_file" name="certificate_file" accept=".pdf,image/*">
                                <span class="text-muted small ms-1">Upload verified student certificate (PDF or Image file).</span>
                            </div>

                            <div class="col-md-6">
                                <label for="student_name" class="form-label font-weight-semibold">Student Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="student_name" name="student_name" required placeholder="Full Student Name">
                            </div>

                            <div class="col-md-6">
                                <label for="phone_number" class="form-label font-weight-semibold">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="phone_number" name="phone_number" required placeholder="e.g. +91 9876543210">
                            </div>

                            <div class="col-md-6">
                                <label for="email_id" class="form-label font-weight-semibold">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email_id" name="email_id" required placeholder="student@example.com">
                            </div>

                            <div class="col-md-6">
                                <label for="course_applied" class="form-label font-weight-semibold">Course Applied <span class="text-danger">*</span></label>
                                <select class="form-select" id="course_applied" name="course_applied" required>
                                    <option value="" disabled selected>Select course...</option>
                                    <option value="Full Stack Development">Full Stack Development</option>
                                    <option value="Architectural Design">Architectural Design</option>
                                    <option value="Interior Design">Interior Design</option>
                                    <option value="Digital Marketing">Digital Marketing</option>
                                    <option value="Graphic Design & Video Editing">Graphic Design & Video Editing</option>
                                    <option value="Graphic Design">Graphic Design</option>
                                    <option value="Visual Media Program">Visual Media Program</option>
                                    <option value="Tally & GST">Tally & GST</option>
                                    <option value="Advanced Excel">Advanced Excel</option>
                                    <?php 
                                    $listed_courses = [
                                        "Full Stack Development",
                                        "Architectural Design",
                                        "Interior Design",
                                        "Digital Marketing",
                                        "Graphic Design & Video Editing",
                                        "Graphic Design",
                                        "Visual Media Program",
                                        "Tally & GST",
                                        "Advanced Excel"
                                    ];
                                    if ($courses_query && mysqli_num_rows($courses_query) > 0): 
                                        mysqli_data_seek($courses_query, 0);
                                        while ($course = mysqli_fetch_assoc($courses_query)): 
                                            if (!in_array($course['title'], $listed_courses)):
                                    ?>
                                                <option value="<?php echo htmlspecialchars($course['title']); ?>">
                                                    <?php echo htmlspecialchars($course['title']); ?>
                                                </option>
                                    <?php 
                                            endif;
                                        endwhile; 
                                    endif; 
                                    ?>
                                </select>
                            </div>

                            <div class="col-12">
                                <div class="p-3 bg-light rounded-3 border">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="has_college" onchange="toggleOptionalField('', 'college')">
                                        <label class="form-check-label font-weight-semibold" for="has_college">Attending College / University</label>
                                    </div>
                                    <div id="college_wrapper" class="mt-2" style="display: none;">
                                        <input type="text" class="form-control" id="college" name="college" placeholder="Enter College / Institute Name">
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="p-3 bg-light rounded-3 border">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="has_internship" onchange="toggleOptionalField('', 'internship')">
                                        <label class="form-check-label font-weight-semibold" for="has_internship">Includes Practical Internship</label>
                                    </div>
                                    <div id="internship_wrapper" class="mt-2" style="display: none;">
                                        <input type="text" class="form-control" id="internship" name="internship" placeholder="e.g. Yes (3 Months), Live Projects">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="start_date" class="form-label font-weight-semibold">Start Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="start_date" name="start_date" required>
                            </div>

                            <div class="col-md-6">
                                <label for="end_date" class="form-label font-weight-semibold">End Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="end_date" name="end_date" required>
                            </div>

                            <div class="col-12">
                                <label for="key_skills" class="form-label font-weight-semibold">Key Skills / Tools <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="key_skills" name="key_skills" required placeholder="e.g. HTML5, CSS3, React, Node.js, Figma">
                            </div>

                        </div>

                    </div>

                    <div class="modal-footer bg-light p-3 border-top">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_admission" class="btn btn-primary px-4 fw-bold">Submit & Admit Student</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Admission Modal -->
    <div class="modal fade" id="editAdmissionModal" tabindex="-1" aria-labelledby="editAdmissionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                <form action="index.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="edit_id">

                    <div class="modal-header bg-dark text-white p-4">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-pencil-square text-warning fs-4"></i>
                            <h5 class="modal-title fw-bold" id="editAdmissionModalLabel">Edit Student Admission</h5>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4">
                        
                        <!-- Student Profile Header Banner -->
                        <div class="p-3 bg-light rounded-4 border mb-4 d-flex align-items-center gap-3">
                            <div id="edit_profile_avatar_wrapper">
                                <!-- Populated dynamically by JS -->
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark mb-1" id="edit_profile_header_name">Student Name</h5>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-3 py-1 rounded-pill font-monospace fw-bold" id="edit_profile_header_id">GDEDU1001</span>
                            </div>
                        </div>
                        
                        <div class="row g-3">
                            
                            <div class="col-12">
                                <label for="edit_profile_image" class="form-label font-weight-semibold"><i class="bi bi-person-circle me-1 text-primary"></i>Update Profile Image (Optional)</label>
                                <input type="file" class="form-control" id="edit_profile_image" name="profile_image" accept="image/*">
                                <span class="text-muted small ms-1">Leave blank to keep existing profile photo.</span>
                            </div>

                            <div class="col-12">
                                <label for="edit_certificate_file" class="form-label font-weight-semibold"><i class="bi bi-file-earmark-pdf-fill text-danger me-1"></i>Upload / Replace Certificate Document (PDF / Image)</label>
                                <input type="file" class="form-control" id="edit_certificate_file" name="certificate_file" accept=".pdf,image/*">
                                <div id="edit_certificate_preview" class="mt-1"></div>
                            </div>

                            <div class="col-md-6">
                                <label for="edit_student_name" class="form-label font-weight-semibold">Student Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_student_name" name="student_name" required>
                            </div>

                            <div class="col-md-6">
                                <label for="edit_phone_number" class="form-label font-weight-semibold">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="edit_phone_number" name="phone_number" required>
                            </div>

                            <div class="col-md-6">
                                <label for="edit_email_id" class="form-label font-weight-semibold">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="edit_email_id" name="email_id" required>
                            </div>

                            <div class="col-md-6">
                                <label for="edit_course_applied" class="form-label font-weight-semibold">Course Applied <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_course_applied" name="course_applied" required>
                                    <option value="" disabled selected>Select course...</option>
                                    <option value="Full Stack Development">Full Stack Development</option>
                                    <option value="Architectural Design">Architectural Design</option>
                                    <option value="Interior Design">Interior Design</option>
                                    <option value="Digital Marketing">Digital Marketing</option>
                                    <option value="Graphic Design & Video Editing">Graphic Design & Video Editing</option>
                                    <option value="Photography & Camera Handling">Photography & Camera Handling</option>
                                    <?php 
                                    if ($courses_query && mysqli_num_rows($courses_query) > 0): 
                                        mysqli_data_seek($courses_query, 0);
                                        while ($course = mysqli_fetch_assoc($courses_query)): 
                                            if (!in_array($course['title'], $listed_courses)):
                                    ?>
                                                <option value="<?php echo htmlspecialchars($course['title']); ?>">
                                                    <?php echo htmlspecialchars($course['title']); ?>
                                                </option>
                                    <?php 
                                            endif;
                                        endwhile; 
                                    endif; 
                                    ?>
                                </select>
                            </div>

                            <div class="col-12">
                                <div class="p-3 bg-light rounded-3 border">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="edit_has_college" onchange="toggleOptionalField('edit_', 'college')">
                                        <label class="form-check-label font-weight-semibold" for="edit_has_college">Attending College / University</label>
                                    </div>
                                    <div id="edit_college_wrapper" class="mt-2" style="display: none;">
                                        <input type="text" class="form-control" id="edit_college" name="college">
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="p-3 bg-light rounded-3 border">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="edit_has_internship" onchange="toggleOptionalField('edit_', 'internship')">
                                        <label class="form-check-label font-weight-semibold" for="edit_has_internship">Includes Practical Internship</label>
                                    </div>
                                    <div id="edit_internship_wrapper" class="mt-2" style="display: none;">
                                        <input type="text" class="form-control" id="edit_internship" name="internship">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="edit_start_date" class="form-label font-weight-semibold">Start Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="edit_start_date" name="start_date" required>
                            </div>

                            <div class="col-md-6">
                                <label for="edit_end_date" class="form-label font-weight-semibold">End Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="edit_end_date" name="end_date" required>
                            </div>

                            <div class="col-12">
                                <label for="edit_key_skills" class="form-label font-weight-semibold">Key Skills / Tools <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_key_skills" name="key_skills" required>
                            </div>

                        </div>

                    </div>

                    <div class="modal-footer bg-light p-3 border-top">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="edit_admission" class="btn btn-warning px-4 fw-bold text-dark">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Admission Modal -->
    <div class="modal fade" id="viewAdmissionModal" tabindex="-1" aria-labelledby="viewAdmissionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                <div class="modal-header bg-dark text-white p-4">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-eye-fill text-info fs-4"></i>
                        <h5 class="modal-title fw-bold" id="viewAdmissionModalLabel">Student Admission Details</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Top Banner with Avatar, Name, ID & Public Link -->
                    <div class="p-3 bg-light rounded-4 border mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div id="view_profile_avatar_wrapper">
                                <!-- Populated by JS -->
                            </div>
                            <div>
                                <h4 class="fw-bold text-dark mb-1" id="view_student_name">Student Name</h4>
                                <span class="badge bg-primary bg-opacity-10 text-black border border-primary-subtle px-3 py-1 rounded-pill font-monospace fw-bold fs-6" id="view_student_id">GDEDU1001</span>
                            </div>
                        </div>
                        <a href="#" id="view_public_link" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill fw-semibold px-3">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Verification Page
                        </a>
                    </div>

                    <!-- Structured Grid Details -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 bg-white border rounded-3 h-100">
                                <span class="text-muted small d-block"><i class="bi bi-envelope-fill text-primary me-1"></i> Email Address</span>
                                <strong class="text-dark" id="view_email">student@example.com</strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-white border rounded-3 h-100">
                                <span class="text-muted small d-block"><i class="bi bi-telephone-fill text-success me-1"></i> Phone Number</span>
                                <strong class="text-dark" id="view_phone">+91 9876543210</strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-white border rounded-3 h-100">
                                <span class="text-muted small d-block"><i class="bi bi-journal-bookmark-fill text-primary me-1"></i> Course Applied</span>
                                <strong class="text-primary" id="view_course">Full Stack Development</strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-white border rounded-3 h-100">
                                <span class="text-muted small d-block"><i class="bi bi-building-fill text-secondary me-1"></i> College / Institution</span>
                                <strong class="text-dark" id="view_college">Independent</strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-white border rounded-3 h-100">
                                <span class="text-muted small d-block"><i class="bi bi-briefcase-fill text-success me-1"></i> Internship</span>
                                <strong class="text-dark" id="view_internship">None</strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-white border rounded-3 h-100">
                                <span class="text-muted small d-block"><i class="bi bi-calendar3 text-primary me-1"></i> Duration</span>
                                <strong class="text-dark" id="view_duration">Jan 01, 2026 to Jun 30, 2026</strong>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="p-3 bg-white border rounded-3">
                                <span class="text-muted small d-block mb-1.5"><i class="bi bi-tags-fill text-warning me-1"></i> Key Skills</span>
                                <div id="view_skills_wrapper" class="d-flex flex-wrap gap-1">
                                    <!-- Populated by JS -->
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="p-3 bg-white border rounded-3">
                                <span class="text-muted small d-block mb-1.5"><i class="bi bi-file-earmark-pdf-fill text-danger me-1"></i> Certificate Document</span>
                                <div id="view_certificate_wrapper">
                                    <!-- Populated by JS -->
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer bg-light p-3 border-top">
                    <button type="button" class="btn btn-secondary px-4 rounded-pill" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    function toggleOptionalField(prefix, field) {
        const checkbox = document.getElementById(prefix + 'has_' + field);
        const wrapper = document.getElementById(prefix + field + '_wrapper');
        const input = document.getElementById(prefix + field);
        const show = checkbox.checked;
        wrapper.style.display = show ? 'block' : 'none';
        if (!show) {
            input.value = '';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const editButtons = document.querySelectorAll('.edit-btn');
        const viewButtons = document.querySelectorAll('.view-btn');
        const editModal = new bootstrap.Modal(document.getElementById('editAdmissionModal'));
        const viewModal = new bootstrap.Modal(document.getElementById('viewAdmissionModal'));

        document.getElementById('addAdmissionModal').addEventListener('show.bs.modal', function() {
            document.getElementById('has_college').checked = false;
            document.getElementById('has_internship').checked = false;
            toggleOptionalField('', 'college');
            toggleOptionalField('', 'internship');
        });

        // View Button Event Listener
        viewButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const studentName = this.getAttribute('data-name') || '';
                const studentId = this.getAttribute('data-student-id') || '';
                const profileVal = (this.getAttribute('data-profile') || '').trim();

                document.getElementById('view_student_name').textContent = studentName;
                document.getElementById('view_student_id').textContent = studentId;
                document.getElementById('view_public_link').href = this.getAttribute('data-verify-url');

                const avatarWrapper = document.getElementById('view_profile_avatar_wrapper');
                if (profileVal !== '') {
                    avatarWrapper.innerHTML = '<img src="../../uploads/profiles/' + profileVal + '" alt="Profile" class="rounded-circle border object-fit-cover shadow-sm" style="width: 56px; height: 56px;">';
                } else {
                    const nameParts = studentName.trim().split(' ');
                    let initials = nameParts[0] ? nameParts[0].charAt(0).toUpperCase() : '';
                    if (nameParts.length > 1) initials += nameParts[nameParts.length - 1].charAt(0).toUpperCase();
                    avatarWrapper.innerHTML = '<div class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px; background: linear-gradient(135deg, #0d7298, #0f172a); font-size: 18px;">' + (initials || 'GD') + '</div>';
                }

                document.getElementById('view_email').textContent = this.getAttribute('data-email') || '-';
                document.getElementById('view_phone').textContent = this.getAttribute('data-phone') || '-';
                document.getElementById('view_course').textContent = this.getAttribute('data-course') || '-';
                document.getElementById('view_college').textContent = this.getAttribute('data-college') || 'Independent';
                document.getElementById('view_internship').textContent = this.getAttribute('data-internship') || 'None';

                const startDate = this.getAttribute('data-start') || '';
                const endDate = this.getAttribute('data-end') || '';
                document.getElementById('view_duration').textContent = (startDate || '-') + ' to ' + (endDate || '-');

                const skillsVal = this.getAttribute('data-skills') || '';
                const skillsWrapper = document.getElementById('view_skills_wrapper');
                skillsWrapper.innerHTML = '';
                if (skillsVal.trim() !== '') {
                    skillsVal.split(',').forEach(s => {
                        if (s.trim() !== '') {
                            skillsWrapper.innerHTML += '<span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2.5 py-1 rounded-pill me-1 mb-1">' + s.trim() + '</span>';
                        }
                    });
                } else {
                    skillsWrapper.innerHTML = '<span class="text-muted small">No key skills specified</span>';
                }

                const certVal = (this.getAttribute('data-certificate') || '').trim();
                const certWrapper = document.getElementById('view_certificate_wrapper');
                if (certVal !== '') {
                    certWrapper.innerHTML = '<a href="../../uploads/certificates/' + certVal + '" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1 fw-semibold"><i class="bi bi-file-earmark-pdf-fill me-1"></i>View / Download Certificate (' + certVal + ')</a>';
                } else {
                    certWrapper.innerHTML = '<span class="badge bg-light text-muted border px-2.5 py-1 rounded-pill">No Certificate Document Uploaded</span>';
                }

                viewModal.show();
            });
        });

        editButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const studentName = this.getAttribute('data-name') || '';
                const studentId = this.getAttribute('data-student-id') || '';
                const profileVal = (this.getAttribute('data-profile') || '').trim();

                document.getElementById('edit_profile_header_name').textContent = studentName;
                document.getElementById('edit_profile_header_id').textContent = 'ID: ' + studentId;

                const avatarWrapper = document.getElementById('edit_profile_avatar_wrapper');
                if (profileVal !== '') {
                    avatarWrapper.innerHTML = '<img src="../../uploads/profiles/' + profileVal + '" alt="Profile" class="rounded-circle border object-fit-cover shadow-sm" style="width: 56px; height: 56px;">';
                } else {
                    const nameParts = studentName.trim().split(' ');
                    let initials = nameParts[0] ? nameParts[0].charAt(0).toUpperCase() : '';
                    if (nameParts.length > 1) initials += nameParts[nameParts.length - 1].charAt(0).toUpperCase();
                    avatarWrapper.innerHTML = '<div class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px; background: linear-gradient(135deg, #0d7298, #0f172a); font-size: 18px;">' + (initials || 'GD') + '</div>';
                }

                document.getElementById('edit_id').value = this.getAttribute('data-id');
                document.getElementById('edit_student_name').value = studentName;
                document.getElementById('edit_phone_number').value = this.getAttribute('data-phone');
                document.getElementById('edit_email_id').value = this.getAttribute('data-email');
                document.getElementById('edit_course_applied').value = this.getAttribute('data-course');
                document.getElementById('edit_start_date').value = this.getAttribute('data-start');
                document.getElementById('edit_end_date').value = this.getAttribute('data-end');
                document.getElementById('edit_key_skills').value = this.getAttribute('data-skills');

                const certVal = (this.getAttribute('data-certificate') || '').trim();
                const certPreview = document.getElementById('edit_certificate_preview');
                if (certVal !== '') {
                    certPreview.innerHTML = '<span class="badge bg-success-subtle text-success border px-2.5 py-1 rounded-pill me-2"><i class="bi bi-check-circle me-1"></i>Current: ' + certVal + '</span> <a href="../../uploads/certificates/' + certVal + '" target="_blank" class="small text-primary text-decoration-none fw-bold"><i class="bi bi-eye-fill me-1"></i>View Current Certificate</a>';
                } else {
                    certPreview.innerHTML = '<span class="text-muted small">No certificate document uploaded yet.</span>';
                }

                const collegeVal = this.getAttribute('data-college') || '';
                document.getElementById('edit_college').value = collegeVal;
                document.getElementById('edit_has_college').checked = collegeVal.trim() !== '';
                toggleOptionalField('edit_', 'college');
                if (collegeVal.trim() !== '') {
                    document.getElementById('edit_college').value = collegeVal;
                }

                const internshipVal = this.getAttribute('data-internship') || '';
                document.getElementById('edit_internship').value = internshipVal;
                document.getElementById('edit_has_internship').checked = internshipVal.trim() !== '';
                toggleOptionalField('edit_', 'internship');
                if (internshipVal.trim() !== '') {
                    document.getElementById('edit_internship').value = internshipVal;
                }

                editModal.show();
            });
        });
    });
    </script>
</body>
</html>