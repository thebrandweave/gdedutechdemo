<?php
session_start();
require_once './Configurations/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: career.php');
    exit();
}

// Validate required fields
$required_fields = ['job_id', 'first_name', 'last_name', 'email', 'phone', 'terms'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        $_SESSION['error'] = "Please fill in all required fields.";
        header('Location: apply.php?job_id=' . $_POST['job_id']);
        exit();
    }
}

// Handle resume upload
$resume_path = '';
if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
    $file_info = pathinfo($_FILES['resume']['name']);
    if (strtolower($file_info['extension']) !== 'pdf') {
        $_SESSION['error'] = "Only PDF files are allowed for resume.";
        header('Location: apply.php?job_id=' . $_POST['job_id']);
        exit();
    }

    // Create unique filename
    $resume_filename = uniqid('resume_') . '.pdf';
    $upload_dir = 'Uploads/Resumes/';
    
    // Create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $resume_path = $upload_dir . $resume_filename;
    
    if (!move_uploaded_file($_FILES['resume']['tmp_name'], $resume_path)) {
        $_SESSION['error'] = "Failed to upload resume.";
        header('Location: apply.php?job_id=' . $_POST['job_id']);
        exit();
    }
}

try {
    // Prepare SQL statement
    $sql = "INSERT INTO job_applications (
        job_id, 
        first_name, 
        last_name, 
        email, 
        phone, 
        resume_path, 
        cover_letter, 
        portfolio_url, 
        application_date,
        status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'Pending')";

    // Create variables for binding
    $job_id = $_POST['job_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $cover_letter = $_POST['cover_letter'] ?? '';
    $portfolio = $_POST['portfolio'] ?? '';

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "isssssss",
        $job_id,
        $first_name,
        $last_name,
        $email,
        $phone,
        $resume_path,
        $cover_letter,
        $portfolio
    );

    if ($stmt->execute()) {
        $_SESSION['success'] = "Your application has been submitted successfully!";
        header('Location: career.php');
        exit();
    } else {
        throw new Exception("Failed to submit application");
    }

} catch (Exception $e) {
    // Delete uploaded file if database insertion fails
    if (!empty($resume_path) && file_exists($resume_path)) {
        unlink($resume_path);
    }
    
    $_SESSION['error'] = "An error occurred while submitting your application. Please try again.";
    header('Location: apply.php?job_id=' . $_POST['job_id']);
    exit();
}