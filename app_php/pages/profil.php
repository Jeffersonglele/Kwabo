<?php
session_start();
require_once '../config/database.php';

// Définir le chemin de base
$base_path = '/stage/ProjetBinome/';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit();
}

// Initialiser les variables avec des valeurs par défaut
$user = [
    'prenom' => '',
    'nom' => '',
    'email' => '',
    'telephone' => '',
    'date_inscription' => date('Y-m-d')
];
$errors = [];
$success = '';

try {
    // Récupérer les informations de l'utilisateur
    $stmt = $pdo->prepare("SELECT prenom, nom, email, telephone, date_inscription FROM utilisateurs WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $db_user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($db_user) {
        $user = array_merge($user, $db_user);
    } else {
        header('Location: deconnexion.php');
        exit();
    }

    // Traitement du formulaire de mise à jour
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Récupération et nettoyage des données
        $prenom = trim($_POST['prenom'] ?? '');
        $nom = trim($_POST['nom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telephone = trim($_POST['telephone'] ?? '');

        // Validation des champs
        if (empty($prenom)) $errors[] = "Le prénom est requis";
        if (empty($nom)) $errors[] = "Le nom est requis";
        if (empty($email)) $errors[] = "L'email est requis";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Format d'email invalide";

        // Vérifier si l'email existe déjà pour un autre utilisateur
        if ($email !== $user['email']) {
            $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ? AND id != ?");
            $stmt->execute([$email, $_SESSION['user_id']]);
            if ($stmt->fetch()) {
                $errors[] = "Cet email est déjà utilisé";
            }
        }

        // Si pas d'erreurs, mettre à jour le profil
        if (empty($errors)) {
            try {
                $pdo->beginTransaction();

                $stmt = $pdo->prepare("UPDATE utilisateurs SET prenom = ?, nom = ?, email = ?, telephone = ? WHERE id = ?");
                $result = $stmt->execute([$prenom, $nom, $email, $telephone, $_SESSION['user_id']]);

                if ($result) {
                    $pdo->commit();
                    
                    // Mettre à jour les variables de session
                    $_SESSION['user_prenom'] = $prenom;
                    $_SESSION['user_nom'] = $nom;
                    
                    // Mettre à jour les données de l'utilisateur
                    $user['prenom'] = $prenom;
                    $user['nom'] = $nom;
                    $user['email'] = $email;
                    $user['telephone'] = $telephone;
                    
                    $success = "Profil mis à jour avec succès";
                } else {
                    throw new PDOException("Erreur lors de la mise à jour du profil");
                }
            } catch (PDOException $e) {
                $pdo->rollBack();
                $errors[] = "Erreur lors de la mise à jour du profil : " . $e->getMessage();
            }
        }
    }
} catch (PDOException $e) {
    $errors[] = "Erreur de base de données : " . $e->getMessage();
} catch (Exception $e) {
    $errors[] = "Une erreur inattendue est survenue : " . $e->getMessage();
}

// Formater la date d'inscription
$date_inscription = !empty($user['date_inscription']) ? date('F Y', strtotime($user['date_inscription'])) : 'Date inconnue';

// Fonction helper pour afficher les valeurs des champs
function getFieldValue($field) {
    global $user;
    return htmlspecialchars($user[$field] ?? '');
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - Bénin Tourisme</title>
    <link rel="icon" type="image/svg+xml" href="../assets/favicon/favicon.svg">
    <link rel="icon" type="image/png" sizes="96x96" href="../assets/favicon/favicon-96x96.png">
    <link rel="shortcut icon" href="../assets/favicon/favicon.ico">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .profile-header {
            background: linear-gradient(135deg, #1a365d 0%, #2d3748 100%);
        }
        .profile-card {
            backdrop-filter: blur(10px);
            background-color: rgba(255, 255, 255, 0.9);
        }
        /* Ajout des styles pour les inputs et textarea */
        input[type="text"],
        input[type="email"],
        input[type="tel"],
        textarea {
            @apply mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500;
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php include '../includes/navbar.php'; ?>

    <!-- En-tête du profil -->
    <div class="profile-header text-white py-20">
        <div class="container mx-auto px-4">
            <div class="flex items-center space-x-6">
                <div class="relative">
                    <img src="<?= $base_path ?>app_php/assets/images/users.jpg" 
                         alt="Photo de profil" 
                         class="w-32 h-32 rounded-full border-4 border-white shadow-lg object-cover"
                         onerror="this.src='<?= $base_path ?>assets/images/default-user.png'">
                    <button class="absolute bottom-0 right-0 bg-blue-600 text-white p-2 rounded-full shadow-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-camera"></i>
                    </button>
                </div>
                <div>
                    <h1 class="text-3xl font-bold"><?= getFieldValue('prenom') . ' ' . getFieldValue('nom') ?></h1>
                    <p class="text-gray-300 mt-1"><?= getFieldValue('email') ?></p>
                    <p class="text-gray-300 mt-1">Membre depuis <?= $date_inscription ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <?php if (!empty($errors)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul class="list-disc list-inside">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <div class="bg-white shadow rounded-lg p-6">
                <form method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="prenom" class="block text-sm font-medium text-gray-700">Prénom</label>
                            <input type="text" id="prenom" name="prenom" value="<?= getFieldValue('prenom') ?>" 
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="nom" class="block text-sm font-medium text-gray-700">Nom</label>
                            <input type="text" id="nom" name="nom" value="<?= getFieldValue('nom') ?>" 
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" id="email" name="email" value="<?= getFieldValue('email') ?>" 
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="telephone" class="block text-sm font-medium text-gray-700">Téléphone</label>
                            <input type="tel" id="telephone" name="telephone" value="<?= getFieldValue('telephone') ?>" 
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div class="flex justify-end space-x-4">
                        <a href="gestion.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Retour
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-save mr-2"></i>
                            Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 