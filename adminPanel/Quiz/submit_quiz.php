<?php
// Add after quiz completion
if ($stmt->execute()) {
    logActivity($conn, $_SESSION['user_id'], 'quiz_completed', "Completed quiz in $course_title with score: $score%", $quiz_id);
    // ... rest of your success handling
} 