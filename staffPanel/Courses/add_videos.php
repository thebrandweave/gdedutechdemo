"INSERT INTO Videos (lesson_id, title, video_url, duration, video_order) VALUES (%d, '%s', '%s', '%s', %d)",
                    $lesson_id,
                    mysqli_real_escape_string($conn, $video['title']),
                    mysqli_real_escape_string($conn, $video['url']),
                    mysqli_real_escape_string($conn, $video['duration']),
                    $video['video_order']
                );
                mysqli_query($conn, $video_insert_query);
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

// Helper function to sanitize filename
function sanitize_filename($filename) {
    // Remove any character that isn't a letter, number, dot, hyphen or underscore
    $filename = preg_replace('/[^a-zA-Z0-9.-_]/', '', $filename);
    // Remove any dots except the last one
    $filename = preg_replace('/\.(?=.*\.)/', '', $filename);
    return $filename;
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

            <form method="POST">
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
                                        <label class="form-label">Video URL</label>
                                        <input type="url" name="videos[x][y][url]" class="form-control video-url" required>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label">Duration (HH:MM:SS)</label>
                                        <input type="text" pattern="[0-9]{2}:[0-9]{2}:[0-9]{2}" name="videos[x][y][duration]" class="form-control video-duration" placeholder="00:00:00" required>
                                    </div>
                                    <div class="col-md-12 mb-2">
                                        <label class="form-label">Subtitle File</label>
                                        <input type="file" name="videos[x][y][subtitle]" class="form-control video-subtitle" accept=".vtt,.srt">
                                        <small class="text-muted">Upload WebVTT (.vtt) or SubRip (.srt) subtitle files. The file will be saved using the video title.</small>
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
            newVideo.querySelector('.video-url').name = `videos[${lessonIndex}][${videoCount}][url]`;
            newVideo.querySelector('.video-duration').name = `videos[${lessonIndex}][${videoCount}][duration]`;
            newVideo.querySelector('.video-subtitle').name = `videos[${lessonIndex}][${videoCount}][subtitle]`;
            
            // Add event listener for video title changes
            const titleInput = newVideo.querySelector('.video-title');
            const subtitleInput = newVideo.querySelector('.video-subtitle');
            
            titleInput.addEventListener('change', function() {
                if (this.value) {
                    subtitleInput.setAttribute('data-title', this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-'));
                }
            });
            
            container.appendChild(newVideo);
        });
    });

    // Trigger first video for each lesson
    document.querySelectorAll('.add-video').forEach(button => button.click());
});
</script>
</body>
</html>