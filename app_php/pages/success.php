<?php
session_start();
require_once '../config/database.php';

// Activer l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit();
}

// Récupérer les informations de l'utilisateur
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Logger les données reçues
error_log("Données reçues dans success.php : " . json_encode($_GET));

// Vérifier si le paiement a réussi
if (isset($_GET['status']) && $_GET['status'] === 'success') {
    try {
        // Mettre à jour le statut de paiement
        $stmt = $pdo->prepare("UPDATE utilisateurs SET statut_paiement = 'en_attente' WHERE id = ?");
        $stmt->execute([$user_id]);
        
        error_log("Statut mis à jour pour l'utilisateur ID: " . $user_id);
        
        // Envoyer un email de confirmation
        $to = $user['email'];
        $subject = "Confirmation de paiement - Bénin Tourisme";
        $message = "Bonjour " . $user['prenom'] . ",\n\n";
        $message .= "Votre paiement a été reçu avec succès. Votre demande est maintenant en cours d'examen par notre équipe.\n";
        $message .= "Vous recevrez une notification dès que votre compte sera validé.\n\n";
        $message .= "Cordialement,\nL'équipe Bénin Tourisme";
        
        $headers = "From: noreply@benintourisme.com\r\n";
        $headers .= "Reply-To: support@benintourisme.com\r\n";
        
        mail($to, $subject, $message, $headers);
        
    } catch (Exception $e) {
        error_log("Erreur dans success.php : " . $e->getMessage());
    }
}

include_once("../includes/navbar.php");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement Réussi - Bénin Tourisme</title>
    <link rel="icon" type="image/svg+xml" href="../assets/favicon/favicon.svg">
    <link rel="icon" type="image/png" sizes="96x96" href="../assets/favicon/favicon-96x96.png">
    <link rel="shortcut icon" href="../assets/favicon/favicon.ico">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-white">
<section class="bg-white py-16 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto text-center">
        <div class="mb-8">
            <i class="fas fa-check-circle text-green-500 text-6xl"></i>
        </div>
        <h1 class="text-4xl font-extrabold text-gray-900 mb-4">🌟 Paiement effectué avec succès !</h1>
        <p class="text-lg text-gray-600 mb-8">
            Votre demande pour devenir gestionnaire a été enregistrée avec succès. Notre équipe va examiner votre dossier et vous recevrez une notification une fois validé.
        </p>
    </div>

    <div class="mt-12 grid gap-12 md:grid-cols-2">
        <!-- Section prochaines étapes -->
        <div>
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Prochaines étapes</h2>
            <ul class="list-disc list-inside text-gray-700 space-y-2">
                <li>Vérification de votre dossier par notre équipe</li>
                <li>Validation de votre compte gestionnaire</li>
                <li>Accès à votre tableau de bord</li>
                <li>Possibilité de gérer vos lieux touristiques</li>
            </ul>
        </div>

        <!-- Section informations -->
        <div>
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Informations importantes</h2>
            <p class="text-gray-700 mb-4">
                Vous recevrez un email de confirmation dès que votre compte sera validé. En attendant, vous pouvez continuer à utiliser votre compte utilisateur normal.
            </p>
            <p class="text-gray-700">
                Si vous avez des questions, n'hésitez pas à contacter notre support.
            </p>
        </div>
    </div>

    <!-- Boutons d'action -->
    <div class="mt-16 text-center space-x-4">
        <a href="gestion.php" class="inline-block bg-blue-600 text-white text-lg font-semibold py-3 px-6 rounded-lg shadow-md hover:bg-blue-700 transition">
            <i class="fas fa-home mr-2"></i>
            Retour à mon compte
        </a>
        <a href="../contact.php" class="inline-block bg-gray-600 text-white text-lg font-semibold py-3 px-6 rounded-lg shadow-md hover:bg-gray-700 transition">
            <i class="fas fa-envelope mr-2"></i>
            Contacter le support
        </a>
    </div>
</section>

<?php include('../includes/footer.php'); ?>
</body>
</html> 