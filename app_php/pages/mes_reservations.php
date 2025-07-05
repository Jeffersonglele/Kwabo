<?php
session_start();
include_once("..\config\database.php");
include_once(__DIR__ . "/../includes/navbar.php");

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Récupérer les réservations (même code que précédemment)
$reservations_evenements = []; // Vos données
$reservations_hotels = []; // Vos données
$reservations_circuits = []; // Vos données
$reservations_lieux = []; // Vos données

// Traitement de l'annulation (même code que précédemment)
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Réservations - Bénin Tourisme</title>
    <link rel="icon" type="image/svg+xml" href="../assets/favicon/favicon.svg">
    <link rel="icon" type="image/png" sizes="96x96" href="../assets/favicon/favicon-96x96.png">
    <link rel="shortcut icon" href="../assets/favicon/favicon.ico">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#2c3e50',
                        secondary: '#3498db',
                        accent: '#e74c3c',
                        success: '#27ae60',
                    }
                }
            }
        }
    </script>
    <style type="text/tailwindcss">
        @layer components {
            .tab-item {
                @apply px-4 py-2 text-gray-600 border-b-2 border-transparent hover:text-primary hover:border-gray-300 transition-colors duration-200;
            }
            .tab-item.active {
                @apply text-primary border-primary font-medium;
            }
            .reservation-item {
                @apply bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-4 transition-all hover:shadow-md;
            }
            .reservation-title {
                @apply text-lg font-semibold text-gray-800;
            }
            .reservation-detail {
                @apply text-gray-600 flex items-start space-x-2 mt-1;
            }
            .reservation-price {
                @apply text-success font-bold mt-2;
            }
            .btn-cancel {
                @apply bg-accent text-white px-3 py-1 rounded-md text-sm hover:bg-red-700 transition-colors;
            }
            .empty-state {
                @apply bg-gray-50 text-gray-500 rounded-lg p-8 text-center;
            }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-8">Mes Réservations</h1>

        <!-- Messages d'alerte -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <?= $_SESSION['success_message'] ?>
                <?php unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <?= $_SESSION['error_message'] ?>
                <?php unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <!-- Onglets -->
        <div class="flex overflow-x-auto border-b border-gray-200 mb-6">
            <button onclick="switchTab('evenements')" class="tab-item active mr-2 whitespace-nowrap">
                Événements (<?= count($reservations_evenements) ?>)
            </button>
            <button onclick="switchTab('hotels')" class="tab-item mr-2 whitespace-nowrap">
                Hôtels (<?= count($reservations_hotels) ?>)
            </button>
            <button onclick="switchTab('circuits')" class="tab-item mr-2 whitespace-nowrap">
                Circuits (<?= count($reservations_circuits) ?>)
            </button>
            <button onclick="switchTab('lieux')" class="tab-item whitespace-nowrap">
                Lieux (<?= count($reservations_lieux) ?>)
            </button>
        </div>

        <!-- Contenu des onglets -->
        <div id="tab-content">
            <!-- Événements -->
            <div id="evenements-tab" class="tab-panel">
                <?php if (empty($reservations_evenements)): ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-times text-4xl mb-3 text-gray-400"></i>
                        <p class="text-lg">Aucune réservation d'événement</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($reservations_evenements as $reservation): ?>
                            <div class="reservation-item">
                                <div class="flex justify-between items-start">
                                    <h3 class="reservation-title"><?= htmlspecialchars($reservation['evenement_nom']) ?></h3>
                                    <form method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette réservation ?');">
                                        <input type="hidden" name="reservation_id" value="<?= $reservation['id'] ?>">
                                        <input type="hidden" name="type" value="evenement">
                                        <button type="submit" name="annuler" class="btn-cancel">
                                            <i class="fas fa-times mr-1"></i> Annuler
                                        </button>
                                    </form>
                                </div>
                                <div class="mt-3">
                                    <p class="reservation-detail">
                                        <i class="fas fa-calendar mt-1"></i>
                                        <span>Du <?= date('d/m/Y', strtotime($reservation['date_debut'])) ?> au <?= date('d/m/Y', strtotime($reservation['date_fin'])) ?></span>
                                    </p>
                                    <p class="reservation-detail">
                                        <i class="fas fa-clock mt-1"></i>
                                        <span><?= $reservation['heure'] ?></span>
                                    </p>
                                    <p class="reservation-detail">
                                        <i class="fas fa-map-marker-alt mt-1"></i>
                                        <span><?= htmlspecialchars($reservation['ville']) ?></span>
                                    </p>
                                    <p class="reservation-detail">
                                        <i class="fas fa-users mt-1"></i>
                                        <span><?= $reservation['nombre_places'] ?> place(s)</span>
                                    </p>
                                    <p class="reservation-price">
                                        Total : <?= number_format($reservation['montant_total'], 0, ',', ' ') ?> FCFA
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Hôtels -->
            <div id="hotels-tab" class="tab-panel hidden">
                <?php if (empty($reservations_hotels)): ?>
                    <div class="empty-state">
                        <i class="fas fa-hotel text-4xl mb-3 text-gray-400"></i>
                        <p class="text-lg">Aucune réservation d'hôtel</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($reservations_hotels as $reservation): ?>
                            <div class="reservation-item">
                                <div class="flex justify-between items-start">
                                    <h3 class="reservation-title"><?= htmlspecialchars($reservation['hotel_nom']) ?></h3>
                                    <form method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette réservation ?');">
                                        <input type="hidden" name="reservation_id" value="<?= $reservation['id'] ?>">
                                        <input type="hidden" name="type" value="hotel">
                                        <button type="submit" name="annuler" class="btn-cancel">
                                            <i class="fas fa-times mr-1"></i> Annuler
                                        </button>
                                    </form>
                                </div>
                                <div class="mt-3">
                                    <p class="reservation-detail">
                                        <i class="fas fa-calendar mt-1"></i>
                                        <span>Du <?= date('d/m/Y', strtotime($reservation['date_arrivee'])) ?> au <?= date('d/m/Y', strtotime($reservation['date_depart'])) ?></span>
                                    </p>
                                    <p class="reservation-detail">
                                        <i class="fas fa-bed mt-1"></i>
                                        <span><?= htmlspecialchars($reservation['type_chambre']) ?></span>
                                    </p>
                                    <p class="reservation-detail">
                                        <i class="fas fa-map-marker-alt mt-1"></i>
                                        <span><?= htmlspecialchars($reservation['ville']) ?></span>
                                    </p>
                                    <p class="reservation-detail">
                                        <i class="fas fa-users mt-1"></i>
                                        <span><?= $reservation['nombre_personnes'] ?> personne(s)</span>
                                    </p>
                                    <p class="reservation-price">
                                        Total : <?= number_format($reservation['prix_total'], 0, ',', ' ') ?> FCFA
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Circuits -->
            <div id="circuits-tab" class="tab-panel hidden">
                <?php if (empty($reservations_circuits)): ?>
                    <div class="empty-state">
                        <i class="fas fa-route text-4xl mb-3 text-gray-400"></i>
                        <p class="text-lg">Aucune réservation de circuit</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($reservations_circuits as $reservation): ?>
                            <div class="reservation-item">
                                <div class="flex justify-between items-start">
                                    <h3 class="reservation-title"><?= htmlspecialchars($reservation['circuit_nom']) ?></h3>
                                    <form method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette réservation ?');">
                                        <input type="hidden" name="reservation_id" value="<?= $reservation['id'] ?>">
                                        <input type="hidden" name="type" value="circuit">
                                        <button type="submit" name="annuler" class="btn-cancel">
                                            <i class="fas fa-times mr-1"></i> Annuler
                                        </button>
                                    </form>
                                </div>
                                <div class="mt-3">
                                    <p class="reservation-detail">
                                        <i class="fas fa-calendar mt-1"></i>
                                        <span>Du <?= date('d/m/Y', strtotime($reservation['date_depart'])) ?></span>
                                    </p>
                                    <p class="reservation-detail">
                                        <i class="fas fa-clock mt-1"></i>
                                        <span>Durée : <?= $reservation['duree'] ?> jours</span>
                                    </p>
                                    <p class="reservation-detail">
                                        <i class="fas fa-users mt-1"></i>
                                        <span><?= $reservation['nombre_personnes'] ?> personne(s)</span>
                                    </p>
                                    <p class="reservation-price">
                                        Total : <?= number_format($reservation['prix_total'], 0, ',', ' ') ?> FCFA
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Lieux -->
            <div id="lieux-tab" class="tab-panel hidden">
                <?php if (empty($reservations_lieux)): ?>
                    <div class="empty-state">
                        <i class="fas fa-map-marked-alt text-4xl mb-3 text-gray-400"></i>
                        <p class="text-lg">Aucune réservation de lieu touristique</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($reservations_lieux as $reservation): ?>
                            <div class="reservation-item">
                                <div class="flex justify-between items-start">
                                    <h3 class="reservation-title"><?= htmlspecialchars($reservation['lieu_nom']) ?></h3>
                                    <form method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette réservation ?');">
                                        <input type="hidden" name="reservation_id" value="<?= $reservation['id'] ?>">
                                        <input type="hidden" name="type" value="lieu">
                                        <button type="submit" name="annuler" class="btn-cancel">
                                            <i class="fas fa-times mr-1"></i> Annuler
                                        </button>
                                    </form>
                                </div>
                                <div class="mt-3">
                                    <p class="reservation-detail">
                                        <i class="fas fa-calendar mt-1"></i>
                                        <span>Le <?= date('d/m/Y', strtotime($reservation['date_visite'])) ?></span>
                                    </p>
                                    <p class="reservation-detail">
                                        <i class="fas fa-map-marker-alt mt-1"></i>
                                        <span><?= htmlspecialchars($reservation['ville']) ?></span>
                                    </p>
                                    <p class="reservation-detail">
                                        <i class="fas fa-users mt-1"></i>
                                        <span><?= $reservation['nombre_personnes'] ?> personne(s)</span>
                                    </p>
                                    <p class="reservation-price">
                                        Total : <?= number_format($reservation['prix_total'], 0, ',', ' ') ?> FCFA
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include_once(__DIR__ . "/../includes/footer.php"); ?>

    <script>
        function switchTab(tabName) {
            // Désactiver tous les onglets
            document.querySelectorAll('.tab-item').forEach(item => {
                item.classList.remove('active');
            });
            document.querySelectorAll('.tab-panel').forEach(panel => {
                panel.classList.add('hidden');
            });

            // Activer l'onglet sélectionné
            document.querySelector(`[onclick="switchTab('${tabName}')"]`).classList.add('active');
            document.getElementById(`${tabName}-tab`).classList.remove('hidden');
        }
    </script>
</body>
</html>