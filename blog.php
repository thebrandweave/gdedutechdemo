<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once './Configurations/config.php';

// Load categories for category section and optional filtering
$selectedCategoryId = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 6;
$offset = ($page - 1) * $limit;
$blogCategories = [];
$catRes = $conn->query("SELECT category_id, name FROM BlogCategories ORDER BY name ASC");
if ($catRes) {
    while ($c = $catRes->fetch_assoc()) { $blogCategories[] = $c; }
}

// Helper to resolve blog image URL from stored value
function resolveBlogImageUrl($value) {
    if (!$value) {
        return '';
    }
    // Absolute URL
    if (preg_match('/^https?:\/\//i', $value)) {
        return $value;
    }
    // Already a root-relative or includes uploads path
    if (substr($value, 0, 1) === '/') {
        return $value;
    }
    if (strpos($value, 'uploads/') === 0 || strpos($value, './uploads/') === 0) {
        return './' . ltrim($value, './');
    }
    // Default: filename saved by admin goes into uploads/blogs/
    return './uploads/blogs/' . ltrim($value, '/');
}

// Fetch published blog posts
$blog_query = "SELECT b.*, CONCAT(u.first_name, ' ', u.last_name) as author_name ";
$blog_query .= "FROM Blogs b ";
$blog_query .= "LEFT JOIN Users u ON b.author_id = u.user_id ";
$where = "WHERE b.status = 'published'";
if ($selectedCategoryId > 0) {
    $where .= " AND b.category_id = " . intval($selectedCategoryId);
}
$blog_query .= $where . " ORDER BY b.created_at DESC LIMIT " . intval($limit) . " OFFSET " . intval($offset);

// total count for pagination
$count_query = "SELECT COUNT(*) as total FROM Blogs b " . $where;
$totalBlogs = 0;
$count_res = $conn->query($count_query);
if ($count_res && $count_res->num_rows) {
    $totalBlogs = intval(($count_res->fetch_assoc())['total']);
}

$blog_result = $conn->query($blog_query);
$blogs = [];
if ($blog_result && $blog_result->num_rows > 0) {
    while ($row = $blog_result->fetch_assoc()) {
        $blogs[] = $row;
    }
}

// If AJAX request to load more cards, return only cards markup and exit
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    if (!empty($blogs)) {
        foreach ($blogs as $index => $blog) {
            ?>
            <div class="col-lg-4 col-md-6 col-12" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                <div class="card blog-post-card h-100 shadow-sm border-0 position-relative">
                    <?php if (!empty($blog['main_cover_image'])): ?>
                        <img src="<?php echo htmlspecialchars(resolveBlogImageUrl($blog['main_cover_image'])); ?>" 
                             class="card-img-top" 
                             alt="<?php echo htmlspecialchars($blog['title']); ?>"
                             style="height: 200px; object-fit: cover;">
                    <?php else: ?>
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                             style="height: 200px;">
                            <i class="bi bi-file-text display-4 text-muted"></i>
                        </div>
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center mb-2">
                            <small class="text-muted">
                                <i class="bi bi-calendar3 me-1"></i>
                                <?php echo date('M d, Y', strtotime($blog['created_at'])); ?>
                            </small>
                            <?php if (!empty($blog['author_name'])): ?>
                                <small class="text-muted ms-3">
                                    <i class="bi bi-person me-1"></i>
                                    <?php echo htmlspecialchars($blog['author_name']); ?>
                                </small>
                            <?php endif; ?>
                        </div>
                        <a href="blog-details.php?blog_id=<?php echo intval($blog['blog_id']); ?>" class="stretched-link" aria-label="Read <?php echo htmlspecialchars($blog['title']); ?>"></a>
                        <h5 class="card-title mb-3">
                            <a href="blog-details.php?blog_id=<?php echo intval($blog['blog_id']); ?>" class="text-decoration-none text-dark text-clamp-2">
                                <?php echo htmlspecialchars($blog['title']); ?>
                            </a>
                        </h5>
                        <p class="card-text text-muted flex-grow-1 text-clamp-3">
                            <?php 
                            $content = strip_tags($blog['content']);
                            echo $content;
                            ?>
                        </p>
                        <?php 
                            $links = $blogIdToSocialLinks[intval($blog['blog_id'])] ?? [];
                            if (!empty($links)):
                        ?>
                            <div class="mt-2 pt-2 border-top d-flex align-items-center gap-3">
                                <?php foreach ($links as $lnk): 
                                    $icon = getSocialIconClass($lnk['platform']);
                                    $url = $lnk['url'];
                                    if (!$url) { continue; }
                                ?>
                                    <a href="<?php echo htmlspecialchars($url); ?>" target="_blank" rel="noopener" class="text-muted" aria-label="<?php echo htmlspecialchars($lnk['platform']); ?> link">
                                        <i class="<?php echo htmlspecialchars($icon); ?>"></i>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php
        }
    }
    exit;
}

