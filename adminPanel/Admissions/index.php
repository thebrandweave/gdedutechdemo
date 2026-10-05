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
require_once __DIR__ . '/../../Configurations/config.php';

// Auto-migration: Ensure table student_admission_courses and certificate_file exist
try {
    // 1. Ensure student_admission_courses table exists
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS student_admission_courses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        admission_id INT NULL,
        student_id VARCHAR(20) NOT NULL,
        course_name VARCHAR(255) NOT NULL,
        start_date DATE NULL,
        end_date DATE NULL,
        internship VARCHAR(255) NULL,
        key_skills VARCHAR(255) NULL,
        certificate_file VARCHAR(255) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX (student_id),
        INDEX (admission_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 2. Ensure certificate_file column in student_admissions exists
    $col_check = mysqli_query($conn, "SHOW COLUMNS FROM student_admissions LIKE 'certificate_file'");
    if ($col_check && mysqli_num_rows($col_check) === 0) {
        @mysqli_query($conn, "ALTER TABLE student_admissions ADD COLUMN certificate_file VARCHAR(255) NULL AFTER profile_image");
    }

    // 3. Auto-migrate legacy rows from student_admissions to student_admission_courses if not yet migrated
    $mig_sql = "INSERT INTO student_admission_courses (admission_id, student_id, course_name, start_date, end_date, internship, key_skills, certificate_file, created_at)
        SELECT sa.id, sa.student_id, sa.course_applied, sa.start_date, sa.end_date, sa.internship, sa.key_skills, sa.certificate_file, sa.created_at
        FROM student_admissions sa
        LEFT JOIN student_admission_courses sac ON sa.student_id = sac.student_id
        WHERE sac.id IS NULL AND sa.course_applied IS NOT NULL AND sa.course_applied != ''";
    @mysqli_query($conn, $mig_sql);
} catch (Throwable $e) {
    // Fail silently
}

// Helper: Available Course Titles
$listed_courses = [
    "Full Stack Development",
    "Architectural Design",
    "Interior Design",
    "Digital Marketing",
    "Graphic Design & Video Editing",
    "Graphic Design",
    "Visual Media Program",
    "Tally & GST",
    "Advanced Excel",
    "Photography & Camera Handling"
];
try {
    $courses_query = mysqli_query($conn, "SELECT course_id, title FROM Courses ORDER BY title ASC");
    if ($courses_query && mysqli_num_rows($courses_query) > 0) {
        while ($c = mysqli_fetch_assoc($courses_query)) {
            if (!in_array($c['title'], $listed_courses)) {
                $listed_courses[] = $c['title'];
            }
        }
    }
} catch (Throwable $e) {}

// Get admin details from session
$admin_name = $_SESSION['username'] ?? 'Admin';

// ==========================================
// HANDLE ACTIONS
// ==========================================

// Handle Delete Course (Specific Course under a Student ID)
if (isset($_GET['delete_course']) && isset($_GET['course_id'])) {
    $course_id = intval($_GET['course_id']);
    $res = mysqli_query($conn, "SELECT certificate_file, student_id FROM student_admission_courses WHERE id = $course_id");
    if ($res && $row = mysqli_fetch_assoc($res)) {
        if (!empty($row['certificate_file'])) {
            $fpath = rtrim(__DIR__, '/\\') . '/../../uploads/certificates/' . $row['certificate_file'];
            if (file_exists($fpath)) @unlink($fpath);
        }
        $sid = mysqli_real_escape_string($conn, $row['student_id']);
        mysqli_query($conn, "DELETE FROM student_admission_courses WHERE id = $course_id");

        // Sync summary in student_admissions
        $rem_res = mysqli_query($conn, "SELECT course_name FROM student_admission_courses WHERE student_id = '$sid' ORDER BY id ASC");
        $rem_names = [];
        while ($rr = mysqli_fetch_assoc($rem_res)) {
            $rem_names[] = $rr['course_name'];
        }
        $summary = mysqli_real_escape_string($conn, implode(', ', $rem_names));
        mysqli_query($conn, "UPDATE student_admissions SET course_applied = '$summary', updated_at = NOW() WHERE student_id = '$sid'");

        $_SESSION['message'] = "Course deleted successfully.";
        $_SESSION['message_type'] = "success";
    }
    header("Location: index.php");
    exit();
}

// Handle Delete Entire Admission Record
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stud = mysqli_query($conn, "SELECT student_id, profile_image FROM student_admissions WHERE id = $id");
    if ($stud && $s_row = mysqli_fetch_assoc($stud)) {
        $sid = mysqli_real_escape_string($conn, $s_row['student_id']);
        if (!empty($s_row['profile_image'])) {
            $img_path = rtrim(__DIR__, '/\\') . '/../../uploads/profiles/' . $s_row['profile_image'];
            if (file_exists($img_path)) @unlink($img_path);
        }
        // Delete all certificate files for this student
        $c_res = mysqli_query($conn, "SELECT certificate_file FROM student_admission_courses WHERE student_id = '$sid'");
        if ($c_res) {
            while ($c_row = mysqli_fetch_assoc($c_res)) {
                if (!empty($c_row['certificate_file'])) {
                    $c_path = rtrim(__DIR__, '/\\') . '/../../uploads/certificates/' . $c_row['certificate_file'];
                    if (file_exists($c_path)) @unlink($c_path);
                }
            }
        }
        mysqli_query($conn, "DELETE FROM student_admission_courses WHERE student_id = '$sid'");
        mysqli_query($conn, "DELETE FROM student_admissions WHERE id = $id");

        $_SESSION['message'] = "Admission record and all associated courses deleted successfully.";
        $_SESSION['message_type'] = "success";
    }
    header("Location: index.php");
    exit();
}

