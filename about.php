<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once './Configurations/config.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - GD Edu Tech</title>
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
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800&family=Great+Vibes&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Custom JavaScript -->
    <script src="./js/main.js" defer></script>
        
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <style>

        /* Add custom styles for the contact image */
        .about-hero-image {
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
        /* Mobile Responsiveness for About Page */
        @media (max-width: 991.98px) {
            .about-header {
                padding: 100px 0 60px;
            }

            .about-header h1 {
                font-size: 2.5rem;
            }

            .about-header p {
                font-size: 1.1rem;
            }

            .mission-vision-card {
                padding: 2rem;
            }

            .team-member-card {
                margin-bottom: 2rem;
            }

            .team-member-image {
                height: 250px;
            }

            .stats-card {
                padding: 2rem;
                margin-bottom: 1.5rem;
            }

            .stats-number {
                font-size: 2.5rem;
            }
        }

        @media (max-width: 767.98px) {
            .about-header {
                padding: 80px 0 40px;
                text-align: center;
            }

            .about-header h1 {
                font-size: 2rem;
            }

            .about-header p {
                font-size: 1rem;
            }

            .mission-vision-card {
                padding: 1.5rem;
                margin-bottom: 1.5rem;
            }

            .mission-vision-card h3 {
                font-size: 1.5rem;
            }

            .team-member-card {
                margin-bottom: 1.5rem;
            }

            .team-member-image {
                height: 200px;
            }

            .team-member-info h4 {
                font-size: 1.3rem;
            }

            .stats-card {
                padding: 1.5rem;
                text-align: center;
            }

            .stats-number {
                font-size: 2rem;
            }

            .stats-label {
                font-size: 1rem;
            }

            .timeline-item {
                padding: 1.5rem;
            }

            .timeline-item h4 {
                font-size: 1.3rem;
            }

            .values-card {
                padding: 1.5rem;
                margin-bottom: 1.5rem;
            }

            .values-card h3 {
                font-size: 1.5rem;
            }

            .cta-section {
                padding: 3rem 0;
            }

            .cta-section h2 {
                font-size: 1.8rem;
            }

            .cta-section p {
                font-size: 1rem;
            }

            .btn-cta {
                width: 100%;
                margin-top: 1rem;
            }
        }

        /* Touch-friendly improvements */
        .btn, 
        .nav-link,
        .team-member-card,
        .values-card {
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        /* Animation improvements */
        @media (prefers-reduced-motion: no-preference) {
            .mission-vision-card,
            .team-member-card,
            .stats-card,
            .values-card {
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }

            .mission-vision-card:hover,
            .team-member-card:hover,
            .stats-card:hover,
            .values-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            }
        }
    </style>
</head>

<body>
    <?php include 'navbar.php'; ?>

    <!-- Redesigned Executive Page Header -->
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
                            <li class="breadcrumb-item active text-black" aria-current="page">About Us</li>
                        </ol>
                    </nav>

                    <h1 class="display-4 fw-bold text-black mb-3">
                        About <span class="cta-gold-text">GD Edu Tech</span>
                    </h1>

                    <p class="lead text-black-50 mb-4" style="max-width: 600px;">
                        Empowering minds through quality education, industry-aligned practical training, and innovative digital learning solutions.
                    </p>

                    <div class="d-flex flex-wrap gap-3 align-items-center pt-2">
                        <div class="about-header-badge">
                            <i class="bi bi-people-fill text-warning"></i>
                            <span style="color: #000;">10,000+ Alumni</span>
                        </div>
                        <div class="about-header-badge">
                            <i class="bi bi-book-half text-info"></i>
                            <span style="color: #000;">200+ Courses</span>
                        </div>
                        <div class="about-header-badge">
                            <i class="bi bi-patch-check-fill text-success"></i>
                            <span style="color: #000;">98% Career Success</span>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Floating Vision Glass Card -->
                <div class="col-lg-5 text-center text-lg-end" data-aos="fade-left" data-aos-delay="200">
                    <div class="about-header-card text-start">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="rounded-circle  bg-opacity-20 p-3 text-warning">
                                <i class="bi bi-mortarboard-fill fs-3"></i>
                            </div>
                            <div>
                                <h6 class="text-black fw-bold mb-0">Our Core Mission</h6>
                                <span class="text-black-50 small">Transforming Futures Worldwide</span>
                            </div>
                        </div>
                        <p class="text-black-50 small mb-0" style="line-height: 1.6;">
                            Bridge the gap between academic theory and practical industry demand through accessible, high-impact education.
                        </p>
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

    <!-- Our Story Section (Executive Portrait & Quote Layout) -->
    <section class="story-executive-section py-5 position-relative overflow-hidden w-100 my-0">
        <div class="story-bg-skyline position-absolute inset-0 w-100"></div>
        <div class="container py-4 position-relative z-2">
            <div class="row align-items-center g-5">
                <!-- Left Column: Quote Manifesto & Founder Info -->
                <div class="col-lg-8 text-start" data-aos="fade-right">
                    <h2 class="executive-quote-title text-uppercase fw-bold mb-4">
                        <span class="quote-closing">“</span>Learning never stops. Technology just makes the journey better.<span class="quote-closing">”</span>
                    </h2>
                    
                    <div class="signature-block mt-4 pt-2">
                        <h5 class="founder-name text-uppercase fw-bold mb-1">SAMEER AKBAR</h5>
                        <p class="founder-title text-uppercase text-muted small mb-0">MANAGING DIRECTOR</p>
                    </div>
                </div>

                <!-- Right Column: Arch Dome Portrait Frame -->
                <div class="col-lg-4 text-center" data-aos="fade-left" data-aos-delay="200">
                    <div class="executive-portrait-wrapper">
                        <div class="executive-portrait-card">
                            <img src="./Images/Others/md.png" alt="Our Story Portrait" class="img-fluid">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Redesigned Mission & Vision Section -->
    <section class="mv-section py-5">
        <div class="container py-4">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <div class="promo-badge mb-3 d-inline-flex align-items-center px-3 py-1 rounded-pill" data-aos="fade-up">
                        <i class="bi bi-compass-fill text-warning me-2"></i> GUIDING PRINCIPLES
                    </div>
                    <h2 class="display-5 fw-bold text-center mb-3" data-aos="fade-up" data-aos-delay="100">
                        Our <span class="highlight-gold">Mission</span> &amp; <span class="text-gradient">Vision</span>
                    </h2>
                    <!-- <p class="lead text-muted" data-aos="fade-up" data-aos-delay="200">
                        The core purpose and future roadmap driving everything we build at GD Edu Tech
                    </p> -->
                </div>
            </div>

            <div class="row g-4">
                <!-- Mission Card -->
                <div class="col-lg-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="mv-card h-100">
                        <div class="mv-card-accent-1"></div>
                        <i class="bi bi-bullseye mv-watermark-icon"></i>
                        
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="mv-icon-box mv-icon-box-1">
                                <i class="bi bi-bullseye"></i>
                            </div>
                            <span class="mv-badge mv-badge-1">01 · OBJECTIVE</span>
                        </div>

                        <h3 class="fw-bold fs-3 text-dark mb-3">Our Mission</h3>
                        <p class="text-secondary leading-relaxed mb-4">
                            To democratize education by providing affordable, accessible, and high-quality learning experiences that empower individuals to transform their lives and communities.
                        </p>
                        <p class="text-muted leading-relaxed mb-4">
                            We believe that education is a fundamental right, not a privilege. By leveraging cutting-edge technology and industry-aligned mentorship, we break down barriers to create lifelong learning opportunities.
                        </p>

                        <hr class="my-4 border-slate-200">

                        <div class="mv-pillars">
                            <div class="mv-list-item">
                                <i class="bi bi-check-circle-fill text-primary me-3 fs-5"></i>
                                <span>Accessible &amp; Affordable Industry Education</span>
                            </div>
                            <div class="mv-list-item">
                                <i class="bi bi-check-circle-fill text-primary me-3 fs-5"></i>
                                <span>Empowering Practical Skill Mastery</span>
                            </div>
                            <div class="mv-list-item">
                                <i class="bi bi-check-circle-fill text-primary me-3 fs-5"></i>
                                <span>Breaking Geographical &amp; Economic Barriers</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vision Card -->
                <div class="col-lg-6" data-aos="fade-up" data-aos-delay="400">
                    <div class="mv-card h-100">
                        <div class="mv-card-accent-2"></div>
                        <i class="bi bi-eye mv-watermark-icon"></i>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="mv-icon-box mv-icon-box-2">
                                <i class="bi bi-eye-fill"></i>
                            </div>
                            <span class="mv-badge mv-badge-2">02 · ASPIRATION</span>
                        </div>

                        <h3 class="fw-bold fs-3 text-dark mb-3">Our Vision</h3>
                        <p class="text-secondary leading-relaxed mb-4">
                            To be the world's leading platform for transformative learning experiences, globally recognized for excellence, innovation, and career impact.
                        </p>
                        <p class="text-muted leading-relaxed mb-4">
                            We envision a world where anyone, regardless of background or circumstances, can seamlessly access education that unlocks their true potential and enables them to achieve their professional dreams.
                        </p>

                        <hr class="my-4 border-slate-200">

                        <div class="mv-pillars">
                            <div class="mv-list-item">
                                <i class="bi bi-star-fill text-warning me-3 fs-5"></i>
                                <span>Global Excellence in Career-Ready Learning</span>
                            </div>
                            <div class="mv-list-item">
                                <i class="bi bi-star-fill text-warning me-3 fs-5"></i>
                                <span>Fostering Innovation &amp; Hands-On Mastery</span>
                            </div>
                            <div class="mv-list-item">
                                <i class="bi bi-star-fill text-warning me-3 fs-5"></i>
                                <span>Unlocking Potential for Every Single Student</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Redesigned Our Values Section -->
    <section class="values-section py-5">
        <div class="container py-4">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <div class="promo-badge mb-3 d-inline-flex align-items-center px-3 py-1 rounded-pill" data-aos="fade-up">
                        <i class="bi bi-star-fill text-warning me-2"></i> CORE PILLARS
                    </div>
                    <h2 class="display-5 fw-bold text-center mb-3" data-aos="fade-up" data-aos-delay="100">
                        Our Core <span class="highlight-gold">Values</span>
                    </h2>
                    <p class="lead text-muted" data-aos="fade-up" data-aos-delay="200">
                        The fundamental principles driving our culture, choices, and commitment to every learner
                    </p>
                </div>
            </div>

            <div class="row g-4">
                <!-- Value 1: Innovation -->
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="val-card val-card-1 h-100">
                        <div class="val-top-accent"></div>
                        <i class="bi bi-lightbulb-fill val-watermark"></i>
                        
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="val-icon-box">
                                <i class="bi bi-lightbulb-fill"></i>
                            </div>
                            <span class="val-number">01</span>
                        </div>

                        <h4 class="val-title">Innovation</h4>
                        <p class="val-desc">
                            We embrace change and continuously pioneer creative learning methodologies to keep education modern and engaging.
                        </p>
                    </div>
                </div>

                <!-- Value 2: Excellence -->
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="val-card val-card-2 h-100">
                        <div class="val-top-accent"></div>
                        <i class="bi bi-shield-check val-watermark"></i>
                        
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="val-icon-box">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <span class="val-number">02</span>
                        </div>

                        <h4 class="val-title">Excellence</h4>
                        <p class="val-desc">
                            We commit to the highest standards of quality, accuracy, and practical rigor in everything we teach and deliver.
                        </p>
                    </div>
                </div>

                <!-- Value 3: Inclusivity -->
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="400">
                    <div class="val-card val-card-3 h-100">
                        <div class="val-top-accent"></div>
                        <i class="bi bi-people-fill val-watermark"></i>
                        
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="val-icon-box">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <span class="val-number">03</span>
                        </div>

                        <h4 class="val-title">Inclusivity</h4>
                        <p class="val-desc">
                            We build accessible learning experiences welcoming to everyone, breaking down economic and social boundaries.
                        </p>
                    </div>
                </div>

                <!-- Value 4: Empathy -->
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="500">
                    <div class="val-card val-card-4 h-100">
                        <div class="val-top-accent"></div>
                        <i class="bi bi-heart-fill val-watermark"></i>
                        
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="val-icon-box">
                                <i class="bi bi-heart-fill"></i>
                            </div>
                            <span class="val-number">04</span>
                        </div>

                        <h4 class="val-title">Empathy</h4>
                        <p class="val-desc">
                            We prioritize student growth by actively listening to their challenges and supporting their personal career success.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <!-- <section class="py-5 bg-light">
        <div class="container py-5">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="section-heading text-center" data-aos="fade-up">Meet Our Team</h2>
                    <p class="lead text-muted" data-aos="fade-up" data-aos-delay="200">The passionate individuals behind GD Edu Tech</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6" data-aos="fade-up">
                    <div class="premium-card text-center h-100 p-4">
                        <div class="rounded-circle overflow-hidden mb-4 mx-auto" style="width: 150px; height: 150px;">
                            <img src="./Images/Others/team-1.jpg" alt="Team Member" class="img-fluid">
                        </div>
                        <h5>Dr. Sarah Johnson</h5>
                        <p class="text-primary mb-3">Founder & CEO</p>
                        <p class="text-muted small">Former professor with a passion for making education accessible to all.</p>
                        <div class="social-links mt-3">
                            <a href="#" class="text-primary mx-2"><i class="bi bi-linkedin"></i></a>
                            <a href="#" class="text-primary mx-2"><i class="bi bi-twitter"></i></a>
                            <a href="#" class="text-primary mx-2"><i class="bi bi-envelope"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="premium-card text-center h-100 p-4">
                        <div class="rounded-circle overflow-hidden mb-4 mx-auto" style="width: 150px; height: 150px;">
                            <img src="./Images/Others/team-2.jpg" alt="Team Member" class="img-fluid">
                        </div>
                        <h5>Michael Chen</h5>
                        <p class="text-primary mb-3">CTO</p>
                        <p class="text-muted small">Tech innovator with experience in creating educational platforms.</p>
                        <div class="social-links mt-3">
                            <a href="#" class="text-primary mx-2"><i class="bi bi-linkedin"></i></a>
                            <a href="#" class="text-primary mx-2"><i class="bi bi-github"></i></a>
                            <a href="#" class="text-primary mx-2"><i class="bi bi-envelope"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="premium-card text-center h-100 p-4">
                        <div class="rounded-circle overflow-hidden mb-4 mx-auto" style="width: 150px; height: 150px;">
                            <img src="./Images/Others/team-3.jpg" alt="Team Member" class="img-fluid">
                        </div>
                        <h5>Emily Rodriguez</h5>
                        <p class="text-primary mb-3">Head of Content</p>
                        <p class="text-muted small">Curriculum expert with a background in instructional design.</p>
                        <div class="social-links mt-3">
                            <a href="#" class="text-primary mx-2"><i class="bi bi-linkedin"></i></a>
                            <a href="#" class="text-primary mx-2"><i class="bi bi-twitter"></i></a>
                            <a href="#" class="text-primary mx-2"><i class="bi bi-envelope"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="premium-card text-center h-100 p-4">
                        <div class="rounded-circle overflow-hidden mb-4 mx-auto" style="width: 150px; height: 150px;">
                            <img src="./Images/Others/team-4.jpg" alt="Team Member" class="img-fluid">
                        </div>
                        <h5>David Patel</h5>
                        <p class="text-primary mb-3">Head of Student Success</p>
                        <p class="text-muted small">Dedicated to ensuring every student achieves their learning goals.</p>
                        <div class="social-links mt-3">
                            <a href="#" class="text-primary mx-2"><i class="bi bi-linkedin"></i></a>
                            <a href="#" class="text-primary mx-2"><i class="bi bi-twitter"></i></a>
                            <a href="#" class="text-primary mx-2"><i class="bi bi-envelope"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section> -->

    <!-- Floating Plan Tilted Cards CTA Section (Full-Width Edge-to-Edge) -->
    <section class="cta-plan-section py-0 my-0 w-100 position-relative overflow-hidden" data-aos="fade-up">
        <div class="cta-plan-banner position-relative text-center w-100 rounded-0 border-0">
            
            <!-- Wavy Dashed Track Decor Left & Right -->
            <svg class="cta-wavy-track track-left d-none d-lg-block" viewBox="0 0 220 130" fill="none" stroke="#7e858d" stroke-width="3" stroke-dasharray="6 6">
                <path d="M10,110 C70,20 140,120 210,20" />
            </svg>
            <svg class="cta-wavy-track track-right d-none d-lg-block" viewBox="0 0 220 130" fill="none" stroke="#7e858d" stroke-width="3" stroke-dasharray="6 6">
                <path d="M10,20 C80,120 150,20 210,110" />
            </svg>

            <!-- Tilted Left Floating Plan Card: Student Pass -->
            <div class="plan-card plan-card-left text-start d-none d-lg-block">
                <div class="plan-header mb-2">
                    <h6 class="plan-title fw-bold mb-1"><i class="bi bi-mortarboard-fill text-primary me-2"></i> Student Pass</h6>
                    <p class="plan-subtext text-muted small mb-0">Essential learning track.</p>
                </div>
                <div class="plan-price mb-2">
                    <span class="price-num fw-bold fs-5 text-primary">Skill Focused</span>
                </div>
                <ul class="plan-features list-unstyled small mb-3" style="font-size: 0.82rem;">
                    <li class="mb-1.5"><i class="bi bi-check2 me-1.5 text-dark fw-bold"></i> 10+ Core Tech Courses</li>
                    <li class="mb-1.5"><i class="bi bi-check2 me-1.5 text-dark fw-bold"></i> Self-Paced Practice Labs</li>
                    <li class="mb-1.5"><i class="bi bi-check2 me-1.5 text-dark fw-bold"></i> Verified Certificates</li>
                </ul>
                <a href="courses.php" class="btn btn-plan-dark w-100 text-center text-decoration-none">Our Courses</a>
            </div>

            <!-- Center Headline & Action Buttons -->
            <div class="cta-center-content mx-auto text-center px-3">
                <h2 class="cta-banner-heading fw-bold mb-3">
                    Start your learning journey with GD Edu Tech today.
                </h2>
                <p class="cta-banner-subtext text-muted mb-4">
                    Empowering students and working professionals with practical skills, expert mentorship, and industry-recognized certifications.
                </p>
                <div class="d-flex flex-wrap gap-3 justify-content-center align-items-center">
                  
                    <a href="./studentPanel/signup.php" class="btn btn-cta-secondary-outline text-decoration-none">
                        <span>Get Started</span>
                        <i class="bi bi-arrow-right ms-2 fs-5"></i>
                    </a>
                </div>
            </div>

            <!-- Tilted Right Floating Plan Card: Career Track -->
            <div class="plan-card plan-card-right text-start d-none d-lg-block">
                <div class="plan-header mb-2">
                    <h6 class="plan-title fw-bold mb-1"><i class="bi bi-stars text-warning me-2"></i> Career Track</h6>
                    <p class="plan-subtext text-muted small mb-0">Mentorship for growth.</p>
                </div>
                <div class="plan-price mb-2">
                    <span class="price-num fw-bold fs-5 text-warning" style="color: #d97706 !important;">Job Ready</span>
                </div>
                <ul class="plan-features list-unstyled small mb-3" style="font-size: 0.82rem;">
                    <li class="mb-1.5"><i class="bi bi-diamond-fill me-1.5 text-dark small"></i> Unlimited Pro Courses</li>
                    <li class="mb-1.5"><i class="bi bi-diamond-fill me-1.5 text-dark small"></i> 1-on-1 Mentorship</li>
                    <li class="mb-1.5"><i class="bi bi-diamond-fill me-1.5 text-dark small"></i> Placement Support</li>
                </ul>
                <a href="contact.php" class="btn btn-plan-dark w-100 text-center text-decoration-none">Contact Us</a>
            </div>

        </div>
    </section>



     

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
    <!-- Back to Top Button -->

    <!-- Include footer -->
    <?php include 'footer.php'; ?>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <!-- Back to Top Button -->
    <script src="js/back-to-top.js"></script>
</body>

</html> 