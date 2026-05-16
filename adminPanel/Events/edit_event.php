<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
	header('Location: ../admin_login.php');
	exit();
}

require_once '../../Configurations/config.php';

function getUploadsBasePath() {
	$relative = '../../uploads/'; // Using relative path instead of absolute path
	return $relative;
}

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: ./'); exit(); }

$q = $conn->prepare("SELECT * FROM Events WHERE event_id = ?");
$q->bind_param('i', $id);
$q->execute();
$event = $q->get_result()->fetch_assoc();
if (!$event) { $_SESSION['message']='Event not found.'; $_SESSION['message_type']='danger'; header('Location: ./'); exit(); }

// Load existing images
$imagesQuery = $conn->prepare("SELECT * FROM events_images WHERE event_id = ? ORDER BY created_at DESC");
$imagesQuery->bind_param('i', $id);
$imagesQuery->execute();
$existingImages = $imagesQuery->get_result()->fetch_all(MYSQLI_ASSOC);

// Load existing social links
$socialQuery = $conn->prepare("SELECT * FROM social_links WHERE target_type = 'event' AND target_id = ? ORDER BY platform ASC");
$socialQuery->bind_param('i', $id);
$socialQuery->execute();
$existingSocialLinks = $socialQuery->get_result()->fetch_all(MYSQLI_ASSOC);

$error = '';
$createdCategoryId = 0;

// Inline create category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_category'])) {
    $newCat = trim($_POST['new_category_name'] ?? '');
    if ($newCat !== '') {
        $insCat = $conn->prepare("INSERT INTO EventCategories (name) VALUES (?)");
        $insCat->bind_param('s', $newCat);
        if ($insCat->execute()) {
            $createdCategoryId = $insCat->insert_id;
        } else {
            $error = 'Failed to create category: ' . $conn->error;
        }
    } else {
        $error = 'Category name cannot be empty.';
    }
}

