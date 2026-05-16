-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 24, 2025 at 09:43 AM
-- Server version: 11.8.3-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u229215627_edutech`
--

-- --------------------------------------------------------

--
-- Table structure for table `Accessories`
--

CREATE TABLE `Accessories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Accessories`
--

INSERT INTO `Accessories` (`id`, `name`, `description`, `price`, `image`, `created_at`, `updated_at`) VALUES
(1, 'MacBook air M1', 'The 13-inch MacBook Air is thin, light and capable — supercharged by the M1 chip and features up to 18 hours of battery life.', 79999.00, '../../uploads/shop_items/mac m1.jpg', '2024-12-22 06:11:53', '2024-12-22 06:11:53');

-- --------------------------------------------------------

--
-- Table structure for table `access_requests`
--

CREATE TABLE `access_requests` (
  `id` int(11) NOT NULL,
  `paper_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `request_time` datetime DEFAULT current_timestamp(),
  `status` enum('pending','granted') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ActivityLog`
--

CREATE TABLE `ActivityLog` (
  `activity_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `activity_type` enum('course_created','course_enrolled','quiz_completed','payment_made','faq_added','user_registered') DEFAULT NULL,
  `activity_description` text DEFAULT NULL,
  `related_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `AdminSettings`
--

CREATE TABLE `AdminSettings` (
  `setting_id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `value` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `AdminSettings`
--

INSERT INTO `AdminSettings` (`setting_id`, `setting_key`, `value`, `created_at`, `updated_at`) VALUES
(1, 'payment_qr', '1734847853_WhatsApp Image 2024-12-22 at 11.40.34 AM.jpeg', '2024-12-22 06:10:53', '2024-12-22 06:10:53');

-- --------------------------------------------------------

--
-- Table structure for table `Answers`
--

