<?php
session_start();
require_once '../../Configurations/config.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login_page.php");
    exit();
}

// Check if category ID is provided
if (!isset($_GET['id'])) {
    header("Location: ./");
    exit();
}

$category_id = intval($_GET['id']);

// Fetch category details
$category_query = "SELECT name, description FROM Categories WHERE category_id = ?";
$category_stmt = $conn->prepare($category_query);
$category_stmt->bind_param("i", $category_id);
$category_stmt->execute();
$category_result = $category_stmt->get_result();

if (!$category_result || $category_result->num_rows === 0) {
    header("Location: ./");
    exit();
}

$category = $category_result->fetch_assoc();

// Filter parameters
$where_conditions = ["category_id = ? AND status = 'published'"];
$params = [$category_id];
$types = "i";

// Handle filters
$price_min = isset($_GET['price_min']) && $_GET['price_min'] !== '' ? floatval($_GET['price_min']) : null;
$price_max = isset($_GET['price_max']) && $_GET['price_max'] !== '' ? floatval($_GET['price_max']) : null;
$level = isset($_GET['level']) ? trim($_GET['level']) : '';
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

if ($level !== '') {
    $where_conditions[] = "level = ?";
    $params[] = $level;
    $types .= "s";
}

if ($search !== '') {
    $where_conditions[] = "(title LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ss";
}

// Fetch courses matching filter parameters
$courses_query = "
    SELECT 
        course_id,
        title,
        description,
        thumbnail,
        price,
        level,
        course_type
    FROM Courses
    WHERE " . implode(" AND ", $where_conditions) . "
    ORDER BY course_id DESC
";

