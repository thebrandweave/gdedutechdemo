<?php
session_start();
require_once '../Configurations/config.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}

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

            $headers = "From: gd-updates@gdedutech.com";
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Payments Queue - GD Edu Tech Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="../Images/Logos/GD_Only_logo.png">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0 flex-nowrap">
            
            <!-- Executive Sidebar -->
            <div class="col-auto col-md-3 col-xl-2 px-0 sidebar sticky-top vh-100 overflow-auto hide-scrollbar d-flex flex-column">
                <div class="p-3 border-bottom border-white border-opacity-10 d-flex align-items-center gap-2">
                    <img height="36" src="../Images/Logos/GD_Only_logo.png" alt="GD Logo">
                    <div>
                        <div class="fw-bold text-white fs-6">GD Edu Tech</div>
                        <span class="text-success small fw-semibold">● System Online</span>
                    </div>
                </div>

                <ul class="nav nav-pills flex-column mb-auto p-2 w-100" id="menu">
                    <li class="w-100"><a href="./" class="nav-link"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
                    <li class="w-100"><a href="./Categories/" class="nav-link"><i class="bi bi-grid me-2"></i> Categories</a></li>
                    <li class="w-100"><a href="./Admissions/" class="nav-link"><i class="bi bi-person-plus me-2"></i> Student Admission</a></li>
                    <li class="w-100"><a href="./Courses/" class="nav-link"><i class="bi bi-book me-2"></i> Courses</a></li>
                    <li class="w-100"><a href="./Applications/" class="nav-link"><i class="bi bi-journal-text me-2"></i> Scholarships</a></li>
                    <li class="w-100"><a href="./Events/" class="nav-link"><i class="bi bi-calendar2-event me-2"></i> Events</a></li>
                    <li class="w-100"><a href="./social_links.php" class="nav-link"><i class="bi bi-link-45deg me-2"></i> Social Links</a></li>
                    <li class="w-100"><a href="./Schedule/index.php" class="nav-link"><i class="bi bi-calendar-event me-2"></i> Schedule</a></li>
                    <li class="w-100"><a href="./feedback/feedback.php" class="nav-link"><i class="bi bi-chat-square-heart me-2"></i> Feedback</a></li>
                    <li class="w-100"><a href="./Messages/index.php" class="nav-link"><i class="bi bi-chat-dots me-2"></i> Messages</a></li>
                    <li class="w-100"><a href="./FAQ/" class="nav-link"><i class="bi bi-question-circle me-2"></i> FAQ</a></li>
                    <li class="w-100"><a href="./Users/" class="nav-link"><i class="bi bi-people me-2"></i> Users</a></li>
                    <li class="w-100"><a href="./manage_qr.php" class="nav-link"><i class="bi bi-qr-code me-2"></i> Payment QR</a></li>
                    <li class="w-100"><a href="./pending_payments.php" class="nav-link active"><i class="bi bi-credit-card me-2"></i> Pending Payments</a></li>
                </ul>

                <div class="p-3 border-top border-white border-opacity-10 mt-auto">
                    <a href="./logout.php" class="nav-link text-danger justify-content-center m-0">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </a>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="col min-vh-100 d-flex flex-column">
                
                <!-- Top Header -->
                <div class="bg-white border-bottom px-4 py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="fw-bold text-dark mb-0">Pending Payment Verification Queue</h4>
                        <span class="text-muted small">Verify student transaction payment proofs and approve course enrollments</span>
                    </div>
                </div>

                <div class="p-4 flex-grow-1">
                    
                    <div id="alertContainer"></div>

                    <!-- Pending Payments Table Card -->
                    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                        <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-clock-history text-warning me-2"></i>Unverified Transactions</h6>
                            <span class="badge bg-warning bg-opacity-20 text-dark border border-warning-subtle px-3 py-1.5 rounded-pill fw-semibold">
                                Pending: <?php echo ($pending_result) ? $pending_result->num_rows : 0; ?>
                            </span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Student User</th>
                                        <th>Course Enrolled</th>
                                        <th>Amount</th>
                                        <th>Payment Proof</th>
                                        <th>Transaction Date</th>
                                        <th class="text-center">Verification Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($pending_result && $pending_result->num_rows > 0): ?>
                                        <?php while ($row = $pending_result->fetch_assoc()): ?>
                                            <tr id="row-<?php echo $row['transaction_id']; ?>">
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold small" style="width: 36px; height: 36px;">
                                                            <?php echo strtoupper(substr($row['username'], 0, 1)); ?>
                                                        </div>
                                                        <div>
                                                            <strong class="text-dark d-block"><?php echo htmlspecialchars($row['username']); ?></strong>
                                                            <span class="text-muted small"><?php echo htmlspecialchars($row['email']); ?></span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="fw-semibold text-dark"><?php echo htmlspecialchars($row['course_title']); ?></span>
                                                </td>
                                                <td>
                                                    <strong class="text-success fs-6">₹<?php echo number_format($row['amount']); ?></strong>
                                                </td>
                                                <td>
                                                    <?php if (!empty($row['payment_proof'])): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="viewProof('../uploads/payment_proofs/<?php echo htmlspecialchars($row['payment_proof']); ?>')">
                                                            <i class="bi bi-image me-1"></i> View Receipt
                                                        </button>
                                                    <?php else: ?>
                                                        <span class="text-muted small">No Receipt Uploaded</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-muted small">
                                                    <?php echo date('M d, Y · H:i', strtotime($row['payment_date'])); ?>
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-flex justify-content-center gap-2">
                                                        <button class="btn btn-sm btn-success rounded-pill px-3 fw-semibold" onclick="handlePayment(<?php echo $row['transaction_id']; ?>, 'approve')">
                                                            <i class="bi bi-check-lg me-1"></i> Approve
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-semibold" onclick="handlePayment(<?php echo $row['transaction_id']; ?>, 'reject')">
                                                            <i class="bi bi-x-lg me-1"></i> Reject
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">
                                                <i class="bi bi-check2-all fs-2 text-success d-block mb-2"></i>
                                                All payment transactions have been verified!
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- Proof Modal -->
    <div class="modal fade" id="proofModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title fw-bold text-dark"><i class="bi bi-receipt me-2 text-primary"></i>Payment Receipt Proof</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <img id="proofImage" src="" alt="Payment Proof" class="img-fluid rounded-3 border shadow-sm">
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function viewProof(imageSrc) {
            document.getElementById('proofImage').src = imageSrc;
            new bootstrap.Modal(document.getElementById('proofModal')).show();
        }

        function handlePayment(transactionId, action) {
            if (!confirm(`Are you sure you want to ${action} this payment?`)) return;

            const formData = new FormData();
            formData.append('transaction_id', transactionId);
            formData.append('action', action);

            fetch('pending_payments.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const alertContainer = document.getElementById('alertContainer');
                if (data.status === 'success') {
                    alertContainer.innerHTML = `
                        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                            <span class="fw-semibold">${data.message}</span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `;
                    const row = document.getElementById(`row-${transactionId}`);
                    if (row) row.remove();
                } else {
                    alertContainer.innerHTML = `
                        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                            <span class="fw-semibold">${data.message}</span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    </script>
</body>
</html>