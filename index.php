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
    <div id="herosection"></div>
    <div class="hero-bg-glow"></div>
    
    <div class="container text-center">
        <div class="hero-wrapper">
            <div class="bg-stroke-text">Gdedutech</div>

            <div class="hero-main-layout">
                <div class="hero-left" data-aos="fade-right">
                    <h1 class="hero-title">Discover a <br> World of Learning <br> for Your Future</span></h1>
                   <div class="hero-flex">
                        <div class="hero-actions">
                        <a href="courses.php" class="btn-get-started">Explore Courses <i class="bi bi-arrow-right"></i></a>
                        
                    </div>
                    <div class="hero-actions">
                        <a href="scholarship.php" class="btn-get-started">Apply Scholarship <i class="bi bi-arrow-right"></i></a>
                        
                    </div>
                   </div>
                  <div class="nebula-shroud">
  <div class="frost-obelisk">
    <h2>10+</h2>
    <p>Expert Instructors</p>
  </div>

  <div class="frost-obelisk">
    <h2>6+</h2>
    <p>Quality Courses</p>
  </div>

  <div class="frost-obelisk">
    <h2>5K+</h2>
    <p>Active Students</p>
  </div>
</div>
                    
                </div>
                <div class="stats-container">
                    


                <div class="hero-center" data-aos="fade-up">
                    <img src="./assets/images/middlegirl.png" alt="Consultant" class="main-person">
                </div>

               <div class="hero-right" data-aos="fade-left">
    <div class="experience-badge-v2">
        <div class="exp-circle-v2">
      <div class="certificate-badge">
    <div class="badge-seal">
        <i class="bi bi-patch-check-fill"></i> 
    </div>
    <div class="badge-ribbon"></div>
</div>
        </div>
        <div class="exp-content-v2">
            <h3>Get Certified</h3>
            <p>Industry Recognized</p>
        </div>
        <span class="boomerang-path"></span>
    </div>

    <div class="description-box">
        <p class="hero-description">
            Join thousands of students in our world-class online programs and develop the skills needed for in-demand careers.
        </p>
      <div class="certification-badges">

    <div class="cert-badge-item">
        <img
            src="./Images/Others/badge.png"
            alt="ISO Certified"
            class="cert-badge-image"
        >
    </div>

    <div class="cert-badge-item">
        <img
            src="./Images/Others/badge1.png"
            alt="Industry Approved"
            class="cert-badge-image"
        >
    </div>

</div>

    </div>
</div>
            </div>
        </div>
    </div>
</section>

    <!-- Features Section -->
    <section class="features-section" id="features">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <span class="sub-heading text-accent-gradient">Why Choose Us</span>
                <h2 class="heading">Features That <span class="text-gradient">Set Us Apart</span></h2>
                <p class="lead">Discover the features that make our learning platform unique and effective</p>
            </div>
            
            <div class="features-grid">
                <!-- Feature 1 -->
                <div class="feature-card-wrapper" data-aos="fade-up" data-aos-delay="100">
                    <div class="premium-feature-card">
                        <div class="feature-content">
                            <div class="card-icon">
                                <i class="bi bi-laptop-fill"></i>
                            </div>
                            <h3>Online Learning</h3>
                            <p>Access our courses anytime, anywhere with our flexible online learning platform. Learn at your own pace with 24/7 access to course materials.</p>
                        </div>
                    </div>
                </div>

                <!-- Feature 2 -->
                <div class="feature-card-wrapper" data-aos="fade-up" data-aos-delay="200">
                    <div class="premium-feature-card">
                        <div class="feature-content">
                            <div class="card-icon">
                                <i class="bi bi-person-video3"></i>
                            </div>
                            <h3>Expert Instructors</h3>
                            <p>Learn from industry professionals with years of practical experience. Get personalized guidance and support throughout your learning journey.</p>
                        </div>
                    </div>
                </div>

                <!-- Feature 3 -->
                <div class="feature-card-wrapper" data-aos="fade-up" data-aos-delay="300">
                    <div class="premium-feature-card">
                        <div class="feature-content">
                            <div class="card-icon">
                                <i class="bi bi-award-fill"></i>
                            </div>
                            <h3>Certifications</h3>
                            <p>Earn industry-recognized certifications upon course completion. Boost your resume with credentials that matter to employers.</p>
                        </div>
                    </div>
                </div>
                       <div class="feature-card-wrapper" data-aos="fade-up" data-aos-delay="100">
    <div class="premium-feature-card">
        <div class="feature-content">
            <div class="card-icon">
                <i class="bi bi-briefcase-fill"></i>
            </div>
            <h3>Internship Programs</h3>
            <p>Gain real-world experience with our curated internship opportunities. Bridge the gap between academic theory and professional practice with hands-on projects.</p>
        </div>
    </div>
