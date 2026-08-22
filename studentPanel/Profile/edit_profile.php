<?php
session_start();
require_once '../../Configurations/config.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../login_page.php');
    exit();
}

// Get student details from database
$student_id = $_SESSION['user_id'];
$query = "SELECT * FROM Users WHERE user_id = ? AND role = 'Student'";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $new_username = mysqli_real_escape_string($conn, $_POST['username']);
    $old_username = $student['username'];
    
    // Handle profile image upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_image'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (!in_array($file['type'], $allowed_types)) {
            $_SESSION['message'] = "Only JPG, PNG and GIF images are allowed.";
            $_SESSION['message_type'] = "danger";
        } elseif ($file['size'] > $max_size) {
            $_SESSION['message'] = "File size must be less than 5MB.";
            $_SESSION['message_type'] = "danger";
        } else {
            $upload_dir = '../../uploads/profiles/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_filename = 'student_' . $student_id . '_' . time() . '.' . $file_ext;
            $upload_path = $upload_dir . $new_filename;

            // Remove old profile image if exists
            if (!empty($student['profile_image'])) {
                $old_full_path = '../../uploads/profiles/' . $student['profile_image'];
                if (file_exists($old_full_path)) {
                    @unlink($old_full_path);
                }
            }

            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                // Update profile image filename in database
                $update_image = "UPDATE Users SET profile_image = ? WHERE user_id = ?";
                $stmt = mysqli_prepare($conn, $update_image);
                mysqli_stmt_bind_param($stmt, "si", $new_filename, $student_id);
                mysqli_stmt_execute($stmt);
                $student['profile_image'] = $new_filename;
            }
        }
    }

    // Check if email is already taken by another user
    $email_check = "SELECT user_id FROM Users WHERE email = ? AND user_id != ?";
    $stmt = mysqli_prepare($conn, $email_check);
    mysqli_stmt_bind_param($stmt, "si", $email, $student_id);
    mysqli_stmt_execute($stmt);
    $email_result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($email_result) > 0) {
        $_SESSION['message'] = "Email is already in use by another user.";
        $_SESSION['message_type'] = "danger";
    } else {
        // Update user details
        $update_query = "UPDATE Users SET first_name = ?, last_name = ?, email = ?, username = ? WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "ssssi", $first_name, $last_name, $email, $new_username, $student_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['username'] = $new_username;
            $_SESSION['first_name'] = $first_name;
            $_SESSION['last_name'] = $last_name;
            $_SESSION['message'] = "Profile updated successfully!";
            $_SESSION['message_type'] = "success";
            header("Location: ./");
            exit();
        } else {
            $_SESSION['message'] = "Error updating profile details.";
            $_SESSION['message_type'] = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - GD Edu Tech</title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="../../Images/Logos/GD_Only_logo.png">

    <style>
        :root {
            --admin-primary: #0d7298;
            --admin-primary-dark: #065d7d;
            --admin-dark-bg: #0f172a;
            --admin-dark-surface: #1e293b;
            --admin-light-bg: #f8fafc;
            --admin-border-color: #e2e8f0;
        }

        * {
            font-family: 'Poppins', sans-serif;
            box-sizing: border-box;
        }

        body {
            background-color: var(--admin-light-bg);
            color: #0f172a;
            overflow-x: hidden;
        }

        /* Sidebar Styling */
        .sidebar {
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%) !important;
            min-height: 100vh;
            color: #ffffff;
            box-shadow: 4px 0 25px rgba(15, 23, 42, 0.15);
            z-index: 1000;
        }

        .sidebar .nav-link {
            color: #94a3b8 !important;
            font-size: 0.9rem;
            font-weight: 500;
            padding: 12px 18px;
            margin: 3px 10px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.25s ease;
            text-decoration: none !important;
        }

        .sidebar .nav-link i {
            font-size: 1.15rem;
        }

        .sidebar .nav-link:hover {
            color: #ffffff !important;
            background: rgba(255, 255, 255, 0.08) !important;
            transform: translateX(4px);
        }

        .sidebar .nav-link.active {
            color: #ffffff !important;
            background: linear-gradient(90deg, rgba(13, 114, 152, 0.9) 0%, rgba(6, 93, 125, 0.95) 100%) !important;
            box-shadow: 0 8px 20px rgba(13, 114, 152, 0.35);
            font-weight: 700 !important;
        }

        .sidebar .nav-link.active i {
            color: #38bdf8 !important;
        }

        .sidebar .nav-link.text-danger {
            color: #f87171 !important;
            background: rgba(239, 68, 68, 0.1) !important;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .sidebar .nav-link.text-danger:hover {
            background: #ef4444 !important;
            color: #ffffff !important;
        }

        /* Layout bounds */
        .row.flex-nowrap > .col,
        .main-content {
            min-width: 0 !important;
            max-width: 100% !important;
        }

        .edit-profile-card {
            border: 1px solid var(--admin-border-color) !important;
            border-radius: 24px !important;
            background: #ffffff;
            box-shadow: 0 10px 30px -10px rgba(15, 23, 42, 0.06) !important;
            overflow: hidden;
        }

        .form-control-custom {
            border-radius: 12px !important;
            border: 1.5px solid #cbd5e1 !important;
            padding: 10px 14px !important;
            font-size: 0.9rem !important;
        }

        .form-control-custom:focus {
            border-color: #0d7298 !important;
            box-shadow: 0 0 0 4px rgba(13, 114, 152, 0.12) !important;
        }

        .avatar-preview-container {
            width: 130px;
            height: 130px;
            position: relative;
            margin: 0 auto;
        }

        .avatar-preview-img {
            width: 130px;
            height: 130px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #ffffff;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.15);
        }

        .camera-badge {
            position: absolute;
            bottom: 4px;
            right: 4px;
            background: #0d7298;
            color: #ffffff;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 3px solid #ffffff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            transition: all 0.2s ease;
        }

        .camera-badge:hover {
            background: #065d7d;
            transform: scale(1.08);
        }

        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
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
                        <span class="text-info small fw-semibold">● Student Portal</span>
                    </div>
                </div>

                <ul class="nav nav-pills flex-column mb-auto p-2 w-100">
                    <li class="w-100"><a href="../" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                    <li class="w-100"><a href="../MyCourses/" class="nav-link"><i class="bi bi-journal-bookmark"></i> My Courses</a></li>
                    <li class="w-100"><a href="../Categories/" class="nav-link"><i class="bi bi-grid"></i> Categories</a></li>
                    <li class="w-100"><a href="../Schedule/" class="nav-link"><i class="bi bi-calendar-event"></i> Schedule</a></li>
                    <li class="w-100"><a href="../Messages/" class="nav-link"><i class="bi bi-chat-dots"></i> Messages</a></li>
                    <li class="w-100"><a href="./" class="nav-link active"><i class="bi bi-person"></i> Profile</a></li>
                    <li class="w-100"><a href="../Resources/" class="nav-link"><i class="bi bi-file-earmark-text"></i> Resources</a></li>
                </ul>

                <div class="p-3 border-top border-white border-opacity-10 mt-auto">
                    <a href="../../logout.php" class="nav-link text-danger justify-content-center m-0">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="col main-content min-vh-100 d-flex flex-column" style="min-width: 0; overflow-x: hidden;">
                
                <!-- Top Header Bar -->
                <div class="bg-white border-bottom px-4 py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <a href="./" class="btn btn-outline-secondary btn-sm rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="Back to Profile">
                            <i class="bi bi-arrow-left fs-6"></i>
                        </a>
                        <div>
                            <h4 class="fw-bold text-dark mb-0">Edit Student Profile</h4>
                            <span class="text-muted small">Update your personal details, email, and profile avatar</span>
                        </div>
                    </div>
                </div>

                <div class="p-4 flex-grow-1">
                    <div class="row justify-content-center">
                        <div class="col-12 col-md-10 col-lg-8">
                            
                            <?php if (isset($_SESSION['message'])): ?>
                                <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                                    <?php 
                                        echo $_SESSION['message'];
                                        unset($_SESSION['message']);
                                        unset($_SESSION['message_type']);
                                    ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>

                            <div class="card edit-profile-card p-4 p-md-5">
                                <form method="POST" class="needs-validation" enctype="multipart/form-data" novalidate>
                                    
                                    <!-- Avatar Upload Preview -->
                                    <div class="text-center mb-4">
                                        <div class="avatar-preview-container mb-3">
                                            <?php 
                                                $imgSrc = '../../assets/images/default-avatar.png';
                                                if (!empty($student['profile_image'])) {
                                                    if (strpos($student['profile_image'], '/') !== false) {
                                                        $imgSrc = $student['profile_image'];
                                                    } else {
                                                        $imgSrc = '../../uploads/profiles/' . $student['profile_image'];
                                                    }
                                                }
                                            ?>
                                            <img src="<?php echo htmlspecialchars($imgSrc); ?>" 
                                                 alt="Profile Picture" 
                                                 class="avatar-preview-img" 
                                                 id="profile-preview"
                                                 onerror="this.src='../../assets/images/default-avatar.png';"
                                            >
                                            <label for="profile_image" class="camera-badge" title="Upload new avatar">
                                                <i class="bi bi-camera-fill"></i>
                                                <input type="file" 
                                                       id="profile_image" 
                                                       name="profile_image" 
                                                       class="d-none" 
                                                       accept="image/jpeg,image/png,image/gif"
                                                       onchange="previewImage(this)">
                                            </label>
                                        </div>
                                        <span class="text-muted small d-block">Click the camera badge to select a new profile photo (Max 5MB)</span>
                                    </div>

                                    <!-- Name Fields Row -->
                                    <div class="row g-3 mb-3">
                                        <div class="col-12 col-md-6">
                                            <label for="first_name" class="form-label font-weight-semibold small text-secondary">First Name *</label>
                                            <input type="text" 
                                                   class="form-control form-control-custom" 
                                                   id="first_name" 
                                                   name="first_name" 
                                                   value="<?php echo htmlspecialchars($student['first_name']); ?>" 
                                                   required>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label for="last_name" class="form-label font-weight-semibold small text-secondary">Last Name *</label>
                                            <input type="text" 
                                                   class="form-control form-control-custom" 
                                                   id="last_name" 
                                                   name="last_name" 
                                                   value="<?php echo htmlspecialchars($student['last_name']); ?>" 
                                                   required>
                                        </div>
                                    </div>

                                    <!-- Email -->
                                    <div class="mb-3">
                                        <label for="email" class="form-label font-weight-semibold small text-secondary">Email Address *</label>
                                        <input type="email" 
                                               class="form-control form-control-custom" 
                                               id="email" 
                                               name="email" 
                                               value="<?php echo htmlspecialchars($student['email']); ?>" 
                                               required>
                                    </div>

                                    <!-- Username -->
                                    <div class="mb-4">
                                        <label for="username" class="form-label font-weight-semibold small text-secondary">Username *</label>
                                        <input type="text" 
                                               class="form-control form-control-custom" 
                                               id="username" 
                                               name="username" 
                                               value="<?php echo htmlspecialchars($student['username']); ?>" 
                                               required>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="pt-3 border-top d-flex justify-content-end gap-3">
                                        <a href="./" class="btn btn-outline-secondary rounded-pill px-4 py-2.5 fw-semibold">Cancel</a>
                                        <button type="submit" class="btn btn-primary rounded-pill px-4 py-2.5 fw-semibold">
                                            <i class="bi bi-check2-circle me-1.5"></i>Save Changes
                                        </button>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
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

        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profile-preview').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Add file size & type validation
        document.getElementById('profile_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const fileSize = file.size / 1024 / 1024; // Convert to MB
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            
            if (fileSize > 5) {
                alert('File size must be less than 5MB');
                this.value = '';
                return;
            }
            
            if (!allowedTypes.includes(file.type)) {
                alert('Only JPG, PNG and GIF images are allowed');
                this.value = '';
                return;
            }
        });
    </script>
</body>
</html>
