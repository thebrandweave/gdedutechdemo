<?php
// Determine base URL path dynamically for absolute asset loading
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$basePath = ($scriptDir === '/' || $scriptDir === '\\') ? '' : $scriptDir;

$videoPath = $basePath . '/Images/Others/404.mp4';
$posterPath = $basePath . '/Images/Others/404.png';
$logoPath = $basePath . '/Images/Logos/GD_Only_logo.png';

// Determine if user is logged in and their role
session_start();
$redirect_url = $basePath . '/index.php';
if (isset($_SESSION['role'])) {
    switch(strtolower($_SESSION['role'])) {
        case 'admin':
            $redirect_url = $basePath . '/adminPanel/';
            break;
        case 'staff':
            $redirect_url = $basePath . '/staffPanel/';
            break;
        case 'student':
            $redirect_url = $basePath . '/studentPanel/';
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found | GD Edu Tech</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="<?php echo htmlspecialchars($logoPath); ?>">

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
            margin: 0;
            padding: 30px 15px;
            color: #ffffff;
        }

        .error-card {
            background: rgba(30, 41, 59, 0.85);
            backdrop-filter: blur(16px);
            border-radius: 28px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.35);
            border: 1px solid rgba(255, 255, 255, 0.1);
            max-width: 680px;
            width: 100%;
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .video-container {
            width: 100%;
            max-width: 440px;
            margin: 0 auto 24px auto;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            border: 2px solid rgba(255, 255, 255, 0.15);
            background: #000;
        }

        .video-404 {
            width: 100%;
            height: auto;
            display: block;
            object-fit: cover;
        }

        .error-title {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 8px;
            background: linear-gradient(135deg, #38bdf8 0%, #0d7298 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .error-description {
            color: #94a3b8;
            font-size: 0.95rem;
            max-width: 520px;
            margin: 0 auto 20px auto;
            line-height: 1.6;
        }

        .funny-messages {
            background: rgba(15, 23, 42, 0.6);
            border-radius: 14px;
            padding: 12px 20px;
            margin-bottom: 26px;
            border: 1px solid rgba(255, 255, 255, 0.06);
        }

        #randomMessage {
            margin: 0;
            font-size: 0.9rem;
            font-style: italic;
            color: #cbd5e1;
            transition: opacity 0.5s ease;
        }

        .btn-home {
            background: linear-gradient(135deg, #0d7298 0%, #065d7d 100%);
            color: #ffffff;
            padding: 13px 36px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 8px 25px rgba(13, 114, 152, 0.35);
            transition: all 0.3s ease;
        }

        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(13, 114, 152, 0.5);
            color: #ffffff;
        }
    </style>
</head>
<body>

    <div class="error-card">
        <!-- 404 Video Container -->
        <div class="video-container">
            <video id="video404" class="video-404" autoplay loop muted playsinline poster="<?php echo htmlspecialchars($posterPath); ?>">
                <source src="<?php echo htmlspecialchars($videoPath); ?>" type="video/mp4">
                <source src="./Images/Others/404.mp4" type="video/mp4">
                <source src="/Images/Others/404.mp4" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>

        <h2 class="error-title">Houston, We Have a Problem!</h2>
        <p class="error-description">
            Looks like you've ventured into the dark side of the internet. 
            The page you're looking for has probably gone to explore Mars.
        </p>

        <div class="funny-messages">
            <p id="randomMessage"></p>
        </div>

        <a href="<?php echo htmlspecialchars($redirect_url); ?>" class="btn-home">
            <i class="bi bi-rocket-takeoff-fill"></i> Beam Me Back Home
        </a>
    </div>

    <script>
        // Force Video Playback for Chrome/Edge Autoplay Policies
        document.addEventListener("DOMContentLoaded", function () {
            const video = document.getElementById("video404");
            if (video) {
                video.muted = true;
                video.play().catch(function(error) {
                    console.log("Autoplay exception handled:", error);
                });
            }
        });

        // Array of funny messages
        const funnyMessages = [
            "Plot twist: The page is not lost, it's just playing hide and seek... and winning! 🙈",
            "Error 404: Page got tired of waiting and went for coffee ☕",
            "Breaking News: Page last seen heading to Area 51 🛸",
            "This page has been abducted by aliens 👽 (We're working on intergalactic negotiations)",
            "The page you requested is currently on vacation in the Bermuda Triangle 🏖️",
            "Oops! Our hamsters powering the server needed a break 🐹",
            "This page has achieved enlightenment and transcended digital existence 🧘",
            "404: Page found... just kidding! Still looking 🔍",
            "The page was last seen chasing butterflies in /dev/null 🦋",
            "This page is experiencing an existential crisis. Please check back later 🤔"
        ];

        // Function to display random message
        function displayRandomMessage() {
            const messageElement = document.getElementById('randomMessage');
            if (messageElement) {
                messageElement.style.opacity = 0;
                setTimeout(() => {
                    const randomIndex = Math.floor(Math.random() * funnyMessages.length);
                    messageElement.textContent = funnyMessages[randomIndex];
                    messageElement.style.opacity = 1;
                }, 300);
            }
        }

        // Display initial message and change it every 5 seconds
        displayRandomMessage();
        setInterval(displayRandomMessage, 5000);
    </script>
</body>
</html>