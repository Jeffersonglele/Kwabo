<?php
// Démarrer la session
session_start();

// Activer l'affichage des erreurs pour le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Démarrer la mise en tampon de sortie
ob_start();

// Journalisation des données de débogage
function debug_log($message) {
    $log_file = __DIR__ . '/debug_connexion.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}

// Effacer l'ancien fichier de log au premier accès
if (!file_exists(__DIR__ . '/debug_connexion.log')) {
    file_put_contents(__DIR__ . '/debug_connexion.log', "");
}

include_once("../config/database.php");
debug_log("=== NOUVELLE TENTATIVE DE CONNEXION ===");
debug_log("Méthode HTTP: " . $_SERVER['REQUEST_METHOD']);

// Vérifier si une erreur de session existe
if (isset($_SESSION['error_message'])) {
    echo "<div style='color:red;padding:10px;margin:10px;border:1px solid red;'>Erreur: " . htmlspecialchars($_SESSION['error_message']) . "</div>";
    unset($_SESSION['error_message']);
}
$page_title = "Connexion - Bénin Tourisme";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    debug_log("Tentative de connexion avec les données POST: " . print_r($_POST, true));
    
    // Vérification du reCAPTCHA
    if (isset($_POST['g-recaptcha-response'])) {
        $recaptcha_secret = "6LeO-WorAAAAANUJja0VZN9XR_DTA-WEdzUe9tNW"; // Remplacez par votre clé secrète
        $recaptcha_response = $_POST['g-recaptcha-response'];
        
        $recaptcha_url = "https://www.google.com/recaptcha/api/siteverify";
        $recaptcha_data = [
            'secret' => $recaptcha_secret,
            'response' => $recaptcha_response,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ];
        
        $recaptcha_options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($recaptcha_data)
            ]
        ];
        
        $recaptcha_context = stream_context_create($recaptcha_options);
        $recaptcha_result = file_get_contents($recaptcha_url, false, $recaptcha_context);
        $recaptcha_json = json_decode($recaptcha_result);
        
        if (!$recaptcha_json->success) {
            $error = "Veuillez vérifier que vous n'êtes pas un robot";
            debug_log("ERREUR reCAPTCHA: " . print_r($recaptcha_json, true));
        }
    } else {
        $error = "Veuillez compléter le reCAPTCHA";
        debug_log("ERREUR: reCAPTCHA non soumis");
    }
    
    // Si pas d'erreur avec le reCAPTCHA, continuer avec la vérification des identifiants
    if (!isset($error)) {
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password']; // Get password from POST data
        debug_log("Email traité: $email");

        if (!empty($email) && !empty($password)) {
            try {
                debug_log("Recherche de l'utilisateur avec l'email: $email");
                $stmt = $pdo->prepare("SELECT * FROM gestionnaires WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                
                debug_log("Résultat de la requête: " . print_r($user, true));

                if ($user) {
                    debug_log("Utilisateur trouvé dans la base de données");
                    debug_log("Vérification du mot de passe...");
                    
                    if (password_verify($password, $user['mot_de_passe'])) {
                        debug_log("Mot de passe correct");
                        
                        if ($user['statut_paiement'] !== 'valide') {
                            debug_log("ERREUR: Compte non validé. Statut: " . $user['statut_paiement']);
                            $error = "Votre compte n'a pas encore été validé par l'administrateur";
                            $user = false; // Empêcher la connexion
                        } else {
                            debug_log("Compte validé avec succès, connexion en cours...");
                            // Continuer avec la connexion
                        }
                    } else {
                        debug_log("ERREUR: Mot de passe incorrect");
                        $error = "Email ou mot de passe incorrect";
                        $user = false; // Empêcher la connexion
                    }
                    $_SESSION['gestionnaire_id'] = $user['id'];
                    $_SESSION['gestionnaire_nom'] = $user['nom'];
                    $_SESSION['gestionnaire_email'] = $user['email'];
                    $_SESSION['gestionnaire_type'] = $user['type_compte'];
                    
                    // Message de débogage
                    error_log("Session après connexion: " . print_r($_SESSION, true));
                    
                    // Vérifier si la session est bien démarrée
                    if (session_status() === PHP_SESSION_ACTIVE) {
                        error_log("Session active avant redirection");
                        $redirect_url = isset($_GET['redirect']) ? $_GET['redirect'] : 'tableau_bord.php';
                        error_log("Redirection vers: " . $redirect_url);
                        header("Location: " . $redirect_url);
                        exit();
                    } else {
                        error_log("ERREUR: Session non active");
                        header("Location: connexion.php?error=session_not_started");
                        exit();
                    }
                } else {
                    $error = "Email ou mot de passe incorrect";
                }
            } catch (PDOException $e) {
                $error = "Une erreur est survenue";
            }
        } else {
            $error = "Tous les champs sont requis";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="icon" type="image/svg+xml" href="../assets/favicon/favicon.svg">
    <link rel="icon" type="image/png" sizes="96x96" href="../assets/favicon/favicon-96x96.png">
    <link rel="shortcut icon" href="../assets/favicon/favicon.ico">
    <script src="https://cdn.tailwindcss.com"></script>
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
            background: #fafafa; /* Fond clair */
            position: relative;
            overflow-x: hidden;
            padding: 20px;
        }

        body::before {
            /* Fond dégradé commenté pour un fond clair */
            /*
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, white);
            opacity: 0.95;
            z-index: -1;
            */
        }

        .container {
            display: flex;
            align-items: center;
            gap: 50px; /* Espace entre le téléphone et le formulaire */
            width: 100%;
            max-width: 1200px;
            margin: auto;
            padding: 20px;
            position: relative;
            z-index: 1;
            min-height: calc(100vh - 40px);
            justify-content: center; /* Centrer le contenu du conteneur */
        }

        .phone-mockup {
            position: relative;
            width: 250px;
            height: 500px;
            background-size: contain;
            flex-shrink: 0; /* Empêche le mockup de rétrécir */
        }

        .carousel {
            position: absolute;
            top: 40px;
            left: 17px;
            width: 215px;
            height: 420px;
            overflow: hidden;
            border-radius: 28px; /* Ajuster le rayon pour le cadre du téléphone */
        }

        .carousel img {
            width: 100%;
            height: 100%;
            object-fit: cover; /* Couvre la zone sans déformer */
            position: absolute;
            opacity: 0;
            transition: opacity 1s ease-in-out;
        }

        .carousel img.active {
            opacity: 1;
        }

        /* Styles de la section de connexion/inscription */
        .login-section {
            flex: 1; /* Prend l'espace restant */
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-width: 300px; /* Minimum width */
            max-width: 600px; /* Maximum width */
            width: 100%; /* Prend toute la largeur disponible */
            animation: fadeIn 1s ease-out; /* Animation d'apparition */
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

        /* Styles du formulaire de connexion/inscription */
        .login-box {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            width: 100%;
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
            width: 80px;
            display: block;
            margin: 0 auto 1rem auto;
        }

        .logo h1 {
            font-size: 32px;
            color: #1a237e;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            color: #495057;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #1a237e;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(26, 35, 126, 0.1);
        }

        .login-button {
            width: 100%;
            padding: 12px;
            background: #1a237e;
            border: none;
            border-radius: 10px;
            color: #fff;
            font-weight: 500;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 10px;
        }

         .login-button:hover {
            background: #0d47a1;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 71, 161, 0.3);
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

        .facebook-login {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1a237e;
            font-weight: 500;
            font-size: 14px;
            text-decoration: none;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .facebook-login:hover {
            color: #0d47a1;
        }

        .facebook-login i {
            margin-right: 8px;
            color: #1a237e;
        }

        .forgot-password {
            text-align: center;
            font-size: 13px;
            color: #6c757d;
            text-decoration: none;
            display: block;
            margin-top: 15px;
            transition: all 0.3s ease;
        }

        .forgot-password:hover {
            color: #1a237e;
        }

        .signup-box {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 20px;
            text-align: center;
            font-size: 14px;
            color: #495057;
            margin-top: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }

        .signup-box a {
            color: #1a237e;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .signup-box a:hover {
            color: #0d47a1;
        }

        .error-message {
            color: #dc3545;
            font-size: 14px;
            text-align: center;
            margin-bottom: 15px;
            padding: 10px;
            background: rgba(220, 53, 69, 0.1);
            border-radius: 8px;
        }

        /* Media query pour les petits écrans */
        @media (max-width: 768px) {
            .container {
                flex-direction: column; /* Empiler en colonne sur mobile */
                gap: 30px; /* Réduire l'espace */
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
                padding: 0 10px; /* Ajouter un peu de padding horizontal */
            }

            .login-box {
                padding: 20px;
            }

            .logo h1 {
                font-size: 24px;
            }

            /* Adjust social login and forgot password for mobile if needed */
            .facebook-login, .forgot-password {
                font-size: 13px;
            }
             .signup-box {
                padding: 15px;
                font-size: 13px;
            }
        }

    </style>
</head>
<body>

    <div class="container">
        <!-- Mockup de téléphone avec carrousel -->
        <div class="phone-mockup">
            <div class="carousel">
                <img src="../assets/images/Wallpaper1.png" class="active" alt="Image 1">
                <img src="../assets/images/Wallpaper2.png" alt="Image 2">
                <img src="../assets/images/Wallpaper3.png" alt="Image 3">
            </div>
        </div>

        <!-- Section de connexion -->
        <section class="login-section">
            <div class="login-box">
                <div class="logo">
                    <img src="../assets/images/KWA.png" alt="Logo BT">
                </div>
                <?php if (isset($error)): ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="POST" action="" class="login-form">
                    <div class="form-group">
                        <input type="email" name="email" placeholder="Email" required>
                    </div>
                    <div class="form-group">
                        <input type="password" name="password" placeholder="Mot de passe" required>
                    </div>
                    <div class="form-group">
                        <div class="g-recaptcha" data-sitekey=" 6LeO-WorAAAAAIU0eMMRnIsDQtpwC1NL6C_Aafrj"></div>
                    </div>
                    <button type="submit" class="login-button">Se connecter</button>
                </form> 

                <!-- Séparateur -->
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-gray-500">Ou</span>
                </div>
            </div>
                <p class="forgot-password">Vous n'avez pas de compte ?</p> <br>
                <a href="inscription_gestion.php" class="facebook-login"> Inscrivez-vous
                    <i class="fab fa-facebook-square"></i> 
                </a><br>
                <a href="#" class="forgot-password">Mot de passe oublié ?</a>
            </div>
        </section>
    </div>

    <script>
        const images = document.querySelectorAll('.carousel img');
        let index = 0;

        // Active la première image au chargement
        images[index].classList.add('active');

        setInterval(() => {
          // Désactive l'image actuelle
          images[index].classList.remove('active');

          // Passe à l'image suivante
          index = (index + 1) % images.length;

          // Active la nouvelle image
          images[index].classList.add('active');
        }, 3000); // Change d'image toutes les 3 secondes
    </script>

    <script src="https://kit.fontawesome.com/your-font-awesome-kit.js"></script>
    <!-- Script Google reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</body>
</html>