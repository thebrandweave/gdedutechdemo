<?php
session_start();
require_once '../../Configurations/config.php';

// Check if user is logged in and is staff
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Staff') {
    header('Location: ../staff_login.php');
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $new_username = mysqli_real_escape_string($conn, $_POST['username']);
    $old_username = $staff['username'];
    
    // Check if email is already taken by another user
    $email_check = "SELECT user_id FROM Users WHERE email = ? AND user_id != ?";
    $stmt = mysqli_prepare($conn, $email_check);
    mysqli_stmt_bind_param($stmt, "si", $email, $staff_id);
    mysqli_stmt_execute($stmt);
    $email_result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($email_result) > 0) {
        $_SESSION['message'] = "Email already in use by another user.";
        $_SESSION['message_type'] = "danger";
    } else {
        // If username is changed, rename the profile image
        if ($old_username !== $new_username && !empty($staff['profile_image'])) {
            $old_image = $staff['profile_image'];
            if (file_exists($old_image)) {
                $file_ext = pathinfo($old_image, PATHINFO_EXTENSION);
                $new_image = './staff_profile/staff_' . $new_username . '.' . $file_ext;
                
                // Rename the file
                if (rename($old_image, $new_image)) {
                    // Update the image path in the database
                    $update_image = "UPDATE Users SET profile_image = ? WHERE user_id = ?";
                    $stmt = mysqli_prepare($conn, $update_image);
                    mysqli_stmt_bind_param($stmt, "si", $new_image, $staff_id);
                    mysqli_stmt_execute($stmt);
                }
            }
        }

        // Update user details
        $update_query = "UPDATE Users SET first_name = ?, last_name = ?, email = ?, username = ? WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "ssssi", $first_name, $last_name, $email, $new_username, $staff_id);
        
        if (mysqli_stmt_execute($stmt)) {
            // Update session username
            $_SESSION['username'] = $new_username;
            $_SESSION['message'] = "Profile updated successfully!";
            $_SESSION['message_type'] = "success";
            header("Location: profile.php");
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
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="./course.css">
    <link rel="stylesheet" href="../../adminPanel/css/style.css">
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
            color: #d30043;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }

        .form-label {
            font-weight: 600;
            color: #666;
        }

        .form-control:focus {
            border-color: #d30043;
            box-shadow: 0 0 0 0.2rem rgba(211, 0, 67, 0.25);
        }

        .btn-save {
            background: #d30043;
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            transition: all 0.3s ease;
        }

        .btn-save:hover {
            background: #b20039;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(211, 0, 67, 0.3);
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
            <!-- Sidebar -->
            <div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 sidebar">
                <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 min-vh-100">
                    <a href="#" class="d-flex align-items-center pb-3 mb-md-1 mt-md-3 me-md-auto text-white text-decoration-none">
                        <span class="fs-5 fw-bolder" style="display: flex;align-items:center;color:black;">
                            <img height="35px" src="../images/edutechLogo.png" alt="">&nbsp; GD Edu Tech
                        </span>
                    </a>
                    <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100" id="menu">
                        <!-- Same sidebar as profile.php -->
                        <li class="w-100">
                            <a href="../" class="nav-link">
                                <i class="bi bi-speedometer2 me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Courses/" class="nav-link">
                                <i class="bi bi-book me-2"></i> Courses
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Quiz/" class="nav-link">
                                <i class="bi bi-lightbulb me-2"></i> Quiz
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
                        <form method="POST" class="needs-validation" novalidate>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="first_name" class="form-label">First Name</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="first_name" 
                                           name="first_name" 
                                           value="<?php echo htmlspecialchars($staff['first_name']); ?>" 
                                           required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="last_name" class="form-label">Last Name</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="last_name" 
                                           name="last_name" 
                                           value="<?php echo htmlspecialchars($staff['last_name']); ?>" 
                                           required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       value="<?php echo htmlspecialchars($staff['email']); ?>" 
                                       required>
                            </div>

                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="username" 
                                       name="username" 
                                       value="<?php echo htmlspecialchars($staff['username']); ?>" 
                                       required>
                            </div>

                            <div class="mt-4 d-flex justify-content-end gap-2">
                                <a href="profile.php" class="btn btn-secondary btn-cancel">Cancel</a>
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
    </script>
</body>
</html> 