<?php
require_once '../config/database.php';

$message = '';
$error = '';
$token = $_GET['token'] ?? '';

// Vérifie que le token est présent
if (!$token) {
    die("Lien invalide.");
}

// Recherche de l'utilisateur par token
$stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE reset_token = ? AND reset_expires > NOW()");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    die("Ce lien a expiré ou est invalide.");
}

// Soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($new_password) || empty($confirm_password)) {
        $error = "Veuillez remplir tous les champs.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas.";
    } elseif (strlen($new_password) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères.";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Mise à jour du mot de passe et suppression du token
        $stmt = $pdo->prepare("UPDATE utilisateurs SET mot_de_passe = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
        $success = $stmt->execute([$hashed_password, $user['id']]);

        if ($success) {
            $message = "Mot de passe mis à jour avec succès. Vous pouvez maintenant vous connecter.";
        } else {
            $error = "Erreur lors de la mise à jour.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réinitialiser le mot de passe</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-md mx-auto">
        <?php if ($message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($message) ?>
            </div>
            <a href="connexion.php" class="text-blue-600 underline">Se connecter</a>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-xl font-semibold mb-4">Nouveau mot de passe</h3>
                <form method="POST" action="">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Nouveau mot de passe</label>
                        <input type="password" name="new_password" required class="mt-1 w-full rounded border-gray-300 shadow-sm">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Confirmer le mot de passe</label>
                        <input type="password" name="confirm_password" required class="mt-1 w-full rounded border-gray-300 shadow-sm">
                    </div>
                    <button type="submit" name="update_password" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        Réinitialiser
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