$courses_stmt = $conn->prepare($courses_query);
$courses_stmt->bind_param($types, ...$params);
$courses_stmt->execute();
$courses_result = $courses_stmt->get_result();
$course_count = $courses_result ? $courses_result->num_rows : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($category['name']); ?> Courses - GD Edu Tech</title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="../../Images/Logos/GD_Only_logo.png">

    <style>
        :root {
            --admin-primary: #0d7298;
            --admin-primary-dark: #065d7d;
            --admin-dark-bg: #0f172a;
            --admin-dark-surface: #1e293b;
            --admin-light-bg: #f8fafc;
            --admin-border-color: #e2e8f0;
        }

        * {
            font-family: 'Poppins', sans-serif;
            box-sizing: border-box;
        }

        body {
            background-color: var(--admin-light-bg);
            color: #0f172a;
            overflow-x: hidden;
        }

        /* Sidebar Styling */
        .sidebar {
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%) !important;
            min-height: 100vh;
            color: #ffffff;
            box-shadow: 4px 0 25px rgba(15, 23, 42, 0.15);
            z-index: 1000;
        }

        .sidebar .nav-link {
            color: #94a3b8 !important;
            font-size: 0.9rem;
            font-weight: 500;
            padding: 12px 18px;
            margin: 3px 10px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.25s ease;
            text-decoration: none !important;
        }

        .sidebar .nav-link i {
            font-size: 1.15rem;
        }

        .sidebar .nav-link:hover {
            color: #ffffff !important;
            background: rgba(255, 255, 255, 0.08) !important;
            transform: translateX(4px);
        }

        .sidebar .nav-link.active {
            color: #ffffff !important;
            background: linear-gradient(90deg, rgba(13, 114, 152, 0.9) 0%, rgba(6, 93, 125, 0.95) 100%) !important;
            box-shadow: 0 8px 20px rgba(13, 114, 152, 0.35);
            font-weight: 700 !important;
        }

        .sidebar .nav-link.active i {
            color: #38bdf8 !important;
        }

        .sidebar .nav-link.text-danger {
            color: #f87171 !important;
            background: rgba(239, 68, 68, 0.1) !important;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .sidebar .nav-link.text-danger:hover {
            background: #ef4444 !important;
            color: #ffffff !important;
        }

        /* Layout bounds */
        .row.flex-nowrap > .col,
        .main-content {
            min-width: 0 !important;
            max-width: 100% !important;
        }

        /* Filter Box */
        .filter-card {
            border: 1px solid var(--admin-border-color) !important;
            border-radius: 20px !important;
            background: #ffffff;
            box-shadow: 0 10px 30px -10px rgba(15, 23, 42, 0.05) !important;
        }

        .form-control-custom, .form-select-custom {
            border-radius: 12px !important;
            border: 1.5px solid #cbd5e1 !important;
            padding: 10px 14px !important;
            font-size: 0.88rem !important;
        }

        .form-control-custom:focus, .form-select-custom:focus {
            border-color: #0d7298 !important;
            box-shadow: 0 0 0 4px rgba(13, 114, 152, 0.12) !important;
        }

        /* Course Card Styling */
        .course-card {
            border: 1px solid var(--admin-border-color) !important;
            border-radius: 20px !important;
            box-shadow: 0 10px 30px -10px rgba(15, 23, 42, 0.05) !important;
            background: #ffffff;
            transition: all 0.3s ease;
            height: 100%;
        }

        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 14px 35px -10px rgba(15, 23, 42, 0.12) !important;
        }

        .course-thumbnail {
            height: 190px;
            object-fit: cover;
            width: 100%;
        }

        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
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
                        <span class="text-info small fw-semibold">● Student Portal</span>
                    </div>
                </div>

                <ul class="nav nav-pills flex-column mb-auto p-2 w-100">
                    <li class="w-100"><a href="../" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                    <li class="w-100"><a href="../MyCourses/" class="nav-link"><i class="bi bi-journal-bookmark"></i> My Courses</a></li>
                    <li class="w-100"><a href="./" class="nav-link active"><i class="bi bi-grid"></i> Categories</a></li>
                    <li class="w-100"><a href="../Schedule/" class="nav-link"><i class="bi bi-calendar-event"></i> Schedule</a></li>
                    <li class="w-100"><a href="../Messages/" class="nav-link"><i class="bi bi-chat-dots"></i> Messages</a></li>
                    <li class="w-100"><a href="../Profile/" class="nav-link"><i class="bi bi-person"></i> Profile</a></li>
                    <li class="w-100"><a href="../Resources/" class="nav-link"><i class="bi bi-file-earmark-text"></i> Resources</a></li>
                </ul>

                <div class="p-3 border-top border-white border-opacity-10 mt-auto">
                    <a href="../../logout.php" class="nav-link text-danger justify-content-center m-0">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="col main-content min-vh-100 d-flex flex-column" style="min-width: 0; overflow-x: hidden;">
                
                <!-- Top Header Bar -->
                <div class="bg-white border-bottom px-4 py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <a href="./" class="btn btn-outline-secondary btn-sm rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="Back to Categories">
                            <i class="bi bi-arrow-left fs-6"></i>
                        </a>
                        <div>
                            <h4 class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($category['name']); ?> Courses</h4>
                            <span class="text-muted small"><?php echo htmlspecialchars($category['description'] ?: 'Browse all active published courses in this category'); ?></span>
                        </div>
                    </div>

                    <span class="badge bg-primary bg-opacity-10 text-primary border px-3 py-2 rounded-pill font-monospace">
                        <i class="bi bi-collection me-1.5"></i><?php echo $course_count; ?> Courses Available
                    </span>
                </div>

                <div class="p-4 flex-grow-1">

                    <!-- Filter Section Card -->
                    <div class="card filter-card p-4 mb-4">
                        <form method="GET" class="row g-3 align-items-end">
                            <input type="hidden" name="id" value="<?php echo $category_id; ?>">
                            
                            <!-- Search Input -->
                            <div class="col-12 col-md-4 col-lg-3">
                                <label class="form-label font-weight-semibold small text-secondary">Search Courses</label>
                                <div class="position-relative">
                                    <input type="text" class="form-control form-control-custom ps-4" name="search" 
                                           value="<?php echo htmlspecialchars($search); ?>" 
                                           placeholder="Keywords...">
                                </div>
                            </div>
                            
                            <!-- Min Price -->
                            <div class="col-6 col-md-2">
                                <label class="form-label font-weight-semibold small text-secondary">Min Price (₹)</label>
                                <input type="number" step="0.01" class="form-control form-control-custom" name="price_min" 
                                       value="<?php echo $price_min !== null ? $price_min : ''; ?>" 
                                       placeholder="0">
                            </div>
                            
                            <!-- Max Price -->
                            <div class="col-6 col-md-2">
                                <label class="form-label font-weight-semibold small text-secondary">Max Price (₹)</label>
                                <input type="number" step="0.01" class="form-control form-control-custom" name="price_max" 
                                       value="<?php echo $price_max !== null ? $price_max : ''; ?>" 
                                       placeholder="5000">
                            </div>
                            
                            <!-- Level Select -->
                            <div class="col-12 col-md-2">
                                <label class="form-label font-weight-semibold small text-secondary">Skill Level</label>
                                <select class="form-select form-select-custom" name="level">
                                    <option value="">All Levels</option>
                                    <option value="Beginner" <?php echo $level === 'Beginner' ? 'selected' : ''; ?>>Beginner</option>
                                    <option value="Intermediate" <?php echo $level === 'Intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                                    <option value="Advanced" <?php echo $level === 'Advanced' ? 'selected' : ''; ?>>Advanced</option>
                                </select>
                            </div>
                            
                            <!-- Submit & Clear Buttons -->
                            <div class="col-12 col-md-2 col-lg-3 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-semibold">
                                    <i class="bi bi-funnel-fill me-1"></i> Filter
                                </button>
                                <a href="?id=<?php echo $category_id; ?>" class="btn btn-outline-secondary w-100 rounded-pill py-2 fw-semibold">
                                    <i class="bi bi-x-circle me-1"></i> Clear
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- Courses Grid -->
                    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4 mb-4">
                        <?php if ($courses_result && $courses_result->num_rows > 0): ?>
                            <?php while ($course = $courses_result->fetch_assoc()): ?>
                                <div class="col">
                                    <div class="card course-card border-0 d-flex flex-column">
                                        <img 
                                            src="../../uploads/course_uploads/thumbnails/<?php echo htmlspecialchars($course['thumbnail']); ?>" 
                                            class="course-thumbnail rounded-top-4" 
                                            alt="<?php echo htmlspecialchars($course['title']); ?>"
                                            onerror="this.src='../../images/default-course.png';"
                                        >
                                        <div class="card-body p-4 d-flex flex-column flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="badge bg-primary bg-opacity-10 text-primary border px-2.5 py-1 rounded-pill small font-monospace">
                                                    <?php echo htmlspecialchars($course['level'] ?: 'General'); ?>
                                                </span>
                                                <span class="fw-bold text-success fs-6">
                                                    <?php echo ($course['price'] > 0) ? '₹' . number_format($course['price'], 2) : 'Free'; ?>
                                                </span>
                                            </div>

                                            <h6 class="fw-bold text-dark mb-2"><?php echo htmlspecialchars($course['title']); ?></h6>
                                            <p class="text-secondary small flex-grow-1 mb-3" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                                <?php echo htmlspecialchars($course['description']); ?>
                                            </p>

                                            <a href="../MyCourses/course.php?id=<?php echo $course['course_id']; ?>" class="btn btn-outline-primary w-100 rounded-pill fw-semibold py-2 mt-auto">
                                                View Course Details <i class="bi bi-arrow-right ms-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="col-12 text-center py-5">
                                <div class="card border-0 shadow-sm rounded-4 p-5">
                                    <i class="bi bi-search text-secondary opacity-50 display-1 mb-3"></i>
                                    <h4 class="fw-bold text-dark mb-2">No Courses Found</h4>
                                    <p class="text-muted mb-3">No published courses match your current search or filter criteria in this category.</p>
                                    <div>
                                        <a href="?id=<?php echo $category_id; ?>" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold">
                                            <i class="bi bi-arrow-counterclockwise me-1.5"></i>Reset Filters
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
