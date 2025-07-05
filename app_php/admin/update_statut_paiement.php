<?php
require_once '../config/database.php';
require_once 'auth_check.php';

// Vérifier si l'utilisateur est connecté en tant qu'admin
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit();
}

try {
    // Démarrer une transaction
    $pdo->beginTransaction();

    // Mettre à jour uniquement les gestionnaires
    $stmt = $pdo->prepare("UPDATE gestionnaires SET statut_paiement = 'en_attente' WHERE statut_paiement IS NULL OR statut_paiement = ''");
    $stmt->execute();

    // Valider la transaction
    $pdo->commit();

    // Retourner une réponse JSON
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Les statuts de paiement des gestionnaires ont été mis à jour avec succès',
        'details' => [
            'gestionnaires' => 'en_attente'
        ]
    ]);

} catch (PDOException $e) {
    // Annuler la transaction en cas d'erreur
    $pdo->rollBack();
    
    // Logger l'erreur (à implémenter selon vos besoins)
    error_log("Erreur lors de la mise à jour des statuts de paiement : " . $e->getMessage());
    
    // Retourner une réponse d'erreur
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la mise à jour des statuts de paiement',
        'error' => $e->getMessage()
    ]);
}
?> 