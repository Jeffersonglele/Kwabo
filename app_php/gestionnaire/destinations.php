<?php
session_start();
include_once(__DIR__ . "/../config/database.php");
include_once(__DIR__ . "/../config/access_control.php");

// Vérifier que l'utilisateur est connecté et a le bon type de compte
check_access(ALLOWED_DESTINATION);

// Récupération des données du formulaire en cas d'erreur
$form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);

// Afficher les messages de session
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'] ?? 'info';
    $message_icon = [
        'success' => 'check-circle',
        'error' => 'exclamation-circle',
        'warning' => 'exclamation-triangle',
        'info' => 'info-circle'
    ][$message_type] ?? 'info-circle';

    echo "<div class='fixed top-4 right-4 z-50 animate-fade-in'>
            <div class='bg-white p-4 rounded-lg shadow-xl border-l-4 border-$message_type-500 flex items-start max-w-md'>
                <i class='fas fa-$message_icon text-$message_type-500 text-xl mr-3 mt-1'></i>
                <div>
                    <p class='font-semibold text-$message_type-700'>" . ucfirst($message_type) . "</p>
                    <p class='text-gray-700'>$message</p>
                </div>
                <button onclick='this.parentElement.parentElement.remove()' class='ml-4 text-gray-400 hover:text-gray-600'>
                    <i class='fas fa-times'></i>
                </button>
            </div>
          </div>";

    // Supprimer le message après affichage
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);

    // Script JS pour faire disparaître le message après 5 secondes
    echo "<script>
            setTimeout(() => {
                const notice = document.querySelector('.fixed.top-4');
                if (notice) notice.remove();
            }, 5000);
          </script>";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Proposer une Destination - KWABO</title>
    <link rel="icon" type="image/svg+xml" href="../assets/favicon/favicon.svg" />
    <link rel="icon" type="image/png" sizes="96x96" href="../assets/favicon/favicon-96x96.png" />
    <link rel="shortcut icon" href="../assets/favicon/favicon.ico" />
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin=""/>
    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <script src="https://unpkg.com/tippy.js@6"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>
    <!-- Markdown Library -->
    <script src="https://cdn.jsdelivr.net/npm/marked@4.0.0/marked.min.js"></script>
    <script>
    // Configuration de marked.js
    marked.setOptions({
        breaks: true,
        gfm: true,
        headerIds: false,
        mangle: false
    });
    
    // Fonction de rendu Markdown simple et robuste
    window.renderMarkdown = function(markdown) {
        if (!markdown) return '';
        
        try {
            // Vérifier si marked est disponible
            if (typeof marked === 'function' || typeof marked.parse === 'function') {
                return marked.parse(markdown);
            }
            
            // Fallback très simple si marked n'est pas disponible
            return markdown
                .replace(/\n\n/g, '</p><p>')
                .replace(/\n/g, '<br>')
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.*?)\*/g, '<em>$1</em>')
                .replace(/^# (.*$)/gm, '<h3>$1</h3>')
                .replace(/^## (.*$)/gm, '<h4>$1</h4>');
                
        } catch (e) {
            console.error('Erreur de rendu Markdown:', e);
            return markdown.replace(/[<>&]/g, function(c) {
                return {'<':'&lt;','>':'&gt;','&':'&amp;'}[c];
            });
        }
    };
    
    // Vérification du chargement
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Marked.js chargé:', typeof marked !== 'undefined');
    });
    </script>
