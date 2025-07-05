<?php
require_once '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Récupérer les données JSON
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Données invalides']);
    exit();
}

// Configuration de PHPMailer
$mail = new PHPMailer(true);

try {
    // Configuration du serveur
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'contactbenintourisme@gmail.com'; // Votre email Gmail
    $mail->Password = 'mbrl pvvm gczs feqt'; // Votre mot de passe d'application Gmail
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';

    // Destinataires
    $mail->setFrom('contactbenintourisme@gmail.com', 'Bénin Tourisme');
    $mail->addAddress('contactbenintourisme@gmail.com', 'Administrateur');

    // Contenu
    $mail->isHTML(true);
    $mail->Subject = 'Nouvelle inscription - Bénin Tourisme';
    
    // Corps du message
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #1a237e; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background-color: #f9f9f9; }
            .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Nouvelle Inscription</h2>
            </div>
            <div class='content'>
                <p>Bonjour,</p>
                <p>Une nouvelle inscription a été effectuée sur Bénin Tourisme :</p>
                <ul>
                    <li><strong>Nom :</strong> {$data['nom']}</li>
                    <li><strong>Prénom :</strong> {$data['prenom']}</li>
                    <li><strong>Email :</strong> {$data['email']}</li>
                    <li><strong>Type de compte :</strong> " . ucfirst($data['type_compte']) . "</li>
                </ul>
                <p>Le paiement a été effectué avec succès. Veuillez valider cette inscription dans votre espace administrateur.</p>
                <p>
                    <a href='http://localhost/stage/ProjetBinome/admin/inscriptions.php' 
                       style='background-color: #1a237e; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>
                        Accéder à l'administration
                    </a>
                </p>
            </div>
            <div class='footer'>
                <p>Ce message a été généré automatiquement par le système de Bénin Tourisme.</p>
                <p>&copy; " . date('Y') . " Bénin Tourisme. Tous droits réservés.</p>
            </div>
        </div>
    </body>
    </html>";

    $mail->Body = $message;
    $mail->AltBody = "Nouvelle inscription sur Bénin Tourisme\n\n" .
                     "Nom : {$data['nom']}\n" .
                     "Prénom : {$data['prenom']}\n" .
                     "Email : {$data['email']}\n" .
                     "Type de compte : " . ucfirst($data['type_compte']) . "\n\n" .
                     "Le paiement a été effectué avec succès. Veuillez valider cette inscription dans votre espace administrateur.";

    $mail->send();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    error_log("Erreur d'envoi d'email : " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erreur lors de l\'envoi de l\'email']);
}
?> 