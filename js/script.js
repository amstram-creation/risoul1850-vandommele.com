/* ==========================================================================
Mobile Navigation
========================================================================== */

document.addEventListener('DOMContentLoaded', function() {
    const burgerMenu = document.querySelector('.burger-menu');
    const navButtons = document.querySelector('.nav-buttons');
    const navOverlay = document.querySelector('.nav-overlay');
    
    function closeNav() {
        navButtons.classList.remove('show');
        burgerMenu.classList.remove('active');
        navOverlay.classList.remove('active');
        document.body.classList.remove('nav-active');
        enableInteractions();
    }
    
    function openNav() {
        navButtons.classList.add('show');
        burgerMenu.classList.add('active');
        navOverlay.classList.add('active');
        document.body.classList.add('nav-active');
        disableInteractions();
    }
    
    function toggleNav() {
        if (navButtons.classList.contains('show')) {
            closeNav();
        } else {
            openNav();
        }
    }
    
    function disableInteractions() {
        // Désactive les interactions sur le contenu principal
        const main = document.querySelector('main');
        if (main) {
            main.style.pointerEvents = 'none';
        }
        
        // Garde les interactions actives pour le modal et le calendrier
        const interactiveElements = document.querySelectorAll('.modal, .calendar-container, area[coords]');
        interactiveElements.forEach(el => {
            el.style.pointerEvents = 'auto';
        });
    }
    
    function enableInteractions() {
        // Réactive toutes les interactions
        const main = document.querySelector('main');
        if (main) {
            main.style.pointerEvents = 'auto';
        }
    }
    
    // Event Listeners
    burgerMenu.addEventListener('click', toggleNav);
    navOverlay.addEventListener('click', closeNav);
    
    // Ferme la navigation lors du clic sur un lien
    const navLinks = document.querySelectorAll('.nav-buttons a');
    navLinks.forEach(link => {
        link.addEventListener('click', closeNav);
    });
    
    // Gestion des événements pour le modal
    const modal = document.getElementById('imageModal');
    if (modal) {
        modal.addEventListener('click', (e) => {
            e.stopPropagation();
        });
    }
});


/* ==========================================================================
Year update
========================================================================== */


document.addEventListener('DOMContentLoaded', function() {
    // Met à jour l'année dans le copyright
    const yearSpan = document.getElementById('current-year');
    if (yearSpan) {
        yearSpan.textContent = new Date().getFullYear();
    }

    // Ajoute un défilement fluide vers le haut lorsque les liens du footer sont cliqués
    const footerLinks = document.querySelectorAll('.footer a');
    footerLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Si l'URL du lien est "#", on fait défiler en haut
            if (this.getAttribute('href') === '#') {
                e.preventDefault();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }
        });
    });
});