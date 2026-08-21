<?php
require_once '../Configurations/config.php'; // Include database configuration
require_once '../vendor/autoload.php'; // Load Composer dependencies (including JWT)

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

session_start(); // Start a session for user authentication

// Secret key for JWT
$jwtSecretKey = "your_secret_key_here";

$error_msg = '';
$warning_msg = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error_msg = "Please fill in all fields.";
    } else {
        // Check if the username or email exists
        $stmt = $conn->prepare("SELECT user_id, username, password_hash, role, status FROM Users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Check user status
            if ($user['status'] !== 'active') {
                $warning_msg = "Your account is " . htmlspecialchars($user['status']) . ". Please contact the administrator.";
            } elseif (password_verify($password, $user['password_hash'])) {
                // Password matches, create a JWT token
                $payload = [
                    'iss' => 'http://localhost', // Issuer
                    'aud' => 'http://localhost', // Audience
                    'iat' => time(),            // Issued at
                    'exp' => time() + 86400,    // Expiration time (24 hours)
                    'user_id' => $user['user_id'],
                    'username' => $user['username'],
                    'role' => $user['role']
                ];

                $jwt = JWT::encode($payload, $jwtSecretKey, 'HS256');

                // Store JWT in a cookie
                setcookie("auth_token", $jwt, time() + 86400, "/", "", false, true);

                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Redirect based on user role
                if ($user['role'] === 'admin') {
                    header("Location: ../adminPanel/");
                } elseif ($user['role'] === 'Staff') {
                    header("Location: ../staffPanel/");
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                $error_msg = "Invalid username or password.";
            }
        } else {
            $error_msg = "Invalid username or password.";
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
    <title>Login - GD Edu Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        body {
            background-color: #cbe8fe;
            background: linear-gradient(135deg, #cbe8fe 0%, #b3dcff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 40px 15px;
        }
        .login-wrapper {
            position: relative;
            width: 100%;
            max-width: 410px;
            margin: 0 auto;
        }
        
        /* Owl SVG positioning perched on card */
        .owl-container {
            width: 100%;
            display: flex;
            justify-content: center;
            margin-bottom: -32px;
            position: relative;
            z-index: 10;
            pointer-events: none;
        }
        .owl-svg {
            width: 190px;
            height: 160px;
            filter: drop-shadow(0px 4px 6px rgba(0, 40, 80, 0.08));
        }

        /* Card styles */
        .login-card {
            background: #ffffff;
            border-radius: 28px;
            padding: 48px 36px 36px 36px;
            box-shadow: 0 20px 45px rgba(28, 92, 148, 0.12);
            position: relative;
            z-index: 5;
        }

        .form-group {
            margin-bottom: 20px;
        }
        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 8px;
        }
        .form-control-custom {
            width: 100%;
            padding: 14px 16px;
            font-size: 15px;
            color: #1e293b;
            background-color: #f1f5f9;
            border: 2px solid #f1f5f9;
            border-radius: 14px;
            outline: none;
            transition: all 0.2s ease-in-out;
        }
        .form-control-custom::placeholder {
            color: #94a3b8;
        }
        .form-control-custom:focus {
            background-color: #ffffff;
            border-color: #38bdf8;
            box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.15);
        }

        .password-field-wrapper {
            position: relative;
        }
        .password-toggle-btn {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #64748b;
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            font-size: 18px;
        }
        .password-toggle-btn:hover {
            color: #0284c7;
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            font-size: 16px;
            font-weight: 600;
            color: #ffffff;
            background: #00a8ff;
            background: linear-gradient(180deg, #18b6ff 0%, #0096e6 100%);
            border: none;
            border-radius: 50px;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(0, 168, 255, 0.35);
            transition: all 0.2s ease;
            margin-top: 10px;
            margin-bottom: 20px;
        }
        .btn-submit:hover {
            background: linear-gradient(180deg, #33beff 0%, #0087d1 100%);
            transform: translateY(-1px);
            box-shadow: 0 10px 24px rgba(0, 168, 255, 0.45);
        }
        .btn-submit:active {
            transform: translateY(1px);
            box-shadow: 0 4px 12px rgba(0, 168, 255, 0.3);
        }

        .forgot-password-link {
            display: block;
            text-align: center;
            color: #64748b;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        .forgot-password-link:hover {
            color: #0284c7;
            text-decoration: underline;
        }

        .signup-footer {
            margin-top: 24px;
            padding-top: 18px;
            border-top: 1px solid #f1f5f9;
            text-align: center;
            font-size: 14px;
            color: #64748b;
        }
        .signup-footer a {
            color: #00a8ff;
            font-weight: 600;
            text-decoration: none;
        }
        .signup-footer a:hover {
            text-decoration: underline;
        }

        .alert-custom {
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 14px;
            margin-bottom: 18px;
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <!-- SVG Owl illustration sitting on top of card -->
    <div class="owl-container">
        <svg class="owl-svg" viewBox="0 0 200 170" fill="none" xmlns="http://www.w3.org/2000/svg">
            <!-- Ears / Ear tufts -->
            <path d="M42 48C30 20 18 10 18 10C18 10 38 18 52 30" stroke="#134e7a" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round" fill="#2196f3"/>
            <path d="M158 48C170 20 182 10 182 10C182 10 162 18 148 30" stroke="#134e7a" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round" fill="#2196f3"/>

            <!-- Main Body & Head -->
            <path d="M100 18C52 18 28 48 28 92C28 128 48 144 100 144C152 144 172 128 172 92C172 48 148 18 100 18Z" fill="#2196f3" stroke="#134e7a" stroke-width="5"/>
            
            <!-- Belly Feather outline -->
            <path d="M58 85C58 120 74 136 100 136C126 136 142 120 142 85C142 78 138 72 100 72C62 72 58 78 58 85Z" fill="#64b5f6" opacity="0.6"/>

            <!-- Wings -->
            <path d="M28 85C24 105 34 125 45 130" stroke="#134e7a" stroke-width="4" stroke-linecap="round"/>
            <path d="M172 85C176 105 166 125 155 130" stroke="#134e7a" stroke-width="4" stroke-linecap="round"/>

            <!-- Big Eye Circles (White) -->
            <circle cx="68" cy="62" r="30" fill="#ffffff" stroke="#134e7a" stroke-width="5"/>
            <circle cx="132" cy="62" r="30" fill="#ffffff" stroke="#134e7a" stroke-width="5"/>

            <!-- Eye Pupils (Looking down/inward towards input fields) -->
            <circle cx="74" cy="65" r="16" fill="#15202b"/>
            <circle cx="126" cy="65" r="16" fill="#15202b"/>

            <!-- Pupil Eye Highlights -->
            <circle cx="69" cy="60" r="6" fill="#ffffff"/>
            <circle cx="79" cy="71" r="2.5" fill="#ffffff"/>
            <circle cx="121" cy="60" r="6" fill="#ffffff"/>
            <circle cx="131" cy="71" r="2.5" fill="#ffffff"/>

            <!-- Beak -->
            <path d="M100 66L90 82C90 82 96 90 100 90C104 90 110 82 110 82L100 66Z" fill="#ff9800" stroke="#134e7a" stroke-width="4.5" stroke-linejoin="round"/>

            <!-- Feet (Claws clutching the card edge) -->
            <!-- Left Foot -->
            <g>
                <rect x="62" y="138" width="12" height="22" rx="6" fill="#ff9800" stroke="#134e7a" stroke-width="4"/>
                <rect x="76" y="138" width="12" height="24" rx="6" fill="#ff9800" stroke="#134e7a" stroke-width="4"/>
                <rect x="90" y="138" width="12" height="20" rx="6" fill="#ff9800" stroke="#134e7a" stroke-width="4"/>
            </g>
            <!-- Right Foot -->
            <g>
                <rect x="102" y="138" width="12" height="20" rx="6" fill="#ff9800" stroke="#134e7a" stroke-width="4"/>
                <rect x="116" y="138" width="12" height="24" rx="6" fill="#ff9800" stroke="#134e7a" stroke-width="4"/>
                <rect x="130" y="138" width="12" height="22" rx="6" fill="#ff9800" stroke="#134e7a" stroke-width="4"/>
            </g>
        </svg>
    </div>

    <!-- Main Login Card -->
    <div class="login-card">
        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger alert-custom text-center" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error_msg) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($warning_msg)): ?>
            <div class="alert alert-warning alert-custom text-center" role="alert">
                <i class="bi bi-exclamation-circle-fill me-2"></i><?= htmlspecialchars($warning_msg) ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <!-- Email / Username Input -->
            <div class="form-group">
                <label for="username" class="form-label">Email</label>
                <input 
                    type="text" 
                    class="form-control-custom" 
                    id="username" 
                    name="username" 
                    placeholder="jane@framer.com" 
                    required 
                    autocomplete="username" 
                    value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                >
            </div>

            <!-- Password Input -->
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <div class="password-field-wrapper">
                    <input 
                        type="password" 
                        class="form-control-custom" 
                        id="password" 
                        name="password" 
                        placeholder="••••••••" 
                        required 
                        autocomplete="current-password"
                    >
                    <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility()" aria-label="Toggle password visibility">
                        <i class="bi bi-eye" id="toggleIcon"></i>
                    </button>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn-submit">Login</button>

            <!-- Forgot Password Link -->
            <a href="forget_password.php" class="forgot-password-link">I forgot my password.</a>

            <!-- Sign up option -->
            <div class="signup-footer">
                Don't have an account? <a href="signup.php">Sign up</a>
            </div>
        </form>
    </div>
</div>

<script>
function togglePasswordVisibility() {
    const passwordInput = document.getElementById("password");
    const icon = document.getElementById("toggleIcon");

    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        icon.classList.remove("bi-eye");
        icon.classList.add("bi-eye-slash");
    } else {
        passwordInput.type = "password";
        icon.classList.remove("bi-eye-slash");
        icon.classList.add("bi-eye");
    }
}
</script>
</body>
</html>