</head>
<style>
    :root {
        --primary: #2F855A;
        --primary-dark: #276749;
        --secondary: #38B2AC;
    }

    body {
        font-family: 'Poppins', sans-serif;
        background-color: #f8fafc;
    }

    .hero-section {
        height: 60vh;
        min-height: 500px;
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
    }

    .hero-overlay {
        background: linear-gradient(135deg, rgba(47, 133, 90, 0.9) 0%, rgba(29, 78, 53, 0.9) 100%);
    }

    .card {
        border-radius: 16px;
        box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        background: white;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px -10px rgba(0, 0, 0, 0.12);
    }

    .form-input {
        transition: all 0.3s;
        border: 1px solid #E5E7EB;
        border-radius: 10px;
    }

    .form-input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(47, 133, 90, 0.2);
    }

    .btn-primary {
        background: var(--primary);
        transition: all 0.3s;
    }

    .btn-primary:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 10px 20px -10px rgba(47, 133, 90, 0.4);
    }

    .feature-icon {
        width: 60px;
        height: 60px;
        border-radius: 16px;
        background: rgba(47, 133, 90, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary);
        font-size: 1.75rem;
        margin-bottom: 1.5rem;
    }

    .step-number {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: var(--primary);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.5rem;
        flex-shrink: 0;
        margin-right: 1.5rem;
    }

    /* Styles pour la carte */
    #map {
        height: 400px !important;
        width: 100%!important;
        min-height: 400px !important;
        border-radius: 8px;
        z-index: 1;
    }
    
    /* Correction pour les tuiles de la carte */
    .leaflet-container {
        width: 100%;
        height: 100%;
        min-height: 400px;
    }

    .animate-fade-in {
        animation: fadeIn 0.6s ease-in forwards;
    }

    .animate-slide-up {
        animation: slideUp 0.8s ease-out forwards;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .file-upload-label {
        transition: all 0.3s;
    }

    .file-upload-label:hover {
        border-color: var(--primary);
        background: rgba(47, 133, 90, 0.05);
    }

    .tooltip-icon {
        cursor: help;
        color: var(--primary);
    }

    .tab-active {
        border-bottom: 3px solid var(--primary);
        color: var(--primary);
        font-weight: 600;
    }
</style>

<body class="bg-gray-50">
    <!-- Hero Section -->
    <section class="hero-section relative flex items-center justify-center">
        <div class="hero-overlay absolute inset-0"></div>
        <div class="container mx-auto px-4 relative z-10">
            <div class="max-w-4xl mx-auto text-center animate-slide-up">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-6">
                    Partagez la Beauté du Bénin
                </h1>
                <p class="text-xl md:text-2xl text-white opacity-90 mb-8">
                    Proposez votre destination touristique et attirez des visiteurs du monde entier
                </p>
                <div class="flex justify-center space-x-4"></div>
            </div>
        </div>

        <!-- SVG VAGUE -->
        <div class="absolute bottom-0 left-0 right-0 overflow-hidden">
            <svg viewBox="0 0 1200 120" preserveAspectRatio="none" class="w-full h-16">
                <path
                    d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z"
                    opacity=".25" fill="white"></path>
                <path
                    d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z"
                    opacity=".5" fill="white"></path>
                <path
                    d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z"
                    fill="white"></path>
            </svg>
        </div>
    </section>

    <!-- Benefits Section -->
    <section id="benefits" class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">Pourquoi proposer votre destination ?</h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Rejoignez la plateforme Bénin Tourisme et bénéficiez de nombreux avantages
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="card p-8 text-center">
                    <div class="feature-icon mx-auto">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Visibilité Maximale</h3>
                    <p class="text-gray-600">
                        Faites découvrir votre site à des milliers de visiteurs potentiels à travers notre plateforme et nos réseaux sociaux.
                    </p>
                </div>

                <div class="card p-8 text-center">
                    <div class="feature-icon mx-auto">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Croissance Économique</h3>
                    <p class="text-gray-600">
                        Augmentez votre chiffre d'affaires en attirant plus de touristes locaux et internationaux vers votre destination.
                    </p>
                </div>

                <div class="card p-8 text-center">
                    <div class="feature-icon mx-auto">
                        <i class="fas fa-hand-holding-heart"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Valorisation du Patrimoine</h3>
                    <p class="text-gray-600">
                        Contribuez à la préservation et à la valorisation du riche patrimoine culturel et naturel du Bénin.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">Comment proposer votre destination ?</h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Un processus simple et rapide en 3 étapes
                </p>
            </div>

            <div class="max-w-4xl mx-auto space-y-10">
                <div class="flex items-start">
                    <div class="step-number">1</div>
                    <div>
                        <h3 class="text-xl font-semibold mb-3">Remplissez le formulaire</h3>
                        <p class="text-gray-600">
                            Fournissez les informations essentielles sur votre destination : nom, description, localisation, type de site et services disponibles.
                        </p>
                    </div>
                </div>

                <div class="flex items-start">
                    <div class="step-number">2</div>
                    <div>
                        <h3 class="text-xl font-semibold mb-3">Ajoutez des médias attractifs</h3>
                        <p class="text-gray-600">
                            Téléchargez des photos et vidéos de haute qualité pour mettre en valeur votre destination et donner envie aux visiteurs.
                        </p>
                    </div>
                </div>

                <div class="flex items-start">
                    <div class="step-number">3</div>
                    <div>
                        <h3 class="text-xl font-semibold mb-3">Validation et publication</h3>
                        <p class="text-gray-600">
                            Notre équipe examinera votre proposition sous 48h. Une fois approuvée, votre destination sera visible par tous nos utilisateurs.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Form Section -->
    <section id="form-section" class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="max-w-6xl mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Informations principales -->
                    <div class="lg:col-span-2">
                        <div class="card p-8 mb-8">
                            <h2 class="text-2xl font-bold text-gray-800 mb-6">À propos de cette démarche</h2>
                            <div class="prose text-gray-700">
                                <p class="mb-4">
                                    Vous êtes gestionnaire d'un site touristique, propriétaire d'un lieu d'intérêt ou responsable d'une activité culturelle ou naturelle ? Rejoignez la plateforme Bénin Tourisme et faites découvrir votre destination à des milliers de visiteurs !
                                </p>
                                <p class="mb-4">
                                    Ce formulaire vous permet d'inscrire votre site, de décrire ses atouts, d'ajouter des médias attractifs, et de proposer des services associés (guides touristiques, hébergement, restauration, etc.).
                                </p>
                                <p>
                                    En partageant la beauté de votre région, vous contribuez au développement du tourisme béninois tout en augmentant votre propre visibilité.
                                </p>
                            </div>

                            <div class="mt-8 bg-green-50 p-6 rounded-lg border border-green-100">
                                <h3 class="text-lg font-semibold text-green-800 mb-3 flex items-center">
                                    <i class="fas fa-lightbulb mr-2"></i> Conseils pour une bonne soumission
                                </h3>
                                <ul class="text-green-700 space-y-2">
                                    <li class="flex items-start">
                                        <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                                        <span>Fournissez des descriptions complètes et engageantes (entre 100 et 300 mots)</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                                        <span>Utilisez des photos de haute qualité (minimum 1200x800 pixels, formats JPG ou PNG)</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                                        <span>Précisez bien la localisation avec l'adresse exacte et si possible les coordonnées GPS</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                                        <span>Mentionnez les services disponibles à proximité (parking, restauration, hébergement)</span>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- FAQ Section -->
                        <div class="card p-8">
                            <h2 class="text-2xl font-bold text-gray-800 mb-6">Questions fréquentes</h2>
                            <div class="space-y-4">
                                <div class="border-b border-gray-200 pb-4">
                                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Combien de temps prend la validation ?</h3>
                                    <p class="text-gray-600">Notre équipe traite généralement les nouvelles soumissions dans un délai de 48 heures. Vous recevrez une notification par email une fois votre destination approuvée.</p>
                                </div>
                                <div class="border-b border-gray-200 pb-4">
                                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Puis-je modifier ma destination après soumission ?</h3>
                                    <p class="text-gray-600">Oui, une fois connecté à votre compte, vous pourrez modifier les informations de votre destination à tout moment. Les modifications seront soumises à validation.</p>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Y a-t-il des frais pour proposer une destination ?</h3>
                                    <p class="text-gray-600">Oui, l'inscription et la publication de destinations sont entièrement payantes. Cependant, nous croyons en la promotion collaborative du patrimoine béninois.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Formulaire -->
                    <div class="lg:col-span-1">
                        <div class="card p-8 sticky top-8">
                            <div class="text-center mb-6">
                                <h2 class="text-2xl font-bold text-gray-800">Proposer une destination</h2>
                                <p class="text-gray-500 mt-2">Remplissez tous les champs obligatoires (*)</p>
                            </div>

                            <form action="traitement_destinations.php" method="POST" enctype="multipart/form-data" class="space-y-5" id="destinationForm">
                                <!-- Tabs for different sections -->
                                <div class="flex border-b border-gray-200">
                                    <button type="button" class="tab-active px-4 py-2 text-sm font-medium text-green-600" data-tab="info">
                                        <i class="fas fa-info-circle mr-2"></i>Infos
                                    </button>
                                    <button type="button" class="px-4 py-2 text-sm font-medium text-gray-500 hover:text-green-600" data-tab="media">
                                        <i class="fas fa-images mr-2"></i>Médias
                                    </button>
                                    <button type="button" class="px-4 py-2 text-sm font-medium text-gray-500 hover:text-green-600" data-tab="contact">
                                        <i class="fas fa-address-card mr-2"></i>Contact
                                    </button>
                                </div>

                                <!-- Info Tab -->
                                <div id="info-tab" class="tab-content">
                                    <!-- Nom -->
                                    <div>
                                        <label class="block text-gray-700 font-medium mb-2">
                                            <i class="fas fa-map-marker-alt text-green-600 mr-1"></i>Nom du lieu <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="nom" required
                                            value="<?= htmlspecialchars($form_data['nom'] ?? '') ?>"
                                            class="form-input w-full px-4 py-3" />
                                    </div>

                                    <div class="mb-6">
                                        <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2">
                                            <i class="fas fa-align-left text-green-600 mr-2"></i>Description <span class="text-red-500">*</span>
                                            <span class="tooltip" data-tip="Décrivez votre destination de manière attractive (100-300 mots). Mentionnez les activités, points d'intérêt et particularités.">
                                                <i class="fas fa-info-circle text-blue-500 hover:text-blue-700 ml-1 cursor-pointer transition-colors"></i>
                                            </span>
                                        </label>

                                        <div class="relative">
                                            <textarea
                                                name="description"
                                                rows="6"
                                                required
                                                id="richDescription"
                                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-gray transition-all shadow-sm"
                                                placeholder="Décrivez ce qui rend ce lieu spécial... (Astuce : utilisez **texte** pour le gras et *texte* pour l'italique)"
                                                data-texteditor="description"
                                            ><?= htmlspecialchars($form_data['description'] ?? '') ?></textarea>

                                            <!-- Barre d'outils minimale -->
                                            <div class="absolute bottom-16 right-2 flex gap-1 bg-white dark:bg-gray-700 p-1 rounded border border-gray-200 dark:border-gray-600 shadow-sm">
                                                <button type="button" onclick="TextEditor.insertText('**', '**'); return false;" class="p-1 hover:bg-gray-100 dark:hover:bg-gray-600 rounded" title="Gras">
                                                    <i class="fas fa-bold text-sm"></i>
                                                </button>
                                                <button type="button" onclick="TextEditor.insertText('*', '*'); return false;" class="p-1 hover:bg-gray-100 dark:hover:bg-gray-600 rounded" title="Italique">
                                                    <i class="fas fa-italic text-sm"></i>
                                                </button>
                                                <button type="button" onclick="TextEditor.insertText('* ', ''); return false;" class="p-1 hover:bg-gray-100 dark:hover:bg-gray-600 rounded" title="Liste à puces">
                                                    <i class="fas fa-list-ul text-sm"></i>
                                                </button>
                                                <button type="button" onclick="TextEditor.insertText('1. ', ''); return false;" class="p-1 hover:bg-gray-100 dark:hover:bg-gray-600 rounded" title="Liste numérotée">
                                                    <i class="fas fa-list-ol text-sm"></i>
                                                </button>
                                            </div>

                                            <!-- Compteur et prévisualisation -->
                                            <div class="flex justify-between mt-1">
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    <span id="descCount">0</span>/300 mots (Min. 100 mots)
                                                </div>
                                                <button type="button" onclick="TextEditor.togglePreview(); return false;" class="text-xs text-blue-600 dark:text-blue-400 hover:underline flex items-center">
                                                    <i class="fas fa-eye mr-1"></i>
                                                    <span>Aperçu</span>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Aperçu en temps réel (caché par défaut) -->
                                        <div id="previewContainer" class="hidden mt-4 p-4 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 prose max-w-none">
                                            <h4 class="text-gray-700 dark:text-gray-300 font-medium mb-2">Aperçu :</h4>
                                            <div id="descriptionPreview"></div>
                                        </div>
                                    </div>

                                    <!-- Type -->
                                    <div>
                                        <label class="block text-gray-700 font-medium mb-2">
                                            <i class="fas fa-tags text-green-600 mr-1"></i>Type de lieu <span class="text-red-500">*</span>
                                        </label>
                                        <select name="type" required class="form-input w-full px-4 py-3 appearance-none">
                                            <option value="">Sélectionner un type</option>
                                            <option value="Culturel" <?= isset($form_data['type']) && $form_data['type'] === 'Culturel' ? 'selected' : '' ?>>Culturel</option>
                                            <option value="Naturel" <?= isset($form_data['type']) && $form_data['type'] === 'Naturel' ? 'selected' : '' ?>>Naturel</option>
                                            <option value="Historique" <?= isset($form_data['type']) && $form_data['type'] === 'Historique' ? 'selected' : '' ?>>Historique</option>
                                            <option value="Religieux" <?= isset($form_data['type']) && $form_data['type'] === 'Religieux' ? 'selected' : '' ?>>Religieux</option>
                                            <option value="Autre" <?= isset($form_data['type']) && $form_data['type'] === 'Autre' ? 'selected' : '' ?>>Autre</option>
                                        </select>
                                    </div>

                                    <!-- Adresse -->
                                    <div>
                                        <label class="block text-gray-700 font-medium mb-2">
                                            <i class="fas fa-map-marked-alt text-green-600 mr-1"></i>Adresse <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="adresse" required
                                            value="<?= htmlspecialchars($form_data['adresse'] ?? '') ?>"
                                            class="form-input w-full px-4 py-3" />
                                    </div>

                                    <!-- Ville -->
                                    <div>
                                        <label class="block text-gray-700 font-medium mb-2">
                                            <i class="fas fa-city text-green-600 mr-1"></i>Ville <span class="text-red-500">*</span>
                                        </label>
                                        <select name="ville" required class="form-input w-full px-4 py-3 appearance-none">
                                            <option value="">Sélectionner une ville</option>
                                            <option value="Cotonou" <?= isset($form_data['ville']) && $form_data['ville'] === 'Cotonou' ? 'selected' : '' ?>>Cotonou</option>
                                            <option value="Porto-Novo" <?= isset($form_data['ville']) && $form_data['ville'] === 'Porto-Novo' ? 'selected' : '' ?>>Porto-Novo</option>
                                            <option value="Abomey-Calavi" <?= isset($form_data['ville']) && $form_data['ville'] === 'Abomey-Calavi' ? 'selected' : '' ?>>Abomey-Calavi</option>
                                            <option value="Parakou" <?= isset($form_data['ville']) && $form_data['ville'] === 'Parakou' ? 'selected' : '' ?>>Parakou</option>
                                            <option value="Natitingou" <?= isset($form_data['ville']) && $form_data['ville'] === 'Natitingou' ? 'selected' : '' ?>>Natitingou</option>
                                            <option value="Ouidah" <?= isset($form_data['ville']) && $form_data['ville'] === 'Ouidah' ? 'selected' : '' ?>>Ouidah</option>
                                            <option value="Grand-Popo" <?= isset($form_data['ville']) && $form_data['ville'] === 'Grand-Popo' ? 'selected' : '' ?>>Grand-Popo</option>
                                            <option value="Bohicon" <?= isset($form_data['ville']) && $form_data['ville'] === 'Bohicon' ? 'selected' : '' ?>>Bohicon</option>
                                            <option value="Lokossa" <?= isset($form_data['ville']) && $form_data['ville'] === 'Lokossa' ? 'selected' : '' ?>>Lokossa</option>
                                            <option value="Abomey" <?= isset($form_data['ville']) && $form_data['ville'] === 'Abomey' ? 'selected' : '' ?>>Abomey</option>
                                            <option value="Autre" <?= isset($form_data['ville']) && $form_data['ville'] === 'Autre' ? 'selected' : '' ?>>Autre</option>
                                        </select>
                                    </div>

                                    <!-- Région -->
                                    <div>
                                        <label class="block text-gray-700 font-medium mb-2">
                                            <i class="fas fa-globe-africa text-green-600 mr-1"></i>Région <span class="text-red-500">*</span>
                                        </label>
                                        <select name="region" required class="form-input w-full px-4 py-3 appearance-none">
                                            <option value="">Sélectionner une région</option>
                                            <option value="Sud" <?= isset($form_data['region']) && $form_data['region'] === 'Sud' ? 'selected' : '' ?>>Sud</option>
                                            <option value="Centre" <?= isset($form_data['region']) && $form_data['region'] === 'Centre' ? 'selected' : '' ?>>Centre</option>
                                            <option value="Nord" <?= isset($form_data['region']) && $form_data['region'] === 'Nord' ? 'selected' : '' ?>>Nord</option>
                                            <option value="Autre" <?= isset($form_data['region']) && $form_data['region'] === 'Autre' ? 'selected' : '' ?>>Autre</option>
                                        </select>
                                    </div>

                                    <!-- Localisation -->
                                    <div>
                                        <label class="block text-gray-700 font-medium mb-2">
                                            <i class="fas fa-map-pin text-green-600 mr-1"></i>Localisation GPS
                                            <i class="fas fa-info-circle tooltip-icon ml-1" title="Cliquez sur la carte pour positionner votre destination"></i>
                                        </label>
                                        <div id="map" class="mb-3 w-full" style="height: 300px;"></div>
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-sm text-gray-600 mb-1">Latitude</label>
                                                <input type="text" name="latitude" id="latitude"
                                                    value="<?= htmlspecialchars($form_data['latitude'] ?? '') ?>"
                                                    class="form-input w-full px-3 py-2 text-sm" />
                                            </div>
                                            <div>
                                                <label class="block text-sm text-gray-600 mb-1">Longitude</label>
                                                <input type="text" name="longitude" id="longitude"
                                                    value="<?= htmlspecialchars($form_data['longitude'] ?? '') ?>"
                                                    class="form-input w-full px-3 py-2 text-sm" />
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Prix -->
                                    <div>
                                        <label class="block text-gray-700 font-medium mb-2">
                                            <i class="fas fa-ticket-alt text-green-600 mr-1"></i>Prix d'entrée (FCFA)
                                        </label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500">FCFA</span>
                                            </div>
                                            <input type="number" name="prix" min="0" step="500"
                                                value="<?= htmlspecialchars($form_data['prix'] ?? '') ?>"
                                                class="form-input pl-16 w-full px-4 py-3" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Media Tab -->
                                <div id="media-tab" class="tab-content hidden">
                                    <!-- Image principale -->
                                    <div>
                                        <label class="block text-gray-700 font-medium mb-2">
                                            <i class="fas fa-camera text-green-600 mr-1"></i>Image principale <span class="text-red-500">*</span>
                                            <i class="fas fa-info-circle tooltip-icon ml-1" title="Image qui représentera votre destination (format JPG/PNG, max 5MB)"></i>
                                        </label>
                                        <div id="mainImagePreview"></div>
                                        <div class="file-upload-label flex flex-col items-center justify-center w-full h-40 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition mb-2"
                                            id="mainImageLabel">
                                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                                <p class="text-sm text-gray-500" id="mainImageText">Cliquez pour télécharger</p>
                                            </div>
                                            <input type="file" name="image" accept="image/*" required class="hidden" id="mainImageInput" />
                                        </div>
                                        <div class="text-xs text-gray-500">Format recommandé : 1200x800px</div>
                                    </div>

                                    <!-- Vidéo -->
                                    <div>
                                        <label class="block text-gray-700 font-medium mb-2">
                                            <i class="fas fa-video text-green-600 mr-1"></i>Vidéo de présentation
                                        </label>
                                        <div class="file-upload-label flex flex-col items-center justify-center w-full h-40 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition mb-2"
                                            id="videoLabel">
                                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                                <p class="text-sm text-gray-500" id="videoText">Cliquez pour télécharger une vidéo</p>
                                            </div>
                                            <input type="file" name="video" accept="video/*" class="hidden" id="videoInput" />
                                        </div>
                                        <div class="text-xs text-gray-500">Formats acceptés : MP4, MOV (max 100MB)</div>
                                    </div>
                                </div>

                                <!-- Contact Tab (hidden by default) -->
                                <div id="contact-tab" class="tab-content hidden">
                                    <!-- Téléphone -->
                                    <div>
                                        <label class="block text-gray-700 font-medium mb-2">
                                            <i class="fas fa-phone-alt text-green-600 mr-1"></i>Téléphone <span class="text-red-500">*</span>
                                        </label>
                                        <input type="tel" name="telephone" required
                                            value="<?= htmlspecialchars($form_data['telephone'] ?? '') ?>"
                                            class="form-input w-full px-4 py-3"
                                            placeholder="+229 90 00 00 00" />
                                    </div>

                                    <!-- Email -->
                                    <div>
                                        <label class="block text-gray-700 font-medium mb-2">
                                            <i class="fas fa-envelope text-green-600 mr-1"></i>Email <span class="text-red-500">*</span>
                                        </label>
                                        <input type="email" name="email" required
                                            value="<?= htmlspecialchars($form_data['email'] ?? '') ?>"
                                            class="form-input w-full px-4 py-3"
                                            placeholder="contact@votredestination.com" />
                                    </div>

                                    <!-- Heures d'ouverture -->
                                    <div>
                                        <label class="block text-gray-700 font-medium mb-2">
                                            <i class="fas fa-clock text-green-600 mr-1"></i>Heures d'ouverture
                                        </label>
                                        <textarea name="horaires" rows="2"
                                            class="form-input w-full px-4 py-3"
                                            placeholder="Ex: Lundi-Vendredi: 9h-17h, Week-end: 10h-18h"><?= htmlspecialchars($form_data['horaires'] ?? '') ?></textarea>
                                    </div>

                                    <!-- Réseaux sociaux -->
                                    <div>
                                        <label class="block text-gray-700 font-medium mb-2">
                                            <i class="fas fa-share-alt text-green-600 mr-1"></i>Réseaux sociaux
                                        </label>
                                        <div class="space-y-3">
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-2">
                                                    <i class="fab fa-facebook-f text-blue-600"></i>
                                                </div>
                                                <input type="url" name="facebook" 
                                                    value="<?= htmlspecialchars($form_data['facebook'] ?? '') ?>"
                                                    class="form-input flex-1 px-4 py-2"
                                                    placeholder="Lien Facebook">
                                            </div>
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 bg-pink-100 rounded-full flex items-center justify-center mr-2">
                                                    <i class="fab fa-instagram text-pink-600"></i>
                                                </div>
                                                <input type="url" name="instagram" 
                                                    value="<?= htmlspecialchars($form_data['instagram'] ?? '') ?>"
                                                    class="form-input flex-1 px-4 py-2"
                                                    placeholder="Lien Instagram">
                                            </div>
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 bg-blue-400 rounded-full flex items-center justify-center mr-2">
                                                    <i class="fa-brands fa-tiktok text-white"></i>
                                                </div>
                                                <input type="url" name="tiktok" 
                                                    value="<?= htmlspecialchars($form_data['tiktok'] ?? '') ?>"
                                                    class="form-input flex-1 px-4 py-2"
                                                    placeholder="Lien Tiktok">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Navigation buttons -->
                                <div class="flex justify-between pt-4">
                                    <button type="button" id="prevBtn" class="hidden bg-gray-200 text-gray-700 font-semibold px-6 py-2 rounded-lg hover:bg-gray-300 transition">
                                        <i class="fas fa-arrow-left mr-2"></i> Précédent
                                    </button>
                                    <button type="button" id="nextBtn" class="ml-auto bg-green-100 text-green-700 font-semibold px-6 py-2 rounded-lg hover:bg-green-200 transition">
                                        Suivant <i class="fas fa-arrow-right ml-2"></i>
                                    </button>
                                    <button type="submit" id="submitBtn" class="hidden btn-primary w-full text-white font-semibold px-6 py-3 rounded-lg shadow-md transition duration-300">
                                        <i class="fas fa-paper-plane mr-2"></i> Soumettre la destination
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-16 bg-green-700 text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold mb-6">Prêt à partager votre destination ?</h2>
            <p class="text-xl mb-8 max-w-2xl mx-auto">
                Rejoignez notre réseau de partenaires et contribuez au développement du tourisme béninois
            </p>
        </div>
    </section>

    <?php include_once("../includes/footergest.php"); ?>


    <script>
        // Module de gestion de la carte
        const MapManager = {
            debug: false, // Désactivé en production
            map: null,
            marker: null,
            defaultLat: 6.3725,
            defaultLng: 2.3583,
            resizeAttempts: 0,
            maxResizeAttempts: 5,

            log: function(...args) {
                if (this.debug) {
                    console.log('[MapManager]', ...args);
                }
            },

            error: function(...args) {
                console.error('[MapManager]', ...args);
            },

            init: function() {
                try {
                    if (typeof L === 'undefined') {
                        this.error('Leaflet (L) n\'est pas défini');
                        this.showError('Bibliothèque de cartes non chargée');
                        return false;
                    }

                    this.createMap();
                    this.setupEventListeners();
                    return true;
                } catch (error) {
                    this.error('Erreur lors de l\'initialisation:', error);
                    this.showError(error.message);
                    return false;
                }
            },

            createMap: function() {
                try {
                    // Vérifier que Leaflet est chargé
                    if (typeof L === 'undefined') {
                        throw new Error('La bibliothèque Leaflet n\'est pas chargée');
                    }

                    // Vérifier que l'élément map existe
                    const mapElement = document.getElementById('map');
                    if (!mapElement) {
                        throw new Error('L\'élément #map est introuvable dans le DOM');
                    }

                    // Définir les dimensions du conteneur
                    mapElement.style.height = '400px';
                    mapElement.style.width = '100%';
                    mapElement.style.minHeight = '400px';

                    // Créer la carte avec une vue par défaut
                    this.map = L.map('map').setView([6.3725, 2.3583], 13);

                    // Ajouter la couche de tuiles OpenStreetMap
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                        maxZoom: 19,
                        detectRetina: true
                    }).addTo(this.map);

                    // Ajouter un marqueur par défaut
                    const marker = L.marker([6.3725, 2.3583]).addTo(this.map)
                        .bindPopup('Position par défaut')
                        .openPopup();

                    // Mettre à jour les champs de formulaire avec les coordonnées
                    document.getElementById('latitude').value = '6.3725';
                    document.getElementById('longitude').value = '2.3583';

                    // Gestion du clic sur la carte pour déplacer le marqueur
                    this.map.on('click', (e) => {
                        const { lat, lng } = e.latlng;
                        marker.setLatLng([lat, lng]);
                        marker.setPopupContent(`Position: ${lat.toFixed(6)}, ${lng.toFixed(6)}`)
                               .openPopup();
                        
                        // Mettre à jour les champs de formulaire
                        document.getElementById('latitude').value = lat.toFixed(6);
                        document.getElementById('longitude').value = lng.toFixed(6);
                    });

                    // Forcer un redimensionnement après un court délai
                    setTimeout(() => {
                        this.map.invalidateSize();
                    }, 100);

                    return true;
                } catch (error) {
                    this.error('Erreur création carte:', error);
                    this.showError('Erreur création carte');
                    return false;
                }
            },

            initMarker: function() {
                const latInput = document.getElementById('latitude');
                const lngInput = document.getElementById('longitude');

                if (latInput && lngInput && latInput.value && lngInput.value) {
                    const lat = parseFloat(latInput.value);
                    const lng = parseFloat(lngInput.value);
                    if (!isNaN(lat) && !isNaN(lng)) {
                        this.setMarker(lat, lng);
                        return;
                    }
                }

                // Marqueur par défaut
                this.setMarker(this.defaultLat, this.defaultLng);
            },

            setMarker: function(lat, lng) {
                if (this.marker) {
                    this.marker.setLatLng([lat, lng]);
                } else {
                    this.marker = L.marker([lat, lng]).addTo(this.map);
                }
                this.map.setView([lat, lng], 15);
            },

            scheduleResize: function() {
                if (this.resizeAttempts >= this.maxResizeAttempts) return;

                setTimeout(() => {
                    if (this.map) {
                        this.map.invalidateSize({ pan: false });
                        this.resizeAttempts++;
                        
                        // Vérifier si le redimensionnement a fonctionné
                        const bounds = this.map.getBounds();
                        if (bounds.isValid()) {
                            this.log('Carte correctement redimensionnée');
                        } else {
                            this.scheduleResize();
                        }
                    }
                }, 200 * (this.resizeAttempts + 1));
            },

            setupEventListeners: function() {
                if (!this.map) return;

                // Clic sur la carte
                this.map.on('click', (e) => {
                    const { lat, lng } = e.latlng;
                    document.getElementById('latitude').value = lat.toFixed(6);
                    document.getElementById('longitude').value = lng.toFixed(6);
                    this.setMarker(lat, lng);
                });

                // Changement des inputs
                const updateFromInputs = () => {
                    const lat = parseFloat(document.getElementById('latitude').value);
                    const lng = parseFloat(document.getElementById('longitude').value);
                    if (!isNaN(lat) && !isNaN(lng)) {
                        this.setMarker(lat, lng);
                    }
                };

                document.getElementById('latitude')?.addEventListener('change', updateFromInputs);
                document.getElementById('longitude')?.addEventListener('change', updateFromInputs);
            },

            showError: function(message) {
                const mapContainer = document.getElementById('map');
                if (mapContainer) {
                    mapContainer.innerHTML = `
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">Erreur carte</strong>
                            <span class="block sm:inline">${message}</span>
                        </div>
                    `;
                }
            }
        };

        // Module de gestion des onglets
        const TabManager = {
            currentTab: 0, // Index de l'onglet actuel
            tabs: ['info', 'media', 'contact'], // Liste des onglets dans l'ordre
            
            init: function() {
                this.setupTabs();
                this.setupTabButtons();
                this.switchTab('info'); // Afficher l'onglet info par défaut
            },
            
            setupTabs: function() {
                // Récupérer tous les boutons d'onglets
                const tabButtons = document.querySelectorAll('[data-tab]');
                
                tabButtons.forEach(button => {
                    button.addEventListener('click', (e) => {
                        e.preventDefault();
                        const tabId = button.getAttribute('data-tab');
                        this.switchTab(tabId);
                    });
                });
            },
            
            setupTabButtons: function() {
                const nextBtn = document.getElementById('nextBtn');
                const prevBtn = document.getElementById('prevBtn');
                
                if (nextBtn) nextBtn.addEventListener('click', () => this.nextTab());
                if (prevBtn) prevBtn.addEventListener('click', () => this.prevTab());
            },
            
            nextTab: function() {
                if (this.currentTab < this.tabs.length - 1) {
                    this.currentTab++;
                    this.switchTab(this.tabs[this.currentTab]);
                }
            },
            
            prevTab: function() {
                if (this.currentTab > 0) {
                    this.currentTab--;
                    this.switchTab(this.tabs[this.currentTab]);
                }
            },
            
            switchTab: function(tabId) {
                console.log('Changement vers l\'onglet:', tabId);
                
                // Mettre à jour l'index de l'onglet actif
                const tabIndex = this.tabs.indexOf(tabId);
                if (tabIndex !== -1) {
                    this.currentTab = tabIndex;
                }
                
                // Mettre à jour les boutons d'onglets
                const tabButtons = document.querySelectorAll('[data-tab]');
                tabButtons.forEach(button => {
                    if (button.getAttribute('data-tab') === tabId) {
                        button.classList.add('tab-active', 'text-green-600');
                        button.classList.remove('text-gray-500', 'hover:text-green-600');
                    } else {
                        button.classList.remove('tab-active', 'text-green-600');
                        button.classList.add('text-gray-500', 'hover:text-green-600');
                    }
                });
                
                // Afficher le contenu de l'onglet actif
                const tabContents = document.querySelectorAll('.tab-content');
                tabContents.forEach(content => {
                    if (content.id === `${tabId}-tab`) {
                        content.classList.remove('hidden');
                    } else {
                        content.classList.add('hidden');
                    }
                });
                
                // Mettre à jour les boutons de navigation
                this.updateNavButtons(tabId);
                
                // Si on passe à l'onglet média, on rafraîchit la carte
                if (tabId === 'media' && window.MapManager && window.MapManager.map) {
                    setTimeout(() => {
                        window.MapManager.map.invalidateSize();
                    }, 100);
                }
                
                // Si on passe à l'onglet info, on rafraîchit la carte aussi
                if (tabId === 'info' && window.MapManager && window.MapManager.map) {
                    window.MapManager.scheduleResize();
                }
            },
            
            updateNavButtons: function(tabId) {
                const prevBtn = document.getElementById('prevBtn');
                const nextBtn = document.getElementById('nextBtn');
                const submitBtn = document.getElementById('submitBtn');
                
                // Afficher/masquer les boutons en fonction de l'onglet actuel
                if (tabId === 'info') {
                    if (prevBtn) prevBtn.classList.add('hidden');
                    if (nextBtn) nextBtn.classList.remove('hidden');
                    if (submitBtn) submitBtn.classList.add('hidden');
                } else if (tabId === 'media') {
                    if (prevBtn) prevBtn.classList.remove('hidden');
                    if (nextBtn) nextBtn.classList.remove('hidden');
                    if (submitBtn) submitBtn.classList.add('hidden');
                } else if (tabId === 'contact') {
                    if (prevBtn) prevBtn.classList.remove('hidden');
                    if (nextBtn) nextBtn.classList.add('hidden');
                    if (submitBtn) submitBtn.classList.remove('hidden');
                }
            }
        };

        // Module de gestion de l'éditeur de texte
        const TextEditor = {
            init: function() {
                this.setupTextFormatting();
                this.setupWordCounter();
                this.setupPreview();
                
                // Initialiser le compteur au chargement
                this.updateCounter();
            },

            setupTextFormatting: function() {
                // Les boutons de formatage sont gérés directement dans le HTML avec onclick
            },

            setupWordCounter: function() {
                const textarea = document.querySelector('#richDescription');
                const countDisplay = document.getElementById('descCount');
                
                if (textarea && countDisplay) {
                    textarea.addEventListener('input', () => this.updateCounter(textarea, countDisplay));
                    this.updateCounter(textarea, countDisplay);
                }
            },

            setupPreview: function() {
                const previewToggle = document.querySelector('[data-action="toggle-preview"]');
                if (previewToggle) {
                    previewToggle.addEventListener('click', (e) => {
                        e.preventDefault();
                        this.togglePreview();
                    });
                }
            },

            insertText: function(before, after) {
                const textarea = document.querySelector('#richDescription');
                if (!textarea) return;

                const start = textarea.selectionStart;
                const end = textarea.selectionEnd;
                const selectedText = textarea.value.substring(start, end);
                const beforeText = textarea.value.substring(0, start);
                const afterText = textarea.value.substring(end);
                
                // Si du texte est sélectionné, on l'entoure des caractères
                // Sinon, on insère les caractères et on place le curseur entre eux
                if (start === end) {
                    textarea.value = beforeText + before + after + afterText;
                    // Placer le curseur entre les caractères insérés
                    const newPos = start + before.length;
                    textarea.selectionStart = newPos;
                    textarea.selectionEnd = newPos;
                } else {
                    textarea.value = beforeText + before + selectedText + after + afterText;
                    // Sélectionner le texte modifié
                    textarea.selectionStart = start;
                    textarea.selectionEnd = start + before.length + selectedText.length + after.length;
                }
                
                textarea.focus();
                
                // Mettre à jour le compteur et la prévisualisation
                this.updateCounter();
                this.updatePreview();
                
                // Déclencher l'événement input pour les autres écouteurs
                const event = new Event('input', { bubbles: true });
                textarea.dispatchEvent(event);
            },

            updateCounter: function() {
                const textarea = document.querySelector('#richDescription');
                const countDisplay = document.getElementById('descCount');
                
                if (!textarea || !countDisplay) return;

                const text = textarea.value.trim();
                // Compter les mots de manière plus précise
                const wordCount = text ? text.split(/\s+/).filter(word => word.length > 0).length : 0;
                
                countDisplay.textContent = wordCount;
                
                // Mettre à jour les classes en fonction du nombre de mots
                countDisplay.classList.remove('text-red-500', 'text-green-600');
                if (wordCount < 100 || wordCount > 300) {
                    countDisplay.classList.add('text-red-500');
                } else {
                    countDisplay.classList.add('text-green-600');
                }
            },

            updatePreview: function() {
                console.log('Début de updatePreview');
                const preview = document.getElementById('descriptionPreview');
                const textarea = document.querySelector('#richDescription');
                
                if (!preview) {
                    console.error('Élément descriptionPreview introuvable');
                    return;
                }
                
                if (!textarea) {
                    console.error('Élément richDescription introuvable');
                    return;
                }

                const markdown = textarea.value.trim() || 'Aucun contenu à prévisualiser.';
                console.log('Markdown à rendre:', markdown);
                
                try {
                    console.log('Vérification de marked...');
                    console.log('Type de marked:', typeof marked);
                    
                    if (typeof marked === 'undefined' && typeof window.marked === 'undefined') {
                        console.error('Marked.js n\'est pas disponible');
                        throw new Error('La bibliothèque Marked.js n\'est pas chargée');
                    }
                    
                    console.log('Appel de renderMarkdown...');
                    const html = window.renderMarkdown ? 
                        window.renderMarkdown(markdown) : 
                        'Erreur: fonction renderMarkdown non disponible';
                        
                    console.log('HTML généré:', html);
                    preview.innerHTML = html;
                    
                } catch (e) {
                    console.error('Erreur lors du rendu Markdown:', e);
                    // Afficher le texte brut avec l'erreur
                    preview.innerHTML = `
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        Aperçu non disponible : ${e.message}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded border border-gray-200">
                            <pre class="whitespace-pre-wrap">${markdown}</pre>
                        </div>
                    `;
                }
            },
            
            // Cette fonction n'est plus nécessaire car on charge directement les bibliothèques dans le head
            
            showMarkdownError: function(element, message) {
                if (!element) return;
                
                element.innerHTML = `
                    <div class="bg-red-50 border-l-4 border-red-400 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-circle text-red-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700">
                                    ${message} Le texte sera enregistré normalement.
                                </p>
                            </div>
                        </div>
                    </div>`;
            },

            togglePreview: function() {
                const container = document.getElementById('previewContainer');
                const previewButton = document.querySelector('button[onclick*="togglePreview"]');
                
                if (container && previewButton) {
                    const isHidden = container.classList.contains('hidden');
                    container.classList.toggle('hidden');
                    
                    // Mettre à jour l'icône du bouton
                    const icon = previewButton.querySelector('i');
                    if (icon) {
                        if (isHidden) {
                            icon.classList.remove('fa-eye');
                            icon.classList.add('fa-eye-slash');
                            // Mettre à jour la prévisualisation uniquement si elle est visible
                            if (!container.classList.contains('hidden')) {
                                this.updatePreview();
                            }
                        } else {
                            icon.classList.remove('fa-eye-slash');
                            icon.classList.add('fa-eye');
                        }
                    }
                }
            },
            
            // Fonction pour mettre à jour la prévisualisation
            updatePreview: function() {
                console.log('Mise à jour de l\'aperçu...');
                const preview = document.getElementById('descriptionPreview');
                const textarea = document.querySelector('#richDescription');
                
                if (!preview) {
                    console.error('Élément descriptionPreview introuvable');
                    return;
                }
                
                if (!textarea) {
                    console.error('Élément richDescription introuvable');
                    return;
                }
                
                const markdown = textarea.value.trim() || 'Aucun contenu à prévisualiser.';
                
                try {
                    console.log('Vérification de marked...');
                    console.log('Type de marked:', typeof marked);
                    
                    if (typeof marked === 'undefined' && typeof window.marked === 'undefined') {
                        const errorMsg = 'Erreur : La bibliothèque de rendu Markdown n\'est pas chargée';
                        console.error(errorMsg);
                        preview.innerHTML = `<p class="text-red-500">${errorMsg}<br>Marked.js: ${typeof marked}<br>window.marked: ${typeof window.marked}</p>`;
                        return;
                    }
                    
                    console.log('Rendu du markdown...');
                    const html = window.renderMarkdown ? 
                        window.renderMarkdown(markdown) : 
                        marked.parse(markdown);
                        
                    console.log('HTML généré:', html);
                    preview.innerHTML = html;
                    
                } catch (e) {
                    console.error('Erreur lors du rendu de la prévisualisation :', e);
                    preview.innerHTML = `
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        Aperçu non disponible : ${e.message}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded border border-gray-200">
                            <pre class="whitespace-pre-wrap">${markdown}</pre>
                        </div>
                    `;
                }
            }
        };

        // Module de gestion des fichiers
        const FileUploadManager = {
            debug: true, // Activer les logs de débogage
            
            log: function(...args) {
                if (this.debug) {
                    console.log('[FileUpload]', ...args);
                }
            },
            
            error: function(...args) {
                console.error('[FileUpload]', ...args);
            },
            
            init: function() {
                this.log('Initialisation du gestionnaire de fichiers...');
                
                // Configuration pour l'upload d'image
                this.setupFileUpload({
                    inputId: 'mainImageInput',
                    labelId: 'mainImageLabel',
                    textId: 'mainImageText',
                    acceptTypes: ['image/jpeg', 'image/png', 'image/webp'],
                    previewId: 'mainImagePreview',
                    maxSize: 5 * 1024 * 1024, // 5MB
                    type: 'image'
                });

                // Configuration pour l'upload de vidéo
                this.setupFileUpload({
                    inputId: 'videoInput',
                    labelId: 'videoLabel',
                    textId: 'videoText',
                    acceptTypes: ['video/mp4', 'video/webm', 'video/ogg'],
                    previewId: null,
                    maxSize: 50 * 1024 * 1024, // 50MB
                    type: 'video'
                });
            },

            setupFileUpload: function({ inputId, labelId, textId, acceptTypes = [], previewId = null, maxSize = 0, type = 'file' }) {
                this.log(`Configuration de l'upload pour ${type} (${inputId})`);
                
                const input = document.getElementById(inputId);
                const label = document.getElementById(labelId);
                const text = document.getElementById(textId);
                const preview = previewId ? document.getElementById(previewId) : null;

                if (!input) {
                    this.error(`Élément input non trouvé: #${inputId}`);
                    return;
                }
                if (!label) {
                    this.error(`Élément label non trouvé: #${labelId}`);
                    return;
                }
                if (!text) {
                    this.error(`Élément text non trouvé: #${textId}`);
                    return;
                }
                
                this.log(`Éléments trouvés pour ${type}:`, { input, label, text, preview });

                // Gestionnaire de clic sur le label
                label.addEventListener('click', (e) => {
                    // Empêcher le comportement par défaut pour éviter tout conflit
                    e.preventDefault();
                    this.log(`Clic sur le label ${labelId}`);
                    
                    // Déclencher le clic sur l'input file
                    input.click();
                });
                
                // S'assurer que le clic sur la zone de dépôt déclenche bien l'input file
                label.style.cursor = 'pointer';
                
                // Gestionnaire de clic direct sur l'input (au cas où)
                input.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.log(`Clic sur l'input ${inputId}`);
                });

                // Gestionnaire de changement de fichier
                input.addEventListener('change', () => {
                    this.log(`Changement détecté pour ${type}:`, input.files);
                    
                    const files = input.files;
                    if (!files || files.length === 0) {
                        this.log('Aucun fichier sélectionné');
                        this.resetUpload(label, text, preview);
                        return;
                    }

                    // Validation des fichiers
                    const validFiles = Array.from(files).filter(file => {
                        const typeValid = acceptTypes.includes(file.type);
                        const sizeValid = maxSize === 0 || file.size <= maxSize;
                        return typeValid && sizeValid;
                    });

                    if (validFiles.length === 0) {
                        this.resetUpload(label, text, preview);
                        text.textContent = 'Fichier(s) invalide(s)';
                        return;
                    }

                    // Mise à jour de l'interface
                    const fileNames = validFiles.map(f => this.sanitizeFilename(f.name));
                    text.textContent = fileNames.length === 1 ? fileNames[0] : `${fileNames.length} fichiers valides`;
                    label.classList.add('border-green-500', 'bg-green-50');

                    // Prévisualisation si demandée
                    if (preview && validFiles[0].type.startsWith('image/')) {
                        this.showImagePreview(validFiles[0], preview);
                    }
                });
            },

            resetUpload: function(label, text, preview) {
                text.textContent = 'Cliquez pour télécharger';
                label.classList.remove('border-green-500', 'bg-green-50');
                if (preview) preview.innerHTML = '';
            },

            showImagePreview: function(file, previewElement) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    previewElement.innerHTML = `
                        <img src="${e.target.result}" 
                             alt="Prévisualisation" 
                             class="mt-2 rounded shadow-md max-w-full h-auto max-h-48">
                    `;
                };
                reader.readAsDataURL(file);
            },

            sanitizeFilename: function(name) {
                // Supprime les caractères spéciaux potentiellement dangereux
                return name.replace(/[^\w.-]/g, '_');
            }
        };

        // Fonction utilitaire pour attendre que le DOM soit chargé
        function domReady(selector, callback) {
            if (document.readyState === 'complete' || document.readyState === 'interactive') {
                // Attendre le prochain tick pour s'assurer que tout est chargé
                setTimeout(() => {
                    if (document.querySelector(selector)) {
                        callback();
                    } else {
                        const observer = new MutationObserver(() => {
                            if (document.querySelector(selector)) {
                                observer.disconnect();
                                callback();
                            }
                        });
                        observer.observe(document.body, { 
                            childList: true, 
                            subtree: true 
                        });
                        
                        // Timeout au cas où l'élément n'est jamais trouvé
                        setTimeout(() => {
                            observer.disconnect();
                            if (document.querySelector(selector)) {
                                callback();
                            } else {
                                console.warn(`L'élément ${selector} n'a pas été trouvé après 5 secondes`);
                            }
                        }, 5000);
                    }
                }, 0);
            } else {
                window.addEventListener('DOMContentLoaded', () => {
                    domReady(selector, callback);
                });
            }
        }

        // Initialisation de l'application
        function initApp() {
            try {
                console.log('Initialisation de l\'application...');
                
                // Attendre que les éléments nécessaires soient chargés
                domReady('#mainImageInput', () => {
                    console.log('Éléments d\'upload détectés, initialisation...');
                    
                    // Initialiser les composants dans l'ordre
                    TabManager.init();
                    
                    if (MapManager.init()) {
                        // Plusieurs tentatives de redimensionnement
                        [100, 300, 500, 1000].forEach(delay => {
                            setTimeout(() => {
                                if (MapManager.map) MapManager.map.invalidateSize();
                            }, delay);
                        });
                    }

                    TextEditor.init();
                    FileUploadManager.init();
                });

            } catch (error) {
                console.error("Erreur d'initialisation:", error);
                MapManager.showError('Erreur lors de l\'initialisation de l\'application');
            }
        }

        // Initialiser l'application quand le DOM est chargé
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initApp);
        } else {
            // Le DOM est déjà chargé
            initApp();
        }

</script>
</body>
</html>
