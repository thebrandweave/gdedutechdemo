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

        // Headers for native mail()
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: GD Edu Tech Contact <noreply@gdedutech.com>" . "\r\n";
        $headers .= "Reply-To: " . $name . " <" . $email . ">" . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        $mailSent = false;

        // 1. Try PHPMailer if composer vendor/autoload.php exists
        if (file_exists('./vendor/autoload.php')) {
            require_once './vendor/autoload.php';
            if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                try {
                    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                    // Standard PHP Mailer dispatch
                    $mail->isMail();
                    $mail->setFrom('noreply@gdedutech.com', 'GD Edu Tech Contact');
                    $mail->addAddress($to_email, 'GD Edu Tech Admin');
                    $mail->addReplyTo($email, $name);
                    $mail->isHTML(true);
                    $mail->Subject = $email_subject;
                    $mail->Body    = $html_content;
                    $mail->AltBody = "Name: $name\nEmail: $email\nPhone: $phone\nSubject: $subject\nMessage: $message_text";
                    $mailSent = $mail->send();
                } catch (\Exception $e) {
                    $mailSent = false;
                }
            }
        }

        // 2. Native PHP mail() fallback
        if (!$mailSent) {
            $mailSent = @mail($to_email, $email_subject, $html_content, $headers);
        }

        $message = 'Thank you! Your message has been submitted';
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

        /* Google review tab - reference style */
        .gd-google-review-tab {
            position: fixed !important;
            top: 50% !important;
            left: -69px !important;

            width: 170px;
            height: 51px;

            transform: translateY(-50%) rotate(-90deg) !important;
            transform-origin: center center;

            z-index: 999999 !important;

            background: #ffffff !important;
            /* border: 1px solid #eeeeee !important; */
            /* border-radius: 4px !important; */
            /* box-shadow: 0 2px 10px rgba(0, 0, 0, 0.12) !important; */

            display: flex !important;
            align-items: center;
            justify-content: center;

            text-decoration: none !important;
            visibility: visible !important;
            opacity: 1 !important;

            padding: 0 5px;
            cursor: pointer;
        }

       .gd-google-review-tab-inner {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.review-content {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    justify-content: center;
    gap: 3px;
}

.review-stars {
    color: #fbbc04;
    font-size: 16px;
    line-height: 1;
}

.review-text {
    color: #202124;
    font-size: 12px;
    font-weight: 700;
    line-height: 1;
}

        .gd-google-review-tab .google-g-logo {
            width: 25px;
            height: 25px;
            display: block;
            flex: 0 0 25px;
            object-fit: contain;
        }

        .gd-google-review-tab .review-stars {
            color: #fbbc04;
            font-size: 16px;
            line-height: 1;
            letter-spacing: -1px;
            display: inline-flex;
            align-items: center;
            font-family: Arial, sans-serif;
        }

        .gd-google-review-tab .review-text {
            color: #202124;
            font-size: 10px;
            line-height: 1;
            font-weight: 700;
            font-family: Arial, sans-serif;
        }

        .gd-google-review-tab:hover,
        .gd-google-review-tab:focus,
        .gd-google-review-tab:active {
            background: #ffffff !important;
            color: #202124 !important;
            text-decoration: none !important;
            transform: translateY(-50%) rotate(-90deg) !important;
        }

        @media (max-width: 576px) {
            .gd-google-review-tab {
                width: 176px;
                height: 44px;
                left: -66px !important;
                padding: 0 10px;
            }

            .gd-google-review-tab .google-g-logo {
                width: 23px;
                height: 23px;
                flex-basis: 23px;
            }

            .gd-google-review-tab .review-stars {
                font-size: 14px;
            }

            .gd-google-review-tab .review-text {
                font-size: 11px;
            }
        }

</style>
</head>

<body>
<!-- Fixed Google Write a Review Tab -->
  <a
    class="gd-google-review-tab"
    href="https://search.google.com/local/writereview?placeid=ChIJxxHvltdbozsR9dshtS279tk"
    target="_blank"
    rel="noopener noreferrer"
    aria-label="View GD Edu Tech Google Reviews"
>
     <span class="gd-google-review-tab-inner">
    <img
        class="google-g-logo"
        src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg"
        alt="Google"
    >

    <span class="review-content">
        <span class="review-stars" aria-hidden="true">★★★★★</span>
        <span class="review-text">Write a review</span>
    </span>
</span>
    </a>

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
                                    <li><a href="https://wa.me/917204626299?text=Hello%20GD%20Edu%20Tech%2C%20I%20have%20an%20inquiry%20regarding%20course%20admissions." target="_blank" rel="noopener" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a></li>
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
</body>

</html>