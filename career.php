<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once './Configurations/config.php';

// Fetch active job listings
$query = "SELECT * FROM Careers WHERE status = 'Active' ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
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

    <!-- Current Openings Section -->
    <section class="py-5 bg-white">
        <div class="container py-2">
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
                <?php if ($result && mysqli_num_rows($result) > 0): ?>
                    <?php while ($job = mysqli_fetch_assoc($result)): ?>
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
                                        <span class="text-muted small d-block">per annum</span>
                                    </div>
                                </div>
                                
                                <p class="card-text text-secondary mb-4 flex-grow-0" style="line-height: 1.6; font-size: 0.95rem;">
                                    <?php echo htmlspecialchars($job['job_description']); ?>
                                </p>

                                <div class="job-requirements mb-4 flex-grow-1">
                                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-list-check me-1.5 text-primary"></i> Key Requirements:</h6>
                                    <ul class="requirements-list">
                                        <?php
                                        $requirements = explode("\n", $job['requirements']);
                                        foreach ($requirements as $requirement) {
                                            if (!empty(trim($requirement))) {
                                                echo '<li><i class="bi bi-check-circle-fill text-success me-2 fs-6"></i>' . htmlspecialchars(trim($requirement)) . '</li>';
                                            }
                                        }
                                        ?>
                                    </ul>
                                </div>

                                <div class="d-flex flex-wrap justify-content-between align-items-center pt-3 border-top gap-2 mt-auto">
                                    <div class="job-meta small text-muted">
                                        <span class="me-3 d-inline-block"><i class="bi bi-clock me-1 text-primary"></i> Posted <?php echo date('M d, Y', strtotime($job['created_at'])); ?></span>
                                        <span class="d-inline-block"><i class="bi bi-calendar-event me-1 text-danger"></i> Deadline: <?php echo date('M d, Y', strtotime($job['application_deadline'])); ?></span>
                                    </div>
                                    <a href="apply.php?id=<?php echo $job['job_id']; ?>" class="btn btn-dark rounded-pill px-4 py-2 fw-bold text-decoration-none">
                                        Apply Now <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
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

    <!-- Life at GD Edu Tech -->
    <section class="py-5">
        <div class="container py-2">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="display-6 top-section-title mb-2" data-aos="fade-up">Life at GD Edu Tech</h2>
                    <p class="lead top-section-subtitle mb-0" data-aos="fade-up" data-aos-delay="100">See what makes our workplace special</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6" data-aos="fade-up">
                    <div class="life-card">
                        <div class="overflow-hidden">
                            <img src="./Images/Others/office-1.jpg" alt="Office Life" class="life-card-img">
                        </div>
                        <div class="p-4">
                            <h5 class="fw-bold text-dark mb-2">Modern Workspace</h5>
                            <p class="text-muted small mb-0">State-of-the-art facilities designed for productivity and collaboration.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="life-card">
                        <div class="overflow-hidden">
                            <img src="./Images/Others/office-2.jpg" alt="Team Events" class="life-card-img">
                        </div>
                        <div class="p-4">
                            <h5 class="fw-bold text-dark mb-2">Team Events</h5>
                            <p class="text-muted small mb-0">Regular team building activities, workshops, and celebrations.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="life-card">
                        <div class="overflow-hidden">
                            <img src="./Images/Others/office-3.jpg" alt="Learning Culture" class="life-card-img">
                        </div>
                        <div class="p-4">
                            <h5 class="fw-bold text-dark mb-2">Learning Culture</h5>
                            <p class="text-muted small mb-0">Continuous learning and professional development opportunities.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


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
</body>
</html>