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
    <!-- Google Fonts: Montserrat (labels/emblems), UnifrakturMaguntia (Gothic Blackletter), Great Vibes (Signature Script) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=UnifrakturMaguntia&family=Great+Vibes&display=swap" rel="stylesheet">
    <!-- Custom JavaScript -->
    <script src="./js/main.js" defer></script>
    <link rel="icon" type="image/png" href="./Images/Logos/GD_Only_logo.png">

    <style>
        body {
            background: #f4f7fb;
            font-family: 'Montserrat', sans-serif;
            color: #111827;
        }

        /* Hero styling */
        .page-header {
            background: linear-gradient(135deg, #0d7298 0%, #065d7d 100%);
            padding: 80px 0 50px 0;
            color: white;
        }

        /* Glassmorphism search card */
        .search-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(10px);
            padding: 35px;
            margin-top: -40px;
            z-index: 10;
            position: relative;
        }

        .search-btn {
            background: linear-gradient(135deg, #ff6b35 0%, #ff5216 100%);
            color: white;
            border: none;
            font-weight: 600;
            padding: 12px 25px;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 53, 0.3);
            color: white;
        }

        /* Certificate verification card */
        .verification-container {
            margin-top: 40px;
            margin-bottom: 80px;
        }

        .cert-card {
            background: white;
            border-radius: 25px;
            border: 12px double #e2e8f0;
            padding: 65px 55px;
            position: relative;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .cert-border-outer {
            border: 2px solid #0d7298;
            padding: 25px;
            border-radius: 18px;
            position: relative;
            z-index: 2;
        }

        /* Corner Waves */
        .corner-wave {
            position: absolute;
            width: 280px;
            height: auto;
            z-index: 1;
        }
        .wave-top-right {
            top: 0;
            right: 0;
        }
        .wave-bottom-left {
            bottom: 0;
            left: 0;
        }

        /* Title styling */
        .cert-gothic-title {
            font-family: 'UnifrakturMaguntia', cursive;
            color: #065d7d;
            font-size: 3.8rem;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }

        /* Student name script */
        .cert-student-name {
            font-family: 'Great Vibes', cursive;
            font-size: 3.5rem;
            color: #1e293b;
            margin-top: 15px;
        }

        .cert-body-statement {
            font-size: 1.25rem;
            color: #475569;
            line-height: 1.9;
        }

        .print-btn {
            background: #0d7298;
            color: white;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 10px;
            border: none;
            transition: all 0.3s;
        }

        .print-btn:hover {
            background: #065d7d;
            transform: translateY(-1px);
        }

        /* Error box styling */
        .error-card {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            border-radius: 15px;
            padding: 25px;
            margin-top: 30px;
            text-align: center;
        }

        @media (max-width: 768px) {
            .cert-card {
                padding: 30px 20px;
                border-width: 6px;
            }

            .cert-border-outer {
                padding: 15px 10px;
            }

            .cert-gothic-title {
                font-size: 2.2rem;
            }

            .cert-student-name {
                font-size: 2.2rem;
            }

            .cert-body-statement {
                font-size: 1rem;
            }

            .corner-wave {
                width: 150px;
            }

            .cert-footer-row {
                flex-direction: column;
                align-items: center;
                gap: 30px;
                text-align: center;
            }
            .cert-footer-row .col-3,
            .cert-footer-row .col-6 {
                width: 100%;
                text-align: center !important;
            }
            .cert-footer-row .col-3 div {
                align-items: center !important;
            }
        }

        /* Print styles */
        @media print {
            body * {
                visibility: hidden;
            }
            .cert-card, .cert-card * {
                visibility: visible;
            }
            .cert-card {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                border: none;
                box-shadow: none;
                padding: 0;
            }
            .print-action-section {
                display: none;
            }
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <?php include 'navbar.php'; ?>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container py-4">
            <div class="row">
                <div class="col-lg-8">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="index.php" class="text-white-50">Home</a></li>
                            <li class="breadcrumb-item active text-white" aria-current="page">Verification</li>
                        </ol>
                    </nav>
                    <h1 class="display-6 fw-bold mb-2">Certificate Verification</h1>
                    <p class="text-white-50 lead mb-0">Validate credentials, course admission status, and skills instantly.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content Container -->
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-11">
                <!-- Search Card -->
                <div class="search-card">
                    <h4 class="mb-3 fw-bold"><i class="bi bi-search text-primary me-2"></i>Verify Student Credentials</h4>
                    <form action="verify_certificate.php" method="GET" class="row g-3">
                        <div class="col-md-9">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-shield-lock text-muted"></i></span>
                                <input type="text" class="form-control bg-light border-start-0 ps-0" id="student_id" name="student_id" 
                                       placeholder="Enter Student ID (e.g., GDEDU1001)" value="<?php echo htmlspecialchars($student_id); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-3 d-grid">
                            <button type="submit" class="search-btn">Verify Status</button>
                        </div>
                    </form>
                </div>

                <!-- Error Section -->
                <?php if (!empty($error_message)): ?>
                    <div class="error-card shadow-sm" data-aos="fade-up">
                        <i class="bi bi-exclamation-triangle-fill fs-1 text-danger mb-3 d-block"></i>
                        <h5 class="fw-bold">Verification Failed</h5>
                        <p class="mb-0"><?php echo $error_message; ?></p>
                    </div>
                <?php endif; ?>

                <!-- Verification Result Section -->
                <?php if ($admission): ?>
                    <div class="verification-container" data-aos="fade-up">
                        <div class="print-action-section text-end mb-3">
                            <button onclick="window.print();" class="print-btn"><i class="bi bi-printer me-2"></i>Print Certificate</button>
                        </div>
                        
                        <div class="cert-card">
                            <!-- Background Swoosh Accents -->
                            <!-- Top Right Swoosh SVG -->
                            <svg class="corner-wave wave-top-right" viewBox="0 0 300 200" xmlns="http://www.w3.org/2000/svg">
                                <path d="M 0 0 C 150 0 250 50 300 200 L 300 0 Z" fill="#0d7298" />
                                <path d="M 50 0 C 170 0 260 40 300 150 L 300 130 C 255 35 155 0 0 0 Z" fill="#ff6b35" />
                            </svg>
                            <!-- Bottom Left Swoosh SVG -->
                            <svg class="corner-wave wave-bottom-left" viewBox="0 0 300 200" xmlns="http://www.w3.org/2000/svg">
                                <path d="M 0 0 C 150 200 250 200 300 200 L 0 200 Z" fill="#0d7298" />
                                <path d="M 0 50 C 130 200 220 200 300 200 L 280 200 C 200 200 110 160 0 0 Z" fill="#ff6b35" />
                            </svg>

                            <div class="cert-border-outer">
                                <!-- Top Logo Bar -->
                                <div class="row align-items-center mb-4 cert-header-row">
                                    <div class="col-3 text-start">
                                        <img src="https://upload.wikimedia.org/wikipedia/commons/2/23/MSME_Logo_India.svg" style="height: 65px;" alt="MSME Logo">
                                    </div>
                                    <div class="col-9 text-center">
                                        <div class="d-flex flex-column align-items-center justify-content-center">
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="./Images/Logos/GD_Only_logo.png" style="height: 42px;" alt="GD Only Logo">
                                                <span class="fs-2 fw-extrabold text-dark" style="letter-spacing: 1px; font-weight: 800;">GD EDU TECH</span>
                                            </div>
                                            <span class="text-uppercase font-monospace text-primary mt-1" style="font-size: 0.72rem; letter-spacing: 3px; font-weight: 600;">Connecting Passion with Progress</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Subtitle Banner -->
                                <div class="text-center mb-4 px-3 py-2" style="border-top: 1.5px solid #0d7298; border-bottom: 1.5px solid #0d7298; background: rgba(248, 250, 252, 0.8);">
                                    <div class="fw-bold text-dark" style="font-size: 0.88rem; letter-spacing: 1px;">A TECHNICAL TRAINING UNIT OF PRO GEE DEE VENTURES</div>
                                    <div class="text-muted mt-1" style="font-size: 0.65rem; letter-spacing: 0.5px; line-height: 1.4;">
                                        REGISTERED WITH MINISTRY OF MSME, GOVT. OF INDIA | UDYAM REG NO: UDYAM-KR-11-0102678<br>
                                        ISO 9001:2015 CERTIFIED ORGANIZATION
                                    </div>
                                </div>

                                <!-- Registration ID -->
                                <div class="text-start mb-4 ps-2">
                                    <span class="fw-bold text-muted text-uppercase" style="font-size: 0.8rem; letter-spacing: 1px;">Reg no:</span>
                                    <span class="fw-bold text-dark fs-5 ms-1"><?php echo htmlspecialchars($admission['student_id']); ?></span>
                                </div>

                                <!-- Gothic Certificate of Completion Title -->
                                <div class="text-center mb-4">
                                    <h1 class="cert-gothic-title">Certificate of Completion</h1>
                                </div>

                                <div class="text-center text-uppercase text-muted fw-bold mb-4" style="font-size: 0.85rem; letter-spacing: 3px;">
                                    THIS CERTIFICATE IS PROUDLY PRESENTED TO
                                </div>

                                <!-- Student Name script font -->
                                <div class="text-center">
                                    <div class="cert-student-name"><?php echo htmlspecialchars($admission['student_name']); ?></div>
                                </div>
                                <div class="row justify-content-center mb-5">
                                    <div class="col-8 border-bottom border-warning border-3" style="height: 1px;"></div>
                                </div>

                                <!-- Description content statement -->
                                <div class="cert-body-statement text-center px-4 mb-5">
                                    has successfully completed the professional certificate course in
                                    <div class="fw-bold text-dark fs-4 my-2" style="font-family: 'Montserrat', sans-serif;">[<?php echo htmlspecialchars($admission['course_applied']); ?>]</div>
                                    conducted from <span class="fw-bold text-dark"><?php echo !empty($admission['start_date']) ? date('d-m-Y', strtotime($admission['start_date'])) : '[START DATE]'; ?></span> to <span class="fw-bold text-dark"><?php echo !empty($admission['end_date']) ? date('d-m-Y', strtotime($admission['end_date'])) : '[END DATE]'; ?></span>.
                                    <div class="mt-3">
                                        He/She has demonstrated proficiency in <span class="fw-bold text-primary">[<?php echo !empty($admission['key_skills']) ? htmlspecialchars($admission['key_skills']) : 'KEY SOFTWARE/SKILLS'; ?>]</span>.
                                    </div>
                                </div>

                                <!-- Seals & Signatures Footer -->
                                <div class="cert-footer-row row align-items-end mt-5 pt-4">
                                    <!-- QR Code (Left) -->
                                    <div class="col-3 text-start">
                                        <?php 
                                        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? '') == 443) ? "https://" : "http://";
                                        $domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
                                        $path = (strpos($domain, 'gdedutech.com') !== false) ? "/verify_certificate.php" : "/gdedutechdemo/verify_certificate.php";
                                        $verify_url = $protocol . $domain . $path . "?student_id=" . $admission['student_id'];
                                        $qr_api_url = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($verify_url);
                                        ?>
                                        <div class="d-flex flex-column align-items-start gap-1">
                                            <img src="<?php echo $qr_api_url; ?>" alt="Verification QR Code" style="width: 105px; height: 105px; border: 1px solid #dee2e6; border-radius: 4px; padding: 2px;">
                                            <a href="adminPanel/Admissions/download_qr.php?student_id=<?php echo urlencode($admission['student_id']); ?>" class="btn btn-sm btn-outline-secondary py-0 px-2 mt-1 print-action-section" style="font-size: 0.65rem;">
                                                <i class="bi bi-download me-1"></i>Download QR
                                            </a>
                                        </div>
                                    </div>
                                    <!-- Accreditation Badges (Center) -->
                                    <div class="col-6 text-center">
                                        <div class="d-flex align-items-center justify-content-center gap-3">
                                            <!-- IAF Logo SVG -->
                                            <svg viewBox="0 0 100 100" style="height: 48px; width: 48px;">
                                                <circle cx="50" cy="50" r="45" fill="#0d7298" />
                                                <circle cx="50" cy="50" r="40" fill="none" stroke="#fff" stroke-width="2" />
                                                <text x="50" y="55" font-family="'Montserrat', sans-serif" font-weight="800" font-size="22" fill="#fff" text-anchor="middle">IAF</text>
                                                <text x="50" y="78" font-family="'Montserrat', sans-serif" font-weight="500" font-size="7" fill="#fff" text-anchor="middle">ACCREDITATION</text>
                                            </svg>
                                            <!-- EIAC Logo SVG -->
                                            <svg viewBox="0 0 100 50" style="height: 48px; width: 96px;">
                                                <ellipse cx="50" cy="25" rx="45" ry="20" fill="none" stroke="#ff6b35" stroke-width="3" />
                                                <text x="50" y="32" font-family="'Montserrat', sans-serif" font-weight="800" font-size="24" fill="#0d7298" text-anchor="middle">eiac</text>
                                            </svg>
                                            <!-- ISO 9001 Seal SVG -->
                                            <svg viewBox="0 0 100 100" style="height: 48px; width: 48px;">
                                                <circle cx="50" cy="50" r="45" fill="none" stroke="#0d7298" stroke-width="3" />
                                                <circle cx="50" cy="50" r="38" fill="none" stroke="#ff6b35" stroke-width="1.5" />
                                                <text x="50" y="35" font-family="'Montserrat', sans-serif" font-weight="700" font-size="8" fill="#0d7298" text-anchor="middle">CERTIFIED</text>
                                                <text x="50" y="55" font-family="'Montserrat', sans-serif" font-weight="800" font-size="14" fill="#ff6b35" text-anchor="middle">ISO 9001</text>
                                                <text x="50" y="72" font-family="'Montserrat', sans-serif" font-weight="700" font-size="8" fill="#0d7298" text-anchor="middle">CERTIFIED</text>
                                            </svg>
                                        </div>
                                    </div>
                                    <!-- Signature Block (Right) -->
                                    <div class="col-3 text-center">
                                        <div class="d-flex flex-column align-items-center">
                                            <div style="width: 100%; border-bottom: 2px solid #ff6b35; margin-bottom: 8px;"></div>
                                            <div class="fw-bold text-dark" style="font-size: 0.95rem;">Managing Director</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
