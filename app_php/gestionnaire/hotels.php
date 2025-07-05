<?php
session_start();
include_once(__DIR__ . "/../config/database.php");
include_once(__DIR__ . "/../config/access_control.php");

// Vérifier que l'utilisateur est connecté et a le bon type de compte
check_access(ALLOWED_HOTEL);

// Gestion des messages de session
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// Récupération des données du formulaire en cas d'erreur
$form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proposer un hôtel - Bénin Tourisme</title>
    <link rel="icon" type="image/svg+xml" href="../assets/favicon/favicon.svg">
    <link rel="icon" type="image/png" sizes="96x96" href="../assets/favicon/favicon-96x96.png">
    <link rel="shortcut icon" href="../assets/favicon/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
</head>
<style>
    .hero-section {
        height: 50vh;
        min-height: 400px;
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
    }
    .hero-overlay {
        background: linear-gradient(135deg, rgba(241, 210, 71, 0.9) 0%, rgba(22, 23, 23, 0.9) 100%);
    }
    .hero-title {
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        animation: fadeInUp 0.8s ease-out;
    }
    .hero-subtitle {
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
        animation: fadeInUp 1s ease-out;
    }
    .info-card {
        box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .info-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px -10px rgba(0, 0, 0, 0.15);
    }
    .form-section {
        box-shadow: 0 5px 25px -5px rgba(0, 0, 0, 0.1);
    }
    #map {
        height: 300px;
        border-radius: 0.75rem;
        z-index: 0;
    }
    .animate-fade-in {
        animation: fadeIn 0.6s ease-in forwards;
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    .tooltip-icon {
        cursor: help;
        color: #2F855A;
    }
    .star-rating {
        direction: rtl;
        display: inline-block;
    }
    .star-rating input[type=radio] {
        display: none;
    }
    .star-rating label {
        color: #ddd;
        font-size: 1.5rem;
        padding: 0 3px;
        cursor: pointer;
    }
    .star-rating input[type=radio]:checked ~ label,
    .star-rating label:hover,
    .star-rating label:hover ~ label {
        color: #f8ce0b;
    }
</style>
<body class="bg-gray-50">
    <!-- Hero Section -->
    <section class="hero-section relative flex items-center justify-center" style="background-image: url('../assets/images/hotel-hero.jpg');">
        <div class="hero-overlay absolute inset-0"></div>
        <div class="container mx-auto px-4 relative z-10">
            <div class="max-w-3xl mx-auto text-center">
                <h1 class="hero-title text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-4">
                    Proposez Votre Hôtel
                </h1>
                <p class="hero-subtitle text-xl md:text-2xl text-white opacity-90">
                    Rejoignez notre plateforme et augmentez votre visibilité
                </p>
            </div>
        </div>
    </section>

    <!-- Notification Message -->
    <?php if (isset($message)): ?>
    <div class="fixed top-4 right-4 z-50 animate-fade-in">
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 shadow-lg rounded-lg" role="alert">
            <div class="flex items-center">
                <div class="py-1"><i class="fas fa-check-circle text-green-500 mr-3"></i></div>
                <div>
                    <p class="font-bold">Succès</p>
                    <p><?= htmlspecialchars($message) ?></p>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-green-700 hover:text-green-900">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Main Content -->
    <section class="py-12 md:py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Informations principales -->
                <div class="lg:w-2/3">
                    <div class="info-card bg-white rounded-2xl p-8 mb-8">
                        <h2 class="text-3xl font-bold text-gray-800 mb-6">Augmentez votre visibilité</h2>
                        <div class="prose max-w-none text-gray-600">
                            <p class="text-lg mb-4">En tant que gestionnaire d'hôtel, vous savez à quel point la visibilité en ligne est cruciale pour attirer des clients. Bénin Tourisme vous offre une plateforme dédiée pour mettre en valeur votre établissement.</p>
                            
                            <div class="grid md:grid-cols-2 gap-6 my-6">
                                <div class="flex items-start">
                                    <div class="bg-green-100 p-3 rounded-full mr-4">
                                        <i class="fas fa-chart-line text-green-600 text-xl"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-800 mb-1">Visibilité accrue</h4>
                                        <p class="text-gray-600">Apparaissez devant des milliers de voyageurs chaque mois</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <div class="bg-blue-100 p-3 rounded-full mr-4">
                                        <i class="fas fa-calendar-check text-blue-600 text-xl"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-800 mb-1">Réservations directes</h4>
                                        <p class="text-gray-600">Recevez des demandes de réservation sans intermédiaire</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <div class="bg-purple-100 p-3 rounded-full mr-4">
                                        <i class="fas fa-globe-africa text-purple-600 text-xl"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-800 mb-1">Clientèle internationale</h4>
                                        <p class="text-gray-600">Touchez des voyageurs du monde entier</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <div class="bg-yellow-100 p-3 rounded-full mr-4">
                                        <i class="fas fa-star text-yellow-600 text-xl"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-800 mb-1">Notation et avis</h4>
                                        <p class="text-gray-600">Bénéficiez d'un système d'évaluation pour gagner en crédibilité</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 my-6 rounded-r">
                                <h4 class="font-bold text-blue-800 mb-2">Comment ça marche ?</h4>
                                <ol class="list-decimal list-inside space-y-2 text-blue-800">
                                    <li>Remplissez le formulaire avec les détails de votre établissement</li>
                                    <li>Notre équipe valide les informations sous 48h</li>
                                    <li>Votre hôtel apparaît sur notre plateforme</li>
                                    <li>Vous commencez à recevoir des demandes de réservation</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Témoignages -->
                    <div class="info-card bg-white rounded-2xl p-8">
                        <h3 class="text-2xl font-bold text-gray-800 mb-6">Ce que disent nos partenaires</h3>
                        <div class="grid md:grid-cols-2 gap-6">
                            <div class="bg-gray-50 p-6 rounded-xl">
                                <div class="flex items-center mb-4">
                                    <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center mr-4">
                                        <i class="fas fa-user text-green-600 text-xl"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold">Hôtel La Diaspora</h4>
                                        <div class="flex text-yellow-400 text-sm">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-gray-600 italic">"Depuis que notre hôtel est sur Bénin Tourisme, nous avons vu une augmentation de 40% de nos réservations en ligne. La plateforme est très professionnelle."</p>
                            </div>
                            <div class="bg-gray-50 p-6 rounded-xl">
                                <div class="flex items-center mb-4">
                                    <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center mr-4">
                                        <i class="fas fa-user text-blue-600 text-xl"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold">Résidence Les Cocotiers</h4>
                                        <div class="flex text-yellow-400 text-sm">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star-half-alt"></i>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-gray-600 italic">"Excellent outil pour les hôteliers. Nous recevons régulièrement des demandes de réservation de qualité grâce à cette plateforme."</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulaire -->
                <div class="lg:w-1/3">
                    <div class="sticky top-8 bg-white p-8 rounded-2xl form-section">
                        <div class="text-center mb-6">
                            <h2 class="text-2xl font-bold text-gray-800">Ajouter un hôtel</h2>
                            <p class="text-gray-500 mt-2">Remplissez ce formulaire pour enregistrer votre établissement</p>
                        </div>

                        <?php
                        // Générer un jeton CSRF s'il n'existe pas
                        if (empty($_SESSION['csrf_token'])) {
                            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                        }
                        ?>

                        <form action="traitement_hotel.php" method="POST" enctype="multipart/form-data" class="space-y-5" id="hotelForm">
                            <!-- Nom -->
                            <div>
                                <label class="block text-gray-700 font-medium mb-1">
                                    <i class="fas fa-hotel mr-2 text-green-600"></i>Nom de l'hôtel
                                    <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="nom" required 
                                    value="<?= htmlspecialchars($form_data['nom'] ?? '') ?>"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition">
                            </div>

                            <!-- Description courte -->
                            <div>
                                <label class="block text-gray-700 font-medium mb-1">
                                    <i class="fas fa-align-left mr-2 text-green-600"></i>Description courte
                                    <span class="text-red-500">*</span>
                                    <i class="fas fa-info-circle tooltip-icon ml-1" title="Une description concise (max 200 caractères) qui apparaîtra dans les résultats de recherche"></i>
                                </label>
                                <textarea name="description" rows="3" required 
                                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition"
                                    maxlength="200"><?= htmlspecialchars($form_data['description'] ?? '') ?></textarea>
                                <div class="text-xs text-gray-500 text-right"><span id="descCount">0</span>/200 caractères</div>
                            </div>

                            <!-- Ville -->
                            <div>
                                <label class="block text-gray-700 font-medium mb-1">
                                    <i class="fas fa-map-marker-alt mr-2 text-green-600"></i>Ville
                                    <span class="text-red-500">*</span>
                                </label>
                                <select name="ville" required 
                                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition">
                                    <option value="">Sélectionnez une ville</option>
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

                            <!-- Adresse -->
                            <div>
                                <label class="block text-gray-700 font-medium mb-1">
                                    <i class="fas fa-map-marked-alt mr-2 text-green-600"></i>Adresse complète
                                </label>
                                <input type="text" name="adresse" 
                                    value="<?= htmlspecialchars($form_data['adresse'] ?? '') ?>"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition">
                            </div>

                            <!-- Carte pour localisation -->
                            <div>
                                <label class="block text-gray-700 font-medium mb-1">
                                    <i class="fas fa-map mr-2 text-green-600"></i>Localisation sur la carte
                                    <i class="fas fa-info-circle tooltip-icon ml-1" title="Cliquez sur la carte pour définir l'emplacement ou entrez manuellement les coordonnées"></i>
                                </label>
                                <div id="map" class="mb-2"></div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-sm text-gray-600 mb-1">Latitude</label>
                                        <input type="text" name="latitude" id="latitude" 
                                            value="<?= htmlspecialchars($form_data['latitude'] ?? '') ?>"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-200 focus:border-green-500 transition">
                                    </div>
                                    <div>
                                        <label class="block text-sm text-gray-600 mb-1">Longitude</label>
                                        <input type="text" name="longitude" id="longitude" 
                                            value="<?= htmlspecialchars($form_data['longitude'] ?? '') ?>"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-200 focus:border-green-500 transition">
                                    </div>
                                </div>
                            </div>

                            <!-- Image principale -->
                            <div>
                                <label class="block text-gray-700 font-medium mb-1">
                                    <i class="fas fa-camera mr-2 text-green-600"></i>Image principale
                                    <span class="text-red-500">*</span>
                                    <i class="fas fa-info-circle tooltip-icon ml-1" title="Image qui représentera votre hôtel (format JPG/PNG, max 5MB)"></i>
                                </label>
                                <div class="flex items-center justify-center w-full">
                                    <label class="flex flex-col w-full h-32 border-2 border-dashed border-gray-300 hover:border-green-500 rounded-lg cursor-pointer transition hover:bg-gray-50">
                                        <div class="flex flex-col items-center justify-center pt-7">
                                            <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                            <p class="text-sm text-gray-500">Glissez-déposez ou cliquez pour sélectionner</p>
                                        </div>
                                        <input type="file" name="image" accept="image/*" required class="opacity-0 h-0 w-0">
                                    </label>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">Format recommandé : 1200x800px</div>
                            </div>

                            <!-- Nombre d'étoiles -->
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">
                                    <i class="fas fa-star mr-2 text-yellow-500"></i>Classement
                                </label>
                                <div class="star-rating">
                                    <input type="radio" id="5-stars" name="etoiles" value="5" <?= isset($form_data['etoiles']) && $form_data['etoiles'] == 5 ? 'checked' : '' ?>>
                                    <label for="5-stars" class="text-2xl">★</label>
                                    <input type="radio" id="4-stars" name="etoiles" value="4" <?= isset($form_data['etoiles']) && $form_data['etoiles'] == 4 ? 'checked' : '' ?>>
                                    <label for="4-stars" class="text-2xl">★</label>
                                    <input type="radio" id="3-stars" name="etoiles" value="3" <?= isset($form_data['etoiles']) && $form_data['etoiles'] == 3 ? 'checked' : '' ?>>
                                    <label for="3-stars" class="text-2xl">★</label>
                                    <input type="radio" id="2-stars" name="etoiles" value="2" <?= isset($form_data['etoiles']) && $form_data['etoiles'] == 2 ? 'checked' : '' ?>>
                                    <label for="2-stars" class="text-2xl">★</label>
                                    <input type="radio" id="1-star" name="etoiles" value="1" <?= isset($form_data['etoiles']) && $form_data['etoiles'] == 1 ? 'checked' : '' ?>>
                                    <label for="1-star" class="text-2xl">★</label>
                                </div>
                            </div>

                            <!-- Prix -->
                            <div>
                                <label class="block text-gray-700 font-medium mb-1">
                                    <i class="fas fa-money-bill-wave mr-2 text-green-600"></i>Prix moyen par nuit (FCFA)
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-3 text-gray-500">FCFA</span>
                                    <input type="number" name="prix" min="0" step="1000" 
                                        value="<?= htmlspecialchars($form_data['prix'] ?? '') ?>"
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 pl-16 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition"
                                        placeholder="Ex: 25000">
                                </div>
                            </div>

                            <!-- Contact -->
                            <div class="pt-4 border-t border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-800 mb-3">
                                    <i class="fas fa-address-book mr-2 text-green-600"></i>Coordonnées
                                </h3>
                                
                                <!-- Téléphone -->
                                <div class="mb-4">
                                    <label class="block text-gray-700 font-medium mb-1">
                                        <i class="fas fa-phone-alt mr-2 text-green-600"></i>Téléphone
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input type="tel" name="telephone" required 
                                        value="<?= htmlspecialchars($form_data['telephone'] ?? '') ?>"
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition"
                                        placeholder="+229 90 00 00 00">
                                </div>
                                
                                <!-- Email -->
                                <div class="mb-4">
                                    <label class="block text-gray-700 font-medium mb-1">
                                        <i class="fas fa-envelope mr-2 text-green-600"></i>Email
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input type="email" name="email" required 
                                        value="<?= htmlspecialchars($form_data['email'] ?? '') ?>"
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition"
                                        placeholder="contact@votrehotel.com">
                                </div>
                                
                                <!-- Site Web -->
                                <div>
                                    <label class="block text-gray-700 font-medium mb-1">
                                        <i class="fas fa-globe mr-2 text-green-600"></i>Site web
                                    </label>
                                    <input type="url" name="site_web" 
                                        value="<?= htmlspecialchars($form_data['site_web'] ?? '') ?>"
                                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition"
                                        placeholder="https://www.votrehotel.com">
                                </div>
                            </div>

                            <!-- Description détaillée -->
                            <div>
                                <label class="block text-gray-700 font-medium mb-1">
                                    <i class="fas fa-align-left mr-2 text-green-600"></i>Description détaillée
                                    <i class="fas fa-info-circle tooltip-icon ml-1" title="Décrivez en détail votre établissement, ses services, son histoire, etc."></i>
                                </label>
                                <textarea name="description_supp" rows="5" 
                                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-green-200 focus:border-green-500 transition"
                                    placeholder="Décrivez votre établissement, ses services, son ambiance, son histoire..."><?= htmlspecialchars($form_data['description_supp'] ?? '') ?></textarea>
                            </div>

                            <!-- Images supplémentaires -->
                            <div>
                                <label class="block text-gray-700 font-medium mb-1">
                                    <i class="fas fa-images mr-2 text-green-600"></i>Images supplémentaires
                                    <i class="fas fa-info-circle tooltip-icon ml-1" title="Vous pouvez ajouter jusqu'à 5 images supplémentaires (facultatif)"></i>
                                </label>
                                <div class="flex items-center justify-center w-full">
                                    <label class="flex flex-col w-full h-32 border-2 border-dashed border-gray-300 hover:border-green-500 rounded-lg cursor-pointer transition hover:bg-gray-50">
                                        <div class="flex flex-col items-center justify-center pt-7">
                                            <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                            <p class="text-sm text-gray-500">Sélectionnez plusieurs images (max 5)</p>
                                        </div>
                                        <input type="file" name="image_supp[]" multiple accept="image/*" class="opacity-0 h-0 w-0">
                                    </label>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">Formats acceptés : JPG, PNG (max 5MB par image)</div>
                            </div>

                            <!-- Équipements -->
                            <div>
                                <label class="block text-gray-700 font-medium mb-2">
                                    <i class="fas fa-umbrella-beach mr-2 text-green-600"></i>Équipements & Services
                                </label>
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="flex items-center">
                                        <input type="checkbox" id="wifi" name="equipements[]" value="wifi" 
                                            <?= isset($form_data['equipements']) && in_array('wifi', $form_data['equipements']) ? 'checked' : '' ?>
                                            class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                                        <label for="wifi" class="ml-2 text-gray-700">Wi-Fi</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" id="piscine" name="equipements[]" value="piscine" 
                                            <?= isset($form_data['equipements']) && in_array('piscine', $form_data['equipements']) ? 'checked' : '' ?>
                                            class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                                        <label for="piscine" class="ml-2 text-gray-700">Piscine</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" id="restaurant" name="equipements[]" value="restaurant" 
                                            <?= isset($form_data['equipements']) && in_array('restaurant', $form_data['equipements']) ? 'checked' : '' ?>
                                            class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                                        <label for="restaurant" class="ml-2 text-gray-700">Restaurant</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" id="parking" name="equipements[]" value="parking" 
                                            <?= isset($form_data['equipements']) && in_array('parking', $form_data['equipements']) ? 'checked' : '' ?>
                                            class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                                        <label for="parking" class="ml-2 text-gray-700">Parking</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" id="climatisation" name="equipements[]" value="climatisation" 
                                            <?= isset($form_data['equipements']) && in_array('climatisation', $form_data['equipements']) ? 'checked' : '' ?>
                                            class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                                        <label for="climatisation" class="ml-2 text-gray-700">Climatisation</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" id="spa" name="equipements[]" value="spa" 
                                            <?= isset($form_data['equipements']) && in_array('spa', $form_data['equipements']) ? 'checked' : '' ?>
                                            class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                                        <label for="spa" class="ml-2 text-gray-700">SPA</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Bouton de soumission -->
                            <div class="pt-6">
                                <button type="submit" class="w-full bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-bold py-3 px-4 rounded-lg shadow-md transition duration-300 transform hover:scale-105">
                                    <i class="fas fa-paper-plane mr-2"></i>Soumettre mon hôtel
                                </button>
                            </div>

                            <div class="text-xs text-gray-500 mt-4 text-center">
                                En soumettant ce formulaire, vous acceptez nos <a href="#" class="text-green-600 hover:underline">conditions d'utilisation</a> et notre <a href="#" class="text-green-600 hover:underline">politique de confidentialité</a>.
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script>
        // Initialisation de la carte
        function initMap() {
            // Coordonnées par défaut (Cotonou)
            const defaultLat = 6.3725;
            const defaultLng = 2.3583;
            
            // Création de la carte
            const map = L.map('map').setView([defaultLat, defaultLng], 13);
            
            // Ajout du fond de carte
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);
            
            // Marqueur
            let marker = null;
            
            // Si des coordonnées existent déjà, placer le marqueur
            const latInput = document.getElementById('latitude');
            const lngInput = document.getElementById('longitude');
            
            if (latInput.value && lngInput.value) {
                marker = L.marker([latInput.value, lngInput.value]).addTo(map);
                map.setView([latInput.value, lngInput.value], 15);
            }
            
            // Gestion du clic sur la carte
            map.on('click', function(e) {
                const { lat, lng } = e.latlng;
                
                // Mettre à jour les champs de formulaire
                latInput.value = lat.toFixed(6);
                lngInput.value = lng.toFixed(6);
                
                // Déplacer ou créer le marqueur
                if (marker) {
                    marker.setLatLng(e.latlng);
                } else {
                    marker = L.marker(e.latlng).addTo(map);
                }
            });
            
            // Mise à jour de la position depuis les champs de formulaire
            latInput.addEventListener('change', updateMarker);
            lngInput.addEventListener('change', updateMarker);
            
            function updateMarker() {
                const lat = parseFloat(latInput.value);
                const lng = parseFloat(lngInput.value);
                
                if (!isNaN(lat) && !isNaN(lng)) {
                    const newLatLng = L.latLng(lat, lng);
                    
                    if (marker) {
                        marker.setLatLng(newLatLng);
                    } else {
                        marker = L.marker(newLatLng).addTo(map);
                    }
                    
                    map.setView(newLatLng, 15);
                }
            }
        }
        
        // Compteur de caractères pour la description
        document.addEventListener('DOMContentLoaded', function() {
            initMap();
            
            const descTextarea = document.querySelector('textarea[name="description"]');
            const descCount = document.getElementById('descCount');
            
            if (descTextarea && descCount) {
                // Initialiser le compteur
                descCount.textContent = descTextarea.value.length;
                
                // Mettre à jour le compteur lors de la saisie
                descTextarea.addEventListener('input', function() {
                    descCount.textContent = this.value.length;
                });
            }
            
            // Gestion de l'affichage du fichier sélectionné
            const fileInputs = document.querySelectorAll('input[type="file"]');
            fileInputs.forEach(input => {
                input.addEventListener('change', function() {
                    const label = this.closest('label');
                    if (this.files && this.files.length > 0) {
                        if (this.files.length === 1) {
                            label.querySelector('p').textContent = this.files[0].name;
                        } else {
                            label.querySelector('p').textContent = `${this.files.length} fichiers sélectionnés`;
                        }
                    }
                });
            });
            
            // Animation pour les tooltips
            tippy('.tooltip-icon', {
                content(reference) {
                    return reference.getAttribute('title');
                },
                theme: 'light-border',
                animation: 'fade',
                arrow: true,
                placement: 'top'
            });
        });
    </script>
    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <script src="https://unpkg.com/tippy.js@6"></script>

    <?php include_once("../includes/footergest.php"); ?>
</body>
</html>