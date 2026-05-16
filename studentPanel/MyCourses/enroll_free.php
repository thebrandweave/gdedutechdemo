<?php
session_start();
require_once '../../Configurations/config.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_id'])) {
    $course_id = intval($_POST['course_id']);
    $user_id = $_SESSION['user_id'];

    // Verify that the course is free
    $course_check = $conn->prepare("SELECT price FROM Courses WHERE course_id = ? AND status = 'published'");
    $course_check->bind_param("i", $course_id);
    $course_check->execute();
    $result = $course_check->get_result();
    
    if ($result->num_rows > 0) {
        $course = $result->fetch_assoc();
        
        if ($course['price'] == 0) {
            // Enroll the user
            $enroll_stmt = $conn->prepare("
                INSERT INTO Enrollments (
                    student_id, 
                    course_id, 
                    purchase_date, 
                    payment_status, 
                    access_status
                ) VALUES (?, ?, NOW(), 'completed', 'active')
            ");
            $enroll_stmt->bind_param("ii", $user_id, $course_id);
            
            if ($enroll_stmt->execute()) {
                header("Location: course_content.php?id=" . $course_id);
                exit();
            }
        }
    }
}

// If something goes wrong, redirect to the course page
header("Location: course.php?id=" . $course_id);
exit();
?> 