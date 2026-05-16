<?php
require_once '../Configurations/config.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $email = trim($_POST['email']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $profile_image = $_FILES['profile_image'];
    $registration_success = false;

    // Add error message array
    $errors = [];

    // Validate inputs
    if (empty($username)) $errors[] = "Username is required";
    if (empty($password)) $errors[] = "Password is required";
    if (empty($email)) $errors[] = "Email is required";
    if (empty($first_name)) $errors[] = "First name is required";
    if (empty($last_name)) $errors[] = "Last name is required";

    // Check for duplicate username or email
    if (empty($errors)) {
        $checkStmt = $conn->prepare("SELECT user_id FROM Users WHERE username = ? OR email = ?");
        $checkStmt->bind_param("ss", $username, $email);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows > 0) {
            $errors[] = "Username or Email already exists. Please try another.";
            $checkStmt->close();
        } else {
            $checkStmt->close();

            // Hash the password
            $password_hash = password_hash($password, PASSWORD_BCRYPT);

            // Handle profile image upload
            $profile_image_name = null;
            if ($profile_image && $profile_image['error'] == 0) {
                // Validate file type and size
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                if (in_array($profile_image['type'], $allowed_types) && $profile_image['size'] <= 2000000) { // 2MB limit
                    $profile_image_name = time() . '_' . basename($profile_image['name']);
                    $profile_image_path = "./Profile/student_profile/" . $profile_image_name;
                    $uploading_image_path = "student_profile/" . $profile_image_name;

                    
                    // Move the uploaded file
                    if (!move_uploaded_file($profile_image['tmp_name'], $profile_image_path)) {
                        $errors[] = "Error uploading profile image.";
                    }
                } else {
                    $errors[] = "Invalid file type or size. Please upload a JPEG, PNG, or GIF image under 2MB.";
                }
            }

            // If no errors, insert user into the database
            if (empty($errors)) {
                $stmt = $conn->prepare("
                    INSERT INTO Users (username, password_hash, email, first_name, last_name, profile_image, role, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $role = "student"; // Default role
                $status = "active"; // Default status
                $stmt->bind_param("ssssssss", $username, $password_hash, $email, $first_name, $last_name, $uploading_image_path, $role, $status);

                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Registration successful! You will be redirected to the login page.";
                    $registration_success = true;
                } else {
                    $errors[] = "Error: " . $stmt->error;
                }
                ?>
                
                <?php if (isset($registration_success) && $registration_success): ?>
                    <div>
                        <script>
                            setTimeout(() => {
                                window.location.href = "./login.php"; // Redirect to the login page
                            }, 5000); // 5000 milliseconds = 5 seconds
                        </script>
                    </div>
                <?php endif;                 

                $stmt->close();
            }
        }
    }

    // If there are validation errors, store them in session
    if (!empty($errors)) {
        $_SESSION['error_message'] = implode("<br>", $errors);
    }
}

// Add this at the top of your HTML to display error messages
if (isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
    unset($_SESSION['error_message']);
}
if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
    unset($_SESSION['success_message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - GD Edu Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 0;
        }
        .signup-container {
            max-width: 500px;
            margin: 0 auto;
            padding: 40px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        .signup-title {
            font-size: 28px;
            font-weight: 700;
            color: #2C3E50;
            margin-bottom: 30px;
            text-align: center;
        }
        .form-label {
            font-weight: 600;
            color: #2C3E50;
            margin-bottom: 8px;
        }
        .form-control {
            padding: 12px;
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(44, 62, 80, 0.1);
            border-color: #2C3E50;
        }
        .btn-primary {
            padding: 12px;
            font-weight: 600;
            border-radius: 10px;
            background: #2C3E50;
            border: none;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background: #34495E;
            transform: translateY(-2px);
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo img {
            height: 60px;
        }
        .input-group-text {
            background: transparent;
            border-radius: 10px;
            border: 1px solid #e0e0e0;
        }
        .profile-upload {
            text-align: center;
            margin-bottom: 20px;
        }
        .profile-upload label {
            cursor: pointer;
            padding: 10px 20px;
            border-radius: 10px;
            border: 2px dashed #e0e0e0;
            display: inline-block;
            transition: all 0.3s ease;
        }
        .profile-upload label:hover {
            border-color: #2C3E50;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="signup-container">
        <div class="logo">
            <img src="../Images/Logos/GD_Only_logo.png" alt="GD Edu Tech">
        </div>
        <h2 class="signup-title">Create Account</h2>
        <form action="signup.php" method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6 mb-4">
                    <label for="first_name" class="form-label">First Name</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <label for="last_name" class="form-label">Last Name</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                    </div>
                </div>
            </div>
            <div class="mb-4">
                <label for="username" class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-at"></i></span>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
            </div>
            <div class="mb-4">
                <label for="email" class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
            </div>
            <div class="mb-4">
                <label for="profile_image" class="form-label d-block">Profile Image (Optional)</label>
                <div class="profile-upload">
                    <label for="profile_image">
                        <i class="bi bi-cloud-upload me-2"></i>Choose Image
                        <input type="file" class="form-control d-none" id="profile_image" name="profile_image">
                    </label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 mb-3">Create Account</button>
            <div class="text-center">
                <span class="text-muted">Already have an account?</span>
                <a href="login.php" class="text-primary fw-bold text-decoration-none">Login</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>