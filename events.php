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

// Function to fetch sections data
function fetchSectionsData($conn, $selectedStatus, $selectedCategoryId, $limit, $offset) {
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
    return $sectionsData;
}

$sectionsData = fetchSectionsData($conn, $selectedStatus, $selectedCategoryId, $limit, $offset);

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

// AJAX: return only event cards for a specific status and page (infinite load)
if (isset($_GET['ajax']) && $_GET['ajax'] == '1' && $selectedStatus) {
    $ajaxRows = $sectionsData[$selectedStatus]['events'] ?? [];
    if (!empty($ajaxRows)) {
        foreach ($ajaxRows as $index => $ev) {
            ?>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden position-relative event-card" style="cursor: pointer;" onclick="window.location.href='event-details.php?event_id=<?php echo intval($ev['event_id']); ?>'">
                    <?php if (!empty($ev['main_cover_image'])): ?>
                        <img src="<?php echo htmlspecialchars(resolveEventImageUrl($ev['main_cover_image'])); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($ev['title']); ?>" style="height:220px;object-fit:cover;">
                    <?php else: ?>
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height:220px;">
                            <i class="bi bi-calendar-event display-4 text-muted"></i>
                        </div>
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column p-4">
                        <div class="mb-2 text-dark fw-bold small">
                            <?php if (!empty($ev['event_date'])): ?>
                                <i class="bi bi-calendar3 me-1 text-primary"></i><span class="text-black"><?php echo date('M d, Y', strtotime($ev['event_date'])); ?></span>
                            <?php endif; ?>
                            <?php if (!empty($ev['event_time'])): ?>
                                <span class="ms-3 text-black"><i class="bi bi-clock me-1 text-primary"></i><?php echo htmlspecialchars(substr($ev['event_time'],0,5)); ?></span>
                            <?php endif; ?>
                        </div>
                        <h5 class="card-title mb-2 text-black fw-bold text-clamp-2"><?php echo htmlspecialchars($ev['title']); ?></h5>
                        <p class="card-text text-secondary flex-grow-1 text-clamp-3"><?php echo htmlspecialchars(strip_tags($ev['description'] ?? '')); ?></p>
                        <?php if (!empty($ev['location'])): ?>
                            <div class="text-dark fw-semibold small mb-3"><i class="bi bi-geo-alt-fill text-danger me-1"></i><?php echo htmlspecialchars($ev['location']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php
        }
    }
    exit;
}

// Function to render the inner event grid and category chips
function renderEventsGridHTML($sectionsData, $eventCategories, $selectedCategoryId, $selectedStatus, $page) {
    ob_start();
    ?>
    <!-- Category Filter Chips -->
    <div class="row mb-4">
        <div class="col-12" data-aos="fade-up">
            <?php if (!empty($eventCategories)): ?>
                <?php $allActive = ($selectedCategoryId === 0) ? 'btn-dark text-white' : 'btn-outline-dark'; ?>
                <button type="button" class="btn btn-sm rounded-pill px-3 py-1.5 me-2 mb-2 fw-semibold category-filter-btn <?php echo $allActive; ?>" data-category-id="0">All</button>
                <?php foreach ($eventCategories as $cat): 
                    $isActive = ($selectedCategoryId === intval($cat['category_id'])) ? 'btn-dark text-white' : 'btn-outline-dark';
                ?>
                    <button type="button" class="btn btn-sm rounded-pill px-3 py-1.5 me-2 mb-2 fw-semibold category-filter-btn <?php echo $isActive; ?>" data-category-id="<?php echo intval($cat['category_id']); ?>"><?php echo htmlspecialchars($cat['name']); ?></button>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

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
        <div class="row g-4 my-2">
            <div class="col-12">
                <div class="card border-0  rounded-4 text-center py-5 px-3 ">
                    <div class="card-body">
                        <i class="bi bi-calendar-x display-3 text-secondary opacity-50 d-block mb-3"></i>
                        <h4 class="fw-bold text-black mb-2">No Events Found</h4>
                        <p class="text-secondary mb-4" style="max-width: 500px; margin: 0 auto;">There are currently no events listed under this category filter.</p>
                        <?php if ($selectedCategoryId > 0): ?>
                            <button type="button" class="btn btn-dark rounded-pill px-4 py-2 fw-semibold category-filter-btn" data-category-id="0">
                                <i class="bi bi-arrow-counterclockwise me-1.5"></i> Show All Events
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($sectionsData as $statusKey => $section): ?>
            <?php if (!empty($section['events'])): ?>
                <div class="row mb-4 align-items-end">
                    <div class="col-lg-8">
                        <h3 class="display-6 top-section-title mb-1" data-aos="fade-up"><?php echo htmlspecialchars($section['label']); ?></h3>
                        <?php if (!empty($section['subtitle'])): ?>
                            <p class="lead top-section-subtitle mb-0" data-aos="fade-up" data-aos-delay="100"><?php echo htmlspecialchars($section['subtitle']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Event Grid Cards -->
                <div class="row g-4">
                    <?php foreach ($section['events'] as $index => $ev): ?>
                        <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="<?php echo ($index % 3) * 100; ?>">
                            <div class="event-card h-100 d-flex flex-column" style="cursor: pointer;" onclick="window.location.href='event-details.php?event_id=<?php echo intval($ev['event_id']); ?>'">
                                <?php if (!empty($ev['main_cover_image'])): ?>
                                    <img src="<?php echo htmlspecialchars(resolveEventImageUrl($ev['main_cover_image'])); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($ev['title']); ?>" style="height:220px;object-fit:cover;">
                                <?php else: ?>
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height:220px;">
                                        <i class="bi bi-calendar-event display-4 text-muted"></i>
                                    </div>
                                <?php endif; ?>

                                <div class="card-body p-4 d-flex flex-column flex-grow-1">
                                    <div class="mb-2 text-black fw-bold small">
                                        <?php if (!empty($ev['event_date'])): ?>
                                            <i class="bi bi-calendar3 me-1 text-primary"></i><span class="text-black fw-bold"><?php echo date('M d, Y', strtotime($ev['event_date'])); ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($ev['event_time'])): ?>
                                            <span class="ms-3 text-black fw-bold"><i class="bi bi-clock me-1 text-primary"></i><?php echo htmlspecialchars(substr($ev['event_time'],0,5)); ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <h5 class="card-title text-black fw-bold mb-2 text-clamp-2"><?php echo htmlspecialchars($ev['title']); ?></h5>
                                    <p class="card-text text-secondary flex-grow-1 mb-3 text-clamp-3"><?php echo htmlspecialchars(strip_strip_tags_safe($ev['description'] ?? '')); ?></p>
                                    
                                    <?php if (!empty($ev['location'])): ?>
                                        <div class="text-black fw-semibold small mb-3"><i class="bi bi-geo-alt-fill text-danger me-1"></i><?php echo htmlspecialchars($ev['location']); ?></div>
                                    <?php endif; ?>

                                    <div class="mt-auto pt-2">
                                        <span class="btn btn-outline-dark btn-sm rounded-pill w-100 py-2 font-semibold">View Details <i class="bi bi-arrow-right ms-1"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if ($section['hasMore']):
                        $nextPage = ($selectedStatus ? ($page + 1) : 2);
                    ?>
                    <div class="text-center mt-4" data-aos="fade-up">
                        <button class="btn btn-dark rounded-pill px-4 py-2 fw-bold load-more-events" data-status="<?php echo htmlspecialchars($statusKey); ?>" data-next-page="<?php echo intval($nextPage); ?>" data-total="<?php echo intval($section['total']); ?>">View More Events</button>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="my-5"></div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
    <?php
    return ob_get_clean();
}

function strip_strip_tags_safe($txt) {
    return strip_tags((string)$txt);
}

// AJAX Category Filter endpoint
if (isset($_GET['ajax_filter']) && $_GET['ajax_filter'] == '1') {
    echo renderEventsGridHTML($sectionsData, $eventCategories, $selectedCategoryId, $selectedStatus, $page);
    exit;
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

    <style>
        body {
            background: #f8fafc;
            color: #0f172a;
        }

        /* Top Section Text Enforcement - Black & Dark High-Contrast */
        .top-section-title {
            color: #000000 !important;
            font-weight: 800 !important;
        }

        .top-section-subtitle {
            color: #1e293b !important;
            font-weight: 600 !important;
        }

        .event-card-title {
            color: #000000 !important;
            font-weight: 700 !important;
        }

        .event-card-meta {
            color: #0f172a !important;
            font-weight: 600 !important;
        }

        /* Event card hover effects */
        .event-card {
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            box-shadow: 0 10px 30px -10px rgba(15, 23, 42, 0.08);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .event-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px -10px rgba(13, 114, 152, 0.18) !important;
            border-color: rgba(13, 114, 152, 0.3);
        }

        /* Featured carousel sizing */
        #featuredEventsCarousel .event-card .row { min-height: 320px; }
        #featuredEventsCarousel .event-card .col-md-6 { height: 100%; }
        #featuredEventsCarousel .event-card img { height: 100%; object-fit: cover; }
        
        .text-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; line-clamp: 1; }
        .text-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-clamp: 2; }
        .text-clamp-3 { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; line-clamp: 3; }

        .hero-award-img:hover {
            transform: translateY(-8px) scale(1.03);
        }

        #eventsGridContainer {
            transition: opacity 0.25s ease-in-out;
        }
    </style>
