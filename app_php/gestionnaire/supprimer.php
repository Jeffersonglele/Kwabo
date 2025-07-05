<?php
session_start();
include_once("../config/database.php");

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['gestionnaire_id'])) {
    header("Location: connexion.php");
    exit();
}

// Récupérer le type d'élément et l'ID
$type = $_GET['type'] ?? '';
$id = (int)($_GET['id'] ?? 0);
$gestionnaire_id = $_SESSION['gestionnaire_id'];

// Vérifier les paramètres
if (empty($type) || $id <= 0) {
    $_SESSION['error'] = "Paramètres invalides";
    header("Location: liste.php");
    exit();
}

// Vérifier que le type est valide
$valid_types = ['lieu', 'circuit', 'hotel'];
if (!in_array($type, $valid_types)) {
    $_SESSION['error'] = "Type d'élément invalide";
    header("Location: liste.php");
    exit();
}

// Déterminer la table et le champ ID en fonction du type
$table = $type === 'lieu' ? 'lieux' : ($type === 'circuit' ? 'circuits' : 'hotels');
$id_field = $type === 'circuit' ? 'id_circuit' : 'id';

// Vérifier si l'élément existe et appartient au gestionnaire
try {
    $sql = "SELECT $id_field FROM $table WHERE $id_field = ? AND gestionnaire_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id, $gestionnaire_id]);
    
    if (!$stmt->fetch()) {
        $_SESSION['error'] = "Élément introuvable ou accès non autorisé";
        header("Location: liste.php");
        exit();
    }
    
    // Supprimer l'élément
    $delete_sql = "DELETE FROM $table WHERE $id_field = ? AND gestionnaire_id = ?";
    $delete_stmt = $pdo->prepare($delete_sql);
    $delete_stmt->execute([$id, $gestionnaire_id]);
    
    if ($delete_stmt->rowCount() > 0) {
        $_SESSION['success'] = "L'élément a été supprimé avec succès";
    } else {
        $_SESSION['error'] = "Une erreur est survenue lors de la suppression";
    }
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors de la suppression: " . $e->getMessage();
}

// Rediriger vers la liste
header("Location: liste.php");
exit();
