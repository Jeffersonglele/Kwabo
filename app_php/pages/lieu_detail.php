<?php
session_start();
include_once(__DIR__ . "/../config/database.php");

// Inclure l'autoload de Composer pour charger automatiquement les dépendances
require_once __DIR__ . '/../vendor/autoload.php';

// Fonction pour convertir le Markdown en HTML ou retourner le HTML existant
function markdownToHtml($content) {
    // Si le contenu contient des balises HTML, on le retourne tel quel
    if ($content !== strip_tags($content)) {
        return $content;
    }
    
    // Sinon, on traite comme du Markdown
    $parsedown = new Parsedown();
    // Sécurité : Échapper le HTML pour éviter les attaques XSS
    $parsedown->setSafeMode(true);
    return $parsedown->text($content);
}

// Récupérer l'ID du lieu depuis l'URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ../pages/destinations.php');
    exit;
}

$id = (int)$_GET['id'];

// Enregistrer la vue dans la base de données
if (isset($_SESSION['user_id'])) {
    // Récupérer l'ID du gestionnaire du lieu
    $stmt = $pdo->prepare("SELECT gestionnaire_id FROM lieux WHERE id = ?");
    $stmt->execute([$id]);
    $gestionnaire_id = $stmt->fetchColumn();
    
    // Enregistrer la vue avec les informations du visiteur
    $stmt = $pdo->prepare("INSERT INTO vues (element_id, element_type, gestionnaire_id, ip_visiteur, user_agent) VALUES (?, 'destination', ?, ?, ?)");
    $stmt->execute([
        $id,
        $gestionnaire_id,
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['HTTP_USER_AGENT']
    ]);
}
// Requête pour récupérer les détails du lieu
$stmt = $pdo->prepare("SELECT * FROM lieux WHERE id = ?");
$stmt->execute([$id]);
$lieu = $stmt->fetch();

if (!$lieu) {
    header('Location: ../pages/destinations.php');
    exit;
}

// Fonction pour corriger le chemin de l'image
function getCorrectImagePath($imagePath) {
    // Si le chemin est vide, retourner une image par défaut
    if (empty($imagePath)) {
        return '../assets/images/default-placeholder.png'; // Assurez-vous que cette image existe
    }
    
    // Si le chemin commence déjà par http, on le garde tel quel
    if (strpos($imagePath, 'http') === 0) {
        return $imagePath;
    }
    
    // Supprimer les éventuels slashs ou backslashes au début
    $imagePath = ltrim($imagePath, '/\\');
    
    // Construire le chemin relatif depuis la racine du site
    return '../' . $imagePath;
}


