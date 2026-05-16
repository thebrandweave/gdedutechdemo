<?php
// Get the current year for the copyright
$current_year = date('Y');
?>

<!-- Add AOS CSS and JS -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<footer class="footer">
    <div class="container">
        <div class="footer-top">
            <div class="footer-logo-section" data-aos="fade" data-aos-duration="1200">
                <div class="logo-container">
                    <img src="./Images/Logos/GD_Full_logo.png" alt="GD Edu Tech Logo">
                </div>
                <p class="tagline">Empowering minds through quality education and innovative learning solutions. Join us in shaping the future of education.</p>
                <div class="social-icons">
                    <a href="https://www.facebook.com/gdedutechofficial/" target="_blank" class="social-icon" data-aos="fade" data-aos-delay="200"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://www.linkedin.com/company/gd-edu-tech/"target="_blank" class="social-icon" data-aos="fade" data-aos-delay="300"><i class="fab fa-linkedin-in"></i></a>
                    <a href="https://www.instagram.com/gd_edu__tech/" target="_blank" class="social-icon" data-aos="fade" data-aos-delay="400"><i class="fab fa-instagram"></i></a>
                    <a href="https://www.youtube.com/@GDEDUTECH" target="_blank" class="social-icon" data-aos="fade" data-aos-delay="500"><i class="fab fa-youtube"></i></a>
                        </div>

                    <div class="ventures">
                    <h2 class="ventures-title">
                        <a href="https://goldendream.in/">
                        PRO GEE DEE VENTURES
                        </a>
                    </h2>
                </div>
                    </div>
            <div class="footer-links-section" data-aos="fade" data-aos-duration="1200" data-aos-delay="100">
                <h3 class="footer-heading">Quick Links</h3>
                            <ul class="footer-links">
                    <li data-aos="fade" data-aos-delay="100"><a href="index.php">Home</a></li>
                    <li data-aos="fade" data-aos-delay="250"><a href="courses.php">Courses</a></li>
                    <!-- <li data-aos="fade" data-aos-delay="100"><a href="blog.php">Blog</a></li> -->
                    <li data-aos="fade" data-aos-delay="100"><a href="events.php">Events</a></li>
                    <li data-aos="fade" data-aos-delay="200"><a href="about.php">About Us</a></li>
                    <li data-aos="fade" data-aos-delay="350"><a href="career.php">Careers</a></li>
                    <li data-aos="fade" data-aos-delay="350"><a href="scholarship.php">Apply Scholarship</a></li>
                    <li data-aos="fade" data-aos-delay="300"><a href="contact.php">Contact</a></li>
                            </ul>
                    </div>

            <div class="footer-courses-section" data-aos="fade" data-aos-duration="1200" data-aos-delay="200">
                <h3 class="footer-heading">Popular Courses</h3>
           <a href="courses.php">     <div class="course-item" data-aos="fade" data-aos-delay="300">
                    <div class="course-icon"><i class="fas fa-code"></i></div> 
                    <div class="course-name">Fullstack Development</div>
                        </div></a>
              <a href="courses.php">  <div class="course-item" data-aos="fade" data-aos-delay="450">
                    <div class="course-icon"><i class="fas fa-bullhorn"></i></div>
                    <div class="course-name">Digital Marketing</div>
                </div></a>
               <a href="courses.php"> <div class="course-item" data-aos="fade" data-aos-delay="550">
                    <div class="course-icon"><i class="fas fa-drafting-compass"></i></div>
                    <div class="course-name">Architecture Design</div>
                </div></a>
             <a href="courses.php">   <div class="course-item" data-aos="fade" data-aos-delay="600">
                    <div class="course-icon"><i class="fas fa-paint-brush"></i></div>
                    <div class="course-name">Graphic Designing</div>
                </div></a>
            </div>
            
            <div class="footer-contact-section" data-aos="fade" data-aos-duration="1200" data-aos-delay="300">
                <h3 class="footer-heading">Contact Info</h3>
                <ul class="contact-info">
                    <li data-aos="fade" data-aos-delay="400">
                        <div class="contact-icon"><i class="fas fa-map-marker-alt"></i></div>
                        <div class="contact-text">Kankanady Gate 4th floor, Kankanady Bypass road, Kankanady, Mangalore, Karnataka, India</div>
                    </li>
                    <li data-aos="fade" data-aos-delay="450">
                        <div class="contact-icon"><i class="fas fa-envelope"></i></div>
                        <div class="contact-text">gdedutech24@gmail.com</div>
                    </li>
                    <li data-aos="fade" data-aos-delay="500">
                        <div class="contact-icon"><i class="fas fa-phone-alt"></i></div>
                        <div class="contact-text">+91 7204626299</div>
                    </li>
                </ul>
                
                <h3 class="footer-heading" data-aos="fade" data-aos-delay="550">Newsletter</h3>
                <p class="tagline" data-aos="fade" data-aos-delay="600">Subscribe to our newsletter for updates</p>
                <form class="newsletter-form" data-aos="fade" data-aos-delay="650">
                    <input type="email" class="newsletter-input" placeholder="Your email address">
                    <button type="submit" class="newsletter-button"><i class="fas fa-paper-plane"></i></button>
                </form>
            </div>
        </div>

        <div class="footer-divider"></div>
        
        <div class="footer-bottom" data-aos="fade" data-aos-duration="1200" data-aos-delay="400">
            <div class="footer-bottom-text">
                            © <?php echo $current_year; ?> GD Edu Tech. All rights reserved.
            </div>
            <!-- <div class="footer-bottom-links">
                <a href="privacy-policy.php" data-aos="fade" data-aos-delay="500">Privacy Policy</a>
                <a href="terms.php" data-aos="fade" data-aos-delay="550">Terms of Service</a>
                <a href="cookie-policy.php" data-aos="fade" data-aos-delay="600">Cookie Policy</a>
                    </div> -->
            <!-- <div class="developed-by" data-aos="fade" data-aos-delay="650">
                            <span>Developed by</span>
                            <img onclick="window.open('https://intelexsolutions.co.in')" 
                                 src="./Images/Logos/developed_by.png" 
                                 alt="Developed by Intelex Solutions" 
                                 class="footer-dev-logo">
                        </div> -->
                    </div>
                </div>
    
    <a href="#" class="back-to-top">
        <i class="bi bi-arrow-up"></i>
    </a>
    </footer>

