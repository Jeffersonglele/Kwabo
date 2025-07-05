<?php
require_once('auth_check.php');
require_once('../config/database.php');

// Vérifier si l'admin est connecté
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Initialiser les variables
$inscriptions = [];
$error = null;
$success = '';
$error_profile = '';

// Récupérer les informations de l'administrateur
try {
    $stmt = $pdo->prepare("SELECT * FROM administrateurs WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    $admin = $stmt->fetch();
} catch (PDOException $e) {
    $error_profile = "Erreur lors de la récupération des informations";
}

// Traitement du formulaire de mise à jour du profil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $nom = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_STRING);
        $prenom = filter_input(INPUT_POST, 'prenom', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

        try {
            $stmt = $pdo->prepare("UPDATE administrateurs SET nom = ?, prenom = ?, email = ? WHERE id = ?");
            $stmt->execute([$nom, $prenom, $email, $_SESSION['admin_id']]);
            
            $_SESSION['admin_nom'] = $nom;
            $_SESSION['admin_prenom'] = $prenom;
            
            $success = "Profil mis à jour avec succès";
        } catch (PDOException $e) {
            $error_profile = "Erreur lors de la mise à jour du profil";
        }
    }
    
    // Traitement du formulaire de changement de mot de passe
    if (isset($_POST['update_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        try {
            // Vérifier l'ancien mot de passe
            $stmt = $pdo->prepare("SELECT mot_de_passe FROM administrateurs WHERE id = ?");
            $stmt->execute([$_SESSION['admin_id']]);
            $admin = $stmt->fetch();

            if (password_verify($current_password, $admin['mot_de_passe'])) {
                if ($new_password === $confirm_password) {
                    if (strlen($new_password) >= 8) {
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE administrateurs SET mot_de_passe = ? WHERE id = ?");
                        $stmt->execute([$hashed_password, $_SESSION['admin_id']]);
                        $success = "Mot de passe mis à jour avec succès";
                    } else {
                        $error_profile = "Le nouveau mot de passe doit contenir au moins 8 caractères";
                    }
                } else {
                    $error_profile = "Les nouveaux mots de passe ne correspondent pas";
                }
            } else {
                $error_profile = "Mot de passe actuel incorrect";
            }
        } catch (PDOException $e) {
            $error_profile = "Erreur lors de la mise à jour du mot de passe";
        }
    }
}

// Récupérer les inscriptions en attente
try {
    $stmt = $pdo->prepare("
        SELECT g.*, 
               DATE_FORMAT(g.date_inscription, '%d/%m/%Y %H:%i') as date_formatted 
        FROM gestionnaires g 
        WHERE g.statut_paiement = 'en_attente' 
        ORDER BY g.date_inscription DESC
    ");
    $stmt->execute();
    $inscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des inscriptions : " . $e->getMessage();
    $inscriptions = [];
}

// Récupérer les journaux de connexion
try {
    $stmt = $pdo->prepare("
        SELECT 
            a.nom,
            a.prenom,
            a.email,
            a.derniere_connexion,
            a.statut,
            COUNT(*) as tentatives_echouees
        FROM administrateurs a
        LEFT JOIN connexion_logs l ON a.id = l.admin_id
        GROUP BY a.id
        ORDER BY a.derniere_connexion DESC
    ");
    $stmt->execute();
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_logs = "Erreur lors de la récupération des journaux";
    $logs = [];
}

// Déterminer l'onglet actif
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'inscriptions';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Bénin Tourisme</title>
    <link rel="icon" type="image/svg+xml" href="assets/favicon/favicon.svg">
    <link rel="icon" type="image/png" sizes="96x96" href="assets/favicon/favicon-96x96.png">
    <link rel="shortcut icon" href="assets/favicon/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Sidebar -->
        <div class="fixed inset-y-0 left-0 w-64 bg-blue-900 text-white">
            <div class="p-4">
                <h1 class="text-2xl font-bold">Administrateur Contrôle</h1>
            </div>
            <nav class="mt-8">
                <a href="index.php" class="block py-2 px-4 <?php echo $active_tab === 'inscriptions' ? 'bg-blue-800' : 'hover:bg-blue-800'; ?>">
                    <i class="fas fa-home mr-2"></i> Tableau de bord
                </a>
                <a href="index.php?tab=profil" class="block py-2 px-4 <?php echo $active_tab === 'profil' ? 'bg-blue-800' : 'hover:bg-blue-800'; ?>">
                    <i class="fas fa-user mr-2"></i> Profil
                </a>
                <a href="index.php?tab=settings" class="block py-2 px-4 <?php echo $active_tab === 'settings' ? 'bg-blue-800' : 'hover:bg-blue-800'; ?>">
                    <i class="fas fa-cog mr-2"></i> Paramètres
                </a>
                <a href="index.php?tab=logs" class="block py-2 px-4 <?php echo $active_tab === 'logs' ? 'bg-blue-800' : 'hover:bg-blue-800'; ?>">
                    <i class="fas fa-history mr-2"></i> Journaux
                </a>
                <a href="logout.php" class="block py-2 px-4 hover:bg-blue-800">
                    <i class="fas fa-sign-out-alt mr-2"></i> Déconnexion
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="ml-64 p-8">
            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <?php if ($error_profile): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($error_profile); ?>
                </div>
            <?php endif; ?>

            <!-- Onglet Inscriptions -->
            <?php if ($active_tab === 'inscriptions'): ?>
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-2xl font-bold mb-6">Inscriptions en attente</h2>
                    
                    <?php if ($error): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (empty($inscriptions)): ?>
                        <div class="text-center py-4">
                            <p class="text-gray-500">Aucune inscription en attente pour le moment.</p>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Téléphone</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date d'inscription</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <?php foreach ($inscriptions as $inscription): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php echo htmlspecialchars($inscription['nom']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php echo htmlspecialchars($inscription['email']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php echo htmlspecialchars($inscription['telephone']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php echo htmlspecialchars($inscription['date_formatted']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <button onclick="viewDetails(<?php echo $inscription['id']; ?>)" 
                                                    class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 mr-2">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button onclick="validateInscription(<?php echo $inscription['id']; ?>)" 
                                                    class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600 mr-2">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button onclick="rejectInscription(<?php echo $inscription['id']; ?>)" 
                                                    class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Onglet Profil -->
            <?php if ($active_tab === 'profil'): ?>
                <div class="max-w-4xl mx-auto">
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h2 class="text-2xl font-bold mb-6">Mon Profil</h2>

                        <div class="space-y-6">
                            <!-- Informations de base -->
                            <div class="border-b pb-6">
                                <h3 class="text-lg font-semibold mb-4">Informations de base</h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600">Nom</label>
                                        <p class="mt-1 text-gray-900"><?php echo htmlspecialchars($admin['nom']); ?></p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600">Prénom</label>
                                        <p class="mt-1 text-gray-900"><?php echo htmlspecialchars($admin['prenom']); ?></p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600">Email</label>
                                        <p class="mt-1 text-gray-900"><?php echo htmlspecialchars($admin['email']); ?></p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600">Statut</label>
                                        <p class="mt-1">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                <?php echo ucfirst($admin['statut']); ?>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Informations de connexion -->
                            <div class="border-b pb-6">
                                <h3 class="text-lg font-semibold mb-4">Informations de connexion</h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600">Date de création du compte</label>
                                        <p class="mt-1 text-gray-900">
                                            <?php echo date('d/m/Y H:i', strtotime($admin['date_creation'])); ?>
                                        </p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600">Dernière connexion</label>
                                        <p class="mt-1 text-gray-900">
                                            <?php echo $admin['derniere_connexion'] ? date('d/m/Y H:i', strtotime($admin['derniere_connexion'])) : 'Jamais'; ?>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex justify-end space-x-4">
                                <a href="index.php?tab=settings" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                                    <i class="fas fa-cog mr-2"></i> Modifier mes paramètres
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Onglet Paramètres -->
            <?php if ($active_tab === 'settings'): ?>
                <div class="max-w-4xl mx-auto">
                    <h2 class="text-2xl font-bold mb-6">Paramètres du compte</h2>

                    <!-- Formulaire de mise à jour du profil -->
                    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                        <h3 class="text-xl font-semibold mb-4">Informations du profil</h3>
                        <form method="POST" action="" class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nom</label>
                                    <input type="text" name="nom" value="<?php echo htmlspecialchars($admin['nom']); ?>" required
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Prénom</label>
                                    <input type="text" name="prenom" value="<?php echo htmlspecialchars($admin['prenom']); ?>" required
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <button type="submit" name="update_profile"
                                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                                Mettre à jour le profil
                            </button>
                        </form>
                    </div>

                    <!-- Formulaire de changement de mot de passe -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-xl font-semibold mb-4">Changer le mot de passe</h3>
                        <form method="POST" action="" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Mot de passe actuel</label>
                                <input type="password" name="current_password" required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nouveau mot de passe</label>
                                <input type="password" name="new_password" required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Confirmer le nouveau mot de passe</label>
                                <input type="password" name="confirm_password" required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <button type="submit" name="update_password"
                                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                                Changer le mot de passe
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Onglet Journaux -->
            <?php if ($active_tab === 'logs'): ?>
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-2xl font-bold mb-6">Journaux de connexion</h2>

                    <?php if (isset($error_logs)): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <?php echo htmlspecialchars($error_logs); ?>
                        </div>
                    <?php endif; ?>

                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Administrateur</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dernière connexion</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tentatives échouées</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php echo htmlspecialchars($log['prenom'] . ' ' . $log['nom']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php echo htmlspecialchars($log['email']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php echo $log['derniere_connexion'] ? date('d/m/Y H:i', strtotime($log['derniere_connexion'])) : 'Jamais'; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $log['statut'] === 'actif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                            <?php echo ucfirst($log['statut']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php echo $log['tentatives_echouees']; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function viewDetails(id) {
            window.location.href = `view_inscription.php?id=${id}`;
        }

        function validateInscription(id) {
            if (confirm('Êtes-vous sûr de vouloir valider cette inscription ?')) {
                fetch('validate_inscription.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: id })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Inscription validée avec succès !');
                        location.reload();
                    } else {
                        alert('Erreur lors de la validation : ' + data.message);
                    }
                });
            }
        }

        function rejectInscription(id) {
            if (confirm('Êtes-vous sûr de vouloir rejeter cette inscription ?')) {
                fetch('reject_inscription.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: id })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Inscription rejetée avec succès !');
                        location.reload();
                    } else {
                        alert('Erreur lors du rejet : ' + data.message);
                    }
                });
            }
        }
    </script>
</body>
</html>