<?php
session_start();
require_once '../../Configurations/config.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: Location: ../../login.php');
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
            $upload_dir = './student_profile/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_filename = 'student_' . $new_username . '.' . $file_ext;
            $upload_path = $upload_dir . $new_filename;

            // Remove old profile image if exists
            if (!empty($student['profile_image']) && file_exists($student['profile_image'])) {
                unlink($student['profile_image']);
            }

            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                // Update profile image path in database
                $update_image = "UPDATE Users SET profile_image = ? WHERE user_id = ?";
                $stmt = mysqli_prepare($conn, $update_image);
                mysqli_stmt_bind_param($stmt, "si", $upload_path, $student_id);
                mysqli_stmt_execute($stmt);
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
        $_SESSION['message'] = "Email already in use by another user.";
        $_SESSION['message_type'] = "danger";
    } else {
        // If username is changed, rename the profile image
        if ($old_username !== $new_username && !empty($student['profile_image'])) {
            $old_image = $student['profile_image'];
            if (file_exists($old_image)) {
                $file_ext = pathinfo($old_image, PATHINFO_EXTENSION);
                $new_image = './student_profile/student_' . $new_username . '.' . $file_ext;
                
                // Rename the file
                if (rename($old_image, $new_image)) {
                    // Update the image path in the database
                    $update_image = "UPDATE Users SET profile_image = ? WHERE user_id = ?";
                    $stmt = mysqli_prepare($conn, $update_image);
                    mysqli_stmt_bind_param($stmt, "si", $new_image, $student_id);
                    mysqli_stmt_execute($stmt);
                }
            }
        }

        // Update user details
        $update_query = "UPDATE Users SET first_name = ?, last_name = ?, email = ?, username = ? WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "ssssi", $first_name, $last_name, $email, $new_username, $student_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['username'] = $new_username;
            $_SESSION['message'] = "Profile updated successfully!";
            $_SESSION['message_type'] = "success";
            header("Location: ./");
            exit();
        } else {
            $_SESSION['message'] = "Error updating profile.";
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .sidebar {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        
        .edit-profile-section {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 30px;
            margin-top: 20px;
        }

        .section-title {
            color: #2C3E50;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }

        .form-label {
            font-weight: 600;
            color: #666;
        }

        .form-control:focus {
            border-color: #2C3E50;
            box-shadow: 0 0 0 0.2rem rgba(44, 62, 80, 0.25);
        }

        .btn-save {
            background: #2C3E50;
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            transition: all 0.3s ease;
        }

        .btn-save:hover {
            background: #34495E;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(44, 62, 80, 0.3);
        }

        .btn-cancel {
            border-radius: 50px;
            padding: 12px 30px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row flex-nowrap">
            

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

                    <div class="edit-profile-section">
                        <h2 class="section-title">Edit Profile</h2>
                        <form method="POST" class="needs-validation" enctype="multipart/form-data" novalidate>
                            <div class="mb-4 text-center">
                                <div class="position-relative d-inline-block">
                                    <img src="<?php echo !empty($student['profile_image']) ? $student['profile_image'] : '../../assets/images/default-avatar.png'; ?>" 
                                         alt="Profile Picture" 
                                         class="rounded-circle mb-3" 
                                         style="width: 150px; height: 150px; object-fit: cover;" 
                                         id="profile-preview">
                                    <label for="profile_image" class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle p-2 cursor-pointer">
                                        <i class="bi bi-camera-fill"></i>
                                        <input type="file" 
                                               id="profile_image" 
                                               name="profile_image" 
                                               class="d-none" 
                                               accept="image/*"
                                               onchange="previewImage(this)">
                                    </label>
                                </div>
                                <p class="text-muted small">Click the camera icon to change profile picture</p>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="first_name" class="form-label">First Name</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="first_name" 
                                           name="first_name" 
                                           value="<?php echo htmlspecialchars($student['first_name']); ?>" 
                                           required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="last_name" class="form-label">Last Name</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="last_name" 
                                           name="last_name" 
                                           value="<?php echo htmlspecialchars($student['last_name']); ?>" 
                                           required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       value="<?php echo htmlspecialchars($student['email']); ?>" 
                                       required>
                            </div>

                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="username" 
                                       name="username" 
                                       value="<?php echo htmlspecialchars($student['username']); ?>" 
                                       required>
                            </div>

                            <div class="mt-4 d-flex justify-content-end gap-2">
                                <a href="./" class="btn btn-secondary btn-cancel">Cancel</a>
                                <button type="submit" class="btn btn-primary btn-save">Save Changes</button>
                            </div>
                        </form>
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

        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    document.getElementById('profile-preview').src = e.target.result;
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Add file size validation
        document.getElementById('profile_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
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
