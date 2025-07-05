<?php
session_start();
require_once(__DIR__ . '/../config/database.php');

// Initialisation des variables
$message = '';
$error = '';
$email = '';

// Chargement de l'autoloader Composer
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    die("Erreur : Le fichier d'autoload de Composer est introuvable. Veuillez exécuter 'composer install'.");
}
require_once($autoloadPath);

// Génère un token sécurisé
$token = bin2hex(random_bytes(32));

// Sauvegarde le token dans la BDD avec une expiration
$stmt = $pdo->prepare("UPDATE utilisateurs SET reset_token = ?, reset_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email = ?");
$stmt->execute([$token, $email]);

// Envoie le lien de réinitialisation
$reset_link = "http://localhost/mot_de_passe/reset_password.php?token=" . $token;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Vérification de la méthode POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation de l'email
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Veuillez entrer une adresse email valide.";
    } else {
        try {
            // Vérification de l'existence de l'utilisateur
            $stmt = $pdo->prepare("SELECT id, prenom FROM utilisateurs WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                // Génération d'un token sécurisé
                $token = bin2hex(random_bytes(32));
                $expires = date("Y-m-d H:i:s", time() + 3600); // Expire dans 1 heure

                // Stockage du token en base de données
                $stmt = $pdo->prepare("UPDATE utilisateurs SET reset_token = ?, reset_expires = ? WHERE id = ?");
                $stmt->execute([$token, $expires, $user['id']]);

                // Construction du lien de réinitialisation
                $reset_link = "https://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=$token";

                // Configuration de PHPMailer
                $mail = new PHPMailer(true);
                
                try {
                    // Paramètres SMTP
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'contactbenintourisme@gmail.com';
                    $mail->Password = 'mbrl pvvm gczs feqt';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;
                    $mail->CharSet = 'UTF-8';
                    $mail->SMTPDebug = 0;

                    // Destinataire et contenu
                    $mail->setFrom('contactbenintourisme@gmail.com', 'Bénin Tourisme');
                    $mail->addAddress($email);
                    $mail->Subject = "Réinitialisation de votre mot de passe";
                    
                    // Corps du message en HTML et texte
                    $mail->isHTML(true);
                    $mail->Body = "
                        <h2>Réinitialisation de mot de passe</h2>
                        <p>Bonjour " . htmlspecialchars($user['prenom']) . ",</p>
                        <p>Vous avez demandé à réinitialiser votre mot de passe. Cliquez sur le lien ci-dessous pour définir un nouveau mot de passe :</p>
                        <p><a href='$reset_link' style='color: #007bff;'>Réinitialiser mon mot de passe</a></p>
                        <p>Ce lien expirera dans 1 heure.</p>
                        <p>Si vous n'avez pas fait cette demande, ignorez cet email.</p>
                        <p>Cordialement,<br>L'équipe Bénin Tourisme</p>
                    ";
                    

                    $mail->send();
                    $message = "Un email de réinitialisation a été envoyé à votre adresse.";
                } catch (Exception $e) {
                    error_log("Erreur d'envoi d'email: " . $mail->ErrorInfo);
                    $error = "Erreur lors de l'envoi de l'email. Veuillez réessayer plus tard.";
                }
            } else {
                $error = "Aucun utilisateur trouvé avec cette adresse email.";
            }
        } catch (PDOException $e) {
            error_log("Erreur de base de données: " . $e->getMessage());
            $error = "Une erreur est survenue. Veuillez réessayer plus tard.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié - Bénin Tourisme</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .glass-effect {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="container mx-auto px-4 py-8 max-w-md">
        <div class="glass-effect rounded-lg shadow-xl p-8">
            <h1 class="text-3xl font-bold mb-6 text-center text-gray-800">Mot de passe oublié</h1>

            <?php if ($message): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-4">
                <div>
                    <label for="email" class="block text-gray-700 font-medium mb-2">Adresse email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required 
                        value="<?= htmlspecialchars($email) ?>" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                        placeholder="Entrez votre adresse email"
                    >
                </div>

                <button 
                    type="submit" 
                    class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
                >
                    Envoyer le lien de réinitialisation
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="connexion.php" class="text-blue-600 hover:text-blue-800 hover:underline transition-colors">
                    ← Retour à la connexion
                </a>
            </div>
        </div>
    </div>
</body>
</html>