CREATE TABLE `Answers` (
  `answer_id` int(11) NOT NULL,
  `question_id` int(11) DEFAULT NULL,
  `content` text NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `BlogCategories`
--

CREATE TABLE `BlogCategories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `BlogCategories`
--

INSERT INTO `BlogCategories` (`category_id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Technical Event', NULL, '2025-10-11 09:08:43', '2025-10-11 09:08:43');

-- --------------------------------------------------------

--
-- Table structure for table `Blogs`
--

CREATE TABLE `Blogs` (
  `blog_id` int(11) NOT NULL,
  `main_cover_image` varchar(255) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL,
  `status` enum('draft','published','archived') DEFAULT 'draft',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `category_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `BlogSections`
--

CREATE TABLE `BlogSections` (
  `section_id` int(11) NOT NULL,
  `blog_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `section_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Careers`
--

CREATE TABLE `Careers` (
  `job_id` int(11) NOT NULL,
  `job_title` varchar(255) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `salary_range` varchar(100) DEFAULT NULL,
  `job_description` text NOT NULL,
  `requirements` text NOT NULL,
  `benefits` text DEFAULT NULL,
  `application_deadline` date DEFAULT NULL,
  `job_type` enum('Full-time','Part-time','Contract','Internship') NOT NULL,
  `status` enum('Active','Closed','Draft') DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Careers`
--

INSERT INTO `Careers` (`job_id`, `job_title`, `company_name`, `location`, `salary_range`, `job_description`, `requirements`, `benefits`, `application_deadline`, `job_type`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Academic Mentor', 'GD Edu Tech', 'Mudipu, Mangalore', '₹10,000 - ₹25,000', 'We are looking for passionate and dedicated tutors to join GD Edu Tech as School Subject Tutors. The role involves providing personalized tutoring to students across various school subjects, helping them understand concepts, improve their academic performance, and build confidence. Tutors will work with students of different age groups and academic levels, providing both one-on-one and group tutoring sessions.', 'A Bachelor\'s degree in Education or in a relevant subject (e.g., Mathematics, Science, English, etc.).\r\nStrong knowledge and understanding of the subjects being taught.\r\nPrior experience in teaching or tutoring is preferred, but not mandatory.\r\nExcellent communication and interpersonal skills.\r\nAbility to adapt teaching methods to suit the individual needs of students.\r\nPatience, empathy, and passion for helping students succeed.', 'Competitive salary with performance-based incentives.\r\nFlexible working hours.\r\nProfessional development opportunities, including training and workshops.\r\nA collaborative and supportive work environment.\r\nOpportunity to make a significant impact on students\' learning journeys.\r\nWork-from-home options (if applicable) for remote tutoring sessions.', '2024-12-31', 'Part-time', 'Active', '2024-12-22 06:49:57', '2024-12-22 06:49:57');

-- --------------------------------------------------------

--
-- Table structure for table `Categories`
--

CREATE TABLE `Categories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Categories`
--

INSERT INTO `Categories` (`category_id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Web Development', 'This course covers the fundamentals of building dynamic and interactive websites. It includes learning HTML, CSS, JavaScript, and frameworks like React and Node.js, helping you to create modern, responsive web applications.', '2024-12-22 05:36:57', '2024-12-22 05:36:57'),
(2, 'Microsoft Office', 'Learn the essentials of Microsoft Word, Excel, PowerPoint, and other Office tools. This course will help you master document creation, data analysis, presentations, and collaboration using Microsoft Office Suite for professional and personal use.', '2024-12-22 05:37:13', '2024-12-22 05:37:13'),
(3, 'Internet of Things (IoT)', 'Explore the world of connected devices with this course on IoT. It covers the basics of sensors, actuators, and communication protocols, as well as practical applications using platforms like Arduino, Raspberry Pi, and ESP32 to build smart systems and solutions.', '2024-12-22 05:37:30', '2024-12-22 05:37:30'),
(4, 'Computer Programming', 'Computer programming is the process of designing, writing, testing, and maintaining code that enables software and applications to function. It involves using programming languages like Python, Java, and C++ to create everything from websites and mobile apps to artificial intelligence and automation scripts. Whether you\'re a beginner learning the basics or an expert developing complex systems, programming is the foundation of modern technology.', '2025-03-01 17:44:50', '2025-03-01 17:44:50');

-- --------------------------------------------------------

--
-- Table structure for table `Certificates`
--

CREATE TABLE `Certificates` (
  `certificate_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `certificate_url` varchar(255) NOT NULL,
  `issue_date` datetime DEFAULT current_timestamp(),
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Certificates`
--

INSERT INTO `Certificates` (`certificate_id`, `student_id`, `course_id`, `certificate_url`, `issue_date`, `created_at`, `updated_at`) VALUES
(1, 3, 1, '../../uploads/certificates/1_3.png', '2024-12-22 06:29:46', '2024-12-22 06:29:46', '2024-12-22 06:34:40'),
(2, 4, 2, '../../uploads/certificates/2_4.pdf', '2024-12-22 10:33:03', '2024-12-22 10:33:03', '2024-12-22 10:34:24'),
(3, 7, 1, '../../uploads/certificates/1_7.png', '2025-01-11 16:47:33', '2025-01-11 16:47:33', '2025-02-26 10:05:57');

-- --------------------------------------------------------

--
-- Table structure for table `Courses`
--

CREATE TABLE `Courses` (
  `course_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL CHECK (`price` >= 0),
  `language` varchar(50) DEFAULT NULL,
  `level` enum('beginner','intermediate','advanced') NOT NULL,
  `created_by` int(11) NOT NULL,
  `date_created` datetime DEFAULT current_timestamp(),
  `category_id` int(11) DEFAULT NULL,
  `course_type` tinyint(1) DEFAULT 0,
  `status` enum('published','draft') DEFAULT 'published',
  `isPopular` varchar(50) DEFAULT NULL,
  `uploadedBy_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Courses`
--

INSERT INTO `Courses` (`course_id`, `title`, `description`, `thumbnail`, `price`, `language`, `level`, `created_by`, `date_created`, `category_id`, `course_type`, `status`, `isPopular`, `uploadedBy_id`, `created_at`, `updated_at`) VALUES
(1, 'Mastering HTML & CSS', 'This comprehensive course is designed to teach the foundational skills of web development by focusing on HTML and CSS. You will learn how to structure web pages with HTML, style them with CSS, and create responsive layouts using modern techniques like Flexbox and Grid. Whether you\'re a beginner or looking to enhance your skills, this course covers everything from basic tags and styles to advanced layout techniques, making it ideal for anyone looking to build attractive, user-friendly websites. By the end of the course, you\'ll have the skills to create clean, responsive, and visually appealing websites.', 'mastering-html-css_6767a67a77539_Web-Development-Course-Thumbnail.jpg', 999.00, 'English', 'beginner', 2, '2024-12-22 05:59:15', 1, 0, 'published', '1', NULL, '2024-12-22 05:59:15', '2024-12-22 06:41:37'),
(2, 'Fundamentals of Microsoft Office: Word, Excel, and PowerPoint', 'This comprehensive course covers the essential features and tools of Microsoft Word, Excel, and PowerPoint, equipping you with the skills to create professional documents, analyze data, and design impactful presentations. You will learn how to create and format documents in Word, organize and manipulate data in Excel, and design visually engaging slides in PowerPoint. Whether you\'re aiming to enhance productivity in the workplace or improve personal efficiency, this course will give you the knowledge to master these powerful Microsoft Office applications and perform everyday tasks with ease.', 'fundamentals-of-microsoft-office-word-excel-and-powerpoint_6767aefb1762d_OfficeApplicationThumbnail.jpg', 499.00, 'English', 'beginner', 2, '2024-12-22 06:23:41', 2, 0, 'published', '1', NULL, '2024-12-22 06:23:41', '2024-12-23 06:51:35'),
(4, '🚀 Master Python: Beginner to Confident Coder 🐍', 'This intensive  Python course is designed for absolute beginners who want to kickstart their programming journey. You’ll learn Python from scratch—covering fundamentals like variables, loops, and functions—while working on hands-on exercises. By the end of the course, you’ll build a mini-project that integrates all key concepts, setting you up for real-world coding success. Whether you\'re aiming to automate tasks, analyze data, or dive into software development, this course provides the perfect launchpad! 🚀💡', 'master-python-beginner-to-confident-coder_67c347f266aeb.webp', 999.00, 'English', 'beginner', 12, '2025-03-01 18:02:19', 4, 0, 'published', '1', NULL, '2025-03-01 18:02:19', '2025-03-02 12:04:18');

-- --------------------------------------------------------

--
-- Table structure for table `Documents`
--

CREATE TABLE `Documents` (
  `document_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT 0.00 CHECK (`price` >= 0),
  `document_url` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `upload_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Enrollments`
--

CREATE TABLE `Enrollments` (
  `enrollment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `purchase_date` datetime DEFAULT current_timestamp(),
  `payment_status` enum('completed','pending','failed') DEFAULT 'completed',
  `progress` decimal(5,2) DEFAULT 0.00 CHECK (`progress` >= 0 and `progress` <= 100),
  `access_status` enum('active','expired','canceled') DEFAULT 'active',
  `completion_status` enum('pending','completed') DEFAULT 'pending',
  `assessment_status` enum('pending','completed') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Enrollments`
--

INSERT INTO `Enrollments` (`enrollment_id`, `student_id`, `course_id`, `purchase_date`, `payment_status`, `progress`, `access_status`, `completion_status`, `assessment_status`, `created_at`, `updated_at`) VALUES
(1, 3, 1, '2024-12-22 06:27:58', 'completed', 100.00, 'active', 'completed', 'completed', '2024-12-22 06:27:58', '2024-12-22 06:31:25'),
(2, 4, 2, '2024-12-22 07:21:43', 'completed', 100.00, 'active', 'completed', 'completed', '2024-12-22 07:21:43', '2024-12-22 10:33:26'),
(3, 7, 1, '2025-01-11 16:45:58', 'completed', 100.00, 'active', 'completed', 'completed', '2025-01-11 16:45:58', '2025-01-11 16:48:13'),
(4, 3, 4, '2025-03-09 09:09:26', 'completed', 100.00, 'active', 'pending', 'pending', '2025-03-09 09:09:26', '2025-03-09 09:35:59'),
(5, 30, 1, '2025-07-02 16:24:32', 'pending', 0.00, '', 'pending', 'pending', '2025-07-02 16:24:32', '2025-07-02 16:24:32');

-- --------------------------------------------------------

--
-- Table structure for table `EventCategories`
--

CREATE TABLE `EventCategories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `EventCategories`
--

INSERT INTO `EventCategories` (`category_id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Technical Event', NULL, '2025-10-11 09:24:47', '2025-10-11 09:24:47');

-- --------------------------------------------------------

--
-- Table structure for table `Events`
--

CREATE TABLE `Events` (
  `event_id` int(11) NOT NULL,
  `main_cover_image` varchar(255) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `event_time` time DEFAULT NULL,
  `event_link` varchar(255) DEFAULT NULL,
  `media_url` varchar(255) DEFAULT NULL,
  `organizer_id` int(11) DEFAULT NULL,
  `status` enum('upcoming','ongoing','completed','cancelled') DEFAULT 'upcoming',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `category_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events_images`
--

CREATE TABLE `events_images` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `FAQs`
--

CREATE TABLE `FAQs` (
  `faq_id` int(11) NOT NULL,
  `question` text NOT NULL,
  `answer` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_applications`
--

CREATE TABLE `job_applications` (
  `application_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `resume_path` varchar(255) NOT NULL,
  `cover_letter` text DEFAULT NULL,
  `portfolio_url` varchar(255) DEFAULT NULL,
  `application_date` datetime NOT NULL,
  `status` enum('Pending','Reviewed','Shortlisted','Rejected') NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `job_applications`
--

INSERT INTO `job_applications` (`application_id`, `job_id`, `first_name`, `last_name`, `email`, `phone`, `resume_path`, `cover_letter`, `portfolio_url`, `application_date`, `status`) VALUES
(1, 1, 'abdul', 'mausooq', 'abdulmausooq@gmail.com', '9901845606', 'Uploads/resumes/resume_6767eb7a8d4c0.pdf', 'mausooq', 'http://localhost/gdedutech.in/apply.php?job_id=5', '2024-12-22 10:35:38', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `Lessons`
--

CREATE TABLE `Lessons` (
  `lesson_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `lesson_order` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Lessons`
--

INSERT INTO `Lessons` (`lesson_id`, `course_id`, `title`, `description`, `lesson_order`, `created_at`, `updated_at`) VALUES
(1, 1, 'Introduction to HTML & Basic Structure', 'In this lesson, you will learn the basics of HTML, the foundation of every web page. We\'ll cover essential HTML tags, structure, and how to organize content effectively within a webpage. By the end of this lesson, you\'ll understand the anatomy of an HTML document and be able to create your first simple web page.', 1, '2024-12-22 05:59:15', '2024-12-22 05:59:15'),
(2, 2, 'Mastering Microsoft Word for Document Creation', 'This lesson focuses on the key features of Microsoft Word for creating, formatting, and editing professional documents. You will learn how to use essential tools like styles, tables, headers, and footers to structure your documents efficiently. By the end of this lesson, you\'ll be able to create well-organized, polished documents for any purpose.', 1, '2024-12-22 06:23:41', '2024-12-22 06:23:41'),
(3, 2, 'Introduction to Excel for Data Analysis', 'In this lesson, you\'ll explore Microsoft Excel’s core functionalities for managing and analyzing data. You will learn how to enter data, apply basic formulas, and create simple charts. By the end of this lesson, you\'ll be able to organize data and perform basic analysis using Excel’s powerful features.', 2, '2024-12-22 06:23:42', '2024-12-22 06:23:42'),
(5, 4, 'Module 1', 'Introduction to Python and fundamental programming concepts.', 1, '2025-03-01 18:02:19', '2025-03-01 18:02:19'),
(6, 4, 'Module 2', 'Understanding variables, data types, and user input/output.', 2, '2025-03-01 18:02:19', '2025-03-01 18:02:19'),
(7, 4, 'Module 3', 'Control flow using conditionals and loops.', 3, '2025-03-01 18:02:19', '2025-03-01 18:02:19'),
(8, 4, 'Module 4', 'Functions and modular programming.', 4, '2025-03-01 18:02:19', '2025-03-01 18:02:19'),
(9, 4, 'Module 5', 'Working with lists and dictionaries.', 5, '2025-03-01 18:02:19', '2025-03-01 18:02:19'),
(10, 4, 'Module 6', 'Applying all concepts in a real-world mini-project.', 6, '2025-03-01 18:02:19', '2025-03-01 18:02:19');

-- --------------------------------------------------------

--
-- Table structure for table `Logs`
--

CREATE TABLE `Logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action_type` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `meeting_schedules`
--

CREATE TABLE `meeting_schedules` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `meeting_date` date NOT NULL,
  `meeting_time` time NOT NULL,
  `meeting_link` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected','completed') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Messages`
--

CREATE TABLE `Messages` (
  `message_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Messages`
--

INSERT INTO `Messages` (`message_id`, `title`, `content`, `created_by`, `created_at`) VALUES
(1, 'New Job Posting: School Subject Tutor at GD Edu Tech', 'We are excited to announce a new job opening for the position of School Subject Tutor at GD Edu Tech in Mudipu, Mangalore. We are looking for passionate and dedicated individuals who can help students excel in their academic journey.', 1, '2024-12-22 06:54:09');

-- --------------------------------------------------------

--
-- Table structure for table `Notifications`
--

CREATE TABLE `Notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `date_sent` datetime DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expiry` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Questions`
--

CREATE TABLE `Questions` (
  `question_id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `option_a` varchar(255) NOT NULL,
  `option_b` varchar(255) NOT NULL,
  `option_c` varchar(255) NOT NULL,
  `option_d` varchar(255) NOT NULL,
  `correct_option` char(1) DEFAULT NULL CHECK (`correct_option` in ('A','B','C','D')),
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Questions`
--

INSERT INTO `Questions` (`question_id`, `quiz_id`, `content`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_option`, `created_at`, `updated_at`) VALUES
(1, 1, 'What does HTML stand for?', 'HyperText Markup Language', 'Hyper Tool Markup Language', 'HighText Markup Language', 'None of the above', 'A', '2024-12-22 06:02:40', '2024-12-22 06:02:40'),
(2, 1, 'Which tag is used to define the main content of an HTML page?', '<header>', '<footer>', '<body>', '<main>', 'C', '2024-12-22 06:03:12', '2024-12-22 06:03:12'),
(3, 1, 'Which CSS property is used to change the text color of an element?', 'color', 'font-color', 'text-color', 'text-style', 'A', '2024-12-22 06:03:50', '2024-12-22 06:03:50'),
(4, 1, 'Which HTML element is used to define a hyperlink?', '<link>', '<a>', '<href>', '<url>', 'B', '2024-12-22 06:04:23', '2024-12-22 06:04:23'),
(5, 2, 'In Microsoft Word, which of the following is used to quickly apply consistent formatting to a section of text?', 'Styles', 'Themes', 'Headers', 'Paragraph formatting', 'A', '2024-12-22 06:25:55', '2024-12-22 06:25:55'),
(6, 2, 'Which function in Microsoft Excel is used to calculate the sum of a range of numbers?', 'AVERAGE', 'COUNT', 'SUM', 'IF', 'C', '2024-12-22 06:26:31', '2024-12-22 06:26:31'),
(7, 2, 'In Microsoft PowerPoint, which of the following is used to apply visual effects to slide transitions?', 'Slide Layout', 'Animations', 'Transitions', 'Themes', 'C', '2024-12-22 06:27:03', '2024-12-22 06:27:03'),
(10, 5, 'qpq', 'p', 'p', 'p', 'p', 'A', '2025-03-02 13:01:09', '2025-03-02 13:01:09');

-- --------------------------------------------------------

--
-- Table structure for table `question_papers`
--

CREATE TABLE `question_papers` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `pdf` varchar(255) NOT NULL,
  `status` enum('locked','open') NOT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Quizzes`
--

CREATE TABLE `Quizzes` (
  `quiz_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `instructions` text DEFAULT NULL,
  `total_marks` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Quizzes`
--

INSERT INTO `Quizzes` (`quiz_id`, `course_id`, `title`, `instructions`, `total_marks`, `created_at`, `updated_at`) VALUES
(1, 1, 'HTML & CSS Basics Quiz', 'This quiz tests your understanding of the fundamental concepts of HTML and CSS. It covers the basics of HTML structure, essential tags, and introductory CSS properties. Successfully completing this quiz will ensure you have a solid foundation in creating and styling web pages.', 10, '2024-12-22 06:01:57', '2024-12-22 06:01:57'),
(2, 2, 'Microsoft Office Essentials Quiz', 'This quiz evaluates your understanding of the core features and tools in Microsoft Word, Excel, and PowerPoint. It covers document formatting, data analysis, and presentation design. Test your knowledge to ensure you have a solid grasp of the essential functions of these powerful Microsoft Office applications.', 10, '2024-12-22 06:25:12', '2024-12-22 06:25:12'),
(5, 4, 'test1', 't', 10, '2025-03-02 13:01:03', '2025-03-02 13:03:47');

-- --------------------------------------------------------

--
-- Table structure for table `recent_activities`
--

CREATE TABLE `recent_activities` (
  `activity_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_name` varchar(50) NOT NULL,
  `activity_status` varchar(50) NOT NULL,
  `activity_type` varchar(50) NOT NULL,
  `activity_description` text DEFAULT NULL,
  `activity_timestamp` timestamp NULL DEFAULT current_timestamp(),
  `additional_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`additional_details`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Reviews`
--

CREATE TABLE `Reviews` (
  `review_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `date_posted` datetime DEFAULT current_timestamp(),
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `social_links`
--

CREATE TABLE `social_links` (
  `id` int(11) NOT NULL,
  `target_type` enum('blog','event','website') NOT NULL,
  `target_id` int(11) DEFAULT NULL,
  `platform` varchar(100) NOT NULL,
  `url` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `social_links`
--

INSERT INTO `social_links` (`id`, `target_type`, `target_id`, `platform`, `url`, `created_at`, `updated_at`) VALUES
(2, 'event', 1, 'instagram', 'https://www.instagram.com/', '2025-10-11 10:14:12', '2025-10-11 10:14:12');

-- --------------------------------------------------------

--
-- Table structure for table `StaffAssignments`
--

CREATE TABLE `StaffAssignments` (
  `assignment_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `role` enum('instructor','assistant') NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `StudentAnswers`
--

CREATE TABLE `StudentAnswers` (
  `answer_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `StudentAnswers`
--

INSERT INTO `StudentAnswers` (`answer_id`, `question_id`, `content`, `user_id`, `created_at`) VALUES
(1, 1, 'We are generating your certificate soon, please keep checking on the profile section.', 2, '2024-12-22 06:57:23');

-- --------------------------------------------------------

--
-- Table structure for table `StudentQuestions`
--

CREATE TABLE `StudentQuestions` (
  `question_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `status` enum('open','answered') DEFAULT 'open'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `StudentQuestions`
--

INSERT INTO `StudentQuestions` (`question_id`, `title`, `content`, `user_id`, `created_at`, `status`) VALUES
(1, 'Regarding certificate', 'I have completed the web dev course, when will i get the certificate?', 3, '2024-12-22 06:56:42', 'answered');

-- --------------------------------------------------------

--
-- Table structure for table `Transactions`
--

CREATE TABLE `Transactions` (
  `transaction_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL CHECK (`amount` >= 0),
  `payment_date` datetime DEFAULT current_timestamp(),
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_proof` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Transactions`
--

INSERT INTO `Transactions` (`transaction_id`, `student_id`, `course_id`, `amount`, `payment_date`, `payment_method`, `payment_proof`, `status`, `created_at`, `updated_at`) VALUES
(1, 3, 1, 999.00, '2024-12-22 06:27:58', 'QR_CODE', '1734848878_Screenshot 2024-10-26 094403.png', 'approved', '2024-12-22 06:27:58', '2024-12-22 06:28:16'),
(2, 4, 2, 499.00, '2024-12-22 07:21:43', 'QR_CODE', '1734852103_PXL_20241215_075258880.jpg', 'approved', '2024-12-22 07:21:43', '2024-12-22 10:31:06'),
(3, 7, 1, 999.00, '2025-01-11 16:45:58', 'QR_CODE', '1736613958_OfficeApplicationThumbnail.jpg', 'approved', '2025-01-11 16:45:58', '2025-01-11 16:46:38'),
(4, 3, 4, 999.00, '2025-03-09 09:09:26', 'QR_CODE', '1741511366_6198ae9c-078e-4149-ad2c-6616b872fc1b.webp', 'approved', '2025-03-09 09:09:26', '2025-03-09 09:09:55'),
(5, 30, 1, 999.00, '2025-07-02 16:24:32', 'QR_CODE', '1751473472_1000049979.jpg', 'pending', '2025-07-02 16:24:32', '2025-07-02 16:24:32');

-- --------------------------------------------------------

--
-- Table structure for table `UserProgress`
--

CREATE TABLE `UserProgress` (
  `progress_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `lesson_id` int(11) NOT NULL,
  `video_id` int(11) NOT NULL,
  `completed` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `UserProgress`
--

INSERT INTO `UserProgress` (`progress_id`, `user_id`, `course_id`, `lesson_id`, `video_id`, `completed`, `created_at`, `updated_at`) VALUES
(1, 3, 1, 1, 1, 1, '2024-12-22 06:29:14', '2024-12-22 06:29:14'),
(2, 3, 1, 1, 2, 1, '2024-12-22 06:29:46', '2024-12-22 06:29:46'),
(3, 4, 2, 2, 3, 1, '2024-12-22 10:32:35', '2024-12-22 10:32:35'),
(4, 4, 2, 3, 4, 1, '2024-12-22 10:33:03', '2024-12-22 10:33:03'),
(5, 7, 1, 1, 1, 1, '2025-01-11 16:47:15', '2025-01-11 16:47:15'),
(6, 7, 1, 1, 2, 1, '2025-01-11 16:47:33', '2025-01-11 16:47:33'),
(7, 3, 4, 5, 6, 1, '2025-03-09 09:35:59', '2025-03-09 09:35:59'),
(8, 3, 4, 6, 7, 1, '2025-03-09 09:39:46', '2025-03-09 09:39:46');

-- --------------------------------------------------------

--
-- Table structure for table `Users`
--

CREATE TABLE `Users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(150) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `role` enum('admin','Staff','student') NOT NULL,
  `date_joined` datetime DEFAULT current_timestamp(),
  `profile_image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','banned') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Users`
--

INSERT INTO `Users` (`user_id`, `username`, `password_hash`, `email`, `first_name`, `last_name`, `role`, `date_joined`, `profile_image`, `status`, `created_at`, `updated_at`) VALUES
(1, 'azlan', '$2y$10$jwx4gMT7/3lfNc.oYLDt3OP8npnXTV17fTxfeDCrNrJCyNM8zJdSi', 'muhammedazlan11@gmail.com', 'Muhammad', 'Ajlan', 'admin', '2024-12-22 05:14:35', NULL, 'active', '2024-12-22 05:14:35', '2024-12-22 05:14:35'),
(2, 'Mausooq', '$2y$10$JOE/zusINJASaAUwno8yIedJwinrMG1l2m0/Xhzo3CC5XMJW9uf3G', 'abdulmausooq@gmail.com', 'Abdul', 'Mausooq', 'Staff', '2024-12-22 05:17:21', NULL, 'active', '2024-12-22 05:17:21', '2024-12-22 05:17:21'),
(3, 'thameem', '$2y$10$eVKR0Xhus71YHAmXYmWFE.5VsfRK1pP/IyEkubwbDlEnM9piqPgWC', 'ahmedthameem20@gmail.com', 'Ahmed', 'Thameem', 'student', '2024-12-22 05:20:14', NULL, 'active', '2024-12-22 05:20:14', '2024-12-22 05:20:14'),
(4, 'Mausooq100', '$2y$10$ZIRQMVk5FocMFYuhqCcoGuaevAqEtCu2w0y4tPZ45.TxwuE/J5wQu', 'abdulmausooq100@gmail.com', 'Abdul', 'Mausooq', 'student', '2024-12-22 07:20:04', NULL, 'active', '2024-12-22 07:20:04', '2024-12-22 07:20:04'),
(5, 'user11', '$2y$10$oK1WMFU3XpZOz0BtcWOs1ub6EKn3rWmIOSUicGM8FHxK6pmZdV2nq', 'user11@gmail.com', 'user1', 'lastnew', 'student', '2024-12-24 13:44:51', './student_profile/student_user11.php', 'active', '2024-12-24 13:44:51', '2025-01-04 07:57:16'),
(6, 'user22', '$2y$10$oCwjp9yGyToJTcssUNTM/OI/0gS5yTb78BUBpdtksKSqhwi/tAWR2', 'user2@gmail.com', 'user2', 'last2', 'student', '2024-12-26 14:08:20', NULL, 'active', '2024-12-26 14:08:20', '2024-12-26 14:29:36'),
(7, 'azlan11', '$2y$10$VsPrWAEwbvAHWAXYU6XuZuJERXMxev9MbS8PHah79dNuNcmWMbZ3q', 'azlan.ajju@gmail.com', 'muhammad', 'azlan', 'student', '2025-01-11 16:45:22', './student_profile/student_azlan11.jpg', 'active', '2025-01-11 16:45:22', '2025-01-11 16:50:15'),
(8, 'Shakeeb', '$2y$10$D7hC4XGua54OTZuYUSEJmOzFlgBXqrxPU4H9ZiIDheKE2.bgAwDUK', 'sherifshakeeb313@gmail.com', 'Mohammed', 'Shakeeb', 'Staff', '2025-01-11 16:51:42', NULL, 'active', '2025-01-11 16:51:42', '2025-01-11 16:51:42'),
(10, 'Mausooq1', '$2y$10$DFTaGf3bPmBV5YthlQPLY.NyqeuNMQLYXQjOajI32jWNh5Uh4hNka', 'abdulmausooq1@gmail.com', 'Abdul', 'Mausooq', 'student', '2025-01-18 06:57:25', NULL, 'active', '2025-01-18 06:57:25', '2025-01-18 06:57:25'),
(11, 'thameem20', '$2y$10$yDqsl.ZcWbiEtoYKCwxYzekVH7v33FfOd9DBVW98CIT4Z0nLvEl6.', 'Ahmedthameem220@gmail.com', 'Ahmed', 'thameem', 'student', '2025-02-06 08:05:41', NULL, 'active', '2025-02-06 08:05:41', '2025-02-06 08:05:41'),
(12, 'Haashid', '$2y$10$iDREtwBYJOtOE6aotcL4SOqV14k9db7HGtrIeklJXSjVZR7OEZkLy', 'haashid.0607@gmail.com', 'Haashid', 'Haashid', 'Staff', '2025-02-26 09:59:05', NULL, 'active', '2025-02-26 09:59:05', '2025-02-26 09:59:05'),
(15, 'Robertlb', '$2y$10$KiL/6RnFe./0hNcrPnpZ4ujhGLmmy7vHl7dE9dPgc01X0hT7q44TW', 'himself@prostpro.fun', 'RobertnfAA', 'RobertlyAA', 'student', '2025-04-01 04:59:57', NULL, 'active', '2025-04-01 04:59:57', '2025-04-01 04:59:57'),
(16, 'Rohanme1', '$2y$10$yDkWMZ.tF9PXxsTKn4NbZOD0HNeZAsWlLf1E8t2EoIpb4nQCxf4R2', 'Rohanmesta157@gmail.com', 'Rohit', 'Mesta', 'student', '2025-04-02 16:00:22', NULL, 'active', '2025-04-02 16:00:22', '2025-04-02 16:00:22'),
(17, 'aboobakar_sinam', '$2y$10$t63olf99IOtgKdu6Su9y0OdH7t121QIAgsLW18fH4yn4npRvrWffS', 'Ssinan918@gmail.com', 'Aboobakar', 'Sinan', 'student', '2025-04-08 07:12:08', NULL, 'active', '2025-04-08 07:12:08', '2025-04-08 07:12:08'),
(19, 'munazzabegam', '$2y$10$HDPI8euMZ5a1JW0GF2tp7OsnfIawZYJkajSzdQwT/jwVh8Kk45BPW', 'ra353473@gmail.com', 'Munazza', 'Begam', 'student', '2025-04-19 04:03:03', NULL, 'active', '2025-04-19 04:03:03', '2025-04-19 04:03:03'),
(20, 'student', '$2y$10$DiblKqm0ORLhtHffao44FOJtSV57UxTunhZ9ipBG.NISEMyI35Qwq', 'student@gmail.com', 'test', 'student', 'student', '2025-05-05 05:23:22', NULL, 'active', '2025-05-05 05:23:22', '2025-05-05 05:23:22'),
(21, 'acchuss_01', '$2y$10$KzFstY0Z31oPRtyoZfp40e1nt5NjiBo4UHXRy5.jAGxhvu1i49Xdy', 'ashwathas2887@gmail.com', 'Ashwath', 'As', 'student', '2025-05-17 10:09:18', NULL, 'active', '2025-05-17 10:09:18', '2025-05-17 10:09:18'),
(22, 'Munazza_staff', '$2y$10$.gS8dGk19iQDVWX8hmJPxeljlgfidzREIwNTnVloOlENk0guo318W', 'munazzabegam11@gmail.com', 'Munazza', 'Begam', 'Staff', '2025-05-17 10:39:13', NULL, 'active', '2025-05-17 10:39:13', '2025-05-17 10:39:13'),
(23, 'Munazza_admin', '$2y$10$1pYMSn1AMc4Yg7TU1c6T8.HH6fo4ip3oYbMjeOkxfvm1Jq/1lvRyO', 'munazzabegam20@gmail.com', 'Munazza', 'Begam', 'admin', '2025-05-17 10:40:02', NULL, 'active', '2025-05-17 10:40:02', '2025-05-17 10:40:02'),
(24, 'vaish123', '$2y$10$VC.GitlyMgVKk8yhnoG6NeObjqtQXfeMK3B5Fc7pV5YKIdISPFL8K', 'vrsaralaya325@gmail.com', 'Vaishnavi', 'R S', 'student', '2025-05-19 07:07:08', NULL, 'active', '2025-05-19 07:07:08', '2025-05-19 07:07:08'),
(27, 'NARYTHY3922425NERTHRRTH', '$2y$10$WQpVGcRdnINcJ4/fP2jxL.1qM6G30dXh8fLWUJ/Z9XTsG0t3XMxNi', 'pqftodxa@bonsoirmail.com', 'NARYTHY3922425NERTHRRTH', 'NARYTHY3922425NERTHRRTH', 'student', '2025-06-15 20:03:32', NULL, 'active', '2025-06-15 20:03:32', '2025-06-15 20:03:32'),
(28, 'NAERTERHTE8346NERTYTRY', '$2y$10$juWQ5yEpHRbjYU.HBZgbCeqQqH3dIDDVaSqlXniOgMhmDjjGr12GW', 'ueattupa@aurevoirmail.com', 'NAERTERHTE8346NERTYTRY', 'NAERTERHTE8346NERTYTRY', 'student', '2025-06-27 16:53:33', NULL, 'active', '2025-06-27 16:53:33', '2025-06-27 16:53:33'),
(29, 'NAERTREGE403817NERTHRRTH', '$2y$10$WTrYViHT3SBt5lLXa6QziOsGCgdoTroy/vu9b84nRCWrMvYmVsYDC', 'djiaqsjq@aurevoirmail.com', 'NAERTREGE403817NERTHRRTH', 'NAERTREGE403817NERTHRRTH', 'student', '2025-06-28 18:56:33', NULL, 'active', '2025-06-28 18:56:33', '2025-06-28 18:56:33'),
(30, 'Shaheer', '$2y$10$qdf5GIi0QptGqu1IxiroMOBZsJWKpo5jixWqnCKGmpQ2umVdfkf6G', '4pa21cs057@pace.edu.in', 'Shaheer', 'Shaheer', 'student', '2025-07-02 16:23:43', NULL, 'active', '2025-07-02 16:23:43', '2025-07-02 16:23:43'),
(31, 'thameem123', '$2y$10$OVo1o.dgbGy3Gnize5KaY.ixlfOYof5I5oUwZlQldzg99INP/eYSa', 'Ahmed.thameem@gmail.com', 'Ahmed', 'Thameem', 'student', '2025-07-09 06:06:29', NULL, 'active', '2025-07-09 06:06:29', '2025-07-09 06:06:29'),
(32, 'Mfuehudwj hiwjswdwidjwidji jdiwjswihdfeufhiwj ijdiwjwihdiwkdoq jiwjdwidjwifjei jwdodkwofjiehiehgiejd', '$2y$10$wYZcUEc3t9WvrDJiuTX8W.C1XhIEM8T0Wq9lIRnj3b262n/BcTdDa', 'nomin.momin+392w2@mail.ru', 'Mfuehudwj hiwjswdwidjwidji jdiwjswihdfeufhiwj ijdiwjwihdiwkdoq jiwjdwidjwifjei jwdodkwofjiehiehgiejd', 'Mfuehudwj hiwjswdwidjwidji jdiwjswihdfeufhiwj ijdiwjwihdiwkdoq jiwjdwidjwifjei jwdodkwofjiehiehgiejd', 'student', '2025-08-06 07:04:11', NULL, 'active', '2025-08-06 07:04:11', '2025-08-06 07:04:11'),
(34, 'Shrauneml', '$2y$10$iPbbuLO1HIIpzeC3yPhjBOenMHTwIz/SgMpEapAPs9rGsnd2diFvW', 'yasmamadoo@bubuk.site', 'ShraunzipGY', 'ShraunjpcGY', 'student', '2025-09-08 20:38:22', NULL, 'active', '2025-09-08 20:38:22', '2025-09-08 20:38:22'),
(35, 'testuser111', '$2y$10$XVbQzOyyQLiSQkJY/uibd.mktISbhk9NW6USxYvmlGDfuH/XJ54Mi', 'testuser111@gmail.com', 'test', 'user', 'student', '2025-09-21 07:38:43', NULL, 'active', '2025-09-21 07:38:43', '2025-09-21 07:38:43'),
(36, 'mFuBYRIuezvPHPG', '$2y$10$xOTBCnVD237pqp5/qqaDUOdFv9bKxuvqf8EymSBFIlBMIfh6V.0Mq', 'erosasix08@gmail.com', 'GKohzHbvb', 'ksbZHcuR', 'student', '2025-10-04 07:51:19', NULL, 'active', '2025-10-04 07:51:19', '2025-10-04 07:51:19'),
(37, 'hZyujGovvW', '$2y$10$n4bIEmwR49ke/m93zVivN.D/.WXEqTum5EN9L7AHFN8oNS5UO67F6', 'anefugopi04@gmail.com', 'dbxdsDLkuM', 'PdFNieCTLTtMSoD', 'student', '2025-10-09 22:51:53', NULL, 'active', '2025-10-09 22:51:53', '2025-10-09 22:51:53'),
(38, 'OGyDbqJD', '$2y$10$V6hEfVK1pfMFiie1iRmKK.1xmifG3GV/aWnYhazrxNsNuGjOSSgHS', 'dageyarivi55@gmail.com', 'SSncpitdlRQWwzgN', 'moLbQLqnIKplG', 'student', '2025-10-12 08:24:53', NULL, 'active', '2025-10-12 08:24:53', '2025-10-12 08:24:53'),
(39, 'HvKZYYlVstsgBiOM', '$2y$10$99m0TXXWWp6pk4a24ExM7eWY8nhNN.ILA2pXnHyEROCijWEylowrK', 'ufodijafa633@gmail.com', 'hOTwjKFjVRC', 'LHPrDZmLZn', 'student', '2025-10-12 22:33:39', NULL, 'active', '2025-10-12 22:33:39', '2025-10-12 22:33:39'),
(40, 'eRoExHWUrmCu', '$2y$10$onwxJkDAxI.m4aVl47e0Pebfs4KQGMr7qayQ2mFW5jA9Xse/DcpSq', 'uluwulet850@gmail.com', 'TQtUDbKtKEiWk', 'QiVOQFQbz', 'student', '2025-10-13 04:39:12', NULL, 'active', '2025-10-13 04:39:12', '2025-10-13 04:39:12'),
(41, 'odQCgQJSES', '$2y$10$XGb71CzKSIxFZ9sDNR/p3uT6hJfHlRYVEfItScFJHkglfuxo4WpLC', 'ponujayuw58@gmail.com', 'DHTluRNsgbD', 'BGijCsenAgpVG', 'student', '2025-10-14 07:18:43', NULL, 'active', '2025-10-14 07:18:43', '2025-10-14 07:18:43'),
(42, 'Lopoloifhidwjdwfefee fjedwjdwj ijwhfwdj wfiefwjdwd hwidjwidhwfhwidjiwj hjfhefjhwifhewfiwejj hfiwhfqw', '$2y$10$AWjW/YPMqXjRHTGJK1PCWelHPZv5hcizPpgdtdYLwWLuIqfUXGcDO', 'nomin.momin+369l1@mail.ru', 'Lopoloifhidwjdwfefee fjedwjdwj ijwhfwdj wfiefwjdwd hwidjwidhwfhwidjiwj hjfhefjhwifhewfiwejj hfiwhfqw', 'Lopoloifhidwjdwfefee fjedwjdwj ijwhfwdj wfiefwjdwd hwidjwidhwfhwidjiwj hjfhefjhwifhewfiwejj hfiwhfqw', 'student', '2025-10-16 00:35:16', NULL, 'active', '2025-10-16 00:35:16', '2025-10-16 00:35:16'),
(43, 'AxuVtzSt', '$2y$10$drM49H9uFKz.CjIO6I3UO.FPxFDH7M0WVHHvMu5LW46rSZbFd6i9e', 'omeyejunix64@gmail.com', 'aTnSytSDhWB', 'rGhBRKnURK', 'student', '2025-10-17 18:46:42', NULL, 'active', '2025-10-17 18:46:42', '2025-10-17 18:46:42'),
(44, 'SsoGUvoFrH', '$2y$10$Ud9YMnKedKc/ZXFdWbL4pO8QMvyKixuEsj/DyI/KOi5ux6yOLWWSC', 'qezevacif424@gmail.com', 'eyHmiZyBY', 'dQUGHpjL', 'student', '2025-10-17 19:52:49', NULL, 'active', '2025-10-17 19:52:49', '2025-10-17 19:52:49'),
(45, 'LOUgFGtk', '$2y$10$p742XbUMnTZxs9oR3agpOe2Mn3YNL5nr3ZZehSYRkzecPVO.p/erm', 'teronezeke81@gmail.com', 'wvnuxcwia', 'uhdLiabXnA', 'student', '2025-10-18 01:05:23', NULL, 'active', '2025-10-18 01:05:23', '2025-10-18 01:05:23'),
(46, 'RQMUMLGPB', '$2y$10$Kqc2LGFe7JSRKO0wKMjm3uCnIGQb0BrvAHUPaJ1iPujtijbGnkh5C', 'axacovuleku468@gmail.com', 'gfHniKZtgMYkX', 'tMltMGStwB', 'student', '2025-10-18 14:20:51', NULL, 'active', '2025-10-18 14:20:51', '2025-10-18 14:20:51'),
(47, 'uMeDNmQQYSvnJRcZtNIBrhi', '$2y$10$FOQz8b7XgxlOeSbLr7B/M.90nQFdcVn2TBIWvlMcxAM9SKi/ACZW6', 'nulizibor35@gmail.com', 'sVVkAQojPMfZIvkAzzHe', 'NfmVmQpMQOpXmWkRIlz', 'student', '2025-10-22 03:48:55', NULL, 'active', '2025-10-22 03:48:55', '2025-10-22 03:48:55'),
(48, 'VkPTQGBNikevbtQSTmXzP', '$2y$10$qQTtqXKFHZ7WmFmazX88PO42PGNrGaOHpR8YTQ7qJh5ufNFwu9l8a', 'mesazuja396@gmail.com', 'xcgandTmVEGJGrduvev', 'LoBXUfjgHLCTcpdvIlnaWDOk', 'student', '2025-10-22 05:22:14', NULL, 'active', '2025-10-22 05:22:14', '2025-10-22 05:22:14'),
(49, 'hWNMEgIVhzuQzzVbLdGzVINr', '$2y$10$SFzWDqGay93QCmak5kF3T.EK7NAuFjxfgmW/9ml8CQjOAawmbSi1e', 'vakabosap381@gmail.com', 'byUOeovhIwtgNATCZSiJmDks', 'DCkZJfTjtcODTTWf', 'student', '2025-10-22 19:08:12', NULL, 'active', '2025-10-22 19:08:12', '2025-10-22 19:08:12'),
(50, 'WTvWeUUpJjiGPZAcUk', '$2y$10$jS6uhf4iISihJgYcsIQBReA0B1A.3FN5EOq/vid6Sk9MteW6d5x/m', 'cexetiqeze593@gmail.com', 'WvBNGqCnONjaNgnkml', 'ZfZCXDALacPWglrttjw', 'student', '2025-10-23 13:58:59', NULL, 'active', '2025-10-23 13:58:59', '2025-10-23 13:58:59'),
(51, 'gTTvVNJRpAYDrgKlE', '$2y$10$KhTD8mzsXkhSTPsK6ma0L.UdoyhXk8mmMOmPxlOqvs47Y2dw/rpcq', 'ecimahelivas50@gmail.com', 'UGdshfWdTcZAKspkaay', 'WeUGsMsHPMkLVRvPsh', 'student', '2025-10-26 04:02:06', NULL, 'active', '2025-10-26 04:02:06', '2025-10-26 04:02:06'),
(52, 'akWRyLbjXDKrhdhpw', '$2y$10$ZQozZ73LhmBOKpgNxuogBudzWUE.Wftbop5tfYMQb5P53B5edHEkW', 'ostetho1991@gmail.com', 'zVnsvIfCdawcFfBHvhokpqQe', 'snDeBZNHjkmDZNrAKb', 'student', '2025-10-30 01:50:25', NULL, 'active', '2025-10-30 01:50:25', '2025-10-30 01:50:25'),
(53, 'qnKRgXDSJmfupjeOqCDpI', '$2y$10$yNLzvCCP3/7ItHuXQ1MLVe6sX7nc2V9fWbxYIEb7ZUIhC6c7VxF5u', 'ajadahiwuze854@gmail.com', 'XVhslGuSiELVdCCbhZGP', 'FUsODiPtPbeAuSZV', 'student', '2025-11-02 00:42:53', NULL, 'active', '2025-11-02 00:42:53', '2025-11-02 00:42:53'),
(54, 'hxMWxgvRVqfUdljOVKzuPeaO', '$2y$10$cPuZJKW1O.neFS.8RlhRNOMf5OaJGqP2Mc5tAIboxPmbrBbWmxpqe', 'avuxovodu87@gmail.com', 'cQLLzRFEiTNAeLCZj', 'imGzRqIOKFbjnlmluBZMD', 'student', '2025-11-13 13:04:38', NULL, 'active', '2025-11-13 13:04:38', '2025-11-13 13:04:38'),
(55, 'NAAoOCtyPSDLFxRXiyvom', '$2y$10$4Bjo4Cct8bt5JpQtzOxQpOfsj65Fz./bwsPIPjWBNdFYxMKmyg37K', 'ijogusamudax11@gmail.com', 'qlSiMZSQnSjvTSQkLXO', 'NwuqPNkfWmVjeRxYRINAphr', 'student', '2025-11-17 07:31:29', NULL, 'active', '2025-11-17 07:31:29', '2025-11-17 07:31:29'),
(56, 'yFjdedYhjPMgnlfuvdNk', '$2y$10$op/WlTdLpkcfhsAHIvAEjeim7AhNEM3lV5skIakfINMVMGb0sSrUm', 'ebarawisas77@gmail.com', 'vZisBYmZVImLmUOud', 'tiyEDNOvTAknqfgJegSity', 'student', '2025-11-18 10:25:56', NULL, 'active', '2025-11-18 10:25:56', '2025-11-18 10:25:56'),
(57, 'KOLxySfVjaKLmoer', '$2y$10$bpqSy0ZnsthsUsBj1w2hSuh6zqEtdJj3DxXVTNGsGo31PlgqWqJcW', 'dopugedikib65@gmail.com', 'sSOrGfMeHSQwqEozEU', 'PIOeibZGFWIktccfMi', 'student', '2025-11-20 03:02:58', NULL, 'active', '2025-11-20 03:02:58', '2025-11-20 03:02:58'),
(58, 'NARYTHY1808674NEWETREWT', '$2y$10$RnaSCLWTiebZmRFZYCobb.UVoXAIhow92yB0q6Ps5QAxvWYDPWmXi', 'psuwybzm@vargosmail.com', 'NARYTHY1808674NEWETREWT', 'NARYTHY1808674NEWETREWT', 'student', '2025-11-22 10:45:12', NULL, 'active', '2025-11-22 10:45:12', '2025-11-22 10:45:12'),
(59, 'MqNWRnZtIHMCMhPaN', '$2y$10$qqiLA2XMlx/TOMVP.lz8reFAtz28MS2npRiwXW0WngkA4lzR9S5Aq', 'ocavufo379@gmail.com', 'ohzUYmdZfcvdzzNHJ', 'KKaKXpbRcqDAfFTgohGO', 'student', '2025-12-16 19:30:11', NULL, 'active', '2025-12-16 19:30:11', '2025-12-16 19:30:11');

-- --------------------------------------------------------

--
-- Table structure for table `Videos`
--

CREATE TABLE `Videos` (
  `video_id` int(11) NOT NULL,
  `lesson_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `video_url` varchar(255) NOT NULL,
  `subtitle_url` varchar(255) DEFAULT NULL,
  `duration` time DEFAULT NULL,
  `video_order` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Videos`
--

INSERT INTO `Videos` (`video_id`, `lesson_id`, `title`, `description`, `video_url`, `subtitle_url`, `duration`, `video_order`, `created_at`, `updated_at`) VALUES
(1, 1, 'Understanding HTML Tags and Elements', 'This video explains the core HTML tags like <html>, <head>, <body>, <h1>, <p>, and others. You\'ll learn how to structure content properly and use the essential building blocks of HTML to create a webpage.', 'understanding-html-tags-and-elements.mp4', NULL, NULL, 1, '2024-12-22 05:59:15', '2024-12-22 05:59:15'),
(2, 1, 'Building Your First Web Page', 'This video demonstrates the step-by-step process of building your first simple webpage using basic HTML tags. You will apply the concepts learned from the previous video and create a static web page that includes headings, paragraphs, and images.', 'building-your-first-web-page.mp4', NULL, NULL, 2, '2024-12-22 05:59:15', '2024-12-22 05:59:15'),
(3, 2, 'Creating and Formatting Documents in Word', 'This video walks you through the basics of creating a document in Word, including adding and formatting text, using styles, inserting tables, and setting up headers and footers to enhance document structure and readability.', 'creating-and-formatting-documents-in-word.mp4', NULL, NULL, 1, '2024-12-22 06:23:42', '2024-12-22 06:23:42'),
(4, 3, 'Organizing and Analyzing Data in Excel', 'This video introduces you to Excel\'s data management tools, including entering data, sorting, using basic formulas, and creating charts. It will help you develop the skills needed to organize and analyze data efficiently for work or personal projects.', 'organizing-and-analyzing-data-in-excel.mp4', NULL, NULL, 1, '2024-12-22 06:23:42', '2024-12-22 06:23:42'),
(6, 5, 'Introduction to Python and Programming Basics', '<p>This module provides an introduction to Python and fundamental programming concepts. Students will learn what Python is, why it is widely used, and how to set up their coding environment. They will write their first Python program and get familiar with basic syntax. This module sets the stage for a smooth learning experience.</p>\r\n\r\n<h3>Key Topics:</h3>\r\n<ul>\r\n    <li>Overview of Python and its applications</li>\r\n    <li>Installing Python and setting up an IDE (PyCharm, VS Code, or Jupyter Notebook)</li>\r\n    <li>Writing and running a basic Python script (<code>print(\"Hello, World!\")</code>)</li>\r\n    <li>Understanding the importance of syntax and indentation in Python</li>\r\n</ul>\r\n\r\n<h3>Learning Outcome:</h3>\r\n<p>By the end of this module, students will have a fully set-up Python environment and be able to write and execute their first Python program with confidence.</p>\r\n', 'python/module1.mp4', NULL, NULL, 1, '2025-03-01 18:02:19', '2025-03-09 09:45:00'),
(7, 6, 'Variables, Data Types, and Input/Output', '<p>This module explores how data is stored and manipulated in Python. Students will learn how to declare variables, use different data types, and interact with users through input and output functions. Understanding these concepts is crucial for writing dynamic programs.</p>\r\n\r\n<h3>Key Topics:</h3>\r\n<ul>\r\n    <li>Variables: Assigning and updating values</li>\r\n    <li>Data types: Integers, floats, strings, and booleans</li>\r\n    <li>Type conversion: Converting between different data types</li>\r\n    <li>Taking user input with <code>input()</code> and displaying output with <code>print()</code></li>\r\n</ul>\r\n\r\n<h3>Learning Outcome:</h3>\r\n<p>By the end of this module, students will be able to store, process, and display information using variables and different data types. They will also be comfortable with user input and output operations.</p>\r\n', 'python/module2.mp4', NULL, NULL, 1, '2025-03-01 18:02:19', '2025-03-09 09:50:32'),
(8, 7, '    Learn how to control program flow using conditional statements like <code>if</code>, <code>elif</code>, and <code>else</code>.', '<p>This module teaches students how to control the flow of a program using decision-making statements and loops. By understanding conditionals (<code>if-else</code>) and loops (<code>for</code> and <code>while</code>), students will be able to write dynamic and efficient programs.</p>\r\n\r\n<h3>Key Topics:</h3>\r\n<ul>\r\n    <li>Decision-making using <code>if</code>, <code>elif</code>, and <code>else</code></li>\r\n    <li>Implementing loops: <code>for</code> and <code>while</code></li>\r\n    <li>Loop control statements: <code>break</code>, <code>continue</code>, and <code>pass</code></li>\r\n    <li>Writing programs that respond to user input dynamically</li>\r\n</ul>\r\n\r\n<h3>Learning Outcome:</h3>\r\n<p>By the end of this module, students will be able to build interactive programs that make decisions and repeat actions based on conditions.</p>\r\n', 'python/module3.mp4', NULL, NULL, 1, '2025-03-01 18:02:19', '2025-03-09 09:47:31'),
(9, 8, 'Learn how to define and call functions to write reusable and organized code.', '<p>This module introduces the concept of functions, which allow code to be reused and organized efficiently. Students will learn how to define and call functions, pass arguments, and return values. This helps in writing clean and modular code.</p>\r\n\r\n<h3>Key Topics:</h3>\r\n<ul>\r\n    <li>Defining and calling functions</li>\r\n    <li>Function arguments and return values</li>\r\n    <li>Understanding local and global scope</li>\r\n    <li>Writing reusable code using functions</li>\r\n</ul>\r\n\r\n<h3>Learning Outcome:</h3>\r\n<p>By the end of this module, students will be able to structure their programs efficiently using functions, making their code more readable and maintainable.</p>\r\n', 'python/module4.mp4', NULL, NULL, 1, '2025-03-01 18:02:19', '2025-03-09 09:47:31'),
(10, 9, 'Basic Data Structures – Lists and Dictionaries', '<p>This module covers fundamental data structures that allow programmers to store and manage collections of data efficiently. Students will explore lists and dictionaries, learning how to manipulate and retrieve data from these structures.</p>\r\n\r\n<h3>Key Topics:</h3>\r\n<ul>\r\n    <li>Lists: Creating, modifying, and iterating over lists</li>\r\n    <li>List operations: Indexing, slicing, appending, and sorting</li>\r\n    <li>Dictionaries: Storing and retrieving key-value pairs</li>\r\n    <li>Real-world use cases of lists and dictionaries</li>\r\n</ul>\r\n\r\n<h3>Learning Outcome:</h3>\r\n<p>By the end of this module, students will be able to store, organize, and manipulate data using lists and dictionaries, making their programs more structured and efficient.</p>\r\n', 'python/module5.mp4', NULL, NULL, 1, '2025-03-01 18:02:19', '2025-03-09 09:47:31'),
(11, 10, ' Bringing It All Together', '<p>This final module integrates everything learned so far into a real-world mini-project. Students will combine variables, conditionals, loops, functions, and data structures to build a practical application such as a Budget Tracker or a simple Calculator.</p>\r\n\r\n<h3>Key Topics:</h3>\r\n<ul>\r\n    <li>Structuring a Python project</li>\r\n    <li>Writing reusable and modular code</li>\r\n    <li>Debugging and testing for errors</li>\r\n    <li>Improving the project with additional features</li>\r\n</ul>\r\n\r\n<h3>Learning Outcome:</h3>\r\n<p>By the end of this module, students will be able to apply all Python concepts in a cohesive manner, creating a functional project that demonstrates their learning.</p>\r\n', 'python/module6.mp4', NULL, NULL, 1, '2025-03-01 18:02:19', '2025-03-09 09:47:31');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Accessories`
--
ALTER TABLE `Accessories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `access_requests`
--
ALTER TABLE `access_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `paper_id` (`paper_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `ActivityLog`
--
ALTER TABLE `ActivityLog`
  ADD PRIMARY KEY (`activity_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `AdminSettings`
--
ALTER TABLE `AdminSettings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `Answers`
--
ALTER TABLE `Answers`
  ADD PRIMARY KEY (`answer_id`),
  ADD KEY `question_id` (`question_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `BlogCategories`
--
ALTER TABLE `BlogCategories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `Blogs`
--
ALTER TABLE `Blogs`
  ADD PRIMARY KEY (`blog_id`),
  ADD KEY `author_id` (`author_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `BlogSections`
--
ALTER TABLE `BlogSections`
  ADD PRIMARY KEY (`section_id`),
  ADD KEY `blog_id` (`blog_id`);

--
-- Indexes for table `Careers`
--
ALTER TABLE `Careers`
  ADD PRIMARY KEY (`job_id`);

--
-- Indexes for table `Categories`
--
ALTER TABLE `Categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `Certificates`
--
ALTER TABLE `Certificates`
  ADD PRIMARY KEY (`certificate_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `Courses`
--
ALTER TABLE `Courses`
  ADD PRIMARY KEY (`course_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `uploadedBy_id` (`uploadedBy_id`);

--
-- Indexes for table `Documents`
--
ALTER TABLE `Documents`
  ADD PRIMARY KEY (`document_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `Enrollments`
--
ALTER TABLE `Enrollments`
  ADD PRIMARY KEY (`enrollment_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `EventCategories`
--
ALTER TABLE `EventCategories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `Events`
--
ALTER TABLE `Events`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `organizer_id` (`organizer_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `events_images`
--
ALTER TABLE `events_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `FAQs`
--
ALTER TABLE `FAQs`
  ADD PRIMARY KEY (`faq_id`);

--
-- Indexes for table `job_applications`
--
ALTER TABLE `job_applications`
  ADD PRIMARY KEY (`application_id`),
  ADD KEY `job_id` (`job_id`);

--
-- Indexes for table `Lessons`
--
ALTER TABLE `Lessons`
  ADD PRIMARY KEY (`lesson_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `Logs`
--
ALTER TABLE `Logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `meeting_schedules`
--
ALTER TABLE `meeting_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `Messages`
--
ALTER TABLE `Messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `Notifications`
--
ALTER TABLE `Notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `Questions`
--
ALTER TABLE `Questions`
  ADD PRIMARY KEY (`question_id`),
  ADD KEY `quiz_id` (`quiz_id`);

--
-- Indexes for table `question_papers`
--
ALTER TABLE `question_papers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `Quizzes`
--
ALTER TABLE `Quizzes`
  ADD PRIMARY KEY (`quiz_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `recent_activities`
--
ALTER TABLE `recent_activities`
  ADD PRIMARY KEY (`activity_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `Reviews`
--
ALTER TABLE `Reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `social_links`
--
ALTER TABLE `social_links`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `StaffAssignments`
--
ALTER TABLE `StaffAssignments`
  ADD PRIMARY KEY (`assignment_id`),
  ADD KEY `staff_id` (`staff_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `StudentAnswers`
--
ALTER TABLE `StudentAnswers`
  ADD PRIMARY KEY (`answer_id`),
  ADD KEY `question_id` (`question_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `StudentQuestions`
--
ALTER TABLE `StudentQuestions`
  ADD PRIMARY KEY (`question_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `Transactions`
--
ALTER TABLE `Transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `UserProgress`
--
ALTER TABLE `UserProgress`
  ADD PRIMARY KEY (`progress_id`),
  ADD UNIQUE KEY `unique_progress` (`user_id`,`course_id`,`lesson_id`,`video_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `lesson_id` (`lesson_id`),
  ADD KEY `video_id` (`video_id`);

--
-- Indexes for table `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `Videos`
--
ALTER TABLE `Videos`
  ADD PRIMARY KEY (`video_id`),
  ADD KEY `lesson_id` (`lesson_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Accessories`
--
ALTER TABLE `Accessories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `access_requests`
--
ALTER TABLE `access_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `ActivityLog`
--
ALTER TABLE `ActivityLog`
  MODIFY `activity_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `AdminSettings`
--
ALTER TABLE `AdminSettings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `Answers`
--
ALTER TABLE `Answers`
  MODIFY `answer_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `BlogCategories`
--
ALTER TABLE `BlogCategories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `Blogs`
--
ALTER TABLE `Blogs`
  MODIFY `blog_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `BlogSections`
--
ALTER TABLE `BlogSections`
  MODIFY `section_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Careers`
--
ALTER TABLE `Careers`
  MODIFY `job_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `Categories`
--
ALTER TABLE `Categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `Certificates`
--
ALTER TABLE `Certificates`
  MODIFY `certificate_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `Courses`
--
ALTER TABLE `Courses`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `Documents`
--
ALTER TABLE `Documents`
  MODIFY `document_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Enrollments`
--
ALTER TABLE `Enrollments`
  MODIFY `enrollment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `EventCategories`
--
ALTER TABLE `EventCategories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `Events`
--
ALTER TABLE `Events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `events_images`
--
ALTER TABLE `events_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `FAQs`
--
ALTER TABLE `FAQs`
  MODIFY `faq_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `job_applications`
--
ALTER TABLE `job_applications`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `Lessons`
--
ALTER TABLE `Lessons`
  MODIFY `lesson_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `Logs`
--
ALTER TABLE `Logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `meeting_schedules`
--
ALTER TABLE `meeting_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Messages`
--
ALTER TABLE `Messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `Notifications`
--
ALTER TABLE `Notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Questions`
--
ALTER TABLE `Questions`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `question_papers`
--
ALTER TABLE `question_papers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `Quizzes`
--
ALTER TABLE `Quizzes`
  MODIFY `quiz_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `recent_activities`
--
ALTER TABLE `recent_activities`
  MODIFY `activity_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Reviews`
--
ALTER TABLE `Reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `social_links`
--
ALTER TABLE `social_links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `StaffAssignments`
--
ALTER TABLE `StaffAssignments`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `StudentAnswers`
--
ALTER TABLE `StudentAnswers`
  MODIFY `answer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `StudentQuestions`
--
ALTER TABLE `StudentQuestions`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `Transactions`
--
ALTER TABLE `Transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `UserProgress`
--
ALTER TABLE `UserProgress`
  MODIFY `progress_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `Users`
--
ALTER TABLE `Users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `Videos`
--
ALTER TABLE `Videos`
  MODIFY `video_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `access_requests`
--
ALTER TABLE `access_requests`
  ADD CONSTRAINT `access_requests_ibfk_1` FOREIGN KEY (`paper_id`) REFERENCES `question_papers` (`id`),
  ADD CONSTRAINT `access_requests_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`);

--
-- Constraints for table `ActivityLog`
--
ALTER TABLE `ActivityLog`
  ADD CONSTRAINT `ActivityLog_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `Answers`
--
ALTER TABLE `Answers`
  ADD CONSTRAINT `Answers_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `Questions` (`question_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `Answers_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `Blogs`
--
ALTER TABLE `Blogs`
  ADD CONSTRAINT `Blogs_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `Users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `Blogs_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `BlogCategories` (`category_id`) ON DELETE SET NULL;

--
-- Constraints for table `BlogSections`
--
ALTER TABLE `BlogSections`
  ADD CONSTRAINT `BlogSections_ibfk_1` FOREIGN KEY (`blog_id`) REFERENCES `Blogs` (`blog_id`) ON DELETE CASCADE;

--
-- Constraints for table `Certificates`
--
ALTER TABLE `Certificates`
  ADD CONSTRAINT `Certificates_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `Certificates_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `Courses` (`course_id`) ON DELETE CASCADE;

--
-- Constraints for table `Courses`
--
ALTER TABLE `Courses`
  ADD CONSTRAINT `Courses_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `Courses_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `Categories` (`category_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `Courses_ibfk_3` FOREIGN KEY (`uploadedBy_id`) REFERENCES `Users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `Documents`
--
ALTER TABLE `Documents`
  ADD CONSTRAINT `Documents_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `Enrollments`
--
ALTER TABLE `Enrollments`
  ADD CONSTRAINT `Enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `Enrollments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `Courses` (`course_id`) ON DELETE CASCADE;

--
-- Constraints for table `Events`
--
ALTER TABLE `Events`
  ADD CONSTRAINT `Events_ibfk_1` FOREIGN KEY (`organizer_id`) REFERENCES `Users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `Events_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `EventCategories` (`category_id`) ON DELETE SET NULL;

--
-- Constraints for table `events_images`
--
ALTER TABLE `events_images`
  ADD CONSTRAINT `events_images_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `Events` (`event_id`) ON DELETE CASCADE;

--
-- Constraints for table `job_applications`
--
ALTER TABLE `job_applications`
  ADD CONSTRAINT `job_applications_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `Careers` (`job_id`);

--
-- Constraints for table `Lessons`
--
ALTER TABLE `Lessons`
  ADD CONSTRAINT `Lessons_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `Courses` (`course_id`) ON DELETE CASCADE;

--
-- Constraints for table `Logs`
--
ALTER TABLE `Logs`
  ADD CONSTRAINT `Logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `meeting_schedules`
--
ALTER TABLE `meeting_schedules`
  ADD CONSTRAINT `meeting_schedules_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `meeting_schedules_ibfk_2` FOREIGN KEY (`staff_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `Messages`
--
ALTER TABLE `Messages`
  ADD CONSTRAINT `Messages_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `Users` (`user_id`);

--
-- Constraints for table `Notifications`
--
ALTER TABLE `Notifications`
  ADD CONSTRAINT `Notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `Questions`
--
ALTER TABLE `Questions`
  ADD CONSTRAINT `Questions_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `Quizzes` (`quiz_id`) ON DELETE CASCADE;

--
-- Constraints for table `question_papers`
--
ALTER TABLE `question_papers`
  ADD CONSTRAINT `question_papers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `Quizzes`
--
ALTER TABLE `Quizzes`
  ADD CONSTRAINT `Quizzes_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `Courses` (`course_id`) ON DELETE CASCADE;

--
-- Constraints for table `recent_activities`
--
ALTER TABLE `recent_activities`
  ADD CONSTRAINT `recent_activities_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `Reviews`
--
ALTER TABLE `Reviews`
  ADD CONSTRAINT `Reviews_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `Reviews_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `Courses` (`course_id`) ON DELETE CASCADE;

--
-- Constraints for table `StaffAssignments`
--
ALTER TABLE `StaffAssignments`
  ADD CONSTRAINT `StaffAssignments_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `StaffAssignments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `Courses` (`course_id`) ON DELETE CASCADE;

--
-- Constraints for table `StudentAnswers`
--
ALTER TABLE `StudentAnswers`
  ADD CONSTRAINT `StudentAnswers_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `StudentQuestions` (`question_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `StudentAnswers_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `StudentQuestions`
--
ALTER TABLE `StudentQuestions`
  ADD CONSTRAINT `StudentQuestions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `Transactions`
--
ALTER TABLE `Transactions`
  ADD CONSTRAINT `Transactions_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `Transactions_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `Courses` (`course_id`) ON DELETE CASCADE;

--
-- Constraints for table `UserProgress`
--
ALTER TABLE `UserProgress`
  ADD CONSTRAINT `UserProgress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `UserProgress_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `Courses` (`course_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `UserProgress_ibfk_3` FOREIGN KEY (`lesson_id`) REFERENCES `Lessons` (`lesson_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `UserProgress_ibfk_4` FOREIGN KEY (`video_id`) REFERENCES `Videos` (`video_id`) ON DELETE CASCADE;

--
-- Constraints for table `Videos`
--
ALTER TABLE `Videos`
  ADD CONSTRAINT `Videos_ibfk_1` FOREIGN KEY (`lesson_id`) REFERENCES `Lessons` (`lesson_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
