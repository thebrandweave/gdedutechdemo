<?php
// ... (previous code)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        mysqli_begin_transaction($conn);

        foreach ($_FILES['videos'] as $lesson_index => $lesson_videos) {
            foreach ($lesson_videos as $video_index => $video) {
                if (!empty($_FILES['videos']['name'][$lesson_index][$video_index])) {
                    $file = $_FILES['videos']['tmp_name'][$lesson_index][$video_index];
                    $filename = uniqid() . '_' . $_FILES['videos']['name'][$lesson_index][$video_index];
                    $upload_path = '../../uploads/course_uploads/course_videos/' . $filename;

                    if (move_uploaded_file($file, $upload_path)) {
                        $_POST['videos'][$lesson_index][$video_index]['url'] = $filename;

                        // Process video into different qualities
                        require_once '../../studentPanel/MyCourses/process_video.php';

                        // Test FFmpeg first
                        if (!testFFmpeg()) {
                            throw new Exception("FFmpeg is not available or not working properly");
                        }

                        // Generate different quality versions
                        error_log("Starting video processing for: " . $upload_path);
                        generateVideoQualities($upload_path, $filename);

                        // Verify that at least some quality versions were generated
                        $baseDir = dirname($upload_path);
                        $filenameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);
                        $qualityVersions = glob($baseDir . '/' . $filenameWithoutExt . '_*p.mp4');

                        if (empty($qualityVersions)) {
                            error_log("No quality versions were generated for: " . $filename);
                            throw new Exception("Failed to generate video quality versions");
                        }

                        // Insert into database
                        $video_data = $_POST['videos'][$lesson_index][$video_index];
                        $lesson_id = $lesson['lesson_id'];
                        $video_insert_query = sprintf(
                            "INSERT INTO Videos (lesson_id, title, video_url, duration, video_order) 
                             VALUES (%d, '%s', '%s', '%s', %d)",
                            $lesson_id,
                            mysqli_real_escape_string($conn, $video_data['title']),
                            mysqli_real_escape_string($conn, $filename),
                            mysqli_real_escape_string($conn, $video_data['duration']),
                            $video_index
                        );
                        mysqli_query($conn, $video_insert_query);
                    } else {
                        throw new Exception("Failed to upload video file");
                    }
                }
            }
        }

        // Commit transaction
        mysqli_commit($conn);

        // Clear session data
        unset($_SESSION['course_data']);
        unset($_SESSION['course_lessons']);

        // Set success message
        $_SESSION['message'] = "Course created successfully!";
        $_SESSION['message_type'] = "success";

        // Redirect to courses page
        header("Location: courses.php");
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        $errors[] = "Database error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add Course Videos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">Add Videos for Course: <?php echo htmlspecialchars($_SESSION['course_data']['title']); ?></div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <?php foreach ($_SESSION['course_lessons'] as $lesson_index => $lesson): ?>
                        <div class="lesson-videos mb-4 p-3 border rounded">
                            <h5>Videos for Lesson: <?php echo htmlspecialchars($lesson['title']); ?></h5>
                            <div class="videos-container" data-lesson-index="<?php echo $lesson_index; ?>">
                                <div class="video-template mb-3 p-2 border" style="display:none;">
                                    <div class="row">
                                        <div class="col-md-4 mb-2">
                                            <label class="form-label">Video Title</label>
                                            <input type="text" name="videos[x][y][title]" class="form-control video-title" required>
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <label class="form-label">Video File</label>
                                            <input type="file" name="videos[x][y][video]" class="form-control video-file" accept="video/mp4" required>
                                            <input type="hidden" name="videos[x][y][url]" class="video-url">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <label class="form-label">Duration (HH:MM:SS)</label>
                                            <input type="text" pattern="[0-9]{2}:[0-9]{2}:[0-9]{2}" name="videos[x][y][duration]" class="form-control video-duration" placeholder="00:00:00">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-secondary btn-sm add-video" data-lesson-index="<?php echo $lesson_index; ?>">+ Add Video</button>
                        </div>
                    <?php endforeach; ?>

                    <button type="submit" class="btn btn-primary mt-3">Complete Course Creation</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.add-video').forEach(function(button) {
                button.addEventListener('click', function() {
                    const lessonIndex = this.getAttribute('data-lesson-index');
                    const container = this.closest('.lesson-videos').querySelector('.videos-container');
                    const template = this.closest('.lesson-videos').querySelector('.video-template');

                    const newVideo = template.cloneNode(true);
                    newVideo.style.display = 'block';

                    // Update input names dynamically
                    const videoCount = container.querySelectorAll('.video-template:not([style*="display: none"])').length;
                    newVideo.querySelector('.video-title').name = `videos[${lessonIndex}][${videoCount}][title]`;
                    newVideo.querySelector('.video-file').name = `videos[${lessonIndex}][${videoCount}][video]`;
                    newVideo.querySelector('.video-duration').name = `videos[${lessonIndex}][${videoCount}][duration]`;

                    container.appendChild(newVideo);
                });
            });

            // Trigger first video for each lesson
            document.querySelectorAll('.add-video').forEach(button => button.click());
        });
    </script>
</body>

</html>