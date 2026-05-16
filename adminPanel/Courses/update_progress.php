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
// update_progress.php
require_once '../../Configurations/config.php';

if (!isset($_SESSION['user_id'])) {
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
    
    mysqli_commit($conn);
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    mysqli_rollback($conn);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}