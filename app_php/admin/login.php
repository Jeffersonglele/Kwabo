<?php
session_start();
require_once('../config/database.php');

// Vérifier si l'admin est déjà connecté
if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}


// Vérifie si l'accès est légitime via un token ou une variable de session si il entre l'url directement dans le navigateur
if (!isset($_SESSION['allow_admin_login']) || $_SESSION['allow_admin_login'] !== true) {
    http_response_code(404); // ou 403 selon ton besoin
    echo '
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Erreur 404</title>
        <style>
            body {
                background-color: #f8fafc;
                color: #1f2937;
                font-family: Arial, sans-serif;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
            }
            .message {
                text-align: center;
                font-size: 1.5rem;
                font-weight: bold;
            }
        </style>
    </head>
    <body>
        <div class="message">🚫 Accès refusé - Page introuvable.</div>
    </body>
    </html>';
    exit();
}



$error = '';

// Vérifier les tentatives de connexion
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt'] = time();
}

// Réinitialiser les tentatives après 30 minutes
if (time() - $_SESSION['last_attempt'] > 1800) {
    $_SESSION['login_attempts'] = 0;
}

// Bloquer après 5 tentatives échouées
if ($_SESSION['login_attempts'] >= 5) {
    $error = "Trop de tentatives de connexion. Veuillez réessayer dans 30 minutes.";
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM administrateurs WHERE email = ? AND statut = 'actif'");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        // Enregistrer la tentative de connexion
        $stmt = $pdo->prepare("INSERT INTO connexion_logs (admin_id, ip_address, user_agent, status) VALUES (?, ?, ?, ?)");
        
        if ($admin && password_verify($password, $admin['mot_de_passe'])) {
            // Connexion réussie
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_nom'] = $admin['nom'];
            $_SESSION['admin_prenom'] = $admin['prenom'];
            
            // Enregistrer la connexion réussie
            $stmt->execute([$admin['id'], $ip_address, $user_agent, 'success']);
            
            // Mettre à jour la dernière connexion
            $stmt = $pdo->prepare("UPDATE administrateurs SET derniere_connexion = NOW() WHERE id = ?");
            $stmt->execute([$admin['id']]);
            
            // Réinitialiser les tentatives
            $_SESSION['login_attempts'] = 0;
            
            // Rediriger vers le tableau de bord
            header('Location: index.php');
            exit();
        } else {
            // Échec de la connexion
            $_SESSION['login_attempts']++;
            $_SESSION['last_attempt'] = time();
            $error = "Email ou mot de passe incorrect";
            
            // Enregistrer l'échec de connexion
            $admin_id = $admin ? $admin['id'] : null;
            $stmt->execute([$admin_id, $ip_address, $user_agent, 'failed']);
        }
    } catch (PDOException $e) {
        $error = "Une erreur est survenue";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Admin - Bénin Tourisme</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full space-y-8 p-8 bg-white rounded-lg shadow-lg">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Administration
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Bénin Tourisme
                </p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <form class="mt-8 space-y-6" method="POST" action="">
                <div class="rounded-md shadow-sm -space-y-px">
                    <div>
                        <label for="email" class="sr-only">Email</label>
                        <input id="email" name="email" type="email" required 
                               class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" 
                               placeholder="Email">
                    </div>
                    <div>
                        <label for="password" class="sr-only">Mot de passe</label>
                        <input id="password" name="password" type="password" required 
                               class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" 
                               placeholder="Mot de passe">
                    </div>
                </div>

                <div>
                    <button type="submit" 
                            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            <?php echo ($_SESSION['login_attempts'] >= 5) ? 'disabled' : ''; ?>>
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <i class="fas fa-lock"></i>
                        </span>
                        Se connecter
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html> 