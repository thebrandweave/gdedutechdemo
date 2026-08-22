<?php
session_start();
require_once './Configurations/config.php';

$student_id = "";
$admission = null;
$error_message = "";

if (isset($_GET['student_id'])) {
    $student_id = mysqli_real_escape_string($conn, trim($_GET['student_id']));
    if (!empty($student_id)) {
        $query = "SELECT * FROM student_admissions WHERE student_id = '$student_id'";
        $result = mysqli_query($conn, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            $admission = mysqli_fetch_assoc($result);
        } else {
            $error_message = "No record found for Student ID: " . htmlspecialchars($student_id) . ". Please make sure the ID is correct (e.g., GDEDU1001).";
        }
    } else {
        $error_message = "Please enter a valid Student ID.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Certificate - GD Edu Tech</title>
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
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=UnifrakturMaguntia&family=Great+Vibes&display=swap" rel="stylesheet">
    <!-- Custom JavaScript -->
    <script src="./js/main.js" defer></script>
    <link rel="icon" type="image/png" href="./Images/Logos/GD_Only_logo.png">

    <style>
        body {
            background: #f8fafc;
            font-family: 'Montserrat', sans-serif;
            color: #0f172a;
        }

        /* Reference Layout Styling for Search Card & Wave Header */
        .verification-hero-wave {
            background: linear-gradient(90deg, rgba(224, 242, 254, 0.8) 0%, rgba(255, 255, 255, 1) 50%, rgba(252, 231, 243, 0.8) 100%);
            padding: 20px 0 40px 0;
            position: relative;
        }

        .verification-search-card {
            background: #ffffff;
            border-radius: 106px;
            border-top-left-radius: 0px;
            border-bottom-left-radius: 130px;

            border: 1.5px solid #1e293b;
            box-shadow: 0 15px 40px -10px rgba(15, 23, 42, 0.08);
            padding: 35px 40px 35px 120px;
            position: relative;
            margin-top: -55px;
            z-index: 5;

            clip-path: polygon(100% 100% at 65% 50%);
        }

        .shield-icon-badge {
            width: 48px;
            height: 48px;
            background: #e0f2fe;
            border: 1.5px solid #0284c7;
            color: #0284c7;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .search-key-box {
            border: 1.5px solid #64748b;
            border-radius: 14px;
            overflow: hidden;
            background: #ffffff;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .search-key-box:focus-within {
            border-color: #0284c7;
            box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.12);
        }

        .search-key-box .input-group-text {
            background: #ffffff;
            border: none;
            border-right: 1.5px solid #64748b !important;
            padding-left: 18px;
            padding-right: 18px;
        }

        .search-key-box .form-control {
            border: none;
            font-size: 0.95rem;
            color: #0f172a;
        }

        .search-key-box .form-control:focus {
            box-shadow: none;
        }

        .verify-status-btn {
            background: #005b8e;
            color: #ffffff;
            border-radius: 30px;
            border: none;
            font-weight: 700;
            padding: 12px 28px;
            transition: all 0.3s ease;
        }

        .verify-status-btn:hover {
            background: #02456c;
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 91, 142, 0.3);
        }

        @media (max-width: 991px) {
            .verification-search-card {
                padding: 30px 25px;
                border-radius: 28px;
            }
        }

        /* Student Profile Verification Card */
        .student-profile-card {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 20px 50px -10px rgba(15, 23, 42, 0.08);
            border: 1px solid #e2e8f0;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .verified-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 16px;
            font-size: 0.95rem;
            font-weight: 800;
            letter-spacing: 2px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .verified-badge-icon {
            font-size: 1.3rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.15); }
            100% { transform: scale(1); }
        }

        .profile-avatar-container {
            display: flex;
            justify-content: center;
            margin-top: 10px;
            margin-bottom: 15px;
        }

        .profile-avatar-initials {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
            color: #0369a1;
            font-size: 2.2rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 4px solid #f0f9ff;
            box-shadow: 0 8px 20px rgba(3, 105, 161, 0.15);
        }

        .profile-image-actual {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #f0f9ff;
            box-shadow: 0 8px 20px rgba(3, 105, 161, 0.15);
        }

        .student-profile-name {
            font-size: 1.5rem;
            font-weight: 800;
            color: #0f172a;
        }

        .badge-student-id {
            font-size: 0.9rem;
            border-radius: 50px;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
        }

        .info-table-box {
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            background: #ffffff;
        }

        .info-table-row {
            border-bottom: 1px solid #f1f5f9;
        }

        .info-table-row:last-child {
            border-bottom: none;
        }

        .info-table-label {
            background: #f8fafc;
            color: #475569;
            font-weight: 700;
            font-size: 0.9rem;
        }

        .info-table-value {
            color: #0f172a;
            font-weight: 600;
            font-size: 0.95rem;
        }

        /* Error Box Styling */
        .error-card {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            border-radius: 20px;
            padding: 30px;
            margin-top: 40px;
            text-align: center;
        }

        @media print {
            body * {
                visibility: hidden;
            }
            .student-profile-card, .student-profile-card * {
                visibility: visible;
            }
            .student-profile-card {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                border: none;
                box-shadow: none;
            }
        }

        .hero-award-img:hover {
            transform: translateY(-8px) scale(1.03);
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <?php include 'navbar.php'; ?>

    <!-- Redesigned Executive Hero Header -->
    <section class="about-page-header position-relative overflow-hidden w-100 my-0">
        <div class="about-header-glow-1"></div>
        <div class="about-header-glow-3"></div>
        <div class="about-header-pattern"></div>

        <div class="container position-relative z-2 py-4">
            <div class="row align-items-center text-start g-4">
                <div class="col-lg-7" data-aos="fade-right">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb about-breadcrumb px-3 py-1.5 rounded-pill mb-3 d-inline-flex">
                            <li class="breadcrumb-item"><a href="index.php" class="text-black text-decoration-none"><i class="bi bi-house-door-fill me-1"></i> Home</a></li>
                            <li class="breadcrumb-item active text-black" aria-current="page">Verification</li>
                        </ol>
                    </nav>

                    <h1 class="display-4 fw-bold text-black mb-3">
                        Certificate <span class="cta-gold-text">Verification</span>
                    </h1>

                    <p class="lead text-black-50 mb-4" style="max-width: 650px;">
                        Validate credentials, course admission status, training duration, and skill accreditation instantly through our verified database.
                    </p>
                </div>

                <!-- Right Column: Award Image -->
                <div class="col-lg-5 text-center text-lg-end" data-aos="fade-left" data-aos-delay="200">
                    <div class="position-relative d-inline-block">
                        <div class="position-absolute top-50 start-50 translate-middle rounded-circle  bg-opacity-20 blur-2xl" style="width: 140px; height: 140px; filter: blur(40px); z-index: 1;"></div>
                        <img src="./Images/Others/award2.png" alt="Award &amp; Accreditation" class="img-fluid position-relative z-2 hero-award-img" style="max-height: 150px;transition: transform 0.4s ease;">
                    </div>
                </div>
            </div>
        </div>

        <div class="page-header-shape position-absolute bottom-0 start-0 w-100">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 120" preserveAspectRatio="none" style="height: 40px; display: block; width: 100%;">
                <path fill="#f8fafc" fill-opacity="1" d="M0,32L48,42.7C96,53,192,75,288,80C384,85,480,75,576,58.7C672,43,768,21,864,21.3C960,21,1056,43,1152,53.3C1248,64,1344,64,1392,64L1440,64L1440,120L1392,120C1344,120,1248,120,1152,120C1056,120,960,120,864,120C768,120,672,120,576,120C480,120,384,120,288,120C192,120,96,120,48,120L0,120Z"></path>
            </svg>
        </div>
    </section>

    <!-- Main Content Container with Wave Ribbon -->
    <div class="verification-hero-wave mb-4">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10 position-relative">

                    <!-- Magnifying Glass Overlay Image on Left Edge -->
                    <div class="position-absolute start-0 top-50 translate-middle-y d-none d-lg-block pointer-events-none" style="left: -111px !important; top:113px !important; z-index: 10;" data-aos="fade-right" data-aos-duration="1000">
                        <img src="./Images/Others/magnify.png" alt="Magnifying Glass" class="img-fluid" style="max-height: 342px; filter: drop-shadow(0 15px 30px rgba(0, 0, 0, 0.15));">
                    </div>

                    <!-- Verification Search Card Box -->
                    <div class="verification-search-card" data-aos="fade-right" data-aos-duration="1200" data-aos-delay="200">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="shield-icon-badge">
                                <i class="bi bi-shield-check fs-4"></i>
                            </div>
                            <div>
                                <h4 class="mb-1 fw-bold" style="color: #0f172a; font-size: 1.35rem;">Verify Student Credentials</h4>
                                <p class="text-muted small mb-0" style="font-size: 0.88rem; color: #64748b;">Enter the Student Identification Number to inspect valid records.</p>
                            </div>
                        </div>

                        <form action="verify_certificate.php#verification-results" method="GET" class="row g-3 align-items-center">
                            <div class="col-lg-8 col-md-7">
                                <div class="input-group search-key-box">
                                    <span class="input-group-text"><i class="bi bi-person-fill text-primary fs-5"></i></span>
                                    <input type="text" class="form-control px-3 py-2.5 fw-semibold" id="student_id" name="student_id" 
                                           placeholder="Enter Student ID (e.g., GDEDU1001)" value="<?php echo htmlspecialchars($student_id); ?>" required style="color: #0f172a !important;">
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-5 d-grid">
                                <button type="submit" class="verify-status-btn d-flex align-items-center justify-content-center gap-2">
                                    <span>Verify Status</span>
                                    <i class="bi bi-arrow-right fs-5"></i>
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Container for Results -->
    <div class="container pb-5" id="verification-results">
        <div class="row justify-content-center">
            <div class="col-lg-10">

                <!-- Error Section -->
                <?php if (!empty($error_message)): ?>
                    <div class="error-card shadow-sm" data-aos="fade-up">
                        <i class="bi bi-exclamation-triangle-fill fs-1 text-danger mb-3 d-block"></i>
                        <h4 class="fw-bold">Verification Failed</h4>
                        <p class="mb-0 text-muted fs-5"><?php echo $error_message; ?></p>
                    </div>
                <?php endif; ?>

                <!-- Verification Result Section -->
                <?php if ($admission): ?>
                    <div class="my-5" data-aos="fade-up">
                        <div class="student-profile-card">
                            <div class="verified-header">
                                <i class="bi bi-patch-check-fill verified-badge-icon"></i>
                                <span>OFFICIAL RECORD VERIFIED &amp; VALIDATED</span>
                            </div>
                            
                            <div class="card-body p-4 p-md-5">
                                <div class="row align-items-center g-4">
                                    
                                    <!-- Left Column: Student Avatar, Name, ID & Print -->
                                    <div class="col-lg-4 text-center border-end-lg pb-4 pb-lg-0">
                                        <div class="profile-avatar-container">
                                            <?php if (!empty($admission['profile_image'])): ?>
                                                <img src="./uploads/profiles/<?php echo htmlspecialchars($admission['profile_image']); ?>" alt="Profile" class="profile-image-actual">
                                            <?php else: ?>
                                                <div class="profile-avatar-initials">
                                                    <?php
                                                    $words = explode(" ", $admission['student_name']);
                                                    $initials = "";
                                                    foreach ($words as $w) {
                                                        $initials .= strtoupper(substr($w, 0, 1));
                                                    }
                                                    echo htmlspecialchars(substr($initials, 0, 2));
                                                    ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <h3 class="student-profile-name mb-2"><?php echo htmlspecialchars($admission['student_name']); ?></h3>
                                        
                                         <div class="mb-3">
                                             <span class="badge badge-student-id text-secondary px-4 py-2 fs-6">
                                                 ID: <span class="fw-bold text-primary" id="copy-student-id"><?php echo htmlspecialchars($admission['student_id']); ?></span>
                                                 <button class="btn btn-link p-0 ms-2 border-0 align-baseline fs-5 text-decoration-none" onclick="copyStudentId()" title="Copy Student ID">
                                                     <i class="bi bi-clipboard text-muted" id="copy-icon"></i>
                                                 </button>
                                             </span>
                                         </div>

                                         <?php if (!empty($admission['certificate_file'])): ?>
                                             <div class="d-flex flex-column gap-2 mt-3">
                                                 <a href="./uploads/certificates/<?php echo htmlspecialchars($admission['certificate_file']); ?>" download class="btn btn-outline-success rounded-pill px-4 py-2 fw-semibold d-inline-flex align-items-center justify-content-center gap-2">
                                                     <i class="bi bi-download"></i>
                                                     <span>Download Certificate</span>
                                                 </a>
                                             </div>
                                         <?php else: ?>
                                             <button onclick="window.print();" class="btn btn-primary rounded-pill px-4 py-2.5 fw-bold d-inline-flex align-items-center justify-content-center gap-2 shadow-sm mt-3">
                                                 <i class="bi bi-printer-fill fs-5"></i>
                                                 <span>Print Certificate</span>
                                             </button>
                                         <?php endif; ?>
                                     </div>

                                     <!-- Right Column: Structured Table Details -->
                                     <div class="col-lg-8">
                                         <div class="info-table-box">

                                             <?php if (!empty(trim($admission['college']))): ?>
                                             <div class="row g-0 info-table-row">
                                                 <div class="col-md-4 p-3 info-table-label d-flex align-items-center">
                                                     <i class="bi bi-building-fill text-primary me-2 fs-5"></i>
                                                     <span>Institution</span>
                                                 </div>
                                                 <div class="col-md-8 p-3 info-table-value">
                                                     <?php echo htmlspecialchars($admission['college']); ?>
                                                 </div>
                                             </div>
                                             <?php endif; ?>

                                             <div class="row g-0 info-table-row">
                                                 <div class="col-md-4 p-3 info-table-label d-flex align-items-center">
                                                     <i class="bi bi-journal-bookmark-fill text-primary me-2 fs-5"></i>
                                                     <span>Course Enrolled</span>
                                                 </div>
                                                 <div class="col-md-8 p-3 info-table-value text-primary fw-bold">
                                                     <?php echo htmlspecialchars($admission['course_applied']); ?>
                                                 </div>
                                             </div>

                                             <div class="row g-0 info-table-row">
                                                 <div class="col-md-4 p-3 info-table-label d-flex align-items-center">
                                                     <i class="bi bi-envelope-fill text-primary me-2 fs-5"></i>
                                                     <span>Email Address</span>
                                                 </div>
                                                 <div class="col-md-8 p-3 info-table-value text-break">
                                                     <a href="mailto:<?php echo htmlspecialchars($admission['email_id']); ?>" class="text-decoration-none text-primary">
                                                         <?php echo htmlspecialchars($admission['email_id']); ?>
                                                     </a>
                                                 </div>
                                             </div>

                                             <div class="row g-0 info-table-row">
                                                 <div class="col-md-4 p-3 info-table-label d-flex align-items-center">
                                                     <i class="bi bi-telephone-fill text-primary me-2 fs-5"></i>
                                                     <span>Phone Number</span>
                                                 </div>
                                                 <div class="col-md-8 p-3 info-table-value">
                                                     <?php echo htmlspecialchars($admission['phone_number']); ?>
                                                 </div>
                                             </div>

                                             <div class="row g-0 info-table-row">
                                                 <div class="col-md-4 p-3 info-table-label d-flex align-items-center">
                                                     <i class="bi bi-calendar3 text-primary me-2 fs-5"></i>
                                                     <span>Training Duration</span>
                                                 </div>
                                                 <div class="col-md-8 p-3 info-table-value">
                                                     <?php echo date('d M Y', strtotime($admission['start_date'])); ?>
                                                     <strong class="mx-2 text-muted">to</strong>
                                                     <?php echo date('d M Y', strtotime($admission['end_date'])); ?>
                                                 </div>
                                             </div>

                                             <?php if (!empty(trim($admission['internship']))): ?>
                                             <div class="row g-0 info-table-row">
                                                 <div class="col-md-4 p-3 info-table-label d-flex align-items-center">
                                                     <i class="bi bi-briefcase-fill text-primary me-2 fs-5"></i>
                                                     <span>Internship</span>
                                                 </div>
                                                 <div class="col-md-8 p-3 info-table-value">
                                                     <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill">
                                                         <i class="bi bi-check-circle-fill me-1"></i>
                                                         <?php echo htmlspecialchars($admission['internship']); ?>
                                                     </span>
                                                 </div>
                                             </div>
                                             <?php endif; ?>

                                             <div class="row g-0 info-table-row">
                                                 <div class="col-md-4 p-3 info-table-label d-flex align-items-center">
                                                     <i class="bi bi-tags-fill text-primary me-2 fs-5"></i>
                                                     <span>Key Skills</span>
                                                 </div>
                                                 <div class="col-md-8 p-3 info-table-value">
                                                     <div class="d-flex flex-wrap gap-2">
                                                         <?php
                                                         $skills = explode(",", $admission['key_skills']);
                                                         foreach ($skills as $skill) {
                                                             $skill = trim($skill);
                                                             if (!empty($skill)) {
                                                                 echo '<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 rounded-pill">'
                                                                     . htmlspecialchars($skill) .
                                                                     '</span>';
                                                             }
                                                         }
                                                         ?>
                                                     </div>
                                                 </div>
                                             </div>

                                         </div>
                                     </div>

                                 </div> <!-- /row -->

                                 <!-- Contact Disclaimer -->
                                 <div class="text-center text-muted small mt-4 pt-3 border-top">
                                     <i class="bi bi-info-circle-fill me-1 text-primary"></i> If any mistakes, please contact GD EDU TECH
                                 </div>

                             </div> <!-- /card-body -->
                         </div> <!-- /student-profile-card -->

                         <!-- Issued Certificate Document Preview Card -->
                         <?php if (!empty($admission['certificate_file'])): 
                             $certExt = strtolower(pathinfo($admission['certificate_file'], PATHINFO_EXTENSION));
                             $certPath = "./uploads/certificates/" . htmlspecialchars($admission['certificate_file']);
                         ?>
                            
                            
                             </div>
                         <?php endif; ?>

                     </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        if (typeof AOS !== 'undefined') {
            AOS.init({
                duration: 1000,
                once: true
            });
        }
        function copyStudentId() {
            var studentIdText = document.getElementById("copy-student-id").innerText;
            navigator.clipboard.writeText(studentIdText).then(function() {
                var copyIcon = document.getElementById("copy-icon");
                copyIcon.className = "bi bi-check-lg text-success";
                setTimeout(function() {
                    copyIcon.className = "bi bi-clipboard text-muted";
                }, 2000);
            }).catch(function(err) {
                console.error('Could not copy Student ID: ', err);
            });
        }

        function printCertificateFile(e, fileUrl) {
            var isPdf = fileUrl.toLowerCase().endsWith('.pdf');
            if (isPdf) {
                e.preventDefault();
                var printWin = window.open(fileUrl, '_blank');
                if (printWin) {
                    printWin.focus();
                    printWin.onload = function() {
                        printWin.print();
                    };
                }
            }
        }

        <?php if ($admission || !empty($error_message)): ?>
        document.addEventListener('DOMContentLoaded', function() {
            var resultElem = document.getElementById('verification-results');
            if (resultElem) {
                setTimeout(function() {
                    resultElem.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 100);
            }
        });
        <?php endif; ?>
    </script>
</body>

</html>