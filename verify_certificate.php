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
            background: linear-gradient(135deg, #c94a55 0%, #d4634e 100%);
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

        /* Student Profile Card Styling */
        .student-profile-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .student-profile-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.08);
        }
        .verified-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 12px;
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 2px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .verified-badge-icon {
            font-size: 1.1rem;
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
            margin-top: 15px;
            margin-bottom: 15px;
        }
        .profile-avatar-initials {
            width: 100px;
            height: 75px;
            border-radius: 50%;
            background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
            color: #0369a1;
            font-size: 1.8rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid #f0f9ff;
            box-shadow: 0 5px 15px rgba(3, 105, 161, 0.1);
        }
        .student-profile-name {
            font-size: 1.4rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 5px;
        }
        .badge-student-id {
            font-size: 0.85rem;
            border-radius: 8px;
        }
        .info-list {
            text-align: left;
            margin-top: 20px;
        }
        .info-item {
            margin-bottom: 16px;
            border-bottom: 1px dashed #f1f5f9;
            padding-bottom: 12px;
        }
        .info-item:last-child {
            margin-bottom: 0;
            border-bottom: none;
            padding-bottom: 0;
        }
        .info-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
        }
        .info-value {
            font-size: 0.95rem;
            color: #334155;
            font-weight: 500;
        }
        .info-value a {
            color: #0284c7;
            text-decoration: none;
            transition: color 0.2s;
        }
        .info-value a:hover {
            color: #0369a1;
            text-decoration: underline;
        }
        .skill-badge {
            display: inline-block;
            background: #f1f5f9;
            color: #475569;
            font-size: 0.78rem;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 6px;
            margin-right: 6px;
            margin-top: 6px;
            border: 1px solid #e2e8f0;
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
    <div class="verification-container animate__animated animate__fadeIn" data-aos="fade-up">
        <div class="row">
            <!-- Left: Student Info Card -->
            <div class="col-12 mb-4 print-action-section">
                <div class="student-profile-card">
                    <div class="verified-header py-3 fs-5">
                        <i class="bi bi-patch-check-fill verified-badge-icon me-2"></i>
                        <span class="fw-bold tracking-wider">RECORD VERIFIED</span>
                    </div>
                    <div class="card-body p-4 p-md-5">
                        <div class="row align-items-center">
                            
                            <!-- Left Column: Avatar, Name, ID, Actions -->
                            <div class="col-md-5 text-center border-end-md pb-4 pb-md-0">
                                <!-- Avatar/Initials -->
                                <div class="profile-avatar-container mx-auto mb-3" style="width: 100px; height: 100px; font-size: 2rem;">
                                    <div class="profile-avatar-initials d-flex align-items-center justify-content-center h-100 fw-bold">
                                        <?php
                                        $words = explode(" ", $admission['student_name']);
                                        $initials = "";
                                        foreach ($words as $w) {
                                            $initials .= strtoupper(substr($w, 0, 1));
                                        }
                                        echo htmlspecialchars(substr($initials, 0, 2));
                                        ?>
                                    </div>
                                </div>
                                
                                <h3 class="student-profile-name fw-bold mb-3"><?php echo htmlspecialchars($admission['student_name']); ?></h3>
                                
                                <div class="mb-4">
                                    <span class="badge badge-student-id bg-light text-secondary border px-4 py-2 fs-6">
                                        ID: <span class="fw-bold text-primary" id="copy-student-id"><?php echo htmlspecialchars($admission['student_id']); ?></span>
                                        <button class="btn btn-link p-0 ms-2 border-0 align-baseline fs-5" onclick="copyStudentId()" title="Copy Student ID">
                                            <i class="bi bi-clipboard text-muted" id="copy-icon"></i>
                                        </button>
                                    </span>
                                </div>

                                <div class="d-flex gap-3 justify-content-center mt-3">
                                    <a href="adminPanel/Admissions/download_qr.php?student_id=<?php echo urlencode($admission['student_id']); ?>" class="btn btn-outline-primary btn-lg rounded-pill px-4 fs-6 fw-semibold">
                                        <i class="bi bi-download me-2"></i>Download QR
                                    </a>
                                    <!-- <button onclick="window.print();" class="btn btn-primary btn-lg rounded-pill px-4 fs-6 fw-semibold">
                                        <i class="bi bi-printer me-2"></i>Print Page
                                    </button> -->
                                </div>
                            </div>

                            <!-- Right Column: Info Details arranged 2 in a line (Bigger Font) -->
                            <div class="col-md-7 ps-md-5">
                                <div class="row g-4 info-list">
                                    <?php if (!empty(trim($admission['college']))): ?>
                                    <div class="col-12 info-item d-flex align-items-baseline flex-wrap">
                                        <div class="info-label text-secondary fw-semibold fs-6 me-2"><i class="bi bi-building text-primary me-2"></i>College / Institution:</div>
                                        <div class="info-value text-dark fs-5 fw-bold"><?php echo htmlspecialchars($admission['college']); ?></div>
                                    </div>
                                    <?php endif; ?>
                                    <div class="col-12 info-item d-flex align-items-baseline flex-wrap">
                                        <div class="info-label text-secondary fw-semibold fs-6 me-2"><i class="bi bi-journal-bookmark text-primary me-2"></i>Course Enrolled:</div>
                                        <div class="info-value text-primary fs-5 fw-bold"><?php echo htmlspecialchars($admission['course_applied']); ?></div>
                                    </div>
                                    <div class="col-12 info-item d-flex align-items-baseline flex-wrap">
                                        <div class="info-label text-secondary fw-semibold fs-6 me-2"><i class="bi bi-envelope text-primary me-2"></i>Email Address:</div>
                                        <div class="info-value text-break fs-5 fw-semibold"><a href="mailto:<?php echo htmlspecialchars($admission['email_id']); ?>" class="text-decoration-none"><?php echo htmlspecialchars($admission['email_id']); ?></a></div>
                                    </div>
                                    <div class="col-12 info-item d-flex align-items-baseline flex-wrap">
                                        <div class="info-label text-secondary fw-semibold fs-6 me-2"><i class="bi bi-telephone text-primary me-2"></i>Phone Number:</div>
                                        <div class="info-value text-dark fs-5 fw-semibold"><?php echo htmlspecialchars($admission['phone_number']); ?></div>
                                    </div>
                                    <div class="col-12 info-item d-flex align-items-baseline flex-wrap">
                                        <div class="info-label text-secondary fw-semibold fs-6 me-2"><i class="bi bi-calendar3 text-primary me-2"></i>Training Duration:</div>
                                        <div class="info-value text-dark fs-6 fw-semibold">
                                            <?php echo date('d M Y', strtotime($admission['start_date'])); ?> to <?php echo date('d M Y', strtotime($admission['end_date'])); ?>
                                        </div>
                                    </div>
                                    <?php if (!empty(trim($admission['internship']))): ?>
                                    <div class="col-12 info-item d-flex align-items-baseline flex-wrap">
                                        <div class="info-label text-secondary fw-semibold fs-6 me-2"><i class="bi bi-briefcase text-primary me-2"></i>Internship:</div>
                                        <div class="info-value">
                                            <span class="badge bg-success-subtle text-success border border-success-subtle fs-6 px-3 py-2 fw-semibold"><?php echo htmlspecialchars($admission['internship']); ?></span>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    <div class="col-12 info-item d-flex align-items-baseline flex-wrap mt-4">
                                        <div class="info-label text-secondary fw-semibold fs-6 me-2"><i class="bi bi-tags text-primary me-2"></i>Key Software / Skills:</div>
                                        <div class="info-value d-flex flex-wrap gap-2">
                                            <?php 
                                            $skills = explode(",", $admission['key_skills']);
                                            foreach ($skills as $skill) {
                                                $skill = trim($skill);
                                                if (!empty($skill)) {
                                                    echo '<span class="badge bg-light text-dark border px-3 py-2 fs-6 fw-normal">' . htmlspecialchars($skill) . '</span>';
                                                }
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div> <!-- /row -->
                    </div> <!-- /card-body -->
                </div> <!-- /student-profile-card -->
            </div> <!-- /col-12 -->
            
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
    <script>
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
    </script>
</body>

</html>