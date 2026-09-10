<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once './Configurations/config.php';

// Fetch active job listings
$query = "SELECT * FROM Careers WHERE status = 'Active' ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
$jobs = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $jobs[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Careers - GD Edu Tech</title>
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Custom JavaScript -->
    <script src="./js/main.js" defer></script>
    <link rel="icon" type="image/png" href="./Images/Logos/GD_Only_logo.png">
    <link rel="icon" type="image/png" sizes="32x32" href="./Images/Logos/GD_Only_logo.png">
    <link rel="icon" type="image/png" sizes="16x16" href="./Images/Logos/GD_Only_logo.png">
    <link rel="shortcut icon" href="./Images/Logos/GD_Only_logo.png">
    <link rel="apple-touch-icon" href="./Images/Logos/GD_Only_logo.png">

    <style>
        body {
            background: #f8fafc;
            color: #0f172a;
            font-family: 'Poppins', sans-serif;
        }

        /* Top Section Text Enforcement - Black & Dark High-Contrast */
        .top-section-title {
            color: #000000 !important;
            font-weight: 800 !important;
        }

        .top-section-subtitle {
            color: #1e293b !important;
            font-weight: 600 !important;
        }

        /* Benefit Cards */
        .career-benefit-card {
            background: #ffffff;
            border-radius: 24px;
            border: 1px solid #e2e8f0;
            padding: 35px 25px;
            box-shadow: 0 10px 30px -10px rgba(15, 23, 42, 0.06);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100%;
        }

        .career-benefit-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px -10px rgba(13, 114, 152, 0.16);
            border-color: rgba(13, 114, 152, 0.3);
        }

        .benefit-icon-wrapper {
            width: 70px;
            height: 70px;
            border-radius: 20px;
            background: rgba(13, 114, 152, 0.1);
            color: #0d7298;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px auto;
            font-size: 1.8rem;
        }

        /* Life at GD Edu Tech 3-Column Zig-Zag Styling */
        .life-zigzag-section {
            position: relative;
            background: #ffffff;
            border-radius: 36px;
            box-shadow: 0 15px 45px rgba(15, 23, 42, 0.04);
            border: 1px solid #e2e8f0;
            padding: 50px 30px;
        }

        .life-zigzag-card {
            background: #ffffff;
            border-radius: 24px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 12px 35px -10px rgba(15, 23, 42, 0.08);
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .life-zigzag-card:hover {
            transform: translateY(-8px) !important;
            box-shadow: 0 25px 50px -10px rgba(13, 114, 152, 0.18);
            border-color: #0d7298;
        }

        .life-card-img-wrap {
            position: relative;
            height: 220px;
            overflow: hidden;
        }

        .life-card-img-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .life-zigzag-card:hover .life-card-img-wrap img {
            transform: scale(1.08);
        }

        .life-card-number-tag {
            position: absolute;
            top: 14px;
            left: 14px;
            background: rgba(13, 114, 152, 0.95);
            color: #ffffff;
            font-size: 0.75rem;
            font-weight: 800;
            padding: 4px 14px;
            border-radius: 50px;
            backdrop-filter: blur(4px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .life-card-body {
            padding: 28px 24px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        @media (min-width: 992px) {
            .zigzag-col-1 {
                margin-top: 0px;
            }
            .zigzag-col-2 {
                margin-top: 45px;
            }
            .zigzag-col-3 {
                margin-top: 0px;
            }
        }

        /* Wavy Dashed Track Styling */
        .cta-wavy-track {
            position: absolute;
            width: 220px;
            height: 130px;
            z-index: 1;
            opacity: 0.35;
            pointer-events: none;
        }

        .cta-wavy-track.track-left {
            top: 540px;
            left: 720px;
        }

        .cta-wavy-track.track-right {
            bottom: 360px;
            right: 590px;
        }

        /* Job Card Premium Styling */
        .career-job-card {
            background: #ffffff;
            border-radius: 24px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 30px -10px rgba(15, 23, 42, 0.08);
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }

        .career-job-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 25px 50px -10px rgba(13, 114, 152, 0.16);
            border-color: rgba(13, 114, 152, 0.3);
        }

        .job-title-text {
            color: #0f172a;
            font-weight: 700;
            font-size: 1.35rem;
        }

        .job-salary-pill {
            background: rgba(13, 114, 152, 0.08);
            border: 1px solid rgba(13, 114, 152, 0.2);
            padding: 8px 16px;
            border-radius: 12px;
            text-align: right;
        }

        .requirements-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .requirements-list li {
            margin-bottom: 0.6rem;
            display: flex;
            align-items: flex-start;
            font-size: 0.92rem;
            color: #334155;
        }

        .life-card {
            background: #ffffff;
            border-radius: 24px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            box-shadow: 0 10px 30px -10px rgba(15, 23, 42, 0.06);
            transition: all 0.4s ease;
        }

        .life-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px -10px rgba(13, 114, 152, 0.15);
        }

        .life-card-img {
            height: 220px;
            object-fit: cover;
            width: 100%;
            transition: transform 0.4s ease;
        }

        .life-card:hover .life-card-img {
            transform: scale(1.05);
        }

        .career-hero-image {
            max-width: 100%;
            height: auto;
            max-height: 360px;
            object-fit: contain;
            filter: drop-shadow(0 20px 35px rgba(0, 0, 0, 0.18));
            transition: transform 0.4s ease;
        }

        .career-hero-image:hover {
            transform: translateY(-8px) scale(1.02);
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <!-- Redesigned Executive Hero Banner -->
    <section class="about-page-header position-relative overflow-hidden w-100 my-0">
        <div class="about-header-glow-1"></div>
        <div class="about-header-glow-2"></div>
        <div class="about-header-pattern"></div>

        <div class="container position-relative z-2 py-4">
            <div class="row align-items-center text-start g-4">
                <div class="col-lg-7" data-aos="fade-right">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb about-breadcrumb px-3 py-1.5 rounded-pill mb-3 d-inline-flex">
                            <li class="breadcrumb-item"><a href="index.php" class="text-black text-decoration-none"><i class="bi bi-house-door-fill me-1"></i> Home</a></li>
                            <li class="breadcrumb-item active text-black" aria-current="page">Careers</li>
                        </ol>
                    </nav>

                    <h1 class="display-4 fw-bold text-black mb-3">
                        Join Our <span class="cta-gold-text">Career Team</span>
                    </h1>

                    <p class="lead text-black-50 mb-4" style="max-width: 650px;">
                        Be part of our mission to transform education through technology, innovation, and career development.
                    </p>
                </div>

                <div class="col-lg-5 text-center text-lg-end" data-aos="fade-left" data-aos-delay="200">
                    <div class="position-relative d-inline-block">
                        <div class="position-absolute top-50 start-50 translate-middle rounded-circle  bg-opacity-20 blur-2xl" style="width: 220px; height: 220px;  z-index: 1;"></div>
                        <img src="./Images/Others/career3.png" alt="Careers" class="img-fluid position-relative z-2 career-hero-image">
                    </div>
                </div>
            </div>
        </div>

        <div class="page-header-shape position-absolute bottom-0 start-0 w-100">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 120" preserveAspectRatio="none" style="height: 40px; display: block; width: 100%;">
                <path fill="#f8fafc" fill-opacity="1" d="M0,32L48,42.7C96,53,192,75,288,80C384,85,480,75,576,58.7C672,43,768,21,864,21.3C960,21,1056,43,1152,53.3C1248,64,1344,64,1392,64L1440,64L1440,120L1392,120C1344,120,1056,120,960,120C864,120,768,120,672,120C576,120,480,120,384,120C288,120,192,120,96,120C48,120L0,120Z"></path>
            </svg>
        </div>
    </section>

    <!-- Why Join Us Section -->
    <section class="py-5">
        <div class="container py-2">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="display-6 top-section-title mb-2" data-aos="fade-up">Why Join GD Edu Tech?</h2>
                    <p class="lead top-section-subtitle mb-0" data-aos="fade-up" data-aos-delay="100">Discover the benefits of being part of our innovative team</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6" data-aos="fade-up">
                    <div class="career-benefit-card text-center">
                        <div class="benefit-icon-wrapper">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">Growth Opportunities</h5>
                        <p class="text-muted small mb-0">Continuous learning and career advancement in a fast-growing tech company.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="career-benefit-card text-center">
                        <div class="benefit-icon-wrapper">
                            <i class="bi bi-heart-pulse"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">Work-Life Balance</h5>
                        <p class="text-muted small mb-0">Flexible work arrangements and comprehensive wellness programs.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="career-benefit-card text-center">
                        <div class="benefit-icon-wrapper">
                            <i class="bi bi-people"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">Great Culture</h5>
                        <p class="text-muted small mb-0">Collaborative environment with diverse, talented, and passion-driven professionals.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="career-benefit-card text-center">
                        <div class="benefit-icon-wrapper">
                            <i class="bi bi-trophy"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">Competitive Benefits</h5>
                        <p class="text-muted small mb-0">Attractive compensation, health benefits, and continuous skill allowances.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Life at GD Edu Tech (3 Alternating Rows / Zig-Zag Layout) -->
    <section class="py-5 position-relative overflow-hidden">
        <!-- Wavy Dashed Track Decor Left & Right -->
        <svg class="cta-wavy-track track-right d-none d-lg-block" viewBox="0 0 220 130" fill="none" stroke="#7e858d" stroke-width="3" stroke-dasharray="6 6">
            <path d="M10,110 C70,20 140,120 210,20" />
        </svg>
        <svg class="cta-wavy-track track-left d-none d-lg-block" viewBox="0 0 220 130" fill="none" stroke="#7e858d" stroke-width="3" stroke-dasharray="6 6">
            <path d="M10,20 C80,120 150,20 210,110" />
        </svg>

        <div class="container py-3 position-relative z-2">
            
            <!-- Section Header -->
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <span class="badge bg-warning bg-opacity-20 text-dark border border-warning-subtle px-3 py-1.5 rounded-pill mb-3 fw-semibold">
                        <i class="bi bi-heart-fill me-1 text-danger"></i> OUR WORKPLACE CULTURE
                    </span>
                    <h2 class="display-6 top-section-title mb-2" data-aos="fade-up">Life at <span class="cta-gold-text">GD Edu Tech</span></h2>
                    <p class="lead top-section-subtitle mb-0" data-aos="fade-up" data-aos-delay="100">See what makes our environment special, collaborative, and rewarding</p>
                </div>
            </div>

            <div class="d-flex flex-column gap-5">
                
                <!-- Row 1: Image Left | Details Right -->
                <div class="row align-items-center g-4 g-lg-5" data-aos="fade-up">
                    <div class="col-lg-6">
                        <div class="position-relative overflow-hidden shadow-lg" style="border-radius: 24px;">
                            <img src="./Images/Others/workspace.jpeg" onerror="this.onerror=null; this.src='./Images/Others/career.png';" alt="Modern Workspace" class="img-fluid w-100 object-fit-cover" style="height: 360px; border-radius: 24px;">
                            <span style="background-color: #0078a8;" class="badge  text-white position-absolute top-0 start-0 m-3 px-3 py-2 rounded-pill fw-bold shadow-sm">
                                01 / WORKSPACE
                            </span>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="ps-lg-3">
                            <span class="text-primary fw-bold text-uppercase tracking-wider small d-block mb-1">State-of-the-Art Infrastructure</span>
                            <h3 class="fw-bold text-dark mb-3">Modern & Inspiring Workspace</h3>
                            <p class="text-secondary lead fs-6 mb-4" style="line-height: 1.7;">
                                Our office is designed to foster innovation, focus, and collaboration. Equipped with high-speed fiber internet, ergonomic workstations, quiet focus pods, and interactive conference rooms.
                            </p>
                            <div class="row g-3">
                                <div class="col-sm-6">
                               
                                </div>
                                <div class="col-sm-6">
                                  
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 2 (Zig-Zag Flipped): Details Left | Image Right -->
                <div class="row align-items-center g-4 g-lg-5 flex-column-reverse flex-lg-row" data-aos="fade-up" data-aos-delay="100">
                    <div class="col-lg-6">
                        <div class="pe-lg-3">
                            <span class="text-warning fw-bold text-uppercase tracking-wider small d-block mb-1">Vibrant Community</span>
                            <h3 class="fw-bold text-dark mb-3">Team Building & Celebrations</h3>
                            <p class="text-secondary lead fs-6 mb-4" style="line-height: 1.7;">
                              We believe work should be exciting, meaningful, and rewarding. From team outings and festive celebrations to engaging team activities, we create an environment where everyone can connect, grow, and celebrate success together.
                            </p>
                            <div class="row g-3">
                                <div class="col-sm-6">
                               
                                </div>
                                <div class="col-sm-6">
                          
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="position-relative overflow-hidden shadow-lg" style="border-radius: 24px;">
                            <img src="./Images/Others/events.jpeg" onerror="this.onerror=null; this.src='./Images/Others/career2.png';" alt="Team Events" class="img-fluid w-100 object-fit-cover" style="height: 360px; border-radius: 24px;">
                            <span class="badge bg-warning text-dark position-absolute top-0 start-0 m-3 px-3 py-2 rounded-pill fw-bold shadow-sm">
                                02 / COMMUNITY
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Row 3: Image Left | Details Right -->
                <div class="row align-items-center g-4 g-lg-5" data-aos="fade-up" data-aos-delay="200">
                    <div class="col-lg-6">
                        <div class="position-relative overflow-hidden shadow-lg" style="border-radius: 24px;">
                            <img src="./Images/Others/culture.jpeg" onerror="this.onerror=null; this.src='./Images/Others/career3.png';" alt="Learning Culture" class="img-fluid w-100 object-fit-cover" style="height: 360px; border-radius: 24px;">
                            <span style="background-color: #d35850;" class="badge  text-white position-absolute top-0 start-0 m-3 px-3 py-2 rounded-pill fw-bold shadow-sm">
                                03 / LEARNING
                            </span>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="ps-lg-3">
                            <span style="color:#d35850;" class=" fw-bold text-uppercase tracking-wider small d-block mb-1">Career & Professional Development</span>
                            <h3 class="fw-bold text-dark mb-3">Continuous Learning & Growth</h3>
                            <p class="text-secondary lead fs-6 mb-4" style="line-height: 1.7;">
                                Your growth is our priority. We offer 100% sponsored certification courses, 1-on-1 executive mentorship, skill allowances, and clear internal promotion pathways.
                            </p>
                            <div class="row g-3">
                                <div class="col-sm-6">
                                  
                                </div>
                                <div class="col-sm-6">
                                 
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
    <!-- Current Openings Section -->
    <section class="py-5 bg-white">
        <div class="container py-2">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                    <span class="fw-semibold"><?php echo htmlspecialchars($_SESSION['success']); ?></span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                    <span class="fw-semibold"><?php echo htmlspecialchars($_SESSION['error']); ?></span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="row mb-4 align-items-end">
                <div class="col-lg-6">
                    <h2 class="display-6 top-section-title mb-1" data-aos="fade-up">Current Openings</h2>
                    <p class="lead top-section-subtitle mb-0" data-aos="fade-up" data-aos-delay="100">Join our team and be part of something extraordinary</p>
                </div>
                <div class="col-lg-6 text-lg-end mt-3 mt-lg-0" data-aos="fade-up" data-aos-delay="200">
                    <div class="btn-group" role="group" aria-label="Job filter">
                        <button type="button" class="btn btn-dark rounded-pill px-4 py-2 me-2 active" data-filter="all">All Jobs</button>
                        <button type="button" class="btn btn-outline-dark rounded-pill px-4 py-2 me-2" data-filter="Full-time">Full-time</button>
                        <button type="button" class="btn btn-outline-dark rounded-pill px-4 py-2" data-filter="Remote">Remote</button>
                    </div>
                </div>
            </div>
            <div class="row g-4 pt-3">
                <?php if (!empty($jobs)): ?>
                    <?php foreach ($jobs as $job): ?>
                        <div class="col-lg-6" data-aos="fade-up" data-job-type="<?php echo htmlspecialchars($job['job_type']); ?>">
                            <div class="career-job-card h-100 p-4 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-3 py-1.5 rounded-pill mb-2 fw-semibold">
                                            <i class="bi bi-briefcase-fill me-1"></i>
                                            <?php echo htmlspecialchars($job['job_type']); ?>
                                        </span>
                                        <h4 class="job-title-text mb-2"><?php echo htmlspecialchars($job['job_title']); ?></h4>
                                        <div class="d-flex align-items-center text-secondary small">
                                            <i class="bi bi-geo-alt-fill text-danger me-1.5"></i>
                                            <span class="fw-semibold"><?php echo htmlspecialchars($job['location']); ?></span>
                                        </div>
                                    </div>
                                    <div class="job-salary-pill ms-2">
                                        <span class="text-primary fw-bold fs-6"><?php echo htmlspecialchars($job['salary_range']); ?></span>
                                        <span class="text-muted small d-block">per month</span>
                                    </div>
                                </div>
                                
                                <!-- Job Description -->
                                <p class="card-text text-secondary mb-4 flex-grow-1" style="line-height: 1.6; font-size: 0.95rem;">
                                    <?php 
                                        $desc = htmlspecialchars($job['job_description']);
                                        echo (strlen($desc) > 220) ? substr($desc, 0, 220) . '...' : $desc;
                                    ?>
                                </p>

                                <!-- Bottom Bar with More Button & Apply Now Button -->
                                <div class="d-flex flex-wrap justify-content-between align-items-center pt-3 border-top gap-2 mt-auto">
                                    <div class="job-meta small text-muted">
                                        <span class="d-inline-block"><i class="bi bi-clock me-1 text-primary"></i> Posted <?php echo date('M d, Y', strtotime($job['created_at'])); ?></span>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-outline-primary rounded-pill px-3 py-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#jobModal<?php echo $job['job_id']; ?>">
                                            More <i class="bi bi-arrow-right-circle ms-1"></i>
                                        </button>
                                        <a href="apply.php?job_id=<?php echo $job['job_id']; ?>" class="btn btn-dark rounded-pill px-4 py-2 fw-bold text-decoration-none">
                                            Apply Now <i class="bi bi-send-fill ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center" data-aos="fade-up">
                        <div class="alert alert-info py-4 rounded-4 border-0 shadow-sm" role="alert">
                            <i class="bi bi-info-circle fs-3 me-2"></i>
                            <span class="fs-5 fw-semibold">No job openings available at the moment. Please check back later.</span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Job Details Modals (Placed outside transformed parent container) -->
    <?php if (!empty($jobs)): ?>
        <?php foreach ($jobs as $job): ?>
            <div class="modal fade" id="jobModal<?php echo $job['job_id']; ?>" tabindex="-1" aria-labelledby="jobModalLabel<?php echo $job['job_id']; ?>" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                        <div class="modal-header text-white p-4" style="background: linear-gradient(135deg, #0d7298 0%, #065d7d 100%);">
                            <div>
                                <span class="badge bg-white text-primary px-3 py-1.5 rounded-pill mb-2 fw-semibold">
                                    <i class="bi bi-briefcase-fill me-1"></i>
                                    <?php echo htmlspecialchars($job['job_type']); ?>
                                </span>
                                <h4 class="modal-title fw-bold text-white mb-1" id="jobModalLabel<?php echo $job['job_id']; ?>"><?php echo htmlspecialchars($job['job_title']); ?></h4>
                                <?php if (!empty($job['company_name'])): ?>
                                    <p class="text-white-50 mb-2 small"><i class="bi bi-building me-1"></i> <?php echo htmlspecialchars($job['company_name']); ?></p>
                                <?php endif; ?>
                                <div class="d-flex flex-wrap align-items-center text-white-50 small gap-3">
                                    <span><i class="bi bi-geo-alt-fill text-warning me-1"></i><strong class="text-white">Location:</strong> <?php echo htmlspecialchars($job['location']); ?></span>
                                    <span><i class="bi bi-cash-stack text-success me-1"></i><strong class="text-white">Salary:</strong> <?php echo htmlspecialchars($job['salary_range']); ?> per month</span>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto;">
                            <!-- Full Job Description -->
                            <div class="mb-4">
                                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-file-text-fill text-primary me-2"></i>Job Description & Overview</h6>
                                <div class="p-3 bg-light rounded-3 text-secondary" style="line-height: 1.7; font-size: 0.95rem;">
                                    <?php echo nl2br(htmlspecialchars($job['job_description'])); ?>
                                </div>
                            </div>

                            <!-- Key Requirements List -->
                            <?php if (!empty(trim($job['requirements']))): ?>
                                <div class="mb-4">
                                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-list-check text-primary me-2"></i>Key Requirements & Qualifications</h6>
                                    <ul class="ps-0 mb-0" style="list-style: none;">
                                        <?php
                                        $requirements = explode("\n", $job['requirements']);
                                        foreach ($requirements as $requirement) {
                                            if (!empty(trim($requirement))) {
                                                echo '<li class="mb-2 d-flex align-items-start small text-secondary"><i class="bi bi-check-circle-fill text-success me-2 fs-6 mt-0.5"></i><span>' . htmlspecialchars(trim($requirement)) . '</span></li>';
                                            }
                                        }
                                        ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <!-- Benefits & Perks List -->
                            <?php if (!empty(trim($job['benefits']))): ?>
                                <div class="mb-4">
                                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-gift-fill text-warning me-2"></i>Benefits & Perks</h6>
                                    <ul class="ps-0 mb-0" style="list-style: none;">
                                        <?php
                                        $benefits = explode("\n", $job['benefits']);
                                        foreach ($benefits as $benefit) {
                                            if (!empty(trim($benefit))) {
                                                echo '<li class="mb-2 d-flex align-items-start small text-secondary"><i class="bi bi-star-fill text-warning me-2 fs-6 mt-0.5"></i><span>' . htmlspecialchars(trim($benefit)) . '</span></li>';
                                            }
                                        }
                                        ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <!-- Meta Info -->
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <div class="p-3 border rounded-3 bg-white">
                                        <div class="text-muted small"><i class="bi bi-clock text-primary me-1"></i> Posted Date</div>
                                        <div class="fw-bold text-dark"><?php echo date('F d, Y', strtotime($job['created_at'])); ?></div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="p-3 border rounded-3 bg-white">
                                        <div class="text-muted small"><i class="bi bi-calendar-event text-danger me-1"></i> Application Deadline</div>
                                        <div class="fw-bold text-dark"><?php echo date('F d, Y', strtotime($job['application_deadline'])); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light p-3 border-top-0 d-flex justify-content-between align-items-center">
                            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                            <a href="apply.php?job_id=<?php echo $job['job_id']; ?>" class="btn btn-primary rounded-pill px-4 py-2 fw-bold text-decoration-none shadow-sm">
                                Apply Now <i class="bi bi-send-fill ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>




    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            easing: 'ease-in-out',
            once: true
        });

        // Job filtering functionality
        document.addEventListener('DOMContentLoaded', function() {
            const filterButtons = document.querySelectorAll('[data-filter]');
            const jobCards = document.querySelectorAll('[data-job-type]');

            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    filterButtons.forEach(btn => {
                        btn.classList.remove('active', 'btn-dark', 'text-white');
                        btn.classList.add('btn-outline-dark');
                    });
                    this.classList.add('active', 'btn-dark', 'text-white');
                    this.classList.remove('btn-outline-dark');

                    const filter = this.getAttribute('data-filter');

                    jobCards.forEach(card => {
                        if (filter === 'all' || card.getAttribute('data-job-type') === filter) {
                            card.style.display = 'block';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            });
        });
    </script>
     

<!-- =========================================================
     GD EDU TECH CHATBOT
     Self-contained widget only. Existing page design untouched.
     ========================================================= -->
<style>
    #gd-chatbot-root,
    #gd-chatbot-root * {
        box-sizing: border-box;
    }

    #gd-chatbot-root {
        position: fixed;
        right: 2px;
        bottom: 145px;
        z-index: 99999;
        font-family: 'Poppins', sans-serif;
    }

    #gd-chatbot-toggle {
        width: 136px;
        height: 58px;
        border: 0;
        border-radius: 50%;
        background: transparent;
        color: #0079a8;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 25px;
        cursor: pointer;
        box-shadow: none;
        transition: none;
        position: relative;
    }
        10% {
            transform: scale(1.18);
            opacity: 0.22;
        }
        20% {
            transform: scale(1.04);
            opacity: 0.38;
        }
        30% {
            transform: scale(1.25);
            opacity: 0;
        }
        45%, 90% {
            transform: scale(1);
            opacity: 0;
        }
    }
