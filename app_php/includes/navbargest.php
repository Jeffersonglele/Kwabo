<?php
// Démarrage systématique de session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Chemin de base absolu
$base_path = '/'; // À adapter selon votre configuration

// Détermination de la page active
$current_script = basename($_SERVER['SCRIPT_NAME']);
$current_page = str_replace('.php', '', $current_script);

// Menu unifié avec icônes et restrictions de type de compte
$menu_items = [
    'destinations' => [
        'title' => 'Destinations', 
        'url' => '../gestionnaire/destinations.php', 
        'icon' => 'fa-map-marked-alt',
        'allowed_types' => ['destination']
    ],
    'hotels' => [
        'title' => 'Hôtels', 
        'url' => '../gestionnaire/hotels.php', 
        'icon' => 'fa-hotel',
        'allowed_types' => ['hotel']
    ],
    'circuits' => [
        'title' => 'Circuits', 
        'url' => '../gestionnaire/circuits.php', 
        'icon' => 'fa-route',
        'allowed_types' => ['circuit']
    ],
    'evenements' => [
        'title' => 'Tableau de bord', 
        'url' =>'../gestionnaire/tableau_bord.php', 
        'icon' => 'fa-tachometer-alt',
        'allowed_types' => ['destination', 'hotel', 'circuit']
    ]
];

// Filtrer les éléments de menu en fonction du type de compte
$filtered_menu_items = [];
$user_type = $_SESSION['gestionnaire_type'] ?? null;

foreach ($menu_items as $key => $item) {
    if (in_array($user_type, $item['allowed_types']) || $user_type === null) {
        $filtered_menu_items[$key] = $item;
    }
}

// Remplacer le menu complet par le menu filtré
$menu_items = $filtered_menu_items;

// Pages où afficher les boutons de connexion
$auth_pages = ['evenements','hotels','circuits', 'destinations'];
$show_auth_buttons = in_array($current_page, $auth_pages);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bénin Tourisme</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .dropdown-menu {
            display: none;
            opacity: 0;
            transform: translateY(-10px);
            transition: opacity 0.2s ease, transform 0.2s ease;
        }
        
        .dropdown:hover .dropdown-menu,
        .dropdown:focus-within .dropdown-menu {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }
        
        /* Fix pour le z-index */
        nav {
            z-index: 1000;
        }
        
        .dropdown-menu {
            z-index: 1001;
        }
        
        /* Compensation pour la navbar fixe */
        body {
            padding-top: 4rem;
        }
        
        /* Ajustement pour les petits écrans */
        @media (max-width: 640px) {
            .auth-buttons-mobile {
                display: flex !important;
                flex-direction: column;
                padding: 0.5rem 0;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navbar Structure -->
    <nav class="bg-white shadow-lg fixed top-0 w-full">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-16">

                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="tableau_bord.php" class="flex items-center no-underline">
                        <img src="<?= $base_path ?>assets/images/KWA.png" 
                             alt="Bénin Tourisme" 
                             class="h-10 w-auto"
                             onerror="this.src='<?= $base_path ?>assets/images/default-logo.png'">
                        <span class="ml-2 text-xl font-bold text-gray-800">KWABO- Gestionnaire</span>
                    </a>
                </div>

                <!-- Menu Desktop -->
                <div class="hidden md:flex items-center space-x-1">
                    <?php foreach ($menu_items as $page => $item): ?>
                        <a href="<?= $item['url'] ?>" 
                           class="<?= ($current_page === $page) ? 'bg-gray-100 text-primary' : 'text-gray-600 hover:bg-gray-50' ?> 
                                  px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200 flex items-center">
                            <i class="fas <?= $item['icon'] ?> mr-2 text-sm"></i>
                            <?= $item['title'] ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <!-- Section Utilisateur -->
                <div class="flex items-center ml-4">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="dropdown relative">
                            <button class="flex items-center space-x-2 focus:outline-none">
                                <span class="text-gray-700">
                                    <?= htmlspecialchars($_SESSION['user_prenom'] ?? 'Utilisateur') ?>
                                </span>
                                <img src="<?= $base_path ?>assets/images/users.jpg" 
                                     alt="Photo de profil" 
                                     class="h-8 w-8 rounded-full border-2 border-gray-200 object-cover"
                                     onerror="this.src='<?= $base_path ?>assets/images/default-user.png'">
                            </button>
                            <div class="dropdown-menu absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1">
                                <a href="<?= $base_path ?>pages/deconnexion.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Déconnexion
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Bouton Mobile -->
                    <button id="mobile-menu-button" class="md:hidden text-gray-500 hover:text-gray-900 focus:outline-none ml-4">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Menu Mobile -->
        <div id="mobile-menu" class="md:hidden hidden bg-white border-t">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <?php foreach ($menu_items as $page => $item): ?>
                    <a href="<?= $item['url'] ?>" 
                       class="<?= ($current_page === $page) ? 'bg-gray-100 text-primary' : 'text-gray-600 hover:bg-gray-50' ?> 
                              group flex items-center px-3 py-2 text-sm font-medium rounded-md">
                        <i class="fas <?= $item['icon'] ?> w-5 h-5 mr-3 text-gray-400 group-hover:text-primary"></i>
                        <?= $item['title'] ?>
                    </a>
                <?php endforeach; ?>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="pt-4 border-t">
                        <div class="px-3 py-2 flex items-center text-gray-800">
                            <img src="<?= $base_path ?>assets/images/users.jpg" 
                                 alt="Photo de profil" 
                                 class="h-8 w-8 rounded-full border-2 border-gray-200 object-cover mr-3"
                                 onerror="this.src='<?= $base_path ?>assets/images/default-user.png'">
                            <?= htmlspecialchars($_SESSION['user_prenom'] ?? 'Utilisateur') ?>
                        </div>
                        <a href="<?= $base_path ?>../gestionnaire/deconnexion.php" class="block px-3 py-2 text-gray-600 hover:bg-gray-50 flex items-center pl-11">
                            <i class="fas fa-sign-out-alt mr-3 w-5 text-center"></i>Déconnexion
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <script>
        // Gestion du menu mobile
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            
            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function(e) {
                    e.stopPropagation();
                    mobileMenu.classList.toggle('hidden');
                });
            }

            // Fermer le menu au clic ailleurs
            document.addEventListener('click', function() {
                if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
                    mobileMenu.classList.add('hidden');
                }
            });

            // Empêcher la fermeture quand on clique dans le menu
            if (mobileMenu) {
                mobileMenu.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }
        });
    </script>