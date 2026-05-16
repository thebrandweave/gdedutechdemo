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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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

    <!-- Page Header -->
    <section class="page-header position-relative overflow-hidden">
        <div class="container position-relative py-7">
            <div class="row align-items-center">
                <div class="col-md-7" data-aos="fade-right">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-3">
                            <li class="breadcrumb-item"><a href="index.php" class="text-white-50">Home</a></li>
                            <li class="breadcrumb-item active text-white" aria-current="page">About Us</li>
                        </ol>
                    </nav>
                    <h1 class="text-white display-5 fw-bold mb-3">About GD Edutech</h1>
                    <p class="text-white-50 lead mb-0">Empowering minds through quality education and innovative learning solutions.</p>
                </div>
                <!-- <div class="col-md-5 col-12 text-center" data-aos="fade-left">
                    <img src="./Images/Others/book.png" alt="About" class="about-hero-image">
                </div> -->
            </div>
        </div>
        <div class="page-header-shape">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
                <path fill="#ffffff" fill-opacity="1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,112C672,96,768,96,864,112C960,128,1056,160,1152,160C1248,160,1344,128,1392,112L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
            </svg>
        </div>
    </section>

    <!-- Our Story Section -->
    <section class="py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right">
                    <h2 class="section-heading">Our Story</h2>
                    <p class=""><b>Founded in 2020</b>, GD Edu Tech was born from a simple yet powerful idea: education should be accessible to everyone, everywhere.</p>
                    <p>What started as a small team of passionate educators and technologists has grown into a global platform serving thousands of students across the world. Our journey has been guided by our commitment to quality education and innovation.</p>
                    <p>Today, GD Edu Tech offers hundreds of courses across various disciplines, designed to empower learners to achieve their personal and professional goals.</p>
                </div>
                <div class="col-lg-6" data-aos="fade-left" data-aos-delay="200">
                    <div class="position-relative">
                        <img src="./Images/Others/about-hero1.jpg" alt="Our Story" class="img-fluid rounded shadow" style="height: 400px; object-fit: cover;">
                        <!-- <div class="position-absolute top-0 start-0 translate-middle bg-primary text-white rounded-circle p-4" style="width: 120px; height: 120px; display: flex; align-items: center; justify-content: center;">
                            <div class="text-center">
                                <h3 class="mb-0">2020</h3>
                                <p class="mb-0 small">Founded</p>
                            </div>
                        </div> -->
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission and Vision -->
    <section class="py-5 bg-light">
        <div class="container py-5">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="section-heading text-center" data-aos="fade-up">Our Mission & Vision</h2>
                    <p class="lead text-muted" data-aos="fade-up" data-aos-delay="200">Guiding principles that drive everything we do</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-6" data-aos="fade-up">
                    <div class="premium-card p-5 h-100">
                        <div class="d-flex align-items-center mb-4">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                                <i class="bi bi-bullseye fs-3 text-primary"></i>
                            </div>
                            <h4 class="mb-0">Our Mission</h4>
                        </div>
                        <p>To democratize education by providing affordable, accessible, and high-quality learning experiences that empower individuals to transform their lives and communities.</p>
                        <p>We believe that education is a fundamental right, not a privilege. By leveraging technology, we aim to break down barriers to education and create opportunities for lifelong learning.</p>
                    </div>
                </div>
                <div class="col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="premium-card p-5 h-100">
                        <div class="d-flex align-items-center mb-4">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                                <i class="bi bi-eye fs-3 text-primary"></i>
                            </div>
                            <h4 class="mb-0">Our Vision</h4>
                        </div>
                        <p>To be the world's leading platform for transformative learning experiences, recognized for excellence, innovation, and impact.</p>
                        <p>We envision a world where anyone, regardless of background or circumstances, can access education that unlocks their potential and enables them to achieve their dreams.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Values -->
    <section class="py-5">
        <div class="container py-5">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="section-heading text-center" data-aos="fade-up">Our Values</h2>
                    <p class="lead text-muted" data-aos="fade-up" data-aos-delay="200">The core principles that guide our decisions and actions</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6" data-aos="fade-up">
                    <div class="premium-card p-4 text-center h-100">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3 mb-4 mx-auto" style="width: 80px; height: 80px">
                            <i class="bi bi-lightbulb fs-2 text-primary"></i>
                        </div>
                        <h5>Innovation</h5>
                        <p class="text-muted">We embrace change and continuously seek new ways to improve the learning experience.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="premium-card p-4 text-center h-100">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3 mb-4 mx-auto" style="width: 80px; height: 80px">
                            <i class="bi bi-shield-check fs-2 text-primary"></i>
                        </div>
                        <h5>Excellence</h5>
                        <p class="text-muted">We are committed to the highest standards of quality in everything we do.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="premium-card p-4 text-center h-100">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3 mb-4 mx-auto" style="width: 80px; height: 80px">
                            <i class="bi bi-people fs-2 text-primary"></i>
                        </div>
                        <h5>Inclusivity</h5>
                        <p class="text-muted">We believe in creating learning experiences that are accessible to all, regardless of background.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="premium-card p-4 text-center h-100">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3 mb-4 mx-auto" style="width: 80px; height: 80px">
                            <i class="bi bi-heart fs-2 text-primary"></i>
                        </div>
                        <h5>Empathy</h5>
                        <p class="text-muted">We understand our learners' needs and create experiences with their success in mind.</p>
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

    <!-- CTA Section -->
    <section class="cta-section" data-aos="fade-up">
        <div class="floating-shape shape-1"></div>
        <div class="floating-shape shape-2"></div>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="display-5 fw-bold mb-4">Join Our Learning Community</h2>
                    <p class="lead mb-5">Become part of our global community of learners and transform your future today.</p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="./studentPanel/signup.php" class="btn btn-light btn-lg px-5 rounded-pill">
                            Join Now <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                        <a href="contact.php" class="btn btn-outline-light btn-lg px-5 rounded-pill">
                            Contact Us
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

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