<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: admin_login.php');
    exit();
}

require_once '../Configurations/config.php';

$error = '';
$success = '';

// Handle add social link
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_link'])) {
    $platform = trim($_POST['platform'] ?? '');
    $url = trim($_POST['url'] ?? '');
    
    if ($platform && $url) {
        $insertQuery = $conn->prepare("INSERT INTO social_links (target_type, target_id, platform, url) VALUES ('website', NULL, ?, ?)");
        $insertQuery->bind_param('ss', $platform, $url);
        
        if ($insertQuery->execute()) {
            $success = 'Social link added successfully.';
        } else {
            $error = 'Failed to add social link: ' . $conn->error;
        }
    } else {
        $error = 'Please fill in all fields.';
    }
}

// Handle delete social link
if (isset($_GET['delete'])) {
    $linkId = intval($_GET['delete']);
    $deleteQuery = $conn->prepare("DELETE FROM social_links WHERE id = ?");
    $deleteQuery->bind_param('i', $linkId);
    
    if ($deleteQuery->execute()) {
        $success = 'Social link deleted successfully.';
    } else {
        $error = 'Failed to delete social link.';
    }
}

// Get website social links
$linksQuery = $conn->query("SELECT * FROM social_links WHERE target_type = 'website' AND target_id IS NULL ORDER BY platform ASC");
$socialLinks = $linksQuery ? $linksQuery->fetch_all(MYSQLI_ASSOC) : [];

// Common platforms
$platforms = ['facebook', 'twitter', 'instagram', 'linkedin', 'youtube', 'tiktok', 'pinterest', 'snapchat', 'discord', 'telegram', 'whatsapp'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Website Social Links - GD Edu Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 sidebar">
            <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 min-vh-100">
                <a href="#" class="d-flex align-items-center pb-3 mb-md-1 mt-md-3 me-md-auto text-white text-decoration-none">
                    <span class="fs-5 fw-bolder" style="display:flex;align-items:center;color:black;"><img height="35px" src="images/edutechLogo.png" alt="">&nbsp; GD Edu Tech</span>
                </a>
                <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100" id="menu">
                    <li class="w-100"><a href="./" class="nav-link"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
                    <li class="w-100"><a href="Categories/" class="nav-link"><i class="bi bi-grid me-2"></i> Categories</a></li>
                    <li class="w-100"><a href="Courses/" class="nav-link"><i class="bi bi-book me-2"></i> Courses</a></li>
                    <li class="w-100"><a href="Blogs/" class="nav-link"><i class="bi bi-journal-text me-2"></i> Blogs</a></li>
                    <li class="w-100"><a href="Events/" class="nav-link"><i class="bi bi-calendar2-event me-2"></i> Events</a></li>
                    <li class="w-100"><a href="social_links.php" class="nav-link active"><i class="bi bi-link-45deg me-2"></i> Social Links</a></li>
                    <li class="w-100 dropdown">
                        <a href="#" class="nav-link dropdown-toggle" id="quizDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-lightbulb me-2"></i> Quick Links
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="quizDropdown">
                            <li><a class="dropdown-item" href="index.php">Career portal</a></li>
                            <li><a class="dropdown-item" href="Events/Shop/shop.php">Shop</a></li>
                        </ul>
                    </li>
                    <li class="w-100"><a href="Schedule/" class="nav-link"><i class="bi bi-calendar-event me-2"></i> Schedule</a></li>
                    <li class="w-100"><a href="Messages/" class="nav-link"><i class="bi bi-chat-dots me-2"></i> Messages</a></li>
                    <li class="w-100"><a href="FAQ/" class="nav-link"><i class="bi bi-question-circle me-2"></i> FAQ</a></li>
                    <li class="w-100"><a href="Users/" class="nav-link"><i class="bi bi-people me-2"></i> Users</a></li>
                    <li class="w-100"><a href="manage_qr.php" class="nav-link"><i class="bi bi-qr-code me-2"></i> Payment QR</a></li>
                    <li class="w-100"><a href="pending_payments.php" class="nav-link"><i class="bi bi-credit-card me-2"></i> Pending Payments</a></li>
                    <li class="w-100 mt-auto"><a href="logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                </ul>
            </div>
        </div>

        <div class="col py-3">
            <div class="container-fluid">
                <div class="row mb-4">
                    <div class="col">
                        <h2>Website Social Links</h2>
                        <p class="text-muted">Manage social media links for the website</p>
                    </div>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <!-- Add Social Link Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Add Social Link</h5>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Platform</label>
                                    <select name="platform" class="form-select" required>
                                        <option value="">Select Platform</option>
                                        <?php foreach ($platforms as $platform): ?>
                                            <option value="<?php echo htmlspecialchars($platform); ?>">
                                                <?php echo ucfirst($platform); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">URL</label>
                                    <input type="url" name="url" class="form-control" placeholder="https://..." required>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" name="add_link" class="btn btn-primary w-100">
                                        <i class="bi bi-plus me-1"></i>Add
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Social Links List -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-link-45deg me-2"></i>Website Social Links (<?php echo count($socialLinks); ?>)</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($socialLinks)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-link-45deg display-1 text-muted"></i>
                                <p class="text-muted mt-3">No social links added yet.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Platform</th>
                                            <th>URL</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($socialLinks as $link): ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-primary text-capitalize">
                                                        <?php echo htmlspecialchars($link['platform']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank" class="text-decoration-none">
                                                        <?php echo htmlspecialchars($link['url']); ?>
                                                        <i class="bi bi-box-arrow-up-right ms-1"></i>
                                                    </a>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <?php echo date('M d, Y H:i', strtotime($link['created_at'])); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <a href="?delete=<?php echo $link['id']; ?>" 
                                                       class="btn btn-sm btn-outline-danger" 
                                                       onclick="return confirm('Delete this social link?')">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
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
