<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once './Configurations/config.php';

// Fetch popular courses
$popular_courses_query = "
    SELECT c.*, 
           cat.name as category_name,
           (SELECT COUNT(*) FROM Enrollments e WHERE e.course_id = c.course_id) as student_count
    FROM Courses c
    LEFT JOIN Categories cat ON c.category_id = cat.category_id
    WHERE c.isPopular = '1'
    AND c.status = 'published'
    LIMIT 6";
$popular_courses = $conn->query($popular_courses_query)->fetch_all(MYSQLI_ASSOC);

// Fetch categories
$categories_query = "
    SELECT c.*, 
           (SELECT COUNT(*) FROM Courses WHERE category_id = c.category_id) as course_count
    FROM Categories c
    LIMIT 8";
$categories = $conn->query($categories_query)->fetch_all(MYSQLI_ASSOC);
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GD Edu Tech - Transform Your Future</title>
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
    <!-- Particles.js -->
    <!--<script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>-->

        <link rel="icon" type="image/png" href="./Images/Logos/GD_Only_logo.png">
    <link rel="icon" type="image/png" sizes="32x32" href="./Images/Logos/GD_Only_logo.png">
    <link rel="icon" type="image/png" sizes="16x16" href="./Images/Logos/GD_Only_logo.png">
    <link rel="shortcut icon" href="./Images/Logos/GD_Only_logo.png">
    <link rel="apple-touch-icon" href="./Images/Logos/GD_Only_logo.png">
    <meta name="msapplication-TileImage" content="./Images/Logos/GD_Only_logo.png">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="./css/index.css">
    <link rel="stylesheet" href="./css/hero/style.css">

    
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <style>
            /* Reset */
.nebula-shroud {
  display: flex;
  gap: 10px;
  /* flex-wrap: wrap; */
  justify-content: start;
  flex-direction:row;
}

/* The weirdly named card */
.frost-obelisk {
  width: 145px;
  height: 116px;
  border-radius: 20px;
  backdrop-filter: blur(15px);
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);

  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;

  color: white;
  text-align: center;
  transition: 0.3s ease;
}

/* Hover effect for the obelisk */
.frost-obelisk:hover {
  transform: scale(1.08);
  background: rgba(255, 255, 255, 0.15); /* Slightly brighter on hover */
}

.frost-obelisk h2 {
  font-size: 32px;
  font-weight: 700;
}

.frost-obelisk p {
  font-size: 14px;
  margin-top: 8px;
  opacity: 0.85;
}
.hero-flex{
    display: flex;
    gap: 9px;
}

.feedback-card{
    background: white;
    border-radius: 20px;
    padding: 20px;
    height: 100%;
    /* box-shadow: 0 10px 30px rgba(0,0,0,0.08); */
    transition: 0.3s ease;
}

.feedback-card:hover{
    transform: translateY(-8px);
}

.feedback-stars{
    color: #fbbf24;
    margin-bottom: 15px;
    font-size: 18px;
}

.feedback-text{
    color: #555;
    line-height: 1.8;
    margin-bottom: 25px;
    min-height: 19px;
}

.feedback-user{
    display: flex;
    align-items: center;
    gap: 15px;
}

.feedback-user img{
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
}

.feedback-user h5{
    margin: 0;
    font-size: 18px;
    font-weight: 600;
}

.feedback-user span{
    color: #777;
    font-size: 14px;
}

.modern-feedback-section{
    background: linear-gradient(to bottom, #f8f9fa, #dae6fd);
    overflow: hidden;
}

.feedback-subtitle{
    background: rgba(37,99,235,0.1);
    color: #2563eb;
    padding: 8px 18px;
    border-radius: 30px;
    font-weight: 600;
    font-size: 14px;
}

.feedback-heading{
    font-size: 42px;
    font-weight: 700;
    color: #111827;
}

.feedback-description{
    color: #6b7280;
    margin-top: 15px;
    font-size: 17px;
}

.modern-feedback-section{
 
    overflow: hidden;
}

.feedback-subtitle{
    background: rgba(37,99,235,0.1);
    color: #2563eb;
    padding: 8px 18px;
    border-radius: 30px;
    font-weight: 600;
    font-size: 14px;
}

.feedback-heading{
    font-size: 42px;
    font-weight: 700;
    color: #111827;
}

.feedback-description{
    color: #6b7280;
    margin-top: 15px;
    font-size: 17px;
}
/* --- Offline Courses Section Styling --- */

.offline-courses-section {
    padding: 60px 0;
    background-color: #f8f9fa; /* Light background for the section */
}

.offline-banner-card {
    background: #ffffff;
    border-radius: 24px;
    padding: 50px 30px;
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.04);
}

.highlight-gold {
    color: #f5a623; /* Gold color matching your highlight */
}

/* --- Course Card Styling --- */
.course-grid-card {
    background: #ffffff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    height: 100%;
    position: relative;
}

.course-grid-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 25px rgba(0, 0, 0, 0.12);
}

.course-card-link {
    text-decoration: none;
    color: inherit;
    display: flex;
    flex-direction: column;
    height: 100%;
}

/* Image Thumbnail & Badge */
.course-card-thumb {
    position: relative;
    width: 100%;
    height: 220px;
    overflow: hidden;
}

.thumb-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.course-grid-card:hover .thumb-img {
    transform: scale(1.05); /* Slight zoom on hover */
}

