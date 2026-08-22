<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once './Configurations/config.php';

$message = '';
$message_class = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message_text = trim($_POST['message'] ?? ''); // Optional
    
    // 1. Check required fields
    if (empty($name) || empty($email) || empty($phone) || empty($subject)) {
        $message = 'Please fill out all required fields.';
        $message_class = 'alert-danger';
    } 
    // 2. Validate Email
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
        $message_class = 'alert-danger';
    } 
    // 3. Validate Phone Number (Regex for 10 digits)
    elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
        $message = 'Please enter a valid 10-digit phone number.';
        $message_class = 'alert-danger';
    } 
    else {
        // Target Admin Email ID
        $to_email = "gdedutech24@gmail.com";
        $email_subject = "New Inquiry: " . $subject . " - " . $name;
        
        // HTML Formatted Email Body
        $html_content = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: "Segoe UI", Arial, sans-serif; background-color: #f8fafc; color: #0f172a; margin: 0; padding: 20px; }
                .card { background: #ffffff; padding: 30px; border-radius: 16px; border: 1px solid #e2e8f0; max-width: 600px; margin: 0 auto; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
                .header { background: linear-gradient(135deg, #0d7298 0%, #065d7d 100%); color: #ffffff; padding: 20px; border-radius: 12px; text-align: center; margin-bottom: 25px; }
                .header h2 { margin: 0; font-size: 20px; font-weight: 700; }
                .header p { margin: 5px 0 0; font-size: 13px; opacity: 0.9; }
                .field { margin-bottom: 16px; }
                .label { font-weight: 700; color: #475569; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; display: block; }
                .val { font-size: 15px; color: #0f172a; font-weight: 500; background: #f8fafc; padding: 10px 14px; border-radius: 8px; border: 1px solid #cbd5e1; }
                .val a { color: #0d7298; text-decoration: none; font-weight: 600; }
                .footer { font-size: 12px; color: #94a3b8; margin-top: 25px; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 15px; }
            </style>
        </head>
        <body>
            <div class="card">
                <div class="header">
                    <h2>GD Edu Tech Website Inquiry</h2>
                    <p>New Contact Form Submission</p>
                </div>
                <div class="field">
                    <span class="label">Sender Name</span>
                    <div class="val">' . htmlspecialchars($name) . '</div>
                </div>
                <div class="field">
                    <span class="label">Email Address</span>
                    <div class="val"><a href="mailto:' . htmlspecialchars($email) . '">' . htmlspecialchars($email) . '</a></div>
                </div>
                <div class="field">
                    <span class="label">Phone Number</span>
                    <div class="val"><a href="tel:' . htmlspecialchars($phone) . '">' . htmlspecialchars($phone) . '</a></div>
                </div>
                <div class="field">
                    <span class="label">Subject</span>
                    <div class="val">' . htmlspecialchars($subject) . '</div>
                </div>
                <div class="field">
                    <span class="label">Message</span>
                    <div class="val" style="white-space: pre-wrap;">' . htmlspecialchars($message_text ?: 'No message provided.') . '</div>
                </div>
                <div class="footer">
                    Sent from GD Edu Tech Contact Form &bull; ' . date('d M Y, h:i A') . '
                </div>
            </div>
        </body>
        </html>';

        // Headers
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: GD Edu Tech Contact <noreply@gdedutech.com>" . "\r\n";
        $headers .= "Reply-To: " . $name . " <" . $email . ">" . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        // Send Email
        @mail($to_email, $email_subject, $html_content, $headers);

        $message = 'Thank you! Your message has been submitted and sent to gdedutech24@gmail.com.';
        $message_class = 'alert-success';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - GD Edu Tech</title>
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

        /* Hero Image Float Animation */
        .contact-hero-image {
            max-width: 100%;
            height: auto;
            max-height: 335px;
            object-fit: contain;
            filter: drop-shadow(0 20px 35px rgba(0, 0, 0, 0.18));
            transition: transform 0.4s ease;
        }

        .contact-hero-image:hover {
            transform: translateY(-8px) scale(1.02);
        }

        /* Balanced Custom Modern Split Contact Form Layout matching reference */
        .contact-form-wrapper {
            max-width: 1000px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 26px;
            box-shadow: 0 18px 50px -15px rgba(15, 23, 42, 0.11);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        .contact-us-sidebar {
            position: relative;
            background: linear-gradient(135deg, #0d7298 0%, #065d7d 100%);
            min-height: 440px;
            height: 100%;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 32px 25px;
            color: #ffffff;
        }

        .contact-us-sidebar::before {
            position: absolute;
            content: "";
            bottom: -50px;
            left: -100px;
            height: 235px;
            width: 380px;
            background: rgba(248, 183, 216, 0.25);
            transform: rotate(25deg);
            pointer-events: none;
        }

        .contact-us-sidebar::after {
            position: absolute;
            content: "";
            bottom: -80px;
            right: -100px;
            height: 255px;
            width: 380px;
            background: rgba(158, 216, 235, 0.3);
            transform: rotate(-25deg);
            pointer-events: none;
        }

        .vertical-contact-title {
            font-size: 1.65rem;
            font-weight: 800;
            letter-spacing: 3.5px;
            color: #ffffff;
            text-transform: uppercase;
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            opacity: 0.95;
        }

        .contact-social-bar {
            position: relative;
            z-index: 5;
        }

        .contact-social-bar ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            gap: 10px;
        }

        .contact-social-bar ul li a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(5px);
            color: #ffffff;
            font-size: 1rem;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .contact-social-bar ul li a:hover {
            background: #ffffff;
            color: #0d7298;
            transform: translateY(-2px);
        }

        /* Right Panel Info & Form */
        .contact-main-panel {
            padding: 32px 38px;
        }

        .contact-header-title h1 {
            font-size: 1.9rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .contact-header-title h2 {
            font-size: 0.95rem;
            color: #64748b;
            font-weight: 500;
        }

        .contact-info-card {
            text-align: center;
            padding: 10px 5px;
            height: 100%;
        }

        .contact-info-card i {
            color: #0d7298;
            font-size: 1.55rem;
            margin-bottom: 8px;
            display: inline-block;
        }

        .contact-info-card h3 {
            font-size: 0.88rem;
            font-weight: 500;
            color: #334155;
            line-height: 1.45;
            margin: 0;
        }

        .custom-minimal-form {
            position: relative;
            padding-bottom: 8px;
        }

        .custom-minimal-input {
            width: 100%;
            border: 0;
            border-bottom: 1.5px solid #cbd5e1;
            padding: 10px 0;
            outline: none;
            font-size: 0.92rem;
            color: #0f172a;
            background: transparent;
            transition: border-color 0.3s ease;
        }

        .custom-minimal-input:focus {
            border-color: #0d7298;
        }

        .custom-minimal-textarea {
            width: 100%;
            border: 0;
            border-bottom: 1.5px solid #cbd5e1;
            padding: 10px 0;
            outline: none;
            font-size: 0.92rem;
            color: #0f172a;
            background: transparent;
            resize: none;
            transition: border-color 0.3s ease;
        }

        .custom-minimal-textarea:focus {
            border-color: #0d7298;
        }

        .custom-send-btn {
            background: linear-gradient(135deg, #0d7298 0%, #065d7d 100%);
            color: #ffffff;
            font-size: 1rem;
            font-weight: 700;
            border: 0;
            border-radius: 50px;
            padding: 12px 40px;
            box-shadow: 0 7px 22px rgba(13, 114, 152, 0.28);
            transition: all 0.3s ease;
        }

        .custom-send-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 11px 28px rgba(13, 114, 152, 0.38);
            color: #ffffff;
        }

        @media (max-width: 991px) {
            .contact-us-sidebar {
                min-height: 180px;
                padding: 25px;
            }
            .vertical-contact-title {
                writing-mode: horizontal-tb;
                transform: none;
                font-size: 1.4rem;
            }
            .contact-main-panel {
                padding: 25px;
            }
        }

        /* FAQ Image Styling */
        .faq-image {
            max-width: 100%;
            height: auto;
            max-height: 420px;
            object-fit: contain;
            filter: drop-shadow(0 15px 35px rgba(0, 0, 0, 0.12));
            transition: transform 0.4s ease;
        }

        .faq-image:hover {
            transform: translateY(-6px);
        }

        /* FAQ Accordion Styling */
        .contact-faq-item {
            border: 1px solid #e2e8f0 !important;
            border-radius: 16px !important;
            overflow: hidden;
            background: #ffffff;
        }

        .contact-faq-button {
            font-weight: 700 !important;
            color: #0f172a !important;
            padding: 20px 24px !important;
            font-size: 1.05rem !important;
        }

        .contact-faq-button:not(.collapsed) {
            background-color: rgba(13, 114, 152, 0.06) !important;
            color: #0d7298 !important;
            box-shadow: none !important;
        }

        .contact-faq-button:focus {
            box-shadow: none !important;
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
                            <li class="breadcrumb-item active text-black" aria-current="page">Contact</li>
                        </ol>
                    </nav>

                    <h1 class="display-4 fw-bold text-black mb-3">
                        Get In <span class="cta-gold-text">Touch With Us</span>
                    </h1>

                    <p class="lead text-black-50 mb-4" style="max-width: 650px;">
                        Have questions about our training programs, admissions, or certifications? Reach out to our dedicated support team today.
                    </p>
                </div>

                <div class="col-lg-5 text-center text-lg-end" data-aos="fade-left" data-aos-delay="200">
                    <div class="position-relative d-inline-block">
                        <div class="position-absolute top-50 start-50 translate-middle rounded-circle  bg-opacity-20 blur-2xl" style="width: 220px; height: 220px;  z-index: 1;"></div>
                        <img src="./Images/Others/contact.png" alt="Contact Us" class="img-fluid position-relative z-2 contact-hero-image">
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

    <!-- Contact Form & Info Section -->
    <section class="">
        <div class="container py-2">
            <?php if (!empty($message)): ?>
                <div class="alert <?php echo $message_class; ?> alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                    <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                    <span class="fw-semibold"><?php echo $message; ?></span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <div class="contact-form-wrapper" data-aos="fade-up">
                <div class="row g-0">
                    <!-- Left Sidebar Panel -->
                    <div class="col-lg-3 col-md-4">
                        <div class="contact-us-sidebar">
                            <div class="contact-header">
                                <h1 class="vertical-contact-title mb-0">CONTACT US</h1>
                            </div>
                            <div class="contact-social-bar">
                                <ul>
                                    <li><a href="https://www.facebook.com/people/GD-EDU-TECH/" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a></li>
                                    <li><a href="https://www.linkedin.com/company/gd-edu-tech/" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a></li>
                                    <li><a href="https://www.instagram.com/gd_edu__tech/" target="_blank" rel="noopener" aria-label="Instagram"><i class="fab fa-instagram"></i></a></li>
                                    <li><a href="https://www.youtube.com/@GDEDUTECH" target="_blank" rel="noopener" aria-label="YouTube"><i class="fab fa-youtube"></i></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Right Main Content Area -->
                    <div class="col-lg-9 col-md-8">
                        <div class="contact-main-panel">
                            
                            <!-- Header Title -->
                            <div class="contact-header-title text-center mb-3">
                                <h1>Let's Get Started</h1>
                                <h2>Contact us to start your next learning journey!</h2>
                            </div>

                            <!-- Address, Phone, Email Row -->
                            <div class="row g-2 mb-3">
                                <div class="col-md-4">
                                    <div class="contact-info-card">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <h3>Kankanady Gate 4th floor, Kankanady Bypass road, Mangalore, India</h3>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="contact-info-card">
                                        <i class="fas fa-phone-alt fa-flip-horizontal" style="transform: scaleX(-1); display: inline-block;"></i>
                                        <h3>+91 7204626299</h3>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="contact-info-card">
                                        <i class="fas fa-envelope"></i>
                                        <h3>gdedutech24@gmail.com</h3>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Form -->
                            <div class="custom-minimal-form">
                                <form action="contact.php" method="POST">
                                    <div class="row g-2.5">
                                        <div class="col-12">
                                            <input type="text" class="custom-minimal-input" id="name" name="name" placeholder="Your Name *" required>
                                        </div>
                                        <div class="col-6">
                                            <input type="email" class="custom-minimal-input" id="email" name="email" placeholder="Your Email *" required>
                                        </div>
                                        <div class="col-6">
                                            <input type="tel" class="custom-minimal-input" id="phone" name="phone" placeholder="Phone Number (10 digits) *" pattern="[0-9]{10}" title="Please enter a 10-digit phone number" required>
                                        </div>
                                        <div class="col-12">
                                            <input type="text" class="custom-minimal-input" id="subject" name="subject" placeholder="Subject *" required>
                                        </div>
                                        <div class="col-12">
                                            <textarea class="custom-minimal-textarea" id="message" name="message" rows="3" placeholder="Tell us about your inquiry... (Optional)"></textarea>
                                        </div>
                                        <div class="col-12 text-center pt-2">
                                            <button type="submit" name="contact_submit" class="custom-send-btn">
                                                <span>SEND MESSAGE</span>
                                                <i class="bi bi-send-fill ms-2"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section (100% Full-Width Edge-to-Edge with Satellite View) -->
    <section class="py-0 w-100 my-5 overflow-hidden" data-aos="fade-up">
        <div class="container mb-4">
            <div class="row">
                <div class="col-lg-8">
                    <h3 class="display-6 top-section-title mb-1">Our Location</h3>
                    <p class="lead top-section-subtitle mb-0">Visit our training campus &amp; administrative office in Mangalore</p>
                </div>
            </div>
        </div>
        <div class="w-100 position-relative" style="height: 480px;">
            <iframe src="https://maps.google.com/maps?q=GD+EDU+TECH+Kankanady+Bypass+road+Mangalore&t=k&z=18&ie=UTF8&iwloc=&output=embed"
                width="100%" 
                height="480" 
                style="border:0; display: block;" 
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
    </section>

    <!-- FAQ Section (2-Column: Left faq.png + Right Accordion) -->
    <section class="py-5 ">
        <div class="container py-3">
            <div class="row align-items-center g-5">
                <!-- Left Column: FAQ Image -->
                <div class="col-lg-5 text-center" data-aos="fade-right">
                    <img src="./Images/Others/faq.png" alt="Frequently Asked Questions" class="img-fluid faq-image">
                </div>

                <!-- Right Column: FAQ Heading & Accordion -->
                <div class="col-lg-7" data-aos="fade-left">
                    <div class="mb-4">
                        <h2 class="display-6 top-section-title mb-2">Frequently Asked Questions</h2>
                        <p class="lead top-section-subtitle mb-0">Find answers to common questions about GD Edu Tech</p>
                    </div>

                    <div class="accordion" id="faqAccordion">
                        <?php
                        // Fetch FAQs from database
                        $faq_query = "SELECT * FROM FAQs ORDER BY created_at DESC LIMIT 4";
                        $faq_result = mysqli_query($conn, $faq_query);
                        
                        if ($faq_result && mysqli_num_rows($faq_result) > 0) {
                            $first = true;
                            $count = 0;
                            while ($faq = mysqli_fetch_assoc($faq_result)) {
                                $count++;
                                $show = $first ? 'show' : '';
                                $expanded = $first ? 'true' : 'false';
                                $collapsed = $first ? '' : 'collapsed';
                                $first = false;
                        ?>
                        <div class="accordion-item contact-faq-item mb-3 shadow-sm">
                            <h2 class="accordion-header" id="heading<?php echo $count; ?>">
                                <button class="accordion-button contact-faq-button <?php echo $collapsed; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $count; ?>" aria-expanded="<?php echo $expanded; ?>" aria-controls="collapse<?php echo $count; ?>">
                                    <?php echo htmlspecialchars($faq['question']); ?>
                                </button>
                            </h2>
                            <div id="collapse<?php echo $count; ?>" class="accordion-collapse collapse <?php echo $show; ?>" aria-labelledby="heading<?php echo $count; ?>" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-secondary" style="line-height: 1.7; font-size: 0.96rem;">
                                    <?php echo htmlspecialchars($faq['answer']); ?>
                                </div>
                            </div>
                        </div>
                        <?php
                            }
                        } else {
                            // Fallback to static FAQs if none in database
                        ?>
                        <div class="accordion-item contact-faq-item mb-3 shadow-sm">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button contact-faq-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                    How do I enroll in a course or internship program?
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-secondary" style="line-height: 1.7; font-size: 0.96rem;">
                                    Enrolling is quick and easy! Browse our course catalog, select your desired program, click the "Enroll" or "Apply Now" button, and complete the registration form. You can also visit our campus in Mangalore for direct admission guidance.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item contact-faq-item mb-3 shadow-sm">
                            <h2 class="accordion-header" id="headingTwo">
                                <button class="accordion-button contact-faq-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    Are certificates provided upon course completion?
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-secondary" style="line-height: 1.7; font-size: 0.96rem;">
                                    Yes! Upon successful completion of any course or internship program, you will receive an official, industry-recognized certificate from GD Edu Tech complete with a unique Certificate ID for online verification.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item contact-faq-item mb-3 shadow-sm">
                            <h2 class="accordion-header" id="headingThree">
                                <button class="accordion-button contact-faq-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    Does GD Edu Tech offer placement assistance?
                                </button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-secondary" style="line-height: 1.7; font-size: 0.96rem;">
                                    Absolutely! We provide comprehensive placement support including resume building, mock interview preparation, technical portfolio reviews, and direct interview referrals with top hiring partners.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item contact-faq-item mb-3 shadow-sm">
                            <h2 class="accordion-header" id="headingFour">
                                <button class="accordion-button contact-faq-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                    Are both online and offline classes available?
                                </button>
                            </h2>
                            <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-secondary" style="line-height: 1.7; font-size: 0.96rem;">
                                    Yes! We offer flexible learning options including interactive live online classes as well as hands-on classroom sessions at our campus located at Kankanady Gate, Mangalore.
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
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
                    <h6 class="plan-title fw-bold mb-1"><i class="bi bi-chat-dots-fill text-primary me-2"></i> Student Support</h6>
                    <p class="plan-subtext text-muted small mb-0">Instant course inquiries.</p>
                </div>
                <div class="plan-price mb-2">
                    <span class="price-num fw-bold fs-5 text-primary">Direct Help</span>
                </div>
                <ul class="plan-features list-unstyled small mb-3" style="font-size: 0.82rem;">
                    <li class="mb-1.5"><i class="bi bi-check2 me-1.5 text-dark fw-bold"></i> Admission Guidance</li>
                    <li class="mb-1.5"><i class="bi bi-check2 me-1.5 text-dark fw-bold"></i> Skill Assessment</li>
                    <li class="mb-1.5"><i class="bi bi-check2 me-1.5 text-dark fw-bold"></i> Offline Campus Tour</li>
                </ul>
                <a href="contact.php" class="btn btn-plan-dark w-100 text-center text-decoration-none">Contact Us</a>
            </div>

            <div class="cta-center-content mx-auto text-center px-3">
                <h2 class="cta-banner-heading fw-bold mb-3">
                    Have questions? Talk to our education advisors.
                </h2>
                <p class="cta-banner-subtext text-muted mb-4">
                    Our team is ready to assist you with course syllabus details, offline batch timings, and career path guidance.
                </p>
                <div class="d-flex flex-wrap gap-3 justify-content-center align-items-center">
                    <a href="tel:+917204626299" class="btn btn-cta-main-pill text-decoration-none">
                        <span>Call Us Now</span>
                        <i class="bi bi-telephone-fill ms-2 fs-5" style="transform: scaleX(1); display: inline-block;"></i>
                    </a>
                    <a href="mailto:gdedutech24@gmail.com" class="btn btn-cta-secondary-outline text-decoration-none">
                        <span>Email Advisor</span>
                        <i class="bi bi-envelope-fill ms-2"></i>
                    </a>
                </div>
            </div>

            <div class="plan-card plan-card-right text-start d-none d-lg-block">
                <div class="plan-header mb-2">
                    <h6 class="plan-title fw-bold mb-1"><i class="bi bi-building-fill text-warning me-2"></i> Campus Visit</h6>
                    <p class="plan-subtext text-muted small mb-0">Visit our office.</p>
                </div>
                <div class="plan-price mb-2">
                    <span class="price-num fw-bold fs-5 text-warning" style="color: #d97706 !important;">Mangalore, KA</span>
                </div>
                <ul class="plan-features list-unstyled small mb-3" style="font-size: 0.82rem;">
                    <li class="mb-1.5"><i class="bi bi-diamond-fill me-1.5 text-dark small"></i> Kankanady Gate 4th Flr</li>
                    <li class="mb-1.5"><i class="bi bi-diamond-fill me-1.5 text-dark small"></i> Free Demo Classes</li>
                    <li class="mb-1.5"><i class="bi bi-diamond-fill me-1.5 text-dark small"></i> 1-on-1 Counseling</li>
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
    </script>
</body>

</html>