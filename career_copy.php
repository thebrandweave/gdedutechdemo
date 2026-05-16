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
    <!-- Custom JavaScript -->
    <script src="./js/main.js" defer></script>
  
    <link rel="icon" type="image/png" href="./Images/Logos/GD_Only_logo.png">
    <link rel="shortcut icon" href="./Images/Logos/GD_Only_logo.png">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <!-- Page Header -->
    <section class="page-header position-relative overflow-hidden">
        <div class="container position-relative py-7">
            <div class="row align-items-center">
                <div class="col-md-7" data-aos="fade-right">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-3">
                            <li class="breadcrumb-item"><a href="index.php" class="text-white-50">Home</a></li>
                            <li class="breadcrumb-item active text-white" aria-current="page">Careers</li>
                        </ol>
                    </nav>
                    <h1 class="text-white display-5 fw-bold mb-3">Join Our Team</h1>
                    <p class="text-white-50 lead mb-0">Be part of our mission to transform education through technology and innovation.</p>
                </div>
                <div class="col-md-5" data-aos="fade-left">
                    <img src="./Images/Others/career.png" alt="Careers" class="career-hero-image">
                </div>
            </div>
        </div>
        <div class="page-header-shape">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
                <path fill="#ffffff" fill-opacity="1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,112C672,96,768,96,864,112C960,128,1056,160,1152,160C1248,160,1344,128,1392,112L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
            </svg>
        </div>
    </section>

    <!-- Why Join Us Section -->
    <section class="py-5">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="section-heading" data-aos="fade-up">Why Join GD Edu Tech?</h2>
                    <p class="lead text-muted" data-aos="fade-up" data-aos-delay="100">Discover the benefits of being part of our innovative team</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6" data-aos="fade-up">
                    <div class="premium-card text-center p-4 h-100">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3 mb-4 mx-auto" style="width: 80px; height: 80px">
                            <i class="bi bi-graph-up-arrow fs-2 text-primary"></i>
                        </div>
                        <h5>Growth Opportunities</h5>
                        <p class="text-muted">Continuous learning and career advancement in a fast-growing company.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="premium-card text-center p-4 h-100">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3 mb-4 mx-auto" style="width: 80px; height: 80px">
                            <i class="bi bi-heart-pulse fs-2 text-primary"></i>
                        </div>
                        <h5>Work-Life Balance</h5>
                        <p class="text-muted">Flexible work arrangements and comprehensive wellness programs.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="premium-card text-center p-4 h-100">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3 mb-4 mx-auto" style="width: 80px; height: 80px">
                            <i class="bi bi-people fs-2 text-primary"></i>
                        </div>
                        <h5>Great Culture</h5>
                        <p class="text-muted">Collaborative environment with diverse and talented professionals.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="premium-card text-center p-4 h-100">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3 mb-4 mx-auto" style="width: 80px; height: 80px">
                            <i class="bi bi-trophy fs-2 text-primary"></i>
                        </div>
                        <h5>Competitive Benefits</h5>
                        <p class="text-muted">Attractive compensation, health insurance, and learning allowances.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Current Openings Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row mb-5">
                <div class="col-lg-6">
                    <h2 class="section-heading" data-aos="fade-up">Current Openings</h2>
                    <p class="lead text-muted" data-aos="fade-up" data-aos-delay="100">Join our team and be part of something extraordinary</p>
                </div>
                <div class="col-lg-6 text-lg-end" data-aos="fade-up" data-aos-delay="200">
                    <div class="btn-group" role="group" aria-label="Job filter">
                        <button type="button" class="btn btn-primary active" data-filter="all">All</button>
                        <button type="button" class="btn btn-outline-primary" data-filter="Full-time">Full-time</button>
                        <button type="button" class="btn btn-outline-primary" data-filter="Remote">Remote</button>
                    </div>
                </div>
            </div>
            <div class="row g-4">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($job = mysqli_fetch_assoc($result)): ?>
                        <div class="col-lg-6" data-aos="fade-up" data-job-type="<?php echo htmlspecialchars($job['job_type']); ?>">
                            <div class="premium-card job-card h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-4">
                                        <div>
                                            <span class="badge bg-<?php echo $job['job_type'] === 'Full-time' ? 'primary' : 'success'; ?> mb-2">
                                                <?php echo htmlspecialchars($job['job_type']); ?>
                                            </span>
                                            <h4 class="card-title mb-2"><?php echo htmlspecialchars($job['job_title']); ?></h4>
                                            <div class="d-flex align-items-center text-muted mb-3">
                                                <i class="bi bi-geo-alt me-2"></i>
                                                <span><?php echo htmlspecialchars($job['location']); ?></span>
                                            </div>
                                        </div>
                                        <div class="job-salary">
                                            <span class="text-primary fw-bold"><?php echo htmlspecialchars($job['salary_range']); ?></span>
                                            <span class="text-muted d-block">per annum</span>
                                        </div>
                                    </div>
                                    <p class="card-text text-muted mb-4"><?php echo htmlspecialchars($job['job_description']); ?></p>
                                    <div class="job-requirements mb-4">
                                        <h6 class="mb-3">Key Requirements:</h6>
                                        <ul class="requirements-list">
                                            <?php
                                            $requirements = explode("\n", $job['requirements']);
                                            foreach ($requirements as $requirement) {
                                                if (!empty(trim($requirement))) {
                                                    echo '<li><i class="bi bi-check-circle-fill text-primary me-2"></i>' . htmlspecialchars(trim($requirement)) . '</li>';
                                                }
                                            }
                                            ?>
                                        </ul>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="job-meta">
                                            <span class="text-muted me-3">
                                                <i class="bi bi-clock me-1"></i>
                                                Posted <?php echo date('M d, Y', strtotime($job['created_at'])); ?>
                                            </span>
                                            <span class="text-muted">
                                                <i class="bi bi-calendar me-1"></i>
                                                Deadline: <?php echo date('M d, Y', strtotime($job['application_deadline'])); ?>
                                            </span>
                                        </div>
                                        <a href="apply.php?id=<?php echo $job['job_id']; ?>" class="btn btn-primary apply-btn">
                                            Apply Now <i class="bi bi-arrow-right ms-2"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12 text-center" data-aos="fade-up">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            No job openings available at the moment. Please check back later.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Life at GD Edu Tech -->
    <section class="py-5">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="section-heading" data-aos="fade-up">Life at GD Edu Tech</h2>
                    <p class="lead text-muted" data-aos="fade-up" data-aos-delay="100">See what makes our workplace special</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6" data-aos="fade-up">
                    <div class="premium-card overflow-hidden">
                        <img src="./Images/Others/office-1.jpg" alt="Office Life" class="img-fluid">
                        <div class="p-4">
                            <h5>Modern Workspace</h5>
                            <p class="text-muted mb-0">State-of-the-art facilities designed for productivity and collaboration.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="premium-card overflow-hidden">
                        <img src="./Images/Others/office-2.jpg" alt="Team Events" class="img-fluid">
                        <div class="p-4">
                            <h5>Team Events</h5>
                            <p class="text-muted mb-0">Regular team building activities and celebrations.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="premium-card overflow-hidden">
                        <img src="./Images/Others/office-3.jpg" alt="Learning Culture" class="img-fluid">
                        <div class="p-4">
                            <h5>Learning Culture</h5>
                            <p class="text-muted mb-0">Continuous learning and professional development opportunities.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section" data-aos="fade-up">
        <div class="floating-shape shape-1"></div>
        <div class="floating-shape shape-2"></div>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="display-5 fw-bold text-white mb-4">Ready to Join Our Team?</h2>
                    <p class="lead text-white-50 mb-5">Take the first step towards an exciting career with GD Edu Tech.</p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="#" class="btn btn-light btn-lg px-5 rounded-pill">
                            Apply Now <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                        <a href="contact.php" class="btn btn-outline-light btn-lg px-5 rounded-pill">
                            Contact Us
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- Include footer -->
    <?php include 'footer.php'; ?>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <!-- Back to Top Button -->
    <script src="js/back-to-top.js"></script>

    <style>
        /* Job Card Premium Styling */
        .job-card {
            border: none;
            background: #ffffff;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .job-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, rgba(13, 110, 253, 0.05), rgba(13, 110, 253, 0.02));
            opacity: 0;
            transition: all 0.3s ease;
        }

        .job-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .job-card:hover::before {
            opacity: 1;
        }

        .job-card .card-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .job-salary {
            text-align: right;
            padding: 0.5rem 1rem;
            background: rgba(13, 110, 253, 0.1);
            border-radius: 8px;
        }

        .job-salary span {
            display: block;
            line-height: 1.2;
        }

        .requirements-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .requirements-list li {
            margin-bottom: 0.75rem;
            display: flex;
            align-items: flex-start;
        }

        .requirements-list li i {
            margin-top: 0.25rem;
        }

        .job-meta {
            font-size: 0.9rem;
        }

        .apply-btn {
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .apply-btn:hover {
            transform: translateX(5px);
        }

        /* Mobile Responsiveness */
        @media (max-width: 767.98px) {
            .job-card {
                margin-bottom: 1.5rem;
            }

            .job-card .card-title {
                font-size: 1.3rem;
            }

            .job-salary {
                padding: 0.375rem 0.75rem;
            }

            .requirements-list li {
                font-size: 0.9rem;
            }

            .apply-btn {
                padding: 0.625rem 1.25rem;
                font-size: 0.9rem;
            }

            .job-meta {
                font-size: 0.8rem;
            }
        }

        .career-hero-image {
            max-width: 100%;
            height: auto;
            max-height: 400px;
            object-fit: contain;
            animation: float 6s ease-in-out infinite;
            transition: transform 0.3s ease;
            box-shadow: none;
            border: none;
        }
        @keyframes float {
            0% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-15px);
            }
            100% {
                transform: translateY(0px);
            }
        }
    </style>

    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            easing: 'ease-in-out',
            once: true,
            mirror: false
        });

        // Job filtering functionality
        document.addEventListener('DOMContentLoaded', function() {
            const filterButtons = document.querySelectorAll('[data-filter]');
            const jobCards = document.querySelectorAll('[data-job-type]');

            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Update active button
                    filterButtons.forEach(btn => {
                        btn.classList.remove('active');
                        btn.classList.remove('btn-primary');
                        btn.classList.add('btn-outline-primary');
                    });
                    this.classList.add('active');
                    this.classList.remove('btn-outline-primary');
                    this.classList.add('btn-primary');

                    const filter = this.getAttribute('data-filter');

                    // Filter job cards
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