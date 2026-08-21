<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once './Configurations/config.php';

$eventId = isset($_GET['event_id']) ? intval($_GET['event_id']) : (isset($_GET['id']) ? intval($_GET['id']) : 0);
if ($eventId <= 0) {
    header("Location: events.php");
    exit();
}

function resolveEventImageUrl($value) {
    if (!$value) { return ''; }
    if (preg_match('/^https?:\/\//i', $value)) { return $value; }
    if (substr($value, 0, 1) === '/') { return $value; }
    if (strpos($value, 'uploads/') === 0 || strpos($value, './uploads/') === 0) {
        return './' . ltrim($value, './');
    }
    return './uploads/events/' . ltrim($value, '/');
}

$event = null;
if ($eventId > 0) {
    $q = $conn->prepare("SELECT * FROM Events WHERE event_id = ? AND status IN ('upcoming','ongoing','completed')");
    $q->bind_param('i', $eventId);
    $q->execute();
    $res = $q->get_result();
    $event = $res ? $res->fetch_assoc() : null;
}

if (!$event) {
    header("Location: events.php");
    exit();
}

// Load social links for this event
$eventSocialLinks = [];
if ($event) {
    $sl = $conn->prepare("SELECT platform, url FROM social_links WHERE target_type = 'event' AND target_id = ?");
    $sl->bind_param('i', $eventId);
    $sl->execute();
    $slr = $sl->get_result();
    if ($slr) {
        while ($row = $slr->fetch_assoc()) { $eventSocialLinks[] = $row; }
    }
}

// Load additional event images (gallery)
$eventImages = [];
if ($event) {
    $imgq = $conn->prepare("SELECT image_url FROM events_images WHERE event_id = ? ORDER BY id ASC");
    $imgq->bind_param('i', $eventId);
    $imgq->execute();
    $imgr = $imgq->get_result();
    if ($imgr) {
        while ($row = $imgr->fetch_assoc()) { $eventImages[] = $row['image_url']; }
    }
}

function getSocialIconClass($platform) {
    $p = strtolower(trim((string)$platform));
    $map = [
        'facebook'  => 'fa-brands fa-facebook-f',
        'instagram' => 'fa-brands fa-instagram',
        'twitter'   => 'fa-brands fa-x-twitter',
        'x'         => 'fa-brands fa-x-twitter',
        'linkedin'  => 'fa-brands fa-linkedin-in',
        'youtube'   => 'fa-brands fa-youtube',
        'github'    => 'fa-brands fa-github',
        'website'   => 'fa-solid fa-globe',
        'site'      => 'fa-solid fa-globe',
        'web'       => 'fa-solid fa-globe'
    ];
    return $map[$p] ?? 'fa-solid fa-link';
}

