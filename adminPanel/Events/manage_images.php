<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../admin_login.php');
    exit();
}

require_once '../../Configurations/config.php';

$eventId = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;
if ($eventId <= 0) {
    header('Location: ./');
    exit();
}

// Get event details
$eventQuery = $conn->prepare("SELECT * FROM Events WHERE event_id = ?");
$eventQuery->bind_param('i', $eventId);
$eventQuery->execute();
$event = $eventQuery->get_result()->fetch_assoc();
if (!$event) {
    $_SESSION['message'] = 'Event not found.';
    $_SESSION['message_type'] = 'danger';
    header('Location: ./');
    exit();
}

$error = '';
$success = '';

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_image'])) {
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../uploads/events/images/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }
        
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $fileName = time() . '_event_image.' . $ext;
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $imageUrl = 'uploads/events/images/' . $fileName;
            $insertQuery = $conn->prepare("INSERT INTO events_images (event_id, image_url) VALUES (?, ?)");
            $insertQuery->bind_param('is', $eventId, $imageUrl);
            if ($insertQuery->execute()) {
                $success = 'Image uploaded successfully.';
            } else {
                $error = 'Failed to save image to database.';
            }
        } else {
            $error = 'Failed to upload image.';
        }
    } else {
        $error = 'Please select a valid image file.';
    }
}

// Handle image deletion
if (isset($_GET['delete_image'])) {
    $imageId = intval($_GET['delete_image']);
    $deleteQuery = $conn->prepare("DELETE FROM events_images WHERE id = ? AND event_id = ?");
    $deleteQuery->bind_param('ii', $imageId, $eventId);
    if ($deleteQuery->execute()) {
        $success = 'Image deleted successfully.';
    } else {
        $error = 'Failed to delete image.';
    }
}

// Get all images for this event
$imagesQuery = $conn->prepare("SELECT * FROM events_images WHERE event_id = ? ORDER BY created_at DESC");
$imagesQuery->bind_param('i', $eventId);
$imagesQuery->execute();
$images = $imagesQuery->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Event Images - GD Edu Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 sidebar">
            <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 min-vh-100">
                <a href="#" class="d-flex align-items-center pb-3 mb-md-1 mt-md-3 me-md-auto text-white text-decoration-none">
                    <span class="fs-5 fw-bolder" style="display:flex;align-items:center;color:black;"><img height="35px" src="../images/edutechLogo.png" alt="">&nbsp; GD Edu Tech</span>
                </a>
                <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100" id="menu">
                    <li class="w-100"><a href="../" class="nav-link"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
                    <li class="w-100"><a href="../Categories/" class="nav-link"><i class="bi bi-grid me-2"></i> Categories</a></li>
                    <li class="w-100"><a href="../Courses/" class="nav-link"><i class="bi bi-book me-2"></i> Courses</a></li>
                    <li class="w-100"><a href="../Blogs/" class="nav-link"><i class="bi bi-journal-text me-2"></i> Blogs</a></li>
                    <li class="w-100"><a href="../Events/" class="nav-link active"><i class="bi bi-calendar2-event me-2"></i> Events</a></li>
                    <li class="w-100 dropdown">
                        <a href="#" class="nav-link dropdown-toggle" id="quizDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-lightbulb me-2"></i> Quick Links
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="quizDropdown">
                            <li><a class="dropdown-item" href="../index.php">Career portal</a></li>
                            <li><a class="dropdown-item" href="./Shop/shop.php">Shop</a></li>
                        </ul>
                    </li>
                    <li class="w-100"><a href="../Schedule/" class="nav-link"><i class="bi bi-calendar-event me-2"></i> Schedule</a></li>
                    <li class="w-100"><a href="../Messages/" class="nav-link"><i class="bi bi-chat-dots me-2"></i> Messages</a></li>
                    <li class="w-100"><a href="../FAQ/" class="nav-link"><i class="bi bi-question-circle me-2"></i> FAQ</a></li>
                    <li class="w-100"><a href="../Users/" class="nav-link"><i class="bi bi-people me-2"></i> Users</a></li>
                    <li class="w-100"><a href="../manage_qr.php" class="nav-link"><i class="bi bi-qr-code me-2"></i> Payment QR</a></li>
                    <li class="w-100"><a href="../pending_payments.php" class="nav-link"><i class="bi bi-credit-card me-2"></i> Pending Payments</a></li>
                    <li class="w-100 mt-auto"><a href="../logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                </ul>
            </div>
        </div>

        <div class="col py-3">
            <div class="container-fluid">
                <div class="row mb-4">
                    <div class="col">
                        <h2>Manage Event Images</h2>
                        <p class="text-muted">Event: <?php echo htmlspecialchars($event['title']); ?></p>
                    </div>
                    <div class="col-auto">
                        <a href="./" class="btn btn-outline-primary"><i class="bi bi-arrow-left me-2"></i>Back to Events</a>
                    </div>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <!-- Upload Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-cloud-upload me-2"></i>Upload New Image</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" enctype="multipart/form-data">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label">Select Image</label>
                                    <input type="file" name="image" class="form-control" accept="image/*" required>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" name="upload_image" class="btn btn-primary w-100">
                                        <i class="bi bi-upload me-2"></i>Upload Image
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Images Grid -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-images me-2"></i>Event Images (<?php echo count($images); ?>)</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($images)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-image display-1 text-muted"></i>
                                <p class="text-muted mt-3">No images uploaded yet.</p>
                            </div>
                        <?php else: ?>
                            <div class="row g-3">
                                <?php foreach ($images as $image): ?>
                                    <div class="col-md-4 col-lg-3">
                                        <div class="card">
                                            <img src="../../<?php echo htmlspecialchars($image['image_url']); ?>" class="card-img-top" style="height: 200px; object-fit: cover;" alt="Event Image">
                                            <div class="card-body p-2">
                                                <small class="text-muted">
                                                    <i class="bi bi-calendar me-1"></i>
                                                    <?php echo date('M d, Y H:i', strtotime($image['created_at'])); ?>
                                                </small>
                                            </div>
                                            <div class="card-footer p-2">
                                                <a href="?event_id=<?php echo $eventId; ?>&delete_image=<?php echo $image['id']; ?>" 
                                                   class="btn btn-sm btn-outline-danger w-100" 
                                                   onclick="return confirm('Delete this image?')">
                                                    <i class="bi bi-trash me-1"></i>Delete
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
