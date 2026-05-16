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

    <title>Feedback Management - GD Edu Tech</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <link rel="stylesheet" href="../css/style.css">

    <style>

        .feedback-card{
            transition: 0.3s ease;
            overflow: hidden;
        }

        .feedback-card:hover{
            transform: translateY(-5px);
        }

        .feedback-image{
            height: 220px;
            object-fit: cover;
            width: 100%;
        }

        .status-badge{
            font-size: 13px;
            padding: 6px 12px;
            border-radius: 30px;
        }

    </style>

</head>

<body>

<div class="container-fluid">

    <div class="row">

        <!-- SIDEBAR -->
        <div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 sidebar">

            <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 min-vh-100">

                <a href="#"
                    class="d-flex align-items-center pb-3 mb-md-1 mt-md-3 me-md-auto text-white text-decoration-none">

                    <span class="fs-5 fw-bolder"
                        style="display:flex;align-items:center;color:black;">

                        <img height="35px"
                            src="../images/edutechLogo.png"
                            alt="">

                        &nbsp; GD Edu Tech

                    </span>

                </a>

                <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100">

                    <li class="w-100">
                        <a href="../" class="nav-link">
                            <i class="bi bi-speedometer2 me-2"></i>
                            Dashboard
                        </a>
                    </li>

                    <li class="w-100">
                        <a href="../Categories/" class="nav-link">
                            <i class="bi bi-grid me-2"></i>
                            Categories
                        </a>
                    </li>

                    <li class="w-100">
                        <a href="../Applications/" class="nav-link">
                            <i class="bi bi-journal-text me-2"></i>
                            Scholarship Applications
                        </a>
                    </li>

                    <li class="w-100">
                        <a href="../Events/" class="nav-link">
                            <i class="bi bi-calendar2-event me-2"></i>
                            Events
                        </a>
                    </li>

                    <li class="w-100">
                        <a href="../Courses/" class="nav-link">
                            <i class="bi bi-book me-2"></i>
                            Courses
                        </a>
                    </li>

                    <li class="w-100 dropdown">

                        <a href="#"
                            class="nav-link dropdown-toggle"
                            data-bs-toggle="dropdown">

                            <i class="bi bi-lightbulb me-2"></i>
                            Quick Links

                        </a>

                        <ul class="dropdown-menu">

                            <li>
                                <a class="dropdown-item" href="../index.php">
                                    Career Portal
                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item" href="../Shop/shop.php">
                                    Shop
                                </a>
                            </li>

                        </ul>

                    </li>

                    <li class="w-100">
                        <a href="../Schedule/" class="nav-link">
                            <i class="bi bi-calendar-event me-2"></i>
                            Schedule
                        </a>
                    </li>

                    <li class="w-100">
                        <a href="../Messages/" class="nav-link">
                            <i class="bi bi-chat-dots me-2"></i>
                            Messages
                        </a>
                    </li>

                    <li class="w-100">
                        <a href="../FAQ/" class="nav-link">
                            <i class="bi bi-question-circle me-2"></i>
                            FAQ
                        </a>
                    </li>

                    <li class="w-100">
                        <a href="../Users/" class="nav-link">
                            <i class="bi bi-people me-2"></i>
                            Users
                        </a>
                    </li>

                    <!-- ACTIVE FEEDBACK MENU -->
                    <li class="w-100">
                        <a href="./feedback.php" class="nav-link active">
                            <i class="bi bi-chat-square-heart me-2"></i>
                            Feedback
                        </a>
                    </li>

                    <li class="w-100">
                        <a href="../manage_qr.php" class="nav-link">
                            <i class="bi bi-qr-code me-2"></i>
                            Payment QR
                        </a>
                    </li>

                    <li class="w-100">
                        <a href="../pending_payments.php" class="nav-link">
                            <i class="bi bi-credit-card me-2"></i>
                            Pending Payments
                        </a>
                    </li>

                    <li class="w-100 mt-auto">
                        <a href="../logout.php" class="nav-link text-danger">
                            <i class="bi bi-box-arrow-right me-2"></i>
                            Logout
                        </a>
                    </li>

                </ul>

            </div>

        </div>

        <!-- MAIN CONTENT -->
        <div class="col py-4">

            <div class="container-fluid">

                <div class="d-flex justify-content-between align-items-center mb-4">

                    <div>

                        <h2 class="fw-bold">
                            Student Feedback Management
                        </h2>

                        <p class="text-muted mb-0">
                            Manage and approve student testimonials
                        </p>

                    </div>

                </div>

                <div class="row">

                    <?php while($row = $feedbacks->fetch_assoc()): ?>

                        <div class="col-lg-4 col-md-6 mb-4">

                            <div class="card feedback-card shadow border-0 rounded-4 h-100">

                                <?php if(!empty($row['student_image'])): ?>

                                    <img
                                        src="../../uploads/feedback/<?php echo $row['student_image']; ?>"
                                        class="feedback-image"
                                    >

                                <?php else: ?>

                                    <img
                                        src="../images/default-course.png"
                                        class="feedback-image"
                                    >

                                <?php endif; ?>

                                <div class="card-body d-flex flex-column">

                                    <div class="d-flex justify-content-between align-items-start mb-2">

                                        <h5 class="fw-bold mb-0">
                                            <?php echo htmlspecialchars($row['student_name']); ?>
                                        </h5>

                                        <?php
                                        $status = $row['status'];

                                        if($status == 'approved'){
                                            echo '<span class="badge bg-success status-badge">Approved</span>';
                                        }
                                        elseif($status == 'rejected'){
                                            echo '<span class="badge bg-danger status-badge">Rejected</span>';
                                        }
                                        else{
                                            echo '<span class="badge bg-warning text-dark status-badge">Pending</span>';
                                        }
                                        ?>

                                    </div>

                                    <p class="text-muted mb-2">
                                        <i class="bi bi-book me-1"></i>
                                        <?php echo htmlspecialchars($row['course_name']); ?>
                                    </p>
                                    <p class="text-muted mb-2">
    <i class="bi bi-mortarboard me-1"></i>
    <?php echo htmlspecialchars($row['college_name']); ?>
</p> 

                                    <p class="mb-2">

                                        <?php
                                        for($i=1; $i<=$row['rating']; $i++){
                                            echo '⭐';
                                        }
                                        ?>

                                    </p>

                                    <p class="text-muted flex-grow-1">
                                        "<?php echo htmlspecialchars($row['feedback']); ?>"
                                    </p>

                                    <div class="d-flex gap-2 mt-3">

                                        <a
                                            href="../approve-feedback.php?id=<?php echo $row['feedback_id']; ?>"
                                            class="btn btn-success btn-sm w-100"
                                        >
                                            <i class="bi bi-check-circle me-1"></i>
                                            Approve
                                        </a>

                                        <a
                                            href="../reject-feedback.php?id=<?php echo $row['feedback_id']; ?>"
                                            class="btn btn-danger btn-sm w-100"
                                        >
                                            <i class="bi bi-x-circle me-1"></i>
                                            Reject
                                        </a>

                                    </div>

                                </div>

                            </div>

                        </div>

                    <?php endwhile; ?>

                </div>

            </div>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>