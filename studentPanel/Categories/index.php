<?php
session_start();
require_once '../../Configurations/config.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

// Fetch all categories with course count
$categories_query = "
    SELECT 
        c.category_id,
        c.name,
        c.description,
        COUNT(co.course_id) as course_count
    FROM Categories c
    LEFT JOIN Courses co ON c.category_id = co.category_id
    WHERE co.status = 'published'
    GROUP BY c.category_id
    ORDER BY c.name
";
$categories_result = $conn->query($categories_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Categories - GD Edu Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/css/student_dashboard.css">
        
    <link rel="icon" type="image/png" href="../../Images/Logos/GD_Only_logo.png">
    <link rel="shortcut icon" href="../../Images/Logos/GD_Only_logo.png">
    <style>
        .category-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 15px;
            overflow: hidden;
            height: 100%;
        }

        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .category-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #3498db;
        }

        .category-count {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(52, 152, 219, 0.1);
            color: #3498db;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: bold;
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
            /* background:#d30043 !important ; */
            border-color: var(--accent-color) !important;
        }

        @media (max-width: 768px) {



            /* Styles for the fixed sidebar */
            #sidebar {
                position: fixed;
                top: 0;
                left: 0;
                width: 70vw;
                /* Sidebar width */
                height: 100vh;
                /* Full height */
                background-color: #2c3e50;
                /* Sidebar background color */
                z-index: 1000;
                /* Ensure sidebar is above other content */
                transform: translateX(-100%);
                /* Initially hidden */
                transition: transform 0.3s ease;
                /* Smooth transition */
            }

            #sidebar.show {
                transform: translateX(0);
                /* Show sidebar */
            }

            .main-content.hidden {
                display: none;
                /* Hide main content when sidebar is open */
            }

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
                        <li class="w-100">
                            <a href="../Resources/index.php" class="nav-link text-white">
                                <i class="bi bi-file-earmark-text me-2"></i> Resources
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../shop.php" class="nav-link text-white">
                                <i class="bi bi-shop me-2"></i> Shop
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
                        <h2><i class="bi bi-grid me-2"></i>Course Categories</h2>
                        <p class="text-light mb-0">Explore our wide range of course categories</p>
                    </div>
                </div>

                <!-- Categories Grid -->
                <div class="container">
                    <div class="row g-4">
                        <?php while ($category = $categories_result->fetch_assoc()): ?>
                            <div class="col-md-4">
                                <div class="card category-card">
                                    <div class="card-body text-center position-relative">
                                        <span class="category-count">
                                            <?php echo $category['course_count']; ?> Courses
                                        </span>
                                        <div class="category-icon">
                                            <?php
                                            // Assign different icons based on category name
                                            $icon = 'bi-book'; // default icon
                                            switch (strtolower($category['name'])) {
                                                case 'programming':
                                                    $icon = 'bi-code-square';
                                                    break;
                                                case 'design':
                                                    $icon = 'bi-palette';
                                                    break;
                                                case 'business':
                                                    $icon = 'bi-briefcase';
                                                    break;
                                                case 'marketing':
                                                    $icon = 'bi-graph-up';
                                                    break;
                                                case 'music':
                                                    $icon = 'bi-music-note-beamed';
                                                    break;
                                                case 'photography':
                                                    $icon = 'bi-camera';
                                                    break;
                                            }
                                            ?>
                                            <i class="bi <?php echo $icon; ?>"></i>
                                        </div>
                                        <h4 class="card-title">
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </h4>
                                        <p class="card-text text-muted">
                                            <?php echo htmlspecialchars($category['description']); ?>
                                        </p>
                                        <a href="category_courses.php?id=<?php echo $category['category_id']; ?>"
                                            class="btn btn-primary">
                                            Explore Courses
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