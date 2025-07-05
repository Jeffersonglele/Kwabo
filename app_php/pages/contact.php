<?php
session_start();
include_once '../config/database.php';
require '../vendor/autoload.php'; // Assurez-vous que PHPMailer est installé via Composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Initialisation variables
$success = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer et nettoyer les données
    $nom = isset($_POST['nom']) ? trim($_POST['nom']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';

    // Validation simple
    if (empty($nom)) {
        $errors[] = "Le nom est obligatoire.";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Un email valide est obligatoire.";
    }
    if (empty($message)) {
        $errors[] = "Le message ne peut pas être vide.";
    }

    if (empty($errors)) {
        try {
            // Préparer la requête
            $sql = "INSERT INTO messages_contact (nom, email, message, date_envoi) VALUES (?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            
            // Exécuter la requête avec les paramètres
            $params = [
                $nom,
                $email,
                $message
            ];
            
            $result = $stmt->execute($params);
            
            if ($result) {
                $success = true;
                
                // Configuration de PHPMailer
                $mail = new PHPMailer(true);
                try {
                    // Configuration du serveur
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com'; // Remplacez par votre serveur SMTP
                    $mail->SMTPAuth = true;
                    $mail->Username = 'contactbenintourisme@gmail.com'; // Remplacez par votre email
                    $mail->Password = 'mbrl pvvm gczs feqt'; // Remplacez par votre mot de passe
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;
                    $mail->CharSet = 'UTF-8';

                    // Destinataires
                    $mail->setFrom($email, $nom);
                    $mail->addAddress('contactbenintourisme@gmail.com', 'Bénin Tourisme');
                    $mail->addReplyTo($email, $nom);

                    // Contenu
                    $mail->isHTML(true);
                    $mail->Subject = "Nouveau message de contact - Bénin Tourisme";
                    
                   $email_message = "
<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Nouveau message de contact - Bénin Tourisme</title>
    <style type='text/css'>
        /* Base styles */
        body, html {
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f5f5f5;
        }
        
        /* Email container */
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        /* Header */
        .header {
            background: #2F855A;
            padding: 30px 20px;
            text-align: center;
            color: white;
        }
        
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }
        
        /* Content */
        .content {
            padding: 30px;
        }
        
        .message-details {
            background: #f9f9f9;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .detail-row {
            margin-bottom: 15px;
        }
        
        .detail-label {
            font-weight: bold;
            color: #2F855A;
            display: block;
            margin-bottom: 5px;
        }
        
        .detail-value {
            color: #555555;
        }
        
        .message-content {
            background: #f9f9f9;
            border-radius: 5px;
            padding: 20px;
            margin-top: 20px;
        }
        
        /* Footer */
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #999999;
            border-top: 1px solid #eeeeee;
        }
        
        .logo {
            max-width: 150px;
            margin-bottom: 15px;
        }
        
        /* Responsive adjustments */
        @media only screen and (max-width: 480px) {
            .content {
                padding: 15px;
            }
            
            .header h1 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class='email-container'>
        <div class='header'>
            <h1>Nouveau message de contact</h1>
        </div>
        
        <div class='content'>
            <div class='message-details'>
                <div class='detail-row'>
                    <span class='detail-label'>Nom :</span>
                    <span class='detail-value'>".htmlspecialchars($nom)."</span>
                </div>
                
                <div class='detail-row'>
                    <span class='detail-label'>Email :</span>
                    <span class='detail-value'>".htmlspecialchars($email)."</span>
                </div>
            </div>
            
            <div class='message-content'>
                <div class='detail-label'>Message :</div>
                <div class='detail-value'>".nl2br(htmlspecialchars($message))."</div>
            </div>
        </div>
        
        <div class='footer'>
            <p>Ce message a été envoyé depuis le formulaire de contact de <strong>Bénin Tourisme</strong></p>
            <p>© ".date('Y')." Bénin Tourisme - Tous droits réservés</p>
        </div>
    </div>
</body>
</html>";
                    $mail->Body = $email_message;
                    $mail->AltBody = strip_tags($email_message);

                    $mail->send();
                    error_log("Email envoyé avec succès via PHPMailer");
                } catch (Exception $e) {
                    error_log("Erreur lors de l'envoi de l'email : " . $mail->ErrorInfo);
                    // Ne pas afficher l'erreur à l'utilisateur, mais la logger
                }
            } else {
                error_log("Échec de l'insertion");
                $errors[] = "Erreur lors de l'envoi du message. Merci de réessayer plus tard.";
            }
        } catch (Exception $e) {
            error_log("Erreur : " . $e->getMessage());
            $errors[] = "Une erreur est survenue. Veuillez réessayer plus tard.";
        }
    } else {
        error_log("Erreurs de validation : " . print_r($errors, true));
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - Bénin Tourisme</title>
    <link rel="icon" type="image/svg+xml" href="../assets/favicon/favicon.svg">
    <link rel="icon" type="image/png" sizes="96x96" href="../assets/favicon/favicon-96x96.png">
    <link rel="shortcut icon" href="../assets/favicon/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#2F855A',
                        secondary: '#DD6B20',
                        dark: '#1A202C',
                    },
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
        }
        
        .contact-card {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .contact-card:hover {
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }
        
        .form-input {
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
        }
        
        .form-input:focus {
            border-color: #2F855A;
            box-shadow: 0 0 0 3px rgba(47, 133, 90, 0.2);
        }
        
        .btn-submit {
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php include_once(__DIR__ . "/../includes/navbar.php"); ?>

    <!-- Hero Section -->
     <section class="bg-gradient-to-r from-primary to-secondary py-20 text-white">
        <div class="max-w-6xl mx-auto px-6 lg:px-8 text-center fade-in">
            <h1 class="text-4xl md:text-5xl font-bold mb-6" data-aos="fade-up">
                Contactez-nous
            </h1>
            <p class="text-xl text-white max-w-2xl mx-auto" data-aos="fade-up" data-aos-delay="100">
                Nous sommes là pour répondre à toutes vos questions sur le Bénin
            </p>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="py-16">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Contact Form -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl contact-card p-8" data-aos="fade-up">
                        <?php if ($success): ?>
                            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded">
                                <div class="flex items-center">
                                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                    <p class="text-green-700 font-medium">
                                        Merci pour votre message. Nous vous répondrons dans les plus brefs délais.
                                    </p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($errors)): ?>
                            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
                                <div class="flex items-center">
                                    <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                                    <div>
                                        <?php foreach ($errors as $err): ?>
                                            <p class="text-red-700"><?= htmlspecialchars($err) ?></p>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <h2 class="text-2xl font-bold text-gray-800 mb-6">Envoyez-nous un message</h2>
                        
                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="nom" class="block text-sm font-medium text-gray-700 mb-1">Nom complet</label>
                                    <input type="text" id="nom" name="nom" 
                                           value="<?= htmlspecialchars(isset($_SESSION['user_nom']) && isset($_SESSION['user_prenom']) ? $_SESSION['user_nom'] . ' ' . $_SESSION['user_prenom'] : '') ?>"
                                           class="w-full px-4 py-3 rounded-lg form-input focus:outline-none focus:ring-2 focus:ring-primary"
                                           required>
                                </div>
                                
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Adresse email</label>
                                    <input type="email" id="email" name="email" 
                                           value="<?= htmlspecialchars(isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '') ?>"
                                           class="w-full px-4 py-3 rounded-lg form-input focus:outline-none focus:ring-2 focus:ring-primary"
                                           required>
                                </div>
                            </div>
                            
                            <div>
                                <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                                <textarea id="message" name="message" rows="6"
                                          class="w-full px-4 py-3 rounded-lg form-input focus:outline-none focus:ring-2 focus:ring-primary"
                                          required></textarea>
                            </div>
                            
                            <button type="submit" 
                                    class="w-full md:w-auto px-8 py-3 bg-primary hover:bg-green-700 text-white font-bold rounded-lg btn-submit">
                                <i class="fas fa-paper-plane mr-2"></i>
                                Envoyer le message
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Contact Info -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl contact-card p-8 h-full" data-aos="fade-up" data-aos-delay="100">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6">Nos coordonnées</h2>
                        
                        <div class="space-y-6">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 bg-primary bg-opacity-10 p-3 rounded-full text-primary">
                                    <i class="fas fa-map-marker-alt text-lg"></i>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-800">Adresse</h3>
                                    <p class="text-gray-600">Cotonou, Bénin</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="flex-shrink-0 bg-primary bg-opacity-10 p-3 rounded-full text-primary">
                                    <i class="fas fa-phone text-lg"></i>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-800">Téléphone</h3>
                                    <p class="text-gray-600">+229 0164780067<br>+229 0190077139</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="flex-shrink-0 bg-primary bg-opacity-10 p-3 rounded-full text-primary">
                                    <i class="fas fa-envelope text-lg"></i>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-800">Email</h3>
                                    <p class="text-gray-600">
                                        <a href="mailto:contactbenintourisme@gmail.com" class="hover:text-primary transition-colors">
                                            contactbenintourisme@gmail.com
                                        </a>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="flex-shrink-0 bg-primary bg-opacity-10 p-3 rounded-full text-primary">
                                    <i class="fas fa-clock text-lg"></i>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-800">Horaires</h3>
                                    <p class="text-gray-600">
                                        Lundi - Vendredi: 8h - 18h<br>
                                        Samedi: 9h - 13h
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include_once(__DIR__ . "/../includes/footer.php"); ?>

    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true,
            easing: 'ease-out-quad'
        });
    </script>
</body>
</html>