
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImage');
    const modalCaption = document.getElementById('modalCaption');
    const closeBtn = document.querySelector('.close-modal');
    const planContainers = document.querySelectorAll('.plan-container');

    function createZoneMarkers() {
        planContainers.forEach(container => {
            const img = container.querySelector('.plan-image');
            const mapName = img.useMap.replace('#', '');
            const map = document.querySelector(`map[name="${mapName}"]`);
            
            if (!map) return;

            map.querySelectorAll('area').forEach(area => {
                const coords = area.getAttribute('coords').split(',').map(Number);
                if (coords.length < 4) return;

                const centerX = (coords[0] + coords[2]) / 2;
                const centerY = (coords[1] + coords[3]) / 2;

                const marker = document.createElement('div');
                marker.classList.add('zone-marker');
                container.appendChild(marker);

                function updateMarkerPosition() {
                    const ratio = img.width / img.naturalWidth;
                    marker.style.left = `${centerX * ratio}px`;
                    marker.style.top = `${centerY * ratio}px`;
                }

                updateMarkerPosition();
                window.addEventListener('resize', updateMarkerPosition);

                // Ouvrir le modal au clic sur le marqueur
                marker.addEventListener('click', function() {
                    const imgSrc = area.getAttribute('data-img-src');
                    const title = area.getAttribute('title');

                    if (imgSrc) {
                        modalImg.src = imgSrc;
                        modalCaption.textContent = title;
                        modal.style.display = 'block';
                    }
                });
            });
        });
    }
    
    createZoneMarkers();
    // Fermer la modal au clic sur la croix
    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });

    // Fermer la modal en cliquant à l'extérieur
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });

    // Empêcher la propagation du clic sur l'image
    modalImg.addEventListener('click', function(e) {
        e.stopPropagation();
    });
});