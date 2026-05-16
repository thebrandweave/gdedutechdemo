<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
	header('Location: ../admin_login.php');
	exit();
}

require_once '../../Configurations/config.php';

// Resolve project root reliably across environments
function getProjectRootPath() {
	$dir = realpath(__DIR__ . '/../../..');
	if ($dir && is_dir($dir)) return rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
	$doc = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $_SERVER['DOCUMENT_ROOT'] ?? ''), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
	// assume project folder name is gdedutech.in
	$guess = $doc . 'gdedutech.in' . DIRECTORY_SEPARATOR;
	if (is_dir($guess)) return $guess;
	return $doc; // best-effort
}

// Relative uploads base path
function getUploadsBasePath() {
	$relative = '../../uploads/'; // Using relative path instead of absolute path
	return $relative;
}

$error = '';
$success = '';
$createdCategoryId = 0;

// Create category inline
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_category'])) {
    $newCat = trim($_POST['new_category_name'] ?? '');
    if ($newCat !== '') {
        $insCat = $conn->prepare("INSERT INTO BlogCategories (name) VALUES (?)");
        $insCat->bind_param('s', $newCat);
        if ($insCat->execute()) {
            $createdCategoryId = $insCat->insert_id;
            $success = 'Category created.';
        } else {
            $error = 'Failed to create category: ' . $conn->error;
        }
    } else {
        $error = 'Category name cannot be empty.';
    }
}

