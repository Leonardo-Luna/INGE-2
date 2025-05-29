document.addEventListener('DOMContentLoaded', function() {
    const map = L.map('map').setView([-34.9213, -57.9544], 13);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);

    L.marker([-34.9213, -57.9544]).addTo(map)
        .bindPopup('¡Este es el centro de La Plata!')
        .openPopup();
});