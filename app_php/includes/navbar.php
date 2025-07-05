<?php

require_once (__DIR__ . '/../config/database.php');
if (session_status() === PHP_SESSION_NONE) {
   
}
// Chemin de base absolu
$base_path = '/'; // Assurez-vous que ce chemin est correct
 // Démarrer la session en haut du fichier


// Détermination de la page active
$current_script = basename($_SERVER['SCRIPT_NAME']);
$current_page = str_replace('.php', '', $current_script);

// Menu unifié avec icônes
$menu_items = [
    'explorer' => [
        'title' => 'Explorer', 
        'icon' => 'fa-compass',
        'submenu' => [
            'destinations' => ['title' => 'Destinations', 'url' => $base_path . 'pages/destinations.php', 'icon' => 'fa-map-marked-alt'],
            'hotels' => ['title' => 'Hôtels', 'url' => $base_path . 'pages/hotel.php', 'icon' => 'fa-hotel'],
            'circuits' => ['title' => 'Circuits', 'url' => $base_path . 'pages/circuits.php', 'icon' => 'fa-route'],
            'evenements' => ['title' => 'Evènements', 'url' => $base_path . 'pages/evenements.php', 'icon' => 'fa-calendar-alt']
        ]
    ],
    'blog' => ['title' => 'Blog', 'url' => $base_path . 'pages/blog.php', 'icon' => 'fa-blog'],
    'contact' => ['title' => 'Contact', 'url' => $base_path . 'pages/contact.php', 'icon' => 'fa-envelope'],
    'gestionnaire' => ['title' => 'Gestionnaire', 'url' => $base_path . 'pages/gestion.php', 'icon' => 'fa-user-tie'],
    'qui sommes-nous' => ['title' => 'Qui sommes-nous', 'url' => $base_path . 'pages/nous.php', 'icon' => 'fa-users'],
];

// Pages où afficher les boutons de connexion
$auth_pages = ['blog','gestionnaire'];
$show_auth_buttons = in_array($current_page, $auth_pages);

// Vérifier si l'utilisateur est connecté
$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';

// Récupérer les informations de l'utilisateur si connecté
$user_name = '';
if ($isLoggedIn && !$isAdmin) {
    

    // Vérifier si l'utilisateur existe encore en base
    $stmt = $pdo->prepare("SELECT prenom, nom FROM utilisateurs WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if ($user) {
        $user_name = htmlspecialchars($user['prenom'] . ' ' . $user['nom']);
    } else {
        // Utilisateur supprimé : détruire session et rediriger
        session_unset();
        session_destroy();
        header("Location: connexion.php?message=compte_supprime");
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KWABO - Tourisme</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio,line-clamp"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        premium: {
                            gold: '#D4AF37',
                            dark: '#1A1A1A',
                            light: '#F5F5F5',
                            accent: '#2C5282',
                        }
                    },
                    fontFamily: {
                        sans: ['"DM Sans"', 'sans-serif'],
                        serif: ['"Playfair Display"', 'serif'],
                    },
                    boxShadow: {
                        'gold-glow': '0 0 15px rgba(212, 175, 55, 0.3)',
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --navbar-height: 100px;
            --gold-gradient: linear-gradient(135deg, #D4AF37 0%, #F9D423 100%);
        }
        
        [x-cloak] { display: none !important; }
        
        body {
            font-family: 'DM Sans', sans-serif;
            padding-top: var(--navbar-height);
            background-color: #FAFAFA;
            color: #333;
            transition: background-color 0.5s ease;
        }
        
        body.dark {
            background-color: #1A1A1A;
            color: #F5F5F5;
        }
        
        /* Navbar premium */
        .premium-navbar {
            height: var(--navbar-height);
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
        }
        
        .dark .premium-navbar {
            background: rgba(26, 26, 26, 0.98);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .premium-navbar.scrolled {
            height: 75px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
        }
        
        /* Logo */
        .premium-logo {
            transition: all 0.4s ease;
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            letter-spacing: 0.5px;
            position: relative;
            width: 300px; 
            height: auto;
        }
        
        .premium-logo::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 100%;
            height: 2px;
            background: var(--gold-gradient);
            transform: scaleX(0);
            transform-origin: right;
            transition: transform 0.4s ease;
        }
        
        .premium-logo:hover::after {
            transform: scaleX(1);
            transform-origin: left;
        }
        
        .premium-navbar.scrolled .premium-logo {
            transform: scale(0.95);
        }
        
        /* Items de menu */
        .premium-nav-item {
            position: relative;
            padding: 0.75rem 1.25rem;
            font-weight: 500;
            color: #444;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            letter-spacing: 0.3px;
        }
        
        .dark .premium-nav-item {
            color: #E5E5E5;
        }
        
        .premium-nav-item:hover {
            color: #D4AF37;
        }
        
        .premium-nav-item.active {
            color: #2C5282;
        }
        
        .dark .premium-nav-item.active {
            color: #D4AF37;
        }
        
        .premium-nav-item.active::after {
            content: '';
            position: absolute;
            bottom: 8px;
            left: 1.25rem;
            right: 1.25rem;
            height: 2px;
            background: var(--gold-gradient);
            border-radius: 2px;
        }
        
        /* Menu déroulant premium */
        .premium-dropdown-menu {
            opacity: 0;
            visibility: hidden;
            transform: translateY(15px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1), visibility 0.3s;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .dark .premium-dropdown-menu {
            background: #2D2D2D;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .premium-dropdown:hover .premium-dropdown-menu,
        .premium-dropdown:focus-within .premium-dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(5px);
        }
        
        .premium-dropdown-item {
            transition: all 0.25s ease;
            border-left: 3px solid transparent;
        }
        
        .premium-dropdown-item:hover {
            background-color: rgba(212, 175, 55, 0.05);
            border-left: 3px solid #D4AF37;
            transform: translateX(3px);
            color: #2C5282;
        }
        
        .dark .premium-dropdown-item:hover {
            background-color: rgba(212, 175, 55, 0.1);
            color: #D4AF37;
        }
        
        /* Bouton utilisateur premium */
        .premium-user-btn {
            transition: all 0.3s ease;
            background: var(--gold-gradient);
            position: relative;
            overflow: hidden;
        }
        
        .premium-user-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            transition: all 0.6s ease;
        }
        
        .premium-user-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(212, 175, 55, 0.3);
        }
        
        .premium-user-btn:hover::before {
            left: 100%;
        }
        
        /* Menu mobile premium */
        .premium-mobile-menu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: inset 0 10px 10px -10px rgba(0, 0, 0, 0.05);
        }
        
        .dark .premium-mobile-menu {
            background: #2D2D2D;
        }
        
        .premium-mobile-menu.open {
            max-height: 100vh;
        }
        
        /* Hamburger animation premium */
        .premium-hamburger {
            width: 30px;
            height: 20px;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .premium-hamburger span {
            display: block;
            position: absolute;
            height: 2px;
            width: 100%;
            background: #444;
            opacity: 1;
            left: 0;
            transform: rotate(0deg);
            transition: all 0.3s ease;
        }
        
        .dark .premium-hamburger span {
            background: #E5E5E5;
        }
        
        .premium-hamburger span:nth-child(1) {
            top: 0;
            transform-origin: left center;
        }
        
        .premium-hamburger span:nth-child(2) {
            top: 50%;
            transform: translateY(-50%);
            transform-origin: left center;
        }
        
        .premium-hamburger span:nth-child(3) {
            bottom: 0;
            transform-origin: left center;
        }
        
        .premium-hamburger.open span:nth-child(1) {
            transform: rotate(45deg);
            top: -1px;
            left: 4px;
            background: #D4AF37;
        }
        
        .premium-hamburger.open span:nth-child(2) {
            width: 0%;
            opacity: 0;
        }
        
        .premium-hamburger.open span:nth-child(3) {
            transform: rotate(-45deg);
            bottom: 0px;
            left: 4px;
            background: #D4AF37;
        }
        
        /* Mode sombre toggle */
        .dark-mode-toggle {
            transition: all 0.3s ease;
        }
        
        .dark-mode-toggle:hover {
            transform: rotate(30deg);
        }
        
        /* Animation au scroll */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-fadeIn {
            animation: fadeIn 0.6s ease forwards;
        }
    </style>
</head>
<body class="antialiased">
    <!-- Navbar Premium -->
    <nav class="premium-navbar fixed top-0 w-full z-50">
        <div class="container mx-auto px-6">
            <div class="flex justify-between items-center h-full">
                <!-- Logo Premium -->
                <div class="flex items-center">
                    <a href="<?= $base_path ?>index.php" class="flex items-center no-underline premium-logo">
                        <img src="<?= $base_path ?>assets/images/KWA.png" 
                             alt="KWABO Tourisme" 
                             class="h-20 w-auto transition-all duration-300 hover:scale-105"
                             onerror="this.src='<?= $base_path ?>assets/images/default-logo.png'">
                        <span class="ml-3 text-2xl font-bold text-premium-dark dark:text-white premium-logo hover:text-premium-gold transition-colors duration-300">KWABO</span>
                    </a>
                </div>

                <!-- Menu Desktop Premium -->
                <div class="hidden lg:flex items-center space-x-1">
                    <?php foreach ($menu_items as $page => $item): ?>
                        <?php if (!isset($item['submenu'])): ?>
                            <a href="<?= $item['url'] ?>" 
                               class="premium-nav-item <?= ($current_page === $page) ? 'active' : '' ?> flex items-center">
                                <i class="fas <?= $item['icon'] ?> mr-2 text-sm"></i>
                                <span class="relative"><?= $item['title'] ?></span>
                            </a>
                        <?php else: ?>
                            <div class="relative premium-dropdown" x-data="{ open: false }" @mouseleave="open = false">
                                <button 
                                    @mouseenter="open = true"
                                    class="premium-nav-item flex items-center"
                                    :class="{ 'text-premium-gold': open }"
                                >
                                    <i class="fas <?= $item['icon'] ?> mr-2"></i>
                                    <span class="relative"><?= $item['title'] ?></span>
                                    <svg class="ml-1 h-4 w-4 transform transition-transform" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                
                                <div 
                                    x-show="open"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 translate-y-3"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 translate-y-3"
                                    class="premium-dropdown-menu absolute left-0 mt-1 w-56 origin-top-left rounded-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 focus:outline-none z-50 py-1"
                                    x-cloak
                                    @mouseenter="open = true"
                                >
                                    <?php foreach ($item['submenu'] as $subpage => $subitem): ?>
                                        <a 
                                            href="<?= $subitem['url'] ?>" 
                                            class="premium-dropdown-item flex items-center px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:text-premium-accent dark:hover:text-premium-gold"
                                        >
                                            <i class="fas <?= $subitem['icon'] ?> mr-3 text-sm text-premium-gold"></i>
                                            <span class="relative"><?= $subitem['title'] ?></span>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <!-- Boutons utilisateur Premium -->
                <div class="flex items-center space-x-4">
                    <!-- Bouton Mode Sombre -->
                    <button id="dark-mode-toggle" class="p-2 rounded-full bg-gray-200 dark:bg-gray-700 hover:bg-premium-gold dark-mode-toggle">
                        <i class="fas fa-moon text-gray-700 dark:text-yellow-300"></i>
                    </button>

                    <?php if ($isAdmin): ?>
                        <a href="<?= $base_path ?>admin/dashboard.php" class="hidden lg:inline-flex items-center px-5 py-2.5 rounded-lg text-sm font-medium text-white bg-gradient-to-r from-premium-dark to-premium-accent shadow-lg hover:shadow-xl transition-all duration-300 group">
                            <i class="fas fa-user-shield mr-2 group-hover:rotate-12 transition-transform duration-300"></i>
                            <span class="relative">Dashboard</span>
                        </a>
                    <?php elseif ($isLoggedIn): ?>
                    <div class="relative" x-data="{ open: false }">
                        <button 
                            @click="open = !open"
                            class="premium-user-btn hidden lg:inline-flex items-center px-5 py-2.5 rounded-lg text-sm font-medium text-white shadow-lg hover:shadow-xl transition-all duration-300"
                        >
                            <i class="fas fa-user-circle mr-2"></i>
                            <span class="relative"><?= $user_name ?></span>
                            <svg class="ml-2 h-4 w-4 transform transition-transform" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
    
                        <!-- Ajoutez z-50 et vérifiez les classes -->
                        <div 
                            x-show="open"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-1"
                            class="absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 z-50"
                            style="display: none;"
                            x-cloak
                            @click.away="open = false"
                        >
                            <div class="py-1">
                                <a href="<?= $base_path ?>pages/gestion.php" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <i class="fas fa-home mr-3 text-premium-gold"></i>
                                    Mon espace
                                </a>
                                <a href="<?= $base_path ?>pages/profil.php" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <i class="fas fa-user-edit mr-3 text-premium-gold"></i>
                                    Mon profil
                                </a>
                                <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>
                                <a href="<?= $base_path ?>pages/deconnexion.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">
                                    <i class="fas fa-sign-out-alt mr-3"></i>
                                    Déconnexion
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                        <?php if ($show_auth_buttons): ?>
                            <a href="<?= $base_path ?>pages/connexion.php" class="hidden lg:inline-flex items-center px-5 py-2.5 rounded-lg text-sm font-medium text-white bg-gradient-to-r from-premium-dark to-premium-accent shadow-lg hover:shadow-xl transition-all duration-300">
                                <i class="fas fa-sign-in-alt mr-2"></i>
                                Connexion
                            </a>
                            <a href="<?= $base_path ?>pages/inscription.php" class="hidden lg:inline-flex items-center px-5 py-2.5 rounded-lg text-sm font-medium text-white bg-gradient-to-r from-premium-dark to-premium-accent shadow-lg hover:shadow-xl transition-all duration-300">
                                <i class="fas fa-user-plus mr-2"></i>
                                Inscription
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- Hamburger Menu -->
                    <button id="mobile-menu-button" class="lg:hidden premium-hamburger relative">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                </div>
            </div>

        <!-- Menu Mobile Premium -->
        <div 
            id="mobile-menu" 
            class="lg:hidden premium-mobile-menu bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700 shadow-xl"
            style="display: none;"
        >
            <div class="px-4 pt-3 pb-6 space-y-1">
                <?php foreach ($menu_items as $page => $item): ?>
                    <?php if (!isset($item['submenu'])): ?>
                        <a 
                            href="<?= $item['url'] ?>" 
                            class="block px-4 py-3 rounded-lg text-base font-medium flex items-center transition-all duration-300 <?= ($current_page === $page) ? 'bg-premium-light dark:bg-gray-700 text-premium-accent dark:text-premium-gold' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-premium-gold' ?>"
                        >
                            <i class="fas <?= $item['icon'] ?> mr-3 w-5 text-center text-premium-gold"></i>
                            <span class="relative"><?= $item['title'] ?></span>
                        </a>
                    <?php else: ?>
                        <div x-data="{ open: false }" class="space-y-1">
                            <button 
                                @click="open = !open"
                                class="w-full flex justify-between items-center px-4 py-3 rounded-lg text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-premium-gold transition-all duration-300"
                            >
                                <div class="flex items-center">
                                    <i class="fas <?= $item['icon'] ?> mr-3 w-5 text-center text-premium-gold"></i>
                                    <span class="relative"><?= $item['title'] ?></span>
                                </div>
                                <svg class="h-5 w-5 transform transition-transform" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            
                            <div 
                                x-show="open"
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0"
                                x-transition:enter-end="opacity-100"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                class="pl-12 space-y-1"
                            >
                                <?php foreach ($item['submenu'] as $subpage => $subitem): ?>
                                    <a 
                                        href="<?= $subitem['url'] ?>" 
                                        class="block px-4 py-2.5 rounded-lg text-sm font-medium flex items-center text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-premium-accent dark:hover:text-premium-gold transition-all duration-300"
                                    >
                                        <i class="fas <?= $subitem['icon'] ?> mr-3 w-5 text-center text-premium-gold"></i>
                                        <span class="relative"><?= $subitem['title'] ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <div class="border-t border-gray-200 dark:border-gray-700 mt-2 pt-3">
                    <?php if ($isAdmin): ?>
                        <a href="<?= $base_path ?>admin/dashboard.php" class="block px-4 py-3 rounded-lg text-base font-medium flex items-center text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-premium-accent dark:hover:text-premium-gold transition-all duration-300">
                            <i class="fas fa-user-shield mr-3 w-5 text-center text-premium-gold"></i>
                            <span class="relative">Administration</span>
                        </a>
                    <?php elseif ($isLoggedIn): ?>
                        <div class="px-4 py-3 flex items-center">
                            <img 
                                src="<?= $base_path ?>assets/images/users.jpg" 
                                alt="Photo de profil" 
                            >
                            <div>
                                <p class="font-medium text-premium-dark dark:text-white"><?= $user_name ?></p>
                                <p class="text-xs text-premium-gold">Compte <?= $_SESSION['user_type'] ?? 'utilisateur' ?></p>
                            </div>
                        </div>
                        <a href="<?= $base_path ?>pages/gestion.php" class="block px-4 py-2.5 rounded-lg text-sm font-medium flex items-center text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-premium-accent dark:hover:text-premium-gold transition-all duration-300 pl-14">
                            <i class="fas fa-home mr-3 w-5 text-center text-premium-gold"></i>
                            <span class="relative">Mon espace</span>
                        </a>
            
                        <a href="<?= $base_path ?>pages/profil.php" class="block px-4 py-2.5 rounded-lg text-sm font-medium flex items-center text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-premium-accent dark:hover:text-premium-gold transition-all duration-300 pl-14">
                            <i class="fas fa-user-edit mr-3 w-5 text-center text-premium-gold"></i>
                            <span class="relative">Mon profil</span>
                        </a>
                        <a href="<?= $base_path ?>pages/deconnexion.php" class="block px-4 py-2.5 rounded-lg text-sm font-medium flex items-center text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all duration-300 pl-14">
                            <i class="fas fa-sign-out-alt mr-3 w-5 text-center"></i>
                            <span class="relative">Déconnexion</span>
                        </a>
                    <?php elseif ($show_auth_buttons): ?>
                        <div class="px-4 py-3 space-y-3">
                            <a href="<?= $base_path ?>pages/connexion.php" class="block w-full px-4 py-2.5 rounded-lg text-center font-medium text-premium-dark dark:text-white border border-premium-dark dark:border-gray-600 hover:bg-premium-dark hover:text-white transition-all duration-300 group">
                                <span class="relative group-hover:translate-x-1 transition-transform duration-300">Connexion</span>
                            </a>
                            <a href="<?= $base_path ?>pages/inscription.php" class="block w-full px-4 py-2.5 rounded-lg text-center font-medium text-white bg-gradient-to-r from-premium-gold to-yellow-400 shadow-md hover:shadow-lg transition-all duration-300 group">
                                <span class="relative group-hover:translate-x-1 transition-transform duration-300">Inscription</span>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <script>
        // Gestion du menu mobile premium
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            
            // Toggle du menu mobile avec animation hamburger premium
            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function(e) {
                    e.stopPropagation();
                    mobileMenuButton.classList.toggle('open');
                    mobileMenuButton.setAttribute('aria-expanded', mobileMenuButton.classList.contains('open'));
                    
                    if (mobileMenuButton.classList.contains('open')) {
                        mobileMenu.style.display = 'block';
                        setTimeout(() => {
                            mobileMenu.classList.add('open');
                        }, 10);
                    } else {
                        mobileMenu.classList.remove('open');
                        setTimeout(() => {
                            mobileMenu.style.display = 'none';
                        }, 300);
                    }
                });
            }

            // Fermer le menu au clic ailleurs sur le document
            document.addEventListener('click', function(e) {
                if (!mobileMenu.contains(e.target) && !mobileMenuButton.contains(e.target)) {
                    if (mobileMenuButton && mobileMenuButton.classList.contains('open')) {
                        mobileMenuButton.classList.remove('open');
                        mobileMenuButton.setAttribute('aria-expanded', 'false');
                        mobileMenu.classList.remove('open');
                        setTimeout(() => {
                            mobileMenu.style.display = 'none';
                        }, 300);
                    }
                }
            });

            // Animation de la navbar au scroll
            window.addEventListener('scroll', function() {
                const navbar = document.querySelector('.premium-navbar');
                if (window.scrollY > 30) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            });

            // Fermer le menu mobile quand un lien est cliqué
            const mobileLinks = document.querySelectorAll('#mobile-menu a');
            mobileLinks.forEach(link => {
                link.addEventListener('click', () => {
                    if (mobileMenuButton && mobileMenuButton.classList.contains('open')) {
                        mobileMenuButton.classList.remove('open');
                        mobileMenuButton.setAttribute('aria-expanded', 'false');
                        mobileMenu.classList.remove('open');
                        setTimeout(() => {
                            mobileMenu.style.display = 'none';
                        }, 300);
                    }
                });
            });

            // Gestion du mode sombre
            const darkModeToggle = document.getElementById('dark-mode-toggle');
            if (darkModeToggle) {
                darkModeToggle.addEventListener('click', function() {
                    document.documentElement.classList.toggle('dark');
                    localStorage.setItem('darkMode', document.documentElement.classList.contains('dark'));
                });

                // Vérifier le préférence utilisateur
                if (localStorage.getItem('darkMode') === 'true') {
                    document.documentElement.classList.add('dark');
                }
            }
        });
    </script>
    
    <!-- AlpineJS -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.12.0/dist/cdn.min.js" defer></script>
</body>
</html>