#gd-chatbot-toggle:focus-visible,
    #gd-chatbot-close:focus-visible,
    #gd-chatbot-send:focus-visible,
    #gd-chatbot-input:focus-visible,
    .gd-chatbot-quick-btn:focus-visible {
        outline: 3px solid rgba(0, 121, 168, 0.28);
        outline-offset: 2px;
    }

    #gd-chatbot-panel {
        position: absolute;
        right: 12px;
        bottom: 42px;
        width: 377px;
        height: 500px;
        max-height: calc(100vh - 120px);
        background: #fff;
        border: 1px solid #e7e7e7;
        border-radius: 18px;
        box-shadow: 0 18px 55px rgba(0, 0, 0, 0.20);
        overflow: hidden;
        display: none;
        flex-direction: column;
    }

    #gd-chatbot-panel.gd-chatbot-open {
        display: flex;
        animation: gdChatbotOpen 0.2s ease-out;
    }

    @keyframes gdChatbotOpen {
        from { opacity: 0; transform: translateY(10px) scale(0.98); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    .gd-chatbot-header {
        background: #0079a8;
        color: #fff;
        padding: 14px 15px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex: 0 0 auto;
    }

    .gd-chatbot-header-left {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }

    .gd-chatbot-avatar {
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.18);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .gd-chatbot-title {
        margin: 0;
        font-size: 15px;
        font-weight: 700;
        line-height: 1.2;
    }

    .gd-chatbot-status {
        display: block;
        margin-top: 2px;
        font-size: 11px;
        opacity: 0.9;
    }

    #gd-chatbot-close {
        width: 34px;
        height: 34px;
        border: 0;
        border-radius: 50%;
        background: transparent;
        color: #fff;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 21px;
    }

    #gd-chatbot-close:hover {
        background: rgba(255, 255, 255, 0.12);
    }

    #gd-chatbot-messages {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
        padding: 15px;
        background: #f7f9fb;
        scroll-behavior: smooth;
    }

    .gd-chatbot-row {
        display: flex;
        margin-bottom: 10px;
    }

    .gd-chatbot-row.gd-chatbot-user-row {
        justify-content: flex-end;
    }

    .gd-chatbot-message {
        max-width: 82%;
        padding: 10px 12px;
        border-radius: 14px;
        font-size: 13px;
        line-height: 1.55;
        word-break: break-word;
    }

    .gd-chatbot-bot-message {
        background: #fff;
        color: #333;
        border: 1px solid #e9edf1;
        border-bottom-left-radius: 5px;
    }

    .gd-chatbot-user-message {
        background: #0079a8;
        color: #fff;
        border-bottom-right-radius: 5px;
    }

    .gd-chatbot-message a {
        color: #0079a8;
        font-weight: 600;
        text-decoration: none;
    }

    .gd-chatbot-quick-actions {
        padding: 8px 12px 2px;
        display: flex;
        gap: 6px;
        overflow-x: auto;
        background: #fff;
        scrollbar-width: none;
        flex: 0 0 auto;
    }

    .gd-chatbot-quick-actions::-webkit-scrollbar {
        display: none;
    }

    .gd-chatbot-quick-btn {
        flex: 0 0 auto;
        border: 1px solid #d9e7ed;
        background: #fff;
        color: #0079a8;
        border-radius: 999px;
        padding: 6px 10px;
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
    }

    .gd-chatbot-quick-btn:hover {
        background: #eef8fb;
    }

    .gd-chatbot-input-wrap {
        padding: 10px;
        background: #fff;
        border-top: 1px solid #edf0f2;
        display: flex;
        align-items: center;
        gap: 8px;
        flex: 0 0 auto;
    }

    #gd-chatbot-input {
        flex: 1;
        min-width: 0;
        height: 42px;
        border: 1px solid #dfe4e8;
        border-radius: 999px;
        padding: 0 14px;
        font-family: inherit;
        font-size: 13px;
        color: #222;
        background: #fff;
        outline: none;
    }

    #gd-chatbot-input:focus {
        border-color: #0079a8;
    }

    #gd-chatbot-send {
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        border: 0;
        border-radius: 50%;
        background: #0079a8;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 17px;
    }

    #gd-chatbot-send:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .gd-chatbot-typing {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        min-width: 46px;
    }

    .gd-chatbot-typing span {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #9aa6ad;
        animation: gdChatbotTyping 1s infinite ease-in-out;
    }

    .gd-chatbot-typing span:nth-child(2) { animation-delay: 0.15s; }
    .gd-chatbot-typing span:nth-child(3) { animation-delay: 0.3s; }

    @keyframes gdChatbotTyping {
        0%, 60%, 100% { transform: translateY(0); opacity: 0.55; }
        30% { transform: translateY(-3px); opacity: 1; }
    }

    @media (max-width: 576px) {
        #gd-chatbot-root {
            right: 2px;
            bottom: 73px;
        }

        #gd-chatbot-panel {
            position: fixed;
            right: 12px;
            left: 12px;
            bottom: 82px;
            width: auto;
            height: min(500px, calc(100vh - 110px));
            max-height: calc(100vh - 110px);
        }

        #gd-chatbot-toggle {
            width: 94px;
            height: 54px;
            font-size: 23px;
        }
    }

   .gd-chatbot-icon {
    width: 121px;
    height: 598px;
    object-fit: contain;
    display: block;
}
</style>