<style>
    :root {
        --primary: #0078a8;
        --secondary: #d15b50;
        --light: #f8f9fa;
        --dark: #212529;
        --gray: #6c757d;
    }

    .footer {
        background: linear-gradient(135deg, #fff, #f3f4f6);
        box-shadow: 0 -5px 15px rgba(0, 0, 0, 0.05);
        padding: 3rem 0 2rem;
        border-top: 4px solid var(--primary);
        position: relative;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .footer-top {
        display: flex;
        flex-wrap: wrap;
        gap: 30px;
        margin-bottom: 30px;
    }

    .footer-logo-section {
        flex: 1 1 300px;
        padding-right: 30px;
    }

    .logo-container {
        display: flex;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .logo-container img {
        height: 60px;
        margin-right: 10px;
    }

    .logo-text {
        font-size: 24px;
        font-weight: 700;
        color: var(--primary);
    }

    .logo-text span {
        color: var(--secondary);
    }

    .tagline {
        color: var(--dark);
        line-height: 1.7;
        margin-bottom: 1.5rem;
        font-size: 15px;
    }

    .social-icons {
        display: flex;
        gap: 15px;
    }

    .social-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        background-color: var(--light);
        border-radius: 50%;
        color: var(--primary);
        font-size: 18px;
        transition: all 0.3s ease;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
    }

    .social-icon:hover {
        background-color: var(--primary);
        color: white;
        transform: translateY(-3px);
    }

    .footer-links-section {
        flex: 1 1 200px;
    }

    .footer-courses-section {
        flex: 1 1 220px;
    }

    .footer-contact-section {
        flex: 1 1 300px;
    }

    .footer-heading {
        position: relative;
        font-size: 18px;
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 1.5rem;
        padding-bottom: 0.8rem;
    }

    .footer-heading::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 50px;
        height: 3px;
        background-color: var(--secondary);
    }

    .footer-links {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .footer-links li {
        margin-bottom: 12px;
    }

    .footer-links li a {
        color: var(--gray);
        text-decoration: none;
        font-size: 15px;
        transition: all 0.3s ease;
        display: inline-block;
        position: relative;
    }

    /* .footer-links li a::before {
        content: "→";
        margin-right: 8px;
        color: var(--secondary);
        opacity: 0;
        transform: translateX(-8px);
        transition: all 0.3s ease;
    } */

    .footer-links li a:hover {
        color: var(--primary);
        transform: translateX(5px);
    }

    .footer-links li a:hover::before {
        opacity: 1;
        transform: translateX(0);
    }

    .course-item {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        background-color: white;
        padding: 10px;
        border-radius: 8px;
        transition: all 0.3s ease;
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.05);
    }

    .course-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .course-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        background-color: rgba(0, 120, 168, 0.1);
        border-radius: 6px;
        margin-right: 10px;
        color: var(--primary);
    }

    .course-name {
        font-size: 14px;
        color: var(--dark);
        font-weight: 500;
    }

    .contact-info {
        list-style: none;
        padding: 0;
        margin: 0;
        margin-bottom: 1.5em; 
    }

    .contact-info li {
        display: flex;
        margin-bottom: 15px;
        align-items: center;
    }

    .contact-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        background-color: rgba(0, 120, 168, 0.1);
        border-radius: 50%;
        margin-right: 15px;
        color: var(--primary);
        flex-shrink: 0;
    }

    .contact-text {
        font-size: 15px;
        color: var(--gray);
        line-height: 1.5;
    }

    .footer-divider {
        height: 1px;
        background-color: #d15b50;
        margin: 10px 0 20px;
    }

    .footer-bottom {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        align-items: center;
        padding-top: 20px;
    }

    .footer-bottom-text {
        color: var(--gray);
        font-size: 14px;
    }

    .footer-bottom-links {
        display: flex;
        gap: 20px;
    }

    .footer-bottom-links a {
        color: var(--gray);
        text-decoration: none;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .footer-bottom-links a:hover {
        color: var(--primary);
    }

    .newsletter-form {
        display: flex;
        margin-top: 15px;
        margin-bottom: 20px;
    }

    .newsletter-input {
        flex: 1;
        padding: 12px 15px;
        border: 1px solid #e5e7eb;
        border-radius: 6px 0 0 6px;
        font-size: 14px;
        outline: none;
        transition: all 0.3s ease;
    }

    .newsletter-input:focus {
        border-color: var(--primary);
    }

    .newsletter-button {
        background-color: var(--primary);
        color: white;
        border: none;
        padding: 0 20px;
        border-radius: 0 6px 6px 0;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .newsletter-button:hover {
        background-color: #00678e;
    }

    .back-to-top {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 50px;
        height: 50px;
        background-color: var(--primary);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        text-decoration: none;
        z-index: 1000;
        opacity: 0;
        visibility: hidden;
    }

    .back-to-top.visible {
        opacity: 1;
        visibility: visible;
    }

    .back-to-top:hover {
        background-color: var(--secondary);
        transform: translateY(-5px);
        color: white;
    }

    .developed-by {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-left: auto;
    }

    .developed-by span {
        color: var(--gray);
        font-size: 14px;
    }

    .footer-dev-logo {
        height: 30px;
        cursor: pointer;
        transition: transform 0.3s ease;
    }

    .footer-dev-logo:hover {
        transform: scale(1.05);
    }

    .ventures {
        margin-top: 9%;
        margin-left: 2%;
        transition: all 0.3s ease;
    }

    .ventures:hover {
        transform: translateY(-1px);
    }

    .ventures-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: rgb(213, 213, 213);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin: 0;
        position: relative;
        z-index: 1;
    }

    @media (max-width: 768px) {
        .footer-top {
            flex-direction: column;
            gap: 40px;
        }
        
        .footer-logo-section, 
        .footer-links-section, 
        .footer-courses-section, 
        .footer-contact-section {
            flex: 100%;
            padding-right: 0;
        }
        
        .footer-bottom {
            background-color: var(--light);
            flex-direction: column;
            text-align: center;
            gap: 15px;
        }
        
        .footer-bottom-links {
            justify-content: center;
        }
        
        .developed-by {
            margin: 10px auto 0;
            justify-content: center;
        }
        
        .back-to-top {
            bottom: 20px;
            right: 20px;
            width: 45px;
            height: 45px;
            font-size: 18px;
        }
    }
</style>

<script>
    // Initialize AOS with more subtle settings
    AOS.init({
        once: true,
        offset: 50,
        duration: 1200,
        easing: 'ease',
        delay: 0,
        anchorPlacement: 'top-bottom'
    });

    // Back to top button functionality
    document.addEventListener('DOMContentLoaded', function() {
        const backToTopButton = document.querySelector('.back-to-top');
        
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 100) {
                backToTopButton.classList.add('visible');
            } else {
                backToTopButton.classList.remove('visible');
            }
        });
        
        backToTopButton.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    });
</script>

