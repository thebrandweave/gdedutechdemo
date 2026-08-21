<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scholarship Application 2026-27 - GD Edu Tech</title>
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
    
    <link rel="icon" type="image/png" href="./Images/Logos/GD_Only_logo.png">

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

        /* Hero Image Showcase */
        .scholarship-hero-image {
            max-width: 100%;
            height: auto;
            max-height: 468px;
            object-fit: contain;
            filter: drop-shadow(0 20px 35px rgba(0, 0, 0, 0.18));
            transition: transform 0.4s ease;
        }

        .scholarship-hero-image:hover {
            transform: translateY(-8px) scale(1.02);
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-12px); }
            100% { transform: translateY(0px); }
        }

        /* Scholarship Form Container */
        .scholarship-card {
            max-width: 920px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 28px;
            box-shadow: 0 20px 60px -15px rgba(15, 23, 42, 0.1);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        .scholarship-header {
            background: linear-gradient(112deg, #adc8e3 0%, #cadbec 38%, #dca3ab 80%, #dca3ab 100%);
            padding: 35px 30px;
            color: #ffffff;
            text-align: center;
            position: relative;
        }

        .scholarship-header-logo {
            max-width: 190px;
            height: auto;
            margin-bottom: 12px;
            filter: drop-shadow(0 4px 10px rgba(0,0,0,0.15));
        }

        /* Section Dividers */
        .form-section-title {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #020303;
            font-weight: 700;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 30px 0 18px;
        }

        .form-section-title::after {
            content: "";
            flex: 1;
            height: 1.5px;
            background: #e2e8f0;
        }

        .form-section-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: rgba(13, 114, 152, 0.1);
            color: #0d7298;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .field-label {
            font-weight: 600;
            font-size: 0.88rem;
            color: #334155;
            margin-bottom: 7px;
            display: block;
        }

        .required-star {
            color: #e11d48;
            font-weight: bold;
        }

        .styled-input, .styled-select, .styled-textarea {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid #cbd5e1;
            border-radius: 12px;
            background: #f8fafc;
            font-size: 0.95rem;
            color: #0f172a;
            transition: all 0.3s ease;
        }

        .styled-input:focus, .styled-select:focus, .styled-textarea:focus {
            outline: none;
            border-color: #0d7298;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(13, 114, 152, 0.12);
        }

        .styled-file-input {
            padding: 10px 14px;
            border: 1.5px dashed #cbd5e1;
            border-radius: 12px;
            background: #f8fafc;
            width: 100%;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .styled-file-input:hover {
            border-color: #0d7298;
            background: rgba(13, 114, 152, 0.03);
        }

        .btn-submit-app {
            background: linear-gradient(135deg, #0d7298 0%, #065d7d 100%);
            color: #ffffff;
            font-size: 1.05rem;
            font-weight: 700;
            border: 0;
            border-radius: 50px;
            padding: 16px 45px;
            box-shadow: 0 10px 25px rgba(13, 114, 152, 0.3);
            transition: all 0.3s ease;
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-submit-app:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(13, 114, 152, 0.4);
            color: #ffffff;
        }

        /* Success Overlay */
        #successOverlay {
            display: none;
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: #ffffff;
            z-index: 100;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 30px;
        }

        .success-icon {
            font-size: 4rem;
            color: #10b981;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <!-- Redesigned Executive Hero Banner -->
    <section class="about-page-header-1 position-relative overflow-hidden w-100 my-0">
        <div class="about-header-glow-1"></div>
        <div class="about-header-glow-2"></div>
        <div class="about-header-pattern"></div>

        <div class="container position-relative z-2 py-4">
            <div class="row align-items-center text-start g-4">
                <div class="col-lg-7" data-aos="fade-right">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb about-breadcrumb px-3 py-1.5 rounded-pill mb-3 d-inline-flex">
                            <li class="breadcrumb-item"><a href="index.php" class="text-black text-decoration-none"><i class="bi bi-house-door-fill me-1"></i> Home</a></li>
                            <li class="breadcrumb-item active text-black" aria-current="page">Apply Scholarship</li>
                        </ol>
                    </nav>

                    <h1 class="display-4 fw-bold text-black mb-3">
                        Scholarship Application <span class="cta-gold-text">2026-27</span>
                    </h1>

                    <p class="lead text-black-50 mb-4" style="max-width: 650px;">
                        Unlock financial support for your educational journey. Apply today for GD Edu Tech skill development scholarships.
                    </p>
                </div>

                <div class="col-lg-5 text-center text-lg-end" data-aos="fade-left" data-aos-delay="200">
                    <div class="position-relative d-inline-block">
                        <div class="position-absolute top-50 start-50 translate-middle rounded-circle  bg-opacity-20 blur-2xl" style="width: 220px; height: 220px; filter: blur(40px); z-index: 1;"></div>
                        <img src="./Images/Others/graduate.png" alt="Scholarship" class="img-fluid position-relative z-2 scholarship-hero-image">
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

    <!-- Main Scholarship Form Section -->
    <section class="py-5">
        <div class="container py-2">
            <div class="scholarship-card position-relative" data-aos="fade-up">
                
                <!-- Success Overlay -->
                <div id="successOverlay">
                    <i class="bi bi-check-circle-fill success-icon"></i>
                    <h2 class="fw-bold text-dark mb-2">Application Submitted!</h2>
                    <p class="text-secondary mb-4">Thank you for applying. Your scholarship response has been successfully recorded.</p>
                    <button onclick="location.reload()" class="btn btn-dark rounded-pill px-5 py-2.5 fw-bold">Submit Another Application</button>
                </div>

                <!-- Form Header -->
                <div class="scholarship-header">
                    <img src="./Images/Logos/GD_Full_logo.png" alt="GD EDU TECH" class="scholarship-header-logo">
                    <p class="mb-0 text-black-50 small fw-semibold text-uppercase tracking-wider">Official Scholarship Admission Form 2026-27</p>
                </div>

                <!-- Form Body -->
                <form class="p-4 p-md-5" id="fullForm" method="POST" action="submit.php" enctype="multipart/form-data">
                    
                    <!-- 1. Student Identity -->
                    <div class="form-section-title">
                        <span class="form-section-icon"><i class="bi bi-person-vcard-fill"></i></span>
                        <span>Student Identity</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="field-label">First Name <span class="required-star">*</span></label>
                            <input type="text" class="styled-input" name="firstName" placeholder="As per SSLC markscard" required>
                        </div>
                        <div class="col-md-6">
                            <label class="field-label">Last Name <span class="required-star">*</span></label>
                            <input type="text" class="styled-input" name="lastName" placeholder="Surname" required>
                        </div>
                    </div>

                    <!-- 2. Background Details -->
                    <div class="form-section-title">
                        <span class="form-section-icon"><i class="bi bi-house-door-fill"></i></span>
                        <span>Background Details</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="field-label">Residential Address</label>
                            <textarea class="styled-textarea" name="address" rows="3" placeholder="Full permanent address with pincode"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="field-label">School / College Details</label>
                            <textarea class="styled-textarea" name="school" rows="2" placeholder="School/College name & location"></textarea>
                        </div>
                    </div>

                    <!-- 3. Verification & Phone -->
                    <div class="form-section-title">
                        <span class="form-section-icon"><i class="bi bi-telephone-fill"></i></span>
                        <span>Verification Details</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="field-label">Phone Number <span class="required-star">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold text-dark border-end-0 rounded-start-3">+91</span>
                                <input type="tel" class="styled-input rounded-start-0" name="phone1" id="p1" placeholder="Enter 10-digit mobile number" required pattern="[0-9]{10}" title="Please enter a valid 10-digit phone number">
                            </div>
                        </div>
                    </div>

                    <!-- 4. Course & Medium Selection -->
                    <div class="form-section-title">
                        <span class="form-section-icon"><i class="bi bi-book-half"></i></span>
                        <span>Course &amp; Language Preference</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="field-label">Preferred Course <span class="required-star">*</span></label>
                            <select class="styled-select" name="course" required>
                                <option value="">Select Preferred Course</option>
                                <option>Digital Marketing</option>
                                <option>Graphic Designing</option>
                                <option>Architecture Designing</option>
                                <option>Interior Designing</option>
                                <option>FullStack Development</option>
                                <option>Video Editing</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="field-label">SSLC Medium</label>
                            <select class="styled-select" name="medium">
                                <option value="">Select SSLC Medium</option>
                                <option>English</option>
                                <option>Kannada</option>
                                <option>Urdu</option>
                                <option>Malayalam</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="field-label">Languages Known <span class="required-star">*</span></label>
                            <input type="text" class="styled-input" name="langTyped" id="langInput" placeholder="e.g. English, Hindi, Kannada, Tulu" required>
                            <span class="small text-muted mt-1 d-block"><i class="bi bi-info-circle me-1"></i>Separate multiple languages with commas.</span>
                        </div>
                    </div>

                    <!-- 5. Upload Documents -->
                    <div class="form-section-title">
                        <span class="form-section-icon"><i class="bi bi-cloud-arrow-up-fill"></i></span>
                        <span>Upload Documents</span>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="field-label">Upload SSLC Markscard <span class="required-star">*</span></label>
                            <input type="file" class="styled-file-input" name="document" required accept=".jpg,.jpeg,.png,.pdf">
                        </div>
                        <div class="col-md-6">
                            <label class="field-label">Upload Passport Photo</label>
                            <input type="file" class="styled-file-input" name="photo" accept=".jpg,.jpeg,.png">
                        </div>
                    </div>

                    <div class="pt-3">
                        <button type="submit" class="btn-submit-app" id="submitBtn">
                            <span>Submit Application</span>
                            <i class="bi bi-send-fill ms-2"></i>
                        </button>
                    </div>

                </form>

                <div class="py-3 px-4 bg-light text-center border-top">
                    <small class="text-secondary fw-semibold"><i class="bi bi-shield-check me-1 text-primary"></i> GD EDU TECH | Mangalore Campus</small>
                </div>
            </div>
        </div>
    </section>

    <!-- Floating Plan Tilted Cards CTA Section (Full-Width Edge-to-Edge) -->
    <section class="cta-plan-section py-0 my-0 w-100 position-relative overflow-hidden" data-aos="fade-up">
        <div class="cta-plan-banner position-relative text-center w-100 rounded-0 border-0">
            
            <svg class="cta-wavy-track track-left d-none d-lg-block" viewBox="0 0 220 130" fill="none" stroke="#7e858d" stroke-width="3" stroke-dasharray="6 6">
                <path d="M10,110 C70,20 140,120 210,20" />
            </svg>
            <svg class="cta-wavy-track track-right d-none d-lg-block" viewBox="0 0 220 130" fill="none" stroke="#7e858d" stroke-width="3" stroke-dasharray="6 6">
                <path d="M10,20 C80,120 150,20 210,110" />
            </svg>

            <div class="plan-card plan-card-left text-start d-none d-lg-block">
                <div class="plan-header mb-2">
                    <h6 class="plan-title fw-bold mb-1"><i class="bi bi-mortarboard-fill text-primary me-2"></i> Scholarship Pass</h6>
                    <p class="plan-subtext text-muted small mb-0">Financial aid program.</p>
                </div>
                <div class="plan-price mb-2">
                    <span class="price-num fw-bold fs-5 text-primary">Up to 100% Aid</span>
                </div>
                <ul class="plan-features list-unstyled small mb-3" style="font-size: 0.82rem;">
                    <li class="mb-1.5"><i class="bi bi-check2 me-1.5 text-dark fw-bold"></i> Merit-Based Aid</li>
                    <li class="mb-1.5"><i class="bi bi-check2 me-1.5 text-dark fw-bold"></i> Free Course Material</li>
                    <li class="mb-1.5"><i class="bi bi-check2 me-1.5 text-dark fw-bold"></i> Industry Mentorship</li>
                </ul>
                <a href="contact.php" class="btn btn-plan-dark w-100 text-center text-decoration-none">Contact Us</a>
            </div>

            <div class="cta-center-content mx-auto text-center px-3">
                <h2 class="cta-banner-heading fw-bold mb-3">
                    Shape your future with GD Edu Tech Scholarships
                </h2>
                <p class="cta-banner-subtext text-muted mb-4">
                    Our admission advisors are ready to guide you through course selection and scholarship eligibility.
                </p>
                <div class="d-flex flex-wrap gap-3 justify-content-center align-items-center">
                    <a href="contact.php" class="btn btn-cta-main-pill text-decoration-none">
                        <span>Get In Touch</span>
                        <i class="bi bi-arrow-right ms-2 fs-5"></i>
                    </a>
                    <a href="contact.php" class="btn btn-cta-secondary-outline text-decoration-none">
                        <span>Contact Us</span>
                        <i class="bi bi-envelope-fill ms-2"></i>
                    </a>
                </div>
            </div>

            <div class="plan-card plan-card-right text-start d-none d-lg-block">
                <div class="plan-header mb-2">
                    <h6 class="plan-title fw-bold mb-1"><i class="bi bi-stars text-warning me-2"></i> Skill Track</h6>
                    <p class="plan-subtext text-muted small mb-0">Practical hands-on training.</p>
                </div>
                <div class="plan-price mb-2">
                    <span class="price-num fw-bold fs-5 text-warning" style="color: #d97706 !important;">Certified</span>
                </div>
                <ul class="plan-features list-unstyled small mb-3" style="font-size: 0.82rem;">
                    <li class="mb-1.5"><i class="bi bi-diamond-fill me-1.5 text-dark small"></i> Live Projects</li>
                    <li class="mb-1.5"><i class="bi bi-diamond-fill me-1.5 text-dark small"></i> 100% Placement Support</li>
                    <li class="mb-1.5"><i class="bi bi-diamond-fill me-1.5 text-dark small"></i> QR Verified Badge</li>
                </ul>
                <a href="contact.php" class="btn btn-plan-dark w-100 text-center text-decoration-none">Contact Us</a>
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

        document.getElementById('fullForm').addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            btn.innerHTML = '<i class="bi bi-arrow-repeat spin-icon me-2"></i> Submitting Application...';
            btn.disabled = true;
        });
    </script>
</body>
</html>