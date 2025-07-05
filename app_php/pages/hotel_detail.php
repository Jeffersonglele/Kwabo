<?php
session_start();
include_once(__DIR__ . "/../config/database.php");

// Récupération de l'ID de l'hôtel
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: hotel.php');
    exit;
}

$id = (int)$_GET['id'];

// Enregistrer la vue dans la base de données
if (isset($_SESSION['user_id'])) {
    // Récupérer l'ID du gestionnaire de l'hôtel
    $stmt = $pdo->prepare("SELECT gestionnaire_id FROM hotels WHERE id = ?");
    $stmt->execute([$id]);
    $gestionnaire_id = $stmt->fetchColumn();
    
    // Enregistrer la vue avec les informations du visiteur
    $stmt = $pdo->prepare("INSERT INTO vues (element_id, element_type, gestionnaire_id, ip_visiteur, user_agent) VALUES (?, 'hotel', ?, ?, ?)");
    $stmt->execute([
        $id,
        $gestionnaire_id,
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['HTTP_USER_AGENT']
    ]);
}
// Récupération des détails de l'hôtel
$stmt = $pdo->prepare("SELECT * FROM hotels WHERE id = ?");
$stmt->execute([$id]);
$hotel = $stmt->fetch();

// Redirection si l'hôtel n'existe pas
if (!$hotel) {
    header('Location: hotel.php');
    exit;
}

