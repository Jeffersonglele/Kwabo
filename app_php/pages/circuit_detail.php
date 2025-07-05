<?php
session_start();
include_once("../config/database.php");

// Récupérer l'ID du circuit
$circuit_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($circuit_id <= 0) {
    header("Location: circuits.php");
    exit();
}

// Enregistrer la vue dans la base de données
// Récupérer l'ID du gestionnaire du circuit
$stmt = $pdo->prepare("SELECT gestionnaire_id FROM circuits WHERE id = ?");
$stmt->execute([$circuit_id]);
$gestionnaire_id = $stmt->fetchColumn();

// Enregistrer la vue avec les informations du visiteur
$stmt = $pdo->prepare("INSERT INTO vues (element_id, element_type, gestionnaire_id, ip_visiteur, user_agent, date_vue) VALUES (?, 'circuit', ?, ?, ?, NOW())");
$stmt->execute([
    $circuit_id,
    $gestionnaire_id,
    $_SERVER['REMOTE_ADDR'],
    $_SERVER['HTTP_USER_AGENT']
]);

// Récupérer les détails du circuit
$stmt = $pdo->prepare("SELECT * FROM circuits WHERE id = ?");
$stmt->execute([$circuit_id]);
$circuit = $stmt->fetch();

if (!$circuit) {
    header("Location: circuits.php");
    exit();
}