// Load categories list
$eventCategories = [];
$catRes = $conn->query("SELECT category_id, name FROM EventCategories ORDER BY name ASC");
if ($catRes) { while ($c = $catRes->fetch_assoc()) { $eventCategories[] = $c; } }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['create_category'])) {
	$title = trim($_POST['title'] ?? '');
	$description = $_POST['description'] ?? '';
	$location = $_POST['location'] ?? '';
	$event_date = $_POST['event_date'] ?? null;
	$event_time = $_POST['event_time'] ?? null;
	$event_link = $_POST['event_link'] ?? '';
	$status = $_POST['status'] ?? 'upcoming';
    $category_id = intval($_POST['category_id'] ?? 0);
	$coverFileName = $event['main_cover_image'];
	$uploadedImages = [];
	$socialLinks = [];

	if (!empty($_FILES['cover_image']['name']) && isset($_FILES['cover_image']['error']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
		$dir = getUploadsBasePath() . 'events' . DIRECTORY_SEPARATOR;
		if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
		$ext = preg_replace('/[^a-zA-Z0-9]/','', pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION));
		$coverFileName = time() . '_event_cover.' . $ext;
		$target = $dir . $coverFileName;
		if (!is_uploaded_file($_FILES['cover_image']['tmp_name']) || !@move_uploaded_file($_FILES['cover_image']['tmp_name'], $target)) {
			$error = 'Failed to upload cover image.';
		}
	}

	// Handle additional event images
	if (!empty($_FILES['event_images']['name'][0]) && !$error) {
		$imagesDir = getUploadsBasePath() . 'events' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR;
		if (!is_dir($imagesDir)) { @mkdir($imagesDir, 0775, true); }
		
		foreach ($_FILES['event_images']['name'] as $key => $filename) {
			if ($_FILES['event_images']['error'][$key] === UPLOAD_ERR_OK) {
				$ext = preg_replace('/[^a-zA-Z0-9]/','', pathinfo($filename, PATHINFO_EXTENSION));
				$imageFileName = time() . '_' . $key . '_event_image.' . $ext;
				$target = $imagesDir . $imageFileName;
				if (move_uploaded_file($_FILES['event_images']['tmp_name'][$key], $target)) {
					$uploadedImages[] = 'uploads/events/images/' . $imageFileName;
				}
			}
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
        $st = $conn->prepare("UPDATE Events SET main_cover_image=?, title=?, description=?, location=?, event_date=?, event_time=?, event_link=?, status=?, category_id = NULLIF(?, 0) WHERE event_id=?");
        $st->bind_param('ssssssssii', $coverFileName, $title, $description, $location, $event_date, $event_time, $event_link, $status, $category_id, $id);
		if ($st->execute()) {
			// Insert additional images
			foreach ($uploadedImages as $imageUrl) {
				$imageStmt = $conn->prepare("INSERT INTO events_images (event_id, image_url) VALUES (?, ?)");
				$imageStmt->bind_param('is', $id, $imageUrl);
				$imageStmt->execute();
			}
			
			// Delete existing social links and insert new ones
			$deleteSocialStmt = $conn->prepare("DELETE FROM social_links WHERE target_type = 'event' AND target_id = ?");
			$deleteSocialStmt->bind_param('i', $id);
			$deleteSocialStmt->execute();
			
			foreach ($socialLinks as $link) {
				$socialStmt = $conn->prepare("INSERT INTO social_links (target_type, target_id, platform, url) VALUES ('event', ?, ?, ?)");
				$socialStmt->bind_param('iss', $id, $link['platform'], $link['url']);
				$socialStmt->execute();
			}
			
			$_SESSION['message'] = 'Event updated successfully.';
			$_SESSION['message_type'] = 'success';
			header('Location: ./');
			exit();
		} else {
			$error = 'Error updating event: ' . $conn->error;
		}
	}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Edit Event - GD Edu Tech</title>
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
						<h2>Edit Event</h2>
						<p class="text-muted">Update event details</p>
					</div>
					<div class="col-auto"><a href="./" class="btn btn-outline-primary"><i class="bi bi-arrow-left me-2"></i>Back</a></div>
				</div>

				<?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

				<form method="post" enctype="multipart/form-data" class="card p-3">
					<div class="mb-3">
						<label class="form-label">Category</label>
						<div class="dropdown" data-bs-auto-close="outside">
							<?php 
								$selId = $createdCategoryId ?: intval($event['category_id'] ?? 0); 
								$selName = '-- No category --';
								foreach ($eventCategories as $c) { if (intval($c['category_id']) === intval($selId)) { $selName = $c['name']; break; } }
							?>
							<button id="eventCategoryDropdownBtn" class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
								<span id="eventCategoryDropdownLabel"><?php echo htmlspecialchars($selName); ?></span>
							</button>
							<input type="hidden" name="category_id" id="eventCategoryIdInput" value="<?php echo intval($selId); ?>">
							<div class="dropdown-menu p-2" style="min-width: 280px; max-height: 320px; overflow:auto;">
								<div class="list-group list-group-flush mb-2">
									<button type="button" class="list-group-item list-group-item-action event-category-option" data-id="0">-- No category --</button>
									<?php foreach ($eventCategories as $c): ?>
										<button type="button" class="list-group-item list-group-item-action event-category-option" data-id="<?php echo intval($c['category_id']); ?>"><?php echo htmlspecialchars($c['name']); ?></button>
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
					<div class="mb-3"><label class="form-label">Title</label><input name="title" class="form-control" value="<?php echo htmlspecialchars($event['title']); ?>" required></div>
					<div class="mb-3"><label class="form-label">Cover Image</label><input type="file" name="cover_image" class="form-control" accept="image/*">
						<?php if (!empty($event['main_cover_image'])): ?><div class="mt-2"><img src="<?php echo '../../uploads/events/' . htmlspecialchars($event['main_cover_image']); ?>" alt="cover" height="100"></div><?php endif; ?>
					</div>
					<div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="6"><?php echo htmlspecialchars($event['description']); ?></textarea></div>
					<div class="row g-3">
						<div class="col-md-6"><label class="form-label">Location</label><input name="location" class="form-control" value="<?php echo htmlspecialchars($event['location']); ?>"></div>
						<div class="col-md-3"><label class="form-label">Date</label><input type="date" name="event_date" class="form-control" value="<?php echo htmlspecialchars($event['event_date']); ?>"></div>
						<div class="col-md-3"><label class="form-label">Time</label><input type="time" name="event_time" class="form-control" value="<?php echo htmlspecialchars($event['event_time']); ?>"></div>
					</div>
					<div class="row g-3 mt-1">
						<div class="col-md-12"><label class="form-label">Event Link</label><input name="event_link" class="form-control" value="<?php echo htmlspecialchars($event['event_link']); ?>"></div>
					</div>
					<div class="mb-3 mt-3">
						<label class="form-label">Status</label>
						<select name="status" class="form-select">
							<?php $statuses=['upcoming','ongoing','completed','cancelled']; foreach($statuses as $s): ?>
								<option value="<?php echo $s; ?>" <?php echo $event['status']===$s?'selected':''; ?>><?php echo $s; ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					
					<!-- Event Images Section -->
					<div class="mb-4">
						<h5 class="mb-3"><i class="bi bi-images me-2"></i>Event Images</h5>
						<div class="card">
							<div class="card-body">
								<!-- Existing Images -->
								<?php if (!empty($existingImages)): ?>
									<div class="mb-3">
										<label class="form-label">Current Images</label>
										<div class="row g-2">
											<?php foreach ($existingImages as $image): ?>
												<div class="col-md-3">
													<div class="card">
														<img src="../../<?php echo htmlspecialchars($image['image_url']); ?>" class="card-img-top" style="height: 120px; object-fit: cover;" alt="Event Image">
														<div class="card-body p-2">
															<small class="text-muted">
																<?php echo date('M d, Y', strtotime($image['created_at'])); ?>
															</small>
														</div>
													</div>
												</div>
											<?php endforeach; ?>
										</div>
									</div>
								<?php endif; ?>
								
								<!-- Upload New Images -->
								<div class="mb-3">
									<label class="form-label">Upload Additional Images</label>
									<input type="file" name="event_images[]" class="form-control" accept="image/*" multiple>
									<small class="form-text text-muted">You can select multiple images at once</small>
								</div>
								<div id="imagePreview" class="row g-2"></div>
							</div>
						</div>
					</div>
					
					<!-- Social Links Section -->
					<div class="mb-4">
						<h5 class="mb-3"><i class="bi bi-link-45deg me-2"></i>Social Links</h5>
						<div class="card">
							<div class="card-body">
								<div id="socialLinksContainer">
									<?php if (!empty($existingSocialLinks)): ?>
										<?php foreach ($existingSocialLinks as $index => $link): ?>
											<div class="social-link-item row g-3 mb-3">
												<div class="col-md-4">
													<select name="social_platform[]" class="form-select">
														<option value="">Select Platform</option>
														<option value="facebook" <?php echo $link['platform'] === 'facebook' ? 'selected' : ''; ?>>Facebook</option>
														<option value="twitter" <?php echo $link['platform'] === 'twitter' ? 'selected' : ''; ?>>Twitter</option>
														<option value="instagram" <?php echo $link['platform'] === 'instagram' ? 'selected' : ''; ?>>Instagram</option>
														<option value="linkedin" <?php echo $link['platform'] === 'linkedin' ? 'selected' : ''; ?>>LinkedIn</option>
														<option value="youtube" <?php echo $link['platform'] === 'youtube' ? 'selected' : ''; ?>>YouTube</option>
													</select>
												</div>
												<div class="col-md-6">
													<input type="url" name="social_url[]" class="form-control" placeholder="https://..." value="<?php echo htmlspecialchars($link['url']); ?>">
												</div>
												<div class="col-md-2">
													<button type="button" class="btn btn-outline-danger w-100 remove-social-link">
														<i class="bi bi-trash"></i>
													</button>
												</div>
											</div>
										<?php endforeach; ?>
									<?php else: ?>
										<div class="social-link-item row g-3 mb-3">
											<div class="col-md-4">
												<select name="social_platform[]" class="form-select">
													<option value="">Select Platform</option>
													<option value="facebook">Facebook</option>
													<option value="twitter">Twitter</option>
													<option value="instagram">Instagram</option>
													<option value="linkedin">LinkedIn</option>
													<option value="youtube">YouTube</option>
													<option value="tiktok">TikTok</option>
													<option value="pinterest">Pinterest</option>
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
									<?php endif; ?>
								</div>
								<button type="button" class="btn btn-outline-primary" id="addSocialLink">
									<i class="bi bi-plus me-1"></i>Add Social Link
								</button>
							</div>
						</div>
					</div>
					
					<button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Save Changes</button>
				</form>
			</div>
		</div>
	</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('click', function(e){
    if (e.target && e.target.classList.contains('event-category-option')) {
        var id = e.target.getAttribute('data-id');
        var label = e.target.textContent.trim();
        var input = document.getElementById('eventCategoryIdInput');
        var lblEl = document.getElementById('eventCategoryDropdownLabel');
        if (input) input.value = id;
        if (lblEl) lblEl.textContent = label;
    }
});

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

// Image Preview
document.querySelector('input[name="event_images[]"]').addEventListener('change', function(e) {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    
    Array.from(e.target.files).forEach((file, index) => {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const col = document.createElement('div');
                col.className = 'col-md-3';
                col.innerHTML = `
                    <div class="card">
                        <img src="${e.target.result}" class="card-img-top" style="height: 120px; object-fit: cover;">
                        <div class="card-body p-2">
                            <small class="text-muted">${file.name}</small>
                        </div>
                    </div>
                `;
                preview.appendChild(col);
            };
            reader.readAsDataURL(file);
        }
    });
});

// Initialize remove buttons
updateRemoveButtons();
</script>
</body>
</html>


