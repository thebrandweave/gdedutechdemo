<?php
session_start();
require_once '../../Configurations/config.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

// Check if course ID is provided
if (!isset($_GET['course_id'])) {
    header("Location: ../");
    exit();
}

$course_id = intval($_GET['course_id']);
$user_id = $_SESSION['user_id'];

// Fetch course details
$course_query = "SELECT title, price FROM Courses WHERE course_id = ?";
$course_stmt = $conn->prepare($course_query);
$course_stmt->bind_param("i", $course_id);
$course_stmt->execute();
$course = $course_stmt->get_result()->fetch_assoc();

// Fetch payment QR code from admin settings
$qr_query = "SELECT value FROM AdminSettings WHERE setting_key = 'payment_qr'";
$qr_result = $conn->query($qr_query);
$qr_code = $qr_result->fetch_assoc()['value'] ?? 'default_qr.png';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process payment proof upload
    $payment_proof = $_FILES['payment_proof'];
    $proof_name = time() . '_' . $payment_proof['name'];
    $proof_path = '../../uploads/payment_proofs/' . $proof_name;
    
    if (move_uploaded_file($payment_proof['tmp_name'], $proof_path)) {
        // Create transaction record
        $transaction_query = "
            INSERT INTO Transactions (
                student_id, 
                course_id, 
                amount, 
                payment_method,
                payment_proof,
                status
            ) VALUES (?, ?, ?, 'QR_CODE', ?, 'pending')
        ";
        
        $transaction_stmt = $conn->prepare($transaction_query);
        $transaction_stmt->bind_param("iids", $user_id, $course_id, $course['price'], $proof_name);
        
        if ($transaction_stmt->execute()) {
            // Create pending enrollment
            $enrollment_query = "
                INSERT INTO Enrollments (
                    student_id,
                    course_id,
                    payment_status,
                    access_status
                ) VALUES (?, ?, 'pending', 'pending')
            ";
            
            $enrollment_stmt = $conn->prepare($enrollment_query);
            $enrollment_stmt->bind_param("ii", $user_id, $course_id);
            $enrollment_stmt->execute();
            
            // Fetch student details for email notification
            $user_query = "SELECT first_name, last_name FROM Users WHERE user_id = ?";
            $user_stmt = $conn->prepare($user_query);
            $user_stmt->bind_param("i", $user_id);
            $user_stmt->execute();
            $user_result = $user_stmt->get_result();

            if ($user_result->num_rows > 0) {
                $user_data = $user_result->fetch_assoc();
                $first_name = $user_data['first_name'];
                $last_name = $user_data['last_name'];

                // Prepare email details
                $to = $adminMail; // Admin's email
                $subject = "New Enrollment Notification for " . $first_name . " " . $last_name;
                $message = "Dear Admin,\n\n";
                $message .= $first_name . " " . $last_name . " has made an enrollment to the course: " . $course['title'] . ".\n";
                $message .= "Payment has been completed. Please verify and approve the payment to allow the student to access the course.\n\n";
                $message .= "Best regards,\n";
                $message .= "The System";

                // Send the email
                $headers = "From: payment-alerts@gdedutech.com"; // Replace with a valid sender email
                if (mail($to, $subject, $message, $headers)) {
                    // Email sent successfully
                    error_log("Email sent to admin regarding enrollment for " . $first_name . " " . $last_name);
                } else {
                    // Handle email sending failure
                    error_log("Failed to send email to admin regarding enrollment for " . $first_name . " " . $last_name);
                }
            }

            header("Location: payment_confirmation.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - <?php echo htmlspecialchars($course['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background: #f8f9fa;
            min-height: 100vh;
        }
        .checkout-container {
            padding: 40px 0;
        }
        .checkout-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .checkout-header {
            background: linear-gradient(135deg, #2C3E50, #3498db);
            color: white;
            padding: 20px;
            text-align: center;
        }
        .order-summary {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .price-tag {
            font-size: 2rem;
            font-weight: bold;
            color: #2C3E50;
        }
        .qr-code-container {
            max-width: 250px;
            margin: 20px auto;
            padding: 15px;
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            text-align: center;
        }
        .qr-code-container img {
            width: 100%;
            height: auto;
            border-radius: 8px;
        }
        .payment-steps {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .payment-steps ol {
            margin-bottom: 0;
            padding-left: 20px;
        }
        .payment-steps li {
            margin-bottom: 10px;
            color: #2C3E50;
        }
        .upload-section {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
        }
        .btn-submit {
            background: #2C3E50;
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-submit:hover {
            background: #34495E;
            transform: translateY(-2px);
        }
        .course-info {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        .course-icon {
            background: rgba(52, 152, 219, 0.1);
            padding: 15px;
            border-radius: 10px;
            color: #3498db;
        }
    </style>
</head>
<body>
    <div class="container checkout-container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="checkout-card card">
                    <div class="checkout-header">
                        <h3 class="mb-0">Secure Checkout</h3>
                    </div>
                    
                    <div class="card-body p-4">
                        <!-- Course Information -->
                        <div class="order-summary">
                            <div class="course-info">
                                <div class="course-icon">
                                    <i class="bi bi-mortarboard-fill fs-3"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1"><?php echo htmlspecialchars($course['title']); ?></h5>
                                    <div class="price-tag">₹<?php echo number_format($course['price'], 2); ?></div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Steps -->
                        <div class="payment-steps">
                            <h5 class="mb-3"><i class="bi bi-1-circle-fill me-2 text-primary"></i>Payment Instructions</h5>
                            <ol>
                                <li>Scan the QR code below using any UPI payment app (Google Pay, PhonePe, Paytm)</li>
                                <li>Enter the exact amount: <strong>₹<?php echo number_format($course['price'], 2); ?></strong></li>
                                <li>Complete the payment and take a screenshot of the confirmation</li>
                                <li>Upload the screenshot in the section below</li>
                            </ol>
                        </div>

                        <!-- QR Code -->
                        <div class="qr-code-container">
                            <img src="../../adminPanel/qr_codes/<?php echo htmlspecialchars($qr_code); ?>" 
                                 alt="Payment QR Code" 
                                 class="img-fluid mb-2">
                            <small class="text-muted d-block">Scan to pay</small>
                        </div>

                        <!-- Upload Form -->
                        <div class="upload-section">
                            <h5 class="mb-3"><i class="bi bi-2-circle-fill me-2 text-primary"></i>Upload Payment Proof</h5>
                            <form action="" method="POST" enctype="multipart/form-data">
                                <div class="mb-4">
                                    <label for="payment_proof" class="form-label">Payment Screenshot</label>
                                    <input type="file" 
                                           class="form-control" 
                                           id="payment_proof" 
                                           name="payment_proof" 
                                           accept="image/*" 
                                           required>
                                    <small class="text-muted">Please upload a clear screenshot of your payment confirmation</small>
                                </div>
                                
                                <button type="submit" class="btn btn-submit w-100">
                                    <i class="bi bi-lock-fill me-2"></i>Complete Purchase
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