// Récupérer les commentairess
$stmt = $pdo->prepare("
    SELECT c.*, u.nom, u.prenom 
    FROM commentaires_circuits c 
    JOIN utilisateurs u ON c.user_id = u.id 
    WHERE c.circuit_id = ? 
    ORDER BY c.date_creation DESC
");
$stmt->execute([$circuit_id]);
$commentaires = $stmt->fetchAll();

// Traitement du formulaire de commentaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $commentaire = trim($_POST['commentaire'] ?? '');
    $note = (int)($_POST['note'] ?? 5);
    
    if (!empty($commentaire)) {
        $stmt = $pdo->prepare("INSERT INTO commentaires_circuits (circuit_id, user_id, commentaire, note, date_creation) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$circuit_id, $_SESSION['user_id'], $commentaire, $note]);
        header("Location: circuit_detail.php?id=" . $circuit_id);
        exit();
    }
}

$imagePath = realpath(__DIR__ . '/' . $circuit['image']);
if ($imagePath === false) {
    echo "Erreur : chemin introuvable. Vérifie la structure des dossiers.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($circuit['nom']) ?> - Bénin Tourisme</title>
    <link rel="icon" type="image/svg+xml" href="../assets/favicon/favicon.svg">
    <link rel="icon" type="image/png" sizes="96x96" href="../assets/favicon/favicon-96x96.png">
    <link rel="shortcut icon" href="../assets/favicon/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
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
        .circuit-price {
            font-size: 2rem;
            color: gray;
            font-weight: bold;
        }
        .feature-icon {
            width: 30px;
            color: #0d6efd;
        }
        .comment-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .rating {
            color: #ffc107;
            font-size: 1.2rem;
        }
        .comment-form {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 3rem;
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
        .itinerary-day {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        .day-number {
            background: gray;
            color: white;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 1rem;
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php include('../includes/navbar.php'); ?>

    <!-- Hero Section -->
    <section class="hero-section relative" style="background-image: url('<?= htmlspecialchars($circuit['image']) ?>');">
        <div class="hero-overlay absolute inset-0"></div>
        <div class="hero-content relative z-10 flex flex-col justify-center h-full px-8">
            <h1 class="hero-title text-4xl md:text-5xl font-bold text-white mb-4 animate-fade-in">
                <?= htmlspecialchars($circuit['nom']) ?>
            </h1>
            <p class="hero-subtitle text-xl text-white animate-fade-in">
                <?= htmlspecialchars($circuit['description_courte']) ?>
            </p>
        </div>
    </section>

    <div class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Informations détaillées -->
                <div class="lg:w-2/3">
                    <div class="info-card bg-white rounded-xl p-8 mb-8">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6">À propos de ce circuit</h2>
                        <p class="text-gray-700 mb-8"><?= nl2br(htmlspecialchars($circuit['description_longue'])) ?></p>
                        
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Points forts du circuit</h3>
                        <div class="grid gap-6">
                            <div class="text">
                                <ul class="list-unstyled">
                                    <li class="mb-3">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        Guide professionnel francophone
                                    </li>
                                    <li class="mb-3">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        Transport confortable inclus
                                    </li>
                                    <li class="mb-3">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        Hébergement en hôtels sélectionnés
                                    </li>
                                    <li class="mb-3">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        Repas traditionnels inclus
                                    </li>
                                    <li class="mb-3">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        Activités et visites guidées
                                    </li>
                                    <li class="mb-3">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        Assistance 24h/24
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <h3 class="text-2xl font-bold text-gray-800 mb-6">Itinéraire détaillé</h3>
                        <div class="itinerary-days">
                            <?php
                            $jours = explode("\n", $circuit['itineraire']);
                            foreach ($jours as $index => $jour): ?>
                                <div class="itinerary-day d-flex align-items-start">
                                    <div class="day-number">
                                        <?= $index + 1 ?>
                                    </div>
                                    <div>
                                        <?= nl2br(htmlspecialchars($jour)) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Section Commentaires -->
                    <div class="info-card bg-white rounded-xl p-8">
                        <h3 class="text-2xl font-bold text-gray-800 mb-4">Avis des voyageurs</h3>
                        
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <div class="comment-form mb-8">
                                <h4 class="text-xl font-bold text-gray-800 mb-4">Laissez votre avis</h4>
                                <form method="POST">
                                    <div class="mb-4">
                                        <label for="note" class="block text-gray-700 mb-2">Note</label>
                                        <select class="w-full p-2 border rounded-lg" id="note" name="note" required>
                                            <option value="5">5 - Excellent</option>
                                            <option value="4">4 - Très bien</option>
                                            <option value="3">3 - Bien</option>
                                            <option value="2">2 - Moyen</option>
                                            <option value="1">1 - Décevant</option>
                                        </select>
                                    </div>
                                    <div class="mb-4">
                                        <label for="commentaire" class="block text-gray-700 mb-2">Votre commentaire</label>
                                        <textarea class="w-full p-2 border rounded-lg" id="commentaire" name="commentaire" rows="4" required></textarea>
                                    </div>
                                    <button type="submit" class="btn-reserve bg-primary hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg">
                                        <i class="fas fa-paper-plane me-2"></i>
                                        Publier mon avis
                                    </button>
                                </form>
                            </div>
                        <?php else: ?>
                            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-8">
                                <p class="text-blue-700">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <a href="connexion_commentaire.php" class="underline hover:text-blue-900">Connectez-vous</a> pour laisser un avis sur ce circuit.
                                </p>
                            </div>
                        <?php endif; ?>

                        <?php foreach ($commentaires as $commentaire): ?>
                            <div class="comment-card">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <h5 class="text-lg font-semibold text-gray-800">
                                            <?= htmlspecialchars($commentaire['prenom'] . ' ' . $commentaire['nom']) ?>
                                        </h5>
                                        <div class="rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star<?= $i <= $commentaire['note'] ? '' : '-o' ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <small class="text-gray-500">
                                        <?= date('d/m/Y', strtotime($commentaire['date_creation'])) ?>
                                    </small>
                                </div>
                                <p class="text-gray-700"><?= nl2br(htmlspecialchars($commentaire['commentaire'])) ?></p>
                            </div>
                        <?php endforeach; ?>

                        <?php if (empty($commentaires)): ?>
                            <div class="text-center text-gray-500 my-8">
                                <i class="fas fa-comments fa-3x mb-3"></i>
                                <p>Aucun avis pour le moment. Soyez le premier à partager votre expérience !</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="lg:w-1/3">
                    <div class="sticky top-8">
                        <div class="info-card bg-white rounded-xl p-8">
                            <div class="text-center mb-6">
                                <div class="circuit-price mb-2">
                                    <?= number_format($circuit['prix'], 0, ',', '.') ?> FCFA
                                </div>
                                <p class="text-gray-600">par personne</p>
                            </div>

                            <ul class="space-y-4 mb-8">
                                <li class="flex items-center">
                                    <i class="fas fa-clock me-3 text-primary"></i>
                                    <span>Durée : <?= htmlspecialchars($circuit['duree']) ?> jours</span>
                                </li>
                                <li class="flex items-center">
                                    <i class="fas fa-users me-3 text-primary"></i>
                                    <span>Taille du groupe : <?= htmlspecialchars($circuit['taille_groupe']) ?> pers. max</span>
                                </li>
                                <li class="flex items-center">
                                    <i class="fas fa-map-marker-alt me-3 text-primary"></i>
                                    <span>Départ de : <?= htmlspecialchars($circuit['lieu_depart']) ?></span>
                                </li>
                                <li class="flex items-center">
                                    <i class="fas fa-info-circle me-3 text-primary"></i>
                                    <span>Plus d'info ici : <?= htmlspecialchars($circuit['tel']) ?>
                                    <a href="mailto:<?= htmlspecialchars($circuit['email']) ?>">
                                    <?= htmlspecialchars($circuit['email']) ?>
                                    </a>
                                    </span>
                                    
                                </li>
                            </ul>

                            <div class="flex justify-center mt-10">
                                <div class="animate-bounce">
                                    <svg class="w-8 h-8 text-blue-700" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </div>

                            <a href="<?= htmlspecialchars($circuit['site']) ?>" class="btn-reserve block w-full bg-primary hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg text-center transition duration-300">
                                Réserver maintenant
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('../includes/footer.php'); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true
        });
    </script>
</body>
</html>