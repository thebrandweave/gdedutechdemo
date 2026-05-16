<?php
session_start();
require_once '../../Configurations/config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../admin_login.php');
    exit();
}

// Check if a file was uploaded
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['certificate'])) {
    $user_id = $_POST['user_id'];
    $course_id = $_POST['course_id'];
    $file = $_FILES['certificate'];

    // Define the upload directory
    $uploadDir = '../../uploads/certificates/';
    $course_name = str_replace(' ', '_', strtolower($course_id)); // Assuming course_id is the course name
    $uploadFile = $uploadDir . $course_name . '_' . $user_id . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);

    // Move the uploaded file to the specified directory
    if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
       // File is successfully uploaded
        // Update the database with the new certificate URL
        $query = "UPDATE Certificates SET certificate_url = ? WHERE student_id = ? AND course_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'sis', $uploadFile, $user_id, $course_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Redirect to the same page to prevent re-submission
        header('Location: certificates.php');
        exit();
    } else {
        // Handle the error
        echo "Error uploading the file.";
    }
}

// Fetch users who have completed courses and assessments
$query = "
    SELECT 
        u.user_id, 
        u.first_name, 
        u.last_name, 
        c.title AS course_title, 
        e.course_id, 
        e.completion_status, 
        e.assessment_status,
        cert.certificate_url
    FROM 
        Enrollments e
    JOIN 
        Users u ON e.student_id = u.user_id
    JOIN 
        Courses c ON e.course_id = c.course_id
    LEFT JOIN
        Certificates cert ON cert.student_id = u.user_id AND cert.course_id = e.course_id
    WHERE 
        e.completion_status = 'completed' 
        AND e.assessment_status = 'completed'
    ORDER BY 
        u.first_name, u.last_name
";
$result = mysqli_query($conn, $query);

// Get admin details from session
$admin_name = $_SESSION['first_name'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificates - GD Edu Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/style.css">
    <script>
        // Function to hide the certificate upload form dynamically
        function hideForm(formId) {
            document.getElementById(formId).style.display = 'none';
        }
    </script>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 sidebar">
                <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 min-vh-100">
                    <a href="#" class="d-flex align-items-center pb-3 mb-md-1 mt-md-3 me-md-auto text-white text-decoration-none">
                        <span class="fs-5 fw-bolder" style="display: flex;align-items:center;color:black;"><img height="35px" src="../images/edutechLogo.png" alt="">&nbsp; GD Edu Tech</span>
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
                            <a href="." class="nav-link active">
                                <i class="bi bi-book me-2"></i> Courses
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
                            <a href="../Messages/" class="nav-link">
                                <i class="bi bi-chat-dots me-2"></i> Messages
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../FAQ/" class="nav-link">
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
                <div class="container-fluid">
                    <!-- Header -->
                    <div class="row mb-4">
                        <div class="col">
                            <h2>Certificates</h2>
                            <p class="text-muted">List of users who have completed courses and assessments</p>
                        </div>
                    </div>

                    <!-- Users Table -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Course</th>
                                    <th>Completion Status</th>
                                    <th>Assessment Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['course_title']); ?></td>
                                        <td><?php echo htmlspecialchars($row['completion_status']); ?></td>
                                        <td><?php echo htmlspecialchars($row['assessment_status']); ?></td>
                                        <td>
                                            <?php if ($row['certificate_url']): ?>
                                                <div class="btn-group">
                                                    <span class="btn btn-sm btn-success">
                                                        <i class="bi bi-check-circle-fill"></i> Completed
                                                    </span>
                                                </div>
                                            <?php else: ?>
                                                <!-- Upload form will be hidden after successful upload -->
                                                <form id="upload-form-<?php echo $row['user_id']; ?>" action="certificates.php" method="post" enctype="multipart/form-data">
                                                    <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                                                    <input type="hidden" name="course_id" value="<?php echo $row['course_id']; ?>">
                                                    <div class="input-group mb-3">
                                                        <input type="file" name="certificate" accept=".pdf,.jpg,.png" class="form-control" required>
                                                        <button type="submit" class="btn btn-sm btn-outline-primary">Upload</button>
                                                    </div>
                                                </form>
                                            <?php endif; ?>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
