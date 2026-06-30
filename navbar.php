<style>
/* :root {
    --primary: #007bff; 
    --primary-dark: #0078a8;
    --text-dark: #333;
    --shadow: 0 4px 15px rgba(0,0,0,0.08);
    --shadow-md: 0 4px 20px rgba(0,0,0,0.12);
    --text-gradient: linear-gradient(45deg, #007bff, #00d4ff);
} */

/* Base Navigation Styles */
.navbar {
    padding: 5px 0;
    transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    background: rgba(255, 255, 255, 0.95);
    box-shadow: var(--shadow);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    position: fixed;
    z-index: 1000;
    top: 20px;
    width: 90%;
    left: 5%;
    border-radius: 100px;
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.navbar.scrolled {
    top: 0;
    left: 0;
    width: 100%;
    border-radius: 0;
    padding: 10px 0;
    background: rgba(255, 255, 255, 1);
    box-shadow: var(--shadow-md);
    border: none;
}

/* --- Interactive Navigation Links --- */
.navbar-nav {
    display: flex;
    gap: 8px;
}

.nav-link {
    color: var(--text-dark) !important;
    font-weight: 600;
    font-size: 0.92rem;
    padding: 8px 12px !important;
    transition: color 0.3s ease;
    position: relative; /* Required for underline positioning */
}

/* Sliding Underline Animation */
.nav-link::after {
    content: '';
    position: absolute;
    width: 0;
    height: 2px;
    bottom: 0;
    left: 50%;
    background: var(--primary);
    transition: all 0.3s cubic-bezier(0.645, 0.045, 0.355, 1);
    transform: translateX(-50%);
}

.nav-link:hover::after, 
.nav-link.active::after {
    width: 60%; /* Line grows from center outward */
}

.nav-link.active, .nav-link:hover {
    color: var(--primary) !important;
}



.nav-link-a {
    color: var(--text-dark) !important;
    display:flex;
    font-weight: 600;
    font-size: 0.92rem;
    padding: 10px 12px !important;
    transition: color 0.3s ease;
    position: relative; /* Required for underline positioning */
}



.nav-link-a:hover::after, 
.nav-link-a.active::after {
    width: 60%; /* Line grows from center outward */
}

.nav-link-a.active, .nav-link:hover {
    color: var(--primary) !important;
}









/* --- Interactive Action Buttons --- */
.navbar-actions {
    display: flex;
    align-items: center;
    gap: 12px;
}

.btn-login, .btn-signup {
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) !important; /* Springy feel */
}

.btn-login {
    padding: 6px 20px;
    border-radius: 50px;
    border: 1.5px solid var(--primary);
    color: var(--text-dark);
    text-decoration: none;
    font-weight: 600;
}

.btn-login:hover {
    background: var(--primary);
    color: white !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.2);
}

.btn-signup {
    padding: 6px 22px;
    border-radius: 50px;
    background: var(--primary);
    color: white !important;
    text-decoration: none;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-signup:hover {
    background: var(--primary-dark);
    transform: translateY(-2px) scale(1.03);
    box-shadow: 0 6px 15px rgba(0, 123, 255, 0.3);
}

/* --- Toggler & Mobile --- */
.navbar-toggler { border: none; }
.navbar-toggler-icon {
    display: block; width: 24px; height: 2px; background: var(--text-dark);
    position: relative; transition: background 0.3s ease;
}
.navbar-toggler-icon::before, .navbar-toggler-icon::after {
    content: ''; position: absolute; width: 24px; height: 2px; background: var(--text-dark); left: 0; transition: all 0.3s ease;
}
.navbar-toggler-icon::before { top: -8px; }
.navbar-toggler-icon::after { bottom: 8px; }

@media (max-width: 991px) {
    .navbar { width: 95%; left: 2.5%; border-radius: 30px; }
    .navbar-collapse { background: white; margin-top: 15px; border-radius: 20px; padding: 20px; box-shadow: var(--shadow-md); }
    .navbar-nav { flex-direction: column; gap: 10px; }
    .nav-link::after { display: none; } /* Disable underline on mobile for cleaner look */
    .navbar-actions { flex-direction: column; margin-top: 15px; }
    .btn-login, .btn-signup { width: 100%; text-align: center; justify-content: center; }
}
.nav-item.dropdown:hover .dropdown-menu {
    display: block;
    margin-top: 0;
     transition: 0.3s;
}

.dropdown-menu {
    border-radius: 4px;
    border: none;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    padding: 10px 0;
}

.dropdown-item {
    padding: 10px 18px;
    transition: 0.3s;
    font-weight:600;
    font-size:0.92rem;
}

.dropdown-item:hover {
    background: #f3f4f6;
    color: #0078a8;
}

</style>

<nav class="navbar navbar-expand-lg" id="dynamicNavbar">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <img src="./Images/Logos/GD_Full_logo.png" alt="Logo" style="height: 40px;">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">Home</a>
                </li>
            <li class="nav-item dropdown">

    <a
        class="nav-link-a  <?php echo (basename($_SERVER['PHP_SELF']) == 'about.php' || basename($_SERVER['PHP_SELF']) == 'our-story.php' || basename($_SERVER['PHP_SELF']) == 'certification.php') ? 'active' : ''; ?>"
        href="#"
        id="aboutDropdown"
        role="button"
        data-bs-toggle="dropdown"
        aria-expanded="false"
    >
        About
    </a>

    <ul class="dropdown-menu">

    

        <li>
            <a class="dropdown-item" href="about.php">
                Our Story
            </a>
        </li>

        <li>
            <a class="dropdown-item" href="certification.php">
                Certification
            </a>
        </li>

    </ul>

</li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'courses.php' ? 'active' : ''; ?>" href="courses.php">Courses</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'events.php' ? 'active' : ''; ?>" href="events.php">Events</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'career.php' ? 'active' : ''; ?>" href="career.php">Career</a>
                </li>
         
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>" href="contact.php">Contact</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'scholarship.php' ? 'active' : ''; ?>" href="scholarship.php">Apply Scholarship</a>
                </li>
            </ul>
            <div class="navbar-actions ms-lg-4">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="./studentPanel/" class="btn-login">Dashboard</a>
                <?php else: ?>
                    <a href="./studentPanel/login.php" class="btn-login">Login</a>
                    <a href="./studentPanel/signup.php" class="btn-signup">Sign Up <i class="bi bi-arrow-right"></i></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const nav = document.getElementById('dynamicNavbar');
    function handleScroll() {
        if (window.scrollY > 40) {
            nav.classList.add('scrolled');
        } else {
            nav.classList.remove('scrolled');
        }
    }
    window.addEventListener('scroll', handleScroll);
    handleScroll();
});
</script>