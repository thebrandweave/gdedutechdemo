<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once './Configurations/config.php';

// Load categories and setup filtering/pagination
$selectedCategoryId = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
$allowedStatuses = ['upcoming','ongoing','completed'];
$selectedStatus = isset($_GET['status']) && in_array(strtolower($_GET['status']), $allowedStatuses, true)
    ? strtolower($_GET['status'])
    : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 6;
$offset = ($page - 1) * $limit;

$eventCategories = [];
$catRes = $conn->query("SELECT category_id, name FROM EventCategories ORDER BY name ASC");
if ($catRes) {
    while ($c = $catRes->fetch_assoc()) { $eventCategories[] = $c; }
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

// Fetch events (default excludes cancelled) with optional category/status filters
// Build sections data for Upcoming, Ongoing, Completed
$statusLabels = [
    'upcoming' => 'Upcoming Events',
    'ongoing' => 'Ongoing Events',
    'completed' => 'Completed Events'
];
$statusSubtitles = [
    'upcoming' => "Don't miss out on these exciting learning opportunities",
    'ongoing' => 'Happening now — join live sessions and activities',
    'completed' => 'Catch up on past events and recorded sessions'
];
$statusesToShow = $selectedStatus ? [$selectedStatus] : array_keys($statusLabels);
$sectionsData = [];
foreach ($statusesToShow as $statusKey) {
    $limitUse = $selectedStatus ? $limit : 6;
    $offsetUse = $selectedStatus ? $offset : 0;
    $whereParts = ["e.status = '" . $conn->real_escape_string($statusKey) . "'"];
    if ($selectedCategoryId > 0) {
        $whereParts[] = "e.category_id = " . intval($selectedCategoryId);
    }
    $where = ' WHERE ' . implode(' AND ', $whereParts);
    $q = "SELECT e.* FROM Events e" . $where . " ORDER BY e.event_date IS NULL, e.event_date ASC, e.created_at DESC LIMIT " . intval($limitUse) . " OFFSET " . intval($offsetUse);
    $c = "SELECT COUNT(*) as total FROM Events e" . $where;
    $total = 0;
    $countRes = $conn->query($c);
    if ($countRes && $countRes->num_rows) { $total = intval(($countRes->fetch_assoc())['total']); }
    $rows = [];
    $res = $conn->query($q);
    if ($res && $res->num_rows) { while ($r = $res->fetch_assoc()) { $rows[] = $r; } }
    $sectionsData[$statusKey] = [
        'label' => $statusLabels[$statusKey],
        'subtitle' => $statusSubtitles[$statusKey] ?? '',
        'events' => $rows,
        'total' => $total,
        'hasMore' => ($offsetUse + count($rows)) < $total,
    ];
}

// Build featured events carousel data (mix of upcoming + ongoing)
$featuredEvents = [];
foreach (['upcoming','ongoing'] as $s) {
    if (!empty($sectionsData[$s]['events'])) {
        foreach ($sectionsData[$s]['events'] as $ev) {
            $featuredEvents[] = $ev;
            if (count($featuredEvents) >= 6) { break 2; }
        }
    }
}

// AJAX: return only event cards for a specific status and page (infinite load)
if (isset($_GET['ajax']) && $_GET['ajax'] == '1' && $selectedStatus) {
    $ajaxRows = $sectionsData[$selectedStatus]['events'] ?? [];
    if (!empty($ajaxRows)) {
        foreach ($ajaxRows as $index => $ev) {
            ?>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                <div class="card h-100 shadow-sm border-0 position-relative event-card" style="cursor: pointer;" onclick="window.location.href='event-details.php?event_id=<?php echo intval($ev['event_id']); ?>'">
                    <?php if (!empty($ev['main_cover_image'])): ?>
                        <img src="<?php echo htmlspecialchars(resolveEventImageUrl($ev['main_cover_image'])); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($ev['title']); ?>" style="height:200px;object-fit:cover;">
                    <?php else: ?>
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height:200px;">
                            <i class="bi bi-calendar-event display-4 text-muted"></i>
                        </div>
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                        <div class="mb-2 text-muted small">
                            <?php if (!empty($ev['event_date'])): ?>
                                <i class="bi bi-calendar3 me-1"></i><?php echo date('M d, Y', strtotime($ev['event_date'])); ?>
                            <?php endif; ?>
                            <?php if (!empty($ev['event_time'])): ?>
                                <span class="ms-3"><i class="bi bi-clock me-1"></i><?php echo htmlspecialchars(substr($ev['event_time'],0,5)); ?></span>
                            <?php endif; ?>
                        </div>
                        <h5 class="card-title mb-2 text-clamp-2"><?php echo htmlspecialchars($ev['title']); ?></h5>
                        <p class="card-text text-muted flex-grow-1 text-clamp-3"><?php echo htmlspecialchars(strip_tags($ev['description'] ?? '')); ?></p>
                        <?php if (!empty($ev['location'])): ?>
                            <div class="text-muted small mb-3"><i class="bi bi-geo-alt me-1"></i><?php echo htmlspecialchars($ev['location']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php
        }
    }
    exit;
}

// Collect all event IDs currently loaded to fetch social links in one query
$eventIdSet = [];
foreach ($sectionsData as $sec) {
    if (!empty($sec['events'])) {
        foreach ($sec['events'] as $ev) {
            if (isset($ev['event_id'])) { $eventIdSet[intval($ev['event_id'])] = true; }
        }
    }
}
if (!empty($featuredEvents)) {
    foreach ($featuredEvents as $ev) {
        if (isset($ev['event_id'])) { $eventIdSet[intval($ev['event_id'])] = true; }
    }
}

$eventIdList = array_keys($eventIdSet);
$eventIdToSocialLinks = [];
if (!empty($eventIdList)) {
    $idsList = implode(',', array_map('intval', $eventIdList));
    $slq = "SELECT target_id, platform, url FROM social_links WHERE target_type = 'event' AND target_id IN ($idsList)";
    $slres = $conn->query($slq);
    if ($slres && $slres->num_rows) {
        while ($row = $slres->fetch_assoc()) {
            $tid = intval($row['target_id']);
            if (!isset($eventIdToSocialLinks[$tid])) { $eventIdToSocialLinks[$tid] = []; }
            $eventIdToSocialLinks[$tid][] = [
                'platform' => strtolower(trim($row['platform'] ?? '')),
                'url' => $row['url'] ?? ''
            ];
        }
    }
}

// Helper to derive icon class for a platform
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
    <title>Events - GD Edu Tech</title>
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
    <!-- Custom JavaScript -->
    <script src="./js/main.js" defer></script>
        
    <link rel="icon" type="image/png" href="./Images/Logos/GD_Only_logo.png">
    <link rel="icon" type="image/png" sizes="32x32" href="./Images/Logos/GD_Only_logo.png">
    <link rel="icon" type="image/png" sizes="16x16" href="./Images/Logos/GD_Only_logo.png">
    <link rel="shortcut icon" href="./Images/Logos/GD_Only_logo.png">
    <link rel="apple-touch-icon" href="./Images/Logos/GD_Only_logo.png">
    <meta name="msapplication-TileImage" content="./Images/Logos/GD_Only_logo.png">
    <style>
        /* Add custom styles for the events image */
        .premium-feature-card .card-icon i {
            color: #0d7298 !important; 
        }
        .events-hero-image {
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

        /* Event card hover effects */
        .event-card {
            transition: all 0.3s ease;
        }

        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
        }

        .event-card:hover .card-img-top {
            transition: transform 0.3s ease;
        }
        /* Featured carousel sizing: enforce consistent card height across slides */
        #featuredEventsCarousel .event-card .row { height: 320px; }
        #featuredEventsCarousel .event-card .col-md-6 { height: 100%; }
        #featuredEventsCarousel .event-card img { height: 100%; object-fit: cover; }
        #featuredEventsCarousel .event-card .p-4 { height: 100%; display: flex; flex-direction: column; }
        /* Line clamp utilities and consistent card sizing */
        .text-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; line-clamp: 1; }
        .text-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-clamp: 2; }
        .text-clamp-3 { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; line-clamp: 3; }
        
        /* Newsletter Section Styles */
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
            }

            .newsletter-section .btn-subscribe {
                width: 100%;
                display: flex;
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
                <div class="col-md-7" data-aos="fade-right">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-3">
                            <li class="breadcrumb-item"><a href="index.php" class="text-white-50">Home</a></li>
                            <li class="breadcrumb-item active text-white" aria-current="page">Events</li>
                        </ol>
                    </nav>
                    <h1 class="text-white display-5 fw-bold mb-3">Events</h1>
                    <p class="text-white-50 lead mb-0">Join our educational events, workshops, and webinars to enhance your learning journey.</p>
                </div>
                <div class="col-md-5" data-aos="fade-left">
                    <!-- <img src="./Images/Others/event2.png" alt="Events" class="events-hero-image"> -->
                </div>
            </div>
        </div>
        <div class="page-header-shape">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
                <path fill="#ffffff" fill-opacity="1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,112C672,96,768,96,864,112C960,128,1056,160,1152,160C1248,160,1344,128,1392,112L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
            </svg>
        </div>
    </section>

    <!-- Featured Now Carousel (Upcoming & Ongoing) -->
    <?php if (!empty($featuredEvents)): ?>
    <section class="py-4">
        <div class="container">
            <div class="row mb-3">
                <div class="col-lg-6">
                    <h2 class="section-heading" data-aos="fade-up">Happening & Next Up</h2>
                    <p class="lead text-muted" data-aos="fade-up" data-aos-delay="100">A quick look at ongoing and upcoming events</p>
                </div>
            </div>
            <div id="featuredEventsCarousel" class="carousel slide" data-bs-ride="carousel">
                <?php if (count($featuredEvents) > 1): ?>
                <div class="carousel-indicators">
                    <?php foreach ($featuredEvents as $i => $ev): ?>
                        <button type="button" data-bs-target="#featuredEventsCarousel" data-bs-slide-to="<?php echo $i; ?>" class="<?php echo $i===0?'active':''; ?>" aria-current="<?php echo $i===0?'true':'false'; ?>" aria-label="Slide <?php echo $i+1; ?>"></button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <div class="carousel-inner">
                    <?php foreach ($featuredEvents as $i => $ev): ?>
                    <div class="carousel-item <?php echo $i===0?'active':''; ?>">
                        <div class="card border-0 shadow-sm overflow-hidden position-relative event-card" style="cursor: pointer;" onclick="window.location.href='event-details.php?event_id=<?php echo intval($ev['event_id']); ?>'">
                            <div class="row g-0 align-items-stretch">
                                <div class="col-md-6">
                                    <?php if (!empty($ev['main_cover_image'])): ?>
                                        <img src="<?php echo htmlspecialchars(resolveEventImageUrl($ev['main_cover_image'])); ?>" class="w-100 h-100" alt="<?php echo htmlspecialchars($ev['title']); ?>" style="object-fit: cover; min-height: 260px;">
                                    <?php else: ?>
                                        <div class="d-flex align-items-center justify-content-center bg-light w-100 h-100" style="min-height: 260px;">
                                            <i class="bi bi-calendar-event display-4 text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6 d-flex">
                                    <div class="p-4 d-flex flex-column flex-grow-1">
                                        <div class="mb-2 text-muted small">
                                            <?php if (!empty($ev['event_date'])): ?>
                                                <i class="bi bi-calendar3 me-1"></i><?php echo date('M d, Y', strtotime($ev['event_date'])); ?>
                                            <?php endif; ?>
                                            <?php if (!empty($ev['event_time'])): ?>
                                                <span class="ms-3"><i class="bi bi-clock me-1"></i><?php echo htmlspecialchars(substr($ev['event_time'],0,5)); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <h4 class="mb-2 text-clamp-2"><?php echo htmlspecialchars($ev['title']); ?></h4>
                                        <p class="text-muted flex-grow-1 mb-3 text-clamp-3"><?php echo htmlspecialchars(strip_tags($ev['description'] ?? '')); ?></p>
                                        <?php if (!empty($ev['location'])): ?>
                                            <div class="text-muted small mb-3"><i class="bi bi-geo-alt me-1"></i><?php echo htmlspecialchars($ev['location']); ?></div>
                                        <?php endif; ?>
                                        <?php 
                                            $links = $eventIdToSocialLinks[intval($ev['event_id'])] ?? [];
                                            if (!empty($links)):
                                        ?>
                                            <div class="pt-2 mt-auto d-flex align-items-center gap-3">
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
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if (count($featuredEvents) > 1): ?>
                <button class="carousel-control-prev" type="button" data-bs-target="#featuredEventsCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#featuredEventsCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    

    <!-- Events Section -->
    <section class="py-5">
        <div class="container">
            <?php 
            $hasAnyEvents = false;
            foreach ($sectionsData as $statusKey => $section) {
                if (!empty($section['events'])) {
                    $hasAnyEvents = true;
                    break;
                }
            }
            
            if (!$hasAnyEvents): 
            ?>
                <div class="row mb-4 mt-2">
                    <div class="col-lg-6 col-12">
                        <h3 class="section-heading" data-aos="fade-up">Latest Events</h3>
                        <p class="lead text-muted" data-aos="fade-up" data-aos-delay="100">Stay updated with our upcoming events</p>
                    </div>
                </div>
                <!-- <div class="row">
                    <div class="col-12" data-aos="fade-up">
                        <?php if (!empty($eventCategories)): ?>
                            <?php $allActive = $selectedCategoryId === 0 ? 'btn-primary text-white' : 'btn-outline-primary'; ?>
                            <a href="events.php" class="btn btn-sm rounded-pill me-2 mb-3 <?php echo $allActive; ?>">All</a>
                            <?php foreach ($eventCategories as $cat): 
                                $isActive = ($selectedCategoryId === intval($cat['category_id'])) ? 'btn-primary text-white' : 'btn-outline-primary';
                            ?>
                                <a href="events.php?category_id=<?php echo intval($cat['category_id']); ?>" class="btn btn-sm rounded-pill me-2 mb-3 <?php echo $isActive; ?>"><?php echo htmlspecialchars($cat['name']); ?></a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div> -->
                <div class="row g-4">
                    <div class="col-12">
                        <div class="alert alert-info text-center" role="alert">
                            <i class="bi bi-info-circle me-2"></i> No event posts available at the moment. Please check back later.
                        </div>
                    </div>
                </div>
                <div class="my-5"></div>
            <?php else: ?>
                <?php foreach ($sectionsData as $statusKey => $section): ?>
                    <?php if (!empty($section['events'])): ?>
                        <div class="row mb-4 mt-2">
                            <div class="col-lg-6 col-12">
                                <h3 class="section-heading" data-aos="fade-up"><?php echo htmlspecialchars($section['label']); ?></h3>
                                <?php if (!empty($section['subtitle'])): ?>
                                    <p class="lead text-muted" data-aos="fade-up" data-aos-delay="100"><?php echo htmlspecialchars($section['subtitle']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12" data-aos="fade-up">
                                <?php if (!empty($eventCategories)): ?>
                                    <?php $allActive = $selectedCategoryId === 0 ? 'btn-primary text-white' : 'btn-outline-primary'; ?>
                                    <a href="events.php" class="btn btn-sm rounded-pill me-2 mb-3 <?php echo $allActive; ?>">All</a>
                                    <?php foreach ($eventCategories as $cat): 
                                        $isActive = ($selectedCategoryId === intval($cat['category_id'])) ? 'btn-primary text-white' : 'btn-outline-primary';
                                    ?>
                                        <a href="events.php?category_id=<?php echo intval($cat['category_id']); ?>" class="btn btn-sm rounded-pill me-2 mb-3 <?php echo $isActive; ?>"><?php echo htmlspecialchars($cat['name']); ?></a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="row g-4">
                            <?php foreach ($section['events'] as $index => $ev): ?>
                                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                                    <div class="card h-100 shadow-sm border-0 position-relative event-card" style="cursor: pointer;" onclick="window.location.href='event-details.php?event_id=<?php echo intval($ev['event_id']); ?>'">
                                        <?php if (!empty($ev['main_cover_image'])): ?>
                                            <img src="<?php echo htmlspecialchars(resolveEventImageUrl($ev['main_cover_image'])); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($ev['title']); ?>" style="height:200px;object-fit:cover;">
                                        <?php else: ?>
                                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height:200px;">
                                                <i class="bi bi-calendar-event display-4 text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="card-body d-flex flex-column">
                                            <div class="mb-2 text-muted small">
                                                <?php if (!empty($ev['event_date'])): ?>
                                                    <i class="bi bi-calendar3 me-1"></i><?php echo date('M d, Y', strtotime($ev['event_date'])); ?>
                                                <?php endif; ?>
                                                <?php if (!empty($ev['event_time'])): ?>
                                                    <span class="ms-3"><i class="bi bi-clock me-1"></i><?php echo htmlspecialchars(substr($ev['event_time'],0,5)); ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <h5 class="card-title mb-2 text-clamp-2"><?php echo htmlspecialchars($ev['title']); ?></h5>
                                            <p class="card-text text-muted flex-grow-1 text-clamp-3"><?php echo htmlspecialchars(strip_tags($ev['description'] ?? '')); ?></p>
                                            <?php if (!empty($ev['location'])): ?>
                                                <div class="text-muted small mb-3"><i class="bi bi-geo-alt me-1"></i><?php echo htmlspecialchars($ev['location']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php if ($section['hasMore']):
                                $nextPage = ($selectedStatus ? ($page + 1) : 2);
                            ?>
                            <div class="text-center mt-4" data-aos="fade-up">
                                <button class="btn btn-outline-primary btn-sm load-more-events" data-status="<?php echo htmlspecialchars($statusKey); ?>" data-next-page="<?php echo intval($nextPage); ?>" data-total="<?php echo intval($section['total']); ?>">View More</button>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="my-5"></div>
                    <?php endif; ?>
                <?php endforeach; ?>
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
                    <p class="lead text-white-50 mb-5">Get the latest events, updates, and exclusive offers delivered directly to your inbox.</p>
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
    <!-- Back to Top Button -->
    <script src="js/back-to-top.js"></script>
    <script>
    (function(){
        const container = document.querySelector('section.py-5 .container');
        if (!container) return;
        container.addEventListener('click', async function(e){
            const btn = e.target.closest('.load-more-events');
            if (!btn) return;
            e.preventDefault();
            const status = btn.getAttribute('data-status');
            let nextPage = parseInt(btn.getAttribute('data-next-page') || '2', 10);
            const total = parseInt(btn.getAttribute('data-total') || '0', 10);
            const sectionRow = btn.closest('section.py-5').querySelectorAll('.row.g-4');
            // Find the nearest events grid above the button
            let grid = btn.closest('.text-center').previousElementSibling;
            while (grid && !grid.classList.contains('row') ) {
                grid = grid.previousElementSibling;
            }
            if (!status || !grid) return;
            btn.disabled = true; const original = btn.textContent; btn.textContent = 'Loading...';
            try {
                const params = new URLSearchParams(window.location.search);
                params.set('status', status);
                params.set('page', String(nextPage));
                params.set('ajax', '1');
                const res = await fetch('events.php?' + params.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
                const html = await res.text();
                const temp = document.createElement('div');
                temp.innerHTML = html.trim();
                if (!temp.firstChild) { btn.remove(); return; }
                while (temp.firstChild) { grid.appendChild(temp.firstChild); }
                if (window.AOS) { AOS.refreshHard(); }
                nextPage += 1; btn.setAttribute('data-next-page', String(nextPage));
                // Hide button when we've shown all items in this section
                const shown = grid.querySelectorAll('.col-lg-4.col-md-6').length;
                if (total && shown >= total) { btn.remove(); }
                btn.disabled = false; btn.textContent = original;
            } catch (err) {
                console.error(err);
                btn.disabled = false; btn.textContent = original;
            }
        });
    })();
    </script>
</body>

</html>