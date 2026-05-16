<?php
// Add after successful course enrollment
if ($stmt->execute()) {
    logActivity($conn, $_SESSION['user_id'], 'course_enrolled', "Enrolled in course: $course_title", $course_id);
    // ... rest of your enrollment success handling
}
