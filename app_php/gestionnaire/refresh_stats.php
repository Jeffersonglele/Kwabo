<?php
session_start();
require_once('../config/database.php');

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['gestionnaire_id']) || !isset($_SESSION['gestionnaire_type'])) {
    echo json_encode(['success' => false, 'error' => 'Non autorisé']);
    exit;
}

$user_id = $_SESSION['gestionnaire_id'];
$user_type = $_SESSION['gestionnaire_type'];

// Activer les erreurs PDO
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    $response = [];
    
    switch($user_type) {
        case 'destination':
            $table = 'lieux';
            $element_type = 'destination';
            break;
        case 'circuit':
            $table = 'circuits';
            $element_type = 'circuit';
            break;
        case 'hotel':
            $table = 'hotels';
            $element_type = 'hotel';
            break;
        case 'evenement':
            $table = 'evenements';
            $element_type = 'evenement';
            break;
        default:
            throw new Exception("Type d'utilisateur non reconnu");
    }
    
    // Total des éléments
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM $table WHERE gestionnaire_id = ?");
    $stmt->execute([$user_id]);
    $response['total_items'] = (int)$stmt->fetchColumn();
    
    // Éléments ajoutés ce mois
    $stmt_mois = $pdo->prepare("
        SELECT COUNT(*) 
        FROM $table 
        WHERE gestionnaire_id = ? 
        AND MONTH(date_creation) = MONTH(CURRENT_DATE())
        AND YEAR(date_creation) = YEAR(CURRENT_DATE())
    ");
    $stmt_mois->execute([$user_id]);
    $response['items_mois'] = (int)$stmt_mois->fetchColumn();
    
    // Vues totales
    $stmt_vues = $pdo->prepare("
        SELECT COUNT(*) as total_vues 
        FROM vues v
        INNER JOIN $table d ON v.element_id = d.id
        WHERE d.gestionnaire_id = ? 
        AND v.element_type = ?
    ");
    $stmt_vues->execute([$user_id, $element_type]);
    $response['total_vues'] = (int)($stmt_vues->fetchColumn() ?: 0);
    
    // Vues du mois
    $stmt_vues_mois = $pdo->prepare("
        SELECT COUNT(*) as vues_mois 
        FROM vues v
        INNER JOIN $table d ON v.element_id = d.id
        WHERE d.gestionnaire_id = ? 
        AND v.element_type = ?
        AND MONTH(v.date_vue) = MONTH(CURRENT_DATE())
        AND YEAR(v.date_vue) = YEAR(CURRENT_DATE())
    ");
    $stmt_vues_mois->execute([$user_id, $element_type]);
    $response['vues_mois'] = (int)($stmt_vues_mois->fetchColumn() ?: 0);
    
    // Top 3 des éléments les plus vus
    $stmt_top = $pdo->prepare("
        SELECT d.nom, COUNT(v.id) as nombre_vues 
        FROM $table d
        LEFT JOIN vues v ON v.element_id = d.id AND v.element_type = ?
        WHERE d.gestionnaire_id = ?
        GROUP BY d.id, d.nom
        ORDER BY nombre_vues DESC 
        LIMIT 3
    ");
    $stmt_top->execute([$element_type, $user_id]);
    $response['top_items'] = $stmt_top->fetchAll(PDO::FETCH_ASSOC);
    
    $response['success'] = true;
    
} catch (Exception $e) {
    $response = [
        'success' => false,
        'error' => $e->getMessage()
    ];
}

// Retourner la réponse en JSON
header('Content-Type: application/json');
echo json_encode($response);
