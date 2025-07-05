<?php
session_start();
require_once('../config/database.php');
require '../vendor/autoload.php'; // PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
    // Récupérer le gestionnaire
    $stmt = $pdo->prepare("SELECT * FROM gestionnaires WHERE id = ?");
    $stmt->execute([$id]);
    $gestionnaire = $stmt->fetch();

    if (!$gestionnaire) {
        throw new Exception('Gestionnaire non trouvé');
    }

    // Mettre à jour le statut
    $stmt = $pdo->prepare("UPDATE gestionnaires SET statut_paiement = 'valide' WHERE id = ?");
    $stmt->execute([$id]);

    // Préparer les données pour l'email
    $nom = htmlspecialchars($gestionnaire['nom'] ?? '');
    $email = $gestionnaire['email'] ?? '';
    $structure = htmlspecialchars($gestionnaire['type_compte'] ?? '');
    $date = htmlspecialchars($gestionnaire['date_inscription'] ?? date('Y-m-d'));

    // Générer l'URL complète
    $host = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
    $dashboard_url = $host . "/ProjetBinome/gestionnaire/tableau_bord.php";

    // PHPMailer config
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'contactbenintourisme@gmail.com';       
    $mail->Password   = 'mbrl pvvm gczs feqt';            
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('contactbenintourisme@gmail.com', 'Bénin Tourisme');
    $mail->addAddress($email, "$nom");
    $mail->isHTML(true);
    $mail->Subject = 'Votre inscription a été validée - Bénin Tourisme';

    // Corps de l'email avec les variables correctement intégrées
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';

    $mail->Body = '
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Inscription Validée - Bénin Tourisme</title>
        
    </head>
    <body>
        <div class="container" style="max-width: 600px;margin: 0 auto;background: #ffffff;border-radius: 12px;overflow: hidden;box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);">
            <div class="header" style="background: linear-gradient(135deg, #1a237e 0%, #0d47a1 100%);padding: 40px 30px;text-align: center;color: white;">
                <h1 style="margin: 0;font-size: 28px;font-weight: 600;">Bienvenue sur Bénin Tourisme</h1>
                <p style="margin: 10px 0 0;opacity: 0.9;font-size: 16px;">Votre inscription a été validée avec succès</p>
            </div>
            
            <div class="content" style="padding: 40px 30px;">
                <div class="greeting" style="font-size: 18px;margin-bottom: 25px;color: #1a237e;font-weight: 500;">Cher(e) '.$nom.',</div>
                
                <div class="message" style="margin-bottom: 30px;font-size: 16px;">
                    <p>Nous sommes ravis de vous accueillir sur notre plateforme dédiée aux professionnels du tourisme. Votre compte gestionnaire est maintenant actif et vous pouvez commencer à profiter de tous nos services.</p>
                    <p>Merci pour votre confiance et à très bientôt sur Bénin Tourisme !</p>
                </div>
                
                <div class="details-card" style="background: #f8f9fa;border-radius: 10px;padding: 20px;margin: 25px 0;border-left: 4px solid #1a237e;">
                    <div class="detail-item" style="margin-bottom: 10px;display: flex;">
                        <span class="detail-label" style="font-weight: 500;min-width: 120px;color: #555555;">Type de structure :</span>
                        <span class="detail-value" style="font-weight: 400;color: #222222;">'.$structure.'</span>
                    </div>
                    <div class="detail-item" style="margin-bottom: 10px;display: flex;">
                        <span class="detail-label" style="font-weight: 500;min-width: 120px;color: #555555;">Email :</span>
                        <span class="detail-value" style="font-weight: 400;color: #222222;">'.$email.'</span>
                    </div>
                    <div class="detail-item" style="margin-bottom: 10px;display: flex;">
                        <span class="detail-label" style="font-weight: 500;min-width: 120px;color: #555555;">Date d\'inscription :</span>
                        <span class="detail-value" style="font-weight: 400;color: #222222;">'.$date.'</span>
                    </div>
                </div>
                
                <div class="button-container" style="text-align: center;margin: 35px 0;">
                    <a href="'.$dashboard_url.'" class="button" style="display: inline-block;background: linear-gradient(135deg, #1a237e 0%, #0d47a1 100%);text-decoration: none;padding: 14px 28px;border-radius: 50px;font-weight: 500;font-size: 16px;box-shadow: 0 4px 12px rgba(26, 35, 126, 0.2);transition: all 0.3s ease;color: white !important;">Accéder à mon tableau de bord</a>
                </div>
                
                <div class="message" style="margin-bottom: 30px;font-size: 16px;">
                    <p><strong>Besoin d\'aide ?</strong> Notre équipe est à votre disposition pour vous accompagner dans la prise en main de la plateforme.</p>
                </div>
            </div>
            
            <div class="footer" style="background: #f5f7fb;padding: 25px 30px;text-align: center;font-size: 13px;color: #666666;border-top: 1px solid #e9ecef;">
                <div>© '.date('Y').' Bénin Tourisme - Tous droits réservés</div>
                <div class="contact-info" style="margin-top: 15px;">
                    <a href="mailto:contactbenintourisme@gmail.com" style="color: #1a237e;text-decoration: none;">contactbenintourisme@gmail.com</a> | 
                    <a href="tel:+22990077139" tel:+22964780067" style="color: #1a237e;text-decoration: none;">
                    <br>
                    +229 90 07 71 39||+229 64 78 00 67</a>
                </div>
                
                <div class="social-links" style="margin-top: 20px;">
                    <a href="#" class="social-icon" style="display: inline-block;margin: 0 8px;width: 36px;height: 36px;background: #1a237e;border-radius: 50%;text-align: center;line-height: 36px;"><img src="https://cdn-icons-png.flaticon.com/512/124/124010.png" alt="Facebook" style="vertical-align: middle;width: 18px;"></a>
                    <a href="#" class="social-icon" style="display: inline-block;margin: 0 8px;width: 36px;height: 36px;background: #1a237e;border-radius: 50%;text-align: center;line-height: 36px;"><img src="https://cdn-icons-png.flaticon.com/512/733/733579.png" alt="Twitter" style="vertical-align: middle;width: 18px;"></a>
                    <a href="#" class="social-icon" style="display: inline-block;margin: 0 8px;width: 36px;height: 36px;background: #1a237e;border-radius: 50%;text-align: center;line-height: 36px;"><img src="https://cdn-icons-png.flaticon.com/512/2111/2111463.png" alt="Instagram" style="vertical-align: middle;width: 18px;"></a>
                    <a href="#" class="social-icon" style="display: inline-block;margin: 0 8px;width: 36px;height: 36px;background: #1a237e;border-radius: 50%;text-align: center;line-height: 36px;"><img src="https://cdn-icons-png.flaticon.com/512/3536/3536505.png" alt="LinkedIn" style="vertical-align: middle;width: 18px;"></a>
                </div>
            </div>
        </div>
    </body>
    </html>';

    $mail->send();

    header('Content-Type: application/json');
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
}
?>