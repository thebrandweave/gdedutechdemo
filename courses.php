<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once './Configurations/config.php';

// Pagination setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 9;
$offset = ($page - 1) * $items_per_page;

// Category filter
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$category_condition = $category_filter > 0 ? "AND c.category_id = $category_filter" : "";

// Search filter
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$search_condition = !empty($search) ? "AND (c.title LIKE '%$search%' OR c.description LIKE '%$search%')" : "";

// Get total courses for pagination
$count_query = "
    SELECT COUNT(*) as total 
    FROM Courses c
    WHERE c.status = 'published'
    $category_condition
    $search_condition";
$total_courses = $conn->query($count_query)->fetch_assoc()['total'];
$total_pages = ceil($total_courses / $items_per_page);

// Fetch courses
$courses_query = "
    SELECT c.*, 
           cat.name as category_name,
           (SELECT COUNT(*) FROM Enrollments e WHERE e.course_id = c.course_id) as student_count
    FROM Courses c
    LEFT JOIN Categories cat ON c.category_id = cat.category_id
    WHERE c.status = 'published'
    $category_condition
    $search_condition
    ORDER BY c.created_at DESC
    LIMIT $offset, $items_per_page";
$courses = $conn->query($courses_query)->fetch_all(MYSQLI_ASSOC);

// Fetch all categories for filter
$categories_query = "
    SELECT c.*, 
           (SELECT COUNT(*) FROM Courses WHERE category_id = c.category_id AND status = 'published') as course_count
    FROM Categories c
    ORDER BY c.name";
$categories = $conn->query($categories_query)->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses - GD Edu Tech</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- AOS Animation Library -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <!-- Swiper JS CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="./css/style.css">
    <!-- Custom JavaScript -->
    <script src="./js/main.js" defer></script>
    
    <link rel="icon" type="image/png" href="./Images/Logos/GD_Only_logo.png">
    <link rel="icon" type="image/png" sizes="32x32" href="./Images/Logos/GD_Only_logo.png">
    <link rel="icon" type="image/png" sizes="16x16" href="./Images/Logos/GD_Only_logo.png">
    <link rel="shortcut icon" href="./Images/Logos/GD_Only_logo.png">
    <link rel="apple-touch-icon" href="./Images/Logos/GD_Only_logo.png">
    <meta name="msapplication-TileImage" content="./Images/Logos/GD_Only_logo.png">
</head>

