<?php
/**
 * Fichier de configuration et d'envoi d'emails
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Chargement de l'autoloader Composer
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    die("Erreur : Le fichier d'autoload de Composer est introuvable. Veuillez exécuter 'composer install'.");
}
require_once($autoloadPath);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Vérification de la configuration de la base de données
$dbConfigPath = __DIR__ . '/database.php';
if (!file_exists($dbConfigPath)) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Configuration de la base de données introuvable',
        'file' => $dbConfigPath
    ]);
    exit;
}

// Inclusion de la configuration de la base de données
include_once($dbConfigPath);

// Vérification de la configuration de la base de données
if (!isset($pdo) || !($pdo instanceof PDO)) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Configuration de la base de données invalide'
    ]);
    exit;
}

/**
 * Envoie un email de confirmation d'inscription à la newsletter
 * 
 * @param string $email L'adresse email du destinataire
 * @return bool True si l'email a été envoyé avec succès, false sinon
 */
function sendConfirmationEmail($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        logError("Adresse email invalide : $email");
        return false;
    }

    $mail = new PHPMailer(true);
    
    try {
        // Configuration du serveur SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'contactbenintourisme@gmail.com';
        $mail->Password   = 'mbrl pvvm gczs feqt';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';
        $mail->SMTPDebug  = 0;
        $mail->Debugoutput = function($str, $level) {
            logError("PHPMailer: $str");
        };

        // Destinataires
        $fromEmail = 'contactbenintourisme@gmail.com';
        $fromName  = 'Bénin Tourisme';
        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($email);

        // Contenu de l'email
        $mail->isHTML(true);
        $mail->Subject = 'Bienvenue à la newsletter - Bénin Tourisme';
        
        // Construction du contenu HTML
        $mail->Body = buildConfirmationEmailContent();
        $mail->AltBody = strip_tags(str_replace(["</p>", "<br>", "<br/>"], "\n", buildConfirmationEmailContent()));

        $mail->send();
        return true;
    } catch (Exception $e) {
        logError("Erreur d'envoi d'email: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Construit le contenu HTML de l'email de confirmation
 * 
 * @return string Le contenu HTML de l'email
 */
function buildConfirmationEmailContent() {
    $year = date('Y');
    return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue à la newsletter - Bénin Tourisme</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #1a237e, #0d47a1);
            padding: 30px;
            text-align: center;
            color: white;
        }
        .header img {
            max-width: 180px;
            height: auto;
        }
        .content {
            padding: 30px;
        }
        h1 {
            color: #1a237e;
            margin-top: 0;
        }
        .cta-button {
            display: inline-block;
            background: #e67e22;
            color: white !important;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 4px;
            margin: 20px 0;
            font-weight: bold;
        }
        .footer {
            background: #f1f1f1;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .social-links {
            margin: 20px 0;
            text-align: center;
        }
        .social-links a {
            margin: 0 10px;
            color: #1a237e;
            text-decoration: none;
        }
        @media only screen and (max-width: 600px) {
            .container {
                border-radius: 0;
            }
            .content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="../assets/images/BT.png" alt="Bénin Tourisme">
            <h1>Bienvenue dans notre communauté</h1>
        </div>
        <div class="content">
            <p>Cher(e) Touriste,</p>
            
            <p>Nous sommes ravis de vous compter parmi nos abonnés. Vous recevrez désormais nos dernières actualités, offres exclusives et conseils de voyage directement dans votre boîte mail.</p>
            
            <p><strong>Ce que vous allez découvrir :</strong></p>
            <ul>
                <li>Les destinations incontournables du Bénin</li>
                <li>Les événements culturels à ne pas manquer</li>
                <li>Les offres spéciales de nos partenaires</li>
                <li>Des conseils d'experts pour votre séjour</li>
            </ul>
            
            <div style="text-align: center;">
                <a href="https://benintourisme.com/" class="cta-button">Découvrir le Bénin</a>
            </div>
            
            <div class="social-links">
                <p>Suivez-nous :</p>
                <a href="https://facebook.com/benintourisme">Facebook</a> | 
                <a href="https://www.instagram.com/benintourisme2025/?next=%2F#">Instagram</a> | 
                <a href="https://twitter.com/benintourisme">Twitter</a>
            </div>
            
            <p>À très vite,</p>
            <p><strong>L'équipe Bénin Tourisme</strong></p>
        </div>
        <div class="footer">
            <p>© $year Bénin Tourisme - Tous droits réservés</p>
            <p><a href="https://benintourisme.com/unsubscribe" style="color: #666;">Se désabonner</a></p>
        </div>
    </div>
</body>
</html>
HTML;
}

/**
 * Envoie une notification à l'administrateur pour une nouvelle inscription
 * 
 * @param string $email L'adresse email du nouvel inscrit
 * @return bool True si l'email a été envoyé avec succès, false sinon
 */
function sendAdminNotification($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        logError("Adresse email invalide pour la notification admin : $email");
        return false;
    }

    $mail = new PHPMailer(true);
    
    try {
        // Configuration du serveur SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'contactbenintourisme@gmail.com';
        $mail->Password   = 'mbrl pvvm gczs feqt';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';
        $mail->SMTPDebug  = 0;
        $mail->Debugoutput = function($str, $level) {
            logError("PHPMailer (Admin): $str");
        };

        // Destinataires
        $fromEmail = 'contactbenintourisme@gmail.com';
        $fromName  = 'Bénin Tourisme';
        $adminEmail = 'contactbenintourisme@gmail.com';
        $adminName  = 'Admin Bénin Tourisme';
        
        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($adminEmail, $adminName);
        $mail->addReplyTo($email);

        // Contenu de l'email
        $mail->isHTML(true);
        $mail->Subject = 'Nouvelle inscription à la newsletter';
        
        // Construction du contenu HTML
        $mail->Body = buildAdminNotificationContent($email);
        $mail->AltBody = strip_tags(str_replace(["</p>", "<br>", "<br/>"], "\n", buildAdminNotificationContent($email)));

        $mail->send();
        return true;
    } catch (Exception $e) {
        logError("Erreur d'envoi d'email admin: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Construit le contenu HTML de la notification admin
 * 
 * @param string $email L'email de l'abonné
 * @return string Le contenu HTML de l'email
 */
function buildAdminNotificationContent($email) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Inconnue';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Inconnu';
    $date = date('d/m/Y H:i:s');
    $year = date('Y');
    
    return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle inscription newsletter</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #1a237e, #0d47a1);
            padding: 20px;
            text-align: center;
            color: white;
        }
        .content {
            padding: 30px;
        }
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #1a237e;
            padding: 20px;
            margin: 20px 0;
            border-radius: 0 4px 4px 0;
        }
        .info-label {
            font-weight: bold;
            color: #1a237e;
            min-width: 120px;
            display: inline-block;
        }
        .action-button {
            display: inline-block;
            background: #1a237e;
            color: white !important;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 4px;
            margin-top: 10px;
        }
        .footer {
            background: #f1f1f1;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        @media only screen and (max-width: 600px) {
            .info-label {
                display: block;
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Nouvelle inscription newsletter</h2>
        </div>
        <div class="content">
            <p>Un nouvel abonné s'est inscrit à la newsletter :</p>
            
            <div class="info-box">
                <p><span class="info-label">Email :</span> $email</p>
                <p><span class="info-label">Date :</span> $date</p>
                <p><span class="info-label">IP :</span> $ip</p>
                <p><span class="info-label">Navigateur :</span> $userAgent</p>
            </div>
            
            <a href="mailto:$email" class="action-button">Répondre</a>
            
            <p style="margin-top: 30px;">
                <a href="#" style="color: #1a237e;">↪ Voir tous les abonnés</a>
            </p>
        </div>
        <div class="footer">
            <p>© $year Bénin Tourisme - Notification</p>
        </div>
    </div>
</body>
</html>
HTML;
}

/**
 * Journalise les erreurs et les messages de débogage
 * 
 * @param string $message Le message à journaliser
 * @param string $level Niveau de sévérité (error, warning, info, debug)
 * @return bool True si la journalisation a réussi, false sinon
 */
function logError($message, $level = 'error') {
    // Niveaux de log autorisés
    $allowedLevels = ['error', 'warning', 'info', 'debug'];
    $level = strtolower($level);
    
    if (!in_array($level, $allowedLevels)) {
        $level = 'error';
    }
    
    $message = trim($message);
    if (empty($message)) {
        return false;
    }
    
    $logDir = __DIR__ . '/logs';
    
    if (!is_dir($logDir) && !mkdir($logDir, 0755, true) && !is_dir($logDir)) {
        error_log("Impossible de créer le répertoire de logs : $logDir");
        return false;
    }
    
    $logFile = $logDir . '/newsletter_' . date('Y-m-d') . '.log';
    
    try {
        $timestamp = date('[Y-m-d H:i:s]');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'cli';
        $logMessage = "$timestamp [$level] [$ip] $message" . PHP_EOL;
        
        $result = file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
        
        if ($result === false && !file_exists($logFile)) {
            if (touch($logFile) && chmod($logFile, 0644)) {
                $result = file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
            }
        }
        
        if ($result === false) {
            error_log("Échec d'écriture dans le fichier de log : $logFile - Message : $message");
            return false;
        }
        
        return true;
        
    } catch (Exception $e) {
        error_log("Exception lors de la journalisation : " . $e->getMessage());
        return false;
    }
}

// Log initial
logError("Script démarré", 'info');

try {
    // Vérification de la méthode HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        logError("Méthode non autorisée: " . $_SERVER['REQUEST_METHOD']);
        throw new Exception('Méthode non autorisée');
    }

    // Vérification de la présence de l'email
    if (!isset($_POST['email'])) {
        logError("Email manquant dans la requête");
        throw new Exception('Email manquant');
    }

    // Validation de l'email
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    logError("Email reçu: " . $email, 'info');
    
    if (empty($email)) {
    logError("Email vide après sanitation");
    throw new Exception("Adresse email vide");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        logError("Email invalide: " . $email);
        throw new Exception('Adresse email invalide');
    }

    // Vérification de la connexion à la base de données
    try {
        $pdo->query("SELECT 1");
    } catch (PDOException $e) {
        logError("Erreur de connexion à la base de données: " . $e->getMessage());
        throw new Exception('Erreur de connexion à la base de données');
    }

    // Vérification si l'email existe déjà
    $stmt = $pdo->prepare("SELECT id FROM newsletter WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        logError("Email déjà inscrit: " . $email);
        throw new Exception('Cette adresse email est déjà inscrite');
    }

    // Insertion dans la base de données
    try {
        $stmt = $pdo->prepare("INSERT INTO newsletter (email, date_inscription, ip, user_agent) VALUES (?, NOW(), ?, ?)");
        $success = $stmt->execute([
            $email,
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT']
        ]);

        if (!$success) {
            logError("Erreur lors de l'insertion dans la base de données pour l'email: " . $email);
            throw new Exception('Erreur lors de l\'inscription dans la base de données');
        }

        logError("Inscription réussie pour l'email: " . $email, 'info');

        // Envoi des emails de confirmation
        $userEmailSent = sendConfirmationEmail($email);
        $adminEmailSent = sendAdminNotification($email);
        
        if ($userEmailSent && $adminEmailSent) {
            logError("Emails de confirmation envoyés avec succès", 'info');
            echo json_encode([
                'success' => true,
                'message' => 'Inscription réussie à la newsletter ! Un email de confirmation vous a été envoyé.'
            ]);
        } else {
            logError("Échec de l'envoi d'un ou plusieurs emails de confirmation", 'warning');
            echo json_encode([
                'success' => true,
                'message' => 'Inscription réussie, mais un problème est survenu lors de l\'envoi de l\'email. Veuillez vérifier votre boîte mail plus tard ou contacter l\'admin.'
            ]);
        }
    } catch (PDOException $e) {
        logError("Erreur PDO lors de l'insertion: " . $e->getMessage());
        throw new Exception('Erreur lors de l\'inscription dans la base de données');
    }

} catch (Exception $e) {
    logError("Erreur finale: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>