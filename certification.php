<?php 
session_start();
require_once './Configurations/config.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certifications & Accreditations - GD Edu Tech</title>
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
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Custom JavaScript -->
    <script src="./js/main.js" defer></script>
    <link rel="icon" type="image/png" href="./Images/Logos/GD_Only_logo.png">
    <link rel="icon" type="image/png" sizes="32x32" href="./Images/Logos/GD_Only_logo.png">
    <link rel="icon" type="image/png" sizes="16x16" href="./Images/Logos/GD_Only_logo.png">
    <link rel="shortcut icon" href="./Images/Logos/GD_Only_logo.png">
    <link rel="apple-touch-icon" href="./Images/Logos/GD_Only_logo.png">
    <meta name="msapplication-TileImage" content="./Images/Logos/GD_Only_logo.png">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">

    <style>
        body {
            background-color: #f8fafc;
            font-family: 'Poppins', sans-serif;
            color: #0f172a;
        }

        /* Certificate Showcase Section */
        .cert-showcase-section {
            padding: 80px 0;
            background-color: #ffffff;
        }

        /* Standalone Certificate Cards */
        .cert-card-standalone {
            background: #ffffff;
            border-radius: 24px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 15px 35px -10px rgba(15, 23, 42, 0.08);
            overflow: hidden;
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.4s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .cert-card-standalone:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px -10px rgba(13, 114, 152, 0.18);
            border-color: rgba(13, 114, 152, 0.3);
        }

        .cert-image-frame {
            position: relative;
            background: radial-gradient(circle, #f8fafc 0%, #e2e8f0 100%);
            padding: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-grow: 1;
        }

        .cert-image-preview {
            width: 100%;
            max-height: 520px;
            object-fit: contain;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            transition: transform 0.4s ease;
        }

        .cert-card-standalone:hover .cert-image-preview {
            transform: scale(1.025);
        }

        .cert-badge-overlay {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(13, 114, 152, 0.2);
            color: var(--primary, #0d7298);
            font-size: 0.82rem;
            font-weight: 700;
            padding: 8px 18px;
            border-radius: 50px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            z-index: 10;
        }

        .cert-card-footer {
            background: #ffffff;
            border-top: 1px solid #f1f5f9;
        }

        /* Feature Pillars */
        .trust-pillar-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 30px 24px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.04);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }

        .trust-pillar-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.08);
            border-color: rgba(13, 114, 152, 0.25);
        }

        .pillar-icon-box {
            width: 55px;
            height: 55px;
            border-radius: 16px;
            background: rgba(13, 114, 152, 0.1);
            color: var(--primary, #0d7298);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 20px;
        }

        .hero-award-img:hover {
            transform: translateY(-8px) scale(1.03);
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
                            <li class="breadcrumb-item active text-black" aria-current="page">Certifications</li>
                        </ol>
                    </nav>

                    <h1 class="display-4 fw-bold text-black mb-3">
                        Recognized <span class="cta-gold-text">Certifications</span>
                    </h1>

                    <p class="lead text-black-50 mb-4" style="max-width: 600px;">
                        Explore our official recognitions, accreditation standards, and institutional certifications that reflect our commitment to quality education.
                    </p>

                    <div class="d-flex flex-wrap gap-3 align-items-center pt-2">
                        <div class="about-header-badge">
                            <i class="bi bi-patch-check-fill text-warning"></i>
                            <span style="color: #0d7298;">ISO Certified</span>
                        </div>
                        <div class="about-header-badge">
                            <i class="bi bi-shield-check text-info"></i>
                            <span style="color: #0d7298;"   >Industry Approved</span>
                        </div>
                        <div class="about-header-badge">
                            <i class="bi bi-award-fill text-success"></i>
                            <span style="color: #0d7298;">MSME Certified</span>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Floating Vision Glass Card -->
                <div class="col-lg-5 text-center text-lg-end" data-aos="fade-left" data-aos-delay="200">
                    <div class="about-header-card text-start">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="rounded-circle bg-opacity-20 p-3 text-warning">
                                <i class="bi bi-award-fill fs-3"></i>
                            </div>
                            <div>
                                <h6 class="text-black fw-bold mb-0">Official Recognition</h6>
                                <span class="text-black-50 small">Verified Credentials</span>
                            </div>
                        </div>
                        <p class="text-black-50 small mb-0" style="line-height: 1.6;">
                            Our educational courses and skill development initiatives strictly adhere to global quality benchmarks and industry standards.
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

    <!-- Certificate Showcase Section -->
    <section class="cert-showcase-section">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto" data-aos="fade-up">
                    <div class="promo-badge mb-3 d-inline-flex align-items-center px-3 py-1 rounded-pill">
                        <i class="bi bi-star-fill text-warning me-2"></i> VERIFIED ACCREDITATION
                    </div>
                    <h2 class="display-6 fw-bold mb-3">Institutional <span class="highlight-gold">Certifications</span></h2>
                    <p class="lead text-muted">
                        Official certifications approving our educational programs, technical skill development, and career training operations.
                    </p>
                </div>
            </div>

            <div class="row g-4 justify-content-center">
                <!-- CARD 1: Institute Certification -->
                <div class="col-lg-6 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="cert-card-standalone">
                        <div class="cert-image-frame position-relative">
                            <div class="cert-badge-overlay">
                                <i class="bi bi-patch-check-fill text-primary me-1"></i> Officially Verified
                            </div>
                       
                                <img src="./assets/images/certificate.jpg" alt="Institute Certification" class="cert-image-preview img-fluid">
                      
                        </div>
                        <div class="cert-card-footer text-center p-4">
                            <h4 class="fw-bold fs-5 mb-1">Institute Certification</h4>
                            <p class="text-muted small mb-0">Official certification approved for educational programs &amp; skill development initiatives.</p>
                        </div>
                    </div>
                </div>

                <!-- CARD 2: Course Completion Certificate -->
                <div class="col-lg-6 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="cert-card-standalone">
                        <div class="cert-image-frame position-relative">
                            <div class="cert-badge-overlay" style="color: #0284c7; border-color: rgba(2, 132, 199, 0.3);">
                                <i class="bi bi-mortarboard-fill text-warning me-1"></i> Course Certificate
                            </div>
                     
                                <img src="./uploads/certificates/coursecert.png" alt="Course Completion Certificate" class="cert-image-preview img-fluid" onerror="this.onerror=null; this.src='./assets/images/certificate.jpg';">
                         
                        </div>
                        <div class="cert-card-footer text-center p-4">
                            <h4 class="fw-bold fs-5 mb-1">Course Completion Certificate</h4>
                            <p class="text-muted small mb-0">Awarded to students upon successful completion of GD Edu Tech training programs &amp; hands-on projects.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Trust Pillars Grid -->
            <div class="row g-4 mt-5">
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="trust-pillar-card">
                        <div class="pillar-icon-box">
                            <i class="bi bi-award-fill"></i>
                        </div>
                        <h5 class="fw-bold mb-2">ISO Standardized Quality</h5>
                        <p class="text-muted small mb-0">
                            Our educational processes, module delivery, and practical evaluations strictly adhere to international quality benchmarks.
                        </p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="trust-pillar-card">
                        <div class="pillar-icon-box" style="background: rgba(255, 107, 53, 0.1); color: #ff6b35;">
                            <i class="bi bi-gear-wide-connected"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Industry-Aligned Modules</h5>
                        <p class="text-muted small mb-0">
                            Curriculum designed in collaboration with leading technology experts to ensure real-world career readiness.
                        </p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mx-auto" data-aos="fade-up" data-aos-delay="400">
                    <div class="trust-pillar-card">
                        <div class="pillar-icon-box" style="background: rgba(99, 102, 241, 0.1); color: #4f46e5;">
                            <i class="bi bi-patch-check-fill"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Authentic Credentialing</h5>
                        <p class="text-muted small mb-0">
                            Every student certificate issued includes unique verification parameters for seamless employer validation.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>