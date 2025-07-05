<?php
session_start();
include_once("../config/database.php");

// Vérifier si l'ID de l'événement est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: evenements.php');
    exit();
}

$event_id = $_GET['id'];

// Enregistrer la vue dans la base de données
if (isset($_SESSION['user_id'])) {
    // Récupérer l'ID du gestionnaire de l'événement
    $stmt = $pdo->prepare("SELECT gestionnaire_id FROM evenements WHERE id = ?");
    $stmt->execute([$event_id]);
    $gestionnaire_id = $stmt->fetchColumn();
    
    // Enregistrer la vue avec les informations du visiteur
    $stmt = $pdo->prepare("INSERT INTO vues (element_id, element_type, gestionnaire_id, ip_visiteur, user_agent) VALUES (?, 'evenement', ?, ?, ?)");
    $stmt->execute([
        $event_id,
        $gestionnaire_id,
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['HTTP_USER_AGENT']
    ]);
}

// Récupérer les détails de l'événement
$sql = "SELECT * FROM evenements WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$event_id]);
$evenement = $stmt->fetch();

if (!$evenement) {
    header('Location: evenements.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($evenement['nom']) ?> - Bénin Tourisme</title>
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
                        light: '#ecf0f1',
                        dark: '#2c3e50',
                        success: '#27ae60',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style type="text/tailwindcss">
        @layer components {
            .event-header {
                background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('<?= htmlspecialchars($evenement['image']) ?>');
                @apply bg-cover bg-center bg-fixed h-[60vh] flex items-center justify-center text-white relative mb-12;
            }
            
            .event-header::after {
                content: '';
                @apply absolute -bottom-12 left-0 w-full h-12;
                background: url('data:image/svg+xml;utf8,<svg viewBox="0 0 1200 120" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none"><path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z" opacity=".25" fill="%23f8f9fa"/><path d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z" opacity=".5" fill="%23f8f9fa"/><path d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z" fill="%23f8f9fa"/></svg>');
                @apply bg-cover;
            }
            
            .form-title {
                @apply text-primary mb-6 font-bold relative pb-2;
            }
            
            .form-title::after {
                content: '';
                @apply absolute bottom-0 left-0 w-14 h-1 bg-secondary;
            }
        }
    </style>
</head>
<body class="font-sans bg-gray-50 text-gray-800">
    <?php include_once(__DIR__ . "/../includes/navbar.php"); ?>

    <!-- En-tête de l'événement -->
    <header class="event-header">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold mb-4 drop-shadow-lg">
                <?= htmlspecialchars($evenement['nom']) ?>
            </h1>
            <p class="text-xl md:text-2xl opacity-90">
                <i class="fas fa-map-marker-alt mr-2"></i> <?= htmlspecialchars($evenement['ville']) ?>
            </p>
        </div>
    </header>

    <div class="container mx-auto px-4 mb-12">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Détails de l'événement -->
            <div class="lg:w-2/3">
                <div class="bg-white p-8 rounded-xl shadow-md mb-8">
                    <h2 class="text-2xl font-bold mb-6 text-primary">À propos de l'événement</h2>
                    <p class="text-lg mb-8 leading-relaxed">
                        <?= nl2br(htmlspecialchars($evenement['description'])) ?>
                    </p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
                        <div class="flex items-start">
                            <i class="fas fa-calendar-alt text-secondary text-2xl mr-4 mt-1"></i>
                            <div>
                                <h3 class="text-lg font-semibold mb-1">Date</h3>
                                <p class="text-gray-600">
                                    Du <?= date('d/m/Y', strtotime($evenement['date_debut'])) ?> au <?= date('d/m/Y', strtotime($evenement['date_fin'])) ?>
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <i class="fas fa-clock text-secondary text-2xl mr-4 mt-1"></i>
                            <div>
                                <h3 class="text-lg font-semibold mb-1">Heure</h3>
                                <p class="text-gray-600"><?= htmlspecialchars($evenement['heure']) ?></p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <i class="fas fa-map-marker-alt text-secondary text-2xl mr-4 mt-1"></i>
                            <div>
                                <h3 class="text-lg font-semibold mb-1">Lieu</h3>
                                <p class="text-gray-600"><?= htmlspecialchars($evenement['ville']) ?></p>
                            </div>
                        </div>
                        <span class="text-gray-800 font-medium text-lg">
                            Plus d'infos au niveau de la réservation</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Formulaire de réservation -->
            <div class="lg:w-1/3">
                    <div class="bg-white p-8 rounded-xl shadow-md sticky top-6">
                        <h3 class="form-title">Réserver votre place</h3>
                        <span class="text-gray-800 font-medium text-lg">
                        <?= htmlspecialchars($evenement['incitation'])?>
                        </span>
                        <div class="flex justify-center mt-10">
                        <div class="animate-bounce">
                                        <svg class="w-8 h-8 text-gray-700" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                        </svg>
                        </div>   
                        </div>
                        <a href="<?= htmlspecialchars($evenement['site']) ?>" class="btn-reserve block w-full bg-primary hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg text-center transition duration-300">
                        <i class="fas fa-ticket-alt mr-2"></i>Réserver maintenant
                        </a>        
                    </div>
                
            </div>
        </div>
    </div>

    <?php include_once(__DIR__ . "/../includes/footer.php"); ?>

    <script>
        // Calcul du prix total
        const prixUnitaire = <?= $evenement['prix'] ?>;
        const inputNombrePlaces = document.getElementById('nombre_places');
        const divPrixTotal = document.getElementById('prix_total');

        if (inputNombrePlaces) {
            inputNombrePlaces.addEventListener('input', function() {
                const nombrePlaces = this.value;
                const prixTotal = nombrePlaces * prixUnitaire;
                divPrixTotal.textContent = new Intl.NumberFormat('fr-FR').format(prixTotal) + ' FCFA';
            });
        }
    </script>
</body>
</html>