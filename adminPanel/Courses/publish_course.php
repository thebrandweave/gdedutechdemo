<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../admin_login.php');
    exit();
}

// Get admin details from session
$admin_name = $_SESSION['username'] ?? 'Admin';
?>
<?php
// publish_course.php
require_once '../../Configurations/config.php';

if (isset($_GET['id'])) {
    $course_id = intval($_GET['id']);
    
    // Update course status to published
    $query = "UPDATE Courses SET status = 'published' WHERE course_id = $course_id";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['message'] = "Course published successfully.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error publishing course: " . mysqli_error($conn);
        $_SESSION['message_type'] = "danger";
    }
    
    header("Location: courses.php");
    exit();
}
?>