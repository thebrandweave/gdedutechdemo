<?php
session_start();
require_once './Configurations/config.php';

// Fetch all published graduate projects grouped by course
$query = "SELECT * FROM graduate_projects WHERE status = 'published' ORDER BY course_name ASC, id ASC";
$result = mysqli_query($conn, $query);

$courses_data = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $cname = $row['course_name'];
        if (!isset($courses_data[$cname])) {
            $courses_data[$cname] = [
                'name' => $cname,
                'slug' => preg_replace('/[^a-z0-9]+/i', '-', strtolower(trim($cname))),
                'students' => []
            ];
        }
        $courses_data[$cname]['students'][] = $row;
    }
}

// Icon helper for course types
function getCourseIcon($courseName) {
    $c = strtolower($courseName);
    if (strpos($c, 'architect') !== false) return 'bi-buildings';
    if (strpos($c, 'interior') !== false) return 'bi-house-door';
    if (strpos($c, 'full stack') !== false || strpos($c, 'web') !== false) return 'bi-code-slash';
    if (strpos($c, 'python') !== false || strpos($c, 'ai') !== false) return 'bi-cpu';
    if (strpos($c, 'marketing') !== false) return 'bi-megaphone';
    if (strpos($c, 'graphic') !== false || strpos($c, 'video') !== false) return 'bi-palette';
    if (strpos($c, 'photo') !== false || strpos($c, 'camera') !== false) return 'bi-camera';
    if (strpos($c, 'tally') !== false || strpos($c, 'gst') !== false || strpos($c, 'account') !== false) return 'bi-calculator';
    return 'bi-mortarboard';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Graduates &amp; Projects - GD Edu Tech</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AOS Animation Library -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="./css/style.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="./Images/Logos/GD_Only_logo.png">

    <style>
        body {
            background-color: #f8fafc;
            font-family: 'Montserrat', sans-serif;
            color: #0f172a;
        }

        .graduates-hero-section {
            background: linear-gradient(135deg, #0b1f3a 0%, #005b8e 50%, #0284c7 100%);
            color: white;
            padding: 140px 0 70px 0;
            position: relative;
            overflow: hidden;
        }

        .graduates-hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 20% 40%, rgba(255, 255, 255, 0.12) 0%, transparent 50%),
                        radial-gradient(circle at 80% 80%, rgba(0, 212, 255, 0.15) 0%, transparent 60%);
            pointer-events: none;
        }

        .course-nav-card {
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 30px -10px rgba(15, 23, 42, 0.08);
            position: sticky;
            top: 100px;
            overflow: hidden;
        }

        .course-nav-header {
            padding: 20px 24px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-bottom: 1px solid #e2e8f0;
        }

        .course-nav-list {
            padding: 12px;
            max-height: calc(100vh - 220px);
            overflow-y: auto;
        }

        .course-nav-list::-webkit-scrollbar {
            width: 6px;
        }

        .course-nav-list::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .course-nav-btn {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            padding: 14px 18px;
            margin-bottom: 8px;
            border: 1.5px solid transparent;
            border-radius: 14px;
            background: #ffffff;
            color: #334155;
            font-weight: 600;
            font-size: 0.93rem;
            text-align: left;
            transition: all 0.25s ease;
            text-decoration: none;
            cursor: pointer;
        }

        .course-nav-btn:hover {
            background: #f0f9ff;
            color: #0284c7;
            border-color: #bae6fd;
            transform: translateX(4px);
        }

        .course-nav-btn.active {
            background: linear-gradient(135deg, #005b8e 0%, #0284c7 100%);
            color: #ffffff !important;
            border-color: transparent;
            box-shadow: 0 8px 20px rgba(2, 132, 199, 0.25);
        }

        .course-nav-btn.active .course-icon-box {
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
        }

        .course-nav-btn.active .badge-count {
            background: rgba(255, 255, 255, 0.25) !important;
            color: #ffffff !important;
        }

        .course-icon-box {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: #e0f2fe;
            color: #0284c7;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
            transition: all 0.25s ease;
        }

        .badge-count {
            font-size: 0.78rem;
            padding: 5px 10px;
            border-radius: 50px;
            background: #f1f5f9;
            color: #475569;
            font-weight: 700;
        }

        /* Student & Project Showcase Card */
        .showcase-card {
            background: #ffffff;
            border-radius: 24px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 15px 40px -10px rgba(15, 23, 42, 0.08);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .student-header-banner {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            padding: 30px;
            color: white;
            position: relative;
        }

        .student-avatar {
            width: 105px;
            height: 105px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #ffffff;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
            background: #ffffff;
        }

        .student-avatar-fallback {
            width: 105px;
            height: 105px;
            border-radius: 50%;
            border: 4px solid #ffffff;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
            background: linear-gradient(135deg, #0284c7 0%, #38bdf8 100%);
            color: white;
            font-size: 2.2rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .domain-pill {
            background: rgba(2, 132, 199, 0.15);
            color: #38bdf8;
            border: 1px solid rgba(56, 189, 248, 0.3);
            border-radius: 50px;
            padding: 6px 16px;
            font-weight: 600;
            font-size: 0.88rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .project-details-body {
            padding: 35px;
        }

        .project-title-heading {
            font-size: 1.65rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.35;
        }

        .tech-badge {
            background: #f1f5f9;
            color: #0369a1;
            border: 1px solid #cbd5e1;
            border-radius: 50px;
            padding: 6px 14px;
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        .feature-item {
            padding: 10px 14px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            margin-bottom: 8px;
            font-size: 0.92rem;
            color: #334155;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .feature-icon {
            color: #10b981;
            font-size: 1.1rem;
            flex-shrink: 0;
            margin-top: 1px;
        }

        /* Image Gallery */
        .project-gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .gallery-image-thumb {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 14px;
            border: 1.5px solid #e2e8f0;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .gallery-image-thumb:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
            border-color: #0284c7;
        }

        /* Auto-Slide Carousel Controls */
        .carousel-control-prev,
        .carousel-control-next {
            width: 46px;
            height: 46px;
            background: #0f172a;
            border-radius: 50%;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0.85;
            transition: all 0.3s ease;
        }

        .carousel-control-prev { left: -20px; }
        .carousel-control-next { right: -20px; }

        .carousel-control-prev:hover,
        .carousel-control-next:hover {
            opacity: 1;
            background: #0284c7;
            transform: translateY(-50%) scale(1.1);
        }

        .student-counter-badge {
            background: #ffffff;
            color: #0f172a;
            border-radius: 50px;
            padding: 5px 14px;
            font-weight: 700;
            font-size: 0.82rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
        }

        .auto-slide-indicator {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8rem;
            color: #0284c7;
            background: #e0f2fe;
            padding: 4px 12px;
            border-radius: 50px;
            font-weight: 700;
        }

        .auto-slide-indicator .spin-dot {
            width: 8px;
            height: 8px;
            background: #0284c7;
            border-radius: 50%;
            animation: pulse-dot 1.5s infinite;
        }

        @keyframes pulse-dot {
            0% { transform: scale(0.9); opacity: 0.7; }
            50% { transform: scale(1.3); opacity: 1; }
            100% { transform: scale(0.9); opacity: 0.7; }
        }

        @media (max-width: 991px) {
            .course-nav-card {
                position: static;
                margin-bottom: 30px;
            }
            .course-nav-list {
                max-height: 280px;
            }
            .carousel-control-prev { left: 5px; }
            .carousel-control-next { right: 5px; }
            .project-details-body { padding: 25px 20px; }
            .student-header-banner { padding: 25px 20px; }
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <?php include 'navbar.php'; ?>

    <!-- Header Section -->
    <section class="graduates-hero-section text-center">
        <div class="container position-relative z-2">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb justify-content-center px-3 py-1.5 rounded-pill mb-3 d-inline-flex bg-white bg-opacity-10">
                    <li class="breadcrumb-item"><a href="index.php" class="text-white text-decoration-none"><i class="bi bi-house-door-fill me-1"></i> Home</a></li>
                    <li class="breadcrumb-item"><a href="about.php" class="text-white text-decoration-none">About</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page">Our Graduates</li>
                </ol>
            </nav>
            <h1 class="display-4 fw-bold mb-3" data-aos="fade-up">
                Our Graduates &amp; <span style="color: #38bdf8;">Project Showcase</span>
            </h1>
            <p class="lead mx-auto mb-0" style="max-width: 720px; color: #e2e8f0;" data-aos="fade-up" data-aos-delay="100">
                Explore hands-on capstone projects, industry-standard CAD blueprints, software applications, and design portfolios crafted by GD Edu Tech graduates.
            </p>
        </div>
    </section>

    <!-- Main Content Section: Left Sidebar Courses + Right Section Student Showcase -->
    <div class="container py-5">
        <div class="row g-4">

            <!-- LEFT SECTION: All Courses List -->
            <div class="col-lg-4 col-xl-3">
                <div class="course-nav-card" data-aos="fade-right">
                    <div class="course-nav-header d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-grid-fill text-primary me-2"></i>All Courses</h5>
                            <small class="text-muted"><?php echo count($courses_data); ?> Technical Domains</small>
                        </div>
                    </div>

                    <div class="course-nav-list" role="tablist">
                        <?php 
                        $first_course = true;
                        foreach ($courses_data as $course_name => $cdata): 
                            $slug = $cdata['slug'];
                            $student_count = count($cdata['students']);
                            $icon = getCourseIcon($course_name);
                        ?>
                            <button class="course-nav-btn <?php echo $first_course ? 'active' : ''; ?>"
                                    id="tab-btn-<?php echo $slug; ?>"
                                    data-bs-toggle="pill"
                                    data-bs-target="#pane-<?php echo $slug; ?>"
                                    type="button"
                                    role="tab"
                                    aria-controls="pane-<?php echo $slug; ?>"
                                    aria-selected="<?php echo $first_course ? 'true' : 'false'; ?>">
                                <div class="d-flex align-items-center gap-2.5 overflow-hidden">
                                    <div class="course-icon-box">
                                        <i class="bi <?php echo $icon; ?>"></i>
                                    </div>
                                    <span class="text-truncate text-start"><?php echo htmlspecialchars($course_name); ?></span>
                                </div>
                                <span class="badge-count ms-2"><?php echo $student_count; ?> <?php echo $student_count > 1 ? 'Grads' : 'Grad'; ?></span>
                            </button>
                        <?php 
                            $first_course = false;
                        endforeach; 
                        ?>
                    </div>
                </div>
            </div>

            <!-- RIGHT SECTION: Selected Course's Students & Project Showcase (Auto-Slide if multiple students) -->
            <div class="col-lg-8 col-xl-9">
                <div class="tab-content" id="coursesShowcaseContent">
                    <?php 
                    $first_pane = true;
                    foreach ($courses_data as $course_name => $cdata): 
                        $slug = $cdata['slug'];
                        $students = $cdata['students'];
                        $student_count = count($students);
                        $is_multiple = ($student_count > 1);
                        $carousel_id = "carousel-" . $slug;
                    ?>
                        <div class="tab-pane fade <?php echo $first_pane ? 'show active' : ''; ?>"
                             id="pane-<?php echo $slug; ?>"
                             role="tabpanel"
                             aria-labelledby="tab-btn-<?php echo $slug; ?>">

                            <!-- Top Bar for Current Course -->
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 p-3 bg-white rounded-4 border shadow-sm">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="course-icon-box" style="width: 44px; height: 44px; font-size: 1.3rem;">
                                        <i class="bi <?php echo getCourseIcon($course_name); ?>"></i>
                                    </div>
                                    <div>
                                        <h4 class="fw-bold mb-0 text-dark"><?php echo htmlspecialchars($course_name); ?></h4>
                                        <small class="text-muted">Graduates &amp; Final Project Showcase</small>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center gap-2">
                                    <?php if ($is_multiple): ?>
                                        <span class="auto-slide-indicator">
                                            <span class="spin-dot"></span> Auto-slide Active (<?php echo $student_count; ?> Students)
                                        </span>
                                    <?php else: ?>
                                        <span class="student-counter-badge">1 Certified Graduate</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Showcase Area: Auto-slide Carousel if Multiple Students, Single Card otherwise -->
                            <?php if ($is_multiple): ?>
                                <div id="<?php echo $carousel_id; ?>"
                                     class="carousel slide position-relative"
                                     data-bs-ride="carousel"
                                     data-bs-interval="6000"
                                     data-bs-pause="hover">

                                    <div class="carousel-inner">
                                        <?php foreach ($students as $idx => $st): 
                                            $active_class = ($idx === 0) ? 'active' : '';
                                            $project_imgs = json_decode($st['project_images'], true) ?: [];
                                            $key_feats = json_decode($st['key_features'], true) ?: [];
                                            $techs = array_filter(array_map('trim', explode(',', $st['technologies_used'] ?? '')));
                                        ?>
                                            <div class="carousel-item <?php echo $active_class; ?>">
                                                <div class="showcase-card">

                                                    <!-- Student Profile Header Banner -->
                                                    <div class="student-header-banner">
                                                        <div class="d-flex flex-column flex-md-row align-items-center gap-4 text-center text-md-start">
                                                            <div>
                                                                <?php if (!empty($st['student_photo']) && file_exists($st['student_photo'])): ?>
                                                                    <img src="<?php echo htmlspecialchars($st['student_photo']); ?>" alt="<?php echo htmlspecialchars($st['student_name']); ?>" class="student-avatar">
                                                                <?php else: ?>
                                                                    <div class="student-avatar-fallback">
                                                                        <?php 
                                                                        $parts = explode(' ', $st['student_name']);
                                                                        $ini = strtoupper(substr($parts[0] ?? '', 0, 1) . substr($parts[1] ?? '', 0, 1));
                                                                        echo htmlspecialchars($ini ?: 'GD');
                                                                        ?>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>

                                                            <div class="flex-grow-1">
                                                                <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-between gap-2 mb-2">
                                                                    <h3 class="fw-bold mb-0 text-white"><?php echo htmlspecialchars($st['student_name']); ?></h3>
                                                                    <span class="badge bg-white text-dark px-3 py-1.5 rounded-pill fw-semibold">
                                                                        Graduate <?php echo ($idx + 1); ?> of <?php echo $student_count; ?>
                                                                    </span>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <span class="domain-pill">
                                                                        <i class="bi bi-briefcase-fill"></i>
                                                                        <span>Domain: <?php echo htmlspecialchars($st['domain']); ?></span>
                                                                    </span>
                                                                </div>

                                                                <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-start gap-3 small text-white-50">
                                                                    <?php if (!empty($st['student_id'])): ?>
                                                                        <span><i class="bi bi-person-badge me-1 text-info"></i> ID: <strong class="text-white"><?php echo htmlspecialchars($st['student_id']); ?></strong></span>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($st['completion_date'])): ?>
                                                                        <span><i class="bi bi-calendar-check me-1 text-info"></i> Completed: <strong class="text-white"><?php echo htmlspecialchars($st['completion_date']); ?></strong></span>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($st['student_id'])): ?>
                                                                        <a href="verify_certificate.php?student_id=<?php echo urlencode($st['student_id']); ?>#verification-results" class="btn btn-sm btn-outline-info rounded-pill px-3 py-1 text-white">
                                                                            <i class="bi bi-patch-check-fill me-1"></i> Verify Record
                                                                        </a>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Project Details Body -->
                                                    <div class="project-details-body">
                                                        <div class="mb-4">
                                                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                                                <span class="text-uppercase fw-bold text-primary small" style="letter-spacing: 1px;">Capstone Project</span>
                                                                <?php if (!empty($st['project_url'])): ?>
                                                                    <a href="<?php echo htmlspecialchars($st['project_url']); ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                                        <i class="bi bi-box-arrow-up-right me-1"></i> Live Project
                                                                    </a>
                                                                <?php endif; ?>
                                                            </div>
                                                            <h4 class="project-title-heading mb-3"><?php echo htmlspecialchars($st['project_title']); ?></h4>
                                                            <p class="text-secondary leading-relaxed mb-4" style="font-size: 0.98rem; line-height: 1.7;">
                                                                <?php echo nl2br(htmlspecialchars($st['project_description'])); ?>
                                                            </p>
                                                        </div>

                                                        <!-- Technologies Used Badges -->
                                                        <?php if (!empty($techs)): ?>
                                                            <div class="mb-4">
                                                                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-tools text-primary me-2"></i>Technologies &amp; Tools Used:</h6>
                                                                <div class="d-flex flex-wrap gap-2">
                                                                    <?php foreach ($techs as $tech): ?>
                                                                        <span class="tech-badge">
                                                                            <i class="bi bi-check-circle-fill text-primary me-1"></i><?php echo htmlspecialchars($tech); ?>
                                                                        </span>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>

                                                        <!-- Key Deliverables / Features -->
                                                        <?php if (!empty($key_feats)): ?>
                                                            <div class="mb-4">
                                                                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-stars text-warning me-2"></i>Key Project Highlights:</h6>
                                                                <div class="row g-2">
                                                                    <?php foreach ($key_feats as $feat): ?>
                                                                        <div class="col-md-6">
                                                                            <div class="feature-item">
                                                                                <i class="bi bi-check-circle-fill feature-icon"></i>
                                                                                <span><?php echo htmlspecialchars($feat); ?></span>
                                                                            </div>
                                                                        </div>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>

                                                        <!-- Project Images Gallery -->
                                                        <?php if (!empty($project_imgs)): ?>
                                                            <div>
                                                                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-images text-primary me-2"></i>Project Screenshots &amp; Blueprints:</h6>
                                                                <div class="project-gallery-grid">
                                                                    <?php foreach ($project_imgs as $img_idx => $img_path): 
                                                                        if (!file_exists($img_path)) continue;
                                                                    ?>
                                                                        <img src="<?php echo htmlspecialchars($img_path); ?>"
                                                                             alt="Project image <?php echo ($img_idx + 1); ?>"
                                                                             class="gallery-image-thumb"
                                                                             onclick="openImageModal('<?php echo htmlspecialchars($img_path); ?>', '<?php echo htmlspecialchars(addslashes($st['project_title'])); ?>')"
                                                                             title="Click to view full size">
                                                                    <?php endforeach; ?>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>

                                                    </div> <!-- /project-details-body -->
                                                </div> <!-- /showcase-card -->
                                            </div> <!-- /carousel-item -->
                                        <?php endforeach; ?>
                                    </div>

                                    <!-- Carousel Controls -->
                                    <button class="carousel-control-prev" type="button" data-bs-target="#<?php echo $carousel_id; ?>" data-bs-slide="prev">
                                        <i class="bi bi-chevron-left text-white fs-5"></i>
                                        <span class="visually-hidden">Previous</span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#<?php echo $carousel_id; ?>" data-bs-slide="next">
                                        <i class="bi bi-chevron-right text-white fs-5"></i>
                                        <span class="visually-hidden">Next</span>
                                    </button>
                                </div>

                            <?php else: 
                                // Single Student Display (No Carousel needed)
                                $st = $students[0];
                                $project_imgs = json_decode($st['project_images'], true) ?: [];
                                $key_feats = json_decode($st['key_features'], true) ?: [];
                                $techs = array_filter(array_map('trim', explode(',', $st['technologies_used'] ?? '')));
                            ?>
                                <div class="showcase-card">

                                    <!-- Student Profile Header Banner -->
                                    <div class="student-header-banner">
                                        <div class="d-flex flex-column flex-md-row align-items-center gap-4 text-center text-md-start">
                                            <div>
                                                <?php if (!empty($st['student_photo']) && file_exists($st['student_photo'])): ?>
                                                    <img src="<?php echo htmlspecialchars($st['student_photo']); ?>" alt="<?php echo htmlspecialchars($st['student_name']); ?>" class="student-avatar">
                                                <?php else: ?>
                                                    <div class="student-avatar-fallback">
                                                        <?php 
                                                        $parts = explode(' ', $st['student_name']);
                                                        $ini = strtoupper(substr($parts[0] ?? '', 0, 1) . substr($parts[1] ?? '', 0, 1));
                                                        echo htmlspecialchars($ini ?: 'GD');
                                                        ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="flex-grow-1">
                                                <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-between gap-2 mb-2">
                                                    <h3 class="fw-bold mb-0 text-white"><?php echo htmlspecialchars($st['student_name']); ?></h3>
                                                    <span class="badge bg-white text-dark px-3 py-1.5 rounded-pill fw-semibold">
                                                        Certified Graduate
                                                    </span>
                                                </div>

                                                <div class="mb-3">
                                                    <span class="domain-pill">
                                                        <i class="bi bi-briefcase-fill"></i>
                                                        <span>Domain: <?php echo htmlspecialchars($st['domain']); ?></span>
                                                    </span>
                                                </div>

                                                <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-start gap-3 small text-white-50">
                                                    <?php if (!empty($st['student_id'])): ?>
                                                        <span><i class="bi bi-person-badge me-1 text-info"></i> ID: <strong class="text-white"><?php echo htmlspecialchars($st['student_id']); ?></strong></span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($st['completion_date'])): ?>
                                                        <span><i class="bi bi-calendar-check me-1 text-info"></i> Completed: <strong class="text-white"><?php echo htmlspecialchars($st['completion_date']); ?></strong></span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($st['student_id'])): ?>
                                                        <a href="verify_certificate.php?student_id=<?php echo urlencode($st['student_id']); ?>#verification-results" class="btn btn-sm btn-outline-info rounded-pill px-3 py-1 text-white">
                                                            <i class="bi bi-patch-check-fill me-1"></i> Verify Record
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Project Details Body -->
                                    <div class="project-details-body">
                                        <div class="mb-4">
                                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                                <span class="text-uppercase fw-bold text-primary small" style="letter-spacing: 1px;">Capstone Project</span>
                                                <?php if (!empty($st['project_url'])): ?>
                                                    <a href="<?php echo htmlspecialchars($st['project_url']); ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                        <i class="bi bi-box-arrow-up-right me-1"></i> Live Project
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                            <h4 class="project-title-heading mb-3"><?php echo htmlspecialchars($st['project_title']); ?></h4>
                                            <p class="text-secondary leading-relaxed mb-4" style="font-size: 0.98rem; line-height: 1.7;">
                                                <?php echo nl2br(htmlspecialchars($st['project_description'])); ?>
                                            </p>
                                        </div>

                                        <!-- Technologies Used Badges -->
                                        <?php if (!empty($techs)): ?>
                                            <div class="mb-4">
                                                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-tools text-primary me-2"></i>Technologies &amp; Tools Used:</h6>
                                                <div class="d-flex flex-wrap gap-2">
                                                    <?php foreach ($techs as $tech): ?>
                                                        <span class="tech-badge">
                                                            <i class="bi bi-check-circle-fill text-primary me-1"></i><?php echo htmlspecialchars($tech); ?>
                                                        </span>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Key Deliverables / Features -->
                                        <?php if (!empty($key_feats)): ?>
                                            <div class="mb-4">
                                                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-stars text-warning me-2"></i>Key Project Highlights:</h6>
                                                <div class="row g-2">
                                                    <?php foreach ($key_feats as $feat): ?>
                                                        <div class="col-md-6">
                                                            <div class="feature-item">
                                                                <i class="bi bi-check-circle-fill feature-icon"></i>
                                                                <span><?php echo htmlspecialchars($feat); ?></span>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Project Images Gallery -->
                                        <?php if (!empty($project_imgs)): ?>
                                            <div>
                                                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-images text-primary me-2"></i>Project Screenshots &amp; Blueprints:</h6>
                                                <div class="project-gallery-grid">
                                                    <?php foreach ($project_imgs as $img_idx => $img_path): 
                                                        if (!file_exists($img_path)) continue;
                                                    ?>
                                                        <img src="<?php echo htmlspecialchars($img_path); ?>"
                                                             alt="Project image <?php echo ($img_idx + 1); ?>"
                                                             class="gallery-image-thumb"
                                                             onclick="openImageModal('<?php echo htmlspecialchars($img_path); ?>', '<?php echo htmlspecialchars(addslashes($st['project_title'])); ?>')"
                                                             title="Click to view full size">
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                    </div> <!-- /project-details-body -->
                                </div> <!-- /showcase-card -->
                            <?php endif; ?>

                        </div> <!-- /tab-pane -->
                    <?php 
                        $first_pane = false;
                    endforeach; 
                    ?>
                </div>
            </div>

        </div> <!-- /row -->
    </div> <!-- /container -->

    <!-- Image Lightbox Modal -->
    <div class="modal fade" id="imageLightboxModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content border-0 rounded-4 overflow-hidden shadow-lg">
                <div class="modal-header bg-dark text-white border-0 py-3">
                    <h6 class="modal-title fw-bold" id="imageModalLabel">Project Image Preview</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0 bg-black text-center">
                    <img src="" id="lightboxModalImage" alt="Full Preview" class="img-fluid" style="max-height: 80vh; object-fit: contain; width: 100%;">
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        if (typeof AOS !== 'undefined') {
            AOS.init({
                duration: 900,
                once: true
            });
        }

        function openImageModal(imgSrc, title) {
            document.getElementById('lightboxModalImage').src = imgSrc;
            document.getElementById('imageModalLabel').innerText = title || 'Project Image Preview';
            var modal = new bootstrap.Modal(document.getElementById('imageLightboxModal'));
            modal.show();
        }

        // Restart carousel autoplay when switching tabs
        document.querySelectorAll('.course-nav-btn').forEach(function(btn) {
            btn.addEventListener('shown.bs.tab', function(e) {
                var targetPaneId = btn.getAttribute('data-bs-target');
                var targetPane = document.querySelector(targetPaneId);
                if (targetPane) {
                    var carousel = targetPane.querySelector('.carousel');
                    if (carousel) {
                        var bsCarousel = bootstrap.Carousel.getOrCreateInstance(carousel, {
                            interval: 6000,
                            ride: 'carousel'
                        });
                        bsCarousel.cycle();
                    }
                }
            });
        });
    </script>
</body>
</html>
