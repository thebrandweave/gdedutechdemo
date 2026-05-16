INSERT INTO Users (username, password_hash, email, first_name, last_name, role, profile_image)
VALUES ('john_doe', 'hashed_password', 'john@example.com', 'John', 'Doe', 'student', 'profile_image.jpg');

INSERT INTO Categories (name, description)
VALUES ('Web Development', 'Courses related to web development including HTML, CSS, JavaScript, and frameworks.');
INSERT INTO Courses (title, description, thumbnail, price, language, level, created_by, category_id, course_type, status, isPopular)
VALUES (
    'JavaScript for Beginners',
    'An introductory course to JavaScript programming.',
    'js_course_thumbnail.jpg',
    49.99,
    'English',
    'beginner',
    1,                  -- Assume User ID 1 is the creator
    1,                  -- Assume Category ID 1 is Web Development
    'Online',
    'published',
    'Yes'
);
INSERT INTO Lessons (course_id, title, description, lesson_order)
VALUES (
    1,                   -- Assume Course ID 1 is 'JavaScript for Beginners'
    'Introduction to JavaScript',
    'This lesson covers the basics of JavaScript.',
    1                    -- Order of the lesson
);
INSERT INTO Videos (lesson_id, title, description, video_url, duration, video_order)
VALUES (
    1,                  -- Assume Lesson ID 1 is 'Introduction to JavaScript'
    'JavaScript Basics',
    'A video explaining JavaScript basics.',
    'http://example.com/video.mp4',
    '00:10:45',
    1                   -- Order of the video
);

INSERT INTO Enrollments (student_id, course_id, payment_status, progress, access_status)
VALUES (
    1,                  -- Assume User ID 1 (student) is enrolled
    1,                  -- Assume Course ID 1 is 'JavaScript for Beginners'
    'completed',
    0.00,
    'active'
);

INSERT INTO Enrollments (student_id, course_id, payment_status, progress, access_status)
VALUES (
    1,                  -- Assume User ID 1 (student) is enrolled
    1,                  -- Assume Course ID 1 is 'JavaScript for Beginners'
    'completed',
    0.00,
    'active'
);
INSERT INTO Transactions (student_id, course_id, amount, payment_method)
VALUES (
    1,                  -- Assume User ID 1 (student)
    1,                  -- Assume Course ID 1
    49.99,
    'Credit Card'
);
INSERT INTO StaffAssignments (staff_id, course_id, role)
VALUES (
    2,                  -- Assume User ID 2 is a staff member
    1,                  -- Assume Course ID 1
    'instructor'
);

INSERT INTO Reviews (student_id, course_id, rating, comment)
VALUES (
    1,                  -- Assume User ID 1 (student)
    1,                  -- Assume Course ID 1
    5,
    'Great introductory course for JavaScript!'
);

INSERT INTO Quizzes (course_id, title, instructions, total_marks)
VALUES (
    1,                  -- Assume Course ID 1
    'JavaScript Basics Quiz',
    'Answer the following questions to test your JavaScript knowledge.',
    100
);
INSERT INTO Questions (quiz_id, content, option_a, option_b, option_c, option_d, correct_option)
VALUES (
    1,                  -- Assume Quiz ID 1
    'What is the correct way to declare a variable in JavaScript?',
    'var x;',
    'let x;',
    'const x;',
    'All of the above',
    'D'
);

INSERT INTO Certificates (student_id, course_id, certificate_url)
VALUES (
    1,                  -- Assume User ID 1 (student)
    1,                  -- Assume Course ID 1
    'http://example.com/certificate.pdf'
);
INSERT INTO Notifications (user_id, message)
VALUES (
    1,                  -- Assume User ID 1
    'Your course enrollment has been successful.'
);

INSERT INTO Logs (user_id, action_type, description, ip_address, user_agent)
VALUES (
    1,                  -- Assume User ID 1
    'login',
    'User logged in successfully.',
    '192.168.1.1',
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
);

INSERT INTO password_resets (user_id, token, expiry)
VALUES (
    1,                  -- Assume User ID 1
    'unique_reset_token',
    3600                -- Expiry in seconds (1 hour)
);

INSERT INTO recent_activities (user_id, user_name, activity_status, activity_type, activity_description)
VALUES (
    1,                  -- Assume User ID 1
    'john_doe',
    'completed',
    'course',
    'Completed the JavaScript for Beginners course.'
);

INSERT INTO UserProgress (user_id, course_id, lesson_id, video_id, completed)
VALUES (
    1,                  -- Assume User ID 1
    1,                  -- Assume Course ID 1
    1,                  -- Assume Lesson ID 1
    1,                  -- Assume Video ID 1
    1                   -- Mark as completed
);