<body>
    <?php include 'navbar.php'; ?>

    <!-- Redesigned Executive Course Header -->
    <section class="about-page-header position-relative overflow-hidden w-100 my-0">
        <!-- Ambient Decorative Glows & Pattern -->
        <div class="about-header-glow-1"></div>
        <div class="about-header-glow-2"></div>
        <div class="about-header-pattern"></div>

        <div class="container position-relative z-2 py-4">
            <div class="row align-items-center g-5">
                <!-- Left Column: Title & Info -->
                <div class="col-lg-7 text-start" data-aos="fade-right">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb about-breadcrumb px-3 py-1.5 rounded-pill mb-3 d-inline-flex">
                            <li class="breadcrumb-item"><a href="index.php" class="text-black text-decoration-none"><i class="bi bi-house-door-fill me-1"></i> Home</a></li>
                            <li class="breadcrumb-item active text-black" aria-current="page">Courses</li>
                        </ol>
                    </nav>

                    <h1 class="display-4 fw-bold text-black mb-3">
                        Find Your Perfect <span class="cta-gold-text">Learning Path</span>
                    </h1>

                    <p class="lead text-black mb-4" style="max-width: 620px;">
                        Choose from <?php echo $total_courses; ?>+ courses across <?php echo count($categories); ?> categories. Learn from industry experts and transform your career with hands-on projects.
                    </p>

                    <div class="d-flex flex-wrap gap-3 align-items-center pt-2">
                        <div class="about-header-badge">
                            <i class="bi bi-collection-play-fill text-warning"></i>
                            <span style="color: #0f172a;"> <?php echo $total_courses; ?>+ Published Courses</span>
                        </div>
                        <div class="about-header-badge">
                            <i class="bi bi-grid-3x3-gap-fill text-info"></i>
                            <span style="color: #0f172a;"><?php echo count($categories); ?> Categories</span>
                        </div>
                        <div class="about-header-badge">
                            <i class="bi bi-people-fill text-success"></i>
                            <span style="color: #0f172a;">5000+ Active Students</span>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Interactive Search Panel -->
                <div class="col-lg-5 text-center text-lg-end" data-aos="fade-left" data-aos-delay="200">
                    <div class="about-header-card bg-white text-start p-4 p-md-4.5 rounded-4 shadow-lg border-0" style="max-width: 440px; margin-left: auto;">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="rounded-circle  bg-opacity-20 p-3 text-warning d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                                <i class="bi bi-search fs-3 text-warning"></i>
                            </div>
                            <div>
                                <h5 class="text-dark fw-bold mb-0 fs-5">Search Courses</h5>
                                <span class="text-muted small">Filter by topic or category</span>
                            </div>
                        </div>

                        <form id="courseSearchForm" class="course-search-form">
                            <div class="mb-3">
                                <label class="form-label text-dark fw-semibold small mb-1.5">Keywords</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-1 border-secondary-subtle text-dark px-3"><i class="bi bi-search fs-6 text-dark"></i></span>
                                    <input type="text" name="search" id="searchInput" class="form-control bg-light border-1 border-secondary-subtle text-dark px-3" placeholder="What do you want to learn?" value="<?php echo htmlspecialchars($search); ?>" style="color: #0f172a !important;">
                                    <div class="search-spinner spinner-border spinner-border-sm text-warning d-none position-absolute end-0 me-3 top-50 translate-middle-y" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label text-dark fw-semibold small mb-1.5">Category</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-1 border-secondary-subtle text-dark px-3"><i class="bi bi-grid-fill fs-6 text-dark"></i></span>
                                    <select name="category" id="categorySelect" class="form-select bg-light border-1 border-secondary-subtle text-dark px-3" style="color: #0f172a !important;">
                                        <option value="0">All Categories</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['category_id']; ?>" <?php echo $category_filter == $category['category_id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['name']); ?> (<?php echo $category['course_count']; ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-dark w-100 rounded-pill py-2.5 fw-bold text-white d-flex align-items-center justify-content-center gap-2 shadow-sm">
                                <span>Search Catalog</span>
                                <i class="bi bi-arrow-right fs-5"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-header-shape position-absolute bottom-0 start-0 w-100">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 120" preserveAspectRatio="none" style="height: 40px; display: block; width: 100%;">
                <path fill="#ffffff" fill-opacity="1" d="M0,32L48,42.7C96,53,192,75,288,80C384,85,480,75,576,58.7C672,43,768,21,864,21.3C960,21,1056,43,1152,53.3C1248,64,1344,64,1392,64L1440,64L1440,120L1392,120C1344,120,1248,120,1152,120C1056,120,960,120,864,120C768,120,672,120,576,120C480,120,384,120,288,120C192,120,96,120,48,120L0,120Z"></path>
            </svg>
        </div>
    </section>

    <!-- Mobile Search Panel -->
    <div class="mobile-search-panel d-lg-none">
        <div class="container-fluid px-3">
            <!-- Floating Search Bar -->
            <div class="mobile-search-bar">
                <div class="search-input-wrapper">
                    <i class="bi bi-search"></i>
                    <input type="text" 
                           id="mobileSearchInput"
                           class="form-control" 
                           placeholder="Search courses..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button type="button" class="btn-filter" data-bs-toggle="modal" data-bs-target="#filterModal">
                        <i class="bi bi-sliders"></i>
                    </button>
                </div>
            </div>

            <!-- Active Filters Display -->
            <?php if (!empty($search) || $category_filter > 0): ?>
            <div class="active-filters mt-2">
                <div class="active-filters-scroll">
                    <?php if (!empty($search)): ?>
                        <div class="filter-tag">
                            <span><?php echo htmlspecialchars($search); ?></span>
                            <button type="button" class="btn-close-filter" onclick="clearSearch()">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    <?php endif; ?>
                    <?php if ($category_filter > 0): 
                        $filtered_category = array_filter($categories, function($c) use ($category_filter) {
                            return $c['category_id'] == $category_filter;
                        });
                        $filtered_category = reset($filtered_category);
                    ?>
                        <div class="filter-tag">
                            <span><?php echo htmlspecialchars($filtered_category['name']); ?></span>
                            <button type="button" class="btn-close-filter" onclick="clearCategory()">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filter Modal -->
    <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-bottom">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="filterModalLabel">Filter Courses</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="filter-section">
                        <label class="filter-label">Categories</label>
                        <div class="category-chips">
                            <button type="button" 
                                    class="category-chip <?php echo $category_filter == 0 ? 'active' : ''; ?>"
                                    data-category-id="0"
                                    onclick="selectCategory(0)">
                                All Categories
                            </button>
                            <?php foreach ($categories as $category): ?>
                                <button type="button" 
                                        class="category-chip <?php echo $category_filter == $category['category_id'] ? 'active' : ''; ?>"
                                        data-category-id="<?php echo $category['category_id']; ?>"
                                        onclick="selectCategory(<?php echo $category['category_id']; ?>)">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                    <span class="course-count"><?php echo $category['course_count']; ?></span>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="applyFilters()">Apply Filters</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Courses List -->
    <section class="py-5 my-5">
        <div class="container">
            <div class="row mb-4" style="text-align:center;">
                <div class="col-12">
                    <h2 data-aos="fade-up">Found <?php echo $total_courses; ?> Online  Courses</h2>
                    <?php if (!empty($search) || $category_filter > 0): ?>
                        <div class="d-flex align-items-center mt-3">
                            <span class="me-2">Active filters:</span>
                            <?php if (!empty($search)): ?>
                                <span class="badge bg-primary me-2 p-2">
                                    Search: <?php echo htmlspecialchars($search); ?>
                                    <a href="?<?php echo $category_filter > 0 ? 'category=' . $category_filter : ''; ?>" class="text-white ms-2" style="text-decoration: none;">
                                        <i class="bi bi-x"></i>
                                    </a>
                                </span>
                            <?php endif; ?>
                            <?php if ($category_filter > 0): 
                                $filtered_category = array_filter($categories, function($c) use ($category_filter) {
                                    return $c['category_id'] == $category_filter;
                                });
                                $filtered_category = reset($filtered_category);
                            ?>
                                <span class="badge bg-primary me-2 p-2">
                                    Category: <?php echo htmlspecialchars($filtered_category['name']); ?>
                                    <a href="?<?php echo !empty($search) ? 'search=' . htmlspecialchars($search) : ''; ?>" class="text-white ms-2" style="text-decoration: none;">
                                        <i class="bi bi-x"></i>
                                    </a>
                                </span>
                            <?php endif; ?>
                            <a href="courses.php" class="btn btn-sm btn-outline-secondary ms-auto">Clear All Filters</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <style>
                .premium-card {
                    border-radius: 10px;
                    border: none;
                    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                    transition: transform 0.3s ease, box-shadow 0.3s ease;
                    overflow: hidden;
                    color: inherit;
                }

                .premium-card:hover {
                    transform: translateY(-5px);
                    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
                }
                
                .course-card {
                    cursor: pointer;
                }
                
                a.text-decoration-none:hover {
                    text-decoration: none !important;
                }
                
                a.text-decoration-none {
                    color: inherit;
                }

                .badge {
                    background:rgba(133, 134, 134, 0.7) !important;
                    border: 1px solid rgba(207, 210, 211, 0.36);
                    color: black;
                }

                /* Styling the Contact Button */
