<?php
require_once './Configurations/config.php';

$uploadDir = "uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$firstName = $_POST['firstName'];
$lastName  = $_POST['lastName'];
$address   = $_POST['address'];
$school    = $_POST['school'];
$phone     = $_POST['phone1'];
$course    = $_POST['course'];
$medium    = $_POST['medium'];
$lang      = $_POST['langTyped'];

function uploadFile($file, $uploadDir) {
    if ($file['name'] == "") return null;

    $fileName = time() . "_" . basename($file['name']);
    $targetFile = $uploadDir . $fileName;

    move_uploaded_file($file["tmp_name"], $targetFile);

    return $fileName;
}

$document = uploadFile($_FILES['document'], $uploadDir);
$photo    = uploadFile($_FILES['photo'], $uploadDir);

$stmt = $conn->prepare("INSERT INTO applications 
(first_name, last_name, address, school, phone, course, medium, languages, document, photo) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param("ssssssssss",
    $firstName,
    $lastName,
    $address,
    $school,
    $phone,
    $course,
    $medium,
    $lang,
    $document,
    $photo
);

if ($stmt->execute()) {
?>
<!DOCTYPE html>
<html>
<head>
    <title>Submission Successful</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial;
            background: linear-gradient(135deg, #0078a8, #cf5153);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .icon-wrapper {
    width: 90px;
    height: 90px;
    margin: 0 auto 15px;

    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.success-img {

    object-fit: contain;
}
        .card {
            background: white;
            padding: 40px 25px;
            border-radius: 15px;
            text-align: center;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .icon {
            font-size: 60px;
            color: green;
        }
        h2 {
            margin: 15px 0 10px;
        }
        p {
            color: #555;
        }
        .btn {
            margin-top: 20px;
            display: inline-block;
            padding: 12px 20px;
            background: #0078a8;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
        }
        .btn:hover {
            background: #005f85;
        }
    </style>
</head>
<body>

<div class="card">
  <div class="icon-wrapper">
    <img src="check.png" alt="Success" class="success-img">
</div>
    <h2>Application Submitted!</h2>
    <p>Thank you, <?php echo htmlspecialchars($firstName); ?>.<br>Your application has been recorded successfully.</p>
    
    <a href="index.php" class="btn">Submit Another</a>
</div>
<script>
setTimeout(() => {
    window.location.href = "index.php";
}, 2000);
</script>

</body>
</html>

<?php
} else {
    echo "Error: " . $stmt->error;
}
?>