<div id="gd-chatbot-root">
    <div id="gd-chatbot-panel" role="dialog" aria-label="GD Edu Tech chatbot" aria-hidden="true">
        <div class="gd-chatbot-header">
            <div class="gd-chatbot-header-left">
                <div class="gd-chatbot-avatar" aria-hidden="true">
                    <i class="bi bi-robot"></i>
                </div>
                <div>
                    <p class="gd-chatbot-title">GD Edu Tech Assistant</p>
                    <span class="gd-chatbot-status">Online • Ask about our courses</span>
                </div>
            </div>
            <button id="gd-chatbot-close" type="button" aria-label="Close chatbot">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div id="gd-chatbot-messages" aria-live="polite"></div>

        <div class="gd-chatbot-quick-actions" aria-label="Quick questions">
            <button type="button" class="gd-chatbot-quick-btn" data-question="What courses do you offer?">Courses</button>
            <button type="button" class="gd-chatbot-quick-btn" data-question="Tell me about offline courses">Offline Courses</button>
            <button type="button" class="gd-chatbot-quick-btn" data-question="Do you provide placement assistance?">Placement</button>
            <button type="button" class="gd-chatbot-quick-btn" data-question="How can I apply for a scholarship?">Scholarship</button>
        </div>

        <form id="gd-chatbot-form" class="gd-chatbot-input-wrap" autocomplete="off">
            <input
                id="gd-chatbot-input"
                type="text"
                placeholder="Type your message..."
                aria-label="Chat message"
                maxlength="300"
            >
            <button id="gd-chatbot-send" type="submit" aria-label="Send message">
                <i class="bi bi-send-fill"></i>
            </button>
        </form>
    </div>