.btn-contact {
    background-color: #0079a8; /* Bootstrap primary color */
    color: white;
    border-radius: 50px; /* Fully rounded */
    padding: 6px 10px;
    border: none;
    transition: all 0.3s ease;
    font-weight: 500;
    font-size:16px;
}

/* Initial Arrow State */
.arrow-icon {
    display: inline-block;
    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    /* Set initial rotation (45deg is standard for diagonal arrows) */
    transform: rotate(2deg); 
}


/* Hover Effect: Move button slightly and Flex/Translate the arrow */
.premium-card:hover .btn-contact {
    background-color: #0079a9;
    color:white;

}

 .arrow-icon{

        background-color: white;
        color:#d15a4f;
        padding:0px 4px 0px 4px;
        border-radius:50px;
 }

.premium-card:hover .arrow-icon {
    /* "Flex" the arrow in the X direction while maintaining rotation */
    transform: translateX(2px) rotate(43deg);
    color:#d15a4f;
}

/* Card Styling */
.course-card {
    border-radius: 20px;
    overflow: hidden;
    transition: all 0.4s ease;
    background: #fff;

    position: relative;
}



/* Image zoom */
.course-card img {
    transition: transform 0.5s ease;
}

.course-card:hover img {
    transform: scale(1.1);
}

/* Gradient overlay */
.course-card::before {
    content: "";
    position: absolute;
    inset: 0;
    /* background: linear-gradient(to top, rgba(0,0,0,0.6), transparent); */
    opacity: 0;
    transition: 0.4s;
    z-index: 1;
}

.course-card:hover::before {
    opacity: 1;
}

/* Text overlay effect */
.course-card .card-body {
    position: relative;
    z-index: 2;
}

/* Badge */
.course-card .badge {
    font-size: 12px;
    padding: 6px 10px;
    border-radius: 20px;
}

