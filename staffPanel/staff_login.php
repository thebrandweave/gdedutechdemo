<?php
$host = $_SERVER['HTTP_HOST'];

if (strpos($host, 'staff.gdedutech.com') !== false) {
    header("Location: https://gdedutech.com/staffPanel/staff_login.php");
}
?>
<?php
require_once '../Configurations/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

session_start();

$jwtSecretKey = "your_secret_key_here";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Check if the username exists and is a staff member
    $stmt = $conn->prepare("SELECT user_id, username, password_hash, role, status FROM Users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Check if the user is a staff member
        if ($user['role'] === 'Staff') {
            // Check user status
            if ($user['status'] !== 'active') {
                echo "<div class='alert alert-warning text-center'>Your account is {$user['status']}. Please contact the administrator.</div>";
            } elseif (password_verify($password, $user['password_hash'])) {
                // Password matches, create a JWT token
                $payload = [
                    'iss' => 'http://localhost',
                    'aud' => 'http://localhost',
                    'iat' => time(),
                    'exp' => time() + 3600,
                    'user_id' => $user['user_id'],
                    'username' => $user['username'],
                    'role' => $user['role']
                ];

                $jwt = JWT::encode($payload, $jwtSecretKey, 'HS256');

                // Store JWT in a cookie
                setcookie("auth_token", $jwt, time() + 3600, "/", "", false, true);

                // Create session variables
                $_SESSION['logged_in'] = true;
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['username'] = $user['username'];

                // Redirect to staff dashboard
                header("Location: index.php");
                exit();
            } else {
                echo "<div class='alert alert-danger text-center'>Invalid username or password.</div>";
            }
        } else {
            echo "<div class='alert alert-danger text-center'>Access restricted. Only staff members can log in here.</div>";
        }
    } else {
        echo "<div class='alert alert-danger text-center'>Invalid username or password.</div>";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login - GD Edu Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-container {
            max-width: 450px;
            margin: 0 auto;
            padding: 40px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        .login-title {
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
    </style>
</head>
<body>
<div class="container">
    <div class="login-container">
        <div class="logo">
            <img src="../Images/Logos/GD_Only_logo.png" alt="GD Edu Tech">
        </div>
        <h2 class="login-title">Staff Login</h2>
        <form action="staff_login.php" method="POST">
            <div class="mb-4">
                <label for="username" class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 