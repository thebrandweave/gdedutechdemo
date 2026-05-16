<?php
session_start();
require_once '../../Configurations/config.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

// Check if course_id is provided
if (!isset($_GET['course_id'])) {
    header("Location: my_courses.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$course_id = intval($_GET['course_id']);

// Debug log
error_log("Checking certificate for User ID: $user_id, Course ID: $course_id");

// Fetch course and enrollment details with certificate information
$course_query = "SELECT c.title, c.course_id, e.assessment_status, cert.certificate_url 
                 FROM Courses c
                 JOIN Enrollments e ON c.course_id = e.course_id
                 LEFT JOIN Certificates cert ON cert.course_id = c.course_id 
                    AND cert.student_id = e.student_id
                 WHERE c.course_id = ? AND e.student_id = ?";
$course_stmt = $conn->prepare($course_query);
$course_stmt->bind_param('ii', $course_id, $user_id);
$course_stmt->execute();
$course = $course_stmt->get_result()->fetch_assoc();

// Debug course data
error_log("Course data: " . print_r($course, true));

// Get admin contact details from settings
$contact_query = "SELECT value FROM AdminSettings WHERE setting_key = 'support_email'";
$contact_result = $conn->query($contact_query);
$support_email = $contact_result->fetch_assoc()['value'] ?? 'support@gdedutech.com';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Certificate</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 40px 0;
        }
        .certificate-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            padding: 30px;
        }
        .contact-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
        .certificate-status {
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            background: #e9ecef;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="certificate-container">
            <div class="text-center mb-4">
                <h2>Course Certificate</h2>
                <p class="text-muted"><?php echo htmlspecialchars($course['title'] ?? 'Course'); ?></p>
            </div>

            <div class="certificate-status">
    <?php if ($course['certificate_url']): ?>
        <?php if (file_exists($course['certificate_url'])): ?>
            <div class="text-center">
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>Your certificate is ready!
                </div>
                <a href="<?php echo htmlspecialchars($course['certificate_url']); ?>" 
                   class="btn btn-primary" 
                   target="_blank">
                    <i class="bi bi-file-earmark-pdf me-2"></i>View Certificate
                </a>
                <a href="<?php echo htmlspecialchars($course['certificate_url']); ?>" 
                   class="btn btn-success" 
                   download>
                    <i class="bi bi-download me-2"></i>Download Certificate
                </a>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Certificate file is being processed. Please check back later.
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="alert alert-info">
            <h4 class="alert-heading"><i class="bi bi-info-circle me-2"></i>Certificate Status</h4>
            <p>Your certificate will be issued soon. Please allow 2-3 working days for processing.</p>
            <hr>
            <p class="mb-0">You will be notified once your certificate is ready.</p>
        </div>
    <?php endif; ?>
</div>

            <div class="contact-info">
                <h5><i class="bi bi-headset me-2"></i>Need Assistance?</h5>
                <p>If you have any questions about your certificate, please contact us:</p>
                <ul class="list-unstyled">
                    <li><i class="bi bi-envelope me-2"></i>Email: <?php echo htmlspecialchars($support_email); ?></li>
                    <li><i class="bi bi-telephone me-2"></i>Phone: +91 9876543210</li>
                    <li><i class="bi bi-clock me-2"></i>Working Hours: Monday to Friday, 9 AM - 5 PM</li>
                </ul>
            </div>

            <div class="mt-4 text-center">
                <a href="course_content.php?id=<?php echo $course_id; ?>" 
                   class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Course
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
