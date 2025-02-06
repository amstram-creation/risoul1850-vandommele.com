/* ==========================================================================
Mobile Navigation
========================================================================== */

document.addEventListener('DOMContentLoaded', function () {
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
    // Disables interactions on the main content
    const main = document.querySelector('main');
    if (main) {
      main.style.pointerEvents = 'none';
    }

    // Keep interactions active for the modal and the calendar
    const interactiveElements = document.querySelectorAll(
      '.modal, .calendar-container, area[coords]'
    );
    interactiveElements.forEach((el) => {
      el.style.pointerEvents = 'auto';
    });
  }

  function enableInteractions() {
    // Re-enables all interactions
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
  navLinks.forEach((link) => {
    link.addEventListener('click', closeNav);
  });

  // Gestion des événements pour le modal
  const imageModal = document.getElementById('imageModal');
  if (imageModal) {
    imageModal.addEventListener('click', (e) => {
      e.stopPropagation();
    });
  }

  // Met à jour l'année dans le copyright
  const yearSpan = document.getElementById('current-year');
  if (yearSpan) {
    yearSpan.textContent = new Date().getFullYear();
  }

  // Adds smooth scrolling to the top when footer links are clicked
  const footerLinks = document.querySelectorAll('.footer a');
  footerLinks.forEach((link) => {
    link.addEventListener('click', function (e) {
      // If the link URL is "#", scroll to the top
      if (this.getAttribute('href') === '#') {
        e.preventDefault();
        window.scrollTo({
          top: 0,
          behavior: 'smooth',
        });
      }
    });
  });

  const modalImg = document.getElementById('modalImage');
  const modalCaption = document.getElementById('modalCaption');
  const closeBtn = document.querySelector('.close-modal');
  const planContainers = document.querySelectorAll('.plan-container');
  function updateMarkerPosition() {
    const ratio = img.width / img.naturalWidth;
    marker.style.left = `${centerX * ratio}px`;
    marker.style.top = `${centerY * ratio}px`;
  }
  function createZoneMarkers() {
    planContainers.forEach((container) => {
      const img = container.querySelector('.plan-image');
      const mapName = img.useMap.replace('#', '');
      const map = document.querySelector(`map[name="${mapName}"]`);

      if (!map) return;

      map.querySelectorAll('area').forEach((area) => {
        const coords = area.getAttribute('coords').split(',').map(Number);
        if (coords.length < 4) return;

        const centerX = (coords[0] + coords[2]) / 2;
        const centerY = (coords[1] + coords[3]) / 2;

        const marker = document.createElement('div');
        marker.classList.add('zone-marker');
        container.appendChild(marker);

        updateMarkerPosition();
        window.addEventListener('resize', updateMarkerPosition);

        // Ouvrir le modal au clic sur le marqueur
        marker.addEventListener('click', function () {
          const imgSrc = area.getAttribute('data-img-src');
          const title = area.getAttribute('title');

          if (imgSrc) {
            modalImg.src = imgSrc;
            modalCaption.textContent = title;
            imageModal.style.display = 'block';
          }
        });
      });
    });
  }

  createZoneMarkers();
  // Fermer la modal au clic sur la croix
  closeBtn.addEventListener('click', function () {
    imageModal.style.display = 'none';
  });

  // Fermer la modal en cliquant à l'extérieur
  imageModal.addEventListener('click', function (e) {
    if (e.target === imageModal) {
      imageModal.style.display = 'none';
    }
  });

  // Empêcher la propagation du clic sur l'image
  modalImg.addEventListener('click', function (e) {
    e.stopPropagation();
  });
});
