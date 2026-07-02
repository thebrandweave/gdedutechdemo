<?php
session_start();
require_once '../Configurations/config.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$success_message = '';
$error_message = '';

// Handle QR code upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['qr_code']) && $_FILES['qr_code']['error'] === 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($_FILES['qr_code']['type'], $allowed_types)) {
            $error_message = "Only JPG, JPEG & PNG files are allowed.";
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
                
                if ($result->num_rows > 0) {
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
                    $success_message = "QR code updated successfully!";
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
if ($qr_result->num_rows > 0) {
    $current_qr = $qr_result->fetch_assoc()['value'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Payment QR Code - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="./css/style.css">
    <style>
        .qr-preview {
            max-width: 300px;
            margin: 20px auto;
            padding: 15px;
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            text-align: center;
        }
        .qr-preview img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
        }
        .upload-section {
            background: #fff;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .custom-file-upload {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        .custom-file-upload:hover {
            border-color: #6c757d;
            background: #f8f9fa;
        }
    </style>
</head>
<body>
  <div class="container-fluid">
        <div class="row flex-nowrap"> <div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 sidebar sticky-top vh-100 overflow-auto hide-scrollbar"> 
                <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 h-100">
                    <a href="#" class="d-flex align-items-center pb-3 mb-md-1 mt-md-3 me-md-auto text-white text-decoration-none">
                        <span class="fs-5 fw-bolder" style="display: flex;align-items:center;color:black;"><img height="35px" src="./images/edutechLogo.png" alt="">&nbsp; GD Edu Tech</span>
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
                            <a href="./Admissions/" class="nav-link">
                                <i class="bi bi-person-plus me-2"></i> Student Admission
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
                            <a href="../feedback/feedback.php" class="nav-link">
                                <i class="bi bi-chat-square-heart"></i> Feedback
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
                            <a href="./manage_qr.php" class="nav-link active">
                                <i class="bi bi-qr-code me-2"></i> Payment QR
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="./pending_payments.php" class="nav-link">
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
                <div class="container">
                    <div class="row mb-4">
                        <div class="col">
                            <h2>Manage Payment QR Code</h2>
                            <p class="text-muted">Update your payment QR code for course purchases.</p>
                        </div>
                    </div>

                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-body p-4">
                                    <?php if ($success_message): ?>
                                        <div class="alert alert-success" role="alert">
                                            <i class="bi bi-check-circle-fill me-2"></i><?php echo $success_message; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($error_message): ?>
                                        <div class="alert alert-danger" role="alert">
                                            <i class="bi bi-exclamation-circle-fill me-2"></i><?php echo $error_message; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($current_qr): ?>
                                        <div class="qr-preview mb-4">
                                            <h5 class="mb-3">Current QR Code</h5>
                                            <img src="./qr_codes/<?php echo htmlspecialchars($current_qr); ?>" 
                                                 alt="Current QR Code" 
                                                 class="img-fluid mb-2">
                                        </div>
                                    <?php endif; ?>

                                    <form action="" method="POST" enctype="multipart/form-data" style="text-align: center;">
                                        <label for="qr_code" class="custom-file-upload">
                                            <i class="bi bi-cloud-upload fs-3 mb-2"></i>
                                            <h5>Upload New QR Code</h5>
                                            <p class="text-muted mb-0">Click to select or drag and drop</p>
                                            <small class="text-muted">Supported formats: JPG, JPEG, PNG (Max 5MB)</small>
                                        </label>
                                        <input type="file" 
                                               id="qr_code" 
                                               name="qr_code" 
                                               class="form-control d-none" 
                                               accept="image/jpeg,image/png,image/jpg" 
                                               required>
                                        
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="bi bi-cloud-upload-fill me-2"></i>Update QR Code
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('qr_code').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name;
            if (fileName) {
                const label = document.querySelector('.custom-file-upload p');
                label.textContent = fileName;
            }
        });
    </script>
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