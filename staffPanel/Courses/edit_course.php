<?php
session_start();

// Check if user is logged in and is staff
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Staff') {
    header('Location: ../staff_login.php');
    exit();
}

// Get staff details from session
$staff_id = $_SESSION['user_id'];
$staff_name = $_SESSION['username'] ?? 'Staff';
?>
<?php
require_once '../../Configurations/config.php';

// // Check if user is logged in and has admin privileges
// if (!isset($_SESSION['user_id'])) {
//     header("Location: login.php");
//     exit();
// }

// Get course ID from URL
$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$course_id) {
    header("Location: courses.php");
    exit();
}

// Fetch categories for dropdown
$categories_query = mysqli_query($conn, "SELECT * FROM Categories");

// Fetch course details
$course_query = "SELECT * FROM Courses WHERE course_id = ?";
$stmt = mysqli_prepare($conn, $course_query);
mysqli_stmt_bind_param($stmt, 'i', $course_id);
mysqli_stmt_execute($stmt);
$course = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$course) {
    header("Location: courses.php");
    exit();
}

// Fetch existing lessons and videos
$lessons_query = "SELECT * FROM Lessons WHERE course_id = ? ORDER BY lesson_order";
$lessons_stmt = mysqli_prepare($conn, $lessons_query);
mysqli_stmt_bind_param($lessons_stmt, 'i', $course_id);
mysqli_stmt_execute($lessons_stmt);
$lessons_result = mysqli_stmt_get_result($lessons_stmt);
$lessons = [];

