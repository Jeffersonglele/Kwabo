<?php
session_start();
include_once("..\config\database.php");

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// Récupérer les paramètres de l'URL
$type = isset($_GET['type']) ? $_GET['type'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : '';

// Récupérer les informations de l'hôtel si c'est une réservation d'hôtel
$hotel = null;
if ($type === 'hotel' && !empty($id)) {
    $stmt = $pdo->prepare("SELECT * FROM hotels WHERE id = ?");
    $stmt->execute([$id]);
    $hotel = $stmt->fetch();
    
    // Si l'hôtel n'existe pas, rediriger avec un message d'erreur
    if (!$hotel) {
        header("Location: hotel.php?error=hotel_?");
        exit();
    }
}

// Afficher les messages d'erreur s'il y en a
$error_message = '';
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'donnees_manquantes':
            $error_message = "Veuillez remplir tous les champs obligatoires.";
            break;
        case 'hotel_invalide':
            $error_message = "L'hôtel sélectionné n'existe pas.";
            break;
        case 'erreur_systeme':
            $error_message = "Une erreur est survenue. Veuillez réessayer.";
            break;
        default:
            $error_message = "Une erreur est survenue.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation - Bénin Tourisme</title>
    <link rel="icon" type="image/svg+xml" href="../assets/favicon/favicon.svg">
    <link rel="icon" type="image/png" sizes="96x96" href="../assets/favicon/favicon-96x96.png">
    <link rel="shortcut icon" href="../assets/favicon/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .reservation-container {
            padding-top: 100px;
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        .reservation-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <?php include_once(__DIR__ . "/../includes/navbar.php"); ?>

    <div class="reservation-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="reservation-card p-4">
                        <h2 class="text-center mb-4">Réservation</h2>
                        
                        <?php if ($error_message): ?>
                            <div class="alert alert-danger"><?php echo $error_message; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($type === 'hotel' && $hotel): ?>
                            <div class="hotel-info mb-4">
                                <h3><?php echo htmlspecialchars($hotel['nom']); ?></h3>
                                <p class="text-muted"><?php echo htmlspecialchars($hotel['adresse']); ?></p>
                            </div>
                            
                            <form method="POST" action="traitement_reservation.php">
                                <input type="hidden" name="type" value="hotel">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="date_arrivee" class="form-label">Date d'arrivée</label>
                                        <input type="date" class="form-control" id="date_arrivee" name="date_arrivee" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="date_depart" class="form-label">Date de départ</label>
                                        <input type="date" class="form-control" id="date_depart" name="date_depart" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="nombre_personnes" class="form-label">Nombre de personnes</label>
                                    <select class="form-select" id="nombre_personnes" name="nombre_personnes" required>
                                        <option value="1">1 personne</option>
                                        <option value="2">2 personnes</option>
                                        <option value="3">3 personnes</option>
                                        <option value="4">4 personnes</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="commentaires" class="form-label">Commentaires ou demandes spéciales</label>
                                    <textarea class="form-control" id="commentaires" name="commentaires" rows="3"></textarea>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Confirmer la réservation</button>
                                </div>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                Type de réservation non spécifié ou invalide.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include_once(__DIR__ . "/../includes/footer.php"); ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validation des dates
        document.addEventListener('DOMContentLoaded', function() {
            const dateArrivee = document.getElementById('date_arrivee');
            const dateDepart = document.getElementById('date_depart');
            
            // Définir la date minimale comme aujourd'hui
            const today = new Date().toISOString().split('T')[0];
            dateArrivee.min = today;
            
            // Mettre à jour la date minimale de départ quand la date d'arrivée change
            dateArrivee.addEventListener('change', function() {
                dateDepart.min = this.value;
                if (dateDepart.value && dateDepart.value < this.value) {
                    dateDepart.value = this.value;
                }
            });
        });
    </script>
</body>
</html>