/* Section title */
.section-title {
    font-weight: 800;
    font-size: 2.5rem;
    background: linear-gradient(90deg, #00b6ff, #ff4300);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* Modal Styling */
.modal-content {
    border-radius: 20px;
    overflow: hidden;
}

.modal-body img {
    border-radius: 12px;
}

/* Button */
.btn-primary {
    border-radius: 30px;
    padding: 10px 20px;
}

/* Smooth fade */
.course-card, .modal {
    transition: all 0.3s ease-in-out;
}


            </style>
            
            <div class="row g-4">
                <?php if (empty($courses)): ?>
                    <div class="col-12 text-center py-5">
                        <div data-aos="fade-up">
                            <i class="bi bi-search fs-1 text-muted"></i>
                            <h3 class="mt-3">No courses found</h3>
                            <p class="text-muted">Try adjusting your search or filter criteria</p>
                            <a href="courses.php" class="btn btn-primary mt-3">View All Courses</a>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($courses as $index => $course): ?>
                        <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo ($index % 3) * 100; ?>">
                            <a href="./studentPanel/MyCourses/course.php?id=<?php echo $course['course_id']; ?>" class="text-decoration-none">
                                <div class="premium-card h-100 course-card">
                                    <div class="position-relative">
                                        <img src="./uploads/course_uploads/thumbnails/<?php echo htmlspecialchars($course['thumbnail']); ?>"
                                            class="card-img-top" alt="<?php echo htmlspecialchars($course['title']); ?>"
                                            style="height: 200px; object-fit: cover;">
                                        <span class="badge bg-primary position-absolute top-0 text-white end-0 m-3">
                                            <?php echo htmlspecialchars($course['category_name']); ?>
                                        </span>
                                    </div>
                                    <div class="card-body p-4">
                                        <h5 class="card-title mb-3"><?php echo htmlspecialchars($course['title']); ?></h5>
                                        <p class="card-text text-muted mb-4">
                                            <?php echo substr(htmlspecialchars($course['description']), 0, 100) . '...'; ?>
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center mt-auto">
                                            <!-- <span class="text-muted">
                                                <i class="bi bi-people"></i> <?php echo $course['student_count']; ?> students
                                            </span> -->
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="row mt-5">
                    <div class="col-12">
                        <nav aria-label="Course pagination">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . htmlspecialchars($search) : ''; ?><?php echo $category_filter > 0 ? '&category=' . $category_filter : ''; ?>">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . htmlspecialchars($search) : ''; ?><?php echo $category_filter > 0 ? '&category=' . $category_filter : ''; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . htmlspecialchars($search) : ''; ?><?php echo $category_filter > 0 ? '&category=' . $category_filter : ''; ?>">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

<!-- Offline Courses Section -->
<section class="py-5 bg-light position-relative overflow-hidden">
    <div class="container py-4">
        <div class="row text-center mb-5">
            <div class="col-lg-8 mx-auto" data-aos="fade-up">
                <div class="cta-badge d-inline-flex align-items-center px-3 py-1 rounded-pill mb-3">
                    <i class="bi bi-geo-alt-fill text-warning me-2"></i><span style="color: #030303;">In-Person Training</span>
                </div>
                <h2 class="display-6 fw-bold mb-3">Classroom <span class="highlight-gold">Offline Courses</span></h2>
                <p class="lead text-muted">
                    Join our state-of-the-art classroom training programs with hands-on practical labs and expert mentorship.
                </p>
            </div>
        </div>

        <div class="row g-4">

            <!-- Course 1: Full Stack -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="offline-course-card"
                     onclick="openCourseModal(
                        'Full Stack Development',
                        './Images/Others/web.jpg',
                        'Complete 16-week program covering HTML, CSS, JavaScript, ReactJs or NextJs, Node.js, MongoDB, REST APIs, Postman, Git, Github and real-world projects.',
                        ['Offline', '16 Weeks', 'Certification']
                     )"
                     style="cursor:pointer;">

                    <div class="offline-card-img-wrapper">
                        <img src="./Images/Others/web.jpg" alt="Full Stack Development" class="offline-card-img">
                        <span class="badge bg-dark bg-opacity-75 text-white border border-white border-opacity-25 px-3 py-1.5 rounded-pill position-absolute top-0 end-0 m-3">
                            <i class="bi bi-code-slash me-1 text-warning"></i> Full Stack
                        </span>
                    </div>

                    <div class="offline-card-body">
                        <div class="d-flex align-items-center gap-2 mb-2 text-muted small">
                            <span><i class="bi bi-clock me-1 text-primary"></i> 16 Weeks</span>
                            <span>•</span>
                            <span><i class="bi bi-award me-1 text-success"></i> Certificate</span>
                        </div>
                        <h5 class="fw-bold mb-2 text-dark">Full Stack Development</h5>
                        <!-- <p class="text-muted small mb-3">Master front-end, back-end, REST APIs, and database engineering.</p> -->
                        <div class="mt-auto">
                            <button class="btn btn-outline-dark w-100 rounded-pill py-2 text-decoration-none d-flex align-items-center justify-content-center gap-2">
                                <span>Explore Course</span>
                                <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Course 2: Architecture -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="offline-course-card"
                    onclick="openCourseModal(
                        'Architectural Design Course',
                        './Images/Others/architecture.jpg',
                        'Master the fundamentals and advanced concepts of architectural design through this comprehensive program. Learn AutoCAD, Revit, SketchUp, V-Ray, and Lumion with hands-on practical experience.',
                        ['Offline', '16 Weeks', 'Certification']
                    )"
                    style="cursor:pointer;">

                    <div class="offline-card-img-wrapper">
                        <img src="./Images/Others/architecture.jpg" alt="Architectural Design" class="offline-card-img">
                        <span class="badge bg-dark bg-opacity-75 text-white border border-white border-opacity-25 px-3 py-1.5 rounded-pill position-absolute top-0 end-0 m-3">
                            <i class="bi bi-building me-1 text-warning"></i> Architecture
                        </span>
                    </div>

                    <div class="offline-card-body">
                        <div class="d-flex align-items-center gap-2 mb-2 text-muted small">
                            <span><i class="bi bi-clock me-1 text-primary"></i> 16 Weeks</span>
                            <span>•</span>
                            <span><i class="bi bi-award me-1 text-success"></i> Certificate</span>
                        </div>
                        <h5 class="fw-bold mb-2 text-dark">Architectural Design Course</h5>
                        <!-- <p class="text-muted small mb-3">Learn 3D modeling, AutoCAD, Revit, space planning, and Lumion.</p> -->
                        <div class="mt-auto">
                            <button class="btn btn-outline-dark w-100 rounded-pill py-2 text-decoration-none d-flex align-items-center justify-content-center gap-2">
                                <span>Explore Course</span>
                                <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Course 3: Interior Design -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="offline-course-card"
                    onclick="openCourseModal(
                        'Interior Design Course',
                        './Images/Others/int.jpg',
                        'Step into the world of creative interiors and learn how to design stylish, functional spaces from concept to completion. Covers space planning, color schemes, lighting, and furniture layout.',
                        ['Offline', '16 Weeks', 'Certification']
                    )"
                    style="cursor:pointer;">

                    <div class="offline-card-img-wrapper">
                        <img src="./Images/Others/int.jpg" alt="Interior Design" class="offline-card-img">
                        <span class="badge bg-dark bg-opacity-75 text-white border border-white border-opacity-25 px-3 py-1.5 rounded-pill position-absolute top-0 end-0 m-3">
                            <i class="bi bi-palette me-1 text-warning"></i> Interior Design
                        </span>
                    </div>

                    <div class="offline-card-body">
                        <div class="d-flex align-items-center gap-2 mb-2 text-muted small">
                            <span><i class="bi bi-clock me-1 text-primary"></i> 16 Weeks</span>
                            <span>•</span>
                            <span><i class="bi bi-award me-1 text-success"></i> Certificate</span>
                        </div>
                        <h5 class="fw-bold mb-2 text-dark">Interior Design Course</h5>
                        <!-- <p class="text-muted small mb-3">Master space planning, color harmonies, lighting design, and styling.</p> -->
                        <div class="mt-auto">
                            <button class="btn btn-outline-dark w-100 rounded-pill py-2 text-decoration-none d-flex align-items-center justify-content-center gap-2">
                                <span>Explore Course</span>
                                <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Course 4: Digital Marketing -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="400">
                <div class="offline-course-card"
                    onclick="openCourseModal(
                        'Digital Marketing',
                        './Images/Others/market.jpg',
                        'Master SEO, SMM, PPC, Google Ads, content marketing, web analytics, WordPress, and digital design tools.',
                        ['Offline', '16 Weeks', 'Certification']
                    )"
                    style="cursor:pointer;">

                    <div class="offline-card-img-wrapper">
                        <img src="./Images/Others/market.jpg" alt="Digital Marketing" class="offline-card-img">
                        <span class="badge bg-dark bg-opacity-75 text-white border border-white border-opacity-25 px-3 py-1.5 rounded-pill position-absolute top-0 end-0 m-3">
                            <i class="bi bi-graph-up-arrow me-1 text-warning"></i> Marketing
                        </span>
                    </div>

                    <div class="offline-card-body">
                        <div class="d-flex align-items-center gap-2 mb-2 text-muted small">
                            <span><i class="bi bi-clock me-1 text-primary"></i> 16 Weeks</span>
                            <span>•</span>
                            <span><i class="bi bi-award me-1 text-success"></i> Certificate</span>
                        </div>
                        <h5 class="fw-bold mb-2 text-dark">Digital Marketing</h5>
                        <!-- <p class="text-muted small mb-3">Master SEO, Google Ads, Social Media Marketing, and Analytics.</p> -->
                        <div class="mt-auto">
                            <button class="btn btn-outline-dark w-100 rounded-pill py-2 text-decoration-none d-flex align-items-center justify-content-center gap-2">
                                <span>Explore Course</span>
                                <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Course 5: Graphic Design -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="500">
                <div class="offline-course-card"
                    onclick="openCourseModal(
                        'Graphic Design & Video Editing',
                        './Images/Others/designer.jpg',
                        'Learn Canva, Photoshop, Illustrator, Premiere Pro, After Effects, and DaVinci Resolve for professional content creation.',
                        ['Offline', '16 Weeks', 'Certification']
                    )"
                    style="cursor:pointer;">

                    <div class="offline-card-img-wrapper">
                        <img src="./Images/Others/designer.jpg" alt="Graphic Design & Video Editing" class="offline-card-img">
                        <span class="badge bg-dark bg-opacity-75 text-white border border-white border-opacity-25 px-3 py-1.5 rounded-pill position-absolute top-0 end-0 m-3">
                            <i class="bi bi-vector-pen me-1 text-warning"></i> Graphic & Video
                        </span>
                    </div>

                    <div class="offline-card-body">
                        <div class="d-flex align-items-center gap-2 mb-2 text-muted small">
                            <span><i class="bi bi-clock me-1 text-primary"></i> 16 Weeks</span>
                            <span>•</span>
                            <span><i class="bi bi-award me-1 text-success"></i> Certificate</span>
                        </div>
                        <h5 class="fw-bold mb-2 text-dark">Graphic Design &amp; Video Editing</h5>
                        <!-- <p class="text-muted small mb-3">Learn Photoshop, Illustrator, Premiere Pro, and Motion Graphics.</p> -->
                        <div class="mt-auto">
                            <button class="btn btn-outline-dark w-100 rounded-pill py-2 text-decoration-none d-flex align-items-center justify-content-center gap-2">
                                <span>Explore Course</span>
                                <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Course 6: Photography -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="600">
                <div class="offline-course-card"
                    onclick="openCourseModal(
                        'Photography & Camera Handling',
                        './Images/Others/designer.jpeg',
                        'Learn professional photography, camera handling, studio lighting techniques, framing, and photo composition.',
                        ['Offline', '16 Weeks', 'Certification']
                    )"
                    style="cursor:pointer;">

                    <div class="offline-card-img-wrapper">
                        <img src="./Images/Others/designer1.jpeg" alt="Photography & Camera Handling" class="offline-card-img">
                        <span class="badge bg-dark bg-opacity-75 text-white border border-white border-opacity-25 px-3 py-1.5 rounded-pill position-absolute top-0 end-0 m-3">
                            <i class="bi bi-camera me-1 text-warning"></i> Photography
                        </span>
                    </div>

                    <div class="offline-card-body">
                        <div class="d-flex align-items-center gap-2 mb-2 text-muted small">
                            <span><i class="bi bi-clock me-1 text-primary"></i> 16 Weeks</span>
                            <span>•</span>
                            <span><i class="bi bi-award me-1 text-success"></i> Certificate</span>
                        </div>
                        <h5 class="fw-bold mb-2 text-dark">Photography &amp; Camera Handling</h5>
                        <!-- <p class="text-muted small mb-3">Master studio lighting, framing, composition, and camera settings.</p> -->
                        <div class="mt-auto">
                            <button class="btn btn-outline-dark w-100 rounded-pill py-2 text-decoration-none d-flex align-items-center justify-content-center gap-2">
                                <span>Explore Course</span>
                                <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Redesigned Executive Course Details Modal -->
