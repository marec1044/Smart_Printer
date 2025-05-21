/**
 * responsive.js - Core responsive functionality for Smart Printer website
 * This file handles mobile menu toggle and sidebar interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    // Get elements
    const menuToggle = document.querySelector('.menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    const closeSidebar = document.querySelector('.close-sidebar');
    const overlay = document.querySelector('.overlay');
    const sidebarLinks = document.querySelectorAll('.sidebar-nav a');
    
    // Toggle sidebar function
    function toggleSidebar() {
        sidebar.classList.toggle('open');
        overlay.classList.toggle('active');
        document.body.classList.toggle('no-scroll');
    }
    
    // Event listeners
    if (menuToggle) {
        menuToggle.addEventListener('click', toggleSidebar);
    }
    
    if (closeSidebar) {
        closeSidebar.addEventListener('click', toggleSidebar);
    }
    
    if (overlay) {
        overlay.addEventListener('click', toggleSidebar);
    }
    
    // Close sidebar when a link is clicked
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function() {
            toggleSidebar();
        });
    });
    
    // Close sidebar when Escape key is pressed
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && sidebar.classList.contains('open')) {
            toggleSidebar();
        }
    });
    
    // Handle scroll events for sticky navigation
    let lastScrollTop = 0;
    
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar');
        if (!navbar) return;
        
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        // Add shadow when scrolling down
        if (scrollTop > 10) {
            navbar.classList.add('navbar-scrolled');
        } else {
            navbar.classList.remove('navbar-scrolled');
        }
        
        // Auto-hide navbar when scrolling down (optional feature)
        // Uncomment this section if you want the navbar to hide when scrolling down
        /*
        if (scrollTop > lastScrollTop && scrollTop > 200) {
            navbar.style.top = '-80px';
        } else {
            navbar.style.top = '0';
        }
        lastScrollTop = scrollTop;
        */
    });
    
    // Handle resize events
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            // Close sidebar if screen is resized to desktop view
            if (sidebar.classList.contains('open')) {
                sidebar.classList.remove('open');
                overlay.classList.remove('active');
                document.body.classList.remove('no-scroll');
            }
        }
    });
});