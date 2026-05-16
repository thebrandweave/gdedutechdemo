<?php
session_start();
require_once '../Configurations/config.php'; // Adjust the path according to your directory structure

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../adminPanel/admin_login.php");
    exit();
}

// Fetch pending payments
$pending_query = "
    SELECT 
        t.transaction_id,
        t.amount,
        t.payment_date,
        t.payment_proof,
        t.student_id,
        t.course_id,
        u.username,
        u.email,
        c.title as course_title
    FROM Transactions t
    JOIN Users u ON t.student_id = u.user_id
    JOIN Courses c ON t.course_id = c.course_id
    WHERE t.status = 'pending'
    ORDER BY t.payment_date DESC
";

$pending_result = $conn->query($pending_query);

// Handle payment approval/rejection via AJAX
if (isset($_POST['action']) && isset($_POST['transaction_id'])) {
    $transaction_id = intval($_POST['transaction_id']);
    $action = $_POST['action'];

    if ($action === 'approve') {
        // Start transaction
        $conn->begin_transaction();

        try {
            // Update transaction status
            $update_transaction = $conn->prepare("
                UPDATE Transactions 
                SET status = 'approved' 
                WHERE transaction_id = ?
            ");
            $update_transaction->bind_param("i", $transaction_id);
            $update_transaction->execute();

            // Get student and course IDs
            $get_ids = $conn->prepare("
                SELECT student_id, course_id 
                FROM Transactions 
                WHERE transaction_id = ?
            ");
            $get_ids->bind_param("i", $transaction_id);
            $get_ids->execute();
            $ids_result = $get_ids->get_result()->fetch_assoc();

            // Fetch student's email
            $student_query = $conn->prepare("
                SELECT email 
                FROM Users 
                WHERE user_id = ?
            ");
            $student_query->bind_param("i", $ids_result['student_id']);
            $student_query->execute();
            $student_result = $student_query->get_result()->fetch_assoc();
            $student_email = $student_result['email'];

            // Update enrollment status
            $update_enrollment = $conn->prepare("
                UPDATE Enrollments 
                SET payment_status = 'completed', 
                    access_status = 'active' 
                WHERE student_id = ? 
                AND course_id = ?
            ");
            $update_enrollment->bind_param("ii", $ids_result['student_id'], $ids_result['course_id']);
            $update_enrollment->execute();

            // Send email notification to the student
            $subject = "Payment Approved - Course Enrollment";
            $message = "Dear Student,\n\n";
            $message .= "Your payment for the course has been approved. You can now access the course.\n";
            $message .= "Thank you for your payment!\n\n";
            $message .= "Best regards,\n";
            $message .= "The Admin Team";

            $headers = "From: gd-updates@gdedutech.com"; // Replace with a valid sender email
            mail($student_email, $subject, $message, $headers);

            $conn->commit();
            echo json_encode(['status' => 'success', 'message' => 'Payment approved and notification sent']);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    } elseif ($action === 'reject') {
        // Handle rejection
        $update_query = $conn->prepare("
            UPDATE Transactions 
            SET status = 'rejected' 
            WHERE transaction_id = ?
        ");
        $update_query->bind_param("i", $transaction_id);
        
        if ($update_query->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Payment rejected']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to reject payment']);
        }
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Payments - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="./css/style.css">
    
</head>
<body>
    <div class="container-fluid">
        <div class="row">
           
           <!-- Sidebar -->
           <div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 sidebar">
                <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 min-vh-100">
                    <a href="#" class="d-flex align-items-center pb-3 mb-md-1 mt-md-3 me-md-auto text-white text-decoration-none">
                        <span class="fs-5 fw-bolder" style="display: flex; align-items: center; color: black;">
                            <img height="35px" src="./images/edutechLogo.png" alt="">&nbsp; GD Edu Tech
                        </span>
                    </a>
                    <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100" id="menu">
                        <li class="w-100">
                            <a href="./" class="nav-link ">
                                <i class="bi bi-speedometer2 me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="./Categories/" class="nav-link">
                                <i class="bi bi-grid me-2"></i> Categories
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="./Courses/" class="nav-link">
                                <i class="bi bi-book me-2 "></i> Courses
                            </a>
                        </li>
                        <li class="w-100">
                        <a href="./Applications/" class="nav-link">
                                <i class="bi bi-journal-text me-2"></i> Scholarship Applications
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="./Events/" class="nav-link">
                                <i class="bi bi-calendar2-event me-2"></i> Events
                            </a>
                        </li>
                        <li class="w-100 dropdown">
                            <a href="#" class="nav-link dropdown-toggle" id="quizDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-lightbulb me-2"></i> Quick Links
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="quizDropdown">
                                <li><a class="dropdown-item" href="./Career/index.php">Career portal</a></li>
                                <li><a class="dropdown-item" href="./Shop/shop.php">Shop</a></li>
                            </ul>
                        </li>
                        <li class="w-100">
                            <a href="./Schedule/" class="nav-link">
                                <i class="bi bi-calendar-event me-2"></i> Schedule
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="./Messages/" class="nav-link">
                                <i class="bi bi-chat-dots me-2"></i> Messages
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="./FAQ/" class="nav-link">
                                <i class="bi bi-question-circle me-2"></i> FAQ
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="./Users/" class="nav-link">
                                <i class="bi bi-people me-2"></i> Users
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="./manage_qr.php" class="nav-link">
                                <i class="bi bi-qr-code me-2"></i> Payment QR
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="./pending_payments.php" class="nav-link active">
                                <i class="bi bi-credit-card me-2"></i> Pending Payments
                            </a>
                        </li>
                        <li class="w-100 mt-auto">
                            <a href="./logout.php" class="nav-link text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col py-3">
                <div class="container-fluid">
                    <div class="row mb-4">
                        <div class="col">
                            <h2>Pending Payments</h2>
                            <p class="text-muted">Manage student payment approvals and rejections.</p>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Student</th>
                                            <th>Course</th>
                                            <th>Amount</th>
                                            <th>Date</th>
                                            <th>Proof</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($payment = $pending_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($payment['username']); ?></td>
                                                <td><?php echo htmlspecialchars($payment['course_title']); ?></td>
                                                <td>₹<?php echo number_format($payment['amount'], 2); ?></td>
                                                <td><?php echo date('Y-m-d H:i', strtotime($payment['payment_date'])); ?></td>
                                                <td>
                                                    <img height="50px" src="../uploads/payment_proofs/<?php echo htmlspecialchars($payment['payment_proof']); ?>" 
                                                         class="payment-proof-img"
                                                         data-bs-toggle="modal"
                                                         data-bs-target="#proofModal"
                                                         onclick="showProof(this.src)"
                                                         alt="Payment Proof">
                                                </td>
                                                <td>
                                                    <button class="btn btn-success btn-sm" 
                                                            onclick="handlePayment(<?php echo $payment['transaction_id']; ?>, 'approve')">
                                                        <i class="bi bi-check-circle me-1"></i>Approve
                                                    </button>
                                                    <button class="btn btn-danger btn-sm" 
                                                            onclick="handlePayment(<?php echo $payment['transaction_id']; ?>, 'reject')">
                                                        <i class="bi bi-x-circle me-1"></i>Reject
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Proof Modal -->
    <div class="modal fade" id="proofModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Payment Proof</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img style="max-width: 100%;" id="proofImage" src="" class="modal-img" alt="Payment Proof">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showProof(src) {
            document.getElementById('proofImage').src = src;
        }

        function handlePayment(transactionId, action) {
            if (!confirm('Are you sure you want to ' + action + ' this payment?')) {
                return;
            }

            fetch('pending_payments.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=${action}&transaction_id=${transactionId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        }
    </script>
</body>
</html>