<div class="modal fade" id="courseModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden" style="background: #ffffff;">
      
      <!-- Modal Header -->
      <div class="modal-header border-0 text-white p-4 position-relative" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%) !important;">
        <div class="pe-4">
          <span class="badge bg-primary bg-opacity-20 text-info border border-info border-opacity-25 rounded-pill px-3 py-1 mb-2 font-monospace small">GD Edu Tech Program</span>
          <h4 class="modal-title fw-bold text-white mb-0" id="courseTitle">Course Overview</h4>
        </div>
        <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Modal Body -->
      <div class="modal-body p-4">
        <div class="row g-4 align-items-center">
          
          <!-- Thumbnail Column -->
          <div class="col-12 col-md-5">
            <div class="position-relative rounded-4 overflow-hidden shadow-sm" style="min-height: 220px; background-color: #f8fafc;">
              <img id="courseImage" src="" class="w-100 h-100 object-fit-cover rounded-4" style="max-height: 240px;" alt="Course Thumbnail" onerror="this.src='./Images/Logos/GD_Only_logo.png';">
            </div>
          </div>

          <!-- Info Column -->
          <div class="col-12 col-md-7 d-flex flex-column justify-content-between">
            <div>
              <h6 class="fw-bold text-uppercase text-secondary small mb-2 tracking-wider"><i class="bi bi-info-circle text-primary me-1"></i> Course Overview</h6>
              <p id="courseDescription" class="text-secondary small mb-3 leading-relaxed" style="text-align: left;"></p>

              <div id="courseBadges" class="d-flex gap-2 flex-wrap mb-3"></div>
            </div>

            <!-- Action Footer -->
            <div class="pt-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
              <span class="text-muted small"><i class="bi bi-shield-check text-success me-1"></i> Industry Accredited</span>
              <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Close</button>
                <a href="contact.php" class="btn btn-primary btn-sm rounded-pill px-4 fw-semibold">
                  <i class="bi bi-rocket-takeoff-fill me-1"></i> Enroll Now
                </a>
              </div>
            </div>
          </div>

        </div>
      </div>

    </div>
  </div>
