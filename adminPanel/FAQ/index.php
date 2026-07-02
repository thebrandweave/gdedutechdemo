<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../admin_login.php');
    exit();
}

require_once '../../Configurations/config.php';

// Handle FAQ deletion
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $faq_id = intval($_GET['id']);
    $delete_query = "DELETE FROM FAQs WHERE faq_id = ?";
    $stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($stmt, 'i', $faq_id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = "FAQ deleted successfully.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error deleting FAQ.";
        $_SESSION['message_type'] = "danger";
    }

    header("Location: ./");
    exit();
}

// Fetch all FAQs
$query = "SELECT * FROM FAQs ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ Management - GD Edu Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .faq-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .faq-card:hover {
            transform: translateY(-5px);
        }

        .question-header {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 15px 15px 0 0;
            border-bottom: 1px solid #dee2e6;
        }

        .answer-body {
            padding: 20px;
        }

        .btn-action {
            width: 35px;
            height: 35px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-action:hover {
            transform: scale(1.1);
        }
    </style>
</head>

<body>
  <div class="container-fluid">
        <div class="row flex-nowrap"> 
            <div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 sidebar sticky-top vh-100 overflow-auto hide-scrollbar"> 
                <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 h-100">
                    <a href="#" class="d-flex align-items-center pb-3 mb-md-1 mt-md-3 me-md-auto text-white text-decoration-none">
                        <span class="fs-5 fw-bolder" style="display: flex;align-items:center;color:black;">
                            <img height="35px" src="../images/edutechLogo.png" alt="">&nbsp; GD Edu Tech
                        </span>
                    </a>
                    <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100" id="menu">
                        <li class="w-100">
                            <a href="../" class="nav-link">
                                <i class="bi bi-speedometer2 me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Categories/" class="nav-link">
                                <i class="bi bi-grid me-2"></i> Categories
                            </a>
                        </li>
<li class="w-100">
                            <a href="../Admissions/" class="nav-link">
                                <i class="bi bi-person-plus me-2"></i> Student Admission
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Courses/" class="nav-link">
                                <i class="bi bi-book me-2"></i> Courses
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Applications/" class="nav-link">
                                <i class="bi bi-journal-text me-2"></i> Scholarship Applications
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Events/" class="nav-link">
                                <i class="bi bi-calendar2-event me-2"></i> Events
                            </a>
                        </li>
                             <li class="w-100">
                            <a href="../social_links.php" class="nav-link">
                                <i class="bi bi-link-45deg me-2"></i> Social Links
                            </a>
                        </li>
                        <li class="w-100 dropdown">
                            <a href="#" class="nav-link dropdown-toggle" id="quizDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-lightbulb me-2"></i> Quick Links
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="quizDropdown">
                                <li><a class="dropdown-item" href="../Career/index.php">Career portal</a></li>
                                <li><a class="dropdown-item" href="../Shop/shop.php">Shop</a></li>
                            </ul>
                        </li>
                        <li class="w-100">
                            <a href="../Schedule/" class="nav-link">
                                <i class="bi bi-calendar-event me-2"></i> Schedule
                            </a>
                        </li>
                         <li class="w-100">
                            <a href="../feedback/feedback.php" class="nav-link">
                                <i class="bi bi-chat-square-heart"></i> Feedback
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Messages/" class="nav-link">
                                <i class="bi bi-chat-dots me-2"></i> Messages
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="./" class="nav-link active">
                                <i class="bi bi-question-circle me-2"></i> FAQ
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Users/" class="nav-link">
                                <i class="bi bi-people me-2"></i> Users
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../manage_qr.php" class="nav-link">
                                <i class="bi bi-qr-code me-2"></i> Payment QR
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../pending_payments.php" class="nav-link">
                                <i class="bi bi-credit-card me-2"></i> Pending Payments
                            </a>
                        </li>
                        <li class="w-100 mt-auto">
                            <a href="../logout.php" class="nav-link text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col py-3">
                <div class="container">
                    <div class="row mb-4">
                        <div class="col">
                            <h2>FAQ Management</h2>
                            <p class="text-muted">Manage frequently asked questions for your platform.</p>
                        </div>
                        <div class="col-auto">
                            <a href="add_faq.php" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-2"></i>Add New FAQ
                            </a>
                        </div>
                    </div>

                    <!-- Alert Messages -->
                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                            <i class="bi bi-<?php echo $_SESSION['message_type'] == 'success' ? 'check-circle' : 'exclamation-circle'; ?>-fill me-2"></i>
                            <?php
                            echo $_SESSION['message'];
                            unset($_SESSION['message']);
                            unset($_SESSION['message_type']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- FAQs Grid -->
                    <div class="row">
                        <?php while ($faq = mysqli_fetch_assoc($result)): ?>
                            <div class="col-12 mb-4">
                                <div class="faq-card">
                                    <div class="question-header">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0"><?php echo htmlspecialchars($faq['question']); ?></h5>
                                            <div class="btn-group">
                                                <a href="edit_faq.php?id=<?php echo $faq['faq_id']; ?>"
                                                    class="btn btn-action btn-outline-primary me-2"
                                                    title="Edit FAQ">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="?delete=1&id=<?php echo $faq['faq_id']; ?>"
                                                    class="btn btn-action btn-outline-danger"
                                                    onclick="return confirm('Are you sure you want to delete this FAQ?');"
                                                    title="Delete FAQ">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="answer-body">
                                        <p class="mb-3"><?php echo nl2br(htmlspecialchars($faq['answer'])); ?></p>
                                        <small class="text-muted">
                                            <i class="bi bi-clock me-1"></i>
                                            Last updated: <?php echo date('M d, Y', strtotime($faq['updated_at'])); ?>
                                        </small>
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
    <style>
            .hide-scrollbar::-webkit-scrollbar {
            display: none; /* Chrome, Safari and Opera */
        }
        .hide-scrollbar {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }
    </style>
</body>

</html>