<?php
session_start();
require_once '../../Configurations/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

// Check if course ID is provided
if (!isset($_GET['id'])) {
    header("Location: ../");
    exit();
}

$course_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Fetch course details
$course_query = "
    SELECT 
        c.*,
        cat.name AS category_name
    FROM Courses c
    LEFT JOIN Categories cat ON c.category_id = cat.category_id
    WHERE c.course_id = ? AND c.status = 'published'
";

$course_stmt = $conn->prepare($course_query);
$course_stmt->bind_param("i", $course_id);
$course_stmt->execute();
$course_result = $course_stmt->get_result();

if ($course_result->num_rows === 0) {
    header("Location: ../");
    exit();
}

$course = $course_result->fetch_assoc();

// Check if user is already enrolled
$enrollment_check = "
    SELECT * FROM Enrollments 
    WHERE student_id = ? AND course_id = ? AND access_status = 'active'
";
$check_stmt = $conn->prepare($enrollment_check);
$check_stmt->bind_param("ii", $user_id, $course_id);
$check_stmt->execute();
$is_enrolled = $check_stmt->get_result()->num_rows > 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['title']); ?> - Course Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/css/student_dashboard.css">
    <style>
        :root {
            --primary-color: #2C3E50;
            --secondary-color: #E74C3C;
            --accent-color: #3498DB;
            --success-color: #27AE60;
            --warning-color: #F1C40F;
            --light-bg: #F8F9FA;
            --dark-text: #2C3E50;
        }

        .course-header {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .course-card {
            border: none;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
            transition: transform 0.3s ease;
        }

        .course-card:hover {
            transform: translateY(-5px);
        }

        .course-thumbnail {
            border-radius: 15px 15px 0 0;
            height: 300px;
            object-fit: cover;
        }

        .price-tag {
            background: linear-gradient(135deg, var(--success-color), #2ECC71);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
            margin: 1rem 0;
        }

        .feature-icon {
            width: 40px;
            height: 40px;
            background: var(--light-bg);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            color: var(--accent-color);
        }

        .accordion-button:not(.collapsed) {
            background-color: var(--accent-color);
            color: white;
        }

        .lesson-item {
            border-left: 4px solid var(--accent-color);
            margin-bottom: 0.5rem;
            padding: 0.5rem;
            transition: all 0.3s ease;
        }

        .lesson-item:hover {
            background-color: var(--light-bg);
        }

        .buy-now-btn {
            background: linear-gradient(135deg, var(--secondary-color), #C0392B);
            border: none;
            padding: 1rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }

        .buy-now-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .guarantee-badge {
            background: linear-gradient(135deg, #FFF, #F8F9FA);
            border: 2px solid var(--success-color);
            color: var(--success-color);
            font-weight: bold;
        }

        .course-features {
            background: linear-gradient(135deg, #F8F9FA, #FFFFFF);
            border-radius: 15px;
            padding: 1.5rem;
        }

        .learning-points li {
            position: relative;
            padding-left: 2rem;
            margin-bottom: 1rem;
        }

        .learning-points li i {
            position: absolute;
            left: 0;
            top: 4px;
            color: var(--success-color);
        }
@media (max-width: 768px) {
    

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
                            <a href="./" class="nav-link text-white active">
                                <i class="bi bi-book me-2"></i> My Courses
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Categories/" class="nav-link text-white">
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
            <div class="col py-3">
                <div class="container">
                    <!-- Course Header -->
                    <div class="course-header">
                        <div class="container">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="../" class="text-white">Dashboard</a></li>
                                    <li class="breadcrumb-item active text-white" aria-current="page">Course Details</li>
                                </ol>
                            </nav>
                            <h1 class="display-4"><?php echo htmlspecialchars($course['title']); ?></h1>
                            <div class="mt-3">
                                <span class="badge bg-light text-dark me-2"><?php echo htmlspecialchars($course['category_name']); ?></span>
                                <span class="badge bg-warning text-dark me-2"><?php echo htmlspecialchars($course['level']); ?></span>
                                <span class="badge bg-info"><?php echo htmlspecialchars($course['language']); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <!-- Course Card -->
                            <div class="card course-card mb-4">
                                <img src="../../uploads/course_uploads/thumbnails/<?php echo htmlspecialchars($course['thumbnail']); ?>" 
                                     class="course-thumbnail" 
                                     alt="<?php echo htmlspecialchars($course['title']); ?>">
                                <div class="card-body">
                                    <p class="lead"><?php echo htmlspecialchars($course['description']); ?></p>
                                </div>
                            </div>

                            <!-- Course Content -->
                            <div class="card course-card">
                                <div class="card-body">
                                    <h3 class="card-title mb-4">Course Content</h3>
                                    <?php
                                    // Fetch lessons and videos
                                    $lessons_query = "SELECT * FROM Lessons WHERE course_id = ? ORDER BY lesson_order";
                                    $lessons_stmt = $conn->prepare($lessons_query);
                                    $lessons_stmt->bind_param("i", $course_id);
                                    $lessons_stmt->execute();
                                    $lessons_result = $lessons_stmt->get_result();
                                    $total_lessons = $lessons_result->num_rows;
                                    
                                    // Count total videos
                                    $videos_query = "SELECT COUNT(*) as total_videos FROM Videos v 
                                                   JOIN Lessons l ON v.lesson_id = l.lesson_id 
                                                   WHERE l.course_id = ?";
                                    $videos_stmt = $conn->prepare($videos_query);
                                    $videos_stmt->bind_param("i", $course_id);
                                    $videos_stmt->execute();
                                    $total_videos = $videos_stmt->get_result()->fetch_assoc()['total_videos'];
                                    ?>
                                    
                                    <div class="accordion" id="courseContent">
                                        <?php while ($lesson = $lessons_result->fetch_assoc()): ?>
                                            <div class="accordion-item">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button collapsed" type="button" 
                                                            data-bs-toggle="collapse" 
                                                            data-bs-target="#lesson<?php echo $lesson['lesson_id']; ?>">
                                                        <?php echo htmlspecialchars($lesson['title']); ?>
                                                    </button>
                                                </h2>
                                                <div id="lesson<?php echo $lesson['lesson_id']; ?>" 
                                                     class="accordion-collapse collapse">
                                                    <div class="accordion-body">
                                                        <?php
                                                        $videos_query = "SELECT * FROM Videos 
                                                               WHERE lesson_id = ? 
                                                               ORDER BY video_order";
                                                        $videos_stmt = $conn->prepare($videos_query);
                                                        $videos_stmt->bind_param("i", $lesson['lesson_id']);
                                                        $videos_stmt->execute();
                                                        $videos_result = $videos_stmt->get_result();
                                                        ?>
                                                        <ul class="list-group">
                                                            <?php while ($video = $videos_result->fetch_assoc()): ?>
                                                                <li class="list-group-item">
                                                                    <i class="bi bi-play-circle me-2"></i>
                                                                    <?php echo htmlspecialchars($video['title']); ?>
                                                                    <?php if (!$is_enrolled): ?>
                                                                        <i class="bi bi-lock float-end"></i>
                                                                    <?php endif; ?>
                                                                </li>
                                                            <?php endwhile; ?>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <!-- Course Features Card -->
                            <div class="card course-card mb-4">
                                <div class="card-body course-features">
                                    <h4 class="card-title mb-4">Course Features</h4>
                                    <ul class="list-unstyled">
                                        <li class="mb-3">
                                            <div class="feature-icon">
                                                <i class="bi bi-film"></i>
                                            </div>
                                            <?php echo $total_videos; ?> video lessons
                                        </li>
                                        <li class="mb-2">
                                            <i class="bi bi-book me-2"></i> <?php echo $total_lessons; ?> lessons
                                        </li>
                                        <li class="mb-2">
                                            <i class="bi bi-award me-2"></i> Certificate of completion
                                        </li>
                                        <li class="mb-2">
                                            <i class="bi bi-infinity me-2"></i> Lifetime access
                                        </li>
                                    </ul>

                                    <div class="price-tag">
                                        <?php if ($course['price'] == 0): ?>
                                            <h3 class="mb-0">Free</h3>
                                            <small>Lifetime access</small>
                                        <?php else: ?>
                                            <h3 class="mb-0">₹<?php echo number_format($course['price'], 2); ?></h3>
                                            <small>One-time payment</small>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($is_enrolled): ?>
                                        <a href="course_content.php?id=<?php echo $course_id; ?>" 
                                           class="btn btn-success w-100 mb-3">
                                            <i class="bi bi-play-circle me-2"></i>Continue Learning
                                        </a>
                                    <?php else: ?>
                                        <?php if ($course['price'] == 0): ?>
                                            <form action="enroll_free.php" method="POST">
                                                <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                                                <button type="submit" class="btn btn-primary buy-now-btn w-100 mb-3">
                                                    <i class="bi bi-play-circle me-2"></i>Enroll Now - Free
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <a href="checkout.php?course_id=<?php echo $course_id."&".rand(10000000, 99999999).chr(rand(1000, 99999));  ?>" 
                                               class="btn btn-primary buy-now-btn w-100 mb-3">
                                                <i class="bi bi-cart-plus me-2"></i>Buy Now
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if ($course['price'] > 0): ?>
                                        <div class="text-center">
                                            <span class="guarantee-badge d-inline-block p-2 rounded">
                                                <i class="bi bi-shield-check me-2"></i>
                                                30-Day Money-Back Guarantee
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- What you'll learn -->
                            <div class="card course-card">
                                <div class="card-body">
                                    <h4 class="card-title mb-4">What you'll learn</h4>
                                    <ul class="list-unstyled learning-points">
                                        <li class="mb-2">
                                            <i class="bi bi-check2 text-success me-2"></i>
                                            Complete understanding of <?php echo htmlspecialchars($course['title']); ?>
                                        </li>
                                        <li class="mb-2">
                                            <i class="bi bi-check2 text-success me-2"></i>
                                            Practical hands-on exercises
                                        </li>
                                        <li class="mb-2">
                                            <i class="bi bi-check2 text-success me-2"></i>
                                            Real-world project experience
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
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