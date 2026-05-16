<?php
session_start();
require_once '../../Configurations/config.php';

// Check if user is logged in and is staff
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Staff') {
    header('Location: ../staff_login.php');
    exit();
}

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image'])) {
    $file = $_FILES['profile_image'];
    $username = $_SESSION['username'];
    
    // Check for errors
    if ($file['error'] === 0) {
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_ext, $allowed)) {
            // Set directory and file paths
            $upload_dir = './staff_profile/';
            $new_filename = $username . '.' . $file_ext;  // Using just username as filename
            $upload_path = $upload_dir . $new_filename;
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Delete old profile image if exists
            $query = "SELECT profile_image FROM Users WHERE user_id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $old_image = mysqli_fetch_assoc($result)['profile_image'];
            
            if ($old_image && file_exists($old_image)) {
                unlink($old_image); // Delete the old file
            }
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                // Update database with new image path
                $query = "UPDATE Users SET profile_image = ? WHERE user_id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "si", $upload_path, $_SESSION['user_id']);
                
                if (mysqli_stmt_execute($stmt)) {
                    $_SESSION['message'] = "Profile image updated successfully!";
                    $_SESSION['message_type'] = "success";
                } else {
                    $_SESSION['message'] = "Error updating database.";
                    $_SESSION['message_type'] = "danger";
                }
            } else {
                $_SESSION['message'] = "Error uploading file.";
                $_SESSION['message_type'] = "danger";
            }
        } else {
            $_SESSION['message'] = "Invalid file type. Allowed: jpg, jpeg, png, gif";
            $_SESSION['message_type'] = "danger";
        }
    } else {
        $_SESSION['message'] = "Error uploading file.";
        $_SESSION['message_type'] = "danger";
    }
    
    header("Location: profile.php");
    exit();
}

// Get staff details from database
$staff_id = $_SESSION['user_id'];
$query = "SELECT * FROM Users WHERE user_id = ? AND role = 'Staff'";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $staff_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$staff = mysqli_fetch_assoc($result);

if (!$staff) {
    header('Location: ../staff_login.php');
    exit();
}

// Get staff name from session
$staff_name = $_SESSION['username'] ?? 'Staff';

// Get course count
$course_query = "SELECT COUNT(*) as course_count FROM Courses WHERE created_by = ?";
$stmt = mysqli_prepare($conn, $course_query);
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$course_count = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['course_count'];

// Get total enrolled students count
$students_query = "SELECT COUNT(DISTINCT e.student_id) as student_count 
                  FROM Enrollments e 
                  JOIN Courses c ON e.course_id = c.course_id 
                  WHERE c.created_by = ?";
$stmt = mysqli_prepare($conn, $students_query);
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$student_count = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['student_count'];

// Get published courses count
$published_query = "SELECT COUNT(*) as published_count 
                   FROM Courses 
                   WHERE created_by = ? AND status = 'published'";
