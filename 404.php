<?php
// Get the referring URL
$referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;

// Determine if user is logged in and their role
session_start();
$redirect_url = 'index.php';
if (isset($_SESSION['role'])) {
    switch(strtolower($_SESSION['role'])) {
        case 'admin':
            $redirect_url = '/adminPanel/';
            break;
        case 'staff':
            $redirect_url = '/staffPanel/';
            break;
        case 'student':
            $redirect_url = '/studentPanel/';
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Arial', sans-serif;
        }

        .error-container {
            text-align: center;
            padding: 2rem;
            max-width: 600px;
        }

        .error-code {
            font-size: 120px;
            font-weight: bold;
            color: #2C3E50;
            margin: 0;
            line-height: 1;
            animation: bounce 2s infinite;
        }

        .error-message {
            font-size: 24px;
            color: #34495E;
            margin: 20px 0;
            animation: fadeIn 1s ease-in;
        }

        .error-description {
            color: #7F8C8D;
            margin-bottom: 30px;
            animation: slideUp 1s ease-out;
        }

        .lost-astronaut {
            width: 150px;
            margin: 20px 0;
            animation: float 6s ease-in-out infinite;
        }

        .btn-home {
            background: #2C3E50;
            color: white;
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.3s ease;
            animation: fadeIn 2s ease-in;
        }

        .btn-home:hover {
            background: #34495E;
            transform: translateY(-2px);
            color: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }


        @keyframes float {
            0% {
                transform: translateY(0px) rotate(0deg);
            }
            50% {
                transform: translateY(-20px) rotate(5deg);
            }
            100% {
                transform: translateY(0px) rotate(0deg);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .funny-messages {
            margin: 20px 0;
            font-style: italic;
            color: #95A5A6;
            animation: fadeIn 2s ease-in;
        }

        #randomMessage {
            margin: 10px 0;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <img src="/Images/Others/404.png" 
             alt="Lost Astronaut" 
             class="lost-astronaut">
        <h2 class="error-message">Houston, We Have a Problem!</h2>
        <p class="error-description">
            Looks like you've ventured into the dark side of the internet. 
            The page you're looking for has probably gone to explore Mars.
        </p>
        <div class="funny-messages">
            <p id="randomMessage"></p>
        </div>
        <a href="<?php echo $redirect_url; ?>" class="btn btn-home">
            Beam Me Back Home
        </a>
    </div>

    <script>
        // Array of funny messages
        const funnyMessages = [
            "Plot twist: The page is not lost, it's just playing hide and seek... and winning!",
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
            const randomIndex = Math.floor(Math.random() * funnyMessages.length);
            messageElement.textContent = funnyMessages[randomIndex];
        }

        // Display initial message and change it every 5 seconds
        displayRandomMessage();
        setInterval(displayRandomMessage, 5000);
    </script>
</body>
</html> 