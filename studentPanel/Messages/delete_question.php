<?php
session_start();
require_once '../../Configurations/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

// Check if question_id is provided
if (!isset($_POST['question_id'])) {
    echo json_encode(['success' => false, 'message' => 'Question ID not provided']);
    exit();
}

$question_id = mysqli_real_escape_string($conn, $_POST['question_id']);
$user_id = $_SESSION['user_id'];

// Verify ownership of the question
$check_query = "SELECT user_id FROM StudentQuestions WHERE question_id = '$question_id'";
$check_result = mysqli_query($conn, $check_query);
$question = mysqli_fetch_assoc($check_result);

if (!$question || $question['user_id'] != $user_id) {
    echo json_encode(['success' => false, 'message' => 'Not authorized to delete this question']);
    exit();
}

// Delete associated answers first (due to foreign key constraints)
$delete_answers = "DELETE FROM StudentAnswers WHERE question_id = '$question_id'";
mysqli_query($conn, $delete_answers);

// Delete the question
$delete_query = "DELETE FROM StudentQuestions WHERE question_id = '$question_id' AND user_id = '$user_id'";
$result = mysqli_query($conn, $delete_query);

if ($result) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
} 