// Status Badge Helper
$status_badge_class = 'bg-primary';
if ($event['status'] === 'upcoming') {
    $status_badge_class = 'bg-success';
} elseif ($event['status'] === 'ongoing') {
    $status_badge_class = 'bg-warning text-dark';
} elseif ($event['status'] === 'completed') {
    $status_badge_class = 'bg-secondary';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($event['title']); ?> - Events - GD Edu Tech</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AOS Animation -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="./css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="./Images/Logos/GD_Only_logo.png">

    <style>
        /* Explicit Link Text-Decoration Reset (Fixes Navbar & Footer Underlines) */
        a, 
        .nav-link, 
        .navbar-brand, 
        .dropdown-item, 
        .breadcrumb-item a, 
        footer a, 
        .footer a, 
        .site-footer a {
            text-decoration: none !important;
        }

        body {
            background: #f8fafc;
            color: #0f172a;
            font-family: 'Poppins', sans-serif;
        }

        /* Glassmorphic Event Card */
        .event-detail-card {
            background: #ffffff;
            border-radius: 24px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 15px 35px -10px rgba(15, 23, 42, 0.08);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .event-cover-wrap {
            position: relative;
            border-radius: 24px;
            overflow: hidden;
            background: #0f172a;
            box-shadow: 0 15px 40px -10px rgba(15, 23, 42, 0.15);
            max-height: 480px;
        }

        .event-cover-img {
            width: 100%;
            height: 100%;
            max-height: 480px;
            object-fit: cover;
            display: block;
        }

        .info-pill-item {
            background: rgba(13, 114, 152, 0.05);
            border: 1px solid rgba(13, 114, 152, 0.14);
            border-radius: 16px;
            padding: 14px 18px;
        }

        .info-icon-box {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: #0d7298;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }

        /* Masonry Gallery Styling */
        .masonry-gallery {
            column-count: 3;
            column-gap: 16px;
        }

        @media (max-width: 991px) {
            .masonry-gallery { column-count: 2; }
        }

        @media (max-width: 576px) {
            .masonry-gallery { column-count: 1; }
        }

        .gallery-item {
            break-inside: avoid;
            margin-bottom: 16px;
            position: relative;
            border-radius: 16px;
            overflow: hidden;
            cursor: pointer;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
        }

        .gallery-item img {
            width: 100%;
            display: block;
            border-radius: 16px;
            transition: transform 0.5s ease;
        }

        .gallery-item:hover img {
            transform: scale(1.06);
        }

        .gallery-overlay {
            position: absolute;
            inset: 0;
            background: rgba(13, 114, 152, 0.55);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            color: #ffffff;
            font-size: 1.5rem;
            border-radius: 16px;
        }

        .gallery-item:hover .gallery-overlay {
            opacity: 1;
        }

        /* Fullscreen Lightbox */
        .lightbox-modal {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.95);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 99999;
            backdrop-filter: blur(8px);
            animation: fadeIn 0.3s ease;
        }

        .lightbox-content {
            max-width: 90%;
            max-height: 85vh;
            border-radius: 16px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.5);
            animation: zoomIn 0.3s ease;
        }

        .lightbox-close {
            position: absolute;
            top: 24px;
            right: 32px;
            font-size: 2.2rem;
            color: #ffffff;
            cursor: pointer;
            transition: transform 0.2s ease;
            z-index: 100000;
        }

        .lightbox-close:hover {
            transform: scale(1.2);
            color: #f59e0b;
        }

        .lightbox-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.15);
            color: #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            cursor: pointer;
            transition: all 0.3s ease;
            backdrop-filter: blur(4px);
            z-index: 100000;
        }

        .lightbox-nav:hover {
            background: #0d7298;
            color: #ffffff;
            transform: translateY(-50%) scale(1.1);
        }

        .lightbox-prev { left: 24px; }
        .lightbox-next { right: 24px; }

        /* Social Share Circular Buttons */
        .social-share-btn {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: rgba(13, 114, 152, 0.08);
            color: #0d7298;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
            transition: all 0.3s ease;
            text-decoration: none !important;
        }

        .social-share-btn:hover {
            background: #0d7298;
            color: #ffffff;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(13, 114, 152, 0.25);
        }

        .btn-event-action {
            background: linear-gradient(135deg, #0d7298 0%, #065d7d 100%);
            color: #ffffff;
            font-size: 1rem;
            font-weight: 700;
            border: none;
            border-radius: 50px;
            padding: 12px 30px;
            box-shadow: 0 8px 24px rgba(13, 114, 152, 0.28);
            transition: all 0.3s ease;
            text-decoration: none !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-event-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(13, 114, 152, 0.38);
            color: #ffffff;
        }

        .btn-cancel-back {
            border: 2px solid #94a3b8;
            color: #475569;
            font-size: 0.95rem;
            font-weight: 600;
            border-radius: 50px;
            padding: 10px 28px;
            transition: all 0.3s ease;
            text-decoration: none !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-cancel-back:hover {
            background: #94a3b8;
            color: #ffffff;
        }

        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes zoomIn { from { transform: scale(0.85); } to { transform: scale(1); } }
    </style>
</head>

<body>
    <?php include 'navbar.php'; ?>

    <!-- Redesigned Executive Hero Header -->
    <section class="about-page-header position-relative overflow-hidden w-100 my-0">
        <div class="about-header-glow-1"></div>
        <div class="about-header-glow-2"></div>
        <div class="about-header-pattern"></div>

        <div class="container position-relative z-2 py-4">
            <div class="row align-items-center text-start g-4">
                <div class="col-lg-8" data-aos="fade-right">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb about-breadcrumb px-3 py-1.5 rounded-pill mb-3 d-inline-flex">
                            <li class="breadcrumb-item"><a href="index.php" class="text-black"><i class="bi bi-house-door-fill me-1"></i> Home</a></li>
                            <li class="breadcrumb-item"><a href="events.php" class="text-black">Events</a></li>
                            <li class="breadcrumb-item active text-black" aria-current="page"><?php echo htmlspecialchars($event['title']); ?></li>
                        </ol>
                    </nav>

                    <span class="badge <?php echo $status_badge_class; ?> px-3 py-1.5 rounded-pill mb-2 fw-semibold text-uppercase tracking-wider">
                        <i class="bi bi-circle-fill fs-6 me-1 small"></i> <?php echo htmlspecialchars($event['status']); ?>
                    </span>

                    <h1 class="display-5 fw-bold text-black mb-3">
                        <?php echo htmlspecialchars($event['title']); ?>
                    </h1>

                    <p class="lead text-black-50 mb-0" style="max-width: 700px;">
                        <?php if (!empty($event['event_date'])): ?>
                            <i class="bi bi-calendar-event-fill text-primary me-1"></i> <?php echo date('F d, Y', strtotime($event['event_date'])); ?>
                        <?php endif; ?>
                        <?php if (!empty($event['event_time'])): ?>
                            <span class="mx-3">|</span>
                            <i class="bi bi-clock-fill text-warning me-1"></i> <?php echo htmlspecialchars(substr($event['event_time'], 0, 5)); ?>
                        <?php endif; ?>
                        <?php if (!empty($event['location'])): ?>
                            <span class="mx-3">|</span>
                            <i class="bi bi-geo-alt-fill text-danger me-1"></i> <?php echo htmlspecialchars($event['location']); ?>
                        <?php endif; ?>
                    </p>
                </div>

                <div class="col-lg-4 text-center text-lg-end" data-aos="fade-left" data-aos-delay="200">
                    <div class="about-header-card text-start">
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <div class="rounded-circle bg-opacity-20 p-3 text-warning">
                                <i class="bi bi-calendar-check-fill fs-3"></i>
                            </div>
                            <div>
                                <h6 class="text-black fw-bold mb-0">Official Event</h6>
                                <span class="text-black-50 small">GD Edu Tech Campus</span>
                            </div>
                        </div>
                        <p class="text-black-50 small mb-0" style="line-height: 1.5;">
                            Explore event highlights, schedule details, location, and official photo gallery.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-header-shape position-absolute bottom-0 start-0 w-100">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 120" preserveAspectRatio="none" style="height: 40px; display: block; width: 100%;">
                <path fill="#f8fafc" fill-opacity="1" d="M0,32L48,42.7C96,53,192,75,288,80C384,85,480,75,576,58.7C672,43,768,21,864,21.3C960,21,1056,43,1152,53.3C1248,64,1344,64,1392,64L1440,64L1440,120L1392,120C1344,120,1056,120,960,120C1056,120,960,120,864,120C768,120,672,120,576,120C480,120,384,120,288,120C192,120,96,120,48,120L0,120Z"></path>
            </svg>
        </div>
    </section>

    <!-- Main Content Section -->
    <section class="py-5">
        <div class="container py-2">
            <div class="row g-4">
                
                <!-- Left Column: Main Cover, Description, & Gallery -->
                <div class="col-lg-8">
                    
                    <!-- Cover Image Banner -->
                    <?php if (!empty($event['main_cover_image'])): ?>
                        <div class="event-cover-wrap mb-4" data-aos="fade-up">
                            <img src="<?php echo htmlspecialchars(resolveEventImageUrl($event['main_cover_image'])); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>" class="event-cover-img">
                        </div>
                    <?php endif; ?>

                    <!-- Event Description Card -->
                    <div class="event-detail-card p-4 p-lg-5 mb-4" data-aos="fade-up">
                        <h4 class="fw-bold text-dark mb-3 border-bottom pb-3">
                            <i class="bi bi-info-circle-fill text-primary me-2"></i>About This Event
                        </h4>

                        <?php if (!empty($event['description'])): ?>
                            <div class="text-secondary lead fs-6 mb-0" style="line-height: 1.8; white-space: pre-line;">
                                <?php echo htmlspecialchars($event['description']); ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted mb-0">No description provided for this event.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Photo Gallery Section -->
                    <?php if (!empty($eventImages)): ?>
                        <div class="event-detail-card p-4 p-lg-5 mb-4" data-aos="fade-up">
                            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                                <div>
                                    <h4 class="fw-bold text-dark mb-0"><i class="bi bi-images text-warning me-2"></i>Event Gallery</h4>
                                    <span class="text-muted small">Click any image to view in full screen</span>
                                </div>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-3 py-1.5 rounded-pill fw-semibold">
                                    <?php echo count($eventImages); ?> Photos
                                </span>
                            </div>

                            <div class="masonry-gallery">
                                <?php foreach ($eventImages as $index => $img): ?>
                                    <div class="gallery-item" onclick="openLightbox(<?php echo $index; ?>)">
                                        <img src="<?php echo htmlspecialchars(resolveEventImageUrl($img)); ?>" alt="Gallery Image <?php echo $index + 1; ?>" loading="lazy">
                                        <div class="gallery-overlay">
                                            <i class="bi bi-arrows-angle-expand"></i>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>

                <!-- Right Column: Sidebar Metadata & Social Links -->
                <div class="col-lg-4">
                    <div class="sticky-top" style="top: 100px; z-index: 10;">
                        
                        <!-- Event Info Sidebar Card -->
                        <div class="event-detail-card p-4 mb-4" data-aos="fade-left">
                            <h5 class="fw-bold text-dark mb-3 border-bottom pb-3"><i class="bi bi-card-checklist text-primary me-2"></i>Event Overview</h5>

                            <div class="d-flex flex-column gap-3 mb-4">
                                
                                <?php if (!empty($event['event_date'])): ?>
                                    <div class="info-pill-item d-flex align-items-center gap-3">
                                        <div class="info-icon-box">
                                            <i class="bi bi-calendar-event-fill"></i>
                                        </div>
                                        <div>
                                            <span class="text-muted small d-block">Date</span>
                                            <strong class="text-dark"><?php echo date('l, F d, Y', strtotime($event['event_date'])); ?></strong>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($event['event_time'])): ?>
                                    <div class="info-pill-item d-flex align-items-center gap-3">
                                        <div class="info-icon-box" style="background: #f59e0b;">
                                            <i class="bi bi-clock-fill"></i>
                                        </div>
                                        <div>
                                            <span class="text-muted small d-block">Time</span>
                                            <strong class="text-dark"><?php echo date('h:i A', strtotime($event['event_time'])); ?></strong>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($event['location'])): ?>
                                    <div class="info-pill-item d-flex align-items-center gap-3">
                                        <div class="info-icon-box" style="background: #ef4444;">
                                            <i class="bi bi-geo-alt-fill"></i>
                                        </div>
                                        <div>
                                            <span class="text-muted small d-block">Location</span>
                                            <strong class="text-dark"><?php echo htmlspecialchars($event['location']); ?></strong>
                                        </div>
                                    </div>
                                <?php endif; ?>

                            </div>

                            <!-- Registration / Event Link Button -->
                            <?php if (!empty($event['event_link'])): ?>
                                <a href="<?php echo htmlspecialchars($event['event_link']); ?>" target="_blank" rel="noopener" class="btn-event-action w-100 mb-3">
                                    <span>REGISTER / JOIN EVENT</span>
                                    <i class="bi bi-box-arrow-up-right ms-2"></i>
                                </a>
                            <?php endif; ?>

                            <!-- Social Links Widget -->
                            <?php if (!empty($eventSocialLinks)): ?>
                                <div class="border-top pt-3 mt-3">
                                    <span class="text-muted small d-block mb-2 font-weight-semibold">Connect & Share:</span>
                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                        <?php foreach ($eventSocialLinks as $lnk): 
                                            $icon = getSocialIconClass($lnk['platform']);
                                            $url = $lnk['url'];
                                            if (!$url) { continue; }
                                        ?>
                                            <a href="<?php echo htmlspecialchars($url); ?>" target="_blank" rel="noopener" class="social-share-btn" title="<?php echo htmlspecialchars($lnk['platform']); ?>">
                                                <i class="<?php echo htmlspecialchars($icon); ?>"></i>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <hr class="my-3 text-secondary opacity-25">

                            <a href="events.php" class="btn-cancel-back w-100 text-center">
                                <i class="bi bi-arrow-left me-1"></i> Back to Events
                            </a>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Fullscreen Lightbox Modal -->
    <?php if (!empty($eventImages)): ?>
        <div id="lightboxModal" class="lightbox-modal">
            <span class="lightbox-close" onclick="closeLightbox()">&times;</span>
            
            <div class="lightbox-nav lightbox-prev" onclick="navigateLightbox(-1)">
                <i class="bi bi-chevron-left"></i>
            </div>
            
            <img id="lightboxImg" class="lightbox-content" src="" alt="Full Screen Preview">
            
            <div class="lightbox-nav lightbox-next" onclick="navigateLightbox(1)">
                <i class="bi bi-chevron-right"></i>
            </div>
        </div>
    <?php endif; ?>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    
    <script>
        AOS.init({ duration: 900, easing: 'ease-in-out', once: true });

        <?php if (!empty($eventImages)): ?>
        // Lightbox Gallery Script
        const galleryImages = [
            <?php foreach ($eventImages as $img): ?>
                "<?php echo htmlspecialchars(resolveEventImageUrl($img)); ?>",
            <?php endforeach; ?>
        ];

        let currentIndex = 0;
        const lightboxModal = document.getElementById("lightboxModal");
        const lightboxImg = document.getElementById("lightboxImg");

        function openLightbox(index) {
            currentIndex = index;
            lightboxModal.style.display = "flex";
            updateLightboxImage();
            document.body.style.overflow = "hidden";
        }

        function closeLightbox() {
            lightboxModal.style.display = "none";
            document.body.style.overflow = "auto";
        }

        function navigateLightbox(direction) {
            currentIndex += direction;
            if (currentIndex < 0) currentIndex = galleryImages.length - 1;
            if (currentIndex >= galleryImages.length) currentIndex = 0;
            updateLightboxImage();
        }

        function updateLightboxImage() {
            lightboxImg.style.opacity = '0';
            lightboxImg.style.transform = 'scale(0.95)';
            setTimeout(() => {
                lightboxImg.src = galleryImages[currentIndex];
                lightboxImg.style.opacity = '1';
                lightboxImg.style.transform = 'scale(1)';
            }, 120);
        }

        // Keyboard Navigation (Escape, ArrowLeft, ArrowRight)
        document.addEventListener('keydown', function(e) {
            if (lightboxModal && lightboxModal.style.display === 'flex') {
                if (e.key === 'Escape') closeLightbox();
                if (e.key === 'ArrowLeft') navigateLightbox(-1);
                if (e.key === 'ArrowRight') navigateLightbox(1);
            }
        });

        // Touch Swipe Gesture Support
        let touchStartX = 0;
        if (lightboxImg) {
            lightboxImg.addEventListener('touchstart', e => { touchStartX = e.touches[0].clientX; }, {passive: true});
            lightboxImg.addEventListener('touchend', e => {
                let touchEndX = e.changedTouches[0].clientX;
                if (touchStartX - touchEndX > 50) navigateLightbox(1);
                if (touchEndX - touchStartX > 50) navigateLightbox(-1);
            }, {passive: true});
        }
        <?php endif; ?>
    </script>
</body>
</html>
