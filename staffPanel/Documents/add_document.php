<?php
session_start();
require_once '../../Configurations/config.php';

// Check if user is logged in and is staff
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Staff') {
    header('Location: ../staff_login.php');
    exit();
}

// Get staff details from session
$staff_name = $_SESSION['username'] ?? 'Staff';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize inputs
    $title = mysqli_real_escape_string($conn, trim($_POST['title']));
    $price = floatval($_POST['price']);
    $user_id = $_SESSION['user_id'];

    // Handle file upload
    $errors = [];
    if (empty($_FILES['document']['name'])) {
        $errors[] = "Document file is required.";
    } else {
        $target_dir = "../../uploads/Documents"; // Ensure this directory exists and is writable
        
        // Create a new file name based on the document title
        $fileType = strtolower(pathinfo($_FILES["document"]["name"], PATHINFO_EXTENSION));
        $new_file_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $title) . '.' . $fileType; // Sanitize title for filename
        $target_file = $target_dir . '/' . $new_file_name;

        // Check file type (optional)
        $allowed_types = ['pdf', 'doc', 'docx', 'ppt', 'pptx']; // Add allowed file types
        if (!in_array($fileType, $allowed_types)) {
            $errors[] = "Only PDF, DOC, DOCX, PPT, and PPTX files are allowed.";
        }

        // Move uploaded file
        if (empty($errors) && !move_uploaded_file($_FILES["document"]["tmp_name"], $target_file)) {
            $errors[] = "Error uploading file.";
        }
    }

    // Validation
    if (empty($title)) $errors[] = "Document title is required.";
    if ($price < 0) $errors[] = "Price must be a non-negative number.";

    // If no errors, insert document
    if (empty($errors)) {
        $insert_query = "INSERT INTO Documents (title, price, document_url, user_id) 
                         VALUES ('$title', $price, '$target_file', $user_id)"; // Store file path
        
        if (mysqli_query($conn, $insert_query)) {
            $_SESSION['message'] = "Document added successfully.";
            $_SESSION['message_type'] = "success";
            header("Location: ./"); // Redirect to documents list or another page
            exit();
        } else {
            $errors[] = "Database error: " . mysqli_error($conn);
        }
    }
}

// Handle document deletion
// Handle document deletion
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // Fetch the document URL from the database
    $result = mysqli_query($conn, "SELECT document_url FROM Documents WHERE document_id = $delete_id");
    if ($result && mysqli_num_rows($result) > 0) {
        $doc = mysqli_fetch_assoc($result);
        $file_path = $doc['document_url'];

        // Construct the full file path
        $target_dir = '../../uploads/Documents/';
        $file_path = $target_dir . basename($file_path); // Full file path to delete

        // Debugging: Check the constructed file path
        echo "File path: " . $file_path;

        // Check if the file exists before deleting
        if (file_exists($file_path)) {
            // Delete the file from the server
            if (unlink($file_path)) {
                echo "File deleted successfully."; // Debugging message
            } else {
                echo "Failed to delete the file."; // Debugging message
                $_SESSION['message'] = "Failed to delete the file from the server.";
                $_SESSION['message_type'] = "danger";
                header("Location: ./"); // Redirect to documents list or another page
                exit();
            }
        } else {
            echo "File does not exist."; // Debugging message
            $_SESSION['message'] = "File not found on the server.";
            $_SESSION['message_type'] = "danger";
            header("Location: ./"); // Redirect to documents list or another page
            exit();
        }

        // Delete the document from the database
        $delete_query = "DELETE FROM Documents WHERE document_id = $delete_id";
        if (mysqli_query($conn, $delete_query)) {
            $_SESSION['message'] = "Document deleted successfully.";
            $_SESSION['message_type'] = "success";
            header("Location: ./"); // Redirect to documents list or another page
            exit();
        } else {
            $_SESSION['message'] = "Error deleting document from database.";
            $_SESSION['message_type'] = "danger";
        }
    } else {
        $_SESSION['message'] = "Document not found in the database.";
        $_SESSION['message_type'] = "danger";
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Document - GD Edu Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../adminPanel/css/style.css">
    <style>
        .sidebar {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row flex-nowrap">
            <!-- Sidebar -->
            <div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 sidebar">
                <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 min-vh-100">
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
                            <a href="../Courses/" class="nav-link">
                                <i class="bi bi-book me-2"></i> Courses
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="./" class="nav-link active">
                                <i class="bi bi-file-earmark-text me-2"></i> Documents
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Messages/index.php" class="nav-link">
                                <i class="bi bi-chat-dots me-2"></i> Messages
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
            <div class="col py-3 main-content">
                <div class="container mt-5">
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h3 class="mb-0">Add New Document</h3>
                                </div>
                                <div class="card-body">
                                    <!-- Error Handling -->
                                    <?php if (!empty($errors)): ?>
                                        <div class="alert alert-danger">
                                            <?php foreach ($errors as $error): ?>
                                                <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <form method="POST" enctype="multipart/form-data">
                                        <div class="mb-3">
                                            <label for="title" class="form-label">Document Title</label>
                                            <input type="text" class="form-control" id="title" name="title" 
                                                   placeholder="Enter document title" required
                                                   value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
                                        </div>

                                        <div class="mb-3">
                                            <label for="price" class="form-label">Price</label>
                                            <input type="number" class="form-control" id="price" name="price" 
                                                   placeholder="Enter price" required min="0" step="0.01"
                                                   value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : '0.00'; ?>">
                                        </div>

                                        <div class="mb-3">
                                            <label for="document" class="form-label">Upload Document</label>
                                            <input type="file" class="form-control" id="document" name="document" required>
                                        </div>

                                        <div class="d-flex justify-content-between">
                                            <a href="documents.php" class="btn btn-secondary">Cancel</a>
                                            <button type="submit" class="btn btn-primary">Add Document</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
