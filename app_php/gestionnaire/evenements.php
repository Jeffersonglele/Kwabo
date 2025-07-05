<?php
// Démarrer la session si pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclure d'abord les fichiers de configuration et de contrôle d'accès
require_once(__DIR__ . "/../config/database.php");
require_once(__DIR__ . "/../config/access_control.php");

// Vérifier que l'utilisateur est connecté et a le bon type de compte
check_access(ALLOWED_EVENEMENT);

// Gestion des messages de succès
if (isset($_GET['success'])) {
    if ($_GET['success'] == 1 && isset($_GET['type']) && $_GET['type'] == 'evenement') {
        $_SESSION['success_message'] = "L'événement a été ajouté avec succès !";
        header("Location: evenements.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Proposez vos événements touristiques au Bénin et partagez-les avec notre communauté">
    <title>Proposer un Événement - Bénin Tourisme</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="../assets/favicon/favicon.svg">
    <link rel="icon" type="image/png" sizes="96x96" href="../assets/favicon/favicon-96x96.png">
    <link rel="shortcut icon" href="../assets/favicon/favicon.ico">
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lucide@latest/dist/css/lucide.min.css">
    
    <style>
        :root {
            --primary-color: #3b82f6;
            --primary-hover: #2563eb;
        }
        
        .hero-section {
            height: 60vh;
            min-height: 400px;
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        
        .hero-overlay {
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.7));
        }
        
        .hero-title {
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.8s ease-out;
        }
        
        .info-card {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }
        
        .form-input {
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        
        .form-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }
        
        .submit-btn {
            transition: all 0.3s ease;
            background-color: var(--primary-color);
        }
        
        .submit-btn:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @media (max-width: 768px) {
            .hero-section {
                height: 50vh;
                background-attachment: scroll;
            }
            
            .grid-cols-1 {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased">
<?php include_once(__DIR__ . "/../includes/navbargest.php"); ?>

<!-- Hero Section -->
<section class="hero-section relative" style="background-image: url('../assets/images/ev.jpeg');">
    <div class="hero-overlay absolute inset-0"></div>
    <div class="container mx-auto px-4 h-full">
        <div class="hero-content relative z-10 flex flex-col justify-center items-center h-full text-center">
            <h1 class="hero-title text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-4">
                Proposez un évènement
            </h1>
            <p class="text-xl md:text-2xl text-white opacity-90 max-w-2xl mx-auto">
                Partagez vos événements culturels et touristiques avec notre communauté
            </p>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <!-- Messages de statut -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded-lg shadow-sm">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-3"></i>
                    <p class="font-medium"><?= htmlspecialchars($_SESSION['success_message']) ?></p>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-lg shadow-sm">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                    <p class="font-medium"><?= htmlspecialchars($_SESSION['error_message']) ?></p>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($_SESSION['form_errors'])): ?>
            <div class="mb-6 p-4 bg-yellow-50 border-l-4 border-yellow-500 text-yellow-700 rounded-lg shadow-sm">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-yellow-500 mt-1 mr-3"></i>
                    <div>
                        <p class="font-medium mb-2">Veuillez corriger les erreurs suivantes :</p>
                        <ul class="list-disc pl-5 space-y-1">
                            <?php foreach ($_SESSION['form_errors'] as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <?php unset($_SESSION['form_errors']); ?>
            </div>
        <?php endif; ?>
        
        <?php 
        // Récupérer les données du formulaire en cas d'erreur
        $form_data = $_SESSION['form_data'] ?? [];
        unset($_SESSION['form_data']);
        ?>
        
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Informations principales -->
            <div class="lg:w-2/3">
                <div class="info-card bg-white rounded-xl p-6 md:p-8 mb-8">
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-800 mb-6">Proposez vos Événements Touristiques !</h2>
                    
                    <div class="prose max-w-none text-gray-700 space-y-4">
                        <p class="text-lg">
                            Vous êtes organisateur ou gestionnaire d'un événement culturel, touristique, festif ou artistique ?
                            Vous connaissez un festival, une exposition, une célébration traditionnelle ou toute autre activité capable d'attirer les visiteurs dans votre région ?
                        </p>
                        
                        <p class="text-lg font-semibold text-blue-600">
                            Partagez-le avec nous !
                        </p>
                        
                        <p>
                            Nous vous offrons la possibilité d'inscrire gratuitement vos événements sur notre plateforme pour leur donner plus de visibilité auprès des touristes et passionnés de découvertes.
                        </p>
                        
                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                            <p class="font-medium text-blue-800 flex items-center">
                                <i class="fas fa-info-circle mr-2"></i>
                                Remplissez simplement le formulaire ci-contre pour nous proposer un événement.
                            </p>
                        </div>
                        
                        <p>
                            Qu'il s'agisse d'un festival local, d'un marché artisanal, d'un spectacle ou d'un événement historique, votre contribution aide à faire rayonner la richesse culturelle de notre territoire.
                        </p>
                        
                    </div>
                </div>
            </div>

            <!-- Formulaire -->
            <div class="lg:w-1/3" id="event-form">
                <div class="sticky top-8 bg-white p-6 md:p-8 rounded-xl shadow-lg">
                    <h2 class="text-2xl font-bold mb-6 text-gray-800 text-center">
                        <i class="fas fa-calendar-plus mr-2"></i> Ajouter un événement
                    </h2>

                    <form action="traitement_evenement.php" method="POST" enctype="multipart/form-data" class="space-y-5" id="eventForm">
                        <!-- Nom -->
                        <div>
                            <label class="block text-sm font-medium mb-1 flex items-center text-gray-700">
                                <i class="fas fa-heading mr-2 text-blue-500"></i> Nom de l'événement
                            </label>
                            <input type="text" name="nom" required 
                                class="form-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                value="<?= htmlspecialchars($form_data['nom'] ?? '') ?>">
                        </div>
                        
                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium mb-1 flex items-center text-gray-700">
                                <i class="fas fa-align-left mr-2 text-blue-500"></i> Description
                            </label>
                            <textarea name="description" rows="4" required 
                                      class="form-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                      placeholder="Décrivez l'événement en détail..."><?= htmlspecialchars($form_data['description'] ?? '') ?></textarea>
                        </div>
                        
                        <!-- Dates et Heure -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1 flex items-center text-gray-700">
                                    <i class="fas fa-calendar-day mr-2 text-blue-500"></i> Date de début
                                </label>
                                <input type="date" name="date_debut" required 
                                       class="form-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       value="<?= htmlspecialchars($form_data['date_debut'] ?? '') ?>">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1 flex items-center text-gray-700">
                                    <i class="fas fa-calendar-day mr-2 text-blue-500"></i> Date de fin
                                </label>
                                <input type="date" name="date_fin" required 
                                       class="form-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       value="<?= htmlspecialchars($form_data['date_fin'] ?? '') ?>">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-1 flex items-center text-gray-700">
                                <i class="fas fa-clock mr-2 text-blue-500"></i> Heure
                            </label>
                            <input type="time" name="heure" required 
                                class="form-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                value="<?= htmlspecialchars($form_data['heure'] ?? '') ?>">
                        </div>

                        <!-- Lieu -->
                        <div>
                            <label class="block text-sm font-medium mb-1 flex items-center text-gray-700">
                                <i class="fas fa-map-marker-alt mr-2 text-blue-500"></i> Lieu (Ville/Adresse)
                            </label>
                            <input type="text" name="ville" required 
                                class="form-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                placeholder="Ex: Cotonou, Place de l'Étoile Rouge"
                                value="<?= htmlspecialchars($form_data['ville'] ?? '') ?>">
                        </div>

                        <!-- Prix -->
                        <div>
                            <label class="block text-sm font-medium mb-1 flex items-center text-gray-700">
                                <i class="fas fa-ticket-alt mr-2 text-blue-500"></i> Prix (en FCFA)
                            </label>
                            <div class="relative">
                                <input type="number" name="prix" min="0" step="500" 
                                       class="form-input w-full pl-12 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                       placeholder="0 pour gratuit"
                                       value="<?= htmlspecialchars($form_data['prix'] ?? '0') ?>">
                            </div>
                        </div>

                        <!-- Image -->
                        <div>
                            <label class="block text-sm font-medium mb-1 flex items-center text-gray-700">
                                <i class="fas fa-image mr-2 text-blue-500"></i> Image (affiche ou bannière)
                                
                            </label>
                            <div class="flex items-center justify-center w-full"
                            id="galleryLabel">
                                <label class="flex flex-col w-full border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-blue-500 transition">
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6 px-4">
                                        <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                        <p class="text-sm text-gray-500 text-center">
                                            <span class="font-semibold">Cliquez pour uploader</span><br>
                                            ou glissez-déposez une image
                                        </p>
                                        <p class="text-xs text-gray-400 mt-1">JPEG, PNG (Max. 5MB)</p>
                                    </div>
                                    <input type="file" name="image" accept="image/*" required class="hidden">
                                </label>
                            </div>
                        </div>

                        <!-- Site web -->
                        <div>
                            <label class="block text-sm font-medium mb-1 flex items-center text-gray-700">
                                <i class="fas fa-globe mr-2 text-blue-500"></i> Site web 
                            </label>
                            <input type="url" name="site" 
                                class="form-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                placeholder="https://example.com"
                                value="<?= htmlspecialchars($form_data['site'] ?? '') ?>">
                        </div>

                        <!-- Phrase d'incitation -->
                        <div>
                            <label class="block text-sm font-medium mb-1 flex items-center text-gray-700">
                                <i class="fas fa-bullhorn mr-2 text-blue-500"></i> Phrase d'accroche
                            </label>
                            <input type="text" name="incitation" 
                                class="form-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                placeholder="Ex: Ne manquez pas cet événement unique !"
                                value="<?= htmlspecialchars($form_data['incitation'] ?? '') ?>">
                        </div>

                        <!-- Bouton -->
                        <div class="text-center pt-2">
                            <button type="submit" class="submit-btn text-white px-6 py-3 rounded-lg w-full flex items-center justify-center">
                                <i class="fas fa-paper-plane mr-2"></i> Soumettre l'événement
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include_once("../includes/footergest.php"); ?>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/lucide@latest/dist/umd/lucide.min.js"></script>
<script>
    // Initialiser les icônes Lucide
    lucide.createIcons();
    // Gestion de l'upload d'image
    // Fonction pour gérer l'upload des fichiers
    function setupFileUpload(inputId, labelId, textId) {
        const input = document.getElementById(inputId);
        const label = document.getElementById(labelId);
        const text = document.getElementById(textId);
        
        if (!input || !label) return;
        
        // Mettre à jour le texte du label quand un fichier est sélectionné
        input.addEventListener('change', function() {
            if (this.files && this.files.length > 0) {
                const fileNames = [];
                for (let i = 0; i < this.files.length; i++) {
                    fileNames.push(this.files[i].name);
                }
                if (text) {
                    text.textContent = fileNames.length > 1 ? 
                        `${fileNames.length} fichiers sélectionnés` : 
                        fileNames[0];
                }
                label.classList.add('file-selected');
            } else {
                if (text) text.textContent = 'Aucun fichier sélectionné';
                label.classList.remove('file-selected');
            }
        });
        
        // Gestion du glisser-déposer
        label.addEventListener('dragover', (e) => {
            e.preventDefault();
            label.classList.add('dragover');
        });
        
        label.addEventListener('dragleave', () => {
            label.classList.remove('dragover');
        });
        
        label.addEventListener('drop', (e) => {
            e.preventDefault();
            label.classList.remove('dragover');
            
            if (e.dataTransfer.files.length > 0) {
                input.files = e.dataTransfer.files;
                const event = new Event('change');
                input.dispatchEvent(event);
            }
        });
    }
    
    // Initialisation des gestionnaires de téléchargement de fichiers
    setupFileUpload('mainImageInput', 'mainImageLabel', 'mainImageText');
    setupFileUpload('galleryInput', 'galleryLabel', 'galleryText');
    setupFileUpload('videoInput', 'videoLabel', 'videoText');

    
    // Validation des dates
    document.querySelector('input[name="date_debut"]').addEventListener('change', function() {
        const endDateInput = document.querySelector('input[name="date_fin"]');
        if (this.value && endDateInput.value && this.value > endDateInput.value) {
            endDateInput.value = this.value;
        }
        endDateInput.min = this.value;
    });
</script>
</body>
</html>