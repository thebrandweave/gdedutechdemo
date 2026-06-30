<?php
// Secure and public helper to download QR code image
if (isset($_GET['student_id'])) {
    $student_id = preg_replace('/[^a-zA-Z0-9]/', '', $_GET['student_id']);
    
    if (empty($student_id)) {
        die("Invalid Student ID.");
    }
    
    // Build the dynamic URL for the QR code to redirect to
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? '') == 443) ? "https://" : "http://";
    $domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $path = (strpos($domain, 'gdedutech.com') !== false) ? "/verify_certificate.php" : "/gdedutechdemo/verify_certificate.php";
    $verify_url = $protocol . $domain . $path . "?student_id=" . $student_id;
    
    $qr_api_url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($verify_url);
    
    // Fetch QR code image bytes
    $image_data = @file_get_contents($qr_api_url);
    if ($image_data !== false) {
        header('Content-Type: image/png');
        header('Content-Disposition: attachment; filename="' . $student_id . '_qr.png"');
        echo $image_data;
        exit();
    } else {
        die("Error generating QR code for download.");
    }
} else {
    die("Student ID parameter is required.");
}
?>
