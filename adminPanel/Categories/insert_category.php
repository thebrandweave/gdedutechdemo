<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../admin_login.php');
    exit();
}

$admin_name = $_SESSION['username'] ?? 'Admin';
require_once '../../Configurations/config.php';

$errors = [];

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize inputs
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // Validate category name
    if (empty($name)) {
        $errors[] = "Category name is required.";
    } elseif (strlen($name) > 100) {
        $errors[] = "Category name cannot exceed 100 characters.";
    }

    // Validate description
    if (strlen($description) > 500) {
        $errors[] = "Description cannot exceed 500 characters.";
    }

    // If no errors, proceed with database insertion
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO Categories (name, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $description);

        try {
            if ($stmt->execute()) {
                $_SESSION['message'] = "Category added successfully.";
                $_SESSION['message_type'] = "success";
                header("Location: index.php");
                exit();
            } else {
                if ($stmt->errno == 1062) {
                    $errors[] = "A category with this name already exists.";
                } else {
                    $errors[] = "Error adding category: " . $stmt->error;
                }
            }
        } catch (Exception $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Category - GD Edu Tech Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="../../Images/Logos/GD_Only_logo.png">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <div class="container-fluid p-0">
        <div class="row g-0 flex-nowrap">
            
            <!-- Executive Sidebar -->
            <div class="col-auto col-md-3 col-xl-2 px-0 sidebar sticky-top vh-100 overflow-auto hide-scrollbar d-flex flex-column">
                <div class="p-3 border-bottom border-white border-opacity-10 d-flex align-items-center gap-2">
                    <img height="36" src="../../Images/Logos/GD_Only_logo.png" alt="GD Logo">
                    <div>
                        <div class="fw-bold text-white fs-6">GD Edu Tech</div>
                      
                    </div>
                </div>

                <ul class="nav nav-pills flex-column mb-auto p-2 w-100" id="menu">
                    <li class="w-100"><a href="../" class="nav-link"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
                    <li class="w-100"><a href="./" class="nav-link active"><i class="bi bi-grid me-2"></i> Categories</a></li>
                    <li class="w-100"><a href="../Admissions/" class="nav-link"><i class="bi bi-person-plus me-2"></i> Student Admission</a></li>
                    <li class="w-100"><a href="../Courses/" class="nav-link"><i class="bi bi-book me-2"></i> Courses</a></li>
                    <li class="w-100"><a href="../Applications/" class="nav-link"><i class="bi bi-journal-text me-2"></i> Scholarships</a></li>
                    <li class="w-100"><a href="../Events/" class="nav-link"><i class="bi bi-calendar2-event me-2"></i> Events</a></li>
                    <li class="w-100"><a href="../social_links.php" class="nav-link"><i class="bi bi-link-45deg me-2"></i> Social Links</a></li>
                    <li class="w-100"><a href="../Schedule/index.php" class="nav-link"><i class="bi bi-calendar-event me-2"></i> Schedule</a></li>
                    <li class="w-100"><a href="../feedback/feedback.php" class="nav-link"><i class="bi bi-chat-square-heart me-2"></i> Feedback</a></li>
                    <li class="w-100"><a href="../Messages/index.php" class="nav-link"><i class="bi bi-chat-dots me-2"></i> Messages</a></li>
                    <li class="w-100"><a href="../FAQ/" class="nav-link"><i class="bi bi-question-circle me-2"></i> FAQ</a></li>
                    <li class="w-100"><a href="../Users/" class="nav-link"><i class="bi bi-people me-2"></i> Users</a></li>
                    <li class="w-100"><a href="../manage_qr.php" class="nav-link"><i class="bi bi-qr-code me-2"></i> Payment QR</a></li>
                    <li class="w-100"><a href="../pending_payments.php" class="nav-link"><i class="bi bi-credit-card me-2"></i> Pending Payments</a></li>
                </ul>

                <div class="p-3 border-top border-white border-opacity-10 mt-auto">
                    <a href="../logout.php" class="nav-link text-danger justify-content-center m-0">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </a>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="col min-vh-100 d-flex flex-column">
                
                <!-- Top Header -->
                <div class="bg-white border-bottom px-4 py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="fw-bold text-dark mb-0">Add New Category</h4>
                        <span class="text-muted small">Create a new course category to organize learning modules</span>
                    </div>

                    <a href="./index.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1.5"></i> Back to Categories
                    </a>
                </div>

                <div class="p-4 flex-grow-1 d-flex justify-content-center align-items-start">
                    
                    <div class="card shadow-sm border-0 rounded-4 p-4 p-md-5 w-100" style="max-width: 680px;">
                        
                        <!-- Alert Errors -->
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                                <ul class="mb-0 ps-3">
                                    <?php foreach ($errors as $error): ?>
                                        <li><strong class="fw-semibold"><?php echo htmlspecialchars($error); ?></strong></li>
                                    <?php endforeach; ?>
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="insert_category.php" autocomplete="off">
                            <div class="mb-4">
                                <label for="name" class="form-label font-weight-semibold text-dark">Category Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-tag-fill"></i></span>
                                    <input type="text" class="form-control border-start-0" id="name" name="name" placeholder="e.g. Web Development, Data Science" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required maxlength="100">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="description" class="form-label font-weight-semibold text-dark">Category Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4" placeholder="Brief summary describing the scope of courses in this category..." maxlength="500"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                                <span class="text-muted small float-end mt-1">Max 500 characters</span>
                            </div>

                            <div class="d-flex justify-content-end gap-2 pt-2">
                                <a href="./index.php" class="btn btn-light px-4 py-2.5 rounded-3">Cancel</a>
                                <button type="submit" class="btn btn-primary px-4 py-2.5 rounded-3 fw-bold">
                                    <i class="bi bi-check-circle-fill me-1.5"></i> Save Category
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>