.category-pill-badge {
    position: absolute;
    top: 15px;
    left: 15px;
    background: rgba(0, 0, 0, 0.95); /* Gold */
    color: #fff;
    padding: 6px 14px;
    border-radius: 30px;
    font-size: 0.8rem;
    font-weight: 600;
    letter-spacing: 0.5px;
    z-index: 2;
}

/* Card Body */
.course-card-body {
    padding: 20px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

.course-card-title {
    font-size: 1rem;
    font-weight: 700;
    color: #2b2b2b;
    margin-bottom: 15px;
    line-height: 1.4;
}

.course-card-btn {
    margin-top: auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-weight: 600;
    color: #040302;
    transition: color 0.3s;
}

.course-card-btn i {
    transition: transform 0.3s;
}

.course-grid-card:hover .course-card-btn i {
    transform: translateX(5px); /* Arrow moves right on hover */
}

/* --- Swiper Customization --- */
.offline-courses-swiper {
    padding-bottom: 60px; /* Space for pagination bullets */
    padding-top: 10px;
}

/* Navigation Arrows */
.offline-swiper-prev,
.offline-swiper-next {
    color: #0a0908;
    background: #fff;
    width: 45px;
    height: 45px;
    border-radius: 50%;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    top: 45%;
}

.offline-swiper-prev::after,
.offline-swiper-next::after {
    font-size: 1.2rem;
    font-weight: bold;
}

/* Pagination Bullets */
.offline-swiper-pagination .swiper-pagination-bullet {
    width: 10px;
    height: 10px;
    background: #d3d3d3;
    opacity: 1;
    transition: all 0.3s ease;
}

.offline-swiper-pagination .swiper-pagination-bullet-active {
    background: #fffefb;
    width: 24px;
    border-radius: 5px;
}

/* PREMIUM CARD */
.modern-feedback-card{
    background: rgba(255,255,255,0.92);
    border-radius: 24px;
    padding: 22px;
    height: 320px;

    display: flex;
    flex-direction: column;
    justify-content: space-between;

    border: 1px solid rgba(255,255,255,0.5);

   

    backdrop-filter: blur(12px);

    transition: all 0.35s ease;
    overflow: hidden;
    position: relative;
}

.modern-feedback-card::before{
    content: "";
    position: absolute;
    inset: 0;
    border-radius: 24px;
    padding: 1px;
   
    

}

.modern-feedback-card:hover{
    transform: translateY(-8px);
}

/* TOP */
.feedback-top-area{
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 14px;
}

.feedback-profile{
    display: flex;
    align-items: center;
    gap: 12px;
}

.feedback-avatar{
    width: 38px;
    height: 38px;
    border-radius: 50%;
    object-fit: cover;
    
}

.student-name{
    margin: 0;
    font-size: 16px;
    font-weight: 700;
    color: #111827;
}

.college-name{
    margin: 2px 0 0;
    font-size: 13px;
    color: #6b7280;
}

/* QUOTE ICON */
.quote-icon{
    width: 42px;
    height: 42px;
    border-radius: 14px;
    background: #eff6ff;
    color: #2563eb;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 18px;
}

/* STARS */
.modern-stars{
    color: #fbbf24;
    margin-bottom: 12px;
    display: flex;
    gap: 3px;
    font-size: 14px;
}

/* FEEDBACK TEXT */
.modern-feedback-text{
    color: #4b5563;
    line-height: 1.7;
    font-size: 14px;

    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;

    min-height: 92px;
}

/* COURSE TAG */
.feedback-course-tag{
    margin-top: 14px;

    display: inline-flex;
    align-items: center;
    gap: 8px;

    background: #eff6ff;
    color: #2563eb;

    padding: 8px 14px;
    border-radius: 30px;

    font-weight: 600;
    font-size: 12px;

    width: fit-content;
}

/* MOBILE */
@media(max-width:768px){

    .feedback-heading{
        font-size: 30px;
    }

    .modern-feedback-card{
        height: 300px;
        padding: 18px;
    }

    .modern-feedback-text{
        -webkit-line-clamp: 3;
        min-height: 70px;
    }

}


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
                    background:rgba(233, 235, 236, 0.7) !important;
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
    font-weight: 700;
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
.certification-badges{
    display:flex;

    gap:12px;
    margin-top:20px;
}
.certification-badges{
    display:flex;
    gap:18px;
    margin-top:20px;
    align-items:center;
}

.cert-badge-item{
    width:80px;
    height:80px;
    border-radius:50%;
    /* background:rgba(255,255,255,0.08); */
    /* backdrop-filter:blur(12px); */
    /* border:1px solid rgba(255,255,255,0.15); */
    display:flex;
    align-items:center;
    justify-content:center;
    transition:0.3s ease;
    overflow:hidden;
}



.cert-badge-image{
    width:100px;
    height:100px;
    object-fit:contain;
}
    </style>
</head>

<body>
    <!-- Navigation -->
     <?php include './navbar.php'; ?>


<!-- Hero Section -->
<section class="hero-section" id="home">
    <div class="hero-cont">
        <!-- Main Rounded Banner Card -->
        <div class="hero-banner-card" data-aos="fade-up">
            <!-- Background Decorative Vector Patterns -->
            <div class="banner-bg-accents">
                <div class="accents-dots"></div>
                <div class="accents-arcs">
                    <svg viewBox="0 0 400 400" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="200" cy="200" r="80" stroke="rgba(255,255,255,0.12)" stroke-width="2"/>
                        <circle cx="200" cy="200" r="130" stroke="rgba(255,255,255,0.1)" stroke-width="2"/>
                        <circle cx="200" cy="200" r="180" stroke="rgba(255,255,255,0.08)" stroke-width="2"/>
                    </svg>
                </div>
            </div>

            <!-- Left Slider Arrow
            <button class="banner-arrow banner-arrow-prev" aria-label="Previous Slide">
                <i class="bi bi-chevron-left"></i>
            </button> -->

            <!-- Banner Grid Layout -->
            <div class="hero-banner-grid">
                <!-- Left Promo Content -->
                <div class="hero-promo-content">
                    <div class="promo-badge">
                        <i class="bi bi-fire text-warning me-1"></i> LIMITED TIME OFFER
                    </div>
                    
                  <h1 class="hero-promo-title">
    Get <span class="highlight-gold">₹8,000 Off</span> on <br>Selected Courses!
</h1>
                    
                    <p class="hero-promo-sub">
                        Upgrade your skills with industry-focused programs and advance your career.
                    </p>
                    
                    <div class="offer-date-info">
                        <i class="bi bi-calendar-event me-2"></i> Offer Valid Till <strong>05th September, 2026</strong>
                    </div>
                    
                    <div class="hero-promo-actions">
                        <a href="courses.php" class="btn-explore-offer">
                            Explore Offer <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    </div>
                    
                    <!-- Carousel Pagination Dots
                    <div class="banner-pagination-dots">
                        <span class="dot active"></span>
                        <span class="dot"></span>
                        <span class="dot"></span>
                        <span class="dot"></span>
                    </div> -->
                </div>

                <!-- Center Person Image -->
                <div class="hero-person-wrapper">
                    <img src="./assets/images/hi3.png" alt="Student" class="hero-person-img">
                </div>

                <!-- Right Certified White Card -->
                <div class="hero-certified-wrapper">
                    <div class="hero-certified-card">
                        <!-- Top-Right Ribbon Accent -->
                        <div class="card-corner-ribbon"></div>

                        <div class="certified-card-header">
                            <div class="certified-badge-icon">
                                <i class="bi bi-patch-check-fill"></i>
                            </div>
                            <div class="certified-header-text">
                                <h3>Get Certified</h3>
                                <span>Industry Recognized</span>
                            </div>
                        </div>
                        
                        <hr class="certified-divider">
                        
                        <p class="certified-card-desc">
                            Join thousands of students in our world-class online programs and develop the skills needed for in-demand careers.
                        </p>
                        
                        <div class="certified-card-badges">
                            <div class="cert-badge-box">
                                <img src="./Images/Others/badge.png" alt="ISO Certified" class="cert-badge-img">
                            </div>
                            <div class="cert-badge-box">
                                <img src="./Images/Others/badge1.png" alt="Industry Approved" class="cert-badge-img">
                            </div>
                        </div>

                        <!-- Bottom Ribbon Accent -->
                        <div class="card-bottom-ribbon"></div>
                    </div>
                </div>
            </div>

            <!-- Right Slider Arrow
            <button class="banner-arrow banner-arrow-next" aria-label="Next Slide">
                <i class="bi bi-chevron-right"></i>
            </button> -->
        </div>

        <!-- Bottom Stats Row -->
        <div class="hero-stats-row" data-aos="fade-up" data-aos-delay="150">
            <div class="stat-card-white">
                <div class="stat-icon-circle icon-blue">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="stat-content">
                    <h2>10+</h2>
                    <p>Expert Instructors</p>
                </div>
            </div>

            <div class="stat-card-white">
                <div class="stat-icon-circle icon-pink">
                    <i class="bi bi-book-half"></i>
                </div>
                <div class="stat-content">
                    <h2>8+</h2>
                    <p>Quality Courses</p>
                </div>
            </div>

            <div class="stat-card-white">
                <div class="stat-icon-circle icon-green">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
                <div class="stat-content">
                    <h2>5K+</h2>
                    <p>Active Students</p>
                </div>
            </div>
            <div class="stat-card-noborder">
                <!-- Two Action Buttons Row Wise -->
                <div class="hero-actions-row" data-aos="fade-up" data-aos-delay="200">
                    <a href="courses.php" class="btn-action-primary">
                        Explore Courses <i class="bi bi-arrow-right ms-2"></i>
                    </a>
                    <a href="scholarship.php" class="btn-action-secondary">
                        Apply Scholarship <i class="bi bi-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>

       
    </div>
</section>

    <!-- Features Section -->
    <section class="features-section" id="features">
        <div class="container hero-container">
            <!-- Section Header -->
            <div class="section-header text-center mb-5" data-aos="fade-up">
                <div class="promo-badge mb-3">
                    <i class="bi bi-shield-check text-warning me-1"></i> WHY CHOOSE US
                </div>
                <h2 class="section-title-hero">
                    Features That <span class="highlight-gold">Set Us Apart</span>
                </h2>
                <p class="section-lead-hero">
                    Discover the features that make our learning platform unique, effective, and career-focused.
                </p>
            </div>
            
            <!-- Features Grid Layout -->
            <div class="features-grid-v2">
                <!-- Feature 1 -->
                <div class="feature-card-v2" data-aos="fade-up" data-aos-delay="100">
                    <div class="card-step-number">01</div>
                    <div class="feature-icon-box icon-bg-blue">
                        <i class="bi bi-laptop-fill"></i>
                    </div>
                    <h3>Online Learning</h3>
                    <p>Access our courses anytime, anywhere with our flexible online learning platform. Learn at your own pace with 24/7 access to materials.</p>
                    <div class="card-hover-border"></div>
                </div>

                <!-- Feature 2 -->
                <div class="feature-card-v2" data-aos="fade-up" data-aos-delay="200">
                    <div class="card-step-number">02</div>
                    <div class="feature-icon-box icon-bg-pink">
                        <i class="bi bi-person-video3"></i>
                    </div>
                    <h3>Expert Instructors</h3>
                    <p>Learn from industry professionals with years of practical experience. Get personalized guidance throughout your learning journey.</p>
                    <div class="card-hover-border"></div>
                </div>

                <!-- Feature 3 -->
                <div class="feature-card-v2" data-aos="fade-up" data-aos-delay="300">
                    <div class="card-step-number">03</div>
                    <div class="feature-icon-box icon-bg-green">
                        <i class="bi bi-award-fill"></i>
                    </div>
                    <h3>Certifications</h3>
                    <p>Earn industry-recognized certifications upon course completion. Boost your resume with credentials that matter to top employers.</p>
                    <div class="card-hover-border"></div>
                </div>

                <!-- Feature 4 -->
                <div class="feature-card-v2" data-aos="fade-up" data-aos-delay="100">
                    <div class="card-step-number">04</div>
                    <div class="feature-icon-box icon-bg-amber">
                        <i class="bi bi-briefcase-fill"></i>
                    </div>
                    <h3>Internship Programs</h3>
                    <p>Gain real-world experience with curated internship opportunities. Bridge the gap between academic theory and professional practice.</p>
                    <div class="card-hover-border"></div>
                </div>

                <!-- Feature 5 -->
                <div class="feature-card-v2" data-aos="fade-up" data-aos-delay="200">
                    <div class="card-step-number">05</div>
                    <div class="feature-icon-box icon-bg-purple">
                        <i class="bi bi-compass-fill"></i>
                    </div>
                    <h3>Career Guidance</h3>
                    <p>Receive personalized mentorship and roadmap planning from experts. We help you navigate your unique professional journey.</p>
                    <div class="card-hover-border"></div>
                </div>

                <!-- Feature 6 -->
                <div class="feature-card-v2" data-aos="fade-up" data-aos-delay="300">
                    <div class="card-step-number">06</div>
                    <div class="feature-icon-box icon-bg-cyan">
                        <i class="bi bi-person-check-fill"></i>
                    </div>
                    <h3>Placement Assistance</h3>
                    <p>Get priority access to our hiring partner network. From resume building to mock interviews, we help you land your dream job.</p>
                    <div class="card-hover-border"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Offline Courses Section (Proper Swiper Slider inside Rounded Banner Card) -->
    <section class="offline-courses-section" id="offline-courses">
        <div class="container hero-container">
            <div class="offline-banner-card">
                <!-- Section Header -->
                <div class="section-header text-center mb-5" data-aos="fade-up">
                    <div class="promo-badge mb-3">
                        <i class="bi bi-mortarboard-fill text-warning me-1"></i> CLASSROOM TRAINING
                    </div>
                    <h2 class="section-title-hero">
                        Offline <span class="highlight-gold">Courses</span>
                    </h2>
                    <p class="section-lead-hero">
                        Join our top-rated in-person classroom training programs with expert hands-on mentorship.
                    </p>
                </div>

                <!-- Swiper Slider Container -->
                <div class="swiper offline-courses-swiper" data-aos="fade-up" data-aos-delay="100">
                    <div class="swiper-wrapper">

                        <!-- Course 1 -->
                        <div class="swiper-slide">
                            <div class="course-grid-card">
                                <a href="courses.php" class="course-card-link">
                                    <div class="card-corner-ribbon"></div>
                                    <div class="course-card-thumb">
                                        <img src="./Images/Others/web.jpg" alt="Full Stack Development" class="thumb-img">
                                        <span class="category-pill-badge">Full Stack</span>
                                    </div>
                                    <div class="course-card-body">
                                        <h3 class="course-card-title">Full Stack Development</h3>
                                        <div class="course-card-btn">
                                            <span>View Details</span>
                                            <i class="bi bi-arrow-right"></i>
                                        </div>
                                    </div>
                                    <div class="card-bottom-ribbon"></div>
                                </a>
                            </div>
                        </div>

                        <!-- Course 2 -->
                        <div class="swiper-slide">
                            <div class="course-grid-card">
                                <a href="courses.php" class="course-card-link">
                                    <div class="card-corner-ribbon"></div>
                                    <div class="course-card-thumb">
                                        <img src="./Images/Others/architecture.jpg" alt="Architectural Design Course" class="thumb-img">
                                        <span class="category-pill-badge">Architecture</span>
                                    </div>
                                    <div class="course-card-body">
                                        <h3 class="course-card-title">Architectural Design Course</h3>
                                        <div class="course-card-btn">
                                            <span>View Details</span>
                                            <i class="bi bi-arrow-right"></i>
                                        </div>
                                    </div>
                                    <div class="card-bottom-ribbon"></div>
                                </a>
                            </div>
                        </div>

                        <!-- Course 3 -->
                        <div class="swiper-slide">
                            <div class="course-grid-card">
                                <a href="courses.php" class="course-card-link">
                                    <div class="card-corner-ribbon"></div>
                                    <div class="course-card-thumb">
                                        <img src="./Images/Others/int.jpg" alt="Interior Design Course" class="thumb-img">
                                        <span class="category-pill-badge">Interior Design</span>
                                    </div>
                                    <div class="course-card-body">
                                        <h3 class="course-card-title">Interior Design Course</h3>
                                        <div class="course-card-btn">
                                            <span>View Details</span>
                                            <i class="bi bi-arrow-right"></i>
                                        </div>
                                    </div>
                                    <div class="card-bottom-ribbon"></div>
                                </a>
                            </div>
                        </div>

                        <!-- Course 4 -->
                        <div class="swiper-slide">
                            <div class="course-grid-card">
                                <a href="courses.php" class="course-card-link">
                                    <div class="card-corner-ribbon"></div>
                                    <div class="course-card-thumb">
                                        <img src="./Images/Others/market.jpg" alt="Digital Marketing" class="thumb-img">
                                        <span class="category-pill-badge">Digital Marketing</span>
                                    </div>
                                    <div class="course-card-body">
                                        <h3 class="course-card-title">Digital Marketing</h3>
                                        <div class="course-card-btn">
                                            <span>View Details</span>
                                            <i class="bi bi-arrow-right"></i>
                                        </div>
                                    </div>
                                    <div class="card-bottom-ribbon"></div>
                                </a>
                            </div>
                        </div>

                        <!-- Course 5 -->
                        <div class="swiper-slide">
                            <div class="course-grid-card">
                                <a href="courses.php" class="course-card-link">
                                    <div class="card-corner-ribbon"></div>
                                    <div class="course-card-thumb">
                                        <img src="./Images/Others/designer.jpg" alt="Graphic Design & Video Editing" class="thumb-img">
                                        <span class="category-pill-badge">Graphic Design</span>
                                    </div>
                                    <div class="course-card-body">
                                        <h3 class="course-card-title">Graphic Design & Video Editing</h3>
                                        <div class="course-card-btn">
                                            <span>View Details</span>
                                            <i class="bi bi-arrow-right"></i>
                                        </div>
                                    </div>
                                    <div class="card-bottom-ribbon"></div>
                                </a>
                            </div>
                        </div>

                        <!-- Course 6 -->
                        <div class="swiper-slide">
                            <div class="course-grid-card">
                                <a href="courses.php" class="course-card-link">
                                    <div class="card-corner-ribbon"></div>
                                    <div class="course-card-thumb">
                                        <img src="./Images/Others/designer1.jpeg" onerror="this.src='./Images/Others/designer.jpg'" alt="Photography & Camera Handling" class="thumb-img">
                                        <span class="category-pill-badge">Photography</span>
                                    </div>
                                    <div class="course-card-body">
                                        <h3 class="course-card-title">Photography & Camera Handling</h3>
                                        <div class="course-card-btn">
                                            <span>View Details</span>
                                            <i class="bi bi-arrow-right"></i>
                                        </div>
                                    </div>
                                    <div class="card-bottom-ribbon"></div>
                                </a>
                            </div>
                        </div>

                    </div>

                    <!-- Swiper Navigation & Pagination -->
                    <div class="swiper-pagination offline-swiper-pagination"></div>
                    <div class="swiper-button-prev offline-swiper-prev"></div>
                    <div class="swiper-button-next offline-swiper-next"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Swiper Initialization Script (4 Grid Layout) -->
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Swiper !== 'undefined') {
            var offlineSwiper = new Swiper('.offline-courses-swiper', {
                slidesPerView: 1,
                spaceBetween: 20,
                loop: true,
                autoplay: {
                    delay: 3000,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true,
                },
                pagination: {
                    el: '.offline-swiper-pagination',
                    clickable: true,
                },
                navigation: {
                    nextEl: '.offline-swiper-next',
                    prevEl: '.offline-swiper-prev',
                },
                breakpoints: {
                    576: {
                        slidesPerView: 2,
                        spaceBetween: 20,
                    },
                    992: {
                        slidesPerView: 3,
                        spaceBetween: 22,
                    },
                    1200: {
                        slidesPerView: 4,
                        spaceBetween: 24,
                    }
                }
            });
        }
    });
    </script>

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


   


    <!-- 3D Auto-Sliding Floating Cards Gallery Section -->
    <section class="gallery-hero-section position-relative py-5 overflow-hidden" id="gallery">
        <div class="container text-center position-relative z-4">
            <div class="promo-badge mb-3 d-inline-flex align-items-center px-3 py-1 rounded-pill mobgall">
                <i class="bi bi-camera-fill text-warning me-2"></i> OUR COMMUNITY IN ACTION
            </div>
            
            <h2 class="small-team section-title-hero display-4 fw-bold text-center" id="smallTeam">
                <span class="word"><span>Vibrant</span></span>&nbsp;<span class="word"><span>Learning</span></span>&nbsp;<span class="word"><span><span class="highlight-gold">Journey</span></span></span>
            </h2>

            <div class="big-results-wrap">
                <div class="big-results" id="bigResults">
                    <span class="letter">g</span><span class="letter">a</span><span class="letter">l</span><span class="letter">l</span><span class="letter">e</span><span class="letter">r</span><span class="letter">y</span>
                </div>
            </div>

            <!-- Floating 3D Cards Track (Auto Sliding) -->
            <div class="cards-row-wrapper overflow-hidden position-relative w-100 ">
                <div class="cards-row d-flex align-items-center gap-4" id="cardsRow">
                    <?php
                    $images = glob('./Images/gallery/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
                    $rotations = [-7, 5, -4, 6, -3, 7, -5, 4];
                    $depths = [12, 8, 14, 10, 6, 11, 9, 13];
                    if (!empty($images)):
                        // Render images twice for seamless infinite auto sliding loop
                        $all_images = array_merge($images, $images);
                        foreach ($all_images as $index => $image): 
                            $rot = $rotations[$index % count($rotations)];
                            $depth = $depths[$index % count($depths)];
                            $cardNum = ($index % count($images)) + 1;
                            ?>
                            <div class="card card-item" data-rot="<?php echo $rot; ?>" data-depth="<?php echo $depth; ?>">
                                <img src="<?php echo $image; ?>" alt="Gallery Image <?php echo $cardNum; ?>" />
                            </div>
                        <?php endforeach; 
                    endif; ?>
                </div>
            </div>

        
    </section>
    <!-- Redesigned Full-Width CTA Section (Edge-to-Edge Sharp Fit) -->
    <section class="cta-section py-0 my-0 w-100" id="testimonials" data-aos="fade-up">
        <div class="cta-banner position-relative overflow-hidden p-4 p-md-5 rounded-0 shadow-none w-100">
            <!-- Background Decorative Glow & Pattern -->
            <div class="cta-glow cta-glow-1"></div>
            <div class="cta-glow cta-glow-2"></div>
            <div class="cta-pattern"></div>

            <div class="container position-relative z-2 py-3">
                <div class="row align-items-center g-4">
                    <div class="col-lg-8 text-center text-lg-start">
                        <div class="cta-badge d-inline-flex align-items-center px-3 py-1 rounded-pill mb-3">
                            <i class="bi bi-stars me-2 text-warning"></i>
                            <span>Unlock Your Potential</span>
                        </div>
                        <h2 class="cta-heading text-white fw-bold display-6 mb-3">
                            Ready to Start Your <span class="cta-highlight">Learning Journey?</span>
                        </h2>
                        <p class="cta-subtext text-white-50 lead mb-0">
                            Join thousands of successful students who have transformed their careers with our expert-led courses and hands-on projects.
                        </p>
                    </div>
                    <div class="col-lg-4 text-center text-lg-end">
                        <div class="d-flex flex-column flex-sm-row justify-content-center justify-content-lg-end gap-3 align-items-center">
                            <a href="./studentPanel/signup.php" class="btn btn-cta-primary btn-lg rounded-pill px-4 py-3 fw-semibold">
                                <span>Get Started Now</span>
                                <i class="bi bi-arrow-right-circle-fill ms-2 fs-5"></i>
                            </a>
                        </div>
                        <div class="cta-trust-badge mt-3 d-flex align-items-center justify-content-center justify-content-lg-end gap-2 text-white-50 small">
                            <i class="bi bi-shield-check text-white fs-6"></i>
                            <span>100% Satisfaction Guaranteed</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
<!-- Modern Student Feedback Section -->

<?php

$feedback_query = "
SELECT *
FROM student_feedback
WHERE status='approved'
ORDER BY feedback_id DESC
LIMIT 10
";

$feedbacks = $conn->query($feedback_query);

?>

<section class="modern-feedback-section py-5 position-relative overflow-hidden" id="student-feedback" data-aos="fade-up" data-aos-delay="100">
    <div class="container py-4 position-relative z-2">
        <!-- Heading -->
        <div class="row justify-content-center text-center mb-5">
            <div class="col-lg-7">
                <div class="promo-badge mb-3 d-inline-flex align-items-center px-3 py-1 rounded-pill">
                <i class="bi bi-mortarboard-fill text-warning me-2"></i> STUDENT FEEDBACK
            </div>
                <h2 class="feedback-heading mt-2" data-aos="fade-up" data-aos-delay="200">
                    What Our <span style="color:#0079a8;">Students Say</span>
                </h2>
                <p class="feedback-description lead text-muted mt-2" data-aos="fade-up" data-aos-delay="300">
                    Real experiences and success stories from students who transformed their careers with GD Edu Tech.
                </p>
            </div>
        </div>

        <!-- Swiper -->
        <div class="swiper modernFeedbackSwiper pb-5" data-aos="fade-up" data-aos-delay="400">
            <div class="swiper-wrapper">
                <?php while($row = $feedbacks->fetch_assoc()): ?>
                    <div class="swiper-slide h-auto">
                        <div class="modern-feedback-card h-100 p-4 rounded-4 shadow-sm bg-white border d-flex flex-column justify-content-between position-relative overflow-hidden">
                            <div class="card-glow-accent"></div>
                            
                            <div>
                                <!-- Top Area -->
                                <div class="feedback-top-area d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom border-light">
                                    <div class="feedback-profile d-flex align-items-center gap-3">
                                        <?php if(!empty($row['student_image'])): ?>
                                            <img src="./uploads/feedback/<?php echo $row['student_image']; ?>" class="feedback-avatar rounded-circle shadow-sm" alt="student">
                                        <?php else: ?>
                                            <img src="./Images/default-user.png" class="feedback-avatar rounded-circle shadow-sm" alt="student">
                                        <?php endif; ?>
                                        <div>
                                            <h5 class="student-name fw-bold mb-0 text-dark">
                                                <?php echo htmlspecialchars($row['student_name']); ?>
                                            </h5>
                                            <p class="college-name text-muted small mb-0">
                                                <i class="bi bi-mortarboard me-1 text-primary"></i>
                                                <?php echo htmlspecialchars($row['college_name']); ?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="quote-icon bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-quote fs-4"></i>
                                    </div>
                                </div>

                                <!-- Stars -->
                                <div class="modern-stars text-warning mb-3 d-flex gap-1 fs-6">
                                    <?php
                                    for($i = 1; $i <= $row['rating']; $i++){
                                        echo '<i class="bi bi-star-fill"></i>';
                                    }
                                    ?>
                                </div>

                                <!-- Feedback Text -->
                                <p class="modern-feedback-text text-secondary mb-4 fs-6 fst-italic">
                                    "<?php echo htmlspecialchars($row['feedback']); ?>"
                                </p>
                            </div>

                            <!-- Course Tag -->
                            <div class="feedback-course-tag bg-light text-primary rounded-pill px-3 py-2 small fw-semibold d-inline-flex align-items-center gap-2 border">
                                <i class="bi bi-book-half text-primary"></i>
                                <span><?php echo htmlspecialchars($row['course_name']); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            
            <!-- Controls -->
            <div class="swiper-pagination mt-4"></div>
            <!-- <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div> -->
        </div>
    </div>
</section>
    <?php include 'footer.php'; ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        VANTA.TOPOLOGY({
            el: "#herosection",
            mouseControls: false,
            touchControls: false,
            gyroControls: false,
            minHeight: 200.00,
            minWidth: 200.00,
            scale: 1.00,
            scaleMobile: 1.00,
            color: 0xafafaf,
            backgroundAlpha: 0.00,
            /* Stops the internal clock immediately */
            speed: 0.00 
        });

        setTimeout(() => {
            effect.setOptions({
                speed: 0.00
            });
        }, 1500); 

    });
