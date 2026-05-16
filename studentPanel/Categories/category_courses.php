<?php
session_start();
require_once '../../Configurations/config.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

// Check if category ID is provided
if (!isset($_GET['id'])) {
    header("Location: course_category.php");
    exit();
}

$category_id = intval($_GET['id']);

// Fetch category details
$category_query = "SELECT name, description FROM Categories WHERE category_id = ?";
$category_stmt = $conn->prepare($category_query);
$category_stmt->bind_param("i", $category_id);
$category_stmt->execute();
$category_result = $category_stmt->get_result();

if ($category_result->num_rows === 0) {
    header("Location: course_category.php");
    exit();
}

$category = $category_result->fetch_assoc();

// Add this after fetching category details and before the courses query
$where_conditions = ["category_id = ? AND status = 'published'"];
$params = [$category_id];
$types = "i";

// Handle filters
$price_min = isset($_GET['price_min']) ? floatval($_GET['price_min']) : null;
$price_max = isset($_GET['price_max']) ? floatval($_GET['price_max']) : null;
$level = isset($_GET['level']) ? $_GET['level'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($price_min !== null) {
    $where_conditions[] = "price >= ?";
    $params[] = $price_min;
    $types .= "d";
}

if ($price_max !== null) {
    $where_conditions[] = "price <= ?";
    $params[] = $price_max;
    $types .= "d";
}

if ($level) {
    $where_conditions[] = "level = ?";
    $params[] = $level;
    $types .= "s";
}

if ($search) {
    $where_conditions[] = "(title LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ss";
}

// Update the courses query with filters
$courses_query = "
    SELECT 
        course_id,
        title,
        description,
        thumbnail,
        price,
        level
    FROM Courses
    WHERE " . implode(" AND ", $where_conditions);

$courses_stmt = $conn->prepare($courses_query);
$courses_stmt->bind_param($types, ...$params);
$courses_stmt->execute();
$courses_result = $courses_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($category['name']); ?> Courses - GD Edu Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/css/student_dashboard.css">
    <style>
        .course-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 15px;
            overflow: hidden;
            height: 450px;
        }
        
        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .course-thumbnail {
            height: 200px;
            object-fit: cover;
        }

        .category-header {
            background: linear-gradient(135deg, #2C3E50, #3498db);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 10px;
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #0d7298, #0d7298, #1d91bb) !important;
            border-color: var(--accent-color) !important;
        }

        .filter-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .filter-section .form-label {
            font-weight: 500;
            color: #6c757d;
        }
        
        .filter-section .form-control,
        .filter-section .form-select {
            border-radius: 8px;
        }
        
        .filter-section .btn {
            border-radius: 8px;
            padding: 8px 15px;
        }

        /* Styles for the fixed sidebar */
        #sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 70vw; /* Sidebar width */
            height: 100vh; /* Full height */
            background-color: #2c3e50; /* Sidebar background color */
            z-index: 1000; /* Ensure sidebar is above other content */
            transform: translateX(-100%); /* Initially hidden */
            transition: transform 0.3s ease; /* Smooth transition */
        }
        #sidebar.show {
            transform: translateX(0); /* Show sidebar */
        }
        .main-content.hidden {
            display: none; /* Hide main content when sidebar is open */
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar for mobile -->
            <div class="col-auto d-md-none">
                <button class="btn btn-primary" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>
            </div>
            <div class="col-auto sidebar" id="sidebar">
                <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 min-vh-100">
                    <a href="#" class="d-flex align-items-center pb-3 mb-md-1 mt-md-3 me-md-auto text-white text-decoration-none">
                        <span class="fs-5 fw-bolder" style="display: flex;align-items:center;"><img height="35px" src="../../Images/Logos/edutechLogo.png" alt="">&nbsp; GD Edu Tech</span>
                    </a>
                    <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100" id="menu">
                        <li class="w-100">
                            <a href="../" class="nav-link text-white">
                                <i class="bi bi-speedometer2 me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../MyCourses/" class="nav-link text-white">
                                <i class="bi bi-book me-2"></i> My Courses
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Categories/" class="nav-link active">
                                <i class="bi bi-grid me-2"></i> Categories
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Schedule" class="nav-link text-white">
                                <i class="bi bi-calendar-event me-2"></i> Schedule
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Messages" class="nav-link text-white">
                                <i class="bi bi-chat-dots me-2"></i> Messages
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Profile/" class="nav-link text-white">
                                <i class="bi bi-person me-2"></i> Profile
                            </a>
                        </li>
                        <li class="w-100 mt-auto">
                            <a href="../../logout.php" class="nav-link text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col py-3 mainContent">
                <!-- Category Header -->
                <div class="category-header mb-4">
                    <div class="container">
                        <h2><?php echo htmlspecialchars($category['name']); ?> Courses</h2>
                        <p class="text-light mb-0"><?php echo htmlspecialchars($category['description']); ?></p>
                    </div>
                </div>

                <!-- Add this before the courses grid -->
                <div class="container mb-4">
                    <div class="card">
                        <div class="card-body">
                            <form method="GET" class="row g-3">
                                <input type="hidden" name="id" value="<?php echo $category_id; ?>">
                                
                                <div class="col-md-3">
                                    <label class="form-label">Search</label>
                                    <input type="text" class="form-control" name="search" 
                                           value="<?php echo htmlspecialchars($search); ?>" 
                                           placeholder="Search courses...">
                                </div>
                                
                                <div class="col-md-2">
                                    <label class="form-label">Min Price</label>
                                    <input type="number" class="form-control" name="price_min" 
                                           value="<?php echo $price_min; ?>" 
                                           placeholder="Min ₹">
                                </div>
                                
                                <div class="col-md-2">
                                    <label class="form-label">Max Price</label>
                                    <input type="number" class="form-control" name="price_max" 
                                           value="<?php echo $price_max; ?>" 
                                           placeholder="Max ₹">
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label">Level</label>
                                    <select class="form-select" name="level">
                                        <option value="">All Levels</option>
                                        <option value="Beginner" <?php echo $level === 'Beginner' ? 'selected' : ''; ?>>Beginner</option>
                                        <option value="Intermediate" <?php echo $level === 'Intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                                        <option value="Advanced" <?php echo $level === 'Advanced' ? 'selected' : ''; ?>>Advanced</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-2 d-flex align-items-end">
                                    <div class="d-grid gap-2 w-100">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-filter"></i> Filter
                                        </button>
                                        <a href="?id=<?php echo $category_id; ?>" class="btn btn-outline-secondary">
                                            <i class="bi bi-x-circle"></i> Clear
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Courses Grid -->
                <div class="container">
                    <div class="row g-4">
                        <?php while ($course = $courses_result->fetch_assoc()): ?>
                            <div class="col-md-4">
                                <div class="card course-card">
                                    <img src="../../uploads/course_uploads/thumbnails/<?php echo htmlspecialchars($course['thumbnail']); ?>" 
                                         class="card-img-top course-thumbnail" 
                                         alt="<?php echo htmlspecialchars($course['title']); ?>">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($course['title']); ?></h5>
                                        <p class="card-text text-muted">
                                            <?php 
                                            // Limit description to 100 characters
                                            $description = htmlspecialchars($course['description']);
                                            echo strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description; 
                                            ?>
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($course['level']); ?></span>
                                            <span class="text-primary">₹<?php echo number_format($course['price'], 2); ?></span>
                                        </div>
                                        <a href="../MyCourses/course.php?id=<?php echo $course['course_id']; ?>" class="btn btn-primary mt-3 w-100">
                                            View Course
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const sidebar = document.getElementById('sidebar');
        const toggleButton = document.getElementById('sidebarToggle');

        toggleButton.addEventListener('click', function() {
            sidebar.classList.toggle('show'); // Toggle sidebar visibility
        });

        // Close sidebar when clicking outside of it
        document.addEventListener('click', function(event) {
            if (!sidebar.contains(event.target) && !toggleButton.contains(event.target) && sidebar.classList.contains('show')) {
                sidebar.classList.remove('show'); // Hide sidebar
            }
        });
    </script>
</body>
</html>
