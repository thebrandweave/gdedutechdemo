<?php
require_once '../Configurations/config.php';
$error_message = '';
$show_form = false;

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Verify token
    $stmt = $conn->prepare("SELECT user_id, expiry FROM password_resets WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $reset = $result->fetch_assoc();
        if ($reset['expiry'] > time()) {
            $show_form = true;
        } else {
            $error_message = "This token has expired.";
        }
    } else {
        $error_message = "Invalid or expired token.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - GD Edu Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #fcfcfc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        .reset-container {
            max-width: 500px;
            margin: 2rem auto;
            padding: 2.5rem;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
        }

        .logo {
            text-align: center;
            margin-bottom: 2rem;
            transition: transform 0.3s ease;
        }

        .logo:hover {
            transform: scale(1.05);
        }

        .logo img {
            height: 80px;
            object-fit: contain;
        }

        .reset-title {
            font-size: 2rem;
            font-weight: 700;
            color: #2C3E50;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .form-floating {
            margin-bottom: 1.5rem;
        }

        .form-control {
            border-radius: 12px;
            padding: 1rem;
            border: 2px solid #e1e1e1;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
        }

        .btn-reset {
            width: 100%;
            padding: 0.8rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }

        .error-message {
            background: #fee2e2;
            color: #dc2626;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .back-to-login {
            text-align: center;
            margin-top: 1.5rem;
        }

        .back-to-login a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .back-to-login a:hover {
            color: #764ba2;
            transform: translateX(-5px);
        }

        .password-requirements {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 12px;
        }

        .password-requirements ul {
            margin-bottom: 0;
            padding-left: 1.2rem;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="reset-container">
            <div class="logo">
                <img src="../Images/Logos/GD_Only_logo.png" alt="GD Edu Tech" class="img-fluid">
            </div>

            <h2 class="reset-title">Reset Your Password</h2>

            <?php if ($error_message): ?>
                <div class="error-message">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <?php if ($show_form): ?>
                <div class="password-requirements">
                    <strong><i class="bi bi-shield-lock me-2"></i>Password Requirements:</strong>
                    <ul>
                        <li>At least 8 characters long</li>
                        <li>Must contain at least one uppercase letter</li>
                        <li>Must contain at least one number</li>
                        <li>Must contain at least one special character</li>
                    </ul>
                </div>

                <form action="reset_password.php?token=<?php echo htmlspecialchars($token); ?>" method="POST">
                    <div class="form-floating">
                        <input type="password" class="form-control" id="password" name="password"
                            required placeholder="Enter new password"
                            pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$">
                        <label for="password">New Password</label>
                    </div>

                    <div class="form-floating mb-4">
                        <input type="password" class="form-control" id="confirm_password"
                            required placeholder="Confirm new password">
                        <label for="confirm_password">Confirm Password</label>
                    </div>

                    <button type="submit" class="btn btn-reset">
                        <i class="bi bi-check2-circle me-2"></i>Reset Password
                    </button>
                </form>
            <?php endif; ?>

            <div class="back-to-login">
                <a href="login.php">
                    <i class="bi bi-arrow-left"></i>
                    Back to Login
                </a>
            </div>
        </div>
    </div>

    <script>
        // Password confirmation validation
        document.querySelector('form')?.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>