<?php
session_start();
require_once '../Configurations/config.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $profile_image = $_FILES['profile_image'] ?? null;
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

        if ($result && $result->num_rows > 0) {
            $errors[] = "Username or Email already exists. Please try another.";
            $checkStmt->close();
        } else {
            $checkStmt->close();

            // Hash the password
            $password_hash = password_hash($password, PASSWORD_BCRYPT);

            // Handle profile image upload
            $profile_image_name = null;
            $uploading_image_path = null;
            if ($profile_image && $profile_image['error'] == 0) {
                // Validate file type and size
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (in_array($profile_image['type'], $allowed_types) && $profile_image['size'] <= 2000000) { // 2MB limit
                    $profile_image_name = time() . '_' . basename($profile_image['name']);
                    $profile_image_path = "./Profile/student_profile/" . $profile_image_name;
                    $uploading_image_path = "student_profile/" . $profile_image_name;

                    if (!is_dir("./Profile/student_profile/")) {
                        mkdir("./Profile/student_profile/", 0777, true);
                    }

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
                    $_SESSION['success_message'] = "Registration successful! Redirecting to login page...";
                    $registration_success = true;
                } else {
                    $errors[] = "Error: " . $stmt->error;
                }
                
                if (isset($registration_success) && $registration_success) {
                    echo "<script>
                        setTimeout(() => {
                            window.location.href = './login_page.php';
                        }, 2000);
                    </script>";
                }

                $stmt->close();
            }
        }
    }

    // If there are validation errors, store them in session
    if (!empty($errors)) {
        $_SESSION['error_message'] = implode("<br>", $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - GD Edu Tech</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="../Images/Logos/GD_Only_logo.png">
    <!-- Google Identity Services JS -->
    <script src="https://accounts.google.com/gsi/client" async defer></script>

    <style>
        * {
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #eef2f7 0%, #d4e0ed 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
            margin: 0;
            overflow: hidden;
        }

        /* Main Outer Glassmorphism Modal Card */
        .auth-card {
            width: 100%;
            max-width: 980px;
            max-height: calc(100vh - 30px);
            background: #ffffff;
            border-radius: 28px;
            box-shadow: 0 25px 60px -15px rgba(15, 23, 42, 0.15);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.8);
            position: relative;
        }

        /* Left Column Illustration Showcase */
        .auth-illustration-side {
            background: #f0f7f9;
            padding: 30px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            height: 100%;
        }

        .auth-illustration-img {
            max-width: 100%;
            height: auto;
            max-height: 350px;
            object-fit: contain;
            filter: drop-shadow(0 15px 25px rgba(0,0,0,0.08));
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        .auth-illustration-text {
            text-align: center;
            margin-top: 18px;
        }

        .auth-illustration-text h4 {
            font-weight: 700;
            color: #0f172a;
            font-size: 1.25rem;
            margin-bottom: 4px;
        }

        .auth-illustration-text p {
            color: #64748b;
            font-size: 0.84rem;
            margin: 0;
        }

        /* Right Form Area */
        .auth-form-side {
            padding: 30px 35px;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            overflow-y: auto;
            max-height: calc(100vh - 30px);
        }

        /* Top Right Corner Login Pill Switch */
        .login-switch-pill {
            position: absolute;
            top: 20px;
            right: 25px;
        }

        .btn-switch-pill {
            border: 1.5px solid #cbd5e1;
            background: #ffffff;
            color: #334155;
            font-weight: 600;
            font-size: 0.82rem;
            padding: 6px 20px;
            border-radius: 50px;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-switch-pill:hover {
            border-color: #0d7298;
            color: #0d7298;
            background: rgba(13, 114, 152, 0.05);
        }

        .btn-back-home {
            color: #64748b;
            font-weight: 600;
            font-size: 0.82rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s ease;
        }

        .btn-back-home:hover {
            color: #0d7298;
            transform: translateX(-3px);
        }

        /* Form Header */
        .auth-header {
            margin-bottom: 16px;
            margin-top: 5px;
        }

        .auth-title {
            font-size: 1.65rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 3px;
        }

        .auth-subtitle {
            color: #64748b;
            font-size: 0.86rem;
        }

        /* Input Styling */
        .custom-input-group {
            position: relative;
            margin-bottom: 12px;
        }

        .custom-input-group .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1rem;
            z-index: 5;
        }

        .custom-input-field {
            width: 100%;
            padding: 10px 16px 10px 44px;
            border-radius: 14px;
            border: 1.5px solid #e2e8f0;
            background: #ffffff;
            font-size: 0.88rem;
            color: #0f172a;
            transition: all 0.3s ease;
        }

        .custom-input-field:focus {
            outline: none;
            border-color: #0d7298;
            box-shadow: 0 0 0 4px rgba(13, 114, 152, 0.1);
        }

        /* Submit Button */
        .btn-auth-action {
            width: 100%;
            padding: 11px;
            background: linear-gradient(135deg, #0d7298 0%, #065d7d 100%);
            color: #ffffff;
            border: none;
            border-radius: 50px;
            font-size: 0.98rem;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 8px 22px rgba(13, 114, 152, 0.28);
            transition: all 0.3s ease;
            margin-top: 4px;
        }

        .btn-auth-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(13, 114, 152, 0.38);
            color: #ffffff;
        }

        /* Google Button Style */
        .btn-google-auth {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 10px 16px;
            background: #ffffff;
            border: 1.5px solid #cbd5e1;
            border-radius: 50px;
            font-size: 0.88rem;
            font-weight: 600;
            color: #334155;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
            margin-bottom: 14px;
            text-decoration: none;
        }

        .btn-google-auth:hover {
            background: #f8fafc;
            border-color: #0d7298;
            color: #0d7298;
            box-shadow: 0 4px 12px rgba(13, 114, 152, 0.15);
        }

        /* Custom File Upload */
        .file-upload-label {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 16px;
            border: 1.5px dashed #cbd5e1;
            border-radius: 14px;
            background: #f8fafc;
            color: #64748b;
            font-size: 0.84rem;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }

        .file-upload-label:hover {
            border-color: #0d7298;
            color: #0d7298;
            background: rgba(13, 114, 152, 0.04);
        }

        /* Password Visibility Toggle */
        .password-toggle-icon {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            cursor: pointer;
            z-index: 5;
            font-size: 1rem;
        }

        /* Divider */
        .auth-divider {
            display: flex;
            align-items: center;
            text-align: center;
            color: #94a3b8;
            font-size: 0.78rem;
            margin: 12px 0;
        }

        .auth-divider::before, .auth-divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e2e8f0;
        }

        .auth-divider span {
            padding: 0 10px;
        }

        /* Responsive */
        @media (max-width: 991px) {
            .auth-illustration-side {
                display: none;
            }
            .auth-form-side {
                padding: 35px 25px;
            }
            .login-switch-pill {
                top: 20px;
                right: 20px;
            }
        }
    </style>
</head>
<body>

<div class="auth-card">
    <div class="row g-0">
        
        <!-- Left Side: Illustration Panel -->
        <div class="col-lg-6 d-none d-lg-block">
            <div class="auth-illustration-side">
                <img src="../Images/Others/sign.png" alt="GD Edu Tech Learning" class="auth-illustration-img">
                <div class="auth-illustration-text">
                    <h4>Start Your Learning Journey</h4>
                    <p>Access world-class courses, certifications, and career support.</p>
                </div>
            </div>
        </div>

        <!-- Right Side: Sign Up Form -->
        <div class="col-lg-6">
            <div class="auth-form-side">
                
                <!-- Top Action Header -->
                <div class="d-flex align-items-center justify-content-between mb-2 position-relative" style="z-index: 10;">
                    <a href="../index.php" class="btn-back-home">
                        <i class="bi bi-arrow-left me-1.5"></i> Back to Home
                    </a>
                    <a href="login_page.php" class="btn-switch-pill">Sign In</a>
                </div>

                <!-- Form Header -->
                <div class="auth-header">
                    <div class="mb-2">
                        <img src="../Images/Logos/GD_Only_logo.png" alt="GD Edu Tech" style="height: 38px;">
                    </div>
                    <h2 class="auth-title">Create Account</h2>
                    <p class="auth-subtitle mb-0">Enter your details to register as a student</p>
                </div>

                <div id="googleAlertContainer"></div>

                <!-- Session Alert Messages -->
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger rounded-4 py-2.5 px-3 mb-3 small" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-1.5"></i>
                        <?php 
                            echo $_SESSION['error_message']; 
                            unset($_SESSION['error_message']);
                        ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success rounded-4 py-2.5 px-3 mb-3 small" role="alert">
                        <i class="bi bi-check-circle-fill me-1.5"></i>
                        <?php 
                            echo $_SESSION['success_message']; 
                            unset($_SESSION['success_message']);
                        ?>
                    </div>
                <?php endif; ?>

                <!-- Registration Form -->
                <form action="signup.php" method="POST" enctype="multipart/form-data">
                    
                    <!-- First & Last Name Row -->
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="custom-input-group">
                                <i class="bi bi-person input-icon"></i>
                                <input type="text" class="custom-input-field" id="first_name" name="first_name" placeholder="First Name *" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="custom-input-group">
                                <i class="bi bi-person input-icon"></i>
                                <input type="text" class="custom-input-field" id="last_name" name="last_name" placeholder="Last Name *" required>
                            </div>
                        </div>
                    </div>

                    <!-- Username Input -->
                    <div class="custom-input-group">
                        <i class="bi bi-at input-icon"></i>
                        <input type="text" class="custom-input-field" id="username" name="username" placeholder="Choose Username *" required>
                    </div>

                    <!-- Email Input -->
                    <div class="custom-input-group">
                        <i class="bi bi-envelope input-icon"></i>
                        <input type="email" class="custom-input-field" id="email" name="email" placeholder="Email Address *" required>
                    </div>

                    <!-- Password Input -->
                    <div class="custom-input-group">
                        <i class="bi bi-lock input-icon"></i>
                        <input type="password" class="custom-input-field" id="password" name="password" placeholder="Password *" required>
                        <i class="bi bi-eye password-toggle-icon" id="togglePassword"></i>
                    </div>

                    <!-- Profile Image Upload (Optional) -->
                    <div class="mb-3">
                        <label for="profile_image" class="file-upload-label mb-0">
                            <i class="bi bi-cloud-arrow-up fs-5 text-primary"></i>
                            <span id="fileNameText">Upload Profile Image (Optional)</span>
                            <input type="file" class="d-none" id="profile_image" name="profile_image" accept="image/*">
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-auth-action">Create Account</button>

                    <!-- OR Divider -->
                    <div class="auth-divider">
                        <span>or join with</span>
                    </div>

                    <!-- Google Sign-In Button -->
                    <div class="google-auth-wrapper text-center">
                        <div id="g_id_onload"
                             data-client_id="267890495884-euub370ds33tgn98rponciu9n8e59snl.apps.googleusercontent.com.apps.googleusercontent.com"
                             data-context="signup"
                             data-ux_mode="popup"
                             data-callback="handleGoogleSignUp"
                             data-auto_prompt="false">
                        </div>

                        <button type="button" class="btn-google-auth" onclick="triggerGoogleSignUpPrompt()">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M23.54 12.28c0-.85-.07-1.7-.22-2.53H12v4.8h6.48c-.28 1.48-1.12 2.74-2.38 3.58v2.96h3.84c2.25-2.07 3.6-5.13 3.6-8.81z" fill="#4285F4"/>
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.84-2.96c-1.08.72-2.45 1.16-4.09 1.16-3.13 0-5.78-2.11-6.73-4.96H1.29v3.06C3.26 21.3 7.31 24 12 24z" fill="#34A853"/>
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M5.27 14.33c-.25-.74-.38-1.53-.38-2.33s.13-1.59.38-2.33V6.61H1.29C.47 8.24 0 10.06 0 12s.47 3.76 1.29 5.39l3.98-3.06z" fill="#FBBC05"/>
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.31 0 3.26 2.7 1.29 6.61l3.98 3.06c.95-2.85 3.6-4.92 6.73-4.92z" fill="#EA4335"/>
                            </svg>
                            <span>Sign up with Google</span>
                        </button>
                    </div>

                    <div class="text-center">
                        <span class="text-muted small">Already have an account?</span>
                        <a href="login_page.php" class="text-primary fw-bold text-decoration-none small ms-1">Sign In</a>
                    </div>
                </form>

            </div>
        </div>

    </div>
</div>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Password visibility toggle
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');

    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });
    }

    // Display selected file name
    const fileInput = document.getElementById('profile_image');
    const fileNameText = document.getElementById('fileNameText');

    if (fileInput && fileNameText) {
        fileInput.addEventListener('change', function () {
            if (this.files && this.files.length > 0) {
                fileNameText.textContent = this.files[0].name;
            } else {
                fileNameText.textContent = 'Upload Profile Image (Optional)';
            }
        });
    }

    // Google Sign-Up Callback Handler
    function handleGoogleSignUp(response) {
        if (!response || !response.credential) return;

        fetch('google_auth.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ credential: response.credential })
        })
        .then(res => res.json())
        .then(data => {
            const alertBox = document.getElementById('googleAlertContainer');
            if (data.status === 'success') {
                alertBox.innerHTML = `
                    <div class="alert alert-success rounded-4 py-2.5 px-3 mb-3 small" role="alert">
                        <i class="bi bi-check-circle-fill me-1.5"></i> ${data.message}
                    </div>
                `;
                setTimeout(() => {
                    window.location.href = data.redirect || 'index.php';
                }, 800);
            } else {
                alertBox.innerHTML = `
                    <div class="alert alert-danger rounded-4 py-2.5 px-3 mb-3 small" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-1.5"></i> ${data.message}
                    </div>
                `;
            }
        })
        .catch(err => {
            console.error('Google Sign-Up Error:', err);
        });
    }

    function triggerGoogleSignUpPrompt() {
        if (window.google && google.accounts && google.accounts.id) {
            google.accounts.id.prompt();
        } else {
            alert("Google Sign-In is loading. Please try again in a moment.");
        }
    }
</script>
</body>
</html>