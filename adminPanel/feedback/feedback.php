<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../admin_login.php');
    exit();
}

require_once '../../Configurations/config.php';

$query = "
SELECT *
FROM student_feedback
ORDER BY feedback_id DESC
";

$feedbacks = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Feedback Management - GD Edu Tech Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="../../Images/Logos/GD_Only_logo.png">
    <link rel="stylesheet" href="../css/style.css">

    <style>
        .feedback-card {
            background: #ffffff;
            border: 1px solid var(--admin-border-color) !important;
            border-radius: 20px !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .feedback-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.08) !important;
        }

        .feedback-quote {
            font-style: italic;
            line-height: 1.6;
            color: #475569;
            background: #f8fafc;
            border-radius: 14px;
            padding: 16px;
            border-left: 3px solid #0d7298;
        }

        .status-badge {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 6px 14px;
            border-radius: 20px;
        }
    </style>
</head>

<body>
    <div class="container-fluid p-0">
        <div class="row g-0 flex-nowrap">
            
            <!-- Executive Sidebar -->
            <div class="col-auto col-md-3 col-xl-2 px-0 sidebar sticky-top vh-100 overflow-auto hide-scrollbar d-flex flex-column">
                <div class="p-3 border-bottom border-white border-opacity-10 d-flex align-items-center gap-2">
                    <img height="36" src="../../Images/Logos/GD_Only_logo.png" alt="GD Logo">
                    <div>
                        <div class="fw-bold text-white fs-6">GD Edu Tech</div>
                      
                    </div>
                </div>

                <ul class="nav nav-pills flex-column mb-auto p-2 w-100" id="menu">
                    <li class="w-100"><a href="../" class="nav-link"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
                    <li class="w-100"><a href="../Categories/" class="nav-link"><i class="bi bi-grid me-2"></i> Categories</a></li>
                    <li class="w-100"><a href="../Admissions/" class="nav-link"><i class="bi bi-person-plus me-2"></i> Student Admission</a></li>
                    <li class="w-100"><a href="../Courses/" class="nav-link"><i class="bi bi-book me-2"></i> Courses</a></li>
                    <li class="w-100"><a href="../Applications/" class="nav-link"><i class="bi bi-journal-text me-2"></i> Scholarships</a></li>
                    <li class="w-100"><a href="../Events/" class="nav-link"><i class="bi bi-calendar2-event me-2"></i> Events</a></li>
                    <li class="w-100"><a href="../social_links.php" class="nav-link"><i class="bi bi-link-45deg me-2"></i> Social Links</a></li>
                    <li class="w-100"><a href="../Schedule/index.php" class="nav-link"><i class="bi bi-calendar-event me-2"></i> Schedule</a></li>
                    <li class="w-100"><a href="../feedback/feedback.php" class="nav-link active"><i class="bi bi-chat-square-heart me-2"></i> Feedback</a></li>
                    <li class="w-100"><a href="../Messages/index.php" class="nav-link"><i class="bi bi-chat-dots me-2"></i> Messages</a></li>
                    <li class="w-100"><a href="../FAQ/" class="nav-link"><i class="bi bi-question-circle me-2"></i> FAQ</a></li>
                    <li class="w-100"><a href="../Users/" class="nav-link"><i class="bi bi-people me-2"></i> Users</a></li>
                    <li class="w-100"><a href="../manage_qr.php" class="nav-link"><i class="bi bi-qr-code me-2"></i> Payment QR</a></li>
                    <li class="w-100"><a href="../pending_payments.php" class="nav-link"><i class="bi bi-credit-card me-2"></i> Pending Payments</a></li>
                </ul>

                <div class="p-3 border-top border-white border-opacity-10 mt-auto">
                    <a href="../logout.php" class="nav-link text-danger justify-content-center m-0">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </a>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="col main-content min-vh-100 d-flex flex-column" style="min-width: 0; overflow-x: hidden;">
                
                <!-- Top Header Bar -->
                <div class="bg-white border-bottom px-4 py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="fw-bold text-dark mb-0">Student Feedback Management</h4>
                        <span class="text-muted small">Review, moderate, and approve student course testimonials</span>
                    </div>
                </div>

                <div class="p-4 flex-grow-1">
                    
                    <!-- Feedback Cards Grid -->
                    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4 mb-4">
                        <?php if ($feedbacks && $feedbacks->num_rows > 0): ?>
                            <?php while($row = $feedbacks->fetch_assoc()): ?>
                                <div class="col">
                                    <div class="card feedback-card border-0 h-100 d-flex flex-column shadow-sm overflow-hidden p-4">
                                        
                                        <!-- Header Row: Avatar, Student Name, Status -->
                                        <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3">
                                            <div class="d-flex align-items-center gap-3">
                                                <?php if(!empty($row['student_image'])): ?>
                                                    <img 
                                                        src="../../uploads/feedback/<?php echo htmlspecialchars($row['student_image']); ?>" 
                                                        class="rounded-circle border object-fit-cover shadow-sm"
                                                        style="width: 52px; height: 52px;"
                                                        alt="<?php echo htmlspecialchars($row['student_name']); ?>"
                                                        onerror="this.onerror=null; this.src='../images/default-course.png';"
                                                    >
                                                <?php else: ?>
                                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center border shadow-sm" style="width: 52px; height: 52px; font-size: 1.15rem;">
                                                        <?php echo strtoupper(substr($row['student_name'] ?? 'S', 0, 1)); ?>
                                                    </div>
                                                <?php endif; ?>

                                                <div>
                                                    <h6 class="fw-bold mb-0 text-dark"><?php echo htmlspecialchars($row['student_name']); ?></h6>
                                                    <span class="text-muted small">
                                                        <i class="bi bi-mortarboard me-1"></i><?php echo htmlspecialchars($row['college_name'] ?? 'Student'); ?>
                                                    </span>
                                                </div>
                                            </div>

                                            <div>
                                                <?php
                                                    $status = strtolower($row['status'] ?? 'pending');
                                                    if($status === 'approved'){
                                                        echo '<span class="badge bg-success bg-opacity-10 text-success border border-success-subtle status-badge">Approved</span>';
                                                    }
                                                    elseif($status === 'rejected'){
                                                        echo '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle status-badge">Rejected</span>';
                                                    }
                                                    else{
                                                        echo '<span class="badge bg-warning bg-opacity-20 text-dark border border-warning-subtle status-badge">Pending</span>';
                                                    }
                                                ?>
                                            </div>
                                        </div>

                                        <div class="d-flex flex-column flex-grow-1">
                                            
                                            <!-- Course Meta Tag -->
                                            <div class="mb-2">
                                                <span class="badge bg-light text-secondary border px-2.5 py-1.5 font-monospace small">
                                                    <i class="bi bi-book me-1 text-primary"></i>
                                                    <?php echo htmlspecialchars($row['course_name'] ?? 'Course'); ?>
                                                </span>
                                            </div>

                                            <!-- Rating Stars -->
                                            <div class="mb-3 text-warning fs-6">
                                                <?php
                                                    $rating = intval($row['rating'] ?? 5);
                                                    for($i=1; $i<=5; $i++){
                                                        if ($i <= $rating) {
                                                            echo '<i class="bi bi-star-fill me-0.5"></i>';
                                                        } else {
                                                            echo '<i class="bi bi-star text-secondary opacity-25 me-0.5"></i>';
                                                        }
                                                    }
                                                ?>
                                            </div>

                                            <!-- Feedback Quote -->
                                            <p class="feedback-quote flex-grow-1 mb-4 small">
                                                "<?php echo htmlspecialchars($row['feedback']); ?>"
                                            </p>

                                            <!-- Action Buttons -->
                                            <div class="d-flex gap-2 mt-auto pt-2 border-top">
                                                <a 
                                                    href="../approve-feedback.php?id=<?php echo $row['feedback_id']; ?>" 
                                                    class="btn btn-success btn-sm w-100 rounded-3 py-2 fw-semibold d-flex align-items-center justify-content-center"
                                                >
                                                    <i class="bi bi-check-circle-fill me-1.5"></i> Approve
                                                </a>

                                                <a 
                                                    href="../reject-feedback.php?id=<?php echo $row['feedback_id']; ?>" 
                                                    class="btn btn-outline-danger btn-sm w-100 rounded-3 py-2 fw-semibold d-flex align-items-center justify-content-center"
                                                >
                                                    <i class="bi bi-x-circle-fill me-1.5"></i> Reject
                                                </a>
                                            </div>

                                        </div>

                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="col-12 text-center py-5 text-muted">
                                <i class="bi bi-chat-square-heart fs-1 text-secondary opacity-50 d-block mb-2"></i>
                                No student feedback testimonials submitted yet.
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>