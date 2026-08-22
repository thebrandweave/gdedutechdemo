<?php
$host = $_SERVER['HTTP_HOST'];

if (strpos($host, 'admin.gdedutech.com') !== false) {
    header("Location: https://gdedutech.com/adminPanel/");
    exit();
}

require_once '../Configurations/config.php'; // Include database configuration
require_once __DIR__ . '/../vendor/autoload.php'; // Load Composer dependencies (including JWT)

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

session_start(); // Start a session for user authentication

// Secret key for JWT
$jwtSecretKey = "your_secret_key_here";

$error_msg = '';
$alert_type = 'alert-danger';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error_msg = "Please fill in both username and password.";
        $alert_type = "alert-warning";
    } else {
        // Check if the username exists and is an admin
        $stmt = $conn->prepare("SELECT user_id, username, password_hash, role, status FROM Users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Check if the user is an admin
            if ($user['role'] === 'admin') {
                // Check user status
                if ($user['status'] !== 'active') {
                    $error_msg = "Your account is {$user['status']}. Please contact the system administrator.";
                    $alert_type = "alert-warning";
                } elseif (password_verify($password, $user['password_hash'])) {
                    // Password matches, create a JWT token
                    $payload = [
                        'iss' => 'http://localhost', // Issuer
                        'aud' => 'http://localhost', // Audience
                        'iat' => time(),            // Issued at
                        'exp' => time() + 3600,     // Expiration time (1 hour)
                        'user_id' => $user['user_id'],
                        'username' => $user['username'],
                        'role' => $user['role']
                    ];

                    $jwt = JWT::encode($payload, $jwtSecretKey, 'HS256');

                    // Store JWT in a cookie
                    setcookie("auth_token", $jwt, time() + 3600, "/", "", false, true);

                    // Create a session variable to track login state
                    $_SESSION['logged_in'] = true;
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['username'] = $user['username'];

                    // Redirect to admin dashboard
                    header("Location: index.php");
                    exit();
                } else {
                    $error_msg = "Invalid username or password.";
                    $alert_type = "alert-danger";
                }
            } else {
                $error_msg = "Access restricted. Only administrators can log in here.";
                $alert_type = "alert-danger";
            }
        } else {
            $error_msg = "Invalid username or password.";
            $alert_type = "alert-danger";
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - GD Edu Tech</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="../Images/Logos/GD_Only_logo.png">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0d7298 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        /* Ambient Glow Background Accents */
        .ambient-glow-1 {
            position: absolute;
            top: -10%;
            left: -10%;
            width: 450px;
            height: 450px;
            background: rgba(13, 114, 152, 0.35);
            filter: blur(120px);
            border-radius: 50%;
            z-index: 1;
            pointer-events: none;
        }

        .ambient-glow-2 {
            position: absolute;
            bottom: -10%;
            right: -10%;
            width: 450px;
            height: 450px;
            background: rgba(245, 158, 11, 0.25);
            filter: blur(120px);
            border-radius: 50%;
            z-index: 1;
            pointer-events: none;
        }

        .admin-login-card {
            max-width: 440px;
            width: 100%;
            background: rgba(255, 255, 255, 0.98);
            border-radius: 28px;
            padding: 42px 36px;
            box-shadow: 0 25px 60px rgba(15, 23, 42, 0.35);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            z-index: 10;
            backdrop-filter: blur(16px);
        }

        .brand-logo-circle {
            width: 80px;
            height: 80px;
            border-radius: 24px;
            background: #ffffff;
            box-shadow: 0 10px 25px rgba(13, 114, 152, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px auto;
            border: 1px solid #e2e8f0;
        }

        .brand-logo-circle img {
            max-height: 48px;
            width: auto;
        }

        .portal-badge {
            background: rgba(13, 114, 152, 0.1);
            color: #0d7298;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 4px 14px;
            border-radius: 50px;
            border: 1px solid rgba(13, 114, 152, 0.2);
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 8px;
        }

        .login-title {
            font-size: 1.65rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .login-subtitle {
            font-size: 0.88rem;
            color: #64748b;
            margin-bottom: 28px;
        }

        .form-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }

        .custom-input-group {
            position: relative;
        }

        .custom-input-group .input-group-text {
            background: #f8fafc;
            border: 1.5px solid #cbd5e1;
            border-right: none;
            border-radius: 14px 0 0 14px;
            color: #0d7298;
            font-size: 1.1rem;
            padding: 12px 16px;
        }

        .custom-input-group .form-control {
            border: 1.5px solid #cbd5e1;
            border-left: none;
            border-radius: 0 14px 14px 0;
            padding: 12px 16px;
            font-size: 0.95rem;
            color: #0f172a;
            background: #ffffff;
            transition: all 0.3s ease;
        }

        .custom-input-group .form-control:focus {
            box-shadow: none;
            border-color: #0d7298;
        }

        .custom-input-group:focus-within .input-group-text {
            border-color: #0d7298;
            background: rgba(13, 114, 152, 0.05);
        }

        .input-group-password-toggle {
            border: 1.5px solid #cbd5e1 !important;
            border-left: none !important;
            border-radius: 0 14px 14px 0 !important;
            background: #f8fafc !important;
            color: #64748b;
            cursor: pointer;
            padding: 12px 16px;
        }

        .input-group-password-toggle:hover {
            color: #0d7298;
        }

        .btn-admin-submit {
            background: linear-gradient(135deg, #0d7298 0%, #065d7d 100%);
            color: #ffffff;
            font-size: 1rem;
            font-weight: 700;
            border: none;
            border-radius: 50px;
            padding: 14px 30px;
            box-shadow: 0 10px 25px rgba(13, 114, 152, 0.35);
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 10px;
        }

        .btn-admin-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 30px rgba(13, 114, 152, 0.45);
            color: #ffffff;
        }

        .alert-custom {
            border-radius: 14px;
            font-size: 0.88rem;
            font-weight: 500;
            padding: 12px 16px;
            border: none;
        }

        .portal-footer {
            text-align: center;
            margin-top: 24px;
            color: #94a3b8;
            font-size: 0.78rem;
        }

        .portal-footer a {
            color: #0d7298;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>

    <!-- Ambient Glow BG -->
    <div class="ambient-glow-1"></div>
    <div class="ambient-glow-2"></div>

    <div class="admin-login-card">
        <!-- Brand Logo -->
        <div class="brand-logo-circle">
            <img src="../Images/Logos/GD_Only_logo.png" alt="GD Edu Tech">
        </div>

        <div class="text-center">
        
            <h2 class="login-title">Admin Panel Login</h2>
            <!-- <p class="login-subtitle">Enter your credentials to access GD Edu Tech management</p> -->
        </div>

        <!-- Alert Notification Box -->
        <?php if (!empty($error_msg)): ?>
            <div class="alert <?php echo $alert_type; ?> alert-custom alert-dismissible fade show mb-4" role="alert">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    <span><?php echo htmlspecialchars($error_msg); ?></span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form action="admin_login.php" method="POST" autocomplete="off">
            
            <!-- Username Input -->
            <div class="mb-3.5 mb-3">
                <label for="username" class="form-label">Admin Username</label>
                <div class="input-group custom-input-group">
                    <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" required autofocus>
                </div>
            </div>

            <!-- Password Input -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label for="password" class="form-label mb-0">Password</label>
                </div>
                <div class="input-group custom-input-group">
                    <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                    <input type="password" class="form-control" id="password" name="password" style="border-radius: 0;" placeholder="Enter password" required>
                    <span class="input-group-text input-group-password-toggle" onclick="togglePassword()" title="Toggle password visibility">
                        <i class="bi bi-eye-fill" id="toggleIcon"></i>
                    </span>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-admin-submit">
                <span>LOGIN TO DASHBOARD</span>
                <i class="bi bi-arrow-right-short fs-4 align-middle ms-1"></i>
            </button>
        </form>

        <div class="portal-footer">
        
            <div class="mt-1">
                <a href="../index.php"><i class="bi bi-house-door me-1"></i>Return to Main Website</a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function togglePassword() {
            const password = document.getElementById("password");
            const icon = document.getElementById("toggleIcon");

            if (password.type === "password") {
                password.type = "text";
                icon.classList.remove("bi-eye-fill");
                icon.classList.add("bi-eye-slash-fill");
            } else {
                password.type = "password";
                icon.classList.remove("bi-eye-slash-fill");
                icon.classList.add("bi-eye-fill");
            }
        }
    </script>
</body>
</html>