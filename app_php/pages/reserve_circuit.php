<?php
session_start();
include_once("..\config\database.php");

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: connexion.php');
    exit;
}

// Récupérer l'ID du circuit
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: circuits.php');
    exit;
}

$circuit_id = (int)$_GET['id'];

// Récupérer les informations du circuit
$stmt = $pdo->prepare("SELECT * FROM circuits WHERE id = ?");
$stmt->execute([$circuit_id]);
$circuit = $stmt->fetch();

if (!$circuit) {
    header('Location: circuits.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réserver <?= htmlspecialchars($circuit['nom']) ?> - Bénin Tourisme</title>
    <link rel="icon" type="image/svg+xml" href="../assets/favicon/favicon.svg">
    <link rel="icon" type="image/png" sizes="96x96" href="../assets/favicon/favicon-96x96.png">
    <link rel="shortcut icon" href="../assets/favicon/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .reservation-header {
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), 
                        url('<?= htmlspecialchars($circuit['image']) ?>');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 4rem 0;
            margin-bottom: 2rem;
        }
        .circuit-info {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        .price-tag {
            font-size: 2rem;
            color: #0d6efd;
            font-weight: bold;
        }
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            position: relative;
        }
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            z-index: 1;
        }
        .step.active {
            background-color: #0d6efd;
            color: white;
        }
        .step-line {
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 2px;
            background-color: #e9ecef;
            transform: translateY(-50%);
            z-index: 0;
        }
    </style>
</head>
<body>
    <?php include_once(__DIR__ . "/../includes/navbar.php"); ?>

    <header class="reservation-header">
        <div class="container text-center">
            <h1 class="display-4">Réserver votre circuit</h1>
            <p class="lead"><?= htmlspecialchars($circuit['nom']) ?></p>
        </div>
    </header>

    <div class="container mb-5">
        <?php if (isset($_SESSION['error_messages'])): ?>
            <div class="alert alert-danger">
                <?php 
                foreach ($_SESSION['error_messages'] as $error) {
                    echo "<p class='mb-0'><i class='fas fa-exclamation-circle me-2'></i>$error</p>";
                }
                unset($_SESSION['error_messages']);
                ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <form action="circuit_reservation.php" method="POST" class="needs-validation" novalidate>
                            <input type="hidden" name="circuit_id" value="<?= $circuit_id ?>">
                            
                            <div class="circuit-info mb-4">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h4><?= htmlspecialchars($circuit['nom']) ?></h4>
                                        <p class="mb-2">
                                            <i class="fas fa-map-marker-alt me-2"></i>
                                            Départ de <?= htmlspecialchars($circuit['lieu_depart']) ?>
                                        </p>
                                        <p class="mb-0">
                                            <i class="fas fa-clock me-2"></i>
                                            Durée : <?= htmlspecialchars($circuit['duree']) ?> jours
                                        </p>
                                    </div>
                                    <div class="col-md-4 text-md-end">
                                        <div class="price-tag">
                                            <?= number_format($circuit['prix'], 0, ',', ' ') ?> FCFA
                                        </div>
                                        <small class="text-muted">par personne</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="date_depart" class="form-label">Date de départ *</label>
                                    <input type="date" class="form-control" id="date_depart" name="date_depart" 
                                           min="<?= date('Y-m-d') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="nombre_personnes" class="form-label">Nombre de personnes *</label>
                                    <select class="form-select" id="nombre_personnes" name="nombre_personnes" required>
                                        <option value="">Choisir...</option>
                                        <?php for ($i = 1; $i <= min(20, $circuit['places_disponibles']); $i++): ?>
                                            <option value="<?= $i ?>"><?= $i ?> personne<?= $i > 1 ? 's' : '' ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="type_forfait" class="form-label">Type de forfait *</label>
                                    <select class="form-select" id="type_forfait" name="type_forfait" required>
                                        <option value="">Choisir...</option>
                                        <option value="standard">Standard (transport + visites)</option>
                                        <option value="confort">Confort (+20%) - Inclut les repas</option>
                                        <option value="luxe">Premium (+50%) - Inclut repas, guide privé et activités VIP</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="telephone" class="form-label">Téléphone *</label>
                                    <input type="tel" class="form-control" id="telephone" name="telephone" required>
                                </div>
                                <div class="col-12">
                                    <label for="commentaires" class="form-label">Commentaires ou demandes spéciales</label>
                                    <textarea class="form-control" id="commentaires" name="commentaires" rows="3"></textarea>
                                </div>

                                <div class="col-12">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="conditions" required>
                                        <label class="form-check-label" for="conditions">
                                            J'accepte les conditions générales de réservation
                                        </label>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-lg w-100">
                                        <i class="fas fa-check-circle me-2"></i>Confirmer la réservation
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Résumé du circuit</h5>
                        <ul class="list-unstyled mb-4">
                            <li class="mb-2">
                                <i class="fas fa-calendar me-2"></i>
                                Durée : <?= htmlspecialchars($circuit['duree']) ?> jours
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-users me-2"></i>
                                Places disponibles : <?= htmlspecialchars($circuit['places_disponibles']) ?>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                Départ : <?= htmlspecialchars($circuit['lieu_depart']) ?>
                            </li>
                        </ul>

                        <div class="alert alert-info">
                            <h6 class="alert-heading">
                                <i class="fas fa-info-circle me-2"></i>
                                Informations sur les forfaits
                            </h6>
                            <p class="small mb-0">
                                <strong>Standard :</strong> Transport en minibus climatisé et visites des sites.<br>
                                <strong>Confort :</strong> Inclut les repas dans des restaurants locaux sélectionnés.<br>
                                <strong>Premium :</strong> Guide privé, repas gastronomiques, activités exclusives et accès VIP aux sites.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include_once(__DIR__ . "/../includes/footer.php"); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validation du formulaire
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()

        // Calcul du prix total
        function updateTotal() {
            const basePrice = <?= $circuit['prix'] ?>;
            const nbPersonnes = document.getElementById('nombre_personnes').value;
            const typeForfait = document.getElementById('type_forfait').value;
            
            let total = basePrice * nbPersonnes;
            
            switch(typeForfait) {
                case 'confort':
                    total *= 1.2;
                    break;
                case 'luxe':
                    total *= 1.5;
                    break;
            }
            
            document.getElementById('prix_total').textContent = new Intl.NumberFormat('fr-FR').format(total) + ' FCFA';
        }

        document.getElementById('nombre_personnes').addEventListener('change', updateTotal);
        document.getElementById('type_forfait').addEventListener('change', updateTotal);
    </script>
</body>
</html> 