$liens_images = explode(',', $hotel['image_supplementaire']); // Séparer les images
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($hotel['nom']) ?> - Bénin Tourisme</title>
    <link rel="icon" type="image/svg+xml" href="../assets/favicon/favicon.svg">
    <link rel="icon" type="image/png" sizes="96x96" href="../assets/favicon/favicon-96x96.png">
    <link rel="shortcut icon" href="../assets/favicon/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .hero-section {
            height: 60vh;
            background-size: cover;
            background-position: center;
        }
        .hero-overlay {
            background: rgba(0, 0, 0, 0.5);
        }
        .hero-title {
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }
        .hero-subtitle {
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        }
        .info-card {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .map-container {
            height: 400px;
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
        .amenity-icon {
            width: 48px;
            height: 48px;
            background-color: #f3f4f6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.5rem;
            color: #4f46e5;
            font-size: 1.5rem;
        }
        .contact-info-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background-color: #f8fafc;
            border-radius: 0.5rem;
        }
        .contact-info-item i {
            font-size: 1.5rem;
            color: #4f46e5;
            margin-right: 1rem;
            margin-top: 0.25rem;
        }
        .contact-info-item h5 {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }
        .contact-info-item p {
            color: #64748b;
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php include_once(__DIR__ . "/../includes/navbar.php"); ?>

    <!-- Hero Section -->
    <section class="hero-section relative" style="background-image: url('<?= ($hotel['image']) ?>');">
        <div class="hero-overlay absolute inset-0"></div>
        <div class="hero-content relative z-10 flex flex-col justify-center h-full px-8">
            <h1 class="hero-title text-4xl md:text-5xl font-bold text-white mb-4 animate-fade-in">
                <?= htmlspecialchars($hotel['nom']) ?>
            </h1>
            <div class="flex items-center space-x-4 text-white mb-4 animate-fade-in">
                <?php
                $etoiles = $hotel['etoiles'];
                for ($i = 0; $i < 5; $i++) {
                    if ($i < $etoiles) {
                        echo '<i class="fas fa-star"></i>';
                    } else {
                        echo '<i class="far fa-star"></i>';
                    }
                }
                ?>
            </div>
            <p class="hero-subtitle text-xl text-white animate-fade-in">
                <i class="fas fa-map-marker-alt mr-2"></i>
                <?= htmlspecialchars($hotel['ville']) ?>
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
                        <h2 class="text-2xl font-bold text-gray-800 mb-6">À propos</h2>
                        <p class="text-gray-700 mb-8"><?= nl2br(htmlspecialchars($hotel['contenu'])) ?></p>
                        
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Équipements et services</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                            <div class="text-center">
                                <div class="amenity-icon mx-auto">
                                    <i class="fas fa-wifi"></i>
                                </div>
                                <p class="text-gray-600">WiFi gratuit</p>
                            </div>
                            <div class="text-center">
                                <div class="amenity-icon mx-auto">
                                    <i class="fas fa-swimming-pool"></i>
                                </div>
                                <p class="text-gray-600">Piscine</p>
                            </div>
                            <div class="text-center">
                                <div class="amenity-icon mx-auto">
                                    <i class="fas fa-parking"></i>
                                </div>
                                <p class="text-gray-600">Parking gratuit</p>
                            </div>
                            <div class="text-center">
                                <div class="amenity-icon mx-auto">
                                    <i class="fas fa-utensils"></i>
                                </div>
                                <p class="text-gray-600">Restaurant</p>
                            </div>
                        </div>
                    </div>

                    <?php if (isset($hotel['latitude']) && isset($hotel['longitude'])): ?>
                    <!-- Carte -->
                    <div class="info-card bg-white rounded-xl p-8">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6">Localisation</h2>
                        <div class="map-container rounded-xl overflow-hidden">
                            <iframe
                                src="https://www.google.com/maps?q=<?= $hotel['latitude'] ?>,<?= $hotel['longitude'] ?>&output=embed"
                                width="100%"
                                height="100%"
                                style="border:0;"
                                allowfullscreen>
                            </iframe>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Réservation et aperçu -->
                <div class="lg:w-1/3">
                    <div class="sticky top-8 space-y-6">
                        <!-- Prix et réservation -->
                        <div class="info-card bg-white rounded-xl p-8">
                            <div class="text-center mb-6">
                                <h3 class="text-2xl font-bold text-gray-800 mb-2">Prix par nuit</h3>
                                <h5 class="text-1xl font-sans text-gray-800 mb-2">A partir de </h5>
                                <p class="text-3xl font-bold text-primary">
                                    <?= number_format($hotel['prix_min'], 0, ',', ' ') ?> FCFA
                                </p>
                                <div class="flex justify-center mt-10">
                                <p class="text-1xl  text-primary">Plus d'info ici</p>
                                    <div class="animate-bounce">
                                        <svg class="w-8 h-8 text-gray-700" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <a href="<?= htmlspecialchars($hotel['site']) ?>" class="btn-reserve block w-full bg-primary hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg text-center transition duration-300">
                            <i class="fas fa-ticket-alt mr-2"></i>Réserver maintenant
                            </a>
                        </div>

                        <!-- Informations de contact -->
                        <div class="info-card bg-white rounded-xl p-8">
                            <h3 class="text-2xl font-bold text-gray-800 mb-6">Informations de contact</h3>
                            
                            <div class="contact-info-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <div>
                                    <h5>Adresse</h5>
                                    <p class="mb-0"><?= htmlspecialchars($hotel['ville']) ?></p>
                                </div>
                            </div>

                            <div class="contact-info-item">
                                <i class="fas fa-phone"></i>
                                <div>
                                    <h5>Téléphone</h5>
                                    <p class="mb-0"><?= htmlspecialchars($hotel['téléphone']) ?></p>
                                </div>
                            </div>

                            <div class="contact-info-item mb-0">
                                <i class="fas fa-envelope"></i>
                                <div>
                                    
                                <div>
                                <h5>Email</h5>
                                <p class="mb-0 break-all">
                                    <a href="mailto:<?= htmlspecialchars($hotel['email']) ?>">
                                        <?= htmlspecialchars($hotel['email']) ?>
                                    </a>
                                </p>
                              </div>
                            </div>
                        </div>
                        <!-- Galerie photos -->
                        <div class="info-card bg-white rounded-xl p-8">
                            <h2 class="text-2xl font-bold text-gray-800 mb-4">Galerie photos</h2>
                            <div class="grid grid-cols-1 gap-4">
                            <?php foreach ($liens_images as $img) : ?>
                                <img src="<?= htmlspecialchars($img) ?>"alt="Vue de l'hôtel" 
                                class="w-full h-64 object-cover rounded-lg" >
                            <?php endforeach; ?>
                                
                                
                            </div>
                        </div>
                       
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include_once(__DIR__ . "/../includes/footer.php"); ?>
</body>
</html> 