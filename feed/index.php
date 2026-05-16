<?php
require_once '../Configurations/config.php';

if(isset($_POST['submit_feedback'])){

    $student_name = $_POST['student_name'];
    $course_name = $_POST['course_name'];
    $college_name = $_POST['college_name'];
    $rating = $_POST['rating'];
    $feedback = $_POST['feedback'];

$image_name = 'user.png';
    if(!empty($_FILES['student_image']['name'])){

        $image_name = time() . '_' . $_FILES['student_image']['name'];

        move_uploaded_file(
            $_FILES['student_image']['tmp_name'],
            '../uploads/feedback/' . $image_name
        );
    }

    $query = "
    INSERT INTO student_feedback
   (
    student_name,
    course_name,
    college_name,
    student_image,
    rating,
    feedback
)
    VALUES
    (
        '$student_name',
        '$course_name',
        '$college_name',
        '$image_name',
        '$rating',
        '$feedback'
    )
    ";

$success = false;

if($conn->query($query)){

    header("Location: index.php?success=1");
    exit();

}
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Feedback</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
        <style>

.feedback-textarea{
    resize: none;
    border-radius: 16px;
    padding: 14px 16px;
    font-size: 15px;
    line-height: 1.7;

    border: 1px solid #dbe4f0;

    transition: all 0.3s ease;
}

.feedback-textarea:focus{
    border-color: #2563eb;
    box-shadow: 0 0 0 4px rgba(37,99,235,0.12);
}

.feedback-textarea::placeholder{
    color: #9ca3af;
}


    </style>
</head>

<body>

<div class="container py-5">

    <div class="row justify-content-center">
        <div class="col-lg-7">


            <div class="card shadow border-0 rounded-4 p-4">

                <h2 class="mb-4 text-center">
                    Student Feedback
                </h2>
<?php if(isset($_GET['success'])): ?>

    <div class="alert alert-success text-center">
        Feedback submitted successfully!
        Waiting for admin approval.
    </div>

<?php endif; ?>
                <form action="" method="POST" enctype="multipart/form-data">

                    <div class="mb-3">
                        <label>Name</label>
                        <input type="text" name="student_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
    <label>College Name</label>

    <input
        type="text"
        name="college_name"
        class="form-control"
        required
    >
</div>

                    <div class="mb-3">
                        <label>Course Name / Internship</label>
                        <input type="text" name="course_name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Rating</label>

                        <select name="rating" class="form-control">
                            <option value="5">5 Stars</option>
                            <option value="4">4 Stars</option>
                            <option value="3">3 Stars</option>
                            <option value="2">2 Stars</option>
                            <option value="1">1 Star</option>
                        </select>
                    </div>

                 <div class="mb-3">
    <label class="form-label fw-semibold">
        Feedback
    </label>

    <textarea
        name="feedback"
        class="form-control feedback-textarea"
        rows="5"
        maxlength="100"
        placeholder="Share your learning experience..."
        required
    ></textarea>

    <small class="text-muted">
        Maximum 100 characters
    </small>
</div>

                    <div class="mb-3">
                        <label>Student Image</label>

                        <input type="file" name="student_image" class="form-control">
                    </div>

                    <button class="btn btn-primary w-100" name="submit_feedback">
                        Submit Feedback
                    </button>

                </form>

            </div>

        </div>
    </div>

</div>

</body>
</html>