<?php
session_start();
require_once('../config/database.php');

// Vérifier si l'admin est connecté
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Vérifier si l'admin existe toujours dans la base de données
try {
    $stmt = $pdo->prepare("SELECT id FROM administrateurs WHERE id = ? AND statut = 'actif'");
    $stmt->execute([$_SESSION['admin_id']]);
    if (!$stmt->fetch()) {
        // Si l'admin n'existe plus ou n'est plus actif, déconnecter
        session_destroy();
        header('Location: login.php');
        exit();
    }
} catch (PDOException $e) {
    // En cas d'erreur, déconnecter par sécurité
    session_destroy();
    header('Location: login.php');
    exit();
} 