<button id="gd-chatbot-toggle" type="button" aria-label="Open chatbot" aria-expanded="false">
    <img src="./assets/images/t5d42NEZJZ.svg" alt="Chatbot" class="gd-chatbot-icon">
</button>
</div>

<script>
(function () {
    const root = document.getElementById('gd-chatbot-root');
    if (!root) return;

    const panel = document.getElementById('gd-chatbot-panel');
    const toggle = document.getElementById('gd-chatbot-toggle');
    const closeBtn = document.getElementById('gd-chatbot-close');
    const form = document.getElementById('gd-chatbot-form');
    const input = document.getElementById('gd-chatbot-input');
    const messages = document.getElementById('gd-chatbot-messages');
    const quickButtons = document.querySelectorAll('.gd-chatbot-quick-btn');

    let greeted = false;
    let busy = false;

    function scrollToBottom() {
        messages.scrollTop = messages.scrollHeight;
    }

    function addMessage(text, sender, allowHtml = false) {
        const row = document.createElement('div');
        row.className = 'gd-chatbot-row' + (sender === 'user' ? ' gd-chatbot-user-row' : '');

        const bubble = document.createElement('div');
        bubble.className = 'gd-chatbot-message ' + (sender === 'user' ? 'gd-chatbot-user-message' : 'gd-chatbot-bot-message');

        if (allowHtml) {
            bubble.innerHTML = text;
        } else {
            bubble.textContent = text;
        }

        row.appendChild(bubble);
        messages.appendChild(row);
        scrollToBottom();
        return row;
    }

    function showTyping() {
        const row = document.createElement('div');
        row.className = 'gd-chatbot-row';
        row.id = 'gd-chatbot-typing-row';

        const bubble = document.createElement('div');
        bubble.className = 'gd-chatbot-message gd-chatbot-bot-message gd-chatbot-typing';
        bubble.innerHTML = '<span></span><span></span><span></span>';

        row.appendChild(bubble);
        messages.appendChild(row);
        scrollToBottom();
    }

    function hideTyping() {
        const typingRow = document.getElementById('gd-chatbot-typing-row');
        if (typingRow) typingRow.remove();
    }

    function botReply(message) {
        const q = message.toLowerCase().trim();

        if (/\b(hi|hello|hey|good morning|good afternoon|good evening)\b/.test(q)) {
            return 'Hello! 👋 Welcome to GD Edu Tech. How can I help you today?';
        }

        if (q.includes('course') || q.includes('program')) {
            if (q.includes('offline') || q.includes('classroom')) {
                return 'Our classroom training includes Full Stack Development, Architectural Design, Interior Design, Digital Marketing, Graphic Design & Video Editing, and Photography & Camera Handling. <a href="courses.php">View courses</a>.';
            }
            return 'GD Edu Tech offers career-focused programs including Full Stack Development, Architectural Design, Interior Design, Digital Marketing, Graphic Design & Video Editing, and Photography. <a href="courses.php">Explore all courses</a>.';
        }

        if (q.includes('full stack') || q.includes('web development') || q.includes('developer')) {
            return 'Yes, Full Stack Development is one of our featured classroom training programs. You can check the available course details on our <a href="courses.php">Courses page</a>.';
        }

        if (q.includes('interior')) {
            return 'Yes, GD Edu Tech offers an Interior Design course with practical, career-focused training. <a href="courses.php">View course details</a>.';
        }

        if (q.includes('architecture') || q.includes('architectural')) {
            return 'Yes, we offer an Architectural Design course. <a href="courses.php">View the course options</a> for more details.';
        }

        if (q.includes('digital marketing') || q.includes('marketing')) {
            return 'Yes, Digital Marketing is available as one of our training programs. <a href="courses.php">See course details</a>.';
        }

        if (q.includes('graphic') || q.includes('video editing') || q.includes('photography')) {
            return 'We offer Graphic Design & Video Editing and Photography & Camera Handling programs. <a href="courses.php">Explore the courses</a>.';
        }

        if (q.includes('scholarship')) {
            return 'You can apply through our scholarship page. <a href="scholarship.php">Apply for Scholarship</a>.';
        }

        if (q.includes('placement') || q.includes('job') || q.includes('career')) {
            return 'Yes. GD Edu Tech provides placement assistance and career guidance, including support such as resume building and interview preparation.';
        }

        if (q.includes('internship')) {
            return 'Yes. GD Edu Tech provides internship programs designed to give students practical, real-world experience.';
        }

        if (q.includes('certificate') || q.includes('certification')) {
            return 'Courses are designed to provide industry-recognized certification after completion. You can contact the team for certification details for a specific program.';
        }

        if (q.includes('online')) {
            return 'GD Edu Tech supports flexible online learning with access to course materials. For current online course availability, please check the <a href="courses.php">Courses page</a>.';
        }

        if (q.includes('fee') || q.includes('price') || q.includes('cost') || q.includes('duration') || q.includes('timing')) {
            return 'Fees, duration, and batch timings can vary by course. Please <a href="contact.php">contact GD Edu Tech</a> for the latest details.';
        }

        if (q.includes('contact') || q.includes('phone') || q.includes('address') || q.includes('location')) {
            return 'You can reach the GD Edu Tech team through the <a href="contact.php">Contact page</a>.';
        }

        if (q.includes('enroll') || q.includes('join') || q.includes('admission') || q.includes('register')) {
            return 'You can start by exploring the available programs on the <a href="courses.php">Courses page</a>, then use the enrollment/contact option for the course you want.';
        }

        if (q.includes('thank')) {
            return 'You’re welcome! 😊 If you have another question about GD Edu Tech, just ask.';
        }

        return 'I can help with courses, scholarships, internships, certifications, placement assistance, fees, and enrollment. For anything else, please <a href="contact.php">contact our team</a>.';
    }

    function submitMessage(message) {
        const cleanMessage = String(message || '').trim();
        if (!cleanMessage || busy) return;

        busy = true;
        addMessage(cleanMessage, 'user');
        input.value = '';
        showTyping();

        window.setTimeout(function () {
            hideTyping();
            addMessage(botReply(cleanMessage), 'bot', true);
            busy = false;
            input.focus();
        }, 450);
    }

    function openChat() {
        panel.classList.add('gd-chatbot-open');
        panel.setAttribute('aria-hidden', 'false');
        toggle.setAttribute('aria-expanded', 'true');
        toggle.innerHTML = '<i class=""></i>';
        toggle.setAttribute('aria-label', 'Close chatbot');

        if (!greeted) {
            greeted = true;
            addMessage('Hi! 👋 I’m the GD Edu Tech Assistant. Ask me about courses, scholarships, internships, placement assistance, fees, or enrollment.', 'bot');
        }

        window.setTimeout(function () {
            input.focus();
            scrollToBottom();
        }, 50);
    }

    function closeChat() {
        panel.classList.remove('gd-chatbot-open');
        panel.setAttribute('aria-hidden', 'true');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.innerHTML = '<img src="./assets/images/t5d42NEZJZ.svg" alt="Chatbot" class="gd-chatbot-icon">';
        toggle.setAttribute('aria-label', 'Open chatbot');
    }

    toggle.addEventListener('click', function () {
        if (panel.classList.contains('gd-chatbot-open')) {
            closeChat();
        } else {
            openChat();
        }
    });

    closeBtn.addEventListener('click', closeChat);

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        submitMessage(input.value);
    });

    quickButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            submitMessage(button.getAttribute('data-question'));
        });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && panel.classList.contains('gd-chatbot-open')) {
            closeChat();
            toggle.focus();
        }
    });
})();
</script>
</body>
</html>