// Map of blog_id => array of social links [{platform, url}, ...]
$blogIdToSocialLinks = [];
if (!empty($blogs)) {
    $blogIds = [];
    foreach ($blogs as $b) {
        if (isset($b['blog_id'])) { $blogIds[] = intval($b['blog_id']); }
    }
    $blogIds = array_values(array_unique(array_filter($blogIds)));
    if (!empty($blogIds)) {
        $idsList = implode(',', $blogIds);
        $slq = "SELECT target_id, platform, url FROM social_links WHERE target_type = 'blog' AND target_id IN ($idsList)";
        $slres = $conn->query($slq);
        if ($slres && $slres->num_rows) {
            while ($row = $slres->fetch_assoc()) {
                $tid = intval($row['target_id']);
                if (!isset($blogIdToSocialLinks[$tid])) { $blogIdToSocialLinks[$tid] = []; }
                $blogIdToSocialLinks[$tid][] = [
                    'platform' => strtolower(trim($row['platform'] ?? '')),
                    'url' => $row['url'] ?? ''
                ];
            }
        }
    }
}

// Helper to derive icon class for a platform (Font Awesome Brands)
function getSocialIconClass($platform) {
    $p = strtolower(trim((string)$platform));
    $map = [
        'facebook' => 'fa-brands fa-facebook',
        'instagram' => 'fa-brands fa-instagram',
        'twitter' => 'fa-brands fa-x-twitter',
        'x' => 'fa-brands fa-x-twitter',
        'linkedin' => 'fa-brands fa-linkedin',
        'youtube' => 'fa-brands fa-youtube',
        'github' => 'fa-brands fa-github',
        'website' => 'fa-solid fa-globe',
        'site' => 'fa-solid fa-globe',
        'web' => 'fa-solid fa-globe'
    ];
    return $map[$p] ?? 'fa-solid fa-link';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - GD Edu Tech</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AOS Animation Library -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="./css/style.css">
       <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
        
    <link rel="icon" type="image/png" href="./Images/Logos/GD_Only_logo.png">
    <link rel="icon" type="image/png" sizes="32x32" href="./Images/Logos/GD_Only_logo.png">
    <link rel="icon" type="image/png" sizes="16x16" href="./Images/Logos/GD_Only_logo.png">
    <link rel="shortcut icon" href="./Images/Logos/GD_Only_logo.png">
    <link rel="apple-touch-icon" href="./Images/Logos/GD_Only_logo.png">
    <meta name="msapplication-TileImage" content="./Images/Logos/GD_Only_logo.png">
    <style>
        /* Enhanced mobile responsiveness for blog.php */
        :root {
            --mobile-padding: 1rem;
            --card-radius: 16px;
            --transition-speed: 0.3s;
        }

        .premium-feature-card .card-icon i {
            color: #0d7298 !important; 
        }

        /* General Mobile Improvements */
        @media (max-width: 991.98px) {
            .container {
                padding-left: var(--mobile-padding);
                padding-right: var(--mobile-padding);
            }

            .page-header .display-5 {
                font-size: 2.2rem;
                line-height: 1.3;
            }

            .premium-card {
                padding: 1.5rem !important;
                border-radius: var(--card-radius);
                transition: transform var(--transition-speed) ease;
            }

            .premium-card:hover {
                transform: translateY(-5px);
            }

            .section-heading {
                font-size: 1.8rem;
                margin-bottom: 1rem;
            }

            .lead {
                font-size: 1.1rem;
                line-height: 1.6;
            }
        }

        @media (max-width: 767.98px) {
            /* Header Improvements */
            .page-header {
                padding: 100px 0 40px !important;
                min-height: auto !important;
            }

            .page-header .display-5 {
                font-size: 1.8rem;
            }

            .page-header .lead {
                font-size: 1rem;
            }

            /* Card Improvements */
            .premium-card {
                flex-direction: column !important;
                padding: 1.25rem !important;
                margin-bottom: 1.5rem;
            }

            .premium-card img {
                margin-bottom: 1.25rem !important;
                border-radius: calc(var(--card-radius) - 4px);
            }

            .premium-card .card-title {
                font-size: 1.4rem;
                margin-bottom: 0.75rem;
            }

            /* Button Improvements */
            .btn-group {
                display: flex;
                width: 100%;
                margin-top: 1rem;
            }

            .btn-group .btn {
                flex: 1;
                font-size: 0.95rem;
                padding: 0.75rem 1rem;
                white-space: nowrap;
            }

            /* Newsletter Improvements */
            .newsletter-section {
                padding: 3rem 0;
            }

            .newsletter-section .input-group {
                flex-direction: column;
                gap: 1rem;
            }

            .newsletter-section .form-control {
                height: 3.5rem;
                font-size: 1rem;
                border-radius: 50px;
                padding: 0 1.5rem;
            }

            .newsletter-section .btn {
                height: 3.5rem;
                border-radius: 50px;
                font-size: 1rem;
                padding: 0 2rem;
            }

            /* Category Cards Improvements */
            .premium-feature-card {
                padding: 1.5rem;
                margin-bottom: 1rem;
            }

            .premium-feature-card .card-icon {
                width: 70px;
                height: 70px;
                margin-bottom: 1.25rem;
            }

            .premium-feature-card h3 {
                font-size: 1.3rem;
                margin-bottom: 0.75rem;
            }


            /* Author Info Improvements */
            .author-info {
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }

            .author-info img {
                width: 40px;
                height: 40px;
            }

            .author-info small {
                font-size: 0.85rem;
            }

            /* Blog Post Card Improvements */
            .blog-post-card {
                height: 100%;
                display: flex;
                flex-direction: column;
            }

            .blog-post-card .card-body {
                flex: 1;
                display: flex;
                flex-direction: column;
            }

            .blog-post-card .card-text {
                flex: 1;
                margin-bottom: 1rem;
            }

            /* Touch-friendly improvements */
            .btn, 
            .nav-link,
            .feature-link {
                min-height: 44px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }

            /* Image optimizations */
            img {
                max-width: 100%;
                height: auto;
                object-fit: cover;
            }

            /* Spacing improvements */
            .section-padding {
                padding: 3rem 0;
            }

            .mb-mobile {
                margin-bottom: 1.5rem;
            }
        }

        /* Animation improvements */
        @media (prefers-reduced-motion: no-preference) {
            .premium-card,
            .premium-feature-card,
            .btn {
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }

            .premium-card:hover,
            .premium-feature-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            }
        }

        /* Button and Form Improvements */
        .btn-view-more {
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--primary);
            color: white;
            border: none;
            box-shadow: 0 4px 15px rgba(13, 114, 152, 0.2);
        }

        .btn-view-more:hover {
            background: var(--primary) !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(13, 114, 152, 0.3);
            color: white;
        }

        .newsletter-section .form-control {
            height: 3.5rem;
            border-radius: 50px;
            padding: 0 1.5rem;
            font-size: 1rem;
            border: 2px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.1);
            color: white;
            backdrop-filter: blur(10px);
        }

        .newsletter-section .form-control::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .newsletter-section .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.3);
            box-shadow: none;
            color: white;
        }

        .newsletter-section .btn-subscribe {
            height: 3.5rem;
            border-radius: 50px;
            padding: 0 2rem;
            font-size: 1rem;
            font-weight: 600;
            background: white;
            color: var(--primary);
            border: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .newsletter-section .btn-subscribe:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            background: white;
            color: var(--primary);
        }

        /* Newsletter Section Mobile Fixes */
        @media (max-width: 767.98px) {
            .newsletter-section .input-group {
                flex-direction: column;
                gap: 1rem;
                width: 100%;
            }

            .newsletter-section .form-control {
                width: 100%;
                margin-bottom: 0.5rem;
                height: 3.5rem;
                border-radius: 50px;
                padding: 0 1.5rem;
                font-size: 1rem;
                border: 2px solid rgba(255, 255, 255, 0.2);
                background: rgba(255, 255, 255, 0.1);
                color: white;
            }

            .newsletter-section .btn-subscribe {
                width: 100%;
                height: 3.5rem;
                border-radius: 50px;
                padding: 0 2rem;
                font-size: 1rem;
                font-weight: 600;
                background: white;
                color: var(--primary);
                border: none;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
            }

            .newsletter-section .form-control::placeholder {
                color: rgba(255, 255, 255, 0.7);
            }

            .newsletter-section .form-control:focus {
                background: rgba(255, 255, 255, 0.15);
                border-color: rgba(255, 255, 255, 0.3);
                box-shadow: none;
                color: white;
            }
        }

        /* Testimonial CTA Section */
        .testimonial-cta {
            width: 100%;
            height: 20%;
            background-color: var(--primary);
        }

        .testimonial-cta h3 {
            font-size: 4rem;
        }

        @media (max-width: 767.98px) {
            .testimonial-cta h3 {
                font-size: 2.5rem;
            }
        }

        /* Add custom styles for the blog image */
        .blog-hero-image {
            max-width: 100%;
            height: auto;
            max-height: 400px;
            object-fit: contain;
            animation: float 6s ease-in-out infinite;
            transition: transform 0.3s ease;
            box-shadow: none;
            border: none;
        }

        @keyframes float {
            0% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-15px);
            }
            100% {
                transform: translateY(0px);
            }
        }

        .blog-hero-image:hover::after {
            opacity: 1;
        }

        /* Blog Post Card Styles */
        .blog-post-card {
            transition: all 0.3s ease;
            border-radius: 12px;
            overflow: hidden;
        }

        .blog-post-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1) !important;
        }

        .blog-post-card .card-img-top {
            transition: transform 0.3s ease;
        }

        .blog-post-card:hover .card-img-top {
            transform: scale(1.05);
        }

        .blog-post-card .card-title a:hover {
            color: var(--primary) !important;
        }

        .blog-post-card .btn-outline-primary:hover {
            background-color: var(--primary);
            border-color: var(--primary);
            color: white;
        }
        /* Line clamp utilities for consistent text lines */
        .text-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; line-clamp: 1; }
        .text-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-clamp: 2; }
        .text-clamp-3 { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; line-clamp: 3; }
        /* Category chips horizontal scroll */
        .categories-section {
            overflow-x: auto;
            white-space: nowrap;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
        }
        .categories-section a.btn.btn-sm.rounded-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            vertical-align: middle;
        }
        .categories-section {
            background: none !important;
            margin-bottom: -5em !important;
            margin-top: -6em !important;
        }

        /* Mobile optimizations for blog cards */
        @media (max-width: 767.98px) {
            .blog-post-card {
                margin-bottom: 1.5rem;
            }
            
            .blog-post-card .card-img-top {
                height: 180px !important;
            }
            /* Smaller chips on mobile */
            .categories-section {
                margin-bottom: -2em !important;
                margin-top: -4em !important;
            }
            .categories-section .btn.btn-sm.rounded-pill {
                padding: 0.25rem 0.6rem;
                font-size: 0.8rem;
                line-height: 1.1;
                border-radius: 999px;
                margin-left: 0.5rem;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }
        }

    </style>
