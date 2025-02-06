document.addEventListener('DOMContentLoaded', function() {
    // Mobile navigation setup
    const burgerMenu = document.querySelector('.burger-menu');
    const navButtons = document.querySelector('.nav-buttons');
    const navOverlay = document.createElement('div');
    navOverlay.className = 'nav-overlay';
    document.body.appendChild(navOverlay);

    // Handle burger menu
    burgerMenu.addEventListener('click', function() {
        toggleMenu(true);
    });

    // Handle overlay click
    navOverlay.addEventListener('click', function() {
        toggleMenu(false);
    });

    // Handle navigation links
    const navLinks = document.querySelectorAll('.nav-buttons a');
    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            const href = link.getAttribute('href');
            if (href.includes('#')) {
                e.preventDefault();
                const section = href.split('#')[1];
                if (section === 'ete' || section === 'hiver') {
                    showTab(section);
                } else {
                    document.getElementById(section)?.scrollIntoView({ behavior: 'smooth' });
                }
            }
            toggleMenu(false);
        });
    });

    function toggleMenu(show) {
        navButtons.classList.toggle('show', show);
        burgerMenu.classList.toggle('active', show);
        navOverlay.classList.toggle('active', show);
        document.body.style.overflow = show ? 'hidden' : '';
    }

    // Tab functionality
    function showTab(season) {
        const sections = document.querySelectorAll('.activities');
        const tabs = document.querySelectorAll('.tab');
        
        sections.forEach(section => section.classList.add('hidden'));
        tabs.forEach(tab => tab.classList.remove('active'));
        
        document.getElementById(season)?.classList.remove('hidden');
        document.querySelector(`.tab[onclick="showTab('${season}')"]`)?.classList.add('active');
        
        const heroImage = document.querySelector('.hero__video.desktop');
        if (heroImage) {
            heroImage.src = season === 'ete' 
                ? './Assets Risoul/1657118599_image1.webp'
                : './Assets Risoul/risoul-le-domaine-skiable-ouvrira-bien-le-20-decembre.jpg';
        }

        // Update URL without page reload
        history.pushState(null, '', `#${season}`);
    }

    // Make showTab available globally
    window.showTab = showTab;

    // Initialize default tab or load from URL hash
    const initialTab = window.location.hash?.slice(1) || 'hiver';
    showTab(initialTab);
});