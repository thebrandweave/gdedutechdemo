
        document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.querySelector('.sidebar-toggle');
        const sidebarOverlay = document.getElementById('sidebar-overlay');

        // Only add event listeners if elements exist
        if (sidebarToggle && sidebar && sidebarOverlay) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
            sidebarOverlay.classList.toggle('show');
            // Prevent content from shifting
            document.body.style.overflow = sidebar.classList.contains('show') ? 'hidden' : 'auto';
        });

        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
            // Reset body overflow
            document.body.style.overflow = 'auto';
        });

        // Close sidebar when a menu item is clicked
        const menuItems = document.querySelectorAll('.sidebar-menu .nav-link');
        menuItems.forEach(item => {
            item.addEventListener('click', function() {
                sidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
                // Reset body overflow
                document.body.style.overflow = 'auto';
            });
        });
        }
        });
