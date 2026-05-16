<?php

require_once '../Configurations/config.php';

$id = $_GET['id'];

$conn->query("
UPDATE student_feedback
SET status='rejected'
WHERE feedback_id='$id'
");

header("Location: ./feedback/feedback.php");

?>