</div>

<!-- JS -->
<script>
function openCourseModal(title, image, description, badges) {
    document.getElementById("courseTitle").innerText = title;
    document.getElementById("courseImage").src = image;
    document.getElementById("courseDescription").innerText = description;

    let badgesHTML = "";
    if (Array.isArray(badges)) {
        badges.forEach(badge => {
            let badgeClass = "bg-white bg-opacity-10 text-secondary border";
            if (badge === "Offline") {
                badgeClass = "bg-white bg-opacity-10 text-success border border-success-subtle";
            } else if (badge === "16 Weeks" || badge.includes("Week") || badge.includes("Month")) {
                badgeClass = "bg-white bg-opacity-20 text-dark border border-warning-subtle";
            } else if (badge === "Certification" || badge.includes("Certif")) {
                badgeClass = "bg-info bg-opacity-10 text-info border border-info-subtle";
            }
            badgesHTML += `<span class="badge ${badgeClass} rounded-pill px-2.5 py-1 small me-1 mb-1">${badge}</span>`;
        });
    }
    document.getElementById("courseBadges").innerHTML = badgesHTML;

    const modal = new bootstrap.Modal(document.getElementById('courseModal'));
    modal.show();
}
</script>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Waypoints -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/waypoints/4.0.1/jquery.waypoints.min.js"></script>
    <!-- Counter Up -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Counter-Up/1.0.0/jquery.counterup.min.js"></script>
    <!-- Particles.js -->
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <!-- Back to Top Button -->
    <script src="js/back-to-top.js"></script>

    <script>
        // Initialize AOS with custom settings
        AOS.init({
            duration: 1000,
            easing: 'ease-in-out',
            once: true,
            mirror: false,
            offset: 120,
            delay: 100
        });

        // Mobile Search State Management
        const mobileSearchState = {
            currentCategory: <?php echo $category_filter; ?>,
            currentSearch: '<?php echo htmlspecialchars($search); ?>',
            isFiltering: false
        };

        // Debounce function for search input
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Mobile Search Functions
        function selectCategory(categoryId) {
            mobileSearchState.currentCategory = categoryId;
            // Update UI to reflect selected category
            document.querySelectorAll('.category-chip').forEach(chip => {
                const chipCategoryId = parseInt(chip.getAttribute('data-category-id'));
                chip.classList.toggle('active', chipCategoryId === categoryId);
            });
        }

        function clearSearch() {
            mobileSearchState.currentSearch = '';
            const searchInput = document.getElementById('mobileSearchInput');
            if (searchInput) {
                searchInput.value = '';
                filterCoursesMobile();
            }
        }

        function clearCategory() {
            mobileSearchState.currentCategory = 0;
            document.querySelectorAll('.category-chip').forEach(chip => {
                chip.classList.toggle('active', chip.getAttribute('data-category-id') === '0');
            });
            filterCoursesMobile();
        }

        function applyFilters() {
            filterCoursesMobile();
            // Close the modal
            const filterModal = document.getElementById('filterModal');
            if (filterModal) {
                const modal = bootstrap.Modal.getInstance(filterModal);
                if (modal) {
                    modal.hide();
                }
            }
        }

        function filterCoursesMobile() {
            if (mobileSearchState.isFiltering) return;
            mobileSearchState.isFiltering = true;

            const searchInput = document.getElementById('mobileSearchInput');
            const searchValue = searchInput ? searchInput.value : '';
            mobileSearchState.currentSearch = searchValue;

            const coursesContainer = document.querySelector('.row.g-4');
            if (!coursesContainer) {
                mobileSearchState.isFiltering = false;
                return;
            }

            // Show loading state
            coursesContainer.style.opacity = '0.5';

            // Prepare URL with parameters
            const url = new URL(window.location.href);
            url.searchParams.set('search', searchValue);
            url.searchParams.set('category', mobileSearchState.currentCategory);

            // Update URL without reloading the page
            window.history.pushState({}, '', url);

            // Fetch filtered results
            fetch(url)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.text();
                })
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newCourses = doc.querySelector('.row.g-4');
                    const newActiveFilters = doc.querySelector('.active-filters');
                    
                    if (newCourses) {
                        // Animate out old content
                        coursesContainer.style.opacity = '0';
                        
                        setTimeout(() => {
                            // Update content
                            coursesContainer.innerHTML = newCourses.innerHTML;
                            
                            // Update active filters if they exist
                            const activeFiltersContainer = document.querySelector('.active-filters');
                            if (activeFiltersContainer && newActiveFilters) {
                                activeFiltersContainer.innerHTML = newActiveFilters.innerHTML;
                            } else if (activeFiltersContainer) {
                                activeFiltersContainer.innerHTML = '';
                            }
                            
                            // Animate in new content
                            coursesContainer.style.opacity = '1';
                            
                            // Reinitialize AOS for new elements
                            AOS.refresh();
                        }, 300);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    coursesContainer.style.opacity = '1';
                })
                .finally(() => {
                    mobileSearchState.isFiltering = false;
                });
        }

        // Initialize mobile search functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize search input
            const searchInput = document.getElementById('mobileSearchInput');
            if (searchInput) {
                // Set initial value
                searchInput.value = mobileSearchState.currentSearch;
                
                // Add input event listener with debounce
                searchInput.addEventListener('input', debounce(function() {
                    filterCoursesMobile();
                }, 500));
            }

            // Initialize category chips
            document.querySelectorAll('.category-chip').forEach(chip => {
                const categoryId = chip.getAttribute('data-category-id');
                if (parseInt(categoryId) === mobileSearchState.currentCategory) {
                    chip.classList.add('active');
                }
            });

            // Initialize filter modal
            const filterModal = document.getElementById('filterModal');
            if (filterModal) {
                new bootstrap.Modal(filterModal, {
                    backdrop: 'static',
                    keyboard: false
                });
            }
        });

        // Course Search AJAX
        document.getElementById('courseSearchForm').addEventListener('submit', function(e) {
            e.preventDefault();
            filterCourses();
        });

        document.getElementById('searchInput').addEventListener('input', debounce(function() {
            filterCourses();
        }, 500));

        document.getElementById('categorySelect').addEventListener('change', function() {
            filterCourses();
        });

        function filterCourses() {
            const searchValue = document.getElementById('searchInput').value;
            const categoryValue = document.getElementById('categorySelect').value;
            const coursesContainer = document.querySelector('.row.g-4');
            const searchSpinner = document.querySelector('.search-spinner');
            
            // Show loading state
            searchSpinner.classList.remove('d-none');
            coursesContainer.style.opacity = '0.5';

            // Prepare URL with parameters
            const url = new URL(window.location.href);
            url.searchParams.set('search', searchValue);
            url.searchParams.set('category', categoryValue);

            // Update URL without reloading the page
            window.history.pushState({}, '', url);

            // Fetch filtered results
            fetch(url)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newCourses = doc.querySelector('.row.g-4');
                    
                    // Animate out old content
                    coursesContainer.style.opacity = '0';
                    
                    setTimeout(() => {
                        // Update content
                        coursesContainer.innerHTML = newCourses.innerHTML;
                        
                        // Animate in new content
                        coursesContainer.style.opacity = '1';
                        
                        // Reinitialize AOS for new elements
                        AOS.refresh();
                        
                        // Hide loading state
                        searchSpinner.classList.add('d-none');
                    }, 300);
                })
                .catch(error => {
                    console.error('Error:', error);
                    searchSpinner.classList.add('d-none');
                    coursesContainer.style.opacity = '1';
                });
        }

        // Floating shapes animation
        const shapes = document.querySelectorAll('.floating-shape');
        shapes.forEach((shape, index) => {
            shape.style.animation = `float ${3 + index}s ease-in-out infinite`;
        });

        // Initialize particles.js
        particlesJS('particles-js', {
            "particles": {
                "number": {
                    "value": 80,
                    "density": {
                        "enable": true,
                        "value_area": 800
                    }
                },
                "color": {
                    "value": "#ffffff"
                },
                "shape": {
                    "type": "circle",
                    "stroke": {
                        "width": 0,
                        "color": "#000000"
                    },
                    "polygon": {
                        "nb_sides": 5
                    }
                },
                "opacity": {
                    "value": 0.3,
                    "random": true,
                    "anim": {
                        "enable": false,
                        "speed": 1,
                        "opacity_min": 0.1,
                        "sync": false
                    }
                },
                "size": {
                    "value": 3,
                    "random": true,
                    "anim": {
                        "enable": false,
                        "speed": 40,
                        "size_min": 0.1,
                        "sync": false
                    }
                },
                "line_linked": {
                    "enable": true,
                    "distance": 150,
                    "color": "#ffffff",
                    "opacity": 0.2,
                    "width": 1
                },
                "move": {
                    "enable": true,
                    "speed": 2,
                    "direction": "none",
                    "random": false,
                    "straight": false,
                    "out_mode": "out",
                    "bounce": false,
                    "attract": {
                        "enable": false,
                        "rotateX": 600,
                        "rotateY": 1200
                    }
                }
            },
            "interactivity": {
                "detect_on": "canvas",
                "events": {
                    "onhover": {
                        "enable": true,
                        "mode": "grab"
                    },
                    "onclick": {
                        "enable": true,
                        "mode": "push"
                    },
                    "resize": true
                },
                "modes": {
                    "grab": {
                        "distance": 140,
                        "line_linked": {
                            "opacity": 0.5
                        }
                    },
                    "push": {
                        "particles_nb": 4
                    }
                }
            },
            "retina_detect": true
        });

        // Counter Animation
        $('.counter').counterUp({
            delay: 10,
            time: 1000
        });

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
                navbar.classList.remove('navbar-dark');
                navbar.classList.add('navbar-light');
            } else {
                navbar.classList.remove('scrolled');
                navbar.classList.remove('navbar-light');
                navbar.classList.add('navbar-dark');
            }
        });
        
    </script>
    <style>
        
        /* Page Header */
        .page-header {
            position: relative;
            overflow: hidden;
        }
        
        .page-header .wave-bottom {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
        }

        /* Mobile Search Panel Styles */
        .mobile-search-panel {
            background: #ffffff;
            padding: 0.75rem 0;
            position: relative;
            z-index: 1000;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        .mobile-search-bar {
            position: relative;
            padding: 0 0.5rem;
        }

        .search-input-wrapper {
            display: flex;
            align-items: center;
            background: #f8f9fa;
            border-radius: 16px;
            padding: 0.625rem 1rem;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.08);
            transition: all 0.2s ease;
        }

        .search-input-wrapper:focus-within {
            background: #ffffff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-color: var(--primary);
        }

        .search-input-wrapper i {
            color: #6c757d;
            font-size: 1.1rem;
            margin-right: 0.75rem;
            flex-shrink: 0;
        }

        .search-input-wrapper .form-control {
            border: none;
            padding: 0.375rem 0;
            font-size: 0.95rem;
            background: transparent;
            color: #2c3e50;
            font-weight: 500;
        }

        .search-input-wrapper .form-control::placeholder {
            color: #adb5bd;
            font-weight: normal;
        }

        .search-input-wrapper .form-control:focus {
            box-shadow: none;
        }

        .btn-filter {
            background: #ffffff;
            border: 1px solid rgba(0,0,0,0.08);
            color: #6c757d;
            padding: 0.5rem;
            border-radius: 12px;
            margin-left: 0.75rem;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .btn-filter:hover, .btn-filter:focus {
            background: var(--primary);
            color: #ffffff;
            border-color: var(--primary);
        }

        .btn-filter i {
            font-size: 1.1rem;
            margin: 0;
        }

        /* Active Filters Styles */
        .active-filters {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            -ms-overflow-style: none;
            padding: 0.5rem 0.75rem;
            margin-top: 0.5rem;
        }

        .active-filters::-webkit-scrollbar {
            display: none;
        }

        .active-filters-scroll {
            display: flex;
            gap: 0.5rem;
            padding: 0.25rem 0;
        }

        .filter-tag {
            display: inline-flex;
            align-items: center;
            background: rgba(var(--primary-rgb), 0.1);
            color: var(--primary);
            padding: 0.375rem 0.875rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
            white-space: nowrap;
            border: 1px solid rgba(var(--primary-rgb), 0.2);
            transition: all 0.2s ease;
        }

        .filter-tag:hover {
            background: rgba(var(--primary-rgb), 0.15);
        }

        .btn-close-filter {
            background: none;
            border: none;
            color: var(--primary);
            padding: 0.25rem;
            margin-left: 0.5rem;
            font-size: 0.875rem;
            opacity: 0.7;
            transition: opacity 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-close-filter:hover {
            opacity: 1;
        }

        /* Filter Modal Styles */
        .modal-dialog-bottom {
            margin: 0;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            transform: translateY(100%);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            max-width: 100%;
        }

        .modal-dialog-bottom.show {
            transform: translateY(0);
        }

        .modal-content {
            border-radius: 24px 24px 0 0;
            border: none;
            box-shadow: 0 -4px 24px rgba(0,0,0,0.1);
        }

        .modal-header {
            border-bottom: 1px solid rgba(0,0,0,0.08);
            padding: 1.25rem 1.5rem;
            background: #ffffff;
        }

        .modal-header .modal-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #2c3e50;
        }

        .modal-body {
            padding: 1.5rem;
            max-height: 70vh;
            overflow-y: auto;
            background: #f8f9fa;
        }

        .modal-footer {
            border-top: 1px solid rgba(0,0,0,0.08);
            padding: 1rem 1.5rem;
            background: #ffffff;
        }

        .modal-footer .btn {
            padding: 0.625rem 1.5rem;
            font-weight: 500;
            border-radius: 12px;
        }

        .filter-section {
            margin-bottom: 1.5rem;
        }

        .filter-label {
            font-weight: 600;
            margin-bottom: 1rem;
            display: block;
            color: #2c3e50;
            font-size: 1rem;
        }

        .category-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 0.625rem;
        }

        .category-chip {
            background: #ffffff;
            border: 1px solid rgba(0,0,0,0.08);
            border-radius: 16px;
            padding: 0.625rem 1rem;
            font-size: 0.875rem;
            color: #6c757d;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .category-chip:hover {
            background: #f8f9fa;
            border-color: rgba(0,0,0,0.12);
        }

        .category-chip.active {
            background: var(--primary);
            color: #ffffff;
            border-color: var(--primary);
            box-shadow: 0 2px 8px rgba(var(--primary-rgb), 0.3);
        }

        .course-count {
            background: rgba(255,255,255,0.2);
            padding: 0.125rem 0.5rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .category-chip:not(.active) .course-count {
            background: #f1f3f5;
            color: #6c757d;
        }

        /* Add CSS Variables for Colors */
        :root {
            --primary-rgb: 13, 110, 253; /* Bootstrap primary color RGB values */
        }

        /* Mobile-specific adjustments */
        @media (max-width: 991.98px) {
            .navbar {
                /*position: sticky;*/
                    margin-top:10px;
                top: 0;
                z-index: 1001;
            }

            .mobile-search-panel {
                position: relative;
                top: auto;
                padding: 0.625rem 0;
                margin-bottom: 1rem;
            }

            .search-input-wrapper {
                padding: 0.5rem 0.875rem;
            }

            .search-input-wrapper .form-control {
                font-size: 0.9rem;
            }

            .btn-filter {
                width: 32px;
                height: 32px;
                padding: 0.375rem;
            }

            .filter-tag {
                padding: 0.25rem 0.75rem;
                font-size: 0.8125rem;
            }

            .modal-header {
                padding: 1rem 1.25rem;
            }

            .modal-body {
                padding: 1.25rem;
            }

            .modal-footer {
                padding: 0.875rem 1.25rem;
            }

            .category-chip {
                padding: 0.5rem 0.875rem;
                font-size: 0.8125rem;
            }
        }

        /* Add smooth scrolling for iOS */
        @supports (-webkit-touch-callout: none) {
            .modal-body {
                -webkit-overflow-scrolling: touch;
            }
        }
    </style>
</body>

</html>