$stmt = mysqli_prepare($conn, $published_query);
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$published_count = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['published_count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Profile - GD Edu Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="./course.css">
    <link rel="stylesheet" href="../../adminPanel/css/style.css">
    <style>
        .sidebar {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        
        .profile-section {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-top: 20px;
        }

        .profile-header {
            background: linear-gradient(135deg, #d30043 0%, #b20039 100%);
            color: white;
            padding: 40px 20px;
            position: relative;
            text-align: center;
        }

        .profile-image-container {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto 20px;
        }

        .profile-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 5px solid white;
            box-shadow: 0 0 20px rgba(0,0,0,0.2);
            object-fit: cover;
        }

        .camera-btn {
            position: absolute;
            bottom: 0;
            right: 10px;
            background: #fff;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            border: none;
            transition: all 0.3s ease;
        }

        .camera-btn:hover {
            transform: scale(1.1);
            background: #f8f9fa;
        }

        .profile-name {
            font-size: 24px;
            font-weight: 600;
            margin: 10px 0 5px;
        }

        .profile-role {
            font-size: 16px;
            opacity: 0.9;
        }

        .profile-details {
            padding: 30px;
        }

        .info-section {
            margin-bottom: 30px;
        }

        .info-section h2 {
            color: #d30043;
            font-size: 20px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }

        .info-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-radius: 10px;
            background: #f8f9fa;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }

        .info-item:hover {
            background: #f0f0f0;
            transform: translateX(5px);
        }

        .info-label {
            font-weight: 600;
            color: #666;
            width: 140px;
        }

        .info-value {
            color: #333;
            flex-grow: 1;
        }

        .info-icon {
            margin-right: 15px;
            color: #d30043;
            font-size: 20px;
        }

        .edit-profile-btn {
            background: #d30043;
            border: none;
            padding: 8px 20px;
            border-radius: 50px;
            transition: all 0.3s ease;
            color: white;
        }

        .edit-profile-btn:hover {
            background: #b20039;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(211, 0, 67, 0.3);
            color: white;
        }

        /* Modal Styling */
        .modal-content {
            border-radius: 15px;
            border: none;
        }

        .modal-header {
            background: #d30043;
            color: white;
            border-radius: 15px 15px 0 0;
        }

        .modal-body {
            padding: 25px;
        }

        .custom-file-input {
            border: 2px dashed #ddd;
            padding: 20px;
            text-align: center;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .custom-file-input:hover {
            border-color: #d30043;
        }

        .course-item {
            transition: all 0.3s ease;
            border: 1px solid #eee;
        }

        .course-item:hover {
            transform: translateX(5px);
            background: #f8f9fa;
            border-color: #d30043;
        }

        .course-thumbnail img {
            border: 1px solid #eee;
            transition: all 0.3s ease;
        }

        .course-item:hover .course-thumbnail img {
            transform: scale(1.05);
        }

        .course-meta {
            font-size: 0.875rem;
            color: #6c757d;
        }

        .course-meta i {
            font-size: 0.75rem;
        }

        .course-actions {
            opacity: 0;
            transition: all 0.3s ease;
        }

        .course-item:hover .course-actions {
            opacity: 1;
        }

        .badge {
            padding: 0.35em 0.65em;
            font-size: 0.75em;
        }

        .section-title {
            color: #d30043;
            font-size: 20px;
            border-bottom: none; /* Remove bottom border since we're using d-flex */
        }

        .edit-profile-btn {
            background: #d30043;
            border: none;
            padding: 8px 20px;
            border-radius: 50px;
            transition: all 0.3s ease;
            color: white;
        }

        .edit-profile-btn:hover {
            background: #b20039;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(211, 0, 67, 0.3);
            color: white;
        }

        .info-section {
            margin-bottom: 30px;
        }

        .info-section:after {
            content: '';
            display: block;
            width: 100%;
            height: 2px;
            background: #f0f0f0;
            margin-top: 15px;
        }

        .stats-section {
            background: #fff;
            border-radius: 15px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-icon i {
            font-size: 24px;
        }

        .stat-details {
            flex-grow: 1;
        }

        .stat-number {
            font-size: 24px;
            font-weight: 600;
            margin: 0;
            color: #333;
        }

        .stat-label {
            font-size: 14px;
            color: #666;
            margin: 0;
            margin-top: 5px;
        }

        /* Icon colors */
        .bg-primary-subtle i { color: #0d6efd; }
        .bg-success-subtle i { color: #198754; }
        .bg-info-subtle i { color: #0dcaf0; }
        .bg-warning-subtle i { color: #ffc107; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row flex-nowrap">
            <!-- Sidebar -->
            <div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 sidebar">
                <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 min-vh-100">
                    <a href="#" class="d-flex align-items-center pb-3 mb-md-1 mt-md-3 me-md-auto text-white text-decoration-none">
                        <span class="fs-5 fw-bolder" style="display: flex;align-items:center;color:black;">
                            <img height="35px" src="./images/edutechLogo.png" alt="">&nbsp; GD Edu Tech
                        </span>
                    </a>
                    <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100" id="menu">
                        <li class="w-100">
                            <a href="../" class="nav-link">
                                <i class="bi bi-speedometer2 me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="./Courses/" class="nav-link">
                                <i class="bi bi-book me-2"></i> Courses
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="./Quiz/" class="nav-link">
                                <i class="bi bi-lightbulb me-2"></i> Quiz
                            </a>
                        </li>
                        <li class="w-100 mt-auto">
                            <a href="./logout.php" class="nav-link text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col py-3">
                <div class="container">
                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                            <?php 
                                echo $_SESSION['message'];
                                unset($_SESSION['message']);
                                unset($_SESSION['message_type']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="profile-section">
                        <div class="profile-header">
                            <div class="profile-image-container">
                                <img src="<?php echo $staff['profile_image'] ?? 'images/default-profile.jpg'; ?>" 
                                     alt="Profile Image" 
                                     class="profile-image">
                                <button type="button" 
                                        class="camera-btn"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#uploadImageModal">
                                    <i class="bi bi-camera"></i>
                                </button>
                            </div>
                            <h1 class="profile-name"><?php echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']); ?></h1>
                            <p class="profile-role"><?php echo htmlspecialchars($staff['role']); ?></p>
                        </div>

                        <div class="stats-section px-4 py-4">
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <div class="stat-card">
                                        <div class="stat-icon bg-primary-subtle">
                                            <i class="bi bi-book"></i>
                                        </div>
                                        <div class="stat-details">
                                            <h3 class="stat-number"><?php echo $course_count; ?></h3>
                                            <p class="stat-label">Total Courses</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="stat-card">
                                        <div class="stat-icon bg-success-subtle">
                                            <i class="bi bi-people"></i>
                                        </div>
                                        <div class="stat-details">
                                            <h3 class="stat-number"><?php echo $student_count; ?></h3>
                                            <p class="stat-label">Enrolled Students</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="stat-card">
                                        <div class="stat-icon bg-info-subtle">
                                            <i class="bi bi-check-circle"></i>
                                        </div>
                                        <div class="stat-details">
                                            <h3 class="stat-number"><?php echo $published_count; ?></h3>
                                            <p class="stat-label">Published Courses</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="profile-details">
                            <div class="info-section">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h2 class="section-title mb-0">Personal Information</h2>
                                    <a href="edit_profile.php" class="btn btn-primary edit-profile-btn">
                                        <i class="bi bi-pencil-square me-2"></i>Edit Profile
                                    </a>
                                </div>
                                <div class="info-item">
                                    <i class="bi bi-person-badge info-icon"></i>
                                    <span class="info-label">Username</span>
                                    <span class="info-value"><?php echo htmlspecialchars($staff['username']); ?></span>
                                </div>
                                <div class="info-item">
                                    <i class="bi bi-envelope info-icon"></i>
                                    <span class="info-label">Email</span>
                                    <span class="info-value"><?php echo htmlspecialchars($staff['email']); ?></span>
                                </div>
                                <div class="info-item">
                                    <i class="bi bi-person info-icon"></i>
                                    <span class="info-label">First Name</span>
                                    <span class="info-value"><?php echo htmlspecialchars($staff['first_name']); ?></span>
                                </div>
                                <div class="info-item">
                                    <i class="bi bi-person info-icon"></i>
                                    <span class="info-label">Last Name</span>
                                    <span class="info-value"><?php echo htmlspecialchars($staff['last_name']); ?></span>
                                </div>
                                <div class="info-item">
                                    <i class="bi bi-check-circle info-icon"></i>
                                    <span class="info-label">Status</span>
                                    <span class="info-value"><?php echo htmlspecialchars($staff['status']); ?></span>
                                </div>
                                <div class="info-item">
                                    <i class="bi bi-calendar-event info-icon"></i>
                                    <span class="info-label">Joined Date</span>
                                    <span class="info-value"><?php echo date('F j, Y', strtotime($staff['date_joined'])); ?></span>
                                </div>
                            </div>

                            <div class="info-section">
                                <h2>My Courses</h2>
                                <?php
                                // Fetch courses uploaded by this staff
                                $courses_query = "SELECT c.*, cat.name as category_name 
                                                 FROM Courses c 
                                                 LEFT JOIN Categories cat ON c.category_id = cat.category_id 
                                                 WHERE c.created_by = ? 
                                                 ORDER BY c.date_created DESC";
                                $stmt = mysqli_prepare($conn, $courses_query);
                                mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
                                mysqli_stmt_execute($stmt);
                                $courses_result = mysqli_stmt_get_result($stmt);
                                
                                
                                if (mysqli_num_rows($courses_result) > 0) {
                                    while ($course = mysqli_fetch_assoc($courses_result)) {
                                ?>
                                    <div class="course-item info-item">
                                        <div class="d-flex align-items-center w-100">
                                            <div class="course-thumbnail me-3">
                                                <img src="<?php echo '../../uploads/course_uploads/thumbnails/' . ($course['thumbnail'] ?? 'default-course.jpg'); ?>" 
                                                     alt="Course Thumbnail" 
                                                     class="rounded"
                                                     style="width: 100px; height: 60px; object-fit: cover;">
                                            </div>
                                            <div class="course-info flex-grow-1">
                                                <h5 class="mb-1"><?php echo htmlspecialchars($course['title']); ?></h5>
                                                <div class="course-meta d-flex gap-3 text-muted small">
                                                    <span><i class="bi bi-folder me-1"></i><?php echo htmlspecialchars($course['category_name']); ?></span>
                                                    <span><i class="bi bi-bar-chart me-1"></i><?php echo htmlspecialchars($course['level']); ?></span>
                                                    <span><i class="bi bi-calendar me-1"></i><?php echo date('M d, Y', strtotime($course['date_created'])); ?></span>
                                                    <span class="badge <?php echo $course['status'] === 'published' ? 'bg-success' : 'bg-warning'; ?>">
                                                        <?php echo ucfirst(htmlspecialchars($course['status'])); ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="course-actions">
                                                <a href="../Courses/edit_course.php?id=<?php echo $course['course_id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php 
                                    }
                                } else {
                                ?>
                                    <div class="text-center py-4">
                                        <i class="bi bi-journal-x display-4 text-muted"></i>
                                        <p class="mt-2 text-muted">No courses uploaded yet.</p>
                                        <a href="../Courses/add_course.php" class="btn btn-primary mt-2">
                                            <i class="bi bi-plus-circle me-2"></i>Create New Course
                                        </a>
                                    </div>
                                <?php
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Image Modal -->
    <div class="modal fade" id="uploadImageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Profile Picture</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <div class="custom-file-input">
                                <i class="bi bi-cloud-upload mb-3" style="font-size: 2rem;"></i>
                                <h5>Drop your image here</h5>
                                <p class="text-muted">or click to browse</p>
                                <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*" required>
                            </div>
                            <div class="form-text text-center mt-2">Allowed formats: JPG, JPEG, PNG, GIF</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>