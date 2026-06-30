<?php 

session_start();
require_once './Configurations/config.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - GD Edu Tech</title>
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
    <link rel="icon" href="/favicon.ico" type="image/x-icon">

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
            background:#f4f7fb;
            font-family:'Poppins',sans-serif;
            color:#111827;
        }

        /* HERO */
        .certificate-hero{
            background:linear-gradient(rgba(0,0,0,0.65), rgba(0,0,0,0.65)),
            url('./Images/Others/certificate-banner.jpg');
            background-size:cover;
            background-position:center;
            padding:120px 20px;
            text-align:center;
            color:white;
        }

        .certificate-hero h1{
            font-size:55px;
            font-weight:700;
            margin-bottom:15px;
        }

        .certificate-hero p{
            max-width:700px;
            margin:auto;
            font-size:17px;
            line-height:1.8;
            color:#e5e7eb;
        }

        /* SECTION */
        .certificate-section{
            padding:20px;
        }

        .section-heading{
            text-align:center;
            margin-bottom:60px;
        }

        .section-heading h2{
            font-size:42px;
            font-weight:700;
            margin-bottom:15px;
        }

        .section-heading p{
            color:#6b7280;
            max-width:650px;
            margin:auto;
            line-height:1.8;
        }

        /* CARD */
        .certificate-card{
            background:white;
            border-radius:22px;
            overflow:hidden;
            box-shadow:0 10px 35px rgba(0,0,0,0.08);
            transition:0.4s ease;
            height:100%;
            position:relative;
        }

        .certificate-card:hover{
            transform:translateY(-10px);
        }

        .certificate-image-wrapper{
            position:relative;
            overflow:hidden;
            background:white;
            padding:20px;
        }

        .certificate-image{
            width:100%;
            height:720px;
            object-fit:contain;
            transition:0.4s;
        }

        .certificate-card:hover .certificate-image{
            transform:scale(1.03);
        }

        .certificate-badge{
            position:absolute;
            top:18px;
            right:18px;
            background:#667eea;
            color:white;
            padding:8px 16px;
            border-radius:50px;
            font-size:13px;
            font-weight:600;
            letter-spacing:0.5px;
            z-index:10;
        }

        .certificate-content{
            padding:30px;
        }

        .certificate-content h3{
            font-size:28px;
            font-weight:700;
            margin-bottom:15px;
            text-align:center;
        }

        .certificate-content p{
            color:#6b7280;
            line-height:1.8;
            margin-bottom:25px;
            text-align:center;
        }

        .certificate-buttons{
            display:flex;
            gap:15px;
            /* flex-wrap:wrap;
             */
            align-items:center;
            justify-content:center;
        }

        .view-btn,
        .download-btn{
            text-decoration:none;
            padding:12px 24px;
            border-radius:12px;
            font-weight:600;
            transition:0.3s;
            display:inline-flex;
            align-items:center;
            gap:8px;
        }

        .view-btn{
            background:#667eea;
            color:white;
        }

        .view-btn:hover{
            background:#4f46e5;
            color:white;
        }

        .download-btn{
            background:#eef2ff;
            color:#4f46e5;
        }

        .download-btn:hover{
            background:#dbe4ff;
            color:#4f46e5;
        }

        /* RESPONSIVE */
        @media(max-width:768px){

            .certificate-hero{
                padding:90px 20px;
            }

            .certificate-hero h1{
                font-size:38px;
            }

            .section-heading h2{
                font-size:32px;
            }

            .certificate-image{
                height:300px;
            }

            .certificate-content h3{
                font-size:24px;
            }

        }

        .section-title {
    font-weight: 700;
    font-size: 2.5rem;
    background: linear-gradient(90deg, #00b6ff, #ff4300);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
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
                            <li class="breadcrumb-item active text-white" aria-current="page">Certification</li>
                        </ol>
                    </nav>
                    <h1 class="text-white display-5 fw-bold mb-3">Certifications</h1>
                    <p class="text-white-50 lead mb-0">Explore our certifications and official recognitions that reflect our commitment to quality education and professional excellence.
</p>
                </div>
                <div class="col-md-5" data-aos="fade-left">
                    <!-- <img src="./Images/Others/contact.png" alt="Contact Us" class="contact-hero-image"> -->
                </div>
            </div>
        </div>
        <div class="page-header-shape">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
                <path fill="#ffffff" fill-opacity="1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,112C672,96,768,96,864,112C960,128,1056,160,1152,160C1248,160,1344,128,1392,112L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
            </svg>
        </div>
    </section>

<!-- CERTIFICATES -->
<section class="certificate-section">

    <div class="container">

        <div class="section-heading">

                       <h2 class="section-heading text-center section-title" data-aos="fade-up">Recognized Certifications</h2>



        </div>

        <div class="row g-4 justify-content-center">

            <!-- CARD 1 -->
            <div class="col-lg-6 col-md-6">

                <div class="certificate-card">

                    <div class="certificate-image-wrapper">

                        <img
                            src="./assets/images/certificate.jpg"
                            class="certificate-image"
                            alt="Certificate"
                        >

                      
                    </div>

                    <div class="certificate-content">

                        <h3>Institute Certification</h3>

                        <p>
                            Official certification approved for our educational programs and skill development training initiatives.
                        </p>

                    

                    </div>

                </div>

            </div>

            <!-- CARD 2 -->
            <!-- <div class="col-lg-6 col-md-6">

                <div class="certificate-card">

                    <div class="certificate-image-wrapper">

                        <img
                            src="./Images/Others/certificate2.jpg"
                            class="certificate-image"
                            alt="Certificate"
                        >

                

                    </div>

                    <div class="certificate-content">

                        <h3>Training Accreditation</h3>

                        <p>
                            Accredited certification ensuring high-quality training standards and professional learning opportunities.
                        </p>


                    </div>

                </div>

            </div> -->

        </div>

    </div>

</section>

   <?php include 'footer.php'; ?>

</body>
</html>