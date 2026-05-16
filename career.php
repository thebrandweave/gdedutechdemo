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
    <title>Careers - GD Edu Tech</title>
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
        
    <link rel="icon" type="image/png" href="./Images/Logos/GD_Only_logo.png">
    <link rel="icon" type="image/png" sizes="32x32" href="./Images/Logos/GD_Only_logo.png">
    <link rel="icon" type="image/png" sizes="16x16" href="./Images/Logos/GD_Only_logo.png">
    <link rel="shortcut icon" href="./Images/Logos/GD_Only_logo.png">
    <link rel="apple-touch-icon" href="./Images/Logos/GD_Only_logo.png">
    <meta name="msapplication-TileImage" content="./Images/Logos/GD_Only_logo.png">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <!-- Toast Notification -->
    <div id="successToast" class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999; display: none;">
        <div class="toast show bg-success text-white" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-success text-white">
                <i class="bi bi-check-circle-fill me-2"></i>
                <strong class="me-auto">Success</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close" onclick="hideToast()"></button>
            </div>
            <div class="toast-body">
                <p class="mb-0">Your application has been submitted successfully! We'll get back to you soon.</p>
            </div>
        </div>
    </div>
    
    <script>
        // Function to show success toast
        function showSuccessToast() {
            const toast = document.getElementById('successToast');
            toast.style.display = 'block';
            
            // Automatically hide after 5 seconds
            setTimeout(function() {
                hideToast();
            }, 5000);
        }
        
        // Function to hide toast
        function hideToast() {
            const toast = document.getElementById('successToast');
            toast.style.display = 'none';
        }
        
        // Check if redirected from successful form submission
        <?php if(isset($_SESSION['success']) && isset($_SESSION['show_toast'])): ?>
        document.addEventListener('DOMContentLoaded', function() {
            showSuccessToast();
            <?php unset($_SESSION['show_toast']); ?>
        });
        <?php endif; ?>
    </script>

    <!-- Page Header -->
    <section class="page-header position-relative overflow-hidden">
        <div class="container position-relative py-7">
            <div class="row align-items-center">
                <div class="col-md-7" data-aos="fade-right">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-3">
                            <li class="breadcrumb-item"><a href="index.php" class="text-white-50">Home</a></li>
                            <li class="breadcrumb-item active text-white" aria-current="page">Careers</li>
                        </ol>
                    </nav>
                    <h1 class="text-white display-5 fw-bold mb-3">Join Our Team</h1>
                    <p class="text-white-50 lead mb-0">Be part of our mission to transform education through technology and innovation.</p>
                </div>
                <div class="col-md-5" data-aos="fade-left">
                    <img src="./Images/Others/career2.png" alt="Careers" class="career-hero-image">
                </div>
            </div>
        </div>
        <div class="page-header-shape">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
                <path fill="#ffffff" fill-opacity="1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,112C672,96,768,96,864,112C960,128,1056,160,1152,160C1248,160,1344,128,1392,112L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
            </svg>
        </div>
    </section>

    <!-- Why Join Us Section -->
    <section class="py-5">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="section-heading" data-aos="fade-up">Why Join GD Edu Tech?</h2>
                    <p class="lead text-muted" data-aos="fade-up" data-aos-delay="100">Discover the benefits of being part of our innovative team</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6" data-aos="fade-up">
                    <div class="premium-card text-center p-4 h-100">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3 mb-4 mx-auto" style="width: 80px; height: 80px">
                            <i class="bi bi-graph-up-arrow fs-2 text-primary"></i>
                        </div>
                        <h5>Growth Opportunities</h5>
                        <p class="text-muted">Continuous learning and career advancement in a fast-growing company.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="premium-card text-center p-4 h-100">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3 mb-4 mx-auto" style="width: 80px; height: 80px">
                            <i class="bi bi-heart-pulse fs-2 text-primary"></i>
                        </div>
                        <h5>Work-Life Balance</h5>
                        <p class="text-muted">Flexible work arrangements and comprehensive wellness programs.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="premium-card text-center p-4 h-100">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3 mb-4 mx-auto" style="width: 80px; height: 80px">
                            <i class="bi bi-people fs-2 text-primary"></i>
                        </div>
                        <h5>Great Culture</h5>
                        <p class="text-muted">Collaborative environment with diverse and talented professionals.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="premium-card text-center p-4 h-100">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3 mb-4 mx-auto" style="width: 80px; height: 80px">
                            <i class="bi bi-trophy fs-2 text-primary"></i>
                        </div>
                        <h5>Competitive Benefits</h5>
                        <p class="text-muted">Attractive compensation, health insurance, and learning allowances.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Current Openings Section -->
    <section class="py-5 bg-light">
        <div class="container">
            
            <div class="row mb-5">
                <div class="col-lg-6">
                    <h2 class="section-heading" data-aos="fade-up">Current Openings</h2>
                    <p class="lead text-muted" data-aos="fade-up" data-aos-delay="100">Join our team and be part of something extraordinary</p>
                </div>
                <div class="col-lg-6 text-lg-end" data-aos="fade-up" data-aos-delay="200">
                    <div class="btn-group" role="group" aria-label="Job filter">
                        <button type="button" class="btn btn-primary active filter-btn" data-filter="all">All</button>
                        <button type="button" class="btn btn-outline-primary filter-btn" data-filter="Full-time">Full-time</button>
                        <button type="button" class="btn btn-outline-primary filter-btn" data-filter="Part-time">Part-time</button>
                        <button type="button" class="btn btn-outline-primary filter-btn" data-filter="Contract">Contract</button>
                        <button type="button" class="btn btn-outline-primary filter-btn" data-filter="Internship">Internship</button>
                    </div>
                </div>
            </div>
            <div class="row g-4" id="job-listings">
                <!-- No jobs message (hidden by default) -->
                <div id="no-jobs-message" class="col-12 text-center" style="display: none;" data-aos="fade-up">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <span id="no-jobs-text">No job openings available at the moment. Please check back later.</span>
                    </div>
                </div>
                
                <?php
                // Fetch active job listings from the database
                $query = "SELECT * FROM Careers WHERE status = 'Active' AND application_deadline >= CURDATE() ORDER BY created_at DESC";
                $result = mysqli_query($conn, $query);
                
                if (mysqli_num_rows($result) > 0) {
                    while ($job = mysqli_fetch_assoc($result)) {
                        // Format the deadline date
                        $deadline = new DateTime($job['application_deadline']);
                        $formatted_deadline = $deadline->format('M d, Y');
                        
                        // Calculate days remaining
                        $today = new DateTime();
                        $interval = $today->diff($deadline);
                        $days_remaining = $interval->days;
                        $deadline_class = ($days_remaining <= 7) ? 'text-danger' : 'text-muted';
                ?>
                <div class="col-lg-6 job-item" data-category="<?php echo htmlspecialchars($job['job_type']); ?>" data-job-type="<?php echo htmlspecialchars($job['job_type']); ?>" data-aos="fade-up">
                    <div class="job-card-new">
                        <div class="job-tag"><?php echo htmlspecialchars($job['job_type']); ?></div>
                        <div class="job-card-content">
                            <div class="job-location">
                                <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($job['location']); ?>
                            </div>
                            <div class="job-salary-range">
                                ₹ <?php echo str_replace('₹', '', htmlspecialchars($job['salary_range'])); ?>
                            </div>
                            
                            <h3 class="job-title"><?php echo htmlspecialchars($job['job_title']); ?></h3>
                            
                            <p class="job-description"><?php echo nl2br(substr(htmlspecialchars($job['job_description']), 0, 140)) . '...'; ?></p>
                        </div>
                        
                        <div class="job-footer">
                            <div class="deadline <?php echo $deadline_class; ?>">
                                <i class="bi bi-calendar-event"></i> Deadline: <?php echo $formatted_deadline; ?>
                                <?php if ($days_remaining <= 7): ?>
                                    (<?php echo $days_remaining; ?> days left)
                                <?php endif; ?>
                            </div>
                            <a href="apply.php?job_id=<?php echo $job['job_id']; ?>" class="learn-more">
                                Learn More
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php
                    }
                } else {
                    // Show the no-jobs-message div when no jobs are found
                    echo '<script>document.getElementById("no-jobs-message").style.display = "block";</script>';
                }
                ?>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section" data-aos="fade-up">
        <div class="floating-shape shape-1"></div>
        <div class="floating-shape shape-2"></div>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="display-5 fw-bold text-white mb-4">Ready to Join Our Team?</h2>
                    <p class="lead text-white-50 mb-5">Take the first step towards an exciting career with GD Edu Tech.</p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="#" class="btn btn-light btn-lg px-5 rounded-pill">
                            Apply Now <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                        <a href="contact.php" class="btn btn-outline-light btn-lg px-5 rounded-pill">
                            Contact Us
                        </a>
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
    
    <!-- Job Filtering Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize AOS
            AOS.init({
                duration: 800,
                easing: 'ease-in-out',
                once: true
            });
            
            // Job filtering functionality
            const filterButtons = document.querySelectorAll('.filter-btn');
            const jobItems = document.querySelectorAll('.job-item');
            
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons
                    filterButtons.forEach(btn => btn.classList.remove('active', 'btn-primary'));
                    filterButtons.forEach(btn => btn.classList.add('btn-outline-primary'));
                    
                    // Add active class to clicked button
                    this.classList.remove('btn-outline-primary');
                    this.classList.add('active', 'btn-primary');
                    
                    const filter = this.getAttribute('data-filter');
                    
                    // Show/hide job items based on filter
                    jobItems.forEach(item => {
                        if (filter === 'all' || item.getAttribute('data-category') === filter) {
                            item.style.display = 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            });
        });
    </script>

    <style>
        /* Job Card Styling - Matching Course Card Design */
        .job-card-new {
            position: relative;
            background: #ffffff;
            border: none;
            border-radius: 1rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06);
            padding: 0;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .job-card-new:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        
        .job-tag {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--primary);
            backdrop-filter: blur(4px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1;
        }
        
        .job-card-content {
            padding: 1.5rem;
        }
        
        .job-location, .job-salary-range {
            color: #64748b;
            font-size: 0.9rem;
            margin-bottom: 8px;
            display: inline-block;
            margin-right: 15px;
            font-weight: 500;
        }
        
        .job-location i, .job-salary-range i {
            margin-right: 5px;
            color: var(--primary);
        }
        
        .job-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
            margin: 15px 0;
            line-height: 1.4;
        }
        
        .job-description {
            color: #64748b;
            margin-bottom: 20px;
            line-height: 1.6;
            font-size: 0.95rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .job-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
            padding: 1rem 1.5rem;
            border-top: 1px solid #e2e8f0;
        }
        
        .deadline {
            font-size: 0.875rem;
            color: #64748b;
            font-weight: 500;
        }
        
        .deadline i {
            margin-right: 5px;
            color: #94a3b8;
        }
        
        .learn-more {
            display: inline-flex;
            align-items: center;
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        
        .learn-more i {
            margin-left: 6px;
            transition: transform 0.3s ease;
            font-size: 0.85rem;
        }
        
        .learn-more:hover {
            color:rgb(0, 0, 0);
            transform: translateY(-2px);
        }
        
        .learn-more:hover i {
            transform: translateX(5px);
        }
        
        /* Mobile Responsiveness */
        @media (max-width: 767.98px) {
            .job-card-new {
                padding: 20px;
                margin-bottom: 20px;
            }
            
            .job-tag {
                top: 20px;
                right: 20px;
                font-size: 0.7rem;
                padding: 4px 10px;
            }
            
            .job-title {
                font-size: 1.3rem;
                margin: 12px 0;
            }
            
            .job-location, .job-salary-range {
                font-size: 0.8rem;
                margin-bottom: 6px;
            }
            
            .job-footer {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .learn-more {
                margin-top: 10px;
            }
        }

        .career-hero-image {
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
    </style>

    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            easing: 'ease-in-out',
            once: true,
            mirror: false
        });

        // Job filtering functionality
        document.addEventListener('DOMContentLoaded', function() {
            const filterButtons = document.querySelectorAll('[data-filter]');
            const jobCards = document.querySelectorAll('[data-job-type]');
            const noJobsMessage = document.getElementById('no-jobs-message');
            const noJobsText = document.getElementById('no-jobs-text');

            // Function to check if any jobs are visible
            function checkVisibleJobs(filter) {
                let visibleCount = 0;
                jobCards.forEach(card => {
                    if (filter === 'all' || card.getAttribute('data-job-type') === filter) {
                        card.style.display = 'block';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });
                
                // Show or hide the no jobs message
                if (visibleCount === 0) {
                    noJobsMessage.style.display = 'block';
                    if (filter === 'all') {
                        noJobsText.textContent = 'No job openings available at the moment. Please check back later.';
                    } else {
                        noJobsText.textContent = 'No ' + filter + ' positions available at the moment. Please try another category or check back later.';
                    }
                } else {
                    noJobsMessage.style.display = 'none';
                }
                
                return visibleCount;
            }
            
            // Initial check (in case there are no jobs at all)
            if (jobCards.length === 0) {
                noJobsMessage.style.display = 'block';
            }

            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Update active button
                    filterButtons.forEach(btn => {
                        btn.classList.remove('active');
                        btn.classList.remove('btn-primary');
                        btn.classList.add('btn-outline-primary');
                    });
                    this.classList.add('active');
                    this.classList.remove('btn-outline-primary');
                    this.classList.add('btn-primary');

                    const filter = this.getAttribute('data-filter');
                    
                    // Filter job cards and check if any are visible
                    checkVisibleJobs(filter);
                });
            });
        });
    </script>
</body>
</html>