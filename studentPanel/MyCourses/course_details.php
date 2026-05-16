<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Get user details from session
$user_name = $_SESSION['first_name'] ?? 'Student';

// In real implementation, get course ID from URL and fetch from database
$course_id = $_GET['course_id'] ?? 1;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Full Stack Development Fundamentals - GD Edu Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/css/student_dashboard.css">

    <style>

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .course-header {
            background: linear-gradient(45deg, #3498db, #2980b9);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .instructor-badge {
            background: rgba(255, 255, 255, 0.1);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .course-section {
            border-bottom: 1px solid #eee;
            padding: 1rem 0;
        }
        .course-section:last-child {
            border-bottom: none;
        }
        .lesson-item {
            padding: 1rem;
            margin: 0.5rem 0;
            background: #f8f9fa;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.3s;
        }
        .lesson-item:hover {
            background: #e9ecef;
        }
        .progress {
            height: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 sidebar">
                <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 min-vh-100">
                    <a href="student_dashboard.php" class="d-flex align-items-center pb-3 mb-md-1 mt-md-3 me-md-auto text-white text-decoration-none">
                    <span class="fs-5 fw-bolder" style="display: flex;align-items:center;"><img height="35px" src="../../Images/Logos/edutechLogo.png" alt="">&nbsp; GD Edu Tech</span>
                    </a>
                    <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100" id="menu">
                        <li class="w-100">
                            <a href="../" class="nav-link text-white">
                                <i class="bi bi-speedometer2 me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="./" class="nav-link  active">
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
            <div class="col py-0">
                <!-- Course Header -->
                <div class="course-header">
                    <div class="container">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="student_dashboard.php" class="text-white">Dashboard</a></li>
                                        <li class="breadcrumb-item"><a href="#" class="text-white">Courses</a></li>
                                        <li class="breadcrumb-item active text-white" aria-current="page">Full Stack Development</li>
                                    </ol>
                                </nav>
                                <h1 class="mb-3">Full Stack Development Fundamentals</h1>
                                <div class="instructor-badge mb-3">
                                    <img src="/api/placeholder/32/32" class="rounded-circle" alt="Instructor">
                                    <span>John Doe</span>
                                </div>
                                <div class="d-flex gap-4">
                                    <span><i class="bi bi-people"></i> 2,534 students</span>
                                    <span><i class="bi bi-clock"></i> 12 hours</span>
                                    <span><i class="bi bi-star-fill text-warning"></i> 4.8 (245 reviews)</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="text-dark">Your Progress</h5>
                                        <div class="progress mb-2">
                                            <div class="progress-bar bg-success" style="width: 45%"></div>
                                        </div>
                                        <p class="text-dark mb-0">45% Complete</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Course Content -->
                <div class="container pb-4">
                    <div class="row">
                        <div class="col-md-8">
                            <!-- Course Overview -->
                            <div class="card mb-4">
                                <div class="card-body">
                                    <h4 class="card-title">Course Overview</h4>
                                    <p>Master the fundamentals of Full Stack development in this comprehensive course. Learn HTML, CSS, and JavaScript through practical examples and real-world projects. Perfect for beginners looking to start their Full Stack development journey.</p>
                                    <div class="row mt-4">
                                        <div class="col-md-6">
                                            <h5>What you'll learn</h5>
                                            <ul class="list-unstyled">
                                                <li><i class="bi bi-check-circle-fill text-success me-2"></i> HTML5 structure and semantics</li>
                                                <li><i class="bi bi-check-circle-fill text-success me-2"></i> CSS3 styling and layouts</li>
                                                <li><i class="bi bi-check-circle-fill text-success me-2"></i> JavaScript fundamentals</li>
                                                <li><i class="bi bi-check-circle-fill text-success me-2"></i> Responsive web design</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <h5>Prerequisites</h5>
                                            <ul class="list-unstyled">
                                                <li><i class="bi bi-arrow-right me-2"></i> Basic computer knowledge</li>
                                                <li><i class="bi bi-arrow-right me-2"></i> Text editor installed</li>
                                                <li><i class="bi bi-arrow-right me-2"></i> Internet connection</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Course Content -->
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title mb-4">Course Content</h4>
                                    
                                    <!-- Section 1 -->
                                    <div class="course-section">
                                        <h5 class="mb-3">
                                            <i class="bi bi-chevron-down me-2"></i>
                                            Section 1: Introduction to HTML
                                        </h5>
                                        <div class="lesson-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="bi bi-play-circle me-2"></i>
                                                Welcome to the Course
                                            </div>
                                            <span class="text-muted">5:30</span>
                                        </div>
                                        <div class="lesson-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="bi bi-play-circle me-2"></i>
                                                HTML Basics and Structure
                                            </div>
                                            <span class="text-muted">15:45</span>
                                        </div>
                                        <div class="lesson-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="bi bi-file-text me-2"></i>
                                                Exercise: Create Your First HTML Page
                                            </div>
                                            <span class="badge bg-primary">Exercise</span>
                                        </div>
                                    </div>

                                    <!-- Section 2 -->
                                    <div class="course-section">
                                        <h5 class="mb-3">
                                            <i class="bi bi-chevron-down me-2"></i>
                                            Section 2: CSS Fundamentals
                                        </h5>
                                        <div class="lesson-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="bi bi-play-circle me-2"></i>
                                                Introduction to CSS
                                            </div>
                                            <span class="text-muted">12:20</span>
                                        </div>
                                        <div class="lesson-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="bi bi-play-circle me-2"></i>
                                                CSS Selectors and Properties
                                            </div>
                                            <span class="text-muted">18:15</span>
                                        </div>
                                        <div class="lesson-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="bi bi-file-text me-2"></i>
                                                Quiz: CSS Basics
                                            </div>
                                            <span class="badge bg-info">Quiz</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Sidebar -->
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <h5 class="card-title">Course Features</h5>
                                    <ul class="list-unstyled">
                                        <li class="mb-2"><i class="bi bi-film me-2"></i> 24 video lessons</li>
                                        <li class="mb-2"><i class="bi bi-file-text me-2"></i> 12 assignments</li>
                                        <li class="mb-2"><i class="bi bi-award me-2"></i> Certificate of completion</li>
                                        <li class="mb-2"><i class="bi bi-infinity me-2"></i> Lifetime access</li>
                                    </ul>
                                    <button class="btn btn-primary w-100">Continue Learning</button>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Course Resources</h5>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Course Syllabus
                                            <i class="bi bi-download"></i>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Coding Examples
                                            <i class="bi bi-download"></i>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Project Files
                                            <i class="bi bi-download"></i>
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
</body>
</html>