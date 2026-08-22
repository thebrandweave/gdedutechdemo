<?php
session_start();
require_once '../Configurations/config.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}

$success_message = '';
$error_message = '';

// Handle QR code upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['qr_code']) && $_FILES['qr_code']['error'] === 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($_FILES['qr_code']['type'], $allowed_types)) {
            $error_message = "Only JPG, JPEG, WEBP & PNG files are allowed.";
        } elseif ($_FILES['qr_code']['size'] > $max_size) {
            $error_message = "File size must be less than 5MB.";
        } else {
            $file_name = time() . '_' . $_FILES['qr_code']['name'];
            $upload_path = './qr_codes/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_path)) {
                mkdir($upload_path, 0777, true);
            }
            
            if (move_uploaded_file($_FILES['qr_code']['tmp_name'], $upload_path . $file_name)) {
                // Check if QR setting already exists
                $check_query = "SELECT setting_id FROM AdminSettings WHERE setting_key = 'payment_qr'";
                $result = $conn->query($check_query);
                
                if ($result && $result->num_rows > 0) {
                    // Update existing QR code
                    $update_query = "UPDATE AdminSettings SET value = ? WHERE setting_key = 'payment_qr'";
                    $stmt = $conn->prepare($update_query);
                    $stmt->bind_param("s", $file_name);
                } else {
                    // Insert new QR code
                    $insert_query = "INSERT INTO AdminSettings (setting_key, value) VALUES ('payment_qr', ?)";
                    $stmt = $conn->prepare($insert_query);
                    $stmt->bind_param("s", $file_name);
                }
                
                if ($stmt->execute()) {
                    $success_message = "Payment QR code updated successfully!";
                } else {
                    $error_message = "Error updating QR code in database.";
                }
            } else {
                $error_message = "Error uploading file.";
            }
        }
    } else {
        $error_message = "Please select a file to upload.";
    }
}

// Fetch current QR code
$current_qr = '';
$qr_query = "SELECT value FROM AdminSettings WHERE setting_key = 'payment_qr'";
$qr_result = $conn->query($qr_query);
if ($qr_result && $qr_result->num_rows > 0) {
    $current_qr = $qr_result->fetch_assoc()['value'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Payment QR Code - GD Edu Tech Admin</title>
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
                    <li class="w-100"><a href="./manage_qr.php" class="nav-link active"><i class="bi bi-qr-code me-2"></i> Payment QR</a></li>
                    <li class="w-100"><a href="./pending_payments.php" class="nav-link"><i class="bi bi-credit-card me-2"></i> Pending Payments</a></li>
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
                        <h4 class="fw-bold text-dark mb-0">Payment QR Code Settings</h4>
                        <span class="text-muted small">Update official UPI Payment QR Image for course checkout and student fee verification</span>
                    </div>
                </div>

                <div class="p-4 flex-grow-1">
                    
                    <!-- Alert Notifications -->
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                                <span class="fw-semibold"><?php echo htmlspecialchars($error_message); ?></span>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill fs-5"></i>
                                <span class="fw-semibold"><?php echo htmlspecialchars($success_message); ?></span>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="row g-4">
                        
                        <!-- Left Column: Active QR Preview -->
                        <div class="col-lg-5">
                            <div class="card shadow-sm border-0 rounded-4 p-4 text-center h-100">
                                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-qr-code-scan text-primary me-2"></i>Current Active Payment QR</h6>
                                
                                <div class="p-4 border rounded-4 bg-light d-inline-block mx-auto mb-3 shadow-sm" style="max-width: 280px;">
                                    <?php if (!empty($current_qr) && file_exists('./qr_codes/' . $current_qr)): ?>
                                        <img src="./qr_codes/<?php echo htmlspecialchars($current_qr); ?>" alt="Active Payment QR" class="img-fluid rounded-3">
                                    <?php else: ?>
                                        <div class="py-5 text-muted">
                                            <i class="bi bi-qr-code fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                            <span>No active QR Code uploaded</span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-3 py-1.5 rounded-pill fw-semibold mx-auto">
                                    ● Active Checkout QR
                                </span>
                            </div>
                        </div>

                        <!-- Right Column: Upload New QR -->
                        <div class="col-lg-7">
                            <div class="card shadow-sm border-0 rounded-4 p-4 p-lg-5 h-100">
                                <h5 class="fw-bold text-dark mb-2"><i class="bi bi-cloud-arrow-up-fill text-primary me-2"></i>Upload New QR Code</h5>
                                <p class="text-muted small mb-4">Upload a high-resolution PNG or JPG image of your official UPI QR Code.</p>

                                <form method="POST" enctype="multipart/form-data">
                                    <div class="p-4 border-2 border-dashed rounded-4 text-center bg-light mb-4 position-relative">
                                        <i class="bi bi-image-fill fs-1 text-primary mb-2 d-block"></i>
                                        <h6 class="fw-bold text-dark mb-1">Drag & Drop QR Image Here</h6>
                                        <span class="text-muted small d-block mb-3">Supported formats: JPG, PNG, WEBP (Max 5MB)</span>
                                        
                                        <input type="file" name="qr_code" id="qr_code" class="form-control" accept="image/*" required>
                                    </div>

                                    <button type="submit" class="btn btn-primary w-100 py-3 font-weight-bold">
                                        <i class="bi bi-check-circle-fill me-2"></i> UPDATE PAYMENT QR CODE
                                    </button>
                                </form>
                            </div>
                        </div>

                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>