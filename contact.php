<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once './Configurations/config.php';

$message = '';
$message_class = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $subject = trim($_POST['subject']);
    $message_text = trim($_POST['message']); // Now optional
    
    // 1. Check required fields (removed $message_text from here)
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
        // Logic to save to DB or send mail goes here
        $message = 'Thank you! Your message has been sent successfully.';
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
    <style>
        /* Add custom styles for the contact image */
        .contact-hero-image {
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
        /* Center icons perfectly inside rounded circles on this page */
        .rounded-circle.bg-primary.bg-opacity-10 {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 !important;
        }
        .contact-page-info {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .contact-page-info li {
            display: flex !important;
            margin-bottom: 30px !important;
            align-items: flex-start !important;
        }

        .contact-page-info .contact-icon {
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

        .contact-page-info .contact-text {
            font-size: 18px !important;
            line-height: 1.5;
            letter-spacing: 0.5px;
        }
        
        .contact-page-info .contact-text h5 {
            margin-bottom: 6px;
            font-size: 20px !important;
            font-weight: 600;
        }
        
        .contact-page-info .contact-text p {
            color: var(--gray);
        }
        
        .contact-page-info .social-icons {
            display: flex;
            gap: 15px;
        }

        .contact-page-info .social-icon {
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
        
        .contact-page-info .social-icon:hover {
            background-color: var(--primary);
            color: white;
            transform: translateY(-3px);
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
                            <li class="breadcrumb-item active text-white" aria-current="page">Contact</li>
                        </ol>
                    </nav>
                    <h1 class="text-white display-5 fw-bold mb-3">Contact Us</h1>
                    <p class="text-white-50 lead mb-0">Get in touch with us for any questions or inquiries.</p>
                </div>
                <div class="col-md-5" data-aos="fade-left">
                    <!-- <img src="./Images/Others/contact.png" alt="Contact Us" class="contact-hero-image"> -->
                </div>
            </div>
        </div>
        <div class="page-header-shape">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
                <path fill="#ffffff" fill-opacity="1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,112C672,96,768,96,864,112C960,128,1056,160,1152,160C1248,160,1344,128,1392,112L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
            </svg>
        </div>
    </section>

    <!-- Contact Form Section -->
    <section class="py-5">
        <div class="container">
            <?php if (!empty($message)): ?>
                <div class="alert <?php echo $message_class; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <div class="row g-5">
                <div class="col-lg-5" data-aos="fade-right">
                    <h2 class="section-heading">Get In Touch</h2>
                    <p class="lead">We're here to help and answer any questions you might have.</p>
                    
                    <div class="mt-5">
                        <ul class="contact-info contact-page-info">
                            <li data-aos="fade" data-aos-delay="400">
                                <div class="contact-icon"><i class="fas fa-map-marker-alt"></i></div>
                                <div class="contact-text">
                                    <h5>Our Location</h5>
                                    <p class="mb-0">Kankanady Gate 4th floor, Kankanady Bypass road, Kankanady, Mangalore, Karnataka, India</p>
                                </div>
                            </li>
                            <li data-aos="fade" data-aos-delay="450">
                                <div class="contact-icon"><i class="fas fa-envelope"></i></div>
                                <div class="contact-text">
                                    <h5>Email Us</h5>
                                    <p class="mb-0">gdedutech24@gmail.com</p>
                                </div>
                            </li>
                            <li data-aos="fade" data-aos-delay="500">
                                <div class="contact-icon"><i class="fas fa-phone-alt"></i></div>
                                <div class="contact-text">
                                    <h5>Call Us</h5>
                                    <p class="mb-0">+91 7204626299</p>
                                </div>
                            </li>
                        </ul>
                        
                        <div class="social-links mt-5">
                            <h5>Connect With Us</h5>
                            <div class="social-icons mt-3">
                                <a href="https://www.facebook.com/people/GD-EDU-TECH/" class="social-icon" data-aos="fade" data-aos-delay="200"><i class="fab fa-facebook-f"></i></a>
                                <a href="https://www.linkedin.com/company/gd-edu-tech/" class="social-icon" data-aos="fade" data-aos-delay="300"><i class="fab fa-linkedin-in"></i></a>
                                <a href="https://www.instagram.com/gd_edu__tech/" class="social-icon" data-aos="fade" data-aos-delay="400"><i class="fab fa-instagram"></i></a>
                                <a href="https://www.youtube.com/@GDEDUTECH" class="social-icon" data-aos="fade" data-aos-delay="500"><i class="fab fa-youtube"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-7" data-aos="fade-left">
                    <div class="premium-card p-4 p-md-5">
                        <h3 class="mb-4">Send Us A Message</h3>
                       <form action="contact.php" method="POST">
    <div class="row g-3">
        <div class="col-md-6">
            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="name" name="name" placeholder="Your Name" required>
                <label for="name">Your Name*</label>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-floating mb-3">
                <input type="email" class="form-control" id="email" name="email" placeholder="Your Email" required>
                <label for="email">Your Email*</label>
            </div>
        </div>
        <div class="col-12">
            <div class="form-floating mb-3">
                <input type="tel" class="form-control" id="phone" name="phone" placeholder="Phone Number" pattern="[0-9]{10}" title="Please enter a 10-digit phone number" required>
                <label for="phone">Phone Number (10 digits)*</label>
            </div>
        </div>
        <div class="col-12">
            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="subject" name="subject" placeholder="Subject" required>
                <label for="subject">Subject*</label>
            </div>
        </div>
        <div class="col-12">
            <div class="form-floating mb-3">
                <textarea class="form-control" id="message" name="message" placeholder="Your Message" style="height: 150px"></textarea>
                <label for="message">Your Message (Optional)</label>
            </div>
        </div>
        <div class="col-12">
            <button type="submit" name="contact_submit" class="btn btn-primary btn-lg px-5">
                Send Message <i class="bi bi-send ms-2"></i>
            </button>
        </div>
    </div>
</form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12" data-aos="fade-up">
                    <div class="premium-card p-0 overflow-hidden">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d59581.22283851267!2d74.77494418116903!3d12.882478463122082!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xaca9ff5b4f31f2cd%3A0xca2c5cd617d9d383!2sGD%20EDU%20TECH!5e0!3m2!1sen!2sin!4v1751472498695!5m2!1sen!2sin"
                            width="100%" 
                            height="450" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-5 bg-light">
        <div class="container py-5">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="section-heading text-center" data-aos="fade-up">Frequently Asked Questions</h2>
                    <p class="lead text-muted" data-aos="fade-up" data-aos-delay="200">Find answers to common questions about GD Edu Tech</p>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-8 mx-auto" data-aos="fade-up" data-aos-delay="400">
                    <div class="accordion" id="faqAccordion">
                        <?php
                        // Fetch FAQs from database
                        $faq_query = "SELECT * FROM FAQs ORDER BY created_at DESC LIMIT 5";
                        $faq_result = mysqli_query($conn, $faq_query);
                        
                        if (mysqli_num_rows($faq_result) > 0) {
                            $first = true;
                            $count = 0;
                            while ($faq = mysqli_fetch_assoc($faq_result)) {
                                $count++;
                                $show = $first ? 'show' : '';
                                $expanded = $first ? 'true' : 'false';
                                $collapsed = $first ? '' : 'collapsed';
                                $first = false;
                        ?>
                        <div class="accordion-item border-0 mb-3 shadow-sm">
                            <h2 class="accordion-header" id="heading<?php echo $count; ?>">
                                <button class="accordion-button <?php echo $collapsed; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $count; ?>" aria-expanded="<?php echo $expanded; ?>" aria-controls="collapse<?php echo $count; ?>">
                                    <?php echo htmlspecialchars($faq['question']); ?>
                                </button>
                            </h2>
                            <div id="collapse<?php echo $count; ?>" class="accordion-collapse collapse <?php echo $show; ?>" aria-labelledby="heading<?php echo $count; ?>" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <?php echo htmlspecialchars($faq['answer']); ?>
                                </div>
                            </div>
                        </div>
                        <?php
                            }
                        } else {
                            // Fallback to static FAQs if none in database
                        ?>
                        <div class="accordion-item border-0 mb-3 shadow-sm">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                    How do I enroll in a course?
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    To enroll in a course, simply create an account on our platform, browse our course catalog, select the course you're interested in, and click the "Enroll" button. Some courses are free, while others may require payment.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 mb-3 shadow-sm">
                            <h2 class="accordion-header" id="headingTwo">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    What payment methods do you accept?
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    We accept various payment methods including credit/debit cards (Visa, MasterCard, American Express), PayPal, and bank transfers. All transactions are processed securely.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 mb-3 shadow-sm">
                            <h2 class="accordion-header" id="headingThree">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    Can I get a refund if I'm not satisfied with a course?
                                </button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Yes, we offer a 30-day money-back guarantee for most courses. If you're not satisfied with a course, you can request a refund within 30 days of enrollment, provided you haven't completed more than 25% of the course content.
                                </div>
                            </div>
                        </div>
                        <?php } ?>
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
</body>

</html>