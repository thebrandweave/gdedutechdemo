<?php
session_start();
require_once '../../Configurations/config.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];
$video_id = intval($data['video_id']);
$lesson_id = intval($data['lesson_id']);
$course_id = intval($data['course_id']);
$completed = $data['completed'] ? 1 : 0;

// Verify enrollment
$enrollment_check = "SELECT * FROM Enrollments 
                    WHERE student_id = ? AND course_id = ? 
                    AND access_status = 'active'";
$check_enrollment = mysqli_prepare($conn, $enrollment_check);
mysqli_stmt_bind_param($check_enrollment, 'ii', $user_id, $course_id);
mysqli_stmt_execute($check_enrollment);
if (mysqli_stmt_get_result($check_enrollment)->num_rows === 0) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Not enrolled in this course']);
    exit();
}

try {
    // Begin transaction
    mysqli_begin_transaction($conn);
    
    // Check if progress record exists
    $check_query = "SELECT * FROM UserProgress 
                    WHERE user_id = ? AND course_id = ? 
                    AND lesson_id = ? AND video_id = ?";
    $check_stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($check_stmt, 'iiii', 
        $user_id, $course_id, $lesson_id, $video_id);
    mysqli_stmt_execute($check_stmt);
    $result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($result) > 0) {
        // Update existing record
        $update_query = "UPDATE UserProgress 
                        SET completed = ?, updated_at = NOW() 
                        WHERE user_id = ? AND course_id = ? 
                        AND lesson_id = ? AND video_id = ?";
        $update_stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, 'iiiii', 
            $completed, $user_id, $course_id, $lesson_id, $video_id);
        mysqli_stmt_execute($update_stmt);
    } else {
        // Insert new record
        $insert_query = "INSERT INTO UserProgress 
                        (user_id, course_id, lesson_id, video_id, completed, created_at, updated_at) 
                        VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
        $insert_stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($insert_stmt, 'iiiii', 
            $user_id, $course_id, $lesson_id, $video_id, $completed);
        mysqli_stmt_execute($insert_stmt);
    }
    
    // Update overall course progress in Enrollments table
    $progress_query = "
        UPDATE Enrollments 
        SET progress = (
            SELECT (COUNT(CASE WHEN completed = 1 THEN 1 END) * 100.0 / COUNT(*))
            FROM UserProgress
            WHERE user_id = ? AND course_id = ?
        )
        WHERE student_id = ? AND course_id = ?
    ";
    
    $progress_stmt = mysqli_prepare($conn, $progress_query);
    mysqli_stmt_bind_param($progress_stmt, 'iiii', 
        $user_id, $course_id, $user_id, $course_id);
    mysqli_stmt_execute($progress_stmt);
    
    // Check if course is completed
    $completion_check_query = "
        SELECT 
            (SELECT COUNT(*) FROM Videos v 
             JOIN Lessons l ON v.lesson_id = l.lesson_id 
             WHERE l.course_id = ?) as total_videos,
            (SELECT COUNT(*) FROM UserProgress 
             WHERE user_id = ? AND course_id = ? AND completed = 1) as completed_videos
    ";
    
    $completion_stmt = mysqli_prepare($conn, $completion_check_query);
    mysqli_stmt_bind_param($completion_stmt, 'iii', 
        $course_id, $user_id, $course_id);
    mysqli_stmt_execute($completion_stmt);
    $completion_result = mysqli_stmt_get_result($completion_stmt);
    $completion_data = mysqli_fetch_assoc($completion_result);
    
    if ($completion_data['total_videos'] > 0 && 
        $completion_data['total_videos'] === $completion_data['completed_videos']) {
        // Update completion status in Enrollments
        $update_completion = "UPDATE Enrollments 
                            SET completion_status = 'completed' 
                            WHERE student_id = ? AND course_id = ?";
        $completion_update_stmt = mysqli_prepare($conn, $update_completion);
        mysqli_stmt_bind_param($completion_update_stmt, 'ii', 
            $user_id, $course_id);
        mysqli_stmt_execute($completion_update_stmt);
        
        // Generate certificate if not exists
        $cert_check = "SELECT * FROM Certificates 
                      WHERE student_id = ? AND course_id = ?";
        $cert_check_stmt = mysqli_prepare($conn, $cert_check);
        mysqli_stmt_bind_param($cert_check_stmt, 'ii', 
            $user_id, $course_id);
        mysqli_stmt_execute($cert_check_stmt);
        
        if (mysqli_stmt_get_result($cert_check_stmt)->num_rows === 0) {
            $cert_insert = "INSERT INTO Certificates 
                           (student_id, course_id, issue_date) 
                           VALUES (?, ?, NOW())";
            $cert_insert_stmt = mysqli_prepare($conn, $cert_insert);
            mysqli_stmt_bind_param($cert_insert_stmt, 'ii', 
                $user_id, $course_id);
            mysqli_stmt_execute($cert_insert_stmt);
        }
    }
    
    mysqli_commit($conn);
    echo json_encode([
        'success' => true,
        'progress' => $completion_data['completed_videos'] / $completion_data['total_videos'] * 100
    ]);
    
} catch (Exception $e) {
    mysqli_rollback($conn);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