while ($lesson = mysqli_fetch_assoc($lessons_result)) {
    $videos_query = "SELECT * FROM Videos WHERE lesson_id = ? ORDER BY video_order";
    $videos_stmt = mysqli_prepare($conn, $videos_query);
    mysqli_stmt_bind_param($videos_stmt, 'i', $lesson['lesson_id']);
    mysqli_stmt_execute($videos_stmt);
    $videos_result = mysqli_stmt_get_result($videos_stmt);
    
    $lesson['videos'] = [];
    while ($video = mysqli_fetch_assoc($videos_result)) {
        $lesson['videos'][] = $video;
    }
    $lessons[] = $lesson;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    mysqli_begin_transaction($conn);
    
    try {
        // Update course details
        $update_course_query = "UPDATE Courses SET 
            title = ?, 
            description = ?, 
            price = ?, 
            language = ?, 
            level = ?, 
            category_id = ?, 
            course_type = ?
            WHERE course_id = ?";
            
        $stmt = mysqli_prepare($conn, $update_course_query);
        mysqli_stmt_bind_param($stmt, 'ssdssssi', 
            $_POST['title'],
            $_POST['description'],
            $_POST['price'],
            $_POST['language'],
            $_POST['level'],
            $_POST['category_id'],
            $_POST['course_type'],
            $course_id
        );
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Failed to update course details");
        }
        
        // Handle thumbnail update if new file uploaded
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            if (in_array($_FILES['thumbnail']['type'], $allowed_types) && $_FILES['thumbnail']['size'] <= $max_size) {
                $thumbnail_name = uniqid() . '_' . $_FILES['thumbnail']['name'];
                $upload_path = '../../uploads/course_uploads/thumbnails/' . $thumbnail_name;
                
                if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $upload_path)) {
                    // Delete old thumbnail if exists
                    if ($course['thumbnail'] && file_exists('../../uploads/course_uploads/thumbnails/' . $course['thumbnail'])) {
                        unlink('../../uploads/course_uploads/thumbnails/' . $course['thumbnail']);
                    }
                    
                    // Update thumbnail in database
                    $update_thumb = mysqli_prepare($conn, "UPDATE Courses SET thumbnail = ? WHERE course_id = ?");
                    mysqli_stmt_bind_param($update_thumb, 'si', $thumbnail_name, $course_id);
                    mysqli_stmt_execute($update_thumb);
                }
            }
        }
        
        // Process existing lessons updates
        $existing_lesson_ids = array_column($lessons, 'lesson_id');
        $updated_lesson_ids = [];
        
        foreach ($_POST['lesson_ids'] ?? [] as $index => $lesson_id) {
            if ($lesson_id) { // Update existing lesson
                $lesson_title = $_POST['lesson_titles'][$index];
                $lesson_description = $_POST['lesson_descriptions'][$index];
                $lesson_order = $index + 1;
                
                $update_lesson = mysqli_prepare($conn, 
                    "UPDATE Lessons SET title = ?, description = ?, lesson_order = ? WHERE lesson_id = ?"
                );
                mysqli_stmt_bind_param($update_lesson, 'ssii', 
                    $lesson_title, $lesson_description, $lesson_order, $lesson_id
                );
                mysqli_stmt_execute($update_lesson);
                $updated_lesson_ids[] = $lesson_id;
            } else { // Add new lesson
                $insert_lesson = mysqli_prepare($conn, 
                    "INSERT INTO Lessons (course_id, title, description, lesson_order) VALUES (?, ?, ?, ?)"
                );
                $lesson_order = $index + 1;
                mysqli_stmt_bind_param($insert_lesson, 'issi', 
                    $course_id, $_POST['lesson_titles'][$index], 
                    $_POST['lesson_descriptions'][$index], $lesson_order
                );
                mysqli_stmt_execute($insert_lesson);
                $lesson_id = mysqli_insert_id($conn);
                $updated_lesson_ids[] = $lesson_id;
            }
            
            // Process videos for this lesson
            if (isset($_FILES['lesson_videos']['name'][$index]) && is_array($_FILES['lesson_videos']['name'][$index])) {
                foreach ($_FILES['lesson_videos']['name'][$index] as $video_index => $video_name) {
                    if (!empty($_FILES['lesson_videos']['tmp_name'][$index][$video_index])) {
                        $tmp_name = $_FILES['lesson_videos']['tmp_name'][$index][$video_index];
                        $video_title = $_POST['video_titles'][$index][$video_index] ?? $video_name;
                        $video_description = $_POST['video_descriptions'][$index][$video_index] ?? '';
                        
                        // Create sanitized filename from video title
                        $safe_video_title = preg_replace('/[^a-z0-9]+/', '-', strtolower($video_title));
                        $ext = pathinfo($video_name, PATHINFO_EXTENSION);
                        $video_filename = $safe_video_title . '.' . $ext;
                        $upload_path = '../../uploads/course_uploads/course_videos/' . $video_filename;
                        
                        if (move_uploaded_file($tmp_name, $upload_path)) {
                            // Process subtitle file if exists
                            $subtitle_filename = null;
                            if (isset($_FILES['lesson_subtitles']['name'][$index][$video_index]) && 
                                !empty($_FILES['lesson_subtitles']['name'][$index][$video_index])) {
                                
                                $subtitle_name = $_FILES['lesson_subtitles']['name'][$index][$video_index];
                                $subtitle_tmp_name = $_FILES['lesson_subtitles']['tmp_name'][$index][$video_index];
                                
                                // Use same base name as video file
                                $subtitle_ext = pathinfo($subtitle_name, PATHINFO_EXTENSION);
                                $subtitle_filename = $safe_video_title . '.' . $subtitle_ext;
                                $subtitle_upload_path = '../../uploads/course_uploads/course_subtitles/' . $subtitle_filename;

                                move_uploaded_file($subtitle_tmp_name, $subtitle_upload_path);
                            }

                            // Update video query to include subtitle_url
                            $insert_video = mysqli_prepare($conn, 
                                "INSERT INTO Videos (lesson_id, title, description, video_url, subtitle_url, video_order) 
                                VALUES (?, ?, ?, ?, ?, ?)"
                            );
                            $video_order = $video_index + 1;
                            mysqli_stmt_bind_param($insert_video, 'issssi', 
                                $lesson_id, $video_title, $video_description, 
                                $video_filename, $subtitle_filename, $video_order
                            );
                            mysqli_stmt_execute($insert_video);
                        }
                    }
                }
            }
        }
        
        // Delete lessons that weren't updated
        $lessons_to_delete = array_diff($existing_lesson_ids, $updated_lesson_ids);
        foreach ($lessons_to_delete as $lesson_id) {
            // Delete associated videos first
            $videos_query = "SELECT video_url FROM Videos WHERE lesson_id = ?";
            $videos_stmt = mysqli_prepare($conn, $videos_query);
            mysqli_stmt_bind_param($videos_stmt, 'i', $lesson_id);
            mysqli_stmt_execute($videos_stmt);
            $videos_result = mysqli_stmt_get_result($videos_stmt);
            
            while ($video = mysqli_fetch_assoc($videos_result)) {
                if (file_exists('./course_videos/' . $video['video_url'])) {
                    unlink('./course_videos/' . $video['video_url']);
                }
            }
            
            // Delete videos from database
            $delete_videos = mysqli_prepare($conn, "DELETE FROM Videos WHERE lesson_id = ?");
            mysqli_stmt_bind_param($delete_videos, 'i', $lesson_id);
            mysqli_stmt_execute($delete_videos);
            
            // Delete lesson
            $delete_lesson = mysqli_prepare($conn, "DELETE FROM Lessons WHERE lesson_id = ?");
            mysqli_stmt_bind_param($delete_lesson, 'i', $lesson_id);
            mysqli_stmt_execute($delete_lesson);
        }
        
        // Delete individual videos if requested
        if (isset($_POST['videos_to_delete']) && is_array($_POST['videos_to_delete'])) {
            foreach ($_POST['videos_to_delete'] as $video_id) {
                $video_query = "SELECT video_url FROM Videos WHERE video_id = ?";
                $video_stmt = mysqli_prepare($conn, $video_query);
                mysqli_stmt_bind_param($video_stmt, 'i', $video_id);
                mysqli_stmt_execute($video_stmt);
                $video_result = mysqli_fetch_assoc(mysqli_stmt_get_result($video_stmt));
                
                if ($video_result && file_exists('../../uploads/course_uploads/course_videos/' . $video_result['video_url'])) {
                    unlink('../../uploads/course_uploads/course_videos/' . $video_result['video_url']);
                }
                
                $delete_video = mysqli_prepare($conn, "DELETE FROM Videos WHERE video_id = ?");
                mysqli_stmt_bind_param($delete_video, 'i', $video_id);
                mysqli_stmt_execute($delete_video);
            }
        }
        
        mysqli_commit($conn);
        header("Location: index.php");
        exit();
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $errors[] = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course - GD Edu Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../../adminPanel/css/style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 sidebar">
                <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 min-vh-100">
                    <a href="#" class="d-flex align-items-center pb-3 mb-md-1 mt-md-3 me-md-auto text-white text-decoration-none">
                        <span class="fs-5 fw-bolder" style="display: flex;align-items:center;color:black;">
                            <img height="35px" src="../../staffPanel/images/edutechLogo.png" alt="">&nbsp; GD Edu Tech
                        </span>
                    </a>
                    <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100" id="menu">
                        <li class="w-100">
                            <a href="../index.php" class="nav-link ">
                                <i class="bi bi-speedometer2 me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="./" class="nav-link active">
                                <i class="bi bi-book me-2"></i> Courses
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Quiz/" class="nav-link">
                                <i class="bi bi-lightbulb me-2"></i> Quiz
                            </a>
                        </li>
                        <li class="w-100">
                            <a href="../Messages/index.php" class="nav-link">
                                <i class="bi bi-chat-dots me-2"></i> Messages
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
                <div class="container mt-5">
                    <div class="card">
                        <div class="card-header">Edit Course</div>
                        <div class="card-body">
                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger">
                                    <?php foreach ($errors as $error): ?>
                                        <p><?php echo htmlspecialchars($error); ?></p>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" enctype="multipart/form-data">
                                <!-- Course Details Section -->
                                <h4>Course Details</h4>
                                <div class="mb-3">
                                    <label>Course Title</label>
                                    <input type="text" name="title" class="form-control" 
                                           value="<?php echo htmlspecialchars($course['title']); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label>Description</label>
                                    <textarea name="description" class="form-control" rows="4" required><?php 
                                        echo htmlspecialchars($course['description']); 
                                    ?></textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label>Price</label>
                                        <input type="number" name="price" step="0.01" class="form-control" 
                                               value="<?php echo htmlspecialchars($course['price']); ?>" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label>Language</label>
                                        <input type="text" name="language" class="form-control" 
                                               value="<?php echo htmlspecialchars($course['language']); ?>" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label>Level</label>
                                        <select name="level" class="form-select" required>
                                            <option value="beginner" <?php echo $course['level'] == 'beginner' ? 'selected' : ''; ?>>Beginner</option>
                                            <option value="intermediate" <?php echo $course['level'] == 'intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                                            <option value="advanced" <?php echo $course['level'] == 'advanced' ? 'selected' : ''; ?>>Advanced</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label>Category</label>
                                        <select name="category_id" class="form-select" required>
                                            <?php 
                                            mysqli_data_seek($categories_query, 0);
                                            while ($category = mysqli_fetch_assoc($categories_query)): 
                                            ?>
                                                <option value="<?php echo $category['category_id']; ?>" 
                                                    <?php echo $category['category_id'] == $course['category_id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label>Course Type</label>
                                        <input type="text" name="course_type" class="form-control" 
                                               value="<?php echo htmlspecialchars($course['course_type']); ?>">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label>Course Thumbnail</label>
                                    <?php if ($course['thumbnail']): ?>
                                        <div class="mb-2">
                                            <img src="../../uploads/course_uploads/thumbnails/<?php echo htmlspecialchars($course['thumbnail']); ?>" 
                                                 alt="Current thumbnail" style="max-width: 200px;">
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" name="thumbnail" class="form-control" accept="image/jpeg,image/png,image/gif">
                                    <small class="form-text text-muted">Leave empty to keep current thumbnail</small>
                                </div>
                                
                                <!-- Lessons Section -->
                                <h4 class="mt-4">Lessons</h4>
                                <div id="lessons-container">
                                    <?php foreach ($lessons as $index => $lesson): ?>
                                        <div class="lesson-section" data-lesson-index="<?php echo $index; ?>">
                                            <hr>
                                            <h5>Lesson <?php echo $index + 1; ?></h5>
                                            <input type="hidden" name="lesson_ids[]" value="<?php echo $lesson['lesson_id']; ?>">
                                            
                                            <div class="mb-3">
                                                <label>Lesson Title</label>
                                                <input type="text" name="lesson_titles[]" class="form-control" 
                                                value="<?php echo htmlspecialchars($lesson['title']); ?>" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label>Lesson Description</label>
                                                <textarea name="lesson_descriptions[]" class="form-control" rows="3"><?php 
                                                    echo htmlspecialchars($lesson['description']); 
                                                ?></textarea>
                                            </div>
                                            
                                            <!-- Existing Videos -->
                                            <?php if (!empty($lesson['videos'])): ?>
                                                <div class="mb-3">
                                                    <h6>Current Videos</h6>
                                                    <?php foreach ($lesson['videos'] as $video): ?>
                                                        <div class="card mb-2">
                                                            <div class="card-body">
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <div>
                                                                        <span><?php echo htmlspecialchars($video['title']); ?></span>
                                                                        <?php if ($video['subtitle_url']): ?>
                                                                            <small class="text-muted ms-2">
                                                                                (Has subtitles)
                                                                            </small>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                    <div class="form-check">
                                                                        <input type="checkbox" name="videos_to_delete[]" 
                                                                               value="<?php echo $video['video_id']; ?>" 
                                                                               class="form-check-input">
                                                                        <label class="form-check-label">Delete</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <!-- New Videos Upload -->
                                            <div class="mb-3">
                                                <label>Add New Videos</label>
                                                <div class="video-uploads">
                                                    <div class="mb-2">
                                                        <input type="file" name="lesson_videos[<?php echo $index; ?>][]" 
                                                               class="form-control" accept="video/*" multiple>
                                                    </div>
                                                    <div class="mb-2">
                                                        <input type="file" name="lesson_subtitles[<?php echo $index; ?>][]" 
                                                               class="form-control" accept=".srt,.vtt" multiple>
                                                        <small class="form-text text-muted">Upload subtitle files (optional)</small>
                                                    </div>
                                                    <div class="mb-2">
                                                        <input type="text" name="video_titles[<?php echo $index; ?>][]" 
                                                               class="form-control" placeholder="Video Title">
                                                    </div>
                                                    <div class="mb-2">
                                                        <textarea name="video_descriptions[<?php echo $index; ?>][]" 
                                                                  class="form-control" placeholder="Video Description" rows="2"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <button type="button" class="btn btn-danger btn-sm remove-lesson">Remove Lesson</button>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <button type="button" class="btn btn-secondary mt-3" id="add-lesson">Add New Lesson</button>
                                
                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                    <a href="../Courses/index.php" class="btn btn-link">Cancel</a>
                                </div>
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
        const lessonsContainer = document.getElementById('lessons-container');
        const addLessonButton = document.getElementById('add-lesson');
        
        addLessonButton.addEventListener('click', function() {
            const lessonIndex = document.querySelectorAll('.lesson-section').length;
            const newLesson = document.createElement('div');
            newLesson.className = 'lesson-section';
            newLesson.dataset.lessonIndex = lessonIndex;
            
            newLesson.innerHTML = `
                <hr>
                <h5>Lesson ${lessonIndex + 1}</h5>
                <input type="hidden" name="lesson_ids[]" value="">
                
                <div class="mb-3">
                    <label>Lesson Title</label>
                    <input type="text" name="lesson_titles[]" class="form-control" required>
                </div>
                
                <div class="mb-3">
                    <label>Lesson Description</label>
                    <textarea name="lesson_descriptions[]" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="mb-3">
                    <label>Add Videos</label>
                    <div class="video-uploads">
                        <div class="mb-2">
                            <input type="file" name="lesson_videos[${lessonIndex}][]" 
                                   class="form-control" accept="video/*" multiple>
                        </div>
                        <div class="mb-2">
                            <input type="file" name="lesson_subtitles[${lessonIndex}][]" 
                                   class="form-control" accept=".srt,.vtt" multiple>
                            <small class="form-text text-muted">Upload subtitle files (optional)</small>
                        </div>
                        <div class="mb-2">
                            <input type="text" name="video_titles[${lessonIndex}][]" 
                                   class="form-control" placeholder="Video Title">
                        </div>
                        <div class="mb-2">
                            <textarea name="video_descriptions[${lessonIndex}][]" 
                                      class="form-control" placeholder="Video Description" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                
                <button type="button" class="btn btn-danger btn-sm remove-lesson">Remove Lesson</button>
            `;
            
            lessonsContainer.appendChild(newLesson);
        });
        
        // Handle lesson removal
        lessonsContainer.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-lesson')) {
                const lessonSection = e.target.closest('.lesson-section');
                lessonSection.remove();
                
                // Update lesson numbers
                document.querySelectorAll('.lesson-section').forEach((section, index) => {
                    section.querySelector('h5').textContent = `Lesson ${index + 1}`;
                    section.dataset.lessonIndex = index;
                });
            }
        });
    });
    </script>
</body>
</html>