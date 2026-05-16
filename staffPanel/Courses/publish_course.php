<?php
session_start();

// Check if user is logged in and is staff
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Staff') {
    header('Location: ../staff_login.php');
    exit();
}

// Get staff details from session
$staff_id = $_SESSION['user_id'];
$staff_name = $_SESSION['username'] ?? 'Staff';
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
    
    header("Location: index.php");
    exit();
}
?>