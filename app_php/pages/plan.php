<?php
session_start();
include_once("../config/database.php");

// Définir le titre de la page
$page_title = "Plans - Bénin Tourisme";


include_once(__DIR__ . "/../includes/navbar.php"); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plans</title>
    <link rel="icon" type="image/svg+xml" href="../assets/favicon/favicon.svg">
    <link rel="icon" type="image/png" sizes="96x96" href="../assets/favicon/favicon-96x96.png">
    <link rel="shortcut icon" href="../assets/favicon/favicon.ico">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<style>
    .map-container {
            height: 500px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
</style>
<body>
    
</body>
</html>


 <!-- Google Maps Section -->
 <section id="carte" class="py-16 bg-white">
        <div class="container mx-auto px-6">
            <h2 class="text-3xl font-bold text-center text-green-800 mb-12">Explorez le Bénin sur la Carte</h2>
            <div class="map-container mb-8">
                <!-- Google Maps iframe -->
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d4047319.963345527!2d0.72212455!3d8.3076355!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x1034deb3127e6901%3A0x8b06b4a3a9c1389d!2sB%C3%A9nin!5e0!3m2!1sfr!2sfr!4v1689682880249!5m2!1sfr!2sfr" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-green-50 p-6 rounded-lg">
                    <div class="flex items-center mb-4">
                        <div class="bg-green-100 p-3 rounded-full mr-4">
                            <i class="fas fa-map-marker-alt text-green-700 text-xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-green-800">Points d'intérêt</h3>
                    </div>
                    <p class="text-gray-700">Localisez les sites touristiques majeurs, musées et monuments historiques à travers tout le pays.</p>
                </div>
                
                <div class="bg-yellow-50 p-6 rounded-lg">
                    <div class="flex items-center mb-4">
                        <div class="bg-yellow-100 p-3 rounded-full mr-4">
                            <i class="fas fa-hotel text-yellow-700 text-xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-yellow-800">Hébergements</h3>
                    </div>
                    <p class="text-gray-700">Trouvez les meilleurs hôtels, auberges et campements près de votre position ou destination.</p>
                </div>
                
                <div class="bg-blue-50 p-6 rounded-lg">
                    <div class="flex items-center mb-4">
                        <div class="bg-blue-100 p-3 rounded-full mr-4">
                            <i class="fas fa-utensils text-blue-700 text-xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-blue-800">Restauration</h3>
                    </div>
                    <p class="text-gray-700">Découvrez les restaurants et maquis proposant une cuisine locale authentique.</p>
                </div>
            </div>
        </div>
    </section>

<!-- Script pour la carte interactive -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation de la carte
    const map = L.map('map').setView([9.3077, 2.3158], 7);
    
    // Ajout du fond de carte
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    
    // Exemple de marqueurs (à remplacer par des données dynamiques)
    const markers = [
        { lat: 6.4969, lng: 2.6043, title: 'Cotonou', type: 'city' },
        { lat: 6.3176, lng: 2.4667, title: 'Ouidah', type: 'historic' },
        { lat: 7.1907, lng: 2.1000, title: 'Abomey', type: 'historic' }
    ];
    
    // Ajout des marqueurs sur la carte
    markers.forEach(marker => {
        L.marker([marker.lat, marker.lng])
            .bindPopup(marker.title)
            .addTo(map);
    });
    
    // Gestion des filtres
    const filterButtons = document.querySelectorAll('[data-filter]');
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const filter = this.dataset.filter;
            // Logique de filtrage à implémenter
        });
    });
});

        // Simple script for mobile menu toggle (could be enhanced)
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.querySelector('.md\\:hidden');
            const navLinks = document.querySelector('.md\\:flex');
            
            mobileMenuButton.addEventListener('click', function() {
                navLinks.classList.toggle('hidden');
                navLinks.classList.toggle('flex');
                navLinks.classList.toggle('flex-col');
                navLinks.classList.toggle('absolute');
                navLinks.classList.toggle('top-16');
                navLinks.classList.toggle('left-0');
                navLinks.classList.toggle('right-0');
                navLinks.classList.toggle('bg-green-800');
                navLinks.classList.toggle('p-4');
                navLinks.classList.toggle('space-y-4');
                navLinks.classList.toggle('space-x-8');
            });
            
            // Smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    
                    document.querySelector(this.getAttribute('href')).scrollIntoView({
                        behavior: 'smooth'
                    });
                    
                    // Close mobile menu if open
                    if (!navLinks.classList.contains('hidden')) {
                        mobileMenuButton.click();
                    }
                });
            });
        });
</script>

<?php include_once(__DIR__ . "/../includes/footer.php"); ?>

