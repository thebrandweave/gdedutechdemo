<?php
session_start();

// Database connection
require_once '../../Configurations/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../admin_login.php');
    exit();
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize inputs
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $email = trim($_POST['email']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $role = trim($_POST['role']);

    // Input validation
    $errors = [];

    // Validate username
    if (empty($username)) {
        $errors[] = "Username is required.";
    } elseif (strlen($username) > 100) {
        $errors[] = "Username cannot exceed 100 characters.";
    }

    // Validate password
    if (empty($password)) {
        $errors[] = "Password is required.";
    }

    // Validate email
    if (empty($email)) {
        $errors[] = "Email is required.";
    }

    // Validate first name
    if (empty($first_name)) {
        $errors[] = "First name is required.";
    }

    // Validate last name
    if (empty($last_name)) {
        $errors[] = "Last name is required.";
    }

    // If no errors, proceed with database insertion
    if (empty($errors)) {
        // Hash the password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Check if user already exists
        $stmt = $conn->prepare("SELECT * FROM Users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $errors[] = "A user with this username or email already exists.";
        } else {
            // Prepare SQL statement to insert user into the database
            $stmt = $conn->prepare("INSERT INTO Users (username, password_hash, email, first_name, last_name, role) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $username, $password_hash, $email, $first_name, $last_name, $role);

            try {
                if ($stmt->execute()) {
                    $_SESSION['message'] = "User added successfully.";
                    $_SESSION['message_type'] = "success";
                    header("Location: ./");
                    exit();
                } else {
                    $errors[] = "Error adding user: " . $stmt->error;
                }
            } catch (Exception $e) {
                $errors[] = "Database error: " . $e->getMessage();
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New User - GD Edu Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
        
<div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 sidebar">
    <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 min-vh-100">
        <a href="#" class="d-flex align-items-center pb-3 mb-md-1 mt-md-3 me-md-auto text-white text-decoration-none">
            <span class="fs-5 fw-bolder" style="display: flex;align-items:center;color:black;">
                <img height="35px" src="../images/edutechLogo.png" alt="">&nbsp; GD Edu Tech
            </span>
        </a>
        <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100" id="menu">
            <li class="w-100">
                <a href="../" class="nav-link">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>
            <li class="w-100">
                <a href="../Categories/" class="nav-link">
                    <i class="bi bi-grid me-2"></i> Categories
                </a>
            </li>
            <li class="w-100">
                <a href="../Courses/" class="nav-link">
                    <i class="bi bi-book me-2"></i> Courses
                </a>
            </li>
            <li class="w-100 dropdown">
                            <a href="#" class="nav-link dropdown-toggle" id="quizDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-lightbulb me-2"></i> Quick Links
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="quizDropdown">
                                <li><a class="dropdown-item" href="../Career/index.php">Career portal</a></li>
                                <li><a class="dropdown-item" href="../Shop/shop.php">Shop</a></li>
                            </ul>
                        </li>
            <li class="w-100">
                <a href="../Schedule/" class="nav-link">
                    <i class="bi bi-calendar-event me-2"></i> Schedule
                </a>
            </li>
            <li class="w-100">
                <a href="../Messages/" class="nav-link">
                    <i class="bi bi-chat-dots me-2"></i> Messages
                </a>
            </li>
            <li class="w-100">
                <a href="../FAQ/" class="nav-link">
                    <i class="bi bi-question-circle me-2"></i> FAQ
                </a>
            </li>
            <li class="w-100">
                <a href="./" class="nav-link active">
                    <i class="bi bi-people me-2"></i> Users
                </a>
            </li>
            <li class="w-100">
                <a href="../manage_qr.php" class="nav-link">
                    <i class="bi bi-qr-code me-2"></i> Payment QR
                </a>
            </li>
            <li class="w-100">
                <a href="../pending_payments.php" class="nav-link">
                    <i class="bi bi-credit-card me-2"></i> Pending Payments
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
                <div class="container-fluid">
                    <!-- Header -->
                    <div class="row mb-4">
                        <div class="col">
                            <h2>Add New User</h2>
                            <p class="text-muted">Create a new user for the system</p>
                        </div>
                        <div class="col-auto">
                            <a href="./" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Back to Users
                            </a>
                        </div>
                    </div>

                    <!-- Error Messages -->
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php foreach ($errors as $error): ?>
                                <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
                            <?php endforeach; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- User Add Form -->
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="username" 
                                               name="username" 
                                               required 
                                               maxlength="100"
                                               value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>"
                                        >
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" 
                                               class="form-control" 
                                               id="email" 
                                               name="email" 
                                               required 
                                               maxlength="150"
                                               value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"
                                        >
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="first_name" 
                                               name="first_name" 
                                               required 
                                               maxlength="100"
                                               value="<?php echo isset($first_name) ? htmlspecialchars($first_name) : ''; ?>"
                                        >
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="last_name" 
                                               name="last_name" 
                                               required 
                                               maxlength="100"
                                               value="<?php echo isset($last_name) ? htmlspecialchars($last_name) : ''; ?>"
                                        >
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                    <input type="password" 
                                           class="form-control" 
                                           id="password" 
                                           name="password" 
                                           required 
                                           minlength="6"
                                    >
                                </div>
                                <div class="mb-3">
                                    <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                                    <select class="form-select" id="role" name="role" required>
                                        <option value="admin" <?php echo isset($role) && $role == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                        <option value="staff" <?php echo isset($role) && $role == 'staff' ? 'selected' : ''; ?>>Staff</option>
                                        <option value="student" <?php echo isset($role) && $role == 'student' ? 'selected' : ''; ?>>Student</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Add User</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
</body>

</html>