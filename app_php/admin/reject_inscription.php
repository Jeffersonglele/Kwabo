<?php
session_start();
require_once('../config/database.php');

// Vérifier si l'admin est connecté
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit();
}

// Récupérer les données JSON
$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;

if (!$id) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID manquant']);
    exit();
}

try {
    // Récupérer les informations de l'utilisateur
    $stmt = $pdo->prepare("SELECT * FROM gestionnaires WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();

    if (!$user) {
        throw new Exception('Utilisateur non trouvé');
    }

    // Mettre à jour le statut de l'utilisateur
    $stmt = $pdo->prepare("UPDATE utilisateurs SET statut_paiement = 'rejete' WHERE id = ?");
    $stmt->execute([$id]);

    // Envoyer un email de notification à l'utilisateur
    $to = $user['email'];
    $subject = "Statut de votre inscription - Bénin Tourisme";
    
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .container { padding: 20px; }
            .header { background-color: #1a237e; color: white; padding: 20px; }
            .content { padding: 20px; }
            .footer { background-color: #f5f5f5; padding: 20px; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Statut de votre inscription - Bénin Tourisme</h2>
            </div>
            <div class='content'>
                <p>Cher(e) " . htmlspecialchars($user['prenom']) . " " . htmlspecialchars($user['nom']) . ",</p>
                <p>Nous regrettons de vous informer que votre inscription n'a pas pu être validée pour le moment.</p>
                <p>Pour plus d'informations ou pour soumettre une nouvelle demande, veuillez nous contacter directement.</p>
                <p>Type de compte demandé : " . htmlspecialchars($user['type_compte']) . "</p>
            </div>
            <div class='footer'>
                <p>Cet email a été envoyé automatiquement par le système Bénin Tourisme.</p>
                <p>Pour toute question, veuillez contacter notre support.</p>
            </div>
        </div>
    </body>
    </html>";

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Bénin Tourisme <contactbenintourisme@gmail.com>\r\n";

    mail($to, $subject, $message, $headers);

    header('Content-Type: application/json');
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 