</head>

<body>
    <?php include 'navbar.php'; ?>

    <!-- Page Header -->
    <section class="page-header position-relative overflow-hidden">
        <div class="container position-relative py-7">
            <div class="row align-items-center">
                <div class="col-md-7 col-12 mb-4 mb-md-0" data-aos="fade-right">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-3">
                            <li class="breadcrumb-item"><a href="index.php" class="text-white-50">Home</a></li>
                            <li class="breadcrumb-item active text-white" aria-current="page">Blog</li>
                        </ol>
                    </nav>
                    <h1 class="text-white display-5 fw-bold mb-3">Educational Insights</h1>
                    <p class="text-white-50 lead mb-0">Discover the latest trends, tips, and insights in education and technology.</p>
                </div>
                <div class="col-md-5 col-12 text-center" data-aos="fade-left">
                    <!-- <img src="./Images/Others/blog.png" alt="Blog" class="blog-hero-image"> -->
                </div>
            </div>
        </div>
        <div class="page-header-shape">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
                <path fill="#ffffff" fill-opacity="1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,112C672,96,768,96,864,112C960,128,1056,160,1152,160C1248,160,1344,128,1392,112L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
            </svg>
        </div>
    </section>

    

    <!-- Latest Posts -->
    <section class="py-5 section-padding">
        <div class="container">
            <div class="row mb-5">
                <div class="col-lg-6 col-12">
                    <h2 class="section-heading" data-aos="fade-up">Latest Blogs</h2>
                    <p class="lead text-muted" data-aos="fade-up" data-aos-delay="100">Stay updated with our newest content</p>
                </div>
            </div>
            <div class="row">
                <div class="col-12 categories-section mb-5" data-aos="fade-up">
                    <?php if (!empty($blogCategories)): ?>
                        <?php 
                            $allActive = $selectedCategoryId === 0 ? 'btn-primary text-white' : 'btn-outline-primary';
                        ?>
                        <a href="blog.php" class="btn btn-sm rounded-pill me-2 mb-3 <?php echo $allActive; ?>">All</a>
                        <?php foreach ($blogCategories as $cat): 
                            $isActive = ($selectedCategoryId === intval($cat['category_id'])) ? 'btn-primary text-white' : 'btn-outline-primary';
                        ?>
                            <a href="blog.php?category_id=<?php echo intval($cat['category_id']); ?>" class="btn btn-sm rounded-pill me-2 mb-3 <?php echo $isActive; ?>">
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="row g-4">
                <?php if (empty($blogs)): ?>
                    <div class="col-12 text-center" data-aos="fade-up">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            No blog posts available at the moment. Please check back later.
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($blogs as $index => $blog): ?>
                        <div class="col-lg-4 col-md-6 col-12" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                            <div class="card blog-post-card h-100 shadow-sm border-0 position-relative">
                                <?php if (!empty($blog['main_cover_image'])): ?>
                                    <img src="<?php echo htmlspecialchars(resolveBlogImageUrl($blog['main_cover_image'])); ?>" 
                                         class="card-img-top" 
                                         alt="<?php echo htmlspecialchars($blog['title']); ?>"
                                         style="height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                         style="height: 200px;">
                                        <i class="bi bi-file-text display-4 text-muted"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex align-items-center mb-2">
                                        <small class="text-muted">
                                            <i class="bi bi-calendar3 me-1"></i>
                                            <?php echo date('M d, Y', strtotime($blog['created_at'])); ?>
                                        </small>
                                        <?php if (!empty($blog['author_name'])): ?>
                                            <small class="text-muted ms-3">
                                                <i class="bi bi-person me-1"></i>
                                                <?php echo htmlspecialchars($blog['author_name']); ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                    <a href="blog-details.php?blog_id=<?php echo intval($blog['blog_id']); ?>" class="stretched-link" aria-label="Read <?php echo htmlspecialchars($blog['title']); ?>"></a>
                                    <h5 class="card-title mb-3">
                                        <a href="blog-details.php?blog_id=<?php echo intval($blog['blog_id']); ?>" class="text-decoration-none text-dark text-clamp-2">
                                            <?php echo htmlspecialchars($blog['title']); ?>
                                        </a>
                                    </h5>
                                    
                                    <p class="card-text text-muted flex-grow-1 text-clamp-3">
                                        <?php 
                                        $content = strip_tags($blog['content']);
                                        echo $content;
                                        ?>
                                    </p>
                                    <?php 
                                        $links = $blogIdToSocialLinks[intval($blog['blog_id'])] ?? [];
                                        if (!empty($links)):
                                    ?>
                                        <div class="mt-2 pt-2 border-top d-flex align-items-center gap-3">
                                            <?php foreach ($links as $lnk): 
                                                $icon = getSocialIconClass($lnk['platform']);
                                                $url = $lnk['url'];
                                                if (!$url) { continue; }
                                            ?>
                                                <a href="<?php echo htmlspecialchars($url); ?>" target="_blank" rel="noopener" class="text-muted" aria-label="<?php echo htmlspecialchars($lnk['platform']); ?> link">
                                                    <i class="<?php echo htmlspecialchars($icon); ?>"></i>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <?php 
                $hasMore = ($offset + count($blogs)) < $totalBlogs; 
                $nextPage = $page + 1; 
                $qs = http_build_query(array_filter([
                    'category_id' => $selectedCategoryId ?: null,
                    'page' => $nextPage
                ]));
            ?>
            <?php if ($hasMore): ?>
                <div class="text-center mt-5" data-aos="fade-up">
                    <button id="loadMoreBlogs" class="btn btn-view-more" data-next-page="<?php echo $nextPage; ?>" data-total="<?php echo intval($totalBlogs); ?>">
                        View More
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="cta-section newsletter-section" data-aos="fade-up">
        <div class="floating-shape shape-1"></div>
        <div class="floating-shape shape-2"></div>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8 col-12 mx-auto text-center">
                    <h2 class="display-5 fw-bold text-white mb-4">Subscribe to Our Newsletter</h2>
                    <p class="lead text-white-50 mb-5">Get the latest blogs, tips, and insights delivered directly to your inbox.</p>
                    <form class="row justify-content-center">
                        <div class="col-md-8 col-12">
                            <div class="input-group input-group-lg flex-column flex-md-row">
                                <input type="email" class="form-control" placeholder="Enter your email">
                                <button class="btn btn-subscribe" type="submit">
                                    Subscribe <i class="bi bi-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Include footer -->
    <?php include 'footer.php'; ?>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <!-- Initialize AOS and other scripts -->
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            easing: 'ease-in-out',
            once: true,
            mirror: false
        });

        // Load more blogs inline without page reload
        (function(){
            const loadBtn = document.getElementById('loadMoreBlogs');
            if (!loadBtn) return;
            const grid = document.querySelector('.row.g-4');
            const shownCountEl = document.getElementById('shownCount');
            const countInfo = document.getElementById('blogsCountInfo');
            let nextPage = parseInt(loadBtn.getAttribute('data-next-page') || '2', 10);
            const total = parseInt(loadBtn.getAttribute('data-total') || '0', 10);
            let loading = false;

            loadBtn.addEventListener('click', async function(){
                if (loading) return; loading = true;
                loadBtn.disabled = true; loadBtn.innerHTML = 'Loading...';
                try {
                    const params = new URLSearchParams(window.location.search);
                    params.set('page', String(nextPage));
                    params.set('ajax', '1');
                    const res = await fetch('blog.php?' + params.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
                    const html = await res.text();
                    const temp = document.createElement('div');
                    temp.innerHTML = html.trim();
                    const newCards = temp.children;
                    if (newCards.length === 0) {
                        loadBtn.remove();
                        return;
                    }
                    while (temp.firstChild) {
                        grid.appendChild(temp.firstChild);
                    }
                    // Re-init AOS for newly added nodes
                    if (window.AOS) { AOS.refreshHard(); }
                    // Update shown count
                    const currentlyShown = grid.querySelectorAll('.col-lg-4.col-md-6.col-12').length;
                    if (shownCountEl) { shownCountEl.textContent = String(currentlyShown); }
                    nextPage += 1;
                    loadBtn.setAttribute('data-next-page', String(nextPage));
                    if (total && currentlyShown >= total) {
                        loadBtn.remove();
                    }
                } catch (e) {
                    console.error(e);
                } finally {
                    loadBtn.disabled = false; loadBtn.innerHTML = 'View More <i class="bi bi-arrow-right"></i>';
                    loading = false;
                }
            });
        })();
    </script>
</body>

</html> 