</head>

<body>
    <?php include 'navbar.php'; ?>

    <!-- Executive Hero Banner -->
    <section class="about-page-header position-relative overflow-hidden w-100 my-0">
        <div class="about-header-glow-1"></div>
        <div class="about-header-glow-2"></div>
        <div class="about-header-pattern"></div>

        <div class="container position-relative z-2 py-4">
            <div class="row align-items-center text-start g-4">
                <div class="col-lg-7" data-aos="fade-right">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb about-breadcrumb px-3 py-1.5 rounded-pill mb-3 d-inline-flex">
                            <li class="breadcrumb-item"><a href="index.php" class="text-black text-decoration-none"><i class="bi bi-house-door-fill me-1"></i> Home</a></li>
                            <li class="breadcrumb-item active text-black" aria-current="page">Events</li>
                        </ol>
                    </nav>

                    <h1 class="display-4 fw-bold text-black mb-3">
                        Educational <span class="cta-gold-text">Events &amp; Workshops</span>
                    </h1>

                    <p class="lead text-black-50 mb-4" style="max-width: 650px;">
                        Join our educational events, live interactive workshops, hackathons, and webinars to enhance your learning journey with industry experts.
                    </p>
                </div>

                <!-- Right Column: Hero Event Image -->
                <div class="col-lg-5 text-center text-lg-end" data-aos="fade-left" data-aos-delay="200">
                    <div class="position-relative d-inline-block">
                        <div class="position-absolute top-50 start-50 translate-middle rounded-circle bg-opacity-20 blur-2xl" style="width: 320px; height: 320px; filter: blur(40px); z-index: 1;"></div>
                        <img src="./Images/Others/event.png" alt="Educational Events" class="img-fluid position-relative z-2 hero-award-img" style="max-height: 370px; transition: transform 0.4s ease;">
                    </div>
                </div>
            </div>
        </div>

        <div class="page-header-shape position-absolute bottom-0 start-0 w-100">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 120" preserveAspectRatio="none" style="height: 40px; display: block; width: 100%;">
                <path fill="#f8fafc" fill-opacity="1" d="M0,32L48,42.7C96,53,192,75,288,80C384,85,480,75,576,58.7C672,43,768,21,864,21.3C960,21,1056,43,1152,53.3C1248,64,1344,64,1392,64L1440,64L1440,120L1392,120C1344,120,960,120,864,120C768,120,672,120,576,120C480,120,384,120,288,120C192,120,96,120,48,120L0,120Z"></path>
            </svg>
        </div>
    </section>

    <!-- Top Featured Section (Happening & Next Up) -->
    <?php if (!empty($featuredEvents)): ?>
    <section class="py-5">
        <div class="container py-2">
            <div class="row mb-4">
                <div class="col-lg-8">
                    <h2 class="display-6 top-section-title mb-2" data-aos="fade-up">Happening &amp; Next Up</h2>
                    <p class="lead top-section-subtitle mb-0" data-aos="fade-up" data-aos-delay="100">A quick look at ongoing and upcoming events</p>
                </div>
            </div>

            <div id="featuredEventsCarousel" class="carousel slide" data-bs-ride="carousel" data-aos="fade-up">
                <?php if (count($featuredEvents) > 1): ?>
                <div class="carousel-indicators mb-n4">
                    <?php foreach ($featuredEvents as $i => $ev): ?>
                        <button type="button" data-bs-target="#featuredEventsCarousel" data-bs-slide-to="<?php echo $i; ?>" class="<?php echo $i===0?'active':''; ?>" aria-current="<?php echo $i===0?'true':'false'; ?>" aria-label="Slide <?php echo $i+1; ?>"></button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <div class="carousel-inner rounded-4 shadow-lg overflow-hidden">
                    <?php foreach ($featuredEvents as $i => $ev): ?>
                    <div class="carousel-item <?php echo $i===0?'active':''; ?>">
                        <div class="card border-0 event-card" style="cursor: pointer;" onclick="window.location.href='event-details.php?event_id=<?php echo intval($ev['event_id']); ?>'">
                            <div class="row g-0 align-items-stretch">
                                <div class="col-md-6">
                                    <?php if (!empty($ev['main_cover_image'])): ?>
                                        <img src="<?php echo htmlspecialchars(resolveEventImageUrl($ev['main_cover_image'])); ?>" class="w-100 h-100" alt="<?php echo htmlspecialchars($ev['title']); ?>" style="object-fit: cover; min-height: 320px;">
                                    <?php else: ?>
                                        <div class="d-flex align-items-center justify-content-center bg-light w-100 h-100" style="min-height: 320px;">
                                            <i class="bi bi-calendar-event display-3 text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6 d-flex bg-white">
                                    <div class="p-4 p-md-5 d-flex flex-column flex-grow-1">
                                        <div class="mb-3 event-card-meta small">
                                            <?php if (!empty($ev['event_date'])): ?>
                                                <i class="bi bi-calendar3 me-1 text-primary"></i><span class="text-black fw-bold"><?php echo date('M d, Y', strtotime($ev['event_date'])); ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($ev['event_time'])): ?>
                                                <span class="ms-3 text-black fw-bold"><i class="bi bi-clock me-1 text-primary"></i><?php echo htmlspecialchars(substr($ev['event_time'],0,5)); ?></span>
                                            <?php endif; ?>
                                        </div>

                                        <h3 class="mb-3 text-black fw-bold event-card-title text-clamp-2"><?php echo htmlspecialchars($ev['title']); ?></h3>
                                        <p class="text-secondary flex-grow-1 mb-4 text-clamp-3 fs-6"><?php echo htmlspecialchars(strip_tags($ev['description'] ?? '')); ?></p>
                                        
                                        <?php if (!empty($ev['location'])): ?>
                                            <div class="text-black fw-semibold small mb-3"><i class="bi bi-geo-alt-fill text-danger me-1.5"></i><?php echo htmlspecialchars($ev['location']); ?></div>
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
                                                    <a href="<?php echo htmlspecialchars($url); ?>" target="_blank" rel="noopener" class="text-dark fs-5" aria-label="<?php echo htmlspecialchars($lnk['platform']); ?> link">
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
                    <span class="carousel-control-prev-icon bg-dark rounded-circle p-3" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#featuredEventsCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon bg-dark rounded-circle p-3" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Events Section Grid with Category Filters (AJAX Container) -->
    <section class="py-5 bg-white">
        <div class="container py-2" id="eventsGridContainer">
            <?php echo renderEventsGridHTML($sectionsData, $eventCategories, $selectedCategoryId, $selectedStatus, $page); ?>
        </div>
    </section>

    <!-- Floating Plan Tilted Cards CTA Section -->
    <section class="cta-plan-section py-0 my-0 w-100 position-relative overflow-hidden" data-aos="fade-up">
        <div class="cta-plan-banner position-relative text-center w-100 rounded-0 border-0">
            
            <svg class="cta-wavy-track track-left d-none d-lg-block" viewBox="0 0 220 130" fill="none" stroke="#7e858d" stroke-width="3" stroke-dasharray="6 6">
                <path d="M10,110 C70,20 140,120 210,20" />
            </svg>
            <svg class="cta-wavy-track track-right d-none d-lg-block" viewBox="0 0 220 130" fill="none" stroke="#7e858d" stroke-width="3" stroke-dasharray="6 6">
                <path d="M10,20 C80,120 150,20 210,110" />
            </svg>

            <div class="plan-card plan-card-left text-start d-none d-lg-block">
                <div class="plan-header mb-2">
                    <h6 class="plan-title fw-bold mb-1"><i class="bi bi-calendar-event-fill text-primary me-2"></i> Student Pass</h6>
                    <p class="plan-subtext text-muted small mb-0">Essential event access.</p>
                </div>
                <div class="plan-price mb-2">
                    <span class="price-num fw-bold fs-5 text-primary">Skill Focused</span>
                </div>
                <ul class="plan-features list-unstyled small mb-3" style="font-size: 0.82rem;">
                    <li class="mb-1.5"><i class="bi bi-check2 me-1.5 text-dark fw-bold"></i> Live Webinars &amp; Talks</li>
                    <li class="mb-1.5"><i class="bi bi-check2 me-1.5 text-dark fw-bold"></i> Practical Hackathons</li>
                    <li class="mb-1.5"><i class="bi bi-check2 me-1.5 text-dark fw-bold"></i> Participation Badges</li>
                </ul>
                <a href="contact.php" class="btn btn-plan-dark w-100 text-center text-decoration-none">Contact Us</a>
            </div>

            <div class="cta-center-content mx-auto text-center px-3">
                <h2 class="cta-banner-heading fw-bold mb-3">
                    Stay connected with GD Edu Tech events.
                </h2>
                <p class="cta-banner-subtext text-muted mb-4">
                    Empowering students and working professionals through interactive workshops, expert webinars, and networking meetups.
                </p>
                <div class="d-flex flex-wrap gap-3 justify-content-center align-items-center">
                    <a href="./studentPanel/signup.php" class="btn btn-cta-main-pill text-decoration-none">
                        <span>Get Started</span>
                        <i class="bi bi-arrow-right ms-2 fs-5"></i>
                    </a>
                    <a href="contact.php" class="btn btn-cta-secondary-outline text-decoration-none">
                        <span>Contact Us</span>
                        <i class="bi bi-envelope-fill ms-2"></i>
                    </a>
                </div>
            </div>

            <div class="plan-card plan-card-right text-start d-none d-lg-block">
                <div class="plan-header mb-2">
                    <h6 class="plan-title fw-bold mb-1"><i class="bi bi-stars text-warning me-2"></i> Career Track</h6>
                    <p class="plan-subtext text-muted small mb-0">Mentorship for growth.</p>
                </div>
                <div class="plan-price mb-2">
                    <span class="price-num fw-bold fs-5 text-warning" style="color: #d97706 !important;">Job Ready</span>
                </div>
                <ul class="plan-features list-unstyled small mb-3" style="font-size: 0.82rem;">
                    <li class="mb-1.5"><i class="bi bi-diamond-fill me-1.5 text-dark small"></i> Industry Keynotes</li>
                    <li class="mb-1.5"><i class="bi bi-diamond-fill me-1.5 text-dark small"></i> 1-on-1 Q&amp;A Sessions</li>
                    <li class="mb-1.5"><i class="bi bi-diamond-fill me-1.5 text-dark small"></i> Placement Support</li>
                </ul>
                <a href="contact.php" class="btn btn-plan-dark w-100 text-center text-decoration-none">Contact Us</a>
            </div>

        </div>
    </section>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation -->
    <script src="https://cdnjs.cloudflare.com/aos/2.3.4/aos.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function(){
        const gridContainer = document.getElementById('eventsGridContainer');
        if (!gridContainer) return;

        // AJAX Category Filtering handler (No full page reload)
        document.addEventListener('click', async function(e) {
            const btn = e.target.closest('.category-filter-btn');
            if (!btn) return;
            e.preventDefault();

            const categoryId = btn.getAttribute('data-category-id') || '0';
            
            // Visual fade feedback
            gridContainer.style.opacity = '0.4';

            // Smooth URL update without page reload
            const newUrl = categoryId === '0' ? 'events.php' : 'events.php?category_id=' + categoryId;
            history.pushState({ categoryId }, '', newUrl);

            try {
                const res = await fetch('events.php?category_id=' + categoryId + '&ajax_filter=1');
                const html = await res.text();
                gridContainer.innerHTML = html.trim();
                gridContainer.style.opacity = '1';
                if (window.AOS) { AOS.refreshHard(); }
            } catch (err) {
                console.error('Error fetching filtered events:', err);
                gridContainer.style.opacity = '1';
            }
        });

        // Handle Browser Back / Forward buttons (Popstate)
        window.addEventListener('popstate', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const categoryId = urlParams.get('category_id') || '0';
            const targetBtn = document.querySelector(`.category-filter-btn[data-category-id="${categoryId}"]`);
            if (targetBtn) {
                targetBtn.click();
            }
        });

        // Load More pagination click handler
        gridContainer.addEventListener('click', async function(e){
            const btn = e.target.closest('.load-more-events');
            if (!btn) return;
            e.preventDefault();
            const status = btn.getAttribute('data-status');
            let nextPage = parseInt(btn.getAttribute('data-next-page') || '2', 10);
            const total = parseInt(btn.getAttribute('data-total') || '0', 10);
            
            let grid = btn.closest('.text-center').previousElementSibling;
            while (grid && !grid.classList.contains('row')) {
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
                const shown = grid.querySelectorAll('.col-lg-4.col-md-6').length;
                if (total && shown >= total) { btn.remove(); }
                btn.disabled = false; btn.textContent = original;
            } catch (err) {
                console.error(err);
                btn.disabled = false; btn.textContent = original;
            }
        });
    });
    </script>
</body>

</html>