// Helper: Process single certificate upload
function uploadCertificateFile($file_post) {
    if (isset($file_post) && $file_post['error'] === UPLOAD_ERR_OK) {
        $cert_upload_dir = rtrim(__DIR__, '/\\') . '/../../uploads/certificates/';
        if (!is_dir($cert_upload_dir)) {
            mkdir($cert_upload_dir, 0777, true);
        }
        $cert_ext = strtolower(pathinfo($file_post['name'], PATHINFO_EXTENSION));
        if (in_array($cert_ext, ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $new_cert_name = 'cert_' . time() . '_' . rand(1000, 9999) . '.' . $cert_ext;
            $cert_destination = $cert_upload_dir . $new_cert_name;
            if (move_uploaded_file($file_post['tmp_name'], $cert_destination)) {
                return $new_cert_name;
            }
        }
    }
    return NULL;
}

// Handle Add Admission (Supports 1 or more courses with separate certificates)
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['add_admission'])) {
    $student_name = mysqli_real_escape_string($conn, trim($_POST['student_name'] ?? ''));
    $college = mysqli_real_escape_string($conn, trim($_POST['college'] ?? ''));
    $phone_number = mysqli_real_escape_string($conn, trim($_POST['phone_number'] ?? ''));
    $email_id = mysqli_real_escape_string($conn, trim($_POST['email_id'] ?? ''));

    // Collect courses array
    $courses_input = [];
    if (isset($_POST['courses']) && is_array($_POST['courses'])) {
        $courses_input = $_POST['courses'];
    } elseif (!empty($_POST['course_applied'])) {
        // Fallback for legacy single-course form submission
        $courses_input[] = [
            'course_applied' => $_POST['course_applied'],
            'start_date' => $_POST['start_date'] ?? '',
            'end_date' => $_POST['end_date'] ?? '',
            'internship' => $_POST['internship'] ?? '',
            'key_skills' => $_POST['key_skills'] ?? ''
        ];
    }

    if (empty($student_name) || empty($phone_number) || empty($email_id) || empty($courses_input)) {
        $_SESSION['message'] = "Please provide all required student details and at least one course.";
        $_SESSION['message_type'] = "danger";
    } else {
        // Handle Student Profile Photo
        $profile_image = NULL;
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = rtrim(__DIR__, '/\\') . '/../../uploads/profiles/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $file_ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
            if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $new_filename = 'profile_' . time() . '_' . rand(1000, 9999) . '.' . $file_ext;
                $destination = $upload_dir . $new_filename;
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $destination)) {
                    $profile_image = $new_filename;
                }
            }
        }

        // Begin transaction to ensure safe ID generation and multi-course insertion
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

        // Extract primary course data for legacy columns
        $course_names = [];
        $first_cert = NULL;
        $first_start = NULL;
        $first_end = NULL;
        $first_internship = NULL;
        $first_skills = NULL;

        foreach ($courses_input as $idx => $cdata) {
            $c_name = trim($cdata['course_applied'] ?? '');
            if (!empty($c_name)) {
                $course_names[] = $c_name;
            }
        }
        $course_summary = mysqli_real_escape_string($conn, implode(', ', $course_names));

        // Insert primary record into student_admissions
        $insert_query = "INSERT INTO student_admissions (student_id, profile_image, certificate_file, student_name, college, phone_number, email_id, course_applied, internship, start_date, end_date, key_skills) 
                         VALUES ('$student_id', " . ($profile_image ? "'$profile_image'" : "NULL") . ", NULL, '$student_name', '$college', '$phone_number', '$email_id', '$course_summary', '', NULL, NULL, '')";

        if (mysqli_query($conn, $insert_query)) {
            $admission_id = mysqli_insert_id($conn);
            $added_courses_count = 0;

            // Loop and insert each course with its own separate certificate file
            foreach ($courses_input as $idx => $cdata) {
                $c_name = mysqli_real_escape_string($conn, trim($cdata['course_applied'] ?? ''));
                if (empty($c_name)) continue;

                $c_start = mysqli_real_escape_string($conn, trim($cdata['start_date'] ?? ''));
                $c_end = mysqli_real_escape_string($conn, trim($cdata['end_date'] ?? ''));
                $c_intern = mysqli_real_escape_string($conn, trim($cdata['internship'] ?? ''));
                $c_skills = mysqli_real_escape_string($conn, trim($cdata['key_skills'] ?? ''));

                // Handle certificate upload for this course
                $c_cert_file = NULL;
                $file_key = "course_certificate_" . $idx;
                if (isset($_FILES[$file_key])) {
                    $c_cert_file = uploadCertificateFile($_FILES[$file_key]);
                }
                // Fallback check for single legacy certificate file input
                if (!$c_cert_file && $idx === 0 && isset($_FILES['certificate_file'])) {
                    $c_cert_file = uploadCertificateFile($_FILES['certificate_file']);
                }

                if ($idx === 0) {
                    $first_cert = $c_cert_file;
                    $first_start = $c_start;
                    $first_end = $c_end;
                    $first_internship = $c_intern;
                    $first_skills = $c_skills;
                }

                $cert_val = $c_cert_file ? "'$c_cert_file'" : "NULL";
                $start_val = !empty($c_start) ? "'$c_start'" : "NULL";
                $end_val = !empty($c_end) ? "'$c_end'" : "NULL";

                $insert_c_sql = "INSERT INTO student_admission_courses (admission_id, student_id, course_name, start_date, end_date, internship, key_skills, certificate_file)
                                 VALUES ($admission_id, '$student_id', '$c_name', $start_val, $end_val, '$c_intern', '$c_skills', $cert_val)";
                mysqli_query($conn, $insert_c_sql);
                $added_courses_count++;
            }

            // Sync legacy fallback columns with first course
            if ($first_start !== NULL) {
                $sync_sql = "UPDATE student_admissions SET 
                             start_date = " . (!empty($first_start) ? "'$first_start'" : "NULL") . ", 
                             end_date = " . (!empty($first_end) ? "'$first_end'" : "NULL") . ", 
                             internship = '$first_internship', 
                             key_skills = '$first_skills', 
                             certificate_file = " . ($first_cert ? "'$first_cert'" : "NULL") . " 
                             WHERE id = $admission_id";
                mysqli_query($conn, $sync_sql);
            }

            mysqli_commit($conn);
            $_SESSION['message'] = "Student admitted successfully! ID: $student_id with $added_courses_count course(s).";
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

// Handle Add Course to Existing Student ID
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['add_course_to_student'])) {
    $student_id = mysqli_real_escape_string($conn, trim($_POST['student_id'] ?? ''));
    $course_applied = mysqli_real_escape_string($conn, trim($_POST['course_applied'] ?? ''));
    $start_date = mysqli_real_escape_string($conn, trim($_POST['start_date'] ?? ''));
    $end_date = mysqli_real_escape_string($conn, trim($_POST['end_date'] ?? ''));
    $internship = mysqli_real_escape_string($conn, trim($_POST['internship'] ?? ''));
    $key_skills = mysqli_real_escape_string($conn, trim($_POST['key_skills'] ?? ''));

    // Check student
    $check = mysqli_query($conn, "SELECT id, student_name FROM student_admissions WHERE student_id = '$student_id'");
    if (!$check || mysqli_num_rows($check) === 0) {
        $_SESSION['message'] = "Invalid Student ID: $student_id";
        $_SESSION['message_type'] = "danger";
    } elseif (empty($course_applied) || empty($start_date) || empty($end_date) || empty($key_skills)) {
        $_SESSION['message'] = "All required course fields must be filled.";
        $_SESSION['message_type'] = "danger";
    } else {
        $adm_row = mysqli_fetch_assoc($check);
        $adm_id = $adm_row['id'];

        // Handle separate certificate upload for this course
        $certificate_file = NULL;
        if (isset($_FILES['certificate_file'])) {
            $certificate_file = uploadCertificateFile($_FILES['certificate_file']);
        }

        $cert_val = $certificate_file ? "'$certificate_file'" : "NULL";
        $start_val = !empty($start_date) ? "'$start_date'" : "NULL";
        $end_val = !empty($end_date) ? "'$end_date'" : "NULL";

        $insert_c = "INSERT INTO student_admission_courses (admission_id, student_id, course_name, start_date, end_date, internship, key_skills, certificate_file)
                     VALUES ($adm_id, '$student_id', '$course_applied', $start_val, $end_val, '$internship', '$key_skills', $cert_val)";

        if (mysqli_query($conn, $insert_c)) {
            // Update summary in student_admissions
            $all_c = mysqli_query($conn, "SELECT course_name FROM student_admission_courses WHERE student_id = '$student_id' ORDER BY id ASC");
            $names = [];
            while ($cr = mysqli_fetch_assoc($all_c)) {
                $names[] = $cr['course_name'];
            }
            $names_str = mysqli_real_escape_string($conn, implode(', ', $names));
            mysqli_query($conn, "UPDATE student_admissions SET course_applied = '$names_str', updated_at = NOW() WHERE student_id = '$student_id'");

            $_SESSION['message'] = "Course '$course_applied' successfully added to Student ID: $student_id with separate certificate!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error adding course: " . mysqli_error($conn);
            $_SESSION['message_type'] = "danger";
        }

        header("Location: index.php");
        exit();
    }
}