</div>

<div class="feature-card-wrapper" data-aos="fade-up" data-aos-delay="200">
    <div class="premium-feature-card">
        <div class="feature-content">
            <div class="card-icon">
                <i class="bi bi-compass-fill"></i>
            </div>
            <h3>Career Guidance</h3>
            <p>Receive personalized mentorship and roadmap planning from industry experts. We help you identify your strengths and navigate your unique professional journey.</p>
        </div>
    </div>
</div>

<div class="feature-card-wrapper" data-aos="fade-up" data-aos-delay="300">
    <div class="premium-feature-card">
        <div class="feature-content">
            <div class="card-icon">
                <i class="bi bi-person-check-fill"></i>
            </div>
            <h3>Placement Assistance</h3>
            <p>Get priority access to our partner network. From resume building to mock interviews, we provide the tools you need to land your dream job.</p>
        </div>
    </div>
</div>

            </div>
        </div>
    </section>

    <!-- Popular Courses -->
  <section class="py-5 bg-light">
    <div class="container">

        <div class="row mb-4">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="section-title">Offline Courses</h2>
                <p class="text-muted">
                    Join our in-person classroom training programs
                </p>
            </div>
        </div>

        <div class="row g-4">

            <!-- Course 1 -->
            <div class="col-lg-4 col-md-6">

                <a href="courses.php" class="text-decoration-none text-dark">

                    <div class="premium-card h-100 course-card">

                        <div class="position-relative">
                            <img
                                src="./Images/Others/web.jpg"
                                class="card-img-top"
                                style="height:200px; object-fit:cover;"
                            >

                            <span class="badge position-absolute top-0 end-0 m-3">
                                Full Stack Development
                            </span>
                        </div>

                        <div class="card-body p-4">
                            <h5 class="card-title mb-3">
                                Full Stack Development
                            </h5>

                            <p class="text-muted">
                               View Details
                            </p>
                        </div>

                    </div>

                </a>

            </div>

            <!-- Course 2 -->
            <div class="col-lg-4 col-md-6">

                <a href="courses.php" class="text-decoration-none text-dark">

                    <div class="premium-card h-100 course-card">

                        <div class="position-relative">
                            <img
                                src="./Images/Others/architecture.jpg"
                                class="card-img-top"
                                style="height:200px; object-fit:cover;"
                            >

                            <span class="badge position-absolute top-0 end-0 m-3">
                                Architecture
                            </span>
                        </div>

                        <div class="card-body p-4">
                            <h5 class="card-title mb-3">
                                Architectural Design Course
                            </h5>

                            <p class="text-muted">
                               View Details
                            </p>
                        </div>

                    </div>

                </a>

            </div>

            <!-- Course 3 -->
            <div class="col-lg-4 col-md-6">

                <a href="courses.php" class="text-decoration-none text-dark">

                    <div class="premium-card h-100 course-card">

                        <div class="position-relative">
                            <img
                                src="./Images/Others/int.jpg"
                                class="card-img-top"
                                style="height:200px; object-fit:cover;"
                            >

                            <span class="badge position-absolute top-0 end-0 m-3">
                                Interior Design
                            </span>
                        </div>

                        <div class="card-body p-4">
                            <h5 class="card-title mb-3">
                                Interior Design Course
                            </h5>

                            <p class="text-muted">
                               View Details
                            </p>
                        </div>

                    </div>

                </a>

            </div>

            <!-- Course 4 -->
            <div class="col-lg-4 col-md-6">

                <a href="courses.php" class="text-decoration-none text-dark">

                    <div class="premium-card h-100 course-card">

                        <div class="position-relative">
                            <img
                                src="./Images/Others/market.jpg"
                                class="card-img-top"
                                style="height:200px; object-fit:cover;"
                            >

                            <span class="badge position-absolute top-0 end-0 m-3">
                                Digital Marketing
                            </span>
                        </div>

                        <div class="card-body p-4">
                            <h5 class="card-title mb-3">
                                Digital Marketing
                            </h5>

                            <p class="text-muted">
                               View Details
                            </p>
                        </div>

                    </div>

                </a>

            </div>

            <!-- Course 5 -->
            <div class="col-lg-4 col-md-6">

                <a href="courses.php" class="text-decoration-none text-dark">

                    <div class="premium-card h-100 course-card">

                        <div class="position-relative">
                            <img
                                src="./Images/Others/designer.jpg"
                                class="card-img-top"
                                style="height:200px; object-fit:cover;"
                            >

                            <span class="badge position-absolute top-0 end-0 m-3">
                                Graphic Design
                            </span>
                        </div>

                        <div class="card-body p-4">
                            <h5 class="card-title mb-3">
                                Graphic Design & Video Editing
                            </h5>

                            <p class="text-muted">
                               View Details
                            </p>
                        </div>

                    </div>

                </a>

            </div>
                     <!-- Course 6 -->
            <div class="col-lg-4 col-md-6">

                <a href="courses.php" class="text-decoration-none text-dark">

                    <div class="premium-card h-100 course-card">

                        <div class="position-relative">
                            <img
                                src="./Images/Others/designer.jpeg"
                                class="card-img-top"
                                style="height:200px; object-fit:cover;"
                            >

                            <span class="badge position-absolute top-0 end-0 m-3">
                                Photography & Camera Handling
                            </span>
                        </div>

                        <div class="card-body p-4">
                            <h5 class="card-title mb-3">
                                   Photography & Camera Handling
                            </h5>

                            <p class="text-muted">
                               View Details
                            </p>
                        </div>

                    </div>

                </a>

            </div>

        </div>

    </div>
