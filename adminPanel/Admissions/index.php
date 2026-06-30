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
$admin_name = $_SESSION['first_name'] ?? 'Admin';

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

    if (empty($student_name) || empty($college) || empty($phone_number) || empty($email_id) || empty($course_applied) || empty($internship) || empty($start_date) || empty($end_date) || empty($key_skills)) {
        $_SESSION['message'] = "All fields are required.";
        $_SESSION['message_type'] = "danger";
    } else {
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
        $insert_query = "INSERT INTO student_admissions (student_id, student_name, college, phone_number, email_id, course_applied, internship, start_date, end_date, key_skills) 
                         VALUES ('$student_id', '$student_name', '$college', '$phone_number', '$email_id', '$course_applied', '$internship', '$start_date', '$end_date', '$key_skills')";
        
        if (mysqli_query($conn, $insert_query)) {
            mysqli_commit($conn);
            $_SESSION['message'] = "Student admitted successfully! Generated ID: " . $student_id;
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

    if (empty($student_name) || empty($college) || empty($phone_number) || empty($email_id) || empty($course_applied) || empty($internship) || empty($start_date) || empty($end_date) || empty($key_skills)) {
        $_SESSION['message'] = "All fields are required.";
        $_SESSION['message_type'] = "danger";
    } else {
        $update_query = "UPDATE student_admissions SET 
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
    // Fails silently, allowing the rest of the HTML table to load
    $courses_query = false; 
}

?>


<!DOCTYPE html>
<!-- GDEDU ADMISSIONS VERSION 2 -->
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Admission - GD Edu Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .table-bordered th, .table-bordered td {
            border: 1px solid #dee2e6 !important;
        }
        thead.table-light th {
            background-color: #f1f5f9 !important;
            border-bottom: 2px solid #cbd5e1 !important;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
          <!-- Sidebar -->
            <div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 sidebar">
                <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 min-vh-100">
                    <a href="#" class="d-flex align-items-center pb-3 mb-md-1 mt-md-3 me-md-auto text-decoration-none">
                        <span class="fs-5 fw-bolder d-flex align-items-center">
                            <img height="35px" src="../images/edutechLogo.png">
                            &nbsp; GD Edu Tech
                        </span>
                    </a>

                    <ul class="nav nav-pills flex-column w-100">

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
                            <a href="../Admissions/" class="nav-link active">
                                <i class="bi bi-person-plus me-2"></i> Student Admission
                            </a>
                        </li>

                        <li class="w-100">
                            <a href="../Applications/" class="nav-link">
                                <i class="bi bi-journal-text me-2"></i> Applications
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Events/" class="nav-link">
                                <i class="bi bi-calendar2-event me-2"></i> Events
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
            <div class="col py-3" style="min-width: 0;">
                <div class="container-fluid">
                    <!-- Header -->
                    <div class="row mb-4">
                        <div class="col">
                            <h2>Student Admission</h2>
                            <p class="text-muted">Manage student admissions and generate unique certification IDs</p>
                        </div>
                        <div class="col-auto">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAdmissionModal">
                                <i class="bi bi-plus-circle me-2"></i>New Admission
                            </button>
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

                    <!-- Admissions Table -->
                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover mb-0" style="font-size: 0.85rem;">
                                    <thead class="table-light text-nowrap">
                                        <tr>
                                            <th class="py-2 px-2 fw-bold">Student ID</th>
                                            <th class="py-2 px-2 fw-bold text-center">QR Code</th>
                                            <th class="py-2 px-2 fw-bold">Name</th>
                                            <th class="py-2 px-2 fw-bold">College</th>
                                            <th class="py-2 px-2 fw-bold">Phone Number</th>
                                            <th class="py-2 px-2 fw-bold">Email</th>
                                            <th class="py-2 px-2 fw-bold">Course Applied</th>
                                            <th class="py-2 px-2 fw-bold">Internship</th>
                                            <th class="py-2 px-2 fw-bold">Start Date</th>
                                            <th class="py-2 px-2 fw-bold">End Date</th>
                                            <th class="py-2 px-2 fw-bold">Key Skills</th>
                                            <th class="py-2 px-2 fw-bold">Date Admitted</th>
                                            <th class="py-2 px-2 fw-bold text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (mysqli_num_rows($result) > 0): ?>
                                            <?php while ($admission = mysqli_fetch_assoc($result)): ?>
                                                <tr class="align-middle">
                                                    <td class="px-2 fw-bold text-primary"><?php echo htmlspecialchars($admission['student_id']); ?></td>
                                                    <td class="px-2 text-center">
                                                        <?php 
                                                        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? '') == 443) ? "https://" : "http://";
                                                        $domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
                                                        $path = (strpos($domain, 'gdedutech.com') !== false) ? "/verify_certificate.php" : "/gdedutechdemo/verify_certificate.php";
                                                        $verify_url = $protocol . $domain . $path . "?student_id=" . $admission['student_id'];
                                                        $qr_api_url = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($verify_url);
                                                        ?>
                                                        <div class="d-flex flex-column align-items-center gap-1">
                                                            <a href="<?php echo $verify_url; ?>" target="_blank" title="Verify Certificate (Opens in new tab)">
                                                                <img src="<?php echo $qr_api_url; ?>" alt="QR Code" style="width: 35px; height: 35px; border: 1px solid #dee2e6; border-radius: 4px; padding: 2px; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.15)';" onmouseout="this.style.transform='scale(1)';">
                                                            </a>
                                                            <a href="download_qr.php?student_id=<?php echo urlencode($admission['student_id']); ?>" class="btn btn-sm btn-light py-0 px-1 border" style="font-size: 0.6rem;" title="Download QR Code">
                                                                <i class="bi bi-download"></i> Download
                                                            </a>
                                                        </div>
                                                    </td>
                                                    <td class="px-2"><?php echo htmlspecialchars($admission['student_name']); ?></td>
                                                    <td class="px-2" title="<?php echo htmlspecialchars($admission['college']); ?>">
                                                        <div class="text-truncate" style="max-width: 120px;"><?php echo htmlspecialchars($admission['college']); ?></div>
                                                    </td>
                                                    <td class="px-2 text-nowrap"><?php echo htmlspecialchars($admission['phone_number']); ?></td>
                                                    <td class="px-2" title="<?php echo htmlspecialchars($admission['email_id']); ?>">
                                                        <div class="text-truncate" style="max-width: 100px;"><?php echo htmlspecialchars($admission['email_id']); ?></div>
                                                    </td>
                                                    <td class="px-2" title="<?php echo htmlspecialchars($admission['course_applied']); ?>">
                                                        <div class="text-truncate" style="max-width: 110px;"><?php echo htmlspecialchars($admission['course_applied']); ?></div>
                                                    </td>
                                                    <td class="px-2"><span class="badge bg-secondary" style="font-size: 0.75rem;"><?php echo htmlspecialchars($admission['internship']); ?></span></td>
                                                    <td class="px-2 text-nowrap" style="font-size: 0.8rem;"><?php echo $admission['start_date'] ? date('d M Y', strtotime($admission['start_date'])) : '-'; ?></td>
                                                    <td class="px-2 text-nowrap" style="font-size: 0.8rem;"><?php echo $admission['end_date'] ? date('d M Y', strtotime($admission['end_date'])) : '-'; ?></td>
                                                    <td class="px-2" title="<?php echo htmlspecialchars($admission['key_skills']); ?>">
                                                        <div class="text-truncate" style="max-width: 100px;"><?php echo htmlspecialchars($admission['key_skills'] ?? '-'); ?></div>
                                                    </td>
                                                    <td class="px-2 text-nowrap" style="font-size: 0.8rem;"><?php echo date('d M Y', strtotime($admission['created_at'])); ?></td>
                                                    <td class="px-4 text-center text-nowrap">
                                                        <a href="javascript:void(0)" class="text-primary action-icon me-2 edit-btn" 
                                                           data-id="<?php echo $admission['id']; ?>"
                                                           data-name="<?php echo htmlspecialchars($admission['student_name']); ?>"
                                                           data-college="<?php echo htmlspecialchars($admission['college']); ?>"
                                                           data-phone="<?php echo htmlspecialchars($admission['phone_number']); ?>"
                                                           data-email="<?php echo htmlspecialchars($admission['email_id']); ?>"
                                                           data-course="<?php echo htmlspecialchars($admission['course_applied']); ?>"
                                                           data-internship="<?php echo htmlspecialchars($admission['internship']); ?>"
                                                           data-start="<?php echo htmlspecialchars($admission['start_date']); ?>"
                                                           data-end="<?php echo htmlspecialchars($admission['end_date']); ?>"
                                                           data-skills="<?php echo htmlspecialchars($admission['key_skills']); ?>">
                                                            <i class="bi bi-pencil-square text-primary"></i>
                                                        </a>
                                                        <a href="index.php?delete=1&id=<?php echo $admission['id']; ?>" class="text-danger action-icon" onclick="return confirm('Are you sure you want to delete this admission record?')">
                                                            <i class="bi bi-trash text-danger"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="13" class="text-center py-4 text-muted">No admission records found.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav class="mt-4">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="index.php?page=<?php echo $page - 1; ?>">Previous</a>
                                </li>
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                        <a class="page-link" href="index.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="index.php?page=<?php echo $page + 1; ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>

    <!-- Add Admission Modal -->
    <div class="modal fade" id="addAdmissionModal" tabindex="-1" aria-labelledby="addAdmissionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="index.php" method="POST">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="addAdmissionModalLabel">New Student Admission</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="student_name" class="form-label">Student Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="student_name" name="student_name" required placeholder="Enter student full name">
                        </div>
                        <div class="mb-3">
                            <label for="college" class="form-label">College <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="college" name="college" required placeholder="Enter college name">
                        </div>
                        <div class="mb-3">
                            <label for="phone_number" class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="phone_number" name="phone_number" required placeholder="Enter phone number">
                        </div>
                        <div class="mb-3">
                            <label for="email_id" class="form-label">Email ID <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email_id" name="email_id" required placeholder="Enter email address">
                        </div>
                        <div class="mb-3">
                            <label for="course_applied" class="form-label">Course Applied <span class="text-danger">*</span></label>
                            <select class="form-select" id="course_applied" name="course_applied" required>
                                <option value="" disabled selected>Select a course</option>
                                <option value="Full Stack Development">Full Stack Development</option>
                                <option value="Architectural Design course">Architectural Design course</option>
                                <option value="Interior Design course">Interior Design course</option>
                                <option value="Digital Marketing">Digital Marketing</option>
                                <option value="Graphic Design & Video Editing">Graphic Design & Video Editing</option>
                                <option value="Photography & Camera Handling">Photography & Camera Handling</option>
                                <?php 
                                $listed_courses = [
                                    "Full Stack Development",
                                    "Architectural Design course",
                                    "Interior Design course",
                                    "Digital Marketing",
                                    "Graphic Design & Video Editing",
                                    "Photography & Camera Handling"
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
                        <div class="mb-3">
                            <label for="internship" class="form-label">Internship <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="internship" name="internship" required placeholder="e.g. Yes (3 Months), No, Completed">
                        </div>
                        <div class="mb-3">
                            <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="start_date" name="start_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="end_date" name="end_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="key_skills" class="form-label">Key Software/Skills <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="key_skills" name="key_skills" required placeholder="e.g. HTML, CSS, JS, Photoshop, Figma">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_admission" class="btn btn-primary">Submit & Admit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Admission Modal -->
    <div class="modal fade" id="editAdmissionModal" tabindex="-1" aria-labelledby="editAdmissionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="index.php" method="POST">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="editAdmissionModalLabel">Edit Student Admission</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_student_name" class="form-label">Student Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_student_name" name="student_name" required placeholder="Enter student full name">
                        </div>
                        <div class="mb-3">
                            <label for="edit_college" class="form-label">College <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_college" name="college" required placeholder="Enter college name">
                        </div>
                        <div class="mb-3">
                            <label for="edit_phone_number" class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="edit_phone_number" name="phone_number" required placeholder="Enter phone number">
                        </div>
                        <div class="mb-3">
                            <label for="edit_email_id" class="form-label">Email ID <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="edit_email_id" name="email_id" required placeholder="Enter email address">
                        </div>
                        <div class="mb-3">
                            <label for="edit_course_applied" class="form-label">Course Applied <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_course_applied" name="course_applied" required>
                                <option value="" disabled selected>Select a course</option>
                                <option value="Full Stack Development">Full Stack Development</option>
                                <option value="Architectural Design course">Architectural Design course</option>
                                <option value="Interior Design course">Interior Design course</option>
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
                        <div class="mb-3">
                            <label for="edit_internship" class="form-label">Internship <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_internship" name="internship" required placeholder="e.g. Yes (3 Months), No, Completed">
                        </div>
                        <div class="mb-3">
                            <label for="edit_start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="edit_start_date" name="start_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="edit_end_date" name="end_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_key_skills" class="form-label">Key Software/Skills <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_key_skills" name="key_skills" required placeholder="e.g. HTML, CSS, JS, Photoshop, Figma">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="edit_admission" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const editButtons = document.querySelectorAll('.edit-btn');
        const editModal = new bootstrap.Modal(document.getElementById('editAdmissionModal'));
        
        editButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('edit_id').value = this.getAttribute('data-id');
                document.getElementById('edit_student_name').value = this.getAttribute('data-name');
                document.getElementById('edit_college').value = this.getAttribute('data-college');
                document.getElementById('edit_phone_number').value = this.getAttribute('data-phone');
                document.getElementById('edit_email_id').value = this.getAttribute('data-email');
                document.getElementById('edit_course_applied').value = this.getAttribute('data-course');
                document.getElementById('edit_internship').value = this.getAttribute('data-internship');
                document.getElementById('edit_start_date').value = this.getAttribute('data-start');
                document.getElementById('edit_end_date').value = this.getAttribute('data-end');
                document.getElementById('edit_key_skills').value = this.getAttribute('data-skills');
                
                editModal.show();
            });
        });
    });
    </script>
</body>

</html>
