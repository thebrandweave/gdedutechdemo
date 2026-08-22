<?php
session_start();
require_once '../Configurations/config.php';
require_once '../vendor/autoload.php';

use Firebase\JWT\JWT;

header('Content-Type: application/json');

$jwtSecretKey = "your_secret_key_here";

// Get raw POST input or $_POST data
$rawInput = file_get_contents('php://input');
$json = json_decode($rawInput, true);

$credential = $_POST['credential'] ?? $json['credential'] ?? '';

if (empty($credential)) {
    echo json_encode(['status' => 'error', 'message' => 'Google credential token missing.']);
    exit();
}

// Helper to decode base64url
function base64UrlDecode($input) {
    $remainder = strlen($input) % 4;
    if ($remainder) {
        $padlen = 4 - $remainder;
        $input .= str_repeat('=', $padlen);
    }
    return base64_decode(strtr($input, '-_', '+/'));
}

// Decode Google JWT ID Token payload (2nd part of JWT)
$tokenParts = explode('.', $credential);
if (count($tokenParts) < 2) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Google token format.']);
    exit();
}

$payloadJson = base64UrlDecode($tokenParts[1]);
$googleUser = json_decode($payloadJson, true);

if (!$googleUser || empty($googleUser['email'])) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to decode Google user data.']);
    exit();
}

$email = filter_var(trim($googleUser['email']), FILTER_VALIDATE_EMAIL);
if (!$email) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email received from Google.']);
    exit();
}

$firstName = trim($googleUser['given_name'] ?? $googleUser['name'] ?? 'Student');
$lastName = trim($googleUser['family_name'] ?? '');
$profileImage = $googleUser['picture'] ?? null;

// Check if user exists in database
$stmt = $conn->prepare("SELECT user_id, username, role, status FROM Users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    // Existing user login
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user['status'] !== 'active') {
        echo json_encode(['status' => 'error', 'message' => 'Your account is ' . $user['status'] . '. Please contact support.']);
        exit();
    }

    $userId = $user['user_id'];
    $username = $user['username'];
    $role = $user['role'];
} else {
    $stmt->close();

    // New user registration via Google
    $baseUsername = strtolower(explode('@', $email)[0]);
    $baseUsername = preg_replace('/[^a-zA-Z0-9_]/', '', $baseUsername);
    $username = $baseUsername;

    // Ensure username is unique
    $checkUser = $conn->prepare("SELECT user_id FROM Users WHERE username = ?");
    $counter = 1;
    while (true) {
        $checkUser->bind_param("s", $username);
        $checkUser->execute();
        $res = $checkUser->get_result();
        if ($res->num_rows === 0) {
            break;
        }
        $username = $baseUsername . rand(100, 999);
    }
    $checkUser->close();

    // Random secure password hash
    $randomPassword = bin2hex(random_bytes(16));
    $passwordHash = password_hash($randomPassword, PASSWORD_BCRYPT);
    $role = 'student';
    $status = 'active';

    $insertStmt = $conn->prepare("INSERT INTO Users (username, password_hash, email, first_name, last_name, profile_image, role, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $insertStmt->bind_param("ssssssss", $username, $passwordHash, $email, $firstName, $lastName, $profileImage, $role, $status);

    if ($insertStmt->execute()) {
        $userId = $insertStmt->insert_id;
        $insertStmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to create user account via Google.']);
        exit();
    }
}

// Generate JWT token
$jwtPayload = [
    'iss' => 'http://localhost',
    'aud' => 'http://localhost',
    'iat' => time(),
    'exp' => time() + 86400,
    'user_id' => $userId,
    'username' => $username,
    'role' => $role
];

$jwt = JWT::encode($jwtPayload, $jwtSecretKey, 'HS256');

// Set cookie & session
setcookie("auth_token", $jwt, time() + 86400, "/", "", false, true);
$_SESSION['user_id'] = $userId;
$_SESSION['username'] = $username;
$_SESSION['role'] = $role;

// Redirect target
$redirectUrl = 'index.php';
if ($role === 'admin') {
    $redirectUrl = '../adminPanel/';
} elseif ($role === 'Staff') {
    $redirectUrl = '../staffPanel/';
}

echo json_encode([
    'status' => 'success',
    'message' => 'Google Login successful!',
    'redirect' => $redirectUrl
]);
exit();
