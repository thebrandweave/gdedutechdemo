<?php
session_start();

// Check if user is logged in and is Staff
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Staff') {
    header('Location: ../staff_login.php');
    exit();
}

// Get staff details from session
$staff_name = $_SESSION['username'] ?? 'Staff';

require_once '../../Configurations/config.php';

// Fetch categories for dropdown
$categories_query = mysqli_query($conn, "SELECT * FROM Categories");

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = [];

    // Validate inputs
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $language = trim($_POST['language']);
    $level = $_POST['level'];
    $category_id = intval($_POST['category_id']);
    $course_type = trim($_POST['course_type']);

    // Thumbnail upload
    $thumbnail = null;
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (in_array($_FILES['thumbnail']['type'], $allowed_types) && $_FILES['thumbnail']['size'] <= $max_size) {
            // Create a URL-friendly version of the course title
            $safe_title = preg_replace('/[^a-z0-9]+/', '-', strtolower($title));
            $safe_title = trim($safe_title, '-');
            
            // Create filename with course title and unique identifier
            $thumbnail_name = $safe_title . '_' . uniqid() . '_' . $_FILES['thumbnail']['name'];
            $upload_path = '../../uploads/course_uploads/thumbnails/' . $thumbnail_name;
            
            if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $upload_path)) {
                $thumbnail = $thumbnail_name;
            } else {
                $errors[] = "Failed to upload thumbnail.";
            }
        } else {
            $errors[] = "Invalid thumbnail file. Must be JPG, PNG, or GIF and under 5MB.";
        }
    }

    // Basic validation
    if (empty($title)) $errors[] = "Title is required.";
    if (empty($description)) $errors[] = "Description is required.";
    if ($price < 0) $errors[] = "Price cannot be negative.";

    if (empty($errors)) {
        // Prepare data for next step
        $_SESSION['course_data'] = [
            'title' => $title,
            'description' => $description,
            'price' => $price,
            'language' => $language,
            'level' => $level,
            'category_id' => $category_id,
            'course_type' => $course_type,
            'thumbnail' => $thumbnail,
            'created_by' => $_SESSION['user_id']
        ];

        header("Location: add_lessons.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Course - Step 1</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* Add this CSS rule to your existing styles */
        .nav-link.active {
            background-color: red; /* Change the background color to red */
            color: white; /* Change text color to white for better contrast */
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 sidebar">
            <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 min-vh-100">
                <a href="#" class="d-flex align-items-center pb-3 mb-md-1 mt-md-3 me-md-auto text-white text-decoration-none">
                    <span class="fs-5 fw-bolder" style="display: flex;align-items:center;color:black;">
                        <img height="35px" src="../../adminPanel/images/edutechLogo.png" alt="">&nbsp; GD Edu Tech
                    </span>
                </a>
                <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100" id="menu">
                    <li class="w-100">
                        <a href="../index.php" class="nav-link text-primary">
                            <i class="bi bi-speedometer2 me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="w-100">
                        <a href="./" class="nav-link active">
                            <i class="bi bi-book me-2"></i> Courses
                        </a>
                    </li>
                    <li class="w-100">
                        <a href="../Quiz/" class="nav-link">
                            <i class="bi bi-lightbulb me-2"></i> Quiz
                        </a>
                    </li>
                    <li class="w-100">
                        <a href="../Messages/index.php" class="nav-link">
                            <i class="bi bi-chat-dots me-2"></i> Messages
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
        <div class="col-md-10">
            <div class="container mt-5">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">Add New Course - Basic Information</div>
                            <div class="card-body">
                                <?php if (!empty($errors)): ?>
                                    <div class="alert alert-danger">
                                        <?php foreach ($errors as $error): ?>
                                            <p><?php echo htmlspecialchars($error); ?></p>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <form method="POST" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label class="form-label">Course Title</label>
                                        <input type="text" name="title" class="form-control" value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" class="form-control" rows="4" required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Price</label>
                                            <input type="number" name="price" step="0.01" class="form-control" value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : '0.00'; ?>" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Language</label>
                                            <input type="text" name="language" class="form-control" value="<?php echo isset($_POST['language']) ? htmlspecialchars($_POST['language']) : 'English'; ?>" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Level</label>
                                            <select name="level" class="form-select" required>
                                                <option value="beginner" <?php echo isset($_POST['level']) && $_POST['level'] == 'beginner' ? 'selected' : ''; ?>>Beginner</option>
                                                <option value="intermediate" <?php echo isset($_POST['level']) && $_POST['level'] == 'intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                                                <option value="advanced" <?php echo isset($_POST['level']) && $_POST['level'] == 'advanced' ? 'selected' : ''; ?>>Advanced</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Category</label>
                                            <select name="category_id" class="form-select" required>
                                                <?php while ($category = mysqli_fetch_assoc($categories_query)): ?>
                                                    <option value="<?php echo $category['category_id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Course Type</label>
                                            <input type="text" name="course_type" class="form-control" value="<?php echo isset($_POST['course_type']) ? htmlspecialchars($_POST['course_type']) : ''; ?>" placeholder="e.g., Video Course, Text Course">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Course Thumbnail</label>
                                        <input type="file" name="thumbnail" class="form-control" accept="image/jpeg,image/png,image/gif">
                                        <small class="form-text text-muted">Max file size: 5MB. Allowed types: JPG, PNG, GIF</small>
                                    </div>

                                    <button type="submit" class="btn btn-danger">Next: Add Lessons</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