</script>

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
  <script src="https://cdnjs.cloudflare.com/ajax/libs/p5.js/1.4.0/p5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vanta@latest/dist/vanta.topology.min.js"></script>

    

 

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

        // Enhanced Mobile Menu Behavior
        document.addEventListener('DOMContentLoaded', function() {
            const navbarToggler = document.querySelector('.navbar-toggler');
            const navbarCollapse = document.querySelector('.navbar-collapse');
            const navLinks = document.querySelectorAll('.nav-link');
            
            // Close menu when clicking outside
            document.addEventListener('click', function(event) {
                const isClickInside = navbarCollapse.contains(event.target) || navbarToggler.contains(event.target);
                if (!isClickInside && navbarCollapse.classList.contains('show')) {
                    navbarToggler.click();
                }
            });

            // Close menu when clicking on a link
            navLinks.forEach(link => {
                link.addEventListener('click', () => {
                    if (navbarCollapse.classList.contains('show')) {
                        navbarToggler.click();
                    }
                });
            });

            // Enhanced scroll behavior
            let lastScrollTop = 0;
            const navbar = document.querySelector('.navbar');
            
            window.addEventListener('scroll', function() {
                let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                
                // Add/remove scrolled class
                if (scrollTop > 50) {
                    navbar.classList.add('scrolled');
                    navbar.classList.remove('navbar-dark');
                    navbar.classList.add('navbar-light');
                } else {
                    navbar.classList.remove('scrolled');
                    navbar.classList.remove('navbar-light');
                    navbar.classList.add('navbar-dark');
                }

                // Hide/show navbar on scroll
                if (scrollTop > lastScrollTop && scrollTop > 100) {
                    // Scrolling down & not at the top
                    navbar.style.transform = 'translateY(-100%)';
                } else {
                    // Scrolling up or at the top
                    navbar.style.transform = 'translateY(0)';
                }
                
                lastScrollTop = scrollTop;
            });
        });

        // Initialize Swiper for Testimonials
        var testimonialSwiper = new Swiper(".testimonialSwiper", {
            slidesPerView: 1,
            spaceBetween: 20,
            loop: true,
            autoHeight: true,
            pagination: {
                el: ".testimonialSwiper .swiper-pagination",
                clickable: true,
            },
            navigation: {
                nextEl: ".slider-next",
                prevEl: ".slider-prev",
            },
            breakpoints: {
                576: {
                    slidesPerView: 1,
                    spaceBetween: 20,
                },
                768: {
                    slidesPerView: 2,
                    spaceBetween: 30,
                },
                1024: {
                    slidesPerView: 3,
                    spaceBetween: 40,
                },
            },
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
        });
        
        // Initialize Swiper for Gallery (3D Circular / Coverflow Slider)
        var gallerySwiper = new Swiper(".gallerySwiper", {
            effect: "coverflow",
            grabCursor: true,
            centeredSlides: true,
            slidesPerView: "auto",
            loop: true,
            coverflowEffect: {
                rotate: 35,
                stretch: 0,
                depth: 200,
                modifier: 1,
                slideShadows: false,
            },
            navigation: {
                nextEl: ".gallerySwiper .swiper-button-next",
                prevEl: ".gallerySwiper .swiper-button-prev",
            },
            pagination: {
                el: ".gallerySwiper .swiper-pagination",
                clickable: true,
                dynamicBullets: true,
            },
            autoplay: {
                delay: 3500,
                disableOnInteraction: false,
            },
        });
        
        // Counter Animation
        const counterUp = window.counterUp = (el, options = {}) => {
            const {
                duration = 1000,
                delay = 16,
            } = options;
            
            if (typeof el === 'string') {
                el = document.querySelector(el);
            }
            
            const start = el.innerText.replace(/,/g, '');
            const countTo = parseInt(el.getAttribute('data-count').replace(/,/g, ''));
            const inc = countTo / (duration / delay);
            let current = start;
            
            const counter = setInterval(() => {
                current = Math.ceil(current + inc);
                el.innerText = current.toLocaleString();
                
                if (parseInt(current) >= countTo) {
                    clearInterval(counter);
                    el.innerText = countTo.toLocaleString();
                }
            }, delay);
        };
        
        // Initialize counters when in viewport
        const counterElements = document.querySelectorAll('.counter');
        const observerOptions = {
            threshold: 0.2
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    counterUp(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);
        
        counterElements.forEach(el => {
            observer.observe(el);
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
        // Feedback Swiper Initialization
        var feedbackSwiper = new Swiper(".modernFeedbackSwiper", {
            slidesPerView: 1,
            spaceBetween: 25,
            loop: true,
            grabCursor: true,
            autoplay: {
                delay: 4000,
                disableOnInteraction: false,
            },
            navigation: {
                nextEl: ".modernFeedbackSwiper .swiper-button-next",
                prevEl: ".modernFeedbackSwiper .swiper-button-prev",
            },
            pagination: {
                el: ".modernFeedbackSwiper .swiper-pagination",
                clickable: true,
                dynamicBullets: true,
            },
            breakpoints: {
                576: {
                    slidesPerView: 1,
                    spaceBetween: 20,
                },
                768: {
                    slidesPerView: 2,
                    spaceBetween: 25,
                },
                1100: {
                    slidesPerView: 3,
                    spaceBetween: 30,
                },
            },
        });
    </script>

    <!-- GSAP & ScrollTrigger Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        if (typeof gsap === "undefined") return;
        gsap.registerPlugin(ScrollTrigger);

        // Initial states
        gsap.set(".small-team .word > span", { y: "105%" });
        gsap.set(".big-results .letter", { y: 80, opacity: 0 });
        gsap.set("#subline", { opacity: 0, y: 20 });

        // Apply rest rotation
        document.querySelectorAll(".cards-row .card").forEach((card) => {
            const rot = parseFloat(card.dataset.rot) || 0;
            card.dataset.restRot = rot;
            gsap.set(card, { y: -600, rotation: rot + 25, opacity: 0, scale: 0.7 });
        });

        // Intro timeline
        const intro = gsap.timeline({ defaults: { ease: "power3.out" } });
        intro
            .to(".small-team .word > span", { y: "0%", duration: 0.9, stagger: 0.08 }, 0.2)
            .to(".big-results .letter", { y: 0, opacity: 1, duration: 0.9, stagger: 0.05, ease: "back.out(1.6)" }, 0.4)
            .to(".cards-row .card", {
                y: 0,
                opacity: 1,
                scale: 1,
                rotation: (i, el) => parseFloat(el.dataset.restRot) || 0,
                duration: 1.1,
                stagger: { each: 0.08, from: "center" },
                ease: "back.out(1.4)"
            }, 0.6)
            .to("#subline", { opacity: 1, y: 0, duration: 0.8 }, 1.3);

        // Continuous 100% Infinite Auto Sliding Marquee Loop
        const cardsRow = document.querySelector("#cardsRow");
        if (cardsRow) {
            const autoSlide = gsap.to(cardsRow, {
                xPercent: -50,
                duration: 25,
                ease: "none",
                repeat: -1
            });

            // Pause auto sliding on hover so user can tilt & inspect photos
            cardsRow.addEventListener("mouseenter", () => autoSlide.pause());
            cardsRow.addEventListener("mouseleave", () => autoSlide.play());
        }

        // Continuous floating animation
        document.querySelectorAll(".cards-row .card").forEach((card, i) => {
            const rot = parseFloat(card.dataset.restRot) || 0;
            gsap.to(card, {
                y: `+=${8 + (i % 3) * 5}`,
                rotation: rot + (i % 2 === 0 ? 1.5 : -1.5),
                duration: 3 + (i % 4) * 0.5,
                delay: 1.5 + i * 0.1,
                ease: "sine.inOut",
                yoyo: true,
                repeat: -1
            });
        });

        // Mouse Parallax Effect
        const heroSec = document.querySelector(".gallery-hero-section");
        if (heroSec) {
            let mx = 0, my = 0, tx = 0, ty = 0;
            heroSec.addEventListener("mousemove", (e) => {
                const r = heroSec.getBoundingClientRect();
                mx = ((e.clientX - r.left) / r.width - 0.5) * 2;
                my = ((e.clientY - r.top) / r.height - 0.5) * 2;
            });
            heroSec.addEventListener("mouseleave", () => { mx = 0; my = 0; });

            function parallax() {
                tx += (mx - tx) * 0.05;
                ty += (my - ty) * 0.05;
                document.querySelectorAll(".cards-row .card").forEach((card) => {
                    const d = parseFloat(card.dataset.depth) || 8;
                    card.style.transform = `translate3d(${tx * d}px, ${ty * d * 0.5}px, 0px)`;
                });
                requestAnimationFrame(parallax);
            }
            parallax();
        }

        // Card Hover 3D Tilt
        document.querySelectorAll(".cards-row .card").forEach((card) => {
            card.addEventListener("mousemove", (e) => {
                const r = card.getBoundingClientRect();
                const px = (e.clientX - r.left) / r.width - 0.5;
                const py = (e.clientY - r.top) / r.height - 0.5;
                gsap.to(card, {
                    rotateX: -py * 16,
                    rotateY: px * 16,
                    scale: 1.15,
                    zIndex: 20,
                    duration: 0.4,
                    ease: "power2.out",
                    transformPerspective: 700,
                    overwrite: "auto"
                });
            });
            card.addEventListener("mouseleave", () => {
                gsap.to(card, {
                    rotateX: 0,
                    rotateY: 0,
                    scale: 1,
                    zIndex: card.style.zIndex || "",
                    duration: 0.8,
                    ease: "elastic.out(1, 0.6)",
                    overwrite: "auto"
                });
            });
        });
    });
    </script>
</body>

</html>