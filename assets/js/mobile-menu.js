// Mobile Menu for Panel - NO LOCALSTORAGE

(function() {
    console.log('Panel mobile menu script loaded');

    function initMobileMenu() {
        console.log('Initializing mobile menu... Window width:', window.innerWidth);

        // Create mobile overlay if it doesn't exist
        if (!document.querySelector('.mobile-overlay')) {
            console.log('Creating mobile overlay...');
            const overlay = document.createElement('div');
            overlay.className = 'mobile-overlay';
            // Let CSS control positioning/opacity; keep pointer-events off until activated
            overlay.style.cssText = 'display:none;pointer-events:none;';
            document.body.appendChild(overlay);
            console.log('Overlay created with z-index 999');
        }

        const menuButton = document.querySelector('.mobile-menu-toggle');
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.mobile-overlay');

        console.log('Elements found:', {
            menuButton: !!menuButton,
            sidebar: !!sidebar,
            overlay: !!overlay
        });

        if (!menuButton || !sidebar || !overlay) {
            console.log('Menu elements not found - skipping mobile menu init');
            return;
        }

        // Show menu button on mobile
        if (window.innerWidth <= 768) {
            menuButton.style.display = 'block';
        }

        // Toggle function
        function toggleMenu(e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }

            const isOpen = sidebar.classList.contains('mobile-open');
            
            if (isOpen) {
                // Close menu
                console.log('Closing menu...');
                sidebar.classList.remove('mobile-open');
                sidebar.style.left = '';
                overlay.classList.remove('active');
                overlay.style.display = 'none';
                overlay.style.pointerEvents = 'none';
                document.body.classList.remove('menu-open');
                document.body.style.overflow = '';
                console.log('Menu closed');
            } else {
                // Open menu
                console.log('Opening menu...');
                sidebar.classList.add('mobile-open');
                sidebar.style.left = '0';
                overlay.classList.add('active');
                overlay.style.display = 'block';
                overlay.style.pointerEvents = 'auto';
                document.body.classList.add('menu-open');
                document.body.style.overflow = 'hidden';
                console.log('Menu opened');
            }
        }

        // Remove old listeners and add new ones
        const newMenuButton = menuButton.cloneNode(true);
        menuButton.parentNode.replaceChild(newMenuButton, menuButton);

        newMenuButton.addEventListener('click', toggleMenu);
        newMenuButton.addEventListener('touchend', function(e) {
            e.preventDefault();
            toggleMenu(e);
        });

        // Close menu when clicking overlay
        overlay.addEventListener('click', toggleMenu);
        overlay.addEventListener('touchend', function(e) {
            e.preventDefault();
            toggleMenu(e);
        });

        // Close menu when clicking a nav link on mobile
        const navLinks = sidebar.querySelectorAll('.nav-item');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    setTimeout(toggleMenu, 150);
                }
            });
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('mobile-open');
                sidebar.style.left = '';
                overlay.classList.remove('active');
                overlay.style.display = 'none';
                document.body.classList.remove('menu-open');
                document.body.style.overflow = '';
                newMenuButton.style.display = 'none';
            } else {
                newMenuButton.style.display = 'block';
            }
        });

        console.log('Mobile menu initialized successfully');
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMobileMenu);
    } else {
        initMobileMenu();
    }
})();