// Load categories for dropdown
$categories = [];
$catsRes = $conn->query("SELECT category_id, name FROM BlogCategories ORDER BY name ASC");
if ($catsRes) {
    while ($c = $catsRes->fetch_assoc()) { $categories[] = $c; }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['create_category'])) {
	$title = trim($_POST['title'] ?? '');
	$content = $_POST['content'] ?? '';
	$status = $_POST['status'] ?? 'draft';
	$author_id = intval($_SESSION['user_id']);
	$category_id = intval($_POST['category_id'] ?? 0);
	$coverFileName = null;
	$socialLinks = [];

    // Handle cover image upload
    if (!empty($_FILES['cover_image']['name']) && isset($_FILES['cover_image']['error']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = getUploadsBasePath() . 'blogs' . DIRECTORY_SEPARATOR;
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0775, true);
        }
        $ext = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
        $ext = preg_replace('/[^a-zA-Z0-9]/', '', $ext);
        $coverFileName = time() . '_blog_cover.' . $ext;
        $target = $uploadDir . $coverFileName;
        if (!is_uploaded_file($_FILES['cover_image']['tmp_name']) || !@move_uploaded_file($_FILES['cover_image']['tmp_name'], $target)) {
            $error = 'Failed to upload cover image.';
        }
    }

	// Handle social links
	if (!empty($_POST['social_platform']) && !empty($_POST['social_url'])) {
		foreach ($_POST['social_platform'] as $key => $platform) {
			if (!empty($platform) && !empty($_POST['social_url'][$key])) {
				$socialLinks[] = [
					'platform' => $platform,
					'url' => $_POST['social_url'][$key]
				];
			}
		}
	}

	if (!$error) {
		$stmt = $conn->prepare("INSERT INTO Blogs (main_cover_image, title, content, author_id, status, category_id) VALUES (?, ?, ?, ?, ?, NULLIF(?, 0))");
		$stmt->bind_param('sssisi', $coverFileName, $title, $content, $author_id, $status, $category_id);
        if ($stmt->execute()) {
            $newBlogId = $stmt->insert_id;

            // Handle BlogSections (subtitles)
            $sectionTitles = $_POST['section_title'] ?? [];
            $sectionContents = $_POST['section_content'] ?? [];
            $sectionOrders = $_POST['section_order'] ?? [];
            $sectionImages = $_FILES['section_image'] ?? null;

            $sectionsDir = getUploadsBasePath() . 'blogs' . DIRECTORY_SEPARATOR . 'sections' . DIRECTORY_SEPARATOR;
            if (!is_dir($sectionsDir)) {
                @mkdir($sectionsDir, 0775, true);
            }

            if (is_array($sectionTitles)) {
                for ($i = 0; $i < count($sectionTitles); $i++) {
                    $st = trim($sectionTitles[$i] ?? '');
                    $sc = $sectionContents[$i] ?? '';
                    $so = intval($sectionOrders[$i] ?? ($i + 1));
                    $imgName = null;

                    if ($sectionImages && isset($sectionImages['error'][$i]) && $sectionImages['error'][$i] === UPLOAD_ERR_OK && !empty($sectionImages['name'][$i])) {
                        $ext = pathinfo($sectionImages['name'][$i], PATHINFO_EXTENSION);
                        $ext = preg_replace('/[^a-zA-Z0-9]/', '', $ext);
                        $imgName = time() . '_' . $i . '_section.' . $ext;
                        @move_uploaded_file($sectionImages['tmp_name'][$i], $sectionsDir . $imgName);
                    }

                    if ($st !== '' || $sc !== '' || $imgName) {
                        $ins = $conn->prepare("INSERT INTO BlogSections (blog_id, title, content, image, section_order) VALUES (?, ?, ?, ?, ?)");
                        $ins->bind_param('isssi', $newBlogId, $st, $sc, $imgName, $so);
                        $ins->execute();
                    }
                }
            }

            // Insert social links
            foreach ($socialLinks as $link) {
                $socialStmt = $conn->prepare("INSERT INTO social_links (target_type, target_id, platform, url) VALUES ('blog', ?, ?, ?)");
                $socialStmt->bind_param('iss', $newBlogId, $link['platform'], $link['url']);
                $socialStmt->execute();
            }

            $_SESSION['message'] = 'Blog created successfully.';
            $_SESSION['message_type'] = 'success';
            header('Location: ./');
            exit();
        } else {
			$error = 'Error creating blog: ' . $conn->error;
		}
	}
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Add Blog - GD Edu Tech</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
	<link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 sidebar">
                <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 min-vh-100">
                    <a href="#" class="d-flex align-items-center pb-3 mb-md-1 mt-md-3 me-md-auto text-white text-decoration-none">
                        <span class="fs-5 fw-bolder" style="display: flex;align-items:center;color:black;"><img height="35px" src="../images/edutechLogo.png" alt="">&nbsp; GD Edu Tech</span>
                    </a>
                    <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100" id="menu">
                        <li class="w-100">
                            <a href="../" class="nav-link">
                                <i class="bi bi-speedometer2 me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Categories/" class="nav-link">
                                <i class="bi bi-grid me-2"></i> Categories
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Courses/" class="nav-link">
                                <i class="bi bi-book me-2"></i> Courses
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Blogs/" class="nav-link active">
                                <i class="bi bi-journal-text me-2"></i> Blogs
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Events/" class="nav-link">
                                <i class="bi bi-calendar2-event me-2"></i> Events
                            </a>
                        </li>
                        <li class="w-100 dropdown">
                            <a href="#" class="nav-link dropdown-toggle" id="quizDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-lightbulb me-2"></i> Quick Links
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="quizDropdown">
                                <li><a class="dropdown-item" href="../Career/index.php">Career portal</a></li>
                                <li><a class="dropdown-item" href="../Shop/shop.php">Shop</a></li>
                            </ul>
                        </li>
                        <li class="w-100">
                            <a href="../Schedule/index.php" class="nav-link">
                                <i class="bi bi-calendar-event me-2"></i> Schedule
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Messages/index.php" class="nav-link">
                                <i class="bi bi-chat-dots me-2"></i> Messages
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../FAQ/" class="nav-link">
                                <i class="bi bi-question-circle me-2"></i> FAQ
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Users/" class="nav-link">
                                <i class="bi bi-people me-2"></i> Users
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../manage_qr.php" class="nav-link">
                                <i class="bi bi-qr-code me-2"></i> Payment QR
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../pending_payments.php" class="nav-link">
                                <i class="bi bi-credit-card me-2"></i> Pending Payments
                            </a>
                        </li>
                        <li class="w-100 mt-auto">
                            <a href="../logout.php" class="nav-link text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col py-3">
                <div class="container-fluid">
                    <div class="row mb-4">
                        <div class="col">
                            <h2>Add New Blog</h2>
                            <p class="text-muted">Create and publish blog posts</p>
                        </div>
                        <div class="col-auto">
                            <a href="./" class="btn btn-outline-primary"><i class="bi bi-arrow-left me-2"></i>Back</a>
                        </div>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form method="post" enctype="multipart/form-data" class="card p-3">
                        <div class="mb-3">
                            <label class="form-label d-block">Category</label>
                            <div class="dropdown" data-bs-auto-close="outside">
                                <?php 
                                    $selectedCatId = $createdCategoryId ?: 0; 
                                    $selectedCatName = '-- No category --';
                                    foreach ($categories as $cat) {
                                        if (intval($cat['category_id']) === intval($selectedCatId)) { $selectedCatName = $cat['name']; break; }
                                    }
                                ?>
                                <button id="categoryDropdownBtn" class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span id="categoryDropdownLabel"><?php echo htmlspecialchars($selectedCatName); ?></span>
                                </button>
                                <input type="hidden" name="category_id" id="categoryIdInput" value="<?php echo intval($selectedCatId); ?>">
                                <div class="dropdown-menu p-2" style="min-width: 280px; max-height: 320px; overflow:auto;">
                                    <div class="list-group list-group-flush mb-2">
                                        <button type="button" class="list-group-item list-group-item-action category-option" data-id="0">-- No category --</button>
                                        <?php foreach ($categories as $cat): ?>
                                            <button type="button" class="list-group-item list-group-item-action category-option" data-id="<?php echo intval($cat['category_id']); ?>"><?php echo htmlspecialchars($cat['name']); ?></button>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="dropdown-divider"></div>
                                    <div class="px-1 py-2">
                                        <div class="mb-1 small text-muted">Create new category</div>
                                        <div class="input-group">
                                            <input type="text" name="new_category_name" class="form-control" placeholder="New category name">
                                            <button class="btn btn-outline-primary" name="create_category" value="1" type="submit" formnovalidate>Add</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cover Image</label>
                            <input type="file" name="cover_image" class="form-control" accept="image/*">
                            <small class="text-muted">Recommended size: 1200x600</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Content</label>
                            <textarea name="content" class="form-control" rows="8"></textarea>
                        </div>
                        <hr>
                        <h5 class="mb-3">Subtitles / Sections</h5>
                        <div id="sections-wrapper">
                            <div class="section-item border rounded p-3 mb-3">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Section Title</label>
                                        <input type="text" name="section_title[]" class="form-control" placeholder="Subtitle">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Order</label>
                                        <input type="number" name="section_order[]" class="form-control" value="1">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Image</label>
                                        <input type="file" name="section_image[]" class="form-control" accept="image/*">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Section Content</label>
                                        <textarea name="section_content[]" class="form-control" rows="4" placeholder="Write section content..."></textarea>
                                    </div>
                                    <div class="col-12 text-end">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-section">Remove</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" id="add-section" class="btn btn-sm btn-outline-primary mb-3"><i class="bi bi-plus-lg me-1"></i>Add Section</button>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="draft" selected>Draft</option>
                                <option value="published">Published</option>
                            </select>
                        </div>
                        
                        <!-- Social Links Section -->
                        <div class="mb-4">
                            <h5 class="mb-3"><i class="bi bi-link-45deg me-2"></i>Social Links</h5>
                            <div class="card">
                                <div class="card-body">
                                    <div id="socialLinksContainer">
                                        <div class="social-link-item row g-3 mb-3">
                                            <div class="col-md-4">
                                                <select name="social_platform[]" class="form-select">
                                                    <option value="">Select Platform</option>
                                                    <option value="facebook">Facebook</option>
                                                    <option value="twitter">Twitter</option>
                                                    <option value="instagram">Instagram</option>
                                                    <option value="linkedin">LinkedIn</option>
                                                    <option value="youtube">YouTube</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <input type="url" name="social_url[]" class="form-control" placeholder="https://...">
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-outline-danger w-100 remove-social-link" style="display: none;">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary" id="addSocialLink">
                                        <i class="bi bi-plus me-1"></i>Add Social Link
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Create</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function(){
            const wrapper = document.getElementById('sections-wrapper');
            const addBtn = document.getElementById('add-section');
            // category dropdown interaction
            document.addEventListener('click', function(e){
                if (e.target && e.target.classList.contains('category-option')) {
                    const id = e.target.getAttribute('data-id');
                    const label = e.target.textContent.trim();
                    const input = document.getElementById('categoryIdInput');
                    const lblEl = document.getElementById('categoryDropdownLabel');
                    if (input) input.value = id;
                    if (lblEl) lblEl.textContent = label;
                }
            });
            addBtn.addEventListener('click', function(){
                const tmpl = wrapper.querySelector('.section-item');
                const clone = tmpl.cloneNode(true);
                // reset fields
                clone.querySelectorAll('input').forEach(i => { if(i.type==='number'){ i.value = 1 } else { i.value = '' }});
                clone.querySelectorAll('textarea').forEach(t => t.value = '');
                wrapper.appendChild(clone);
            });
            wrapper.addEventListener('click', function(e){
                if(e.target && e.target.classList.contains('remove-section')){
                    const items = wrapper.querySelectorAll('.section-item');
                    if(items.length > 1){
                        e.target.closest('.section-item').remove();
                    }
                }
            });
        })();

        // Social Links Management
        document.getElementById('addSocialLink').addEventListener('click', function() {
            const container = document.getElementById('socialLinksContainer');
            const newItem = document.createElement('div');
            newItem.className = 'social-link-item row g-3 mb-3';
            newItem.innerHTML = `
                <div class="col-md-4">
                    <select name="social_platform[]" class="form-select">
                        <option value="">Select Platform</option>
                        <option value="facebook">Facebook</option>
                        <option value="twitter">Twitter</option>
                        <option value="instagram">Instagram</option>
                        <option value="linkedin">LinkedIn</option>
                        <option value="youtube">YouTube</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <input type="url" name="social_url[]" class="form-control" placeholder="https://...">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-danger w-100 remove-social-link">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            `;
            container.appendChild(newItem);
            updateRemoveButtons();
        });

        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-social-link')) {
                e.target.closest('.social-link-item').remove();
                updateRemoveButtons();
            }
        });

        function updateRemoveButtons() {
            const items = document.querySelectorAll('.social-link-item');
            items.forEach((item, index) => {
                const removeBtn = item.querySelector('.remove-social-link');
                if (items.length > 1) {
                    removeBtn.style.display = 'block';
                } else {
                    removeBtn.style.display = 'none';
                }
            });
        }

        // Initialize remove buttons
        updateRemoveButtons();
    </script>
</body>

</html>


