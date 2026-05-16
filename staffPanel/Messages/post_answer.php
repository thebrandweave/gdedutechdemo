<?php
session_start();
require_once '../../Configurations/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question_id = $_POST['question_id'];
    $content = $_POST['content'];
    $user_id = $_SESSION['user_id'];

    // Insert the answer
    $insert_query = "INSERT INTO StudentAnswers (question_id, user_id, content) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insert_query);
    mysqli_stmt_bind_param($stmt, "iis", $question_id, $user_id, $content);
    
    // Update question status
    $update_query = "UPDATE StudentQuestions SET status = 'answered' WHERE question_id = ?";
    $update_stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($update_stmt, "i", $question_id);

    if (mysqli_stmt_execute($stmt) && mysqli_stmt_execute($update_stmt)) {
        header('Location: ./'); // Redirect back to the Messages page
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>