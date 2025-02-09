export default class MobileNav {
  constructor() {
    this.burgerMenu = document.querySelector('.burger-menu');
    this.navButtons = document.querySelector('.nav-buttons');
    this.navOverlay = document.querySelector('.nav-overlay');
    this.imageModal = document.getElementById('imageModal');
    this.modalImg = document.getElementById('modalImage');
    this.modalCaption = document.getElementById('modalCaption');
    this.closeBtn = document.querySelector('.close-modal');
    this.planContainers = document.querySelectorAll('.plan-container');

    this.init();
  }

  init() {
    this.burgerMenu?.addEventListener('click', () => this.toggleNav());
    this.navOverlay?.addEventListener('click', () => this.closeNav());
    document.querySelectorAll('.nav-buttons a').forEach(link => {
      link.addEventListener('click', () => this.closeNav());
    });

    this.closeBtn?.addEventListener('click', () => this.closeModal());
    this.imageModal?.addEventListener('click', (e) => {
      if (e.target === this.imageModal) this.closeModal();
    });
    this.modalImg?.addEventListener('click', (e) => e.stopPropagation());

    document.addEventListener('keydown', (e) => this.handleKeyDown(e));

    this.updateYear();
    this.setupSmoothScrolling();
    this.createZoneMarkers();
  }

  handleKeyDown(e) {
    if (e.key === 'Escape') {
      this.closeNav();
      this.closeModal();
    }
  }

  toggleNav() {
    this.navButtons.classList.toggle('show');
    this.burgerMenu.classList.toggle('active');
    this.navOverlay.classList.toggle('active');
    document.body.classList.toggle('nav-active');
    this.navButtons.classList.contains('show') ? this.disableInteractions() : this.enableInteractions();
  }

  closeNav() {
    this.navButtons.classList.remove('show');
    this.burgerMenu.classList.remove('active');
    this.navOverlay.classList.remove('active');
    document.body.classList.remove('nav-active');
    this.enableInteractions();
  }

  disableInteractions() {
    const main = document.querySelector('main');
    if (main) main.style.pointerEvents = 'none';

    document.querySelectorAll('.modal, area[coords]').forEach(el => {
      el.style.pointerEvents = 'auto';
    });
  }

  enableInteractions() {
    const main = document.querySelector('main');
    if (main) main.style.pointerEvents = 'auto';
  }

  updateYear() {
    const yearSpan = document.getElementById('current-year');
    if (yearSpan) yearSpan.textContent = new Date().getFullYear();
  }

  setupSmoothScrolling() {
    document.querySelectorAll('.footer a').forEach(link => {
      link.addEventListener('click', (e) => {
        if (link.getAttribute('href') === '#') {
          e.preventDefault();
          window.scrollTo({ top: 0, behavior: 'smooth' });
        }
      });
    });
  }

  createZoneMarkers() {
    this.planContainers.forEach(container => {
      const img = container.querySelector('.plan-image');
      const mapName = img?.useMap.replace('#', '');
      const map = document.querySelector(`map[name="${mapName}"]`);

      if (!map) return;

      map.querySelectorAll('area').forEach(area => {
        const coords = area.getAttribute('coords').split(',').map(Number);
        if (coords.length < 4) return;

        const [x1, y1, x2, y2] = coords;
        const centerX = (x1 + x2) / 2;
        const centerY = (y1 + y2) / 2;

        const marker = document.createElement('div');
        marker.classList.add('zone-marker');
        container.appendChild(marker);

        const updateMarkerPosition = () => {
          const ratio = img.width / img.naturalWidth;
          marker.style.left = `${centerX * ratio}px`;
          marker.style.top = `${centerY * ratio}px`;
        };

        updateMarkerPosition();
        window.addEventListener('resize', updateMarkerPosition);

        marker.addEventListener('click', () => this.openModal(area));
      });
    });
  }

  openModal(area) {
    const imgSrc = area.getAttribute('data-img-src');
    const title = area.getAttribute('title');

    if (imgSrc) {
      this.modalImg.src = imgSrc;
      this.modalCaption.textContent = title;
      this.imageModal.style.display = 'block';
    }
  }

  closeModal() {
    this.imageModal.style.display = 'none';
  }
}
