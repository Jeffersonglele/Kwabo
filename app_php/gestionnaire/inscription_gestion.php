<?php
session_start();

// Inclure les fichiers de configuration
include_once("../config/database.php");
require_once('../vendor/autoload.php');

// Configuration de FedaPay
\FedaPay\FedaPay::setApiKey('sk_live_nYOF4BVxWsJgA_RQcx68xq88');
\FedaPay\FedaPay::setEnvironment('live');

$page_title = "Inscription - Bénin Tourisme";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Gérer l'annulation de l'inscription si action='annuler_inscription'
    if (isset($_POST['action']) && $_POST['action'] == 'annuler_inscription') {
        // Supprime les données de session si elles existent
        if (isset($_SESSION['inscription_data'])) {
            unset($_SESSION['inscription_data']);
        }
        // Supprime toute donnée temporaire en base de données
        try {
            $pdo->exec("DELETE FROM gestionnaires WHERE statut_paiement = 'en_attente' AND email = '" . $pdo->quote($_POST['email']) . "'");
        } catch (Exception $e) {
            error_log("Erreur lors de l'annulation de l'inscription : " . $e->getMessage());
        }
        exit();
    }

    $nom = $_POST['nom'] ?? '';
    $email = $_POST['email'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $type_structure = $_POST['type_compte'] ?? '';
    $terms = isset($_POST['terms']) ? true : false;

    $errors = [];

    // Validation des données
    if (empty($nom)) {
        $errors[] = "Le nom est requis";
    }

    if (empty($email)) {
        $errors[] = "L'email est requis";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format d'email invalide";
    }

    if (empty($telephone)) {
        $errors[] = "Le numéro de téléphone est requis";
    } elseif (!preg_match('/^229[0-9]{8}$/', $telephone)) {
        $errors[] = "Format de numéro de téléphone invalide. Utilisez le format: 64000001";
    }

    if (empty($password)) {
        $errors[] = "Le mot de passe est requis";
    } elseif (strlen($password) < 8) {
        $errors[] = "Le mot de passe doit contenir au moins 8 caractères";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Les mots de passe ne correspondent pas";
    }

    if (empty($type_structure)) {
        $errors[] = "Le type de structure est requis";
    }

    if (!$terms) {
        $errors[] = "Vous devez accepter les conditions d'utilisation";
    }

    // Vérification si l'email existe déjà
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM gestionnaires WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = "Cet email est déjà utilisé";
            }
        } catch (PDOException $e) {
            $errors[] = "Une erreur est survenue lors de la vérification de l'email";
        }
    }

    // Si pas d'erreurs, procéder au paiement
    if (empty($errors)) {
        // Stocker les données en session pour utilisation après paiement
        $_SESSION['pending_inscription'] = [
            'nom' => $nom,
            'email' => $email,
            'telephone' => $telephone,
            'password' => $password,
            'type_compte' => $type_structure,
            'timestamp' => time()
        ];

        try {
            // Fonction pour déterminer le montant selon le type de structure
            function getMontantInscription($type_structure) {
                switch ($type_structure) {
                    case 'hotel':
                        return 5000;
                    case 'destination':
                        return 100;
                    case 'circuit':
                        return 3000;
                    case 'evenement':
                        return 5000;
                    default:
                        return 5000;
                }
            }

            $montant = getMontantInscription($type_structure);
            $_SESSION['pending_inscription']['montant'] = $montant;

            try { 
                // Créer la transaction FedaPay
                $transaction = \FedaPay\Transaction::create([
                    'amount' => $montant,
                    'currency' => ['iso' => 'XOF'],
                    'description' => 'Inscription ' . $type_structure,
                    'callback_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/ProjetBinome/gestionnaire/success.php',
                    'cancel_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/ProjetBinome/gestionnaire/inscription_gestion.php',
                    'customer' => [
                        'firstname' => $nom,
                        'lastname' => '',
                        'email' => $email,
                        'phone_number' => $telephone
                    ],
                    'metadata' => [
                        'type_inscription' => $type_structure,
                        'source' => 'inscription_gestionnaire'
                    ]
                ]);

                // Générer le token de paiement
                $token = $transaction->generateToken();

                // Envoyer la réponse au client
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'token' => $token->token
                ]);
                exit;
            } catch (Exception $e) {
                error_log("Erreur FedaPay: " . $e->getMessage());
                $errors[] = "Erreur lors de la création du paiement. Veuillez réessayer.";
                
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'errors' => $errors
                ]);
                exit;
            }
            // Redirection vers la page de succès
            $_SESSION['success'] = "Votre demande d'inscription a été soumise avec succès. Vous recevrez un email une fois votre compte validé par l'administrateur.";
            header('Location: ../pages/gestion.php');
            exit();
        } catch (Exception $e) {
            $errors[] = "Une erreur est survenue lors de l'inscription";
            error_log("Erreur générale : " . $e->getMessage());
        }
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
            position: relative;
            overflow-x: hidden;
            padding: 20px;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, white);
            opacity: 0.95;
            z-index: -1;
         
        }

        .container {
            display: flex;
            align-items: center;
            gap: 50px;
            width: 100%;
            max-width: 1200px;
            margin: auto;
            padding: 20px;
            position: relative;
            z-index: 1;
            min-height: calc(100vh - 40px);
            justify-content: center;
        }

        .phone-mockup {
            position: relative;
            width: 250px;
            height: 500px;
            background-size: contain;
            flex-shrink: 0;
        }

        .carousel {
            position: absolute;
            top: 40px;
            left: 17px;
            width: 215px;
            height: 420px;
            overflow: hidden;
            border-radius: 28px;
        }

        .carousel img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            opacity: 0;
            transition: opacity 1s ease-in-out;
        }

        .carousel img.active {
            opacity: 1;
        }

        .login-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-width: 300px;
            max-width: 600px;
            width: 100%;
            animation: fadeIn 1s ease-out;
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

        .login-box {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            width: 100%;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .login-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo h1 {
            font-size: 32px;
            color: #1a237e;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .form-row .form-group {
            flex: 1;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group input,
        .form-group select,
        .form-group input[type="file"] {
            width: 100%;
            padding: 14px 18px;
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            color: #495057;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-group input[type="file"] {
            padding: 10px;
            background: #fff;
            cursor: pointer;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #1a237e;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(26, 35, 126, 0.1);
        }

        .checkbox-container {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            color: #495057;
        }

        .checkbox-container input[type="checkbox"] {
            width: auto;
            margin: 0;
        }

        .login-button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #1a237e, #0d47a1);
            border: none;
            border-radius: 12px;
            color: #fff;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 20px;
        }

        .login-button:hover {
            background: linear-gradient(135deg, #0d47a1, #1a237e);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(13, 71, 161, 0.3);
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 20px 0;
            color: #6c757d;
            font-size: 13px;
        }

        .divider::before,
        .divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: #dee2e6;
        }

        .divider::before { margin-right: 18px; }
        .divider::after { margin-left: 18px; }

        .social-login {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 20px;
        }

        .facebook-login {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1a237e;
            font-weight: 500;
            font-size: 14px;
            text-decoration: none;
            padding: 10px;
            border: 1px solid #1a237e;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .facebook-login:hover {
            background: #1a237e;
            color: white;
        }

        .forgot-password {
            text-align: center;
            font-size: 13px;
            color: #6c757d;
            text-decoration: none;
            display: block;
            transition: all 0.3s ease;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #495057;
        }

        .login-link a {
            color: #1a237e;
            text-decoration: none;
            font-weight: 500;
        }

        .login-link a:hover {
            color: #0d47a1;
        }

        .error-message {
            color: #dc3545;
            font-size: 14px;
            text-align: center;
            margin-bottom: 20px;
            padding: 12px;
            background: rgba(220, 53, 69, 0.1);
            border-radius: 12px;
            width: 100%;
            border: 1px solid rgba(220, 53, 69, 0.2);
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                gap: 30px;
                padding: 10px;
                min-height: auto;
            }

            .phone-mockup {
                width: 200px;
                height: 400px;
            }

            .carousel {
                top: 32px;
                left: 14px;
                width: 172px;
                height: 336px;
                border-radius: 22px;
            }

            .login-section {
                min-width: 100%;
                max-width: 100%;
                padding: 0 10px;
            }

            .login-box {
                padding: 20px;
            }

            .logo h1 {
                font-size: 24px;
            }

            .form-row {
                flex-direction: column;
                gap: 0;
            }
        }

        .steps-container {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }

        .step {
            display: flex;
            align-items: center;
            margin: 0 15px;
        }

        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-weight: 600;
            color: #495057;
        }

        .step.active .step-number {
            background: #1a237e;
            color: white;
        }

        .step.completed .step-number {
            background: #28a745;
            color: white;
        }

        .step-title {
            font-size: 14px;
            color: #495057;
        }

        .step.active .step-title {
            color: #1a237e;
            font-weight: 600;
        }

        .form-step {
            display: none;
        }

        .form-step.active {
            display: block;
        }

        .navigation-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .nav-button {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .prev-button {
            background: #e9ecef;
            color: #495057;
        }

        .next-button {
            background: #1a237e;
            color: white;
        }

        .nav-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .payment-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .payment-summary {
            margin-bottom: 20px;
        }

        .payment-summary h3 {
            color: #1a237e;
            margin-bottom: 10px;
        }

        .payment-amount {
            font-size: 24px;
            font-weight: 600;
            color: #28a745;
        }

        /* Nouveaux styles pour le processus de paiement */
        .payment-status {
            display: none;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
            text-align: center;
        }

        .payment-status.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .payment-status.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .payment-status.processing {
            background-color: #e2e3e5;
            color: #383d41;
            border: 1px solid #d6d8db;
        }

        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #1a237e;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .payment-details {
            position: relative;
        }

        .payment-overlay {
            display: none;
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .payment-overlay.active {
            display: flex;
        }

        .payment-methods {
            display: flex;
            gap: 15px;
            margin: 20px 0;
            justify-content: center;
        }

        .payment-method {
            padding: 10px 20px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .payment-method:hover {
            border-color: #1a237e;
            background-color: #f8f9fa;
        }

        .payment-method.active {
            border-color: #1a237e;
            background-color: #e8eaf6;
        }

        .payment-method img {
            height: 30px;
            width: auto;
        }

        /* Styles pour la modal de confidentialité */
        .privacy-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .privacy-modal.active {
            display: flex;
        }

        .privacy-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            position: relative;
        }

        .privacy-content h2 {
            color: #1a237e;
            margin-bottom: 20px;
            font-size: 24px;
        }

        .privacy-content p {
            margin-bottom: 15px;
            line-height: 1.6;
            color: #333;
        }

        .privacy-content ul {
            margin-bottom: 15px;
            padding-left: 20px;
        }

        .privacy-content li {
            margin-bottom: 10px;
            line-height: 1.6;
        }

        .close-modal {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }

        .privacy-link {
            color: #1a237e;
            text-decoration: underline;
            cursor: pointer;
        }

        .privacy-link:hover {
            color: #0d47a1;
        }

        /* Styles pour la modale de paiement */
        .payment-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .payment-modal.active {
            display: flex;
        }

        .payment-modal-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .payment-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .payment-modal-header h2 {
            color: #1a237e;
            margin: 0;
            font-size: 24px;
        }

        .close-payment-modal {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
            padding: 5px;
        }

        .close-payment-modal:hover {
            color: #1a237e;
        }

        #fedapay-button-container {
            min-height: 400px;
            width: 100%;
        }

        /* Ajout des styles pour les champs en erreur */
        .error {
            border-color: #dc3545 !important;
            background-color: #fff8f8 !important;
        }

        .error-message {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .error-message p {
            margin: 5px 0;
        }

        /* Animation pour les messages d'erreur */
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .error {
            animation: shake 0.5s ease-in-out;
        }
    </style>
</head>
<body>
    <!-- Modal de confidentialité -->
    <div class="privacy-modal" id="privacyModal">
        <div class="privacy-content">
            <button class="close-modal" onclick="closePrivacyModal()">&times;</button>
            <h2>Politique de Confidentialité</h2>
            
            <p>En utilisant notre plateforme, vous acceptez les conditions suivantes concernant la protection de vos données personnelles :</p>
            
            <h3>1. Collecte des Données</h3>
            <p>Nous collectons les informations suivantes :</p>
            <ul>
                <li>Nom et prénom</li>
                <li>Adresse e-mail</li>
                <li>Numéro de téléphone</li>
                <li>Type de compte</li>
            </ul>

            <h3>2. Utilisation des Données</h3>
            <p>Vos données sont utilisées pour :</p>
            <ul>
                <li>Gérer votre compte</li>
                <li>Traiter vos paiements</li>
                <li>Communiquer avec vous</li>
                <li>Améliorer nos services</li>
            </ul>

            <h3>3. Protection des Données</h3>
            <p>Nous nous engageons à :</p>
            <ul>
                <li>Protéger vos données contre tout accès non autorisé</li>
                <li>Ne jamais vendre vos données à des tiers</li>
                <li>Utiliser des protocoles de sécurité avancés</li>
                <li>Respecter les normes RGPD</li>
            </ul>

            <h3>4. Vos Droits</h3>
            <p>Vous avez le droit de :</p>
            <ul>
                <li>Accéder à vos données</li>
                <li>Modifier vos informations</li>
                <li>Supprimer votre compte</li>
                <li>Retirer votre consentement</li>
            </ul>

            <h3>5. Cookies</h3>
            <p>Notre site utilise des cookies pour améliorer votre expérience. Vous pouvez les désactiver dans les paramètres de votre navigateur.</p>

            <h3>6. Contact</h3>
            <p>Pour toute question concernant vos données, contactez-nous à :</p>
            <p><a href="mailto:contactbenintourisme@gmail.com">Email: contactbenintourisme@gmail.com</a></p>
            <p><a href="tel:+22990077139">Téléphone: +229 90 07 71 39</a> <br> <a href="tel:+22964780067">Téléphone: +229 64 78 00 67</a></p>
        </div>
    </div>

    <!-- Suppression de la modale de paiement -->
    <div class="container">
        <div class="phone-mockup">
            <div class="carousel">
                <img src="../assets/images/Wallpaper1.png" class="active" alt="Image 1">
                <img src="../assets/images/Wallpaper2.png" alt="Image 2">
                <img src="../assets/images/Wallpaper3.png" alt="Image 3">
            </div>
        </div>

        <section class="login-section">
            <div class="signup-form">
                <div class="login-box">
                    <header class="logo">
                        <h1>Inscription</h1>
                    </header>

                    <div class="steps-container">
                        <div class="step active" data-step="1">
                            <div class="step-number">1</div>
                            <div class="step-title">Informations</div>
                        </div>
                        <div class="step" data-step="2">
                            <div class="step-number">2</div>
                            <div class="step-title">Paiement</div>
                        </div>
                    </div>

                    <form method="POST" action="" class="registration-form" enctype="multipart/form-data" id="registrationForm">
                        <?php if (!empty($errors)): ?>
                            <div class="error-message">
                                <?php foreach ($errors as $error): ?>
                                    <p><?php echo htmlspecialchars($error); ?></p>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Étape 1: Informations personnelles -->
                        <div class="form-step active" data-step="1">
                            <div class="form-group">
                                <input type="text" name="nom" placeholder="Nom officiel" required>
                            </div>
                            <div class="form-group">
                                <input type="email" name="email" placeholder="Email professionnel" required>
                            </div>
                            <div class="form-group">
                                <input type="tel" name="telephone" placeholder="Contact professionnel (22964000001)" required>
                            </div>
                            <div class="form-group">
                                <input type="password" name="password" placeholder="Mot de passe" required>
                            </div>
                            <div class="form-group">
                                <input type="password" name="confirm_password" placeholder="Confirmer le mot de passe" required>
                            </div>
                            <div class="form-group">
                                <select name="type_compte" required>
                                    <option value="">Sélectionnez votre type de compte</option>
                                    <option value="hotel">Hôtel</option>
                                    <option value="destination">Destination</option>
                                    <option value="circuit">Circuit</option>
                                    <option value="evenement">Evènements</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="checkbox-container">
                                    <input type="checkbox" name="terms" required>
                                    J'accepte les <span class="privacy-link" onclick="openPrivacyModal()">conditions d'utilisation et la politique de confidentialité</span>
                                </label>
                            </div>
                            <!-- Intégration reCAPTCHA -->
                            <div class="g-recaptcha" data-sitekey="6LeO-WorAAAAAIU0eMMRnIsDQtpwC1NL6C_Aafrj"></div>
                            <div class="navigation-buttons">
                                <button type="button" class="nav-button next-button" onclick="nextStep(1)">Suivant</button>
                            </div>
                        </div>

                        <!-- Étape 2: Paiement -->
                        <div class="form-step" data-step="2">
                            <div class="payment-details">
                                <div class="payment-summary">
                                    <h3>Récapitulatif de l'inscription</h3>
                                    <p>Type de structure: <span id="accountType"></span></p>
                                    <p class="payment-amount">Montant: 50,000 FCFA</p>
                                </div>
                                <div id="recaptcha-error" class="error-message" style="display: none; margin-top: 10px;">
                                    <p>Veuillez valider que vous n'êtes pas un robot</p>
                                </div>
                                <button type="button" id="pay-button" class="login-button"
                                 class="g-recaptcha login-button" 
                                        data-sitekey="6LeO-WorAAAAANUJja0VZN9XR_DTA-WEdzUe9tNW"
                                        data-callback='onSubmit'
                                        data-action='submit'>>Procéder au paiement</button>
                                <div id="redirect-container" style="display: none; margin-top: 20px; text-align: center;">
                                    <p style="color: #28a745; margin-bottom: 10px;">Paiement effectué avec succès !</p>
                                    <button type="button" id="redirect-button" class="login-button" style="background: #28a745;">Continuer vers mon compte</button>
                                </div>
                            </div>
                            <div class="navigation-buttons">
                                <button type="button" class="nav-button prev-button" onclick="prevStep(2)">Précédent</button>
                            </div>
                        </div>
                    </form>
                    <p class="login-link">Déjà inscrit ? <a href="connexion.php">Se connecter</a></p>
                </div>
            </div>
        </section>
    </div>

    <script src="https://cdn.fedapay.com/checkout.js?v=1.1.7"></script>
    <script>
        // Fonction pour obtenir le montant selon le type de structure
        function getMontantInscription(typeStructure) {
            switch (typeStructure) {
                case 'hotel':
                    return 5000;
                case 'destination':
                    return 100;
                case 'circuit':
                    return 3000;
                case 'evenement':
                    return 5000;
                default:
                    return 5000;
            }
        }

        // Mettre à jour le montant affiché quand le type de structure change
        document.querySelector('select[name="type_compte"]').addEventListener('change', function() {
            const typeStructure = this.value;
            const montant = getMontantInscription(typeStructure);
            document.querySelector('.payment-amount').textContent = `Montant: ${montant.toLocaleString()} FCFA`;
            document.getElementById('accountType').textContent = this.options[this.selectedIndex].text;
        });

        // Gestion du bouton de paiement
        document.getElementById('pay-button').addEventListener('click', function() {
            if (validateStep(2)) {
                const form = document.getElementById('registrationForm');
                const formData = new FormData(form);
                const typeStructure = formData.get('type_compte');
                const montant = getMontantInscription(typeStructure);

                // Initialiser FedaPay
                FedaPay.init('#pay-button', {
                    public_key: 'pk_live_18wV1aZkcnVUdq5stp66NMGg',
                    transaction: {
                        amount: montant,
                        description: 'Inscription - Bénin Tourisme',
                        currency: {
                            iso: 'XOF'
                        },
                        callback_url: window.location.origin + '/ProjetBinome/gestionnaire/success.php',
                        cancel_url: window.location.href
                    },
                    customer: {
                        firstname: formData.get('prenom'),
                        lastname: formData.get('nom'),
                        email: formData.get('email'),
                        phone_number: {
                            number: formData.get('telephone').replace('229', ''),
                            country: 'BJ'
                        }
                    },
                    onComplete: function(response) {
                        console.log("Paiement réussi :", response);
                        // Stocker les données du formulaire en session via AJAX
                        fetch(window.location.href, {
                            method: 'POST',
                            body: formData
                        }).then(() => {
                            // Envoyer un email à l'administrateur
                            fetch('send_admin_notification.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    email: formData.get('email'),
                                    nom: formData.get('nom'),
                                    prenom: formData.get('prenom'),
                                    type_structure: formData.get('type_compte')
                                })
                            }).then(() => {
                                window.location.href = "success.php";
                            }).catch(error => {
                                console.error('Erreur lors de l\'envoi de la notification:', error);
                                window.location.href = "inscription_gestion.php";
                            });
                        }).catch(error => {
                            console.error('Erreur lors de la soumission du formulaire:', error);
                            window.location.href = "inscription_gestion.php";
                        });
                    },
                    onDismiss: function() {
                        // Annuler l'inscription en envoyant une requête au serveur
                        fetch(window.location.href, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'action=annuler_inscription&email=' + encodeURIComponent(formData.get('email'))
                        }).then(response => {
                            if (response.ok) {
                                alert("L'inscription a été annulée. Vous pouvez réessayer si vous le souhaitez.");
                                window.location.href = "inscription_gestion.php";
                            } else {
                                throw new Error('Erreur lors de l\'annulation');
                            }
                        }).catch(error => {
                            console.error('Erreur lors de l\'annulation de l\'inscription:', error);
                            alert("Une erreur est survenue lors de l'annulation. Vous pouvez réessayer si vous le souhaitez.");
                            window.location.href = "inscription_gestion.php";
                        });
                    },
                    onError: function(error) {
                        console.error('Erreur de paiement:', error);
                        alert('Une erreur est survenue lors du paiement. Veuillez réessayer.');
                        window.location.href = "inscription_gestion.php";
                    }
                });
            }
        });
        
        // Gestionnaire pour le bouton de redirection
        document.getElementById('redirect-button').addEventListener('click', function() {
            window.location.href = window.location.origin + '/ProjetBinome/gestionnaire/success.php';
        });

        function nextStep(currentStep) {
            if (validateStep(currentStep)) {
                showStep(currentStep + 1);
            }
        }

        // Gestion des étapes
        function showStep(stepNumber) {
            document.querySelectorAll('.form-step').forEach(step => {
                step.classList.remove('active');
            });
            document.querySelector(`.form-step[data-step="${stepNumber}"]`).classList.add('active');

            document.querySelectorAll('.step').forEach(step => {
                step.classList.remove('active');
                if (parseInt(step.dataset.step) < stepNumber) {
                    step.classList.add('completed');
                }
            });
            document.querySelector(`.step[data-step="${stepNumber}"]`).classList.add('active');
        }

        function prevStep(currentStep) {
            showStep(currentStep - 1);
        }

        function validateStep(step) {
            const form = document.getElementById('registrationForm');
            const inputs = form.querySelectorAll(`.form-step[data-step="${step}"] input, .form-step[data-step="${step}"] select`);
            let isValid = true;
            let errorMessage = '';

            inputs.forEach(input => {
                if (input.hasAttribute('required') && !input.value.trim()) {
                    isValid = false;
                    input.classList.add('error');
                    errorMessage += `Le champ ${input.placeholder || input.name} est requis.\n`;
                } else {
                    input.classList.remove('error');
                }

                // Validation spécifique pour l'email
                if (input.type === 'email' && input.value) {
                    if (!isValidEmail(input.value)) {
                        isValid = false;
                        input.classList.add('error');
                        errorMessage += "Format d'email invalide.\n";
                    }
                }

                // Validation spécifique pour le téléphone
                if (input.name === 'telephone' && input.value) {
                    if (!isValidPhone(input.value)) {
                        isValid = false;
                        input.classList.add('error');
                        errorMessage += "Format de numéro de téléphone invalide. Utilisez le format: 22964000001\n";
                    }
                }

                // Validation spécifique pour le mot de passe
                if (input.name === 'password' && input.value) {
                    if (input.value.length < 8) {
                        isValid = false;
                        input.classList.add('error');
                        errorMessage += "Le mot de passe doit contenir au moins 8 caractères.\n";
                    }
                }

                // Validation de la confirmation du mot de passe
                if (input.name === 'confirm_password' && input.value) {
                    const password = form.querySelector('input[name="password"]').value;
                    if (input.value !== password) {
                        isValid = false;
                        input.classList.add('error');
                        errorMessage += "Les mots de passe ne correspondent pas.\n";
                    }
                }
            });

            // Vérification de la case des conditions
            const termsCheckbox = form.querySelector('input[name="terms"]');
            if (!termsCheckbox.checked) {
                isValid = false;
                termsCheckbox.classList.add('error');
                errorMessage += "Vous devez accepter les conditions d'utilisation et la politique de confidentialité.\n";
            }

            // Afficher le message d'erreur si nécessaire
            if (!isValid) {
                const errorDiv = document.querySelector('.error-message') || document.createElement('div');
                errorDiv.className = 'error-message';
                errorDiv.innerHTML = errorMessage.split('\n').filter(msg => msg).map(msg => `<p>${msg}</p>`).join('');
                
                if (!document.querySelector('.error-message')) {
                    form.insertBefore(errorDiv, form.firstChild);
                }

                // Animation de secousse pour les champs en erreur
                document.querySelectorAll('.error').forEach(field => {
                    field.style.animation = 'none';
                    field.offsetHeight; // Force reflow
                    field.style.animation = 'shake 0.5s ease-in-out';
                });
            }

            return isValid;
        }

        // Ajouter les écouteurs d'événements pour la validation en temps réel
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registrationForm');
            const inputs = form.querySelectorAll('input, select');

            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    this.classList.remove('error');
                    const errorMessage = document.querySelector('.error-message');
                    if (errorMessage) {
                        errorMessage.remove();
                    }
                });
            });

            // Empêcher la soumission du formulaire si la validation échoue
            form.addEventListener('submit', function(e) {
                if (!validateStep(1)) {
                    e.preventDefault();
                }
            });

            // Validation lors du changement d'étape
            document.querySelector('.next-button').addEventListener('click', function(e) {
                e.preventDefault();
                if (validateStep(1)) {
                    showStep(2);
                }
            });
        });

        // Carousel d'images
        const images = document.querySelectorAll('.carousel img');
        let index = 0;
        images[index].classList.add('active');

        setInterval(() => {
            images[index].classList.remove('active');
            index = (index + 1) % images.length;
            images[index].classList.add('active');
        }, 3000);

        // Fonctions pour la modal de confidentialité
        function openPrivacyModal() {
            document.getElementById('privacyModal').classList.add('active');
        }

        function closePrivacyModal() {
            document.getElementById('privacyModal').classList.remove('active');
        }

        // Fermer la modal en cliquant en dehors
        document.getElementById('privacyModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closePrivacyModal();
            }
        });

        // Empêcher la fermeture en cliquant sur le contenu
        document.querySelector('.privacy-content').addEventListener('click', function(e) {
            e.stopPropagation();
        });

        // Fonction pour valider le formulaire
        function validateForm() {
            const form = document.getElementById('registrationForm');
            const inputs = form.querySelectorAll('input[required], select[required]');
            const termsCheckbox = form.querySelector('input[name="terms"]');
            let isValid = true;
            let errorMessage = '';

            // Vérifier tous les champs requis
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    input.classList.add('error');
                    errorMessage += `Le champ ${input.placeholder || input.name} est requis.\n`;
                } else {
                    input.classList.remove('error');
                }
            });

            // Vérifier le format de l'email
            const emailInput = form.querySelector('input[name="email"]');
            if (emailInput.value && !isValidEmail(emailInput.value)) {
                isValid = false;
                emailInput.classList.add('error');
                errorMessage += "Format d'email invalide.\n";
            }

            // Vérifier le format du téléphone
            const phoneInput = form.querySelector('input[name="telephone"]');
            if (phoneInput.value && !isValidPhone(phoneInput.value)) {
                isValid = false;
                phoneInput.classList.add('error');
                errorMessage += "Format de numéro de téléphone invalide. Utilisez le format: 64000001\n";
            }

            // Vérifier la longueur du mot de passe
            const passwordInput = form.querySelector('input[name="password"]');
            if (passwordInput.value && passwordInput.value.length < 8) {
                isValid = false;
                passwordInput.classList.add('error');
                errorMessage += "Le mot de passe doit contenir au moins 8 caractères.\n";
            }

            // Vérifier la correspondance des mots de passe
            const confirmPasswordInput = form.querySelector('input[name="confirm_password"]');
            if (passwordInput.value && confirmPasswordInput.value && passwordInput.value !== confirmPasswordInput.value) {
                isValid = false;
                confirmPasswordInput.classList.add('error');
                errorMessage += "Les mots de passe ne correspondent pas.\n";
            }

            // Vérifier la case des conditions
            if (!termsCheckbox.checked) {
                isValid = false;
                termsCheckbox.classList.add('error');
                errorMessage += "Vous devez accepter les conditions d'utilisation et la politique de confidentialité.\n";
            }

            // Afficher le message d'erreur si nécessaire
            if (!isValid) {
                const errorDiv = document.querySelector('.error-message') || document.createElement('div');
                errorDiv.className = 'error-message';
                errorDiv.innerHTML = errorMessage.split('\n').filter(msg => msg).map(msg => `<p>${msg}</p>`).join('');
                
                if (!document.querySelector('.error-message')) {
                    form.insertBefore(errorDiv, form.firstChild);
                }
            }

            return isValid;
        }

        // Fonction pour valider l'email
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        // Fonction pour valider le téléphone
        function isValidPhone(phone) {
            const phoneRegex = /^229[0-9]{8}$/; // fallait ajouter le 229
            return phoneRegex.test(phone);
        }

        // Ajouter les écouteurs d'événements pour la validation en temps réel
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registrationForm');
            const inputs = form.querySelectorAll('input, select');

            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    this.classList.remove('error');
                    const errorMessage = document.querySelector('.error-message');
                    if (errorMessage) {
                        errorMessage.remove();
                    }
                });
            });

            // Empêcher la soumission du formulaire si la validation échoue
            form.addEventListener('submit', function(e) {
                if (!validateForm()) {
                    e.preventDefault();
                }
            });

            // Validation lors du changement d'étape
            document.querySelector('.next-button').addEventListener('click', function(e) {
                if (!validateForm()) {
                    e.preventDefault();
                }
            });
        });

        // Ajouter le style pour l'animation de secousse
        const style = document.createElement('style');
        style.textContent = `
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                25% { transform: translateX(-5px); }
                75% { transform: translateX(5px); }
            }
            .error {
                border-color: #dc3545 !important;
                background-color: #fff8f8 !important;
            }
            .error-message {
                background-color: #f8d7da;
                border: 1px solid #f5c6cb;
                color: #721c24;
                padding: 15px;
                margin-bottom: 20px;
                border-radius: 8px;
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