</section>

<!-- Modal -->
<div class="modal fade" id="courseModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content shadow-lg">

      <div class="modal-header border-0">
        <h4 class="modal-title fw-bold" id="courseTitle"></h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body text-center px-4 pb-4">
        <img id="courseImage" class="img-fluid mb-3">

        <p id="courseDescription" class="text-muted" style="text-align:start"></p>

        <div class="d-flex justify-content-center gap-3 mt-3 flex-wrap">
            <span class="badge bg-success">Offline</span>
            <span class="badge bg-warning text-dark">16 Weeks</span>
            <span class="badge bg-info text-dark">Certification</span>
        </div>

        <a href="contact.php" class="btn btn-primary mt-4">
             Enroll Now
        </a>
      </div>

    </div>
  </div>
</div>

    <!-- Categories Section -->
    <section class="categories-section" id="categories">
        <div class="container">
            <div class="section-header text-center" data-aos="fade-up">
                <span class="sub-heading text-accent-gradient">Learning Paths</span>
                <h2 class="heading">Explore Our <span class="text-gradient">Categories</span></h2>
                <p class="lead">Discover specialized learning paths tailored to your interests</p>
            </div>
            
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($categories as $index => $category): ?>
                <div class="col" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                    <div class="category-card h-100">
                        <div class="category-icon">
                            <div class="icon-bg"></div>
                            <i class="<?php echo !empty($category['icon']) ? $category['icon'] : 'bi bi-book'; ?>"></i>
                        </div>
                        <div class="category-content">
                            <h3><?php echo $category['name']; ?></h3>
                            <p><?php echo !empty($category['description']) ? substr($category['description'], 0, 80) . '...' : 'Explore courses in this category'; ?></p>
                            <!-- <div class="category-meta">
                                <span><i class="bi bi-collection"></i> <?php echo $category['course_count']; ?> Courses</span>
                            </div> -->
                            <a href="courses.php?category=<?php echo $category['category_id']; ?>" class="category-link">
                                View Courses <i class="bi bi-arrow-right"></i>
                            </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

   


    <!-- Gallery Section -->
    <section class="py-5 bg-light" id="gallery" data-aos="fade-up" data-aos-delay="500">
        <div class="container py-5">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="section-heading text-center section-title" data-aos="fade-up">Our Learning Journey</h2>
                    <p class="lead text-muted" data-aos="fade-up" data-aos-delay="200">Take a glimpse at our vibrant learning community in action</p>
                </div>
            </div>
            <div class="row g-4" data-aos="fade-up" data-aos-delay="400">
                <div class="col-12">
                    <div class="swiper gallerySwiper">
                        <div class="swiper-wrapper">
                            <?php
                            $images = glob('./Images/gallery/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
                            foreach ($images as $image): ?>
                                <div class="swiper-slide">
                                    <div class="premium-card overflow-hidden">
                                        <img src="<?php echo $image; ?>" 
                                            class="img-fluid w-100" 
                                            alt="Gallery Image" 
                                            style="height: 400px; object-fit: cover;">
                                    </div>
                                </div>
                            <?php endforeach; ?>
                </div>
                        <div class="swiper-pagination mt-4"></div>
                        <div class="swiper-button-next"></div>
                        <div class="swiper-button-prev"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
     <!-- Testimonial Section -->
    <section class="testimonial-section" id="testimonials">
        <div>

  

            <!-- Call to Action -->
            <div class="testimonial-cta" data-aos="fade-up" data-aos-delay="400" style="text-align: center;display: flex;justify-content: center;align-items: center;margin-top: 40px;">
                <div class="row align-items-center">
                    <div class="col-lg-8 mb-4 mb-lg-0">
                        <div class="cta-content">
                            <h3 class="text-white mb-2 ">Ready to Start Your Learning Journey?</h3>
                            <p class="text-white-50 mb-0">Join thousands of successful students who have transformed their careers with us.</p>
                        </div>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <a href="./studentPanel/signup.php" class="btn btn-light btn-lg rounded-pill">
                            Get Started Now
                            <i class="bi bi-arrow-right ms-2"></i>
                        </a>
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

<section class="modern-feedback-section py-5" id="student-feedback" data-aos="fade-up" data-aos-delay="100" >

    <div class="container py-5">

        <!-- Heading -->
        <div class="row justify-content-center text-center mb-5">

            <div class="col-lg-7">

                             <span class="sub-heading text-accent-gradient">Testimonials</span>


                <h2 class="feedback-heading mt-3" data-aos-delay="400" data-aos="fade-up">
                    What Our <span class="text-gradient">Students Say</span>
                </h2>

                <p class="feedback-description" data-aos-delay="500" data-aos="fade-up">
                    Real experiences from students who transformed
                    their careers with GD Edu Tech.
                </p>

            </div>

        </div>

        <!-- Swiper -->
        <div class="swiper modernFeedbackSwiper" data-aos-delay="600" data-aos="fade-up">

            <div class="swiper-wrapper">

                <?php while($row = $feedbacks->fetch_assoc()): ?>

                    <div class="swiper-slide">

                        <div class="modern-feedback-card">

                            <!-- Top -->
                            <div class="feedback-top-area">

                                <div class="feedback-profile">

                                    <?php if(!empty($row['student_image'])): ?>

                                        <img
                                            src="./uploads/feedback/<?php echo $row['student_image']; ?>"
                                            class="feedback-avatar"
                                            alt="student"
                                        >

                                    <?php else: ?>

                                        <img
                                            src="./Images/default-user.png"
                                            class="feedback-avatar"
                                            alt="student"
                                        >

                                    <?php endif; ?>

                                    <div>

                                        <h5 class="student-name">
                                            <?php echo htmlspecialchars($row['student_name']); ?>
                                        </h5>

                                        <p class="college-name">
                                            <?php echo htmlspecialchars($row['college_name']); ?>
                                        </p>

                                    </div>

                                </div>

                                <div class="quote-icon">
                                    <i class="bi bi-quote"></i>
                                </div>

                            </div>

                            <!-- Stars -->
                            <div class="modern-stars">

                                <?php
                                for($i = 1; $i <= $row['rating']; $i++){
                                    echo '<i class="bi bi-star-fill"></i>';
                                }
                                ?>

                            </div>

                            <!-- Feedback -->
                            <p class="modern-feedback-text">

                                <?php echo htmlspecialchars($row['feedback']); ?>

                            </p>

                            <!-- Course -->
                            <div class="feedback-course-tag">

                                <i class="bi bi-book"></i>

                                <?php echo htmlspecialchars($row['course_name']); ?>

                            </div>

                        </div>

                    </div>

                <?php endwhile; ?>

            </div>
            <!-- <div class="swiper-button-next"></div>
<div class="swiper-button-prev"></div>
<div class="swiper-pagination"></div> -->

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
        
        // Initialize Swiper for Gallery
        var gallerySwiper = new Swiper(".gallerySwiper", {
            slidesPerView: 1,
            spaceBetween: 10,
            loop: true,
            autoHeight: true,
            navigation: {
                nextEl: ".gallerySwiper .swiper-button-next",
                prevEl: ".gallerySwiper .swiper-button-prev",
            },
            pagination: {
                el: ".gallerySwiper .swiper-pagination",
                clickable: true,
            },
            breakpoints: {
                480: {
                    slidesPerView: 1,
                    spaceBetween: 10,
                },
                576: {
                    slidesPerView: 2,
                    spaceBetween: 20,
                },
                992: {
                    slidesPerView: 3,
                    spaceBetween: 30,
                },
            },
            autoplay: {
                delay: 4000,
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
       // Feedback Swiper
var feedbackSwiper = new Swiper(".modernFeedbackSwiper", {

    slidesPerView: 1,
    spaceBetween: 25,

    loop: true,
    autoHeight: true,

    navigation: {
        nextEl: ".modernFeedbackSwiper .swiper-button-next",
        prevEl: ".modernFeedbackSwiper .swiper-button-prev",
    },

    pagination: {
        el: ".modernFeedbackSwiper .swiper-pagination",
        clickable: true,
    },

    breakpoints: {

        480: {
            slidesPerView: 1,
            spaceBetween: 10,
        },

        576: {
            slidesPerView: 2,
            spaceBetween: 20,
        },

        992: {
            slidesPerView: 3,
            spaceBetween: 30,
        },

    },

    autoplay: {
        delay: 2500,
        disableOnInteraction: false,
    },

});
    </script>

   
</body>

</html>