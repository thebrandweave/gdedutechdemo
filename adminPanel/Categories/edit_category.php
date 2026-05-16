<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: admin_login.php');
    exit();
}

// Get admin details from session
$admin_name = $_SESSION['username'] ?? 'Admin';
?>
<?php

// Database connection
require_once '../../Configurations/config.php';

// Check if category ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['message'] = "Invalid category ID.";
    $_SESSION['message_type'] = "danger";
    header("Location: ./");
    exit();
}

$category_id = intval($_GET['id']);

// Fetch existing category details
$stmt = $conn->prepare("SELECT * FROM Categories WHERE category_id = ?");
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if category exists
if ($result->num_rows === 0) {
    $_SESSION['message'] = "Category not found.";
    $_SESSION['message_type'] = "danger";
    header("Location: ./");
    exit();
}

$category = $result->fetch_assoc();
$stmt->close();

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize inputs
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);

    // Input validation
    $errors = [];

    // Validate category name
    if (empty($name)) {
        $errors[] = "Category name is required.";
    } elseif (strlen($name) > 100) {
        $errors[] = "Category name cannot exceed 100 characters.";
    }

    // Validate description (optional, but limit length if provided)
    if (strlen($description) > 500) {
        $errors[] = "Description cannot exceed 500 characters.";
    }

    // If no errors, proceed with database update
    if (empty($errors)) {
        // Prepare SQL statement to prevent SQL injection
        $stmt = $conn->prepare("UPDATE Categories SET name = ?, description = ? WHERE category_id = ?");
        $stmt->bind_param("ssi", $name, $description, $category_id);

        try {
            if ($stmt->execute()) {
                $_SESSION['message'] = "Category updated successfully.";
                $_SESSION['message_type'] = "success";
                header("Location: ./");
                exit();
            } else {
                // Check for duplicate entry
                if ($stmt->errno == 1062) {
                    $errors[] = "A category with this name already exists.";
                } else {
                    $errors[] = "Error updating category: " . $stmt->error;
                }
            }
        } catch (Exception $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }

        $stmt->close();
    }
} else {
    // If not a POST request, use existing category data
    $name = $category['name'];
    $description = $category['description'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Category - GD Edu Tech</title>
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
            <span class="fs-5 fw-bolder" style="display: flex;align-items:center;color:black;"><img height="35px" src="../images/edutechLogo.png" alt="">&nbsp; GD Edu Tech</span>
        </a>
        <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100" id="menu">
            <li class="w-100">
                <a href="../" class="nav-link">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>
            <li class="w-100">
                <a href="../Categories/" class="nav-link active">
                    <i class="bi bi-grid me-2"></i> Categories
                </a>
            </li>
            <li class="w-100">
                <a href="../Courses/" class="nav-link">
                    <i class="bi bi-book me-2"></i> Courses
                </a>
            </li>
            <li class="w-100">
                <a href="../Blogs/" class="nav-link">
                    <i class="bi bi-journal-text me-2"></i> Blogs
                </a>
            </li>
            <li class="w-100">
                <a href="../Events/" class="nav-link">
                    <i class="bi bi-calendar2-event me-2"></i> Events
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
                <a href="../Schedule/index.php" class="nav-link">
                    <i class="bi bi-calendar-event me-2"></i> Schedule
                </a>
            </li>
            <li class="w-100">
                <a href="../Messages/index.php" class="nav-link">
                    <i class="bi bi-chat-dots me-2"></i> Messages
                </a>
            </li>
            <li class="w-100">
                <a href="../FAQ/" class="nav-link">
                    <i class="bi bi-question-circle me-2"></i> FAQ
                </a>
            </li>
            <li class="w-100">
                <a href="../Users/" class="nav-link">
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
                            <h2>Edit Category</h2>
                            <p class="text-muted">Modify existing course category</p>
                        </div>
                        <div class="col-auto">
                            <a href="./" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Back to Categories
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

                    <!-- Category Edit Form -->
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . $category_id); ?>">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="name" 
                                               name="name" 
                                               required 
                                               maxlength="100"
                                               value="<?php echo htmlspecialchars($name); ?>"
                                        >
                                        <small class="form-text text-muted">Maximum 100 characters</small>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea 
                                            class="form-control" 
                                            id="description" 
                                            name="description" 
                                            rows="4" 
                                            maxlength="500"
                                        ><?php echo htmlspecialchars($description); ?></textarea>
                                        <small class="form-text text-muted">Optional. Maximum 500 characters</small>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-save me-2"></i>Update Category
                                        </button>
                                        <a href="./" class="btn btn-secondary ms-2">Cancel</a>
                                    </div>
                                </div>
                                
                                <!-- Additional Category Info -->
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <div class="text-muted">
                                            <small>
                                                <strong>Created:</strong> <?php echo date('Y-m-d H:i', strtotime($category['created_at'])); ?><br>
                                                <strong>Last Updated:</strong> <?php echo date('Y-m-d H:i', strtotime($category['updated_at'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Optional: Confirm navigation away with unsaved changes
        document.addEventListener('DOMContentLoaded', function() {
            var form = document.querySelector('form');
            var initialData = {
                name: document.getElementById('name').value,
                description: document.getElementById('description').value
            };

            form.addEventListener('input', function() {
                var currentData = {
                    name: document.getElementById('name').value,
                    description: document.getElementById('description').value
                };

                window.onbeforeunload = (JSON.stringify(initialData) !== JSON.stringify(currentData)) 
                    ? function() { return "You have unsaved changes. Are you sure you want to leave?"; } 
                    : null;
            });
        });
    </script>
</body>

</html>