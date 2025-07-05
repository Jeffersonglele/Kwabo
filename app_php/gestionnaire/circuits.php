<?php
// Démarrer la session en premier
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclure les fichiers nécessaires
require_once(__DIR__ . "/../config/database.php");
require_once(__DIR__ . "/../config/access_control.php");
require_once(__DIR__ . "/../includes/navbargest.php");

// Vérifier les permissions
check_access(ALLOWED_CIRCUIT);

// Gestion des messages
if (isset($_SESSION['message'])) {
    header("Location: circuits.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Proposez votre circuit touristique au Bénin et partagez-le avec notre communauté">
    <title>Proposer un Circuit - Bénin Tourisme</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="../assets/favicon/favicon.svg">
    <link rel="icon" type="image/png" sizes="96x96" href="../assets/favicon/favicon-96x96.png">
    <link rel="shortcut icon" href="../assets/favicon/favicon.ico">
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        
        .upload-area {
            transition: all 0.3s ease;
        }
        
        .upload-area:hover {
            border-color: var(--primary-color);
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
<section class="hero-section relative" style="background-image: url('../assets/images/cir.jpeg');">
    <div class="hero-overlay absolute inset-0"></div>
    <div class="container mx-auto px-4 h-full">
        <div class="hero-content relative z-10 flex flex-col justify-center items-center h-full text-center">
            <h1 class="hero-title text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-4">
                Proposez Votre Circuit
            </h1>
            <p class="text-xl md:text-2xl text-white opacity-90 max-w-2xl mx-auto">
                Partagez vos circuits touristiques avec notre communauté
            </p>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Informations principales -->
            <div class="lg:w-2/3">
                <div class="info-card bg-white rounded-xl p-6 md:p-8 mb-8">
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-800 mb-6">Proposez votre circuit touristique dès aujourd'hui !</h2>
                    
                    <div class="prose max-w-none text-gray-700 space-y-4">
                        <p class="text-lg">
                            Vous êtes guide, agence de voyage ou gestionnaire d'un circuit touristique au Bénin ?
                            Vous connaissez des lieux uniques, des expériences authentiques ou des parcours captivants qui méritent d'être découverts ?
                        </p>
                        
                        <p class="text-lg font-semibold text-blue-600">
                            Rejoignez notre plateforme et faites rayonner votre offre !
                        </p>
                        
                        <p>
                            Nous vous offrons la possibilité de référencer gratuitement vos circuits touristiques pour leur donner plus de visibilité auprès des voyageurs.
                        </p>
                        
                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                            <p class="font-medium text-blue-800 flex items-center">
                                <i class="fas fa-info-circle mr-2"></i>
                                Remplissez simplement le formulaire ci-contre pour nous proposer votre circuit.
                            </p>
                        </div>
                        
                        <h3 class="text-xl font-bold mt-6 text-gray-800">Avantages :</h3>
                        <ul class="space-y-2">
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                                <span>Visibilité accrue auprès des touristes</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                                <span>Promotion gratuite de vos circuits</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                                <span>Accès à une communauté de voyageurs</span>
                            </li>
                        </ul>
                        
                    </div>
                </div>
            </div>

            <!-- Formulaire -->
            <div class="lg:w-1/3" id="circuit-form">
                <div class="sticky top-8 bg-white p-6 md:p-8 rounded-xl shadow-lg">
                    <h2 class="text-2xl font-bold mb-6 text-gray-800 text-center">
                        <i class="fas fa-route mr-2 text-blue-500"></i> Formulaire de circuit
                    </h2>

                    <form action="traitement_circuit.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                        <!-- Section 1: Informations de base -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="font-bold text-lg mb-3 text-gray-700 flex items-center">
                                <i class="fas fa-info-circle mr-2 text-blue-500"></i>
                                Informations de base
                            </h3>
                            
                            <!-- Nom -->
                            <div class="mb-4">
                                <label class="block font-medium mb-1 flex items-center text-gray-700">
                                    <i class="fas fa-heading mr-2 text-blue-500"></i> Nom du circuit*
                                </label>
                                <input type="text" name="nom" required 
                                       class="form-input w-full px-4 py-2 border border-gray-300 rounded-lg">
                            </div>
                            
                            <!-- Sous-titre -->
                            <div class="mb-4">
                                <label class="block font-medium mb-1 flex items-center text-gray-700">
                                    <i class="fas fa-tag mr-2 text-blue-500"></i> Sous-titre*
                                </label>
                                <input type="text" name="sous_titre" required
                                       class="form-input w-full px-4 py-2 border border-gray-300 rounded-lg"
                                       placeholder="Ex: Découverte culturelle en 3 jours">
                            </div>
                            
                            <!-- Type -->
                            <div class="mb-4">
                                <label class="block font-medium mb-1 flex items-center text-gray-700">
                                    <i class="fas fa-tags mr-2 text-blue-500"></i> Type de circuit*
                                </label>
                                <select name="type" required 
                                        class="form-input w-full px-4 py-2 border border-gray-300 rounded-lg">
                                    <option value="">Choisir un type</option>
                                    <option value="Culturel">Culturel</option>
                                    <option value="Aventure">Aventure</option>
                                    <option value="Historique">Historique</option>
                                    <option value="Nature">Nature</option>
                                    <option value="Religieux">Religieux</option>
                                    <option value="Gastronomique">Gastronomique</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Section 2: Description -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="font-bold text-lg mb-3 text-gray-700 flex items-center">
                                <i class="fas fa-align-left mr-2 text-blue-500"></i>
                                Description
                            </h3>
                            
                            <!-- Description courte -->
                            <div class="mb-4">
                                <label class="block font-medium mb-1 flex items-center text-gray-700">
                                    <i class="fas fa-comment-alt mr-2 text-blue-500"></i> Description courte*
                                </label>
                                <textarea name="description_courte" required rows="3"
                                          class="form-input w-full px-4 py-2 border border-gray-300 rounded-lg"
                                          placeholder="Résumé en quelques phrases"></textarea>
                            </div>
                            
                            <!-- Description longue -->
                            <div class="mb-4">
                                <label class="block font-medium mb-1 flex items-center text-gray-700">
                                    <i class="fas fa-file-alt mr-2 text-blue-500"></i> Description détaillée*
                                </label>
                                <textarea name="description_longue" required rows="5"
                                          class="form-input w-full px-4 py-2 border border-gray-300 rounded-lg"
                                          placeholder="Détails du circuit, points forts..."></textarea>
                            </div>
                        </div>
                        
                        <!-- Section 3: Itinéraire -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="font-bold text-lg mb-3 text-gray-700 flex items-center">
                                <i class="fas fa-route mr-2 text-blue-500"></i>
                                Itinéraire
                            </h3>
                            
                            <!-- Itinéraires -->
                            <div class="mb-4">
                                <label class="block font-medium mb-1 flex items-center text-gray-700">
                                    <i class="fas fa-map-marked-alt mr-2 text-blue-500"></i> Étapes principales*
                                </label>
                                <input type="text" name="itineraire" required 
                                       class="form-input w-full px-4 py-2 border border-gray-300 rounded-lg"
                                       placeholder="Ex: Cotonou - Ouidah - Abomey">
                            </div>
                            
                            <!-- Villes visitées -->
                            <div class="mb-4">
                                <label class="block font-medium mb-1 flex items-center text-gray-700">
                                    <i class="fas fa-city mr-2 text-blue-500"></i> Villes visitées*
                                </label>
                                <input type="text" name="villes_visitees" required 
                                       class="form-input w-full px-4 py-2 border border-gray-300 rounded-lg"
                                       placeholder="Ex: Cotonou, Ouidah, Abomey">
                            </div>
                            
                            <!-- Lieu de départ -->
                            <div class="mb-4">
                                <label class="block font-medium mb-1 flex items-center text-gray-700">
                                    <i class="fas fa-map-marker-alt mr-2 text-blue-500"></i> Lieu de départ*
                                </label>
                                <input type="text" name="lieu_depart" required 
                                       class="form-input w-full px-4 py-2 border border-gray-300 rounded-lg"
                                       placeholder="Ex: Hôtel du Port, Cotonou">
                            </div>
                        </div>
                        
                        <!-- Section 4: Détails pratiques -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="font-bold text-lg mb-3 text-gray-700 flex items-center">
                                <i class="fas fa-info-circle mr-2 text-blue-500"></i>
                                Détails pratiques
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Durée -->
                                <div>
                                    <label class="block font-medium mb-1 flex items-center text-gray-700">
                                        <i class="fas fa-clock mr-2 text-blue-500"></i> Durée*
                                    </label>
                                    <input type="text" name="duree" required 
                                           class="form-input w-full px-4 py-2 border border-gray-300 rounded-lg"
                                           placeholder="Ex: 3 jours / 2 nuits">
                                </div>
                                
                                <!-- Prix -->
                                <div>
                                    <label class="block font-medium mb-1 flex items-center text-gray-700">
                                        <i class="fas fa-money-bill-wave mr-2 text-blue-500"></i> Prix (FCFA)*
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">FCFA</span>
                                        <input type="number" name="prix" min="0" step="500" required 
                                               class="form-input w-full pl-12 pr-4 py-2 border border-gray-300 rounded-lg"
                                               placeholder="0 pour gratuit">
                                    </div>
                                </div>
                                
                                <!-- Taille groupe -->
                                <div>
                                    <label class="block font-medium mb-1 flex items-center text-gray-700">
                                        <i class="fas fa-users mr-2 text-blue-500"></i> Taille du groupe*
                                    </label>
                                    <input type="number" name="taille_groupe" min="1" required
                                           class="form-input w-full px-4 py-2 border border-gray-300 rounded-lg"
                                           placeholder="Ex: 15">
                                </div>
                                
                                <!-- Places disponibles -->
                                <div>
                                    <label class="block font-medium mb-1 flex items-center text-gray-700">
                                        <i class="fas fa-ticket-alt mr-2 text-blue-500"></i> Places disponibles*
                                    </label>
                                    <input type="number" name="places_disponibles" min="1" required
                                           class="form-input w-full px-4 py-2 border border-gray-300 rounded-lg"
                                           placeholder="Ex: 10">
                                </div>
                            </div>
                            
                            <!-- Inclus -->
                            <div class="mt-4">
                                <label class="block font-medium mb-1 flex items-center text-gray-700">
                                    <i class="fas fa-check-circle mr-2 text-blue-500"></i> Services inclus*
                                </label>
                                <textarea name="inclus" required rows="2"
                                          class="form-input w-full px-4 py-2 border border-gray-300 rounded-lg"
                                          placeholder="Ex: Transport, guide, hébergement..."></textarea>
                            </div>
                            
                            <!-- Non inclus -->
                            <div class="mt-4">
                                <label class="block font-medium mb-1 flex items-center text-gray-700">
                                    <i class="fas fa-times-circle mr-2 text-blue-500"></i> Services non inclus*
                                </label>
                                <textarea name="non_inclus" required rows="2"
                                          class="form-input w-full px-4 py-2 border border-gray-300 rounded-lg"
                                          placeholder="Ex: Repas, assurances..."></textarea>
                            </div>
                        </div>
                        
                        <!-- Section 5: Contact -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="font-bold text-lg mb-3 text-gray-700 flex items-center">
                                <i class="fas fa-id-card mr-2 text-blue-500"></i>
                                Contact
                            </h3>
                            
                            <!-- Téléphone -->
                            <div class="mb-4">
                                <label class="block font-medium mb-1 flex items-center text-gray-700">
                                    <i class="fas fa-phone-alt mr-2 text-blue-500"></i> Téléphone*
                                </label>
                                <input type="tel" name="tel" required 
                                       class="form-input w-full px-4 py-2 border border-gray-300 rounded-lg"
                                       placeholder="Ex: +229 12 34 56 78">
                            </div>
                            
                            <!-- Email -->
                            <div class="mb-4">
                                <label class="block font-medium mb-1 flex items-center text-gray-700">
                                    <i class="fas fa-envelope mr-2 text-blue-500"></i> Email
                                </label>
                                <input type="email" name="email" 
                                       class="form-input w-full px-4 py-2 border border-gray-300 rounded-lg"
                                       placeholder="votre@email.com">
                            </div>
                            
                            <!-- Site web -->
                            <div>
                                <label class="block font-medium mb-1 flex items-center text-gray-700">
                                    <i class="fas fa-globe mr-2 text-blue-500"></i> Site web
                                </label>
                                <input type="url" name="site" 
                                       class="form-input w-full px-4 py-2 border border-gray-300 rounded-lg"
                                       placeholder="https://votresite.com">
                            </div>
                        </div>

                        <!-- Section 6: Image -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="font-bold text-lg mb-3 text-gray-700 flex items-center">
                                <i class="fas fa-image mr-2 text-blue-500"></i>
                                Visuel
                            </h3>

                            <label class="block font-medium mb-1 flex items-center text-gray-700">
                                <i class="fas fa-camera mr-2 text-blue-500"></i> Image du circuit*
                            </label>

                            <div class="flex items-center justify-center w-full">
                                <label class="upload-area flex flex-col w-full border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-blue-500 transition relative">
                                    <div id="image-preview" class="flex flex-col items-center justify-center pt-5 pb-6 px-4 text-center">
                                        <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                        <p class="text-sm text-gray-500">
                                            <span class="font-semibold">Cliquez pour uploader</span><br>
                                            ou glissez-déposez une image
                                        </p>
                                        <p class="text-xs text-gray-400 mt-1">JPEG, PNG (Max. 5MB)</p>
                                    </div>
                                    <input type="file" name="image" accept="image/*" required class="hidden">
                                </label>
                            </div>
                        </div>
                        
                        <!-- Bouton de soumission -->
                        <div class="text-center pt-4">
                            <button type="submit" class="submit-btn text-white px-6 py-3 rounded-lg w-full flex items-center justify-center">
                                <i class="fas fa-paper-plane mr-2"></i> Soumettre le circuit
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
    
    document.querySelector('input[name="image"]').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const previewContainer = document.getElementById('image-preview');

        if (!file) {
            previewContainer.innerHTML = '<p class="text-sm text-gray-500">Cliquez pour ajouter une image</p>';
            return;
        }

        const fileName = file.name;

        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(event) {
                previewContainer.innerHTML = `
                    <img src="${event.target.result}" class="h-32 mx-auto mb-2 rounded-lg object-cover">
                    <p class="text-sm font-medium text-gray-700 truncate">${fileName}</p>
                    <p class="text-xs text-gray-500">Cliquez pour changer</p>
                `;
            };
            reader.readAsDataURL(file);
        } else {
            previewContainer.innerHTML = `
                <i class="fas fa-check-circle text-green-500 text-2xl mb-2"></i>
                <p class="text-sm font-medium text-gray-700">${fileName}</p>
                <p class="text-xs text-gray-500">Cliquez pour changer</p>
            `;
        }
    });
    
    // Validation des champs numériques
    document.querySelectorAll('input[type="number"]').forEach(input => {
        input.addEventListener('input', function() {
            if (this.value < 0) this.value = 0;
        });
    });
</script>
</body>
</html>