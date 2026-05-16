<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../admin_login.php');
    exit();
}

// Get admin details from session
$admin_name = $_SESSION['username'] ?? 'Admin';
?>
<?php
require_once '../../Configurations/config.php';

// Check if course data exists in session
if (!isset($_SESSION['course_data'])) {
    header("Location: add_course.php");
    exit();
}

// Insert course into database first
$course_data = $_SESSION['course_data'];
$insert_course_query = "INSERT INTO Courses (
    title, description, price, language, level, 
    category_id, course_type, thumbnail, created_by
) VALUES (
    ?, ?, ?, ?, ?, ?, ?, ?, ?
)";

$stmt = mysqli_prepare($conn, $insert_course_query);
mysqli_stmt_bind_param(
    $stmt,
    'ssdsssssi',
    $course_data['title'],
    $course_data['description'],
    $course_data['price'],
    $course_data['language'],
    $course_data['level'],
    $course_data['category_id'],
    $course_data['course_type'],
    $course_data['thumbnail'],
    $course_data['created_by']
);

// Errors array to track any issues
$errors = [];

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Start transaction
    mysqli_begin_transaction($conn);

    try {
        // Execute course insertion
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Failed to insert course");
        }

        // Get the inserted course ID
        $course_id = mysqli_insert_id($conn);

        // Process lessons and videos
        $lesson_titles = $_POST['lesson_titles'] ?? [];
        $lesson_descriptions = $_POST['lesson_descriptions'] ?? [];

        foreach ($lesson_titles as $index => $lesson_title) {
            // Skip empty lessons
            if (empty(trim($lesson_title))) continue;

            // Insert lesson
            $lesson_description = $lesson_descriptions[$index] ?? '';
            $lesson_order = $index + 1;

            $lesson_query = "INSERT INTO Lessons (course_id, title, description, lesson_order) VALUES (?, ?, ?, ?)";
            $lesson_stmt = mysqli_prepare($conn, $lesson_query);
            mysqli_stmt_bind_param($lesson_stmt, 'issi', $course_id, $lesson_title, $lesson_description, $lesson_order);

            if (!mysqli_stmt_execute($lesson_stmt)) {
                throw new Exception("Failed to insert lesson: " . $lesson_title);
            }

            $lesson_id = mysqli_insert_id($conn);

            // Process videos for this lesson
            if (isset($_FILES['lesson_videos']['name'][$index]) && is_array($_FILES['lesson_videos']['name'][$index])) {
                foreach ($_FILES['lesson_videos']['name'][$index] as $video_index => $video_name) {
                    // Skip empty video uploads
                    if (empty(trim($video_name))) continue;

                    // Video validation
                    $tmp_name = $_FILES['lesson_videos']['tmp_name'][$index][$video_index];
                    $video_title = $_POST['video_titles'][$index][$video_index] ?? $video_name;
                    $video_description = $_POST['video_descriptions'][$index][$video_index] ?? '';

                    // Move uploaded video
                    $video_filename = uniqid() . '_' . $video_name;
                    $upload_path = '../../uploads/course_uploads/course_videos/' . $video_filename;

                    if (!move_uploaded_file($tmp_name, $upload_path)) {
                        throw new Exception("Failed to upload video: " . $video_name);
                    }

                    // Insert video details
                    $video_query = "INSERT INTO Videos (lesson_id, title, description, video_url, video_order) VALUES (?, ?, ?, ?, ?)";
                    $video_stmt = mysqli_prepare($conn, $video_query);
                    $video_order = $video_index + 1;
                    mysqli_stmt_bind_param($video_stmt, 'isssi', $lesson_id, $video_title, $video_description, $video_filename, $video_order);

                    if (!mysqli_stmt_execute($video_stmt)) {
                        throw new Exception("Failed to insert video details");
                    }
                }
            }
        }

        // Commit transaction
        mysqli_commit($conn);

        // Clear session data and redirect
        unset($_SESSION['course_data']);
        header("Location: ./");
        exit();
    } catch (Exception $e) {
        // Rollback transaction
        mysqli_rollback($conn);
        $errors[] = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add Course Lessons</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/style.css">

    <style>
    .loader-container {
        background: rgba(0, 0, 0, 0.8);
        backdrop-filter: blur(8px);
    }

    .spinner {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        border: 9px solid #f1f1f1;
        border-top: 9px solid #2C3E50;
        animation: spin 1s linear infinite;
        margin: 0 auto;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Add some animations */
    .loader-message-animation {
        animation: fadeInOut 2s ease-in-out infinite;
    }

    @keyframes fadeInOut {
        0% { opacity: 0.6; }
        50% { opacity: 1; }
        100% { opacity: 0.6; }
    }

    /* Disable form while submitting */
    .form-disabled {
        opacity: 0.7;
        pointer-events: none;
    }
    </style>
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
                <a href="../Courses/" class="nav-link active">
                    <i class="bi bi-book me-2"></i> Courses
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
                <a href="../Schedule/" class="nav-link">
                    <i class="bi bi-calendar-event me-2"></i> Schedule
                </a>
            </li>
            <li class="w-100">
                <a href="../Messages/" class="nav-link">
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
            <div class="col-md-9 ms-sm-auto col-lg-10 px-4">
                <div class="container mt-5">
                    <div class="card">
                        <div class="card-header">Add Lessons and Videos</div>
                        <div class="card-body">
                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger">
                                    <?php foreach ($errors as $error): ?>
                                        <p><?php echo htmlspecialchars($error); ?></p>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST" enctype="multipart/form-data" id="lessonsForm">
                                <div id="lessons-container">
                                    <div class="lesson-section" data-lesson-index="0">
                                        <h4>Lesson 1</h4>
                                        <div class="mb-3">
                                            <label>Lesson Title</label>
                                            <input type="text" name="lesson_titles[]" class="form-control" required>
                                        </div>
                                        <div class="mb-3">
                                            <label>Lesson Description</label>
                                            <textarea name="lesson_descriptions[]" class="form-control" rows="3"></textarea>
                                        </div>

                                        <div class="videos-container">
                                            <div class="video-section" data-video-index="0">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h5>Video 1</h5>
                                                    <button type="button" class="btn btn-danger btn-sm remove-video-btn">Remove Video</button>
                                                </div>
                                                <div class="mb-3">
                                                    <label>Video Title</label>
                                                    <input type="text" name="video_titles[0][]" class="form-control">
                                                </div>
                                                <div class="mb-3">
                                                    <label>Video Description</label>
                                                    <textarea name="video_descriptions[0][]" class="form-control" rows="2"></textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label>Video File</label>
                                                    <input type="file" name="lesson_videos[0][]" class="form-control" accept="video/*">
                                                </div>
                                            </div>
                                        </div>

                                        <button type="button" class="btn btn-secondary add-video-btn">+ Add Video</button>
                                    </div>
                                </div>

                                <button type="button" class="btn btn-secondary mt-3" id="addLessonBtn">+ Add Lesson</button>
                                <button type="submit" class="btn btn-primary mt-3">Save Course</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let lessonIndex = 1;

            // Setup initial video buttons for the first lesson
            setupVideoButtons(document.querySelector('.lesson-section'));

            document.getElementById('addLessonBtn').addEventListener('click', function() {
                lessonIndex++;
                const lessonsContainer = document.getElementById('lessons-container');
                const newLesson = document.querySelector('.lesson-section').cloneNode(true);

                // Reset lesson details
                newLesson.querySelector('h4').textContent = `Lesson ${lessonIndex}`;
                newLesson.querySelector('input[name^="lesson_titles"]').value = '';
                newLesson.querySelector('textarea[name^="lesson_descriptions"]').value = '';

                // Reset video section
                const videosContainer = newLesson.querySelector('.videos-container');
                videosContainer.innerHTML = `
                    <div class="video-section" data-video-index="0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5>Video 1</h5>
                            <button type="button" class="btn btn-danger btn-sm remove-video-btn">Remove Video</button>
                        </div>
                        <div class="mb-3">
                            <label>Video Title</label>
                            <input type="text" name="video_titles[${lessonIndex-1}][]" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Video Description</label>
                            <textarea name="video_descriptions[${lessonIndex-1}][]" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label>Video File</label>
                            <input type="file" name="lesson_videos[${lessonIndex-1}][]" class="form-control" accept="video/*">
                        </div>
                    </div>
                `;

                newLesson.setAttribute('data-lesson-index', lessonIndex - 1);
                lessonsContainer.appendChild(newLesson);

                // Setup video buttons for the new lesson
                setupVideoButtons(newLesson);
            });

            function setupVideoButtons(lessonContainer) {
                // Remove existing event listeners
                const addVideoBtn = lessonContainer.querySelector('.add-video-btn');
                const newAddVideoBtn = addVideoBtn.cloneNode(true);
                addVideoBtn.parentNode.replaceChild(newAddVideoBtn, addVideoBtn);

                // Add new event listener
                newAddVideoBtn.addEventListener('click', function() {
                    const videosContainer = lessonContainer.querySelector('.videos-container');
                    const lessonIndex = lessonContainer.getAttribute('data-lesson-index');
                    const currentVideoCount = videosContainer.children.length;

                    const newVideo = document.createElement('div');
                    newVideo.className = 'video-section';
                    newVideo.setAttribute('data-video-index', currentVideoCount);
                    newVideo.innerHTML = `
                        <div class="d-flex justify-content-between align-items-center">
                            <h5>Video ${currentVideoCount + 1}</h5>
                            <button type="button" class="btn btn-danger btn-sm remove-video-btn">Remove Video</button>
                        </div>
                        <div class="mb-3">
                            <label>Video Title</label>
                            <input type="text" name="video_titles[${lessonIndex}][]" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Video Description</label>
                            <textarea name="video_descriptions[${lessonIndex}][]" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label>Video File</label>
                            <input type="file" name="lesson_videos[${lessonIndex}][]" class="form-control" accept="video/*">
                        </div>
                    `;

                    videosContainer.appendChild(newVideo);
                    setupRemoveVideoButton(newVideo);
                });

                // Setup remove buttons for existing videos
                lessonContainer.querySelectorAll('.video-section').forEach(videoSection => {
                    setupRemoveVideoButton(videoSection);
                });
            }

            function setupRemoveVideoButton(videoSection) {
                const removeBtn = videoSection.querySelector('.remove-video-btn');
                removeBtn.addEventListener('click', function() {
                    const videosContainer = videoSection.parentElement;
                    videoSection.remove();

                    // Update video numbers for remaining videos
                    const remainingVideos = videosContainer.querySelectorAll('.video-section');
                    remainingVideos.forEach((video, index) => {
                        video.querySelector('h5').textContent = `Video ${index + 1}`;
                    });
                });
            }
        });

        const loaderMessages = [
            "Preparing your course content...",
            "Organizing lessons and videos...",
            "Almost there, finalizing details...",
            "Making everything perfect...",
            "Just a few more moments..."
        ];

        let messageIndex = 0;
        let messageInterval;

        function updateLoaderMessage() {
            const messageElement = document.getElementById('loaderMessage');
            messageElement.textContent = loaderMessages[messageIndex];
            messageIndex = (messageIndex + 1) % loaderMessages.length;
        }

        function showLoader() {
            const loaderModal = new bootstrap.Modal(document.getElementById('submitLoader'));
            loaderModal.show();
            
            // Start cycling through messages
            messageInterval = setInterval(updateLoaderMessage, 3000);
            
            // Disable the form
            document.getElementById('lessonsForm').classList.add('form-disabled');
        }

        function hideLoader() {
            const loaderModal = document.getElementById('submitLoader');
            const bsModal = bootstrap.Modal.getInstance(loaderModal);
            if (bsModal) {
                bsModal.hide();
            }
            
            // Clear message interval
            clearInterval(messageInterval);
            
            // Enable the form
            document.getElementById('lessonsForm').classList.remove('form-disabled');
        }

        // Update your form submission handler
        async function handleFormSubmit(event) {
            event.preventDefault();
            
            try {
                showLoader();
                const form = event.target;
                const formData = new FormData(form);

                // Validate form data
                if (!validateForm(formData)) {
                    throw new Error('Please fill in all required fields');
                }

                // Show upload progress for videos if any
                const hasVideos = Array.from(form.querySelectorAll('input[type="file"]'))
                    .some(input => input.files.length > 0);
                    
                if (hasVideos) {
                    hideLoader();
                    await handleVideoUploads(form);
                }

                // Submit the form
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error('Failed to save course');
                }

                // Success - redirect
                window.location.href = './';
                
            } catch (error) {
                hideLoader();
                alert('Error: ' + error.message);
            }
        }

        function validateForm(formData) {
            // Add your form validation logic here
            const title = formData.get('lesson_titles[]');
            if (!title || title.trim() === '') {
                return false;
            }
            return true;
        }

        // Add this to your existing video upload handling
        async function handleVideoUploads(form) {
            const progressModal = new bootstrap.Modal(document.getElementById('uploadProgress'));
            progressModal.show();
            
            // Your existing video upload code here...
        }

        // Prevent multiple form submissions
        document.getElementById('lessonsForm').addEventListener('submit', function(e) {
            const submitButton = this.querySelector('button[type="submit"]');
            if (submitButton.disabled) {
                e.preventDefault();
                return false;
            }
            submitButton.disabled = true;
        });

        // Re-enable submit button if form submission fails
        window.addEventListener('unload', function() {
            const submitButton = document.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = false;
            }
        });
    </script>
</body>

</html>