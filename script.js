// Modern theme is now the default - NO LOCALSTORAGE

// Mobile Menu Toggle - FIXED FOR MOBILE
window.toggleMobileMenu = function() {
    console.log('toggleMobileMenu called!');
    const navLinks = document.getElementById('navLinks');
    const overlay = document.getElementById('mobileOverlay');

    console.log('Elements found:', {
        navLinks: !!navLinks,
        overlay: !!overlay
    });

    if (!navLinks || !overlay) {
        console.error('Navigation elements not found!', {navLinks, overlay});
        alert('Menu elements not found! Check console.');
        return;
    }

    const isActive = navLinks.classList.contains('active');
    console.log('Current state - isActive:', isActive);

    if (isActive) {
        // Close menu
        console.log('Closing menu...');
        navLinks.classList.remove('active');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
        console.log('Menu closed');
    } else {
        // Open menu
        console.log('Opening menu...');
        navLinks.classList.add('active');
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
        console.log('Menu opened');
    }
}

console.log('script.js loaded - toggleMobileMenu defined:', typeof window.toggleMobileMenu);

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded, initializing...');

    // Mobile menu - Close when clicking a link
    const navLinksEl = document.getElementById('navLinks');
    if (navLinksEl) {
        const links = navLinksEl.querySelectorAll('a');
        links.forEach(link => {
            link.addEventListener('click', function(e) {
                if (window.innerWidth <= 968) {
                    // Add delay to allow navigation to occur
                    setTimeout(function() {
                        window.toggleMobileMenu();
                    }, 300);
                }
            });
        });
    }

    // Mobile menu button
    const menuBtn = document.getElementById('mobileMenuBtn');
    console.log('Looking for menu button...', menuBtn);
    if (menuBtn) {
        console.log('Menu button found! Attaching event listener...');

        // Remove any existing listeners by cloning
        const newMenuBtn = menuBtn.cloneNode(true);
        menuBtn.parentNode.replaceChild(newMenuBtn, menuBtn);

        // Add click event
        newMenuBtn.addEventListener('click', function(e) {
            console.log('Menu button CLICKED!');
            e.preventDefault();
            e.stopPropagation();
            window.toggleMobileMenu();
        });

        // Add touch event for mobile
        newMenuBtn.addEventListener('touchend', function(e) {
            console.log('Menu button TOUCHED!');
            e.preventDefault();
            e.stopPropagation();
            window.toggleMobileMenu();
        });

        console.log('Event listeners attached to menu button');
    } else {
        console.error('Menu button NOT found!');
    }

    // Overlay click
    const overlay = document.getElementById('mobileOverlay');
    console.log('Looking for overlay...', overlay);
    if (overlay) {
        console.log('Overlay found! Attaching event listener...');
        overlay.addEventListener('click', function(e) {
            console.log('Overlay clicked!');
            e.preventDefault();
            window.toggleMobileMenu();
        });
        overlay.addEventListener('touchend', function(e) {
            console.log('Overlay touched!');
            e.preventDefault();
            window.toggleMobileMenu();
        });
    } else {
        console.error('Overlay NOT found!');
    }
});

// Smooth scrolling
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href === '#') return;
            
            e.preventDefault();
            const target = document.querySelector(href);
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});

// Form submission
const contactForm = document.querySelector('.contact-form');
if (contactForm) {
    contactForm.addEventListener('submit', function(e) {
        e.preventDefault();
        alert('Thank you for your message! We will get back to you soon.');
        this.reset();
    });
}

// Navbar scroll effect
let lastScroll = 0;
window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.navbar');
    if (!navbar) return;
    
    const currentScroll = window.pageYOffset;

    if (currentScroll > 100) {
        navbar.classList.add('scrolled');
        navbar.style.background = 'rgba(5, 8, 22, 0.98)';
        navbar.style.boxShadow = '0 5px 20px rgba(0, 255, 136, 0.1)';
    } else {
        navbar.classList.remove('scrolled');
        navbar.style.background = 'rgba(5, 8, 22, 0.95)';
        navbar.style.boxShadow = 'none';
    }

    lastScroll = currentScroll;
});

// Animate elements on scroll
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -100px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

document.querySelectorAll('.service-card, .feature, .testimonial-card').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(30px)';
    el.style.transition = 'all 0.6s ease-out';
    observer.observe(el);
});

// Typing effect
const subtitle = document.querySelector('.subtitle');
if (subtitle) {
    const text = subtitle.textContent;
    subtitle.textContent = '';
    let i = 0;

    setTimeout(() => {
        const typeInterval = setInterval(() => {
            if (i < text.length) {
                subtitle.textContent += text.charAt(i);
                i++;
            } else {
                clearInterval(typeInterval);
            }
        }, 50);
    }, 500);
}

// Stats animation
const statItems = document.querySelectorAll('.stat-item, .hero-stats .stat');
if (statItems.length > 0) {
    const statsObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, index * 100);
            }
        });
    }, { threshold: 0.5 });

    statItems.forEach(item => {
        item.style.opacity = '0';
        item.style.transform = 'translateY(20px)';
        item.style.transition = 'all 0.5s ease-out';
        statsObserver.observe(item);
    });
}