// Handle Edit Admission (Supports editing student details and all courses & certificates)
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['edit_admission'])) {
    $id = intval($_POST['id']);
    $student_name = mysqli_real_escape_string($conn, trim($_POST['student_name']));
    $college = mysqli_real_escape_string($conn, trim($_POST['college']));
    $phone_number = mysqli_real_escape_string($conn, trim($_POST['phone_number']));
    $email_id = mysqli_real_escape_string($conn, trim($_POST['email_id']));

    // Fetch existing student_id
    $s_check = mysqli_query($conn, "SELECT student_id FROM student_admissions WHERE id = $id");
    if (!$s_check || mysqli_num_rows($s_check) === 0) {
        $_SESSION['message'] = "Student admission record not found.";
        $_SESSION['message_type'] = "danger";
        header("Location: index.php");
        exit();
    }
    $s_row = mysqli_fetch_assoc($s_check);
    $student_id = $s_row['student_id'];

    if (empty($student_name) || empty($phone_number) || empty($email_id)) {
        $_SESSION['message'] = "Required student fields are missing.";
        $_SESSION['message_type'] = "danger";
    } else {
        // Handle Image Upload for Edit
        $image_update_query = "";
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = rtrim(__DIR__, '/\\') . '/../../uploads/profiles/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

            $file_ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
            if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $new_filename = 'profile_' . time() . '_' . rand(1000, 9999) . '.' . $file_ext;
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_dir . $new_filename)) {
                    $image_update_query = "profile_image = '$new_filename', ";
                }
            }
        }

        // Update Student Personal Info
        $update_query = "UPDATE student_admissions SET 
                         $image_update_query
                         student_name = '$student_name', 
                         college = '$college', 
                         phone_number = '$phone_number', 
                         email_id = '$email_id',
                         updated_at = NOW()
                         WHERE id = $id";
        mysqli_query($conn, $update_query);

        // Process Existing Courses Updates
        if (isset($_POST['existing_courses']) && is_array($_POST['existing_courses'])) {
            foreach ($_POST['existing_courses'] as $course_id => $c_data) {
                $cid = intval($course_id);
                $c_name = mysqli_real_escape_string($conn, trim($c_data['course_name'] ?? ''));
                $c_start = mysqli_real_escape_string($conn, trim($c_data['start_date'] ?? ''));
                $c_end = mysqli_real_escape_string($conn, trim($c_data['end_date'] ?? ''));
                $c_intern = mysqli_real_escape_string($conn, trim($c_data['internship'] ?? ''));
                $c_skills = mysqli_real_escape_string($conn, trim($c_data['key_skills'] ?? ''));

                $cert_update_part = "";
                $file_key = "existing_course_cert_" . $cid;
                if (isset($_FILES[$file_key])) {
                    $new_c_cert = uploadCertificateFile($_FILES[$file_key]);
                    if ($new_c_cert) {
                        $cert_update_part = "certificate_file = '$new_c_cert', ";
                    }
                }

                $s_date_val = !empty($c_start) ? "'$c_start'" : "NULL";
                $e_date_val = !empty($c_end) ? "'$c_end'" : "NULL";

                $up_c_sql = "UPDATE student_admission_courses SET 
                             $cert_update_part
                             course_name = '$c_name',
                             start_date = $s_date_val,
                             end_date = $e_date_val,
                             internship = '$c_intern',
                             key_skills = '$c_skills'
                             WHERE id = $cid AND student_id = '$student_id'";
                mysqli_query($conn, $up_c_sql);
            }
        }

        // Process Newly Added Courses inside Edit Modal
        if (isset($_POST['new_courses']) && is_array($_POST['new_courses'])) {
            foreach ($_POST['new_courses'] as $n_idx => $nc_data) {
                $n_cname = mysqli_real_escape_string($conn, trim($nc_data['course_name'] ?? ''));
                if (empty($n_cname)) continue;

                $n_start = mysqli_real_escape_string($conn, trim($nc_data['start_date'] ?? ''));
                $n_end = mysqli_real_escape_string($conn, trim($nc_data['end_date'] ?? ''));
                $n_intern = mysqli_real_escape_string($conn, trim($nc_data['internship'] ?? ''));
                $n_skills = mysqli_real_escape_string($conn, trim($nc_data['key_skills'] ?? ''));

                $n_cert = NULL;
                $n_file_key = "new_course_cert_" . $n_idx;
                if (isset($_FILES[$n_file_key])) {
                    $n_cert = uploadCertificateFile($_FILES[$n_file_key]);
                }

                $n_cert_val = $n_cert ? "'$n_cert'" : "NULL";
                $n_sval = !empty($n_start) ? "'$n_start'" : "NULL";
                $n_eval = !empty($n_end) ? "'$n_end'" : "NULL";

                $ins_new = "INSERT INTO student_admission_courses (admission_id, student_id, course_name, start_date, end_date, internship, key_skills, certificate_file)
                            VALUES ($id, '$student_id', '$n_cname', $n_sval, $n_eval, '$n_intern', '$n_skills', $n_cert_val)";
                mysqli_query($conn, $ins_new);
            }
        }

        // Resync Course Summary in student_admissions
        $all_c = mysqli_query($conn, "SELECT course_name FROM student_admission_courses WHERE student_id = '$student_id' ORDER BY id ASC");
        $names = [];
        while ($cr = mysqli_fetch_assoc($all_c)) {
            $names[] = $cr['course_name'];
        }
        $names_str = mysqli_real_escape_string($conn, implode(', ', $names));
        mysqli_query($conn, "UPDATE student_admissions SET course_applied = '$names_str' WHERE student_id = '$student_id'");

        $_SESSION['message'] = "Admission record and courses updated successfully.";
        $_SESSION['message_type'] = "success";
        header("Location: index.php");
        exit();
    }
}

// ==========================================
// FETCH ADMISSIONS & COURSES FOR DISPLAY
// ==========================================

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

// Load records into memory and collect Student IDs
$admissions_list = [];
$student_ids_quoted = [];
while ($row = mysqli_fetch_assoc($result)) {
    $admissions_list[] = $row;
    $student_ids_quoted[] = "'" . mysqli_real_escape_string($conn, $row['student_id']) . "'";
}

// Fetch all courses for the students on current page
$courses_by_student = [];
if (!empty($student_ids_quoted)) {
    $in_ids = implode(',', $student_ids_quoted);
    $c_res = mysqli_query($conn, "SELECT * FROM student_admission_courses WHERE student_id IN ($in_ids) ORDER BY id ASC");
    if ($c_res) {
        while ($crow = mysqli_fetch_assoc($c_res)) {
            $courses_by_student[$crow['student_id']][] = $crow;
        }
    }
}

