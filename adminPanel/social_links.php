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

function getPlatformIcon($p) {
    $map = [
        'facebook' => 'bi-facebook text-primary',
        'twitter' => 'bi-twitter-x text-dark',
        'instagram' => 'bi-instagram text-danger',
        'linkedin' => 'bi-linkedin text-primary',
        'youtube' => 'bi-youtube text-danger',
        'whatsapp' => 'bi-whatsapp text-success',
        'telegram' => 'bi-telegram text-info',
        'discord' => 'bi-discord text-primary',
        'tiktok' => 'bi-tiktok text-dark',
        'pinterest' => 'bi-pinterest text-danger',
        'snapchat' => 'bi-snapchat text-warning'
    ];
    return $map[strtolower($p)] ?? 'bi-globe text-secondary';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Website Social Links - GD Edu Tech Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="../Images/Logos/GD_Only_logo.png">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0 flex-nowrap">
            
            <!-- Executive Sidebar -->
            <div class="col-auto col-md-3 col-xl-2 px-0 sidebar sticky-top vh-100 overflow-auto hide-scrollbar d-flex flex-column">
                <div class="p-3 border-bottom border-white border-opacity-10 d-flex align-items-center gap-2">
                    <img height="36" src="../Images/Logos/GD_Only_logo.png" alt="GD Logo">
                    <div>
                        <div class="fw-bold text-white fs-6">GD Edu Tech</div>
                      
                    </div>
                </div>

                <ul class="nav nav-pills flex-column mb-auto p-2 w-100" id="menu">
                    <li class="w-100"><a href="./" class="nav-link"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
                    <li class="w-100"><a href="Categories/" class="nav-link"><i class="bi bi-grid me-2"></i> Categories</a></li>
                    <li class="w-100"><a href="Admissions/" class="nav-link"><i class="bi bi-person-plus me-2"></i> Student Admission</a></li>
                    <li class="w-100"><a href="Courses/" class="nav-link"><i class="bi bi-book me-2"></i> Courses</a></li>
                    <li class="w-100"><a href="Applications/" class="nav-link"><i class="bi bi-journal-text me-2"></i> Scholarships</a></li>
                    <li class="w-100"><a href="./Events/" class="nav-link"><i class="bi bi-calendar2-event me-2"></i> Events</a></li>
                    <li class="w-100"><a href="./Career/" class="nav-link"><i class="bi bi-briefcase me-2"></i> Careers</a></li>
                    <li class="w-100"><a href="social_links.php" class="nav-link active"><i class="bi bi-link-45deg me-2"></i> Social Links</a></li>
                    <li class="w-100"><a href="Schedule/index.php" class="nav-link"><i class="bi bi-calendar-event me-2"></i> Schedule</a></li>
                    <li class="w-100"><a href="feedback/feedback.php" class="nav-link"><i class="bi bi-chat-square-heart me-2"></i> Feedback</a></li>
                    <li class="w-100"><a href="Messages/index.php" class="nav-link"><i class="bi bi-chat-dots me-2"></i> Messages</a></li>
                    <li class="w-100"><a href="FAQ/" class="nav-link"><i class="bi bi-question-circle me-2"></i> FAQ</a></li>
                    <li class="w-100"><a href="Users/" class="nav-link"><i class="bi bi-people me-2"></i> Users</a></li>
                    <li class="w-100"><a href="manage_qr.php" class="nav-link"><i class="bi bi-qr-code me-2"></i> Payment QR</a></li>
                    <li class="w-100"><a href="pending_payments.php" class="nav-link"><i class="bi bi-credit-card me-2"></i> Pending Payments</a></li>
                </ul>

                <div class="p-3 border-top border-white border-opacity-10 mt-auto">
                    <a href="logout.php" class="nav-link text-danger justify-content-center m-0">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </a>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="col min-vh-100 d-flex flex-column">
                
                <!-- Top Header -->
                <div class="bg-white border-bottom px-4 py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="fw-bold text-dark mb-0">Website Social Links</h4>
                        <span class="text-muted small">Configure global social media channels for navbar and footer</span>
                    </div>
                </div>

                <div class="p-4 flex-grow-1">
                    
                    <!-- Alert Messages -->
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                                <span class="fw-semibold"><?php echo htmlspecialchars($error); ?></span>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill fs-5"></i>
                                <span class="fw-semibold"><?php echo htmlspecialchars($success); ?></span>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Add Social Link Card -->
                    <div class="card shadow-sm border-0 rounded-4 mb-4">
                        <div class="card-header bg-white py-3 px-4 border-bottom">
                            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-plus-circle-fill text-primary me-2"></i>Add New Social Platform Link</h6>
                        </div>
                        <div class="card-body p-4">
                            <form method="post" autocomplete="off">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-4">
                                        <label class="form-label font-weight-semibold">Select Platform</label>
                                        <select name="platform" class="form-select" required>
                                            <option value="" disabled selected>Choose platform...</option>
                                            <?php foreach ($platforms as $platform): ?>
                                                <option value="<?php echo htmlspecialchars($platform); ?>">
                                                    <?php echo ucfirst($platform); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label font-weight-semibold">URL Address</label>
                                        <input type="url" name="url" class="form-control" placeholder="https://facebook.com/yourpage" required>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" name="add_link" class="btn btn-primary w-100 py-2.5">
                                            <i class="bi bi-plus-lg me-1"></i> Add Link
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Social Links List Card -->
                    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                        <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-link-45deg text-primary me-2"></i>Configured Website Social Links</h6>
                            <span class="badge bg-primary bg-opacity-10 text-primary border px-3 py-1.5 rounded-pill fw-semibold">
                                Total: <?php echo count($socialLinks); ?> Links
                            </span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Platform</th>
                                        <th>Target URL</th>
                                        <th>Scope</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($socialLinks)): ?>
                                        <?php foreach ($socialLinks as $link): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center fs-5" style="width: 40px; height: 40px;">
                                                            <i class="bi <?php echo getPlatformIcon($link['platform']); ?>"></i>
                                                        </div>
                                                        <strong class="text-dark fs-6"><?php echo ucfirst(htmlspecialchars($link['platform'])); ?></strong>
                                                    </div>
                                                </td>
                                                <td>
                                                    <a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank" rel="noopener" class="text-primary font-monospace text-decoration-none">
                                                        <?php echo htmlspecialchars($link['url']); ?>
                                                        <i class="bi bi-box-arrow-up-right ms-1 small"></i>
                                                    </a>
                                                </td>
                                                <td>
                                                    <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-2.5 py-1 rounded-pill">
                                                        Website Global
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <a href="social_links.php?delete=<?php echo $link['id']; ?>" class="action-icon text-danger" onclick="return confirm('Delete this social link?');" title="Delete Link">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">No website social links configured yet.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
