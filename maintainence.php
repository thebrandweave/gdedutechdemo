<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>We'll Be Back Soon | GD Edu Tech</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- AOS Animation Library -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="./css/index.css">
    <link rel="icon" href="./Images/Logos/edutechLogo.png" type="image/png">
    <style>
        :root {
            --primary: #0078a8;
            --primary-dark: #065d7d;
            --secondary: #1d91bb;
            --accent: #cb4d55;
            --accent-secondary: #d6624e;
            --dark: #111827;
            --light: #f8f9fa;
            --text-dark: #2d3436;
            --text-light: #636e72;
            --text-white: #ffffff;
            --background-gradient: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            --text-gradient: linear-gradient(90deg, var(--primary), var(--secondary));
            --accent-gradient: linear-gradient(90deg, var(--accent), var(--accent-secondary));
        }
        
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            background-color: var(--light);
            position: relative;
            overflow: hidden;
        }

        #particles-js {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 0;
        }

        .shape {
            position: absolute;
            z-index: -1;
            opacity: 0.4;
        }
        .maintenance-container {
            max-width: 600px;
            padding: 40px;
            background: transparent;
            border-radius: 20px;
            animation: fadeIn 1.5s ease-in-out;
            z-index: 10;
            margin: 20px;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            font-weight: 700;
            color: var(--text-dark);
            position: relative;
            display: inline-block;
        }

        /* h1:after {
            content: '';
            position: absolute;
            width: 50%;
            height: 4px;
            background: var(--text-gradient);
            bottom: -10px;
            left: 25%;
            border-radius: 2px;
        } */

        p {
            font-size: 1.1rem;
            margin-bottom: 25px;
            line-height: 1.6;
            color: var(--text-light);
        }

        .loader {
            display: inline-block;
            width: 80px;
            height: 80px;
            margin: 20px auto;
            position: relative;
        }

        .loader:after {
            content: '';
            display: block;
            width: 64px;
            height: 64px;
            margin: 8px;
            border-radius: 50%;
            border: 6px solid var(--primary);
            border-color: var(--primary) transparent var(--primary) transparent;
            animation: spin 1.2s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .contact-link {
            display: inline-block;
            padding: 10px 25px;
            background: var(--background-gradient);
            color: white;
            border-radius: 50px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(13, 114, 152, 0.3);
        }

        .contact-link:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(13, 114, 152, 0.4);
            color: white;
        }

        footer {
            margin-top: 40px;
            font-size: 0.9rem;
            color: var(--text-light);
        }
    </style>
</head>
<body>
    <div id="particles-js"></div>
    
    <div class="maintenance-container" data-aos="fade-up" data-aos-duration="1000">
        <img src="./Images/Logos/edutechLogo.png" alt="GD Edu Tech Logo" width="120" style="margin-bottom:25px;">
        <h1>Maintenance Mode</h1>
        <p>
            We'll Be Back Soon!
        </p>
        <div class="loader"></div>
        <p>If you need assistance, feel free to contact us:</p>
        <a href="tel:+91 7204626299" class="contact-link">
            <i class="bi bi-envelope-fill me-2"></i>Contact Support
        </a>
    </div>
    
    <footer>
        &copy; <?php echo date('Y'); ?> GD Edu Tech. All rights reserved.
    </footer>

    <!-- Particles.js -->
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <!-- AOS Animation Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        // Initialize AOS
        AOS.init();
        
        // Initialize particles.js
        particlesJS('particles-js', {
            "particles": {
                "number": {
                    "value": 80,
                    "density": {
                        "enable": true,
                        "value_area": 800
                    }
                },
                "color": {
                    "value": "#808080"
                },
                "shape": {
                    "type": "circle",
                    "stroke": {
                        "width": 0,
                        "color": "#000000"
                    },
                },
                "opacity": {
                    "value": 0.3,
                    "random": false,
                },
                "size": {
                    "value": 3,
                    "random": true,
                },
                "line_linked": {
                    "enable": true,
                    "distance": 150,
                    "color": "#808080",
                    "opacity": 0.3,
                    "width": 1
                },
                "move": {
                    "enable": true,
                    "speed": 2,
                    "direction": "none",
                    "random": false,
                    "straight": false,
                    "out_mode": "out",
                    "bounce": false,
                }
            },
            "interactivity": {
                "detect_on": "canvas",
                "events": {
                    "onhover": {
                        "enable": true,
                        "mode": "repulse",
                        "color": "#0078a8"
                    },
                    "onclick": {
                        "enable": true,
                        "mode": "push"
                    },
                    "resize": true
                },
                "modes": {
                    "grab": {
                        "distance": 140,
                        "line_linked": {
                            "opacity": 1,
                            "color": "#0078a8"
                        }
                    },
                    "repulse": {
                        "distance": 100,
                        "color": "#0078a8"
                    },
                    "push": {
                        "particles_nb": 4
                    }
                }
            },
            "retina_detect": true
        });
    </script>
</body>
</html>