// Fetch all students for "Add Course to Existing Student" dropdown
$all_students_res = mysqli_query($conn, "SELECT student_id, student_name FROM student_admissions ORDER BY student_id DESC");
$all_students = [];
if ($all_students_res) {
    while ($s = mysqli_fetch_assoc($all_students_res)) {
        $all_students[] = $s;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Admissions & Multi-Course Certifications - GD Edu Tech Admin</title>
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
        .course-card-box {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 16px;
            position: relative;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            transition: border-color 0.2s;
        }
        .course-card-box:hover {
            border-color: #cbd5e1;
        }
        .course-badge-pill {
            font-size: 0.75rem;
            padding: 4px 8px;
            border-radius: 6px;
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
                    <li class="w-100"><a href="../Career/" class="nav-link"><i class="bi bi-briefcase me-2"></i> Careers</a></li>
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
                        <h4 class="fw-bold text-dark mb-0">Student Admissions & Multiple Courses Certification</h4>
                        <span class="text-muted small">Manage student admissions, multiple course enrollments, and separate course certificates under single Student ID</span>
                    </div>

                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <button type="button" class="btn btn-outline-primary text-nowrap fw-semibold" data-bs-toggle="modal" data-bs-target="#addCourseModal" onclick="openAddCourseGeneric()">
                            <i class="bi bi-journal-plus me-1.5"></i>Add Course to Existing Student
                        </button>
                        <button type="button" class="btn btn-primary text-nowrap fw-semibold" data-bs-toggle="modal" data-bs-target="#addAdmissionModal">
                            <i class="bi bi-person-plus-fill me-1.5"></i>New Student Admission
                        </button>
                    </div>
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
                                        <th class="text-center">Certificates</th>
                                        <th>Student Info</th>
                                        <th>College / Campus</th>
                                        <th>Course(s) Enrolled</th>
                                        <th>Internship</th>
                                        <th>Duration</th>
                                        <th>Key Skills</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($admissions_list)): ?>
                                        <?php foreach ($admissions_list as $admission): 
                                            $s_id = $admission['student_id'];
                                            $courses = $courses_by_student[$s_id] ?? [];
                                            
                                            // Fallback if courses table has no entries for this student
                                            if (empty($courses) && !empty($admission['course_applied'])) {
                                                $courses[] = [
                                                    'id' => 0,
                                                    'student_id' => $s_id,
                                                    'course_name' => $admission['course_applied'],
                                                    'start_date' => $admission['start_date'],
                                                    'end_date' => $admission['end_date'],
                                                    'internship' => $admission['internship'],
                                                    'key_skills' => $admission['key_skills'],
                                                    'certificate_file' => $admission['certificate_file']
                                                ];
                                            }
                                            
                                            $courses_json = htmlspecialchars(json_encode($courses), ENT_QUOTES, 'UTF-8');
                                        ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-primary bg-opacity-10 text-black border border-primary-subtle px-2.5 py-1 rounded-pill font-monospace fw-bold fs-6">
                                                        <?php echo htmlspecialchars($admission['student_id']); ?>
                                                    </span>
                                                    <?php if (count($courses) > 1): ?>
                                                        <span class="badge bg-secondary bg-opacity-10 text-dark border px-2 py-0.5 rounded-pill d-block mt-1 font-monospace" style="font-size: 0.65rem;">
                                                            <?php echo count($courses); ?> Courses
                                                        </span>
                                                    <?php endif; ?>
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
                                                            <img src="<?php echo $qr_api_url; ?>" alt="QR Code" class="rounded-2 border p-1 bg-white shadow-sm" style="width: 54px; height: 54px; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.2)';" onmouseout="this.style.transform='scale(1)';">
                                                        </a>
                                                        <a href="download_qr.php?student_id=<?php echo urlencode($admission['student_id']); ?>" class="btn btn-sm btn-outline-secondary py-0.5 px-3 rounded-pill" style="font-size: 0.62rem;" title="Download QR Image">
                                                            <i class="bi bi-download"></i> QR
                                                        </a>
                                                    </div>
                                                </td>

                                                <!-- Certificates Column: Display separate certificate for each course -->
                                                <td class="text-center" style="min-width: 130px;">
                                                    <div class="d-flex flex-column align-items-center gap-1.5">
                                                        <?php foreach ($courses as $c): ?>
                                                            <?php if (!empty($c['certificate_file'])): ?>
                                                                <a href="../../uploads/certificates/<?php echo htmlspecialchars($c['certificate_file']); ?>" target="_blank" class="btn btn-sm btn-outline-success py-1 px-2.5 rounded-pill font-monospace fw-semibold shadow-sm text-nowrap d-inline-flex align-items-center gap-1" style="font-size: 0.72rem;" title="View <?php echo htmlspecialchars($c['course_name']); ?> Certificate">
                                                                    <i class="bi bi-file-earmark-pdf-fill"></i>
                                                                    <span><?php echo (count($courses) > 1) ? htmlspecialchars(mb_strimwidth($c['course_name'], 0, 12, '...')) : 'Cert'; ?></span>
                                                                </a>
                                                            <?php else: ?>
                                                                <span class="badge bg-light text-muted border px-2 py-0.5 rounded-pill text-nowrap" style="font-size: 0.68rem;" title="No certificate uploaded for <?php echo htmlspecialchars($c['course_name']); ?>">
                                                                    <?php echo (count($courses) > 1) ? htmlspecialchars(mb_strimwidth($c['course_name'], 0, 10, '...')) . ': None' : 'None'; ?>
                                                                </span>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </td>

                                                <td style="max-width: 160px;">
                                                    <strong class="text-dark d-block text-truncate"><?php echo htmlspecialchars($admission['student_name']); ?></strong>
                                                    <span class="text-muted small d-block"><i class="bi bi-telephone-fill text-success me-1"></i><?php echo htmlspecialchars($admission['phone_number']); ?></span>
                                                    <span class="text-muted small d-block text-truncate"><i class="bi bi-envelope-fill text-primary me-1"></i><?php echo htmlspecialchars($admission['email_id']); ?></span>
                                                </td>

                                                <td style="max-width: 140px;">
                                                    <span class="badge bg-light text-dark border px-2 py-1 rounded-pill text-wrap">
                                                        <?php echo htmlspecialchars($admission['college'] ?: 'Independent'); ?>
                                                    </span>
                                                </td>

                                                <!-- Enrolled Courses -->
                                                <td style="min-width: 160px; max-width: 210px;">
                                                    <div class="d-flex flex-column gap-1">
                                                        <?php foreach ($courses as $c): ?>
                                                            <div class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle text-start text-wrap py-1 px-2 fw-semibold" style="font-size: 0.76rem;">
                                                                <i class="bi bi-journal-text me-1"></i><?php echo htmlspecialchars($c['course_name']); ?>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </td>

                                                <!-- Internship -->
                                                <td>
                                                    <div class="d-flex flex-column gap-1">
                                                        <?php foreach ($courses as $c): ?>
                                                            <?php if (!empty($c['internship']) && strtolower($c['internship']) !== 'none'): ?>
                                                                <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-2 py-1 rounded-pill" style="font-size: 0.7rem;">
                                                                    <i class="bi bi-briefcase me-1"></i><?php echo htmlspecialchars($c['internship']); ?>
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle px-2 py-1 rounded-pill" style="font-size: 0.7rem;">None</span>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </td>

                                                <!-- Duration -->
                                                <td class="text-muted small text-nowrap">
                                                    <div class="d-flex flex-column gap-1">
                                                        <?php foreach ($courses as $c): ?>
                                                            <div>
                                                                <?php echo !empty($c['start_date']) ? date('M d, Y', strtotime($c['start_date'])) : '-'; ?> <br>
                                                                <span class="text-secondary">to <?php echo !empty($c['end_date']) ? date('M d, Y', strtotime($c['end_date'])) : '-'; ?></span>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </td>

                                                <!-- Key Skills -->
                                                <td style="max-width: 140px;">
                                                    <div class="d-flex flex-column gap-1">
                                                        <?php foreach ($courses as $c): ?>
                                                            <span class="text-secondary small d-block text-truncate" title="<?php echo htmlspecialchars($c['key_skills'] ?? '-'); ?>">
                                                                <?php echo htmlspecialchars($c['key_skills'] ?? '-'); ?>
                                                            </span>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </td>

                                                <!-- Actions -->
                                                <td class="text-center">
                                                    <div class="d-flex justify-content-center align-items-center gap-1.5">
                                                        <!-- View Button -->
                                                        <a href="javascript:void(0)" class="action-icon view-btn text-info" 
                                                           data-id="<?php echo $admission['id']; ?>"
                                                           data-student-id="<?php echo htmlspecialchars($admission['student_id']); ?>"
                                                           data-profile="<?php echo htmlspecialchars($admission['profile_image'] ?? ''); ?>"
                                                           data-name="<?php echo htmlspecialchars($admission['student_name']); ?>"
                                                           data-college="<?php echo htmlspecialchars($admission['college']); ?>"
                                                           data-phone="<?php echo htmlspecialchars($admission['phone_number']); ?>"
                                                           data-email="<?php echo htmlspecialchars($admission['email_id']); ?>"
                                                           data-courses="<?php echo $courses_json; ?>"
                                                           data-verify-url="<?php echo htmlspecialchars($verify_url); ?>"
                                                           title="View Student & All Course Certificates">
                                                            <i class="bi bi-eye-fill text-info fs-6"></i>
                                                        </a>

                                                        <!-- Add Another Course Button -->
                                                        <a href="javascript:void(0)" class="action-icon add-course-btn text-success"
                                                           data-student-id="<?php echo htmlspecialchars($admission['student_id']); ?>"
                                                           data-student-name="<?php echo htmlspecialchars($admission['student_name']); ?>"
                                                           title="Add Another Course to this Student ID">
                                                            <i class="bi bi-journal-plus text-success fs-6"></i>
                                                        </a>

                                                        <!-- Edit Button -->
                                                        <a href="javascript:void(0)" class="action-icon edit-btn" 
                                                           data-id="<?php echo $admission['id']; ?>"
                                                           data-student-id="<?php echo htmlspecialchars($admission['student_id']); ?>"
                                                           data-profile="<?php echo htmlspecialchars($admission['profile_image'] ?? ''); ?>"
                                                           data-name="<?php echo htmlspecialchars($admission['student_name']); ?>"
                                                           data-college="<?php echo htmlspecialchars($admission['college']); ?>"
                                                           data-phone="<?php echo htmlspecialchars($admission['phone_number']); ?>"
                                                           data-email="<?php echo htmlspecialchars($admission['email_id']); ?>"
                                                           data-courses="<?php echo $courses_json; ?>"
                                                           title="Edit Student & Courses">
                                                            <i class="bi bi-pencil-fill text-warning fs-6"></i>
                                                        </a>

                                                        <!-- Delete Button -->
                                                        <a href="index.php?delete=1&id=<?php echo $admission['id']; ?>" class="action-icon text-danger" onclick="return confirm('Are you sure you want to delete this admission record and ALL its enrolled courses?')" title="Delete Record">
                                                            <i class="bi bi-trash-fill fs-6"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="10" class="text-center py-4 text-muted">No student admission records found.</td>
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

    <!-- ============================================================== -->
    <!-- MODAL 1: ADD NEW STUDENT ADMISSION (Supports Multiple Courses) -->
    <!-- ============================================================== -->
    <div class="modal fade" id="addAdmissionModal" tabindex="-1" aria-labelledby="addAdmissionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                <form action="index.php" method="POST" enctype="multipart/form-data" id="addAdmissionForm">
                    <div class="modal-header bg-dark text-white p-4">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-person-plus-fill text-primary fs-4"></i>
                            <div>
                                <h5 class="modal-title fw-bold mb-0" id="addAdmissionModalLabel">New Student Admission</h5>
                                <span class="small text-white-50">Generates unique Student ID & admits with one or multiple courses</span>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="row g-3">
                            
                            <!-- Student Profile Information -->
                            <div class="col-12">
                                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="bi bi-person-badge-fill text-primary me-2"></i>Student Personal Information</h6>
                            </div>

                            <div class="col-md-6">
                                <label for="profile_image" class="form-label fw-semibold">Profile Photo (Optional)</label>
                                <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                            </div>

                            <div class="col-md-6">
                                <label for="student_name" class="form-label fw-semibold">Student Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="student_name" name="student_name" required placeholder="Full Student Name">
                            </div>

                            <div class="col-md-6">
                                <label for="phone_number" class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="phone_number" name="phone_number" required placeholder="e.g. +91 9876543210">
                            </div>

                            <div class="col-md-6">
                                <label for="email_id" class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email_id" name="email_id" required placeholder="student@example.com">
                            </div>

                            <div class="col-12">
                                <div class="p-3 bg-light rounded-3 border">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="has_college" onchange="toggleOptionalField('', 'college')">
                                        <label class="form-check-label fw-semibold" for="has_college">Attending College / University</label>
                                    </div>
                                    <div id="college_wrapper" class="mt-2" style="display: none;">
                                        <input type="text" class="form-control" id="college" name="college" placeholder="Enter College / Institute Name">
                                    </div>
                                </div>
                            </div>

                            <!-- Course(s) and Certificate(s) Section -->
                            <div class="col-12 mt-4">
                                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-mortarboard-fill text-primary me-2"></i>Enrolled Course(s) & Separate Certificates</h6>
                                        <span class="small text-muted">Upload a distinct certificate file for each course under the same Student ID.</span>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill fw-semibold" id="btn_add_more_course">
                                        <i class="bi bi-plus-circle-fill me-1"></i>+ Add Another Course
                                    </button>
                                </div>
                            </div>

                            <!-- Courses Container -->
                            <div class="col-12">
                                <div id="add_courses_container" class="d-flex flex-column gap-3">
                                    <!-- Course cards populated dynamically by JS -->
                                </div>
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

    <!-- ============================================================== -->
    <!-- MODAL 2: ADD COURSE TO EXISTING STUDENT ID                     -->
    <!-- ============================================================== -->
    <div class="modal fade" id="addCourseModal" tabindex="-1" aria-labelledby="addCourseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                <form action="index.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-header bg-dark text-white p-4">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-journal-plus text-success fs-4"></i>
                            <div>
                                <h5 class="modal-title fw-bold mb-0" id="addCourseModalLabel">Add Course to Student ID</h5>
                                <span class="small text-white-50">Enrolls existing student into an additional course with a separate certificate</span>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="row g-3">
                            
                            <!-- Student Selector or Fixed Display -->
                            <div class="col-12" id="existing_student_picker_wrapper">
                                <label for="add_course_student_select" class="form-label fw-semibold">Select Admitted Student <span class="text-danger">*</span></label>
                                <select class="form-select" id="add_course_student_select" name="student_id" required>
                                    <option value="" disabled selected>Choose student ID...</option>
                                    <?php foreach ($all_students as $st): ?>
                                        <option value="<?php echo htmlspecialchars($st['student_id']); ?>">
                                            <?php echo htmlspecialchars($st['student_id'] . ' - ' . $st['student_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-12" id="existing_student_banner_wrapper" style="display: none;">
                                <div class="p-3 bg-light rounded-4 border d-flex align-items-center justify-content-between">
                                    <div>
                                        <span class="text-muted small d-block">Adding new course for:</span>
                                        <h5 class="fw-bold text-dark mb-0" id="add_course_banner_name">Student Name</h5>
                                    </div>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-3 py-1.5 rounded-pill font-monospace fw-bold fs-6" id="add_course_banner_id">GDEDU1001</span>
                                </div>
                            </div>

                            <div class="col-12">
                                <label for="single_course_applied" class="form-label fw-semibold">Course Applied <span class="text-danger">*</span></label>
                                <select class="form-select" id="single_course_applied" name="course_applied" required>
                                    <option value="" disabled selected>Select course...</option>
                                    <?php foreach ($listed_courses as $cname): ?>
                                        <option value="<?php echo htmlspecialchars($cname); ?>"><?php echo htmlspecialchars($cname); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-12">
                                <label for="single_certificate_file" class="form-label fw-semibold"><i class="bi bi-file-earmark-pdf-fill text-danger me-1"></i>Certificate Document for This Course (PDF / Image - Optional)</label>
                                <input type="file" class="form-control" id="single_certificate_file" name="certificate_file" accept=".pdf,image/*">
                                <span class="text-muted small">Upload verified certificate for this specific course.</span>
                            </div>

                            <div class="col-md-6">
                                <label for="single_start_date" class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="single_start_date" name="start_date" required>
                            </div>

                            <div class="col-md-6">
                                <label for="single_end_date" class="form-label fw-semibold">End Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="single_end_date" name="end_date" required>
                            </div>

                            <div class="col-12">
                                <div class="p-3 bg-light rounded-3 border">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="single_has_internship" onchange="toggleOptionalField('single_', 'internship')">
                                        <label class="form-check-label fw-semibold" for="single_has_internship">Includes Practical Internship</label>
                                    </div>
                                    <div id="single_internship_wrapper" class="mt-2" style="display: none;">
                                        <input type="text" class="form-control" id="single_internship" name="internship" placeholder="e.g. Yes (3 Months), Live Projects">
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <label for="single_key_skills" class="form-label fw-semibold">Key Skills / Tools <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="single_key_skills" name="key_skills" required placeholder="e.g. HTML5, CSS3, React, Figma">
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer bg-light p-3 border-top">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_course_to_student" class="btn btn-success px-4 fw-bold">Add Course & Certificate</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 3: EDIT ADMISSION & MANAGE COURSES / CERTIFICATES        -->
    <!-- ============================================================== -->
    <div class="modal fade" id="editAdmissionModal" tabindex="-1" aria-labelledby="editAdmissionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                <form action="index.php" method="POST" enctype="multipart/form-data" id="editAdmissionForm">
                    <input type="hidden" name="id" id="edit_id">

                    <div class="modal-header bg-dark text-white p-4">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-pencil-square text-warning fs-4"></i>
                            <div>
                                <h5 class="modal-title fw-bold mb-0" id="editAdmissionModalLabel">Edit Student Admission & Courses</h5>
                                <span class="small text-white-50">Manage personal info, enrolled courses, and separate course certificates</span>
                            </div>
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
                                <h6 class="fw-bold text-dark border-bottom pb-2 mb-2"><i class="bi bi-person-fill text-primary me-2"></i>Personal Information</h6>
                            </div>

                            <div class="col-12">
                                <label for="edit_profile_image" class="form-label fw-semibold"><i class="bi bi-person-circle me-1 text-primary"></i>Update Profile Image (Optional)</label>
                                <input type="file" class="form-control" id="edit_profile_image" name="profile_image" accept="image/*">
                                <span class="text-muted small ms-1">Leave blank to keep existing profile photo.</span>
                            </div>

                            <div class="col-md-6">
                                <label for="edit_student_name" class="form-label fw-semibold">Student Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_student_name" name="student_name" required>
                            </div>

                            <div class="col-md-6">
                                <label for="edit_phone_number" class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="edit_phone_number" name="phone_number" required>
                            </div>

                            <div class="col-md-6">
                                <label for="edit_email_id" class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="edit_email_id" name="email_id" required>
                            </div>

                            <div class="col-md-6">
                                <div class="p-2 bg-light rounded-3 border h-100">
                                    <div class="form-check form-switch mt-1">
                                        <input class="form-check-input" type="checkbox" role="switch" id="edit_has_college" onchange="toggleOptionalField('edit_', 'college')">
                                        <label class="form-check-label fw-semibold" for="edit_has_college">Attending College</label>
                                    </div>
                                    <div id="edit_college_wrapper" class="mt-2" style="display: none;">
                                        <input type="text" class="form-control form-control-sm" id="edit_college" name="college" placeholder="College / Institute Name">
                                    </div>
                                </div>
                            </div>

                            <!-- Courses & Certificates Section -->
                            <div class="col-12 mt-4">
                                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-mortarboard-fill text-primary me-2"></i>Enrolled Courses & Certificates</h6>
                                        <span class="small text-muted">Edit details or upload/replace certificate for each course individually.</span>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill fw-semibold" id="btn_edit_add_course">
                                        <i class="bi bi-plus-circle-fill me-1"></i>+ Add New Course
                                    </button>
                                </div>
                            </div>

                            <div class="col-12">
                                <div id="edit_courses_container" class="d-flex flex-column gap-3">
                                    <!-- Populated dynamically by JS with current courses and new course slots -->
                                </div>
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

    <!-- ============================================================== -->
    <!-- MODAL 4: VIEW ADMISSION & ALL COURSES / CERTIFICATES           -->
    <!-- ============================================================== -->
    <div class="modal fade" id="viewAdmissionModal" tabindex="-1" aria-labelledby="viewAdmissionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                <div class="modal-header bg-dark text-white p-4">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-eye-fill text-info fs-4"></i>
                        <h5 class="modal-title fw-bold" id="viewAdmissionModalLabel">Student Admission & Verification Details</h5>
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
                            <i class="bi bi-box-arrow-up-right me-1"></i> Public Verification Page
                        </a>
                    </div>

                    <!-- Personal Information -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="p-3 bg-white border rounded-3 h-100">
                                <span class="text-muted small d-block"><i class="bi bi-envelope-fill text-primary me-1"></i> Email Address</span>
                                <strong class="text-dark small" id="view_email">student@example.com</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-white border rounded-3 h-100">
                                <span class="text-muted small d-block"><i class="bi bi-telephone-fill text-success me-1"></i> Phone Number</span>
                                <strong class="text-dark small" id="view_phone">+91 9876543210</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-white border rounded-3 h-100">
                                <span class="text-muted small d-block"><i class="bi bi-building-fill text-secondary me-1"></i> College / Campus</span>
                                <strong class="text-dark small" id="view_college">Independent</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Enrolled Courses & Separate Certificates Display -->
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                        <i class="bi bi-award-fill text-primary me-2"></i>Enrolled Courses & Separate Certificates
                    </h6>
                    <div id="view_courses_container" class="d-flex flex-column gap-3">
                        <!-- Populated dynamically by JS -->
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
    // Global list of courses for dropdown generation
    const AVAILABLE_COURSES = <?php echo json_encode($listed_courses); ?>;

    function buildCourseSelectOptions(selectedValue = '') {
        let html = '<option value="" disabled ' + (selectedValue === '' ? 'selected' : '') + '>Select course...</option>';
        AVAILABLE_COURSES.forEach(c => {
            const isSel = (c === selectedValue) ? 'selected' : '';
            html += `<option value="${escapeHtml(c)}" ${isSel}>${escapeHtml(c)}</option>`;
        });
        if (selectedValue && !AVAILABLE_COURSES.includes(selectedValue)) {
            html += `<option value="${escapeHtml(selectedValue)}" selected>${escapeHtml(selectedValue)}</option>`;
        }
        return html;
    }

    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function toggleOptionalField(prefix, field) {
        const checkbox = document.getElementById(prefix + 'has_' + field);
        const wrapper = document.getElementById(prefix + field + '_wrapper');
        const input = document.getElementById(prefix + field);
        if (checkbox && wrapper && input) {
            const show = checkbox.checked;
            wrapper.style.display = show ? 'block' : 'none';
            if (!show) {
                input.value = '';
            }
        }
    }

    // Dynamic Course Cards in "New Student Admission" Modal
    let addCourseCounter = 0;
    function renderAddCourseCard(index) {
        const isFirst = (index === 0);
        return `
        <div class="course-card-box position-relative" id="add_course_card_${index}">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-3 py-1.5 rounded-pill fw-bold">
                    <i class="bi bi-book-half me-1"></i> Course #${index + 1}
                </span>
                ${!isFirst ? `<button type="button" class="btn btn-sm btn-outline-danger py-1 px-2.5 rounded-pill" onclick="removeAddCourseCard(${index})"><i class="bi bi-trash-fill me-1"></i>Remove Course</button>` : ''}
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Course Applied <span class="text-danger">*</span></label>
                    <select class="form-select" name="courses[${index}][course_applied]" required>
                        ${buildCourseSelectOptions()}
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold"><i class="bi bi-file-earmark-pdf-fill text-danger me-1"></i>Certificate Document (PDF / Image)</label>
                    <input type="file" class="form-control" name="course_certificate_${index}" accept=".pdf,image/*">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="courses[${index}][start_date]" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">End Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="courses[${index}][end_date]" required>
                </div>
                <div class="col-md-6">
                    <div class="p-2.5 bg-light rounded-3 border h-100">
                        <label class="form-label small fw-semibold mb-1">Practical Internship (Optional)</label>
                        <input type="text" class="form-control form-control-sm" name="courses[${index}][internship]" placeholder="e.g. Yes (3 Months), Live Projects">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Key Skills / Tools <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="courses[${index}][key_skills]" required placeholder="e.g. HTML5, CSS3, React, Figma">
                </div>
            </div>
        </div>
        `;
    }

    function removeAddCourseCard(index) {
        const el = document.getElementById(`add_course_card_${index}`);
        if (el) el.remove();
    }

    function resetAddAdmissionCourses() {
        const container = document.getElementById('add_courses_container');
        container.innerHTML = '';
        addCourseCounter = 0;
        container.insertAdjacentHTML('beforeend', renderAddCourseCard(0));
        addCourseCounter = 1;
    }

    // Open "Add Course to Existing Student" Modal with specific student pre-selected
    function openAddCourseForStudent(studentId, studentName) {
        const selectElem = document.getElementById('add_course_student_select');
        const pickerWrapper = document.getElementById('existing_student_picker_wrapper');
        const bannerWrapper = document.getElementById('existing_student_banner_wrapper');
        const bannerName = document.getElementById('add_course_banner_name');
        const bannerId = document.getElementById('add_course_banner_id');

        if (selectElem) {
            selectElem.value = studentId;
        }
        pickerWrapper.style.display = 'none';
        bannerWrapper.style.display = 'block';
        bannerName.textContent = studentName;
        bannerId.textContent = studentId;

        const modal = new bootstrap.Modal(document.getElementById('addCourseModal'));
        modal.show();
    }

    // Open "Add Course to Existing Student" generic picker
    function openAddCourseGeneric() {
        const pickerWrapper = document.getElementById('existing_student_picker_wrapper');
        const bannerWrapper = document.getElementById('existing_student_banner_wrapper');
        const selectElem = document.getElementById('add_course_student_select');
        
        pickerWrapper.style.display = 'block';
        bannerWrapper.style.display = 'none';
        if (selectElem) selectElem.value = '';
    }

    document.addEventListener('DOMContentLoaded', function() {
        const editButtons = document.querySelectorAll('.edit-btn');
        const viewButtons = document.querySelectorAll('.view-btn');
        const addCourseRowBtns = document.querySelectorAll('.add-course-btn');
        const editModal = new bootstrap.Modal(document.getElementById('editAdmissionModal'));
        const viewModal = new bootstrap.Modal(document.getElementById('viewAdmissionModal'));

        // Reset Add Modal on open
        document.getElementById('addAdmissionModal').addEventListener('show.bs.modal', function() {
            document.getElementById('has_college').checked = false;
            toggleOptionalField('', 'college');
            resetAddAdmissionCourses();
        });

        // "+ Add Another Course" in Add Modal
        document.getElementById('btn_add_more_course').addEventListener('click', function() {
            const container = document.getElementById('add_courses_container');
            container.insertAdjacentHTML('beforeend', renderAddCourseCard(addCourseCounter));
            addCourseCounter++;
        });

        // Add course button on table rows
        addCourseRowBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const sid = this.getAttribute('data-student-id');
                const sname = this.getAttribute('data-student-name');
                openAddCourseForStudent(sid, sname);
            });
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
                    avatarWrapper.innerHTML = `<img src="../../uploads/profiles/${escapeHtml(profileVal)}" alt="Profile" class="rounded-circle border object-fit-cover shadow-sm" style="width: 56px; height: 56px;">`;
                } else {
                    const nameParts = studentName.trim().split(' ');
                    let initials = nameParts[0] ? nameParts[0].charAt(0).toUpperCase() : '';
                    if (nameParts.length > 1) initials += nameParts[nameParts.length - 1].charAt(0).toUpperCase();
                    avatarWrapper.innerHTML = `<div class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px; background: linear-gradient(135deg, #0d7298, #0f172a); font-size: 18px;">${initials || 'GD'}</div>`;
                }

                document.getElementById('view_email').textContent = this.getAttribute('data-email') || '-';
                document.getElementById('view_phone').textContent = this.getAttribute('data-phone') || '-';
                document.getElementById('view_college').textContent = this.getAttribute('data-college') || 'Independent';

                // Render Courses in View Modal
                let courses = [];
                try {
                    courses = JSON.parse(this.getAttribute('data-courses') || '[]');
                } catch(e) {
                    courses = [];
                }

                const coursesContainer = document.getElementById('view_courses_container');
                coursesContainer.innerHTML = '';

                if (courses.length > 0) {
                    courses.forEach((c, idx) => {
                        const certVal = (c.certificate_file || '').trim();
                        let certHtml = '';
                        if (certVal !== '') {
                            const isPdf = certVal.toLowerCase().endsWith('.pdf');
                            const iconClass = isPdf ? 'bi-file-earmark-pdf-fill text-danger' : 'bi-file-earmark-image-fill text-success';
                            const btnClass = isPdf ? 'btn-outline-danger' : 'btn-outline-success';
                            certHtml = `<a href="../../uploads/certificates/${escapeHtml(certVal)}" target="_blank" class="btn btn-sm ${btnClass} rounded-pill px-3 py-1 fw-semibold font-monospace">
                                <i class="bi ${iconClass} me-1"></i>View / Download Certificate (${escapeHtml(certVal)})
                            </a>`;
                        } else {
                            certHtml = `<span class="badge bg-light text-muted border px-2.5 py-1 rounded-pill">No Certificate Document Uploaded</span>`;
                        }

                        let skillsHtml = '';
                        if (c.key_skills && c.key_skills.trim() !== '') {
                            c.key_skills.split(',').forEach(s => {
                                if (s.trim()) {
                                    skillsHtml += `<span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2 py-1 rounded-pill me-1 mb-1">${escapeHtml(s.trim())}</span>`;
                                }
                            });
                        } else {
                            skillsHtml = `<span class="text-muted small">No key skills specified</span>`;
                        }

                        coursesContainer.innerHTML += `
                        <div class="course-card-box">
                            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                    <span class="badge bg-primary px-2.5 py-1 rounded-pill font-monospace">#${idx + 1}</span>
                                    <span>${escapeHtml(c.course_name)}</span>
                                </h6>
                                <div>${certHtml}</div>
                            </div>
                            <div class="row g-2 mt-2 pt-2 border-top small">
                                <div class="col-md-4">
                                    <span class="text-muted d-block"><i class="bi bi-calendar3 text-primary me-1"></i>Duration:</span>
                                    <strong class="text-dark">${escapeHtml(c.start_date || '-')} to ${escapeHtml(c.end_date || '-')}</strong>
                                </div>
                                <div class="col-md-4">
                                    <span class="text-muted d-block"><i class="bi bi-briefcase text-success me-1"></i>Internship:</span>
                                    <strong class="text-dark">${escapeHtml(c.internship || 'None')}</strong>
                                </div>
                                <div class="col-md-4">
                                    <span class="text-muted d-block"><i class="bi bi-tags text-warning me-1"></i>Key Skills:</span>
                                    <div class="mt-1">${skillsHtml}</div>
                                </div>
                            </div>
                        </div>
                        `;
                    });
                } else {
                    coursesContainer.innerHTML = '<div class="text-center text-muted py-3">No courses found for this student.</div>';
                }

                viewModal.show();
            });
        });

        // Edit Button Event Listener
        let newEditCourseCounter = 0;
        editButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const studentName = this.getAttribute('data-name') || '';
                const studentId = this.getAttribute('data-student-id') || '';
                const profileVal = (this.getAttribute('data-profile') || '').trim();

                document.getElementById('edit_profile_header_name').textContent = studentName;
                document.getElementById('edit_profile_header_id').textContent = 'ID: ' + studentId;

                const avatarWrapper = document.getElementById('edit_profile_avatar_wrapper');
                if (profileVal !== '') {
                    avatarWrapper.innerHTML = `<img src="../../uploads/profiles/${escapeHtml(profileVal)}" alt="Profile" class="rounded-circle border object-fit-cover shadow-sm" style="width: 56px; height: 56px;">`;
                } else {
                    const nameParts = studentName.trim().split(' ');
                    let initials = nameParts[0] ? nameParts[0].charAt(0).toUpperCase() : '';
                    if (nameParts.length > 1) initials += nameParts[nameParts.length - 1].charAt(0).toUpperCase();
                    avatarWrapper.innerHTML = `<div class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px; background: linear-gradient(135deg, #0d7298, #0f172a); font-size: 18px;">${initials || 'GD'}</div>`;
                }

                document.getElementById('edit_id').value = this.getAttribute('data-id');
                document.getElementById('edit_student_name').value = studentName;
                document.getElementById('edit_phone_number').value = this.getAttribute('data-phone');
                document.getElementById('edit_email_id').value = this.getAttribute('data-email');

                const collegeVal = this.getAttribute('data-college') || '';
                document.getElementById('edit_college').value = collegeVal;
                document.getElementById('edit_has_college').checked = collegeVal.trim() !== '';
                toggleOptionalField('edit_', 'college');
                if (collegeVal.trim() !== '') {
                    document.getElementById('edit_college').value = collegeVal;
                }

                // Render Existing Courses in Edit Modal
                let courses = [];
                try {
                    courses = JSON.parse(this.getAttribute('data-courses') || '[]');
                } catch(e) {
                    courses = [];
                }

                const editCoursesContainer = document.getElementById('edit_courses_container');
                editCoursesContainer.innerHTML = '';
                newEditCourseCounter = 0;

                courses.forEach((c, idx) => {
                    const certVal = (c.certificate_file || '').trim();
                    let currentCertHtml = '';
                    if (certVal !== '') {
                        const isPdf = certVal.toLowerCase().endsWith('.pdf');
                        const iconClass = isPdf ? 'bi-file-earmark-pdf-fill text-danger' : 'bi-file-earmark-image-fill text-success';
                        currentCertHtml = `
                        <div class="mb-2 d-flex align-items-center gap-2">
                            <span class="badge bg-success-subtle text-success border px-2.5 py-1 rounded-pill">
                                <i class="bi ${iconClass} me-1"></i>Current: ${escapeHtml(certVal)}
                            </span>
                            <a href="../../uploads/certificates/${escapeHtml(certVal)}" target="_blank" class="small text-primary fw-bold text-decoration-none">
                                <i class="bi bi-eye-fill me-1"></i>View Certificate
                            </a>
                        </div>`;
                    } else {
                        currentCertHtml = `<div class="mb-2"><span class="badge bg-light text-muted border px-2.5 py-1 rounded-pill">No certificate uploaded yet</span></div>`;
                    }

                    const removeBtnHtml = (courses.length > 1 && c.id > 0) ? `
                        <a href="index.php?delete_course=1&course_id=${c.id}" class="btn btn-sm btn-outline-danger py-1 px-2.5 rounded-pill" onclick="return confirm('Are you sure you want to remove this course from student?');">
                            <i class="bi bi-trash-fill me-1"></i>Remove Course
                        </a>
                    ` : '';

                    editCoursesContainer.innerHTML += `
                    <div class="course-card-box">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-3 py-1.5 rounded-pill fw-bold">
                                <i class="bi bi-book-half me-1"></i> Course #${idx + 1}
                            </span>
                            ${removeBtnHtml}
                        </div>
                        <input type="hidden" name="existing_courses[${c.id}][id]" value="${c.id}">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Course Applied <span class="text-danger">*</span></label>
                                <select class="form-select" name="existing_courses[${c.id}][course_name]" required>
                                    ${buildCourseSelectOptions(c.course_name)}
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold"><i class="bi bi-file-earmark-pdf-fill text-danger me-1"></i>Upload / Replace Certificate Document</label>
                                ${currentCertHtml}
                                <input type="file" class="form-control" name="existing_course_cert_${c.id}" accept=".pdf,image/*">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="existing_courses[${c.id}][start_date]" value="${escapeHtml(c.start_date || '')}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">End Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="existing_courses[${c.id}][end_date]" value="${escapeHtml(c.end_date || '')}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Practical Internship (Optional)</label>
                                <input type="text" class="form-control" name="existing_courses[${c.id}][internship]" value="${escapeHtml(c.internship || '')}" placeholder="e.g. Yes (3 Months)">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Key Skills / Tools <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="existing_courses[${c.id}][key_skills]" value="${escapeHtml(c.key_skills || '')}" required>
                            </div>
                        </div>
                    </div>
                    `;
                });

                editModal.show();
            });
        });

        // "+ Add New Course" inside Edit Modal
        document.getElementById('btn_edit_add_course').addEventListener('click', function() {
            const container = document.getElementById('edit_courses_container');
            const idx = newEditCourseCounter;
            const html = `
            <div class="course-card-box border-primary border-opacity-50" id="edit_new_course_card_${idx}">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-3 py-1.5 rounded-pill fw-bold">
                        <i class="bi bi-plus-circle me-1"></i> New Course Entry
                    </span>
                    <button type="button" class="btn btn-sm btn-outline-danger py-1 px-2.5 rounded-pill" onclick="document.getElementById('edit_new_course_card_${idx}').remove()">
                        <i class="bi bi-trash-fill me-1"></i>Remove
                    </button>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Course Applied <span class="text-danger">*</span></label>
                        <select class="form-select" name="new_courses[${idx}][course_name]" required>
                            ${buildCourseSelectOptions()}
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold"><i class="bi bi-file-earmark-pdf-fill text-danger me-1"></i>Certificate Document (PDF / Image)</label>
                        <input type="file" class="form-control" name="new_course_cert_${idx}" accept=".pdf,image/*">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="new_courses[${idx}][start_date]" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">End Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="new_courses[${idx}][end_date]" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Practical Internship (Optional)</label>
                        <input type="text" class="form-control" name="new_courses[${idx}][internship]" placeholder="e.g. Yes (3 Months)">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Key Skills / Tools <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="new_courses[${idx}][key_skills]" required placeholder="e.g. HTML5, CSS3, React">
                    </div>
                </div>
            </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
            newEditCourseCounter++;
        });
    });
    </script>
</body>
</html>