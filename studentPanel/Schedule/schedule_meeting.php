<?php
session_start();
require_once '../../Configurations/config.php';

// Verify student is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_SESSION['user_id'];
    $staff_id = mysqli_real_escape_string($conn, $_POST['staff_id']);
    $subject = mysqli_real_escape_string($conn, trim($_POST['subject']));
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));
    $meeting_date = mysqli_real_escape_string($conn, $_POST['meeting_date']);
    $meeting_time = mysqli_real_escape_string($conn, $_POST['meeting_time']);
    $meeting_link = mysqli_real_escape_string($conn, trim($_POST['meeting_link']));

    // Validation
    $errors = [];

    // Validate staff exists and is active
    $staff_check = "SELECT user_id FROM Users 
                   WHERE user_id = ? AND role = 'Staff' AND status = 'active'";
    $stmt = mysqli_prepare($conn, $staff_check);
    mysqli_stmt_bind_param($stmt, 'i', $staff_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) === 0) {
        $errors[] = "Invalid staff selection.";
    }

    // Validate date and time
    $meeting_datetime = strtotime($meeting_date . ' ' . $meeting_time);
    if ($meeting_datetime <= time()) {
        $errors[] = "Please select a future date and time.";
    }

    // Validate meeting link format
    if (!filter_var($meeting_link, FILTER_VALIDATE_URL)) {
        $errors[] = "Please enter a valid meeting link.";
    }

    // Check for empty fields
    if (empty($subject) || empty($description)) {
        $errors[] = "All fields are required.";
    }

    // Check subject length
    if (strlen($subject) > 255) {
        $errors[] = "Subject must be less than 255 characters.";
    }

    // If no errors, proceed with scheduling
    if (empty($errors)) {
        // Insert the meeting
        $query = "INSERT INTO meeting_schedules 
                 (student_id, staff_id, subject, description, meeting_date, meeting_time, meeting_link) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'iisssss', 
            $student_id, 
            $staff_id, 
            $subject, 
            $description, 
            $meeting_date, 
            $meeting_time,
            $meeting_link
        );

        if (mysqli_stmt_execute($stmt)) {
            // Get the staff's email for notification
            $staff_email_query = "SELECT email, first_name, last_name FROM Users WHERE user_id = ?";
            $stmt = mysqli_prepare($conn, $staff_email_query);
            mysqli_stmt_bind_param($stmt, 'i', $staff_id);
            mysqli_stmt_execute($stmt);
            $staff_result = mysqli_stmt_get_result($stmt);
            $staff_data = mysqli_fetch_assoc($staff_result);

            // Get student's name for the notification
            $student_query = "SELECT first_name, last_name FROM Users WHERE user_id = ?";
            $stmt = mysqli_prepare($conn, $student_query);
            mysqli_stmt_bind_param($stmt, 'i', $student_id);
            mysqli_stmt_execute($stmt);
            $student_result = mysqli_stmt_get_result($stmt);
            $student_data = mysqli_fetch_assoc($student_result);

            // Insert notification for staff
            $notification_message = sprintf(
                "New meeting request from %s %s for %s at %s",
                $student_data['first_name'],
                $student_data['last_name'],
                date('d M Y', strtotime($meeting_date)),
                date('h:i A', strtotime($meeting_time))
            );

            $notification_query = "INSERT INTO Notifications (user_id, message) VALUES (?, ?)";
            $stmt = mysqli_prepare($conn, $notification_query);
            mysqli_stmt_bind_param($stmt, 'is', $staff_id, $notification_message);
            mysqli_stmt_execute($stmt);

            // Log the activity
            $activity_description = "Scheduled a meeting with " . $staff_data['first_name'] . " " . $staff_data['last_name'];
            $activity_query = "INSERT INTO ActivityLog (user_id, activity_type, activity_description) 
                             VALUES (?, 'meeting_scheduled', ?)";
            $stmt = mysqli_prepare($conn, $activity_query);
            mysqli_stmt_bind_param($stmt, 'is', $student_id, $activity_description);
            mysqli_stmt_execute($stmt);

            $_SESSION['success'] = "Meeting scheduled successfully. Waiting for staff approval.";
        } else {
            $_SESSION['error'] = "Error scheduling meeting. Please try again.";
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }
}

header('Location: index.php');
exit();