// Définir le titre de la page
$page_title = htmlspecialchars($lieu['nom']) . " - KWABO";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="icon" type="image/svg+xml" href="../assets/favicon/favicon.svg">
    <link rel="icon" type="image/png" sizes="96x96" href="../assets/favicon/favicon-96x96.png">
    <link rel="shortcut icon" href="../assets/favicon/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Ajout des styles GitHub Markdown -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/github-markdown-css/5.1.0/github-markdown.min.css">
    <style>
        /* Style de base pour le contenu Markdown */
        .markdown-body {
            box-sizing: border-box;
            min-width: 200px;
            max-width: 100%;
            margin: 0;
            padding: 1.5rem;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif;
            line-height: 1.7;
            color: #24292e;
            background-color: #fff;
            border-radius: 0.5rem;
        }

        /* Amélioration de la lisibilité */
        .markdown-body h1,
        .markdown-body h2,
        .markdown-body h3,
        .markdown-body h4,
        .markdown-body h5,
        .markdown-body h6 {
            margin-top: 1.5em;
            margin-bottom: 0.8em;
            font-weight: 600;
            line-height: 1.25;
            color: #2d3748;
        }

        .markdown-body h1 {
            font-size: 2em;
            border-bottom: 1px solid #eaecef;
            padding-bottom: 0.3em;
        }

        .markdown-body h2 {
            font-size: 1.5em;
            border-bottom: 1px solid #eaecef;
            padding-bottom: 0.3em;
        }

        .markdown-body h3 {
            font-size: 1.25em;
        }

        .markdown-body p {
            margin-top: 0;
            margin-bottom: 1em;
        }

        .markdown-body ul,
        .markdown-body ol {
            padding-left: 2em;
            margin-top: 0;
            margin-bottom: 1em;
        }

        .markdown-body li {
            margin-bottom: 0.5em;
        }

        .markdown-body li > p {
            margin-top: 1em;
        }

        .markdown-body code {
            font-family: SFMono-Regular, Consolas, "Liberation Mono", Menlo, monospace;
            padding: 0.2em 0.4em;
            margin: 0;
            font-size: 85%;
            background-color: rgba(27, 31, 35, 0.05);
            border-radius: 3px;
        }

        .markdown-body pre {
            background-color: #f6f8fa;
            border-radius: 6px;
            padding: 16px;
            overflow: auto;
            line-height: 1.45;
            margin-bottom: 1em;
        }

        .markdown-body blockquote {
            padding: 0 1em;
            color: #6a737d;
            border-left: 0.25em solid #dfe2e5;
            margin: 0 0 1em 0;
        }

        .markdown-body table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 1em;
            display: block;
            overflow-x: auto;
        }

        .markdown-body th,
        .markdown-body td {
            padding: 6px 13px;
            border: 1px solid #dfe2e5;
        }

        .markdown-body tr {
            background-color: #fff;
            border-top: 1px solid #c6cbd1;
        }

        .markdown-body tr:nth-child(2n) {
            background-color: #f6f8fa;
        }

        .markdown-body img {
            max-width: 100%;
            box-sizing: content-box;
            background-color: #fff;
        }

        /* Adaptation pour les petits écrans */
        @media (max-width: 767px) {
            .markdown-body {
                padding: 1rem;
                font-size: 0.95em;
            }
            
            .markdown-body h1 {
                font-size: 1.75em;
            }
            
            .markdown-body h2 {
                font-size: 1.5em;
            }
            
            .markdown-body h3 {
                font-size: 1.25em;
            }
        }
        .hero-section {
            height: 60vh;
            background-size: cover;
            background-position: center;
        }
        .hero-overlay {
            background: linear-gradient(to bottom, rgba(0,0,0,0.3), rgba(0,0,0,0.7));
        }
        .hero-title {
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }
        .hero-subtitle {
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        }
        .info-card {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .map-container {
            height: 400px;
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .btn-reserve {
            transition: all 0.3s ease;
        }
        .btn-reserve:hover {
            transform: translateY(-2px);
        }
        .animate-fade-in {
            animation: fadeIn 1s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Styles pour la description */
        .description-content {
            line-height: 1.8;
            color: #374151;
            font-size: 1.05rem;
        }
        .description-content h2, 
        .description-content h3, 
        .description-content h4 {
            color: #1f2937;
            margin-top: 1.5em;
            margin-bottom: 0.75em;
            font-weight: 700;
            line-height: 1.3;
        }
        .description-content h2 {
            font-size: 1.5rem;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 0.5em;
        }
        .description-content h3 {
            font-size: 1.3rem;
        }
        .description-content h4 {
            font-size: 1.1rem;
        }
        .description-content p {
            margin-bottom: 1.25em;
        }
        .description-content ul, 
        .description-content ol {
            margin-bottom: 1.25em;
            padding-left: 1.5em;
        }
        .description-content li {
            margin-bottom: 0.5em;
            position: relative;
        }
        .description-content ul li::before {
            content: '•';
            color: #10b981;
            font-weight: bold;
            display: inline-block;
            width: 1em;
            margin-left: -1em;
        }
        .description-content ol {
            list-style-type: decimal;
        }
        .description-content a {
            color: #10b981;
            text-decoration: none;
            font-weight: 500;
            border-bottom: 1px solid #a7f3d0;
            transition: all 0.2s ease;
        }
        .description-content a:hover {
            color: #059669;
            border-bottom-color: #10b981;
        }
        .description-content blockquote {
            border-left: 4px solid #10b981;
            padding: 0.5em 1em;
            margin: 1.5em 0;
            background-color: #f9fafb;
            font-style: italic;
            color: #4b5563;
        }
        
        /* Styles pour le rendu Markdown */
        .markdown-body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.7;
            color: #1f2937;
            max-width: 65ch;
            margin: 0 auto;
            padding: 2rem;
        }

        .markdown-body h1,
        .markdown-body h2,
        .markdown-body h3,
        .markdown-body h4 {
            color: #b91c1c;
            margin-top: 1.5em;
            margin-bottom: 0.75em;
            font-weight: 700;
            line-height: 1.25;
        }

        .markdown-body h2 {
            font-size: 1.75rem;
            border-bottom: 4px solid #dc2626;
            padding-bottom: 0.5rem;
            display: inline-block;
            margin-bottom: 1.5rem;
        }

        .markdown-body h3 {
            font-size: 1.5rem;
            color: #991b1b;
            margin-top: 2.5rem;
            margin-bottom: 1.25rem;
            position: relative;
            padding-left: 1rem;
            border-left: 4px solid #dc2626;
        }

        .markdown-body p {
            margin-bottom: 1.5em;
            font-size: 1.1rem;
            line-height: 1.8;
            color: #374151;
        }

        .markdown-body ul,
        .markdown-body ol {
            margin-bottom: 1.75em;
            padding-left: 1.5em;
        }

        .markdown-body li {
            margin-bottom: 0.75em;
            position: relative;
            padding-left: 0.5em;
        }

        .markdown-body li::before {
            content: '•';
            color: #b91c1c;
            font-weight: bold;
            display: inline-block;
            width: 1em;
            margin-left: -1em;
        }

        .markdown-body blockquote {
            border-left: 4px solid #b91c1c;
            padding: 1.25rem 1.5rem;
            margin: 2rem 0;
            background-color: #fef2f2;
            color: #4b5563;
            border-radius: 0 0.5rem 0.5rem 0;
        }

        .markdown-body a {
            color: #b91c1c;
            text-decoration: none;
            font-weight: 500;
            border-bottom: 1px solid #fca5a5;
            transition: all 0.2s ease;
        }

        .markdown-body a:hover {
            color: #7f1d1d;
            border-bottom-color: #b91c1c;
        }

        /* Styles pour les éléments spéciaux */
        .markdown-body .tip,
        .markdown-body .info,
        .markdown-body .warning {
            padding: 1.25rem 1.5rem;
            margin: 2rem 0;
            border-radius: 0.5rem;
            position: relative;
        }

        .markdown-body .tip {
            background-color: #fffbeb;
            border-left: 4px solid #d97706;
        }

        .markdown-body .warning {
            background-color: #fef2f2;
            border-left: 4px solid #dc2626;
        }

        .markdown-body .info {
            background-color: #eff6ff;
            border-left: 4px solid #2563eb;
        }

        /* Styles spécifiques pour les sections avec bordure */
        .markdown-body .section-border {
            border-left: 4px solid #dc2626;
            padding-left: 1.5rem;
            margin: 2rem 0;
        }

        /* Styles pour les listes à puces */
        .markdown-body ul {
            list-style-type: none;
        }

        .markdown-body ul > li::before {
            content: '•';
            color: #b91c1c;
            font-weight: bold;
            display: inline-block;
            width: 1em;
            margin-left: -1.5em;
        }

        /* Styles pour les listes numérotées */
        .markdown-body ol {
            list-style-type: none;
            counter-reset: item;
        }

        .markdown-body ol > li {
            counter-increment: item;
            position: relative;
            padding-left: 1.5em;
        }

        .markdown-body ol > li::before {
            content: counter(item) '.';
            color: #b91c1c;
            font-weight: bold;
            position: absolute;
            left: 0;
        }

        /* Styles pour les blocs de code */
        .markdown-body pre {
            background-color: #1e293b;
            color: #e2e8f0;
            padding: 1.25rem;
            border-radius: 0.5rem;
            overflow-x: auto;
            margin: 1.5rem 0;
            font-family: 'Fira Code', 'Courier New', monospace;
            font-size: 0.9em;
            line-height: 1.6;
        }

        .markdown-body code {
            background-color: #f3f4f6;
            color: #dc2626;
            padding: 0.2em 0.4em;
            border-radius: 0.25em;
            font-family: 'Fira Code', 'Courier New', monospace;
            font-size: 0.9em;
        }

        .markdown-body pre code {
            background-color: transparent;
            color: inherit;
            padding: 0;
            border-radius: 0;
        }

        /* Styles pour les images */
        .markdown-body img {
            max-width: 100%;
            height: auto;
            border-radius: 0.5rem;
            margin: 1.5rem 0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        /* Styles pour les tableaux */
        .markdown-body table {
            width: 100%;
            border-collapse: collapse;
            margin: 1.5rem 0;
            font-size: 0.9em;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        }

        .markdown-body th,
        .markdown-body td {
            padding: 0.75rem 1rem;
            text-align: left;
            border: 1px solid #e5e7eb;
        }

        .markdown-body th {
            background-color: #f3f4f6;
            font-weight: 600;
            color: #111827;
        }

        .markdown-body tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .markdown-body tr:hover {
            background-color: #f3f4f6;
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php include_once(__DIR__ . "/../includes/navbar.php"); ?>

    <!-- Hero Section -->
    <section class="hero-section relative" style="background-image: url('<?= getCorrectImagePath($lieu['image']) ?>');">
        <div class="hero-overlay absolute inset-0"></div>
        <div class="hero-content relative z-10 flex flex-col justify-center h-full px-8">
            <h1 class="hero-title text-4xl md:text-5xl font-bold text-white mb-4 animate-fade-in">
                <?= htmlspecialchars($lieu['nom']) ?>
            </h1>
            <p class="hero-subtitle text-xl text-white animate-fade-in" style="animation-delay: 0.2s">
                <i class="fas fa-map-marker-alt mr-2"></i>
                <?= htmlspecialchars($lieu['adresse']) ?>, <?= htmlspecialchars($lieu['ville']) ?>
            </p>
        </div>
    </section>

    <!-- Detail Section -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Informations principales -->
                <div class="lg:w-2/3">
                    <div class="info-card bg-white rounded-xl p-8 mb-8">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6 pb-3 border-b border-gray-200">À propos</h2>
                        <div class="description-content">
                            <div class="markdown-body">
                                <?= markdownToHtml($lieu['description']) ?>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex items-center">
                                <i class="fas fa-ticket-alt text-xl mr-3 w-6" style="color: #D4AF37;"></i>
                                <span class="text-gray-700">Prix : <?= isset($lieu['prix']) ? $lieu['prix'] : 'Non spécifié' ?></span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div class="flex items-center">
                                <i class="fas fa-calendar-alt text-xl mr-3 w-6" style="color: #D4AF37;"></i>
                                <span class="text-gray-700">Horaires : <?= isset($lieu['horaires']) ? htmlspecialchars($lieu['horaires']) : 'Non spécifiés' ?></span>
                            </div>
                            <span class="text-gray-800 font-medium text-lg">
                                Alors t'attends quoi pour t'y rendre <span class="ml-1">!!!</span>
                            </span>
                        </div>
                    </div>

                    <!-- Carte -->
                    <div class="info-card bg-white rounded-xl p-8">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6">Localisation</h2>
                        <div class="map-container rounded-xl overflow-hidden">
                            <iframe
                                src="https://www.google.com/maps?q=<?= $lieu['latitude'] ?>,<?= $lieu['longitude'] ?>&output=embed"
                                width="100%"
                                height="100%"
                                style="border:0;"
                                allowfullscreen>
                            </iframe>
                        </div>
                    </div>
                </div>

                <!-- Réservation -->
                <div class="lg:w-1/3">
                    <div class="reservation-card bg-white rounded-xl p-8 sticky top-8">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6">Aperçu</h2>
                        <video src="<?=htmlspecialchars($lieu['Video']) ?>" class="w-full rounded-lg mb-6" autoplay muted loop controls></video>
                        
                        <!-- Réseaux sociaux -->
                        <div class="social-links mt-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-3">Nous suivre</h3>
                            <div class="flex space-x-4">
                                <?php if (!empty($lieu['facebook'])): ?>
                                    <a href="<?= htmlspecialchars($lieu['facebook']) ?>" target="_blank" class="text-gray-700 hover:text-blue-600 transition-colors duration-200" title="Facebook">
                                        <i class="fab fa-facebook-f text-2xl"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if (!empty($lieu['instagram'])): ?>
                                    <a href="<?= htmlspecialchars($lieu['instagram']) ?>" target="_blank" class="text-gray-700 hover:text-pink-600 transition-colors duration-200" title="Instagram">
                                        <i class="fab fa-instagram text-2xl"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if (!empty($lieu['tiktok'])): ?>
                                    <a href="<?= htmlspecialchars($lieu['tiktok']) ?>" target="_blank" class="text-gray-700 hover:text-black transition-colors duration-200" title="TikTok">
                                        <i class="fab fa-tiktok text-2xl"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (!empty($lieu['site_web'])): ?>
                                <div class="mt-4">
                                    <a href="<?= htmlspecialchars($lieu['site_web']) ?>" target="_blank" class="inline-flex items-center text-green-600 hover:text-green-700 font-medium">
                                        <i class="fas fa-globe mr-2"></i>
                                        Visiter le site web
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </section>

    <?php include_once(__DIR__ . "/../includes/footer.php"); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animation au défilement
        document.addEventListener('DOMContentLoaded', function() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate-fade-in');
                    }
                });
            }, {
                threshold: 0.1
            });

            // Observer les sections à animer
            document.querySelectorAll('.info-card, .map-container, .reservation-card').forEach((el) => {
                observer.observe(el);
            });
        });
    </script>
</body>
</html>