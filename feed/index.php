<?php
require_once '../Configurations/config.php';

$success_message = false;

if (isset($_POST['submit_feedback'])) {
    $student_name = trim($_POST['student_name'] ?? '');
    $course_name  = trim($_POST['course_name'] ?? '');
    $college_name = trim($_POST['college_name'] ?? '');
    $rating       = intval($_POST['rating'] ?? 5);
    $feedback     = trim($_POST['feedback'] ?? '');

    $image_name = 'user.png';
    if (!empty($_FILES['student_image']['name'])) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (in_array($_FILES['student_image']['type'], $allowed_types) && $_FILES['student_image']['size'] <= 2000000) {
            $image_name = time() . '_' . basename($_FILES['student_image']['name']);
            $target_dir = '../uploads/feedback/';
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            move_uploaded_file($_FILES['student_image']['tmp_name'], $target_dir . $image_name);
        }
    }

    if (!empty($student_name) && !empty($feedback)) {
        $stmt = $conn->prepare("
            INSERT INTO student_feedback (student_name, course_name, college_name, student_image, rating, feedback, status)
            VALUES (?, ?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->bind_param("ssssis", $student_name, $course_name, $college_name, $image_name, $rating, $feedback);

        if ($stmt->execute()) {
            header("Location: index.php?success=1");
            exit();
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Feedback - GD Edu Tech</title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="../Images/Logos/GD_Only_logo.png">

    <style>
        * {
            font-family: 'Poppins', sans-serif;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 15px;
            margin: 0;
        }

        .feedback-card {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
        }

        .feedback-header {
            background: linear-gradient(135deg, #0d7298 0%, #065d7d 100%);
            color: #ffffff;
            padding: 32px 30px;
            text-align: center;
            position: relative;
        }

        .form-label {
            font-weight: 600;
            color: #334155;
            font-size: 0.88rem;
            margin-bottom: 6px;
        }

        .custom-input {
            border-radius: 12px !important;
            border: 1.5px solid #cbd5e1 !important;
            padding: 12px 16px !important;
            font-size: 0.9rem !important;
            color: #0f172a !important;
            transition: all 0.3s ease !important;
        }

        .custom-input:focus {
            border-color: #0d7298 !important;
            box-shadow: 0 0 0 4px rgba(13, 114, 152, 0.12) !important;
        }

        .feedback-textarea {
            resize: none;
            line-height: 1.6;
        }

        /* Interactive Star Rating */
        .star-rating-container {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .star-icon {
            font-size: 1.8rem;
            color: #cbd5e1;
            cursor: pointer;
            transition: transform 0.2s ease, color 0.2s ease;
        }

        .star-icon.active, .star-icon:hover {
            color: #ffb703;
            transform: scale(1.15);
        }

        /* File Upload */
        .file-upload-box {
            border: 2px dashed #cbd5e1;
            border-radius: 14px;
            padding: 16px;
            text-align: center;
            background: #f8fafc;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-upload-box:hover {
            border-color: #0d7298;
            background: rgba(13, 114, 152, 0.04);
            color: #0d7298;
        }

        .btn-submit-feedback {
            background: linear-gradient(135deg, #0d7298 0%, #065d7d 100%);
            color: #ffffff;
            border: none;
            border-radius: 50px;
            padding: 14px;
            font-weight: 700;
            font-size: 1rem;
            box-shadow: 0 8px 20px rgba(13, 114, 152, 0.3);
            transition: all 0.3s ease;
        }

        .btn-submit-feedback:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(13, 114, 152, 0.4);
            color: #ffffff;
        }
    </style>
</head>

<body>

<div class="container" style="max-width: 650px;">
    <div class="feedback-card">
        
        <!-- Header Banner -->
        <div class="feedback-header">
            <img src="../Images/Logos/GD_Only_logo.png" alt="GD Edu Tech" style="height: 46px;" class="mb-2">
            <h3 class="fw-bold mb-1">Student Feedback</h3>
            <p class="mb-0 text-white-50 small">Share your learning experience & boost your career profile</p>
        </div>

        <div class="p-4 p-md-5">

            <!-- Success Notification Alert -->
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success rounded-4 border-0 shadow-sm p-3 mb-4 text-center">
                    <i class="bi bi-patch-check-fill fs-3 text-success d-block mb-1"></i>
                    <h6 class="fw-bold mb-1">Feedback Submitted Successfully!</h6>
                    <span class="small text-muted">Thank you for sharing your experience. Your feedback is awaiting admin approval.</span>
                </div>
            <?php endif; ?>

            <form action="index.php" method="POST" enctype="multipart/form-data">

                <!-- Full Name Input -->
                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-person me-1 text-primary"></i> Full Name *</label>
                    <input type="text" name="student_name" class="form-control custom-input" placeholder="e.g. Swadeep Kumar" required>
                </div>

                <!-- College Name Input -->
                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-mortarboard me-1 text-primary"></i> College / Institution *</label>
                    <input type="text" name="college_name" class="form-control custom-input" placeholder="e.g. Yenepoya University" required>
                </div>

                <!-- Course / Internship Input -->
                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-book me-1 text-primary"></i> Course Name / Internship *</label>
                    <input type="text" name="course_name" class="form-control custom-input" placeholder="e.g. Full Stack Web Development" required>
                </div>

                <!-- Interactive Star Rating -->
                <div class="mb-3">
                    <label class="form-label d-block"><i class="bi bi-star me-1 text-primary"></i> Overall Rating *</label>
                    <input type="hidden" name="rating" id="ratingInput" value="5">
                    <div class="star-rating-container" id="starContainer">
                        <i class="bi bi-star-fill star-icon active" data-value="1"></i>
                        <i class="bi bi-star-fill star-icon active" data-value="2"></i>
                        <i class="bi bi-star-fill star-icon active" data-value="3"></i>
                        <i class="bi bi-star-fill star-icon active" data-value="4"></i>
                        <i class="bi bi-star-fill star-icon active" data-value="5"></i>
                        <span class="ms-2 fw-semibold text-warning small" id="ratingLabel">5 Stars</span>
                    </div>
                </div>

                <!-- Feedback Message Textarea with Character Counter -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label mb-0"><i class="bi bi-chat-left-quote me-1 text-primary"></i> Feedback Message *</label>
                        <span class="text-muted small" id="charCount">0 / 100</span>
                    </div>
                    <textarea 
                        name="feedback" 
                        id="feedbackTextarea" 
                        class="form-control custom-input feedback-textarea" 
                        rows="4" 
                        maxlength="100" 
                        placeholder="Share your learning experience..." 
                        required
                    ></textarea>
                </div>

                <!-- Student Profile Image Upload -->
                <div class="mb-4">
                    <label class="form-label"><i class="bi bi-camera me-1 text-primary"></i> Student Photo (Optional)</label>
                    <label for="student_image" class="file-upload-box w-100 d-block">
                        <i class="bi bi-cloud-arrow-up fs-3 text-primary d-block mb-1"></i>
                        <span id="fileNameDisplay" class="fw-semibold text-secondary small">Click to upload your profile photo</span>
                        <input type="file" name="student_image" id="student_image" class="d-none" accept="image/*">
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" name="submit_feedback" class="btn btn-submit-feedback w-100">
                    <i class="bi bi-send-fill me-1.5"></i> Submit Feedback
                </button>

            </form>

        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Interactive Star Rating Logic
    const stars = document.querySelectorAll('.star-icon');
    const ratingInput = document.getElementById('ratingInput');
    const ratingLabel = document.getElementById('ratingLabel');

    stars.forEach(star => {
        star.addEventListener('click', function () {
            const selectedVal = parseInt(this.getAttribute('data-value'));
            ratingInput.value = selectedVal;
            ratingLabel.textContent = `${selectedVal} Star${selectedVal > 1 ? 's' : ''}`;

            stars.forEach((s, idx) => {
                if (idx < selectedVal) {
                    s.classList.add('active');
                    s.classList.replace('bi-star', 'bi-star-fill');
                } else {
                    s.classList.remove('active');
                    s.classList.replace('bi-star-fill', 'bi-star');
                }
            });
        });
    });

    // Dynamic Live Character Counter
    const textarea = document.getElementById('feedbackTextarea');
    const charCount = document.getElementById('charCount');

    if (textarea && charCount) {
        textarea.addEventListener('input', function () {
            const currentLen = this.value.length;
            charCount.textContent = `${currentLen} / 100`;
            if (currentLen >= 100) {
                charCount.classList.add('text-danger', 'fw-bold');
            } else {
                charCount.classList.remove('text-danger', 'fw-bold');
            }
        });
    }

    // Display Uploaded File Name
    const imageInput = document.getElementById('student_image');
    const fileNameDisplay = document.getElementById('fileNameDisplay');

    if (imageInput && fileNameDisplay) {
        imageInput.addEventListener('change', function () {
            if (this.files && this.files.length > 0) {
                fileNameDisplay.textContent = `Selected: ${this.files[0].name}`;
                fileNameDisplay.classList.replace('text-secondary', 'text-primary');
            } else {
                fileNameDisplay.textContent = 'Click to upload your profile photo';
            }
        });
    }
</script>

</body>
</html>