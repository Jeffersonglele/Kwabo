<?php
session_start();
$page_title = "Inscription réussie - Bénin Tourisme";

require_once('../vendor/autoload.php');
include_once("../config/database.php");

// Configuration de FedaPay
\FedaPay\FedaPay::setApiKey('sk_live_XTmVwujCVI1dFJgnlqoFOnsG');
\FedaPay\FedaPay::setEnvironment('live');

$paiement_valide = false;
$transaction_id = $_GET['id'] ?? null;
$message = '';

if ($transaction_id) {
    try {
        // Récupération des données de la transaction FedaPay
        $transaction = \FedaPay\Transaction::retrieve($transaction_id);
        $data = $transaction->serialize();

        // Journalisation pour le débogage
        file_put_contents('fedapay_debug.log', date('Y-m-d H:i:s') . " - Transaction ID: $transaction_id - Statut: " . ($data['status'] ?? 'inconnu') . "\n", FILE_APPEND);

        // Vérification du statut du paiement
        // Vérifier le statut de la transaction
        if (!isset($data['status']) || 
            strtolower($data['status']) !== 'approved' || 
            !isset($data['successful']) || 
            $data['successful'] !== true) {
            
            // Journaliser l'échec
            $status = $data['status'] ?? 'inconnu';
            error_log("Paiement non approuvé - Statut: $status - Transaction: $transaction_id");
            
            // Rediriger vers la page d'échec
            header('Location: payment_failed.php');
            exit();
        }
        
        // Si on arrive ici, le paiement est approuvé
        if (!isset($_SESSION['pending_inscription'])) {
            $message = "Session expirée. Veuillez réessayer votre inscription.";
            error_log("Session expirée pour la transaction: $transaction_id");
            header('Location: inscription_gestion.php?error=session_expired');
            exit();
        }
        
        $inscription = $_SESSION['pending_inscription'];
        
        // Vérifier si l'email n'existe pas déjà
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM gestionnaires WHERE email = ? AND statut_paiement = 'valide'");
        $stmt->execute([$inscription['email']]);
        
        if ($stmt->fetchColumn() > 0) {
            $message = "Un compte existe déjà avec cette adresse email.";
            error_log("Tentative de création d'un compte existant: " . $inscription['email']);
            header('Location: inscription_gestion.php?error=email_exists');
            exit();
        }
        
        // Création du compte
        $hashed_password = password_hash($inscription['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO gestionnaires (
            nom, email, telephone, mot_de_passe, type_compte, statut_paiement, date_inscription
        ) VALUES (?, ?, ?, ?, ?, 'valide', NOW())");
        
        $stmt->execute([
            $inscription['nom'],
            $inscription['email'],
            $inscription['telephone'],
            $hashed_password,
            $inscription['type_compte']
        ]);
        
        if ($stmt->rowCount() > 0) {
            $paiement_valide = true;
            $message = "Votre inscription a été validée avec succès ! Vous pouvez maintenant vous connecter.";
            
            // Nettoyer les données de session
            unset($_SESSION['pending_inscription']);
            
            // Préparer l'email de confirmation
            $email_data = [
                'nom' => $inscription['nom'],
                'email' => $inscription['email'],
                'type_compte' => $inscription['type_compte']
            ];
            
            // Envoyer l'email de confirmation (fonction à implémenter)
            // send_confirmation_email($email_data);
        } else {
            $message = "Erreur lors de la création de votre compte. Veuillez contacter le support.";
            error_log("Erreur création compte - Impossible d'insérer dans la base de données");
            header('Location: payment_failed.php?error=database_error');
            exit();
        }

        // Envoi de l'email de confirmation uniquement si le paiement est valide et le compte créé
        if ($paiement_valide) {
            // Préparation du mail
            $date = date("d/m/Y à H:i");
            $to = "contactbenintourisme@gmail.com";
            $subject = "✅ Nouvelle inscription validée - Bénin Tourisme";

            $message = "
                <html>
                <head>
                <title>Nouvelle inscription validée</title>
                <style>
                    body { font-family: 'Poppins', sans-serif; color: #333; }
                    .highlight { font-weight: bold; color: #1a237e; }
                    .section { margin-bottom: 15px; }
                </style>
                </head>
                <body>
                <h2 style=\"color:#1a237e\">Nouvelle inscription validée</h2>

                <div class=\"section\">
                    <p><span class=\"highlight\">Nom :</span> {$inscription['nom']}</p>
                    <p><span class=\"highlight\">Email :</span> {$inscription['email']}</p>
                    <p><span class=\"highlight\">Type de structure :</span> {$inscription['type_compte']}</p>
                    <p><span class=\"highlight\">Date :</span> $date</p>
                </div>

                <div class=\"section\">
                    <p>✅ Cette inscription a été confirmée suite à un paiement validé via FedaPay.</p>
                    <p>ℹ️ Vous pouvez maintenant valider ou activer ce compte depuis le panneau d'administration.</p>
                </div>
                <p style='margin-top: 20px;'>
                    <a href='https://ton-domaine.com/admin' style='display:inline-block; background-color:#1a237e; color:white; padding:10px 20px; border-radius:6px; text-decoration:none;'>📂 Accéder au panneau d'administration</a>
                </p>

                <footer style=\"margin-top: 20px; font-size: 13px; color: #777;\">
                    Email automatique généré par le système Bénin Tourisme
                </footer>
                </body>
                </html>";

            $headers  = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type:text/html; charset=UTF-8\r\n";
            $headers .= "From: $email\r\n";
            $headers .= "Reply-To: $email\r\n";

            @mail($to, $subject, $message, $headers);
            $paiement_valide = true;
        }

    }
    catch (Exception $e) {
        error_log("Erreur FedaPay callback : " . $e->getMessage());
    }
}
?>



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="icon" type="image/svg+xml" href="../assets/favicon/favicon.svg">
    <link rel="icon" type="image/png" sizes="96x96" href="../assets/favicon/favicon-96x96.png">
    <link rel="shortcut icon" href="../assets/favicon/favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #fafafa;
            padding: 20px;
        }

        .success-container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 600px;
            width: 100%;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: #28a745;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            animation: scaleIn 0.5s ease-out;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }

        .success-icon svg {
            width: 40px;
            height: 40px;
            fill: white;
        }

        h1 {
            color: #1a237e;
            margin-bottom: 20px;
            font-size: 28px;
        }

        .message {
            color: #495057;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .status {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 30px;
            font-weight: 500;
        }

        .next-steps {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: left;
        }

        .next-steps h2 {
            color: #1a237e;
            font-size: 18px;
            margin-bottom: 15px;
        }

        .next-steps ul {
            list-style: none;
            padding-left: 20px;
        }

        .next-steps li {
            margin-bottom: 10px;
            color: #495057;
            position: relative;
            padding-left: 25px;
        }

        .next-steps li:before {
            content: "•";
            color: #1a237e;
            font-weight: bold;
            position: absolute;
            left: 0;
        }

        .button {
            display: inline-block;
            padding: 12px 30px;
            background: #1a237e;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .button:hover {
            background: #0d47a1;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 71, 161, 0.3);
        }

        .contact-info {
            margin-top: 30px;
            color: #6c757d;
            font-size: 14px;
        }

        .contact-info a {
            color: #1a237e;
            text-decoration: none;
        }

        .contact-info a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <?php if ($paiement_valide): ?>
            <div class="success-icon">
                <svg viewBox="0 0 24 24">
                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
                </svg>
            </div>
            
            <h1>Paiement Réussi !</h1>
            <div class="message">Votre inscription a été enregistrée avec succès.</div>
            <div class="status">Votre compte est en attente de validation par l'administrateur.</div>
            <div class="next-steps">
                <h2>Prochaines étapes :</h2>
                <ul>
                    <li>Vous recevrez un email de confirmation une fois votre compte validé</li>
                    <li>Vous pourrez alors vous connecter à votre espace gestionnaire</li>
                    <li>Vous aurez accès à toutes les fonctionnalités de gestion</li>
                </ul>
            </div>
            <a href="../index.php" class="button">Retour à l'accueil</a>
        <?php else: ?>
            <h1>Échec du Paiement</h1>
            <div class="message">
                Nous n'avons pas pu confirmer votre paiement. <br>
                Si vous pensez qu'il s'agit d'une erreur, veuillez nous contacter ou réessayer.
            </div>
            <a href="inscription_gestion.php" class="button">Retour à l'inscription</a>
        <?php endif; ?>

        <div class="contact-info">
            <p>Pour toute question, contactez-nous :</p>
            <p>Email : <a href="mailto:contactbenintourisme@gmail.com">contactbenintourisme@gmail.com</a></p>
            <p>Téléphone : <a href="tel:+22990077139">+229 90 07 71 39</a> / <a href="tel:+22964780067">+229 64 78 00 67</a></p>
        </div>
    </div>
</body>
</html>