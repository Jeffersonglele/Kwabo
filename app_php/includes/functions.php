/**
 * Enregistre une vue pour un élément
 * @param PDO $pdo Instance de la connexion PDO
 * @param int $element_id ID de l'élément consulté
 * @param string $element_type Type de l'élément (destination, circuit, hotel, evenement)
 * @return bool True si la vue a été enregistrée avec succès
 */
function enregistrerVue($pdo, $element_id, $element_type) {
    try {
        // Vérifier si une vue existe déjà pour cet élément et cette IP dans les dernières 24h
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM vues 
            WHERE element_id = ? 
            AND element_type = ? 
            AND ip_visiteur = ? 
            AND date_vue > DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        
        $stmt->execute([
            $element_id,
            $element_type,
            $_SERVER['REMOTE_ADDR']
        ]);
        
        // Si aucune vue n'existe dans les dernières 24h, on enregistre une nouvelle vue
        if ($stmt->fetchColumn() == 0) {
            $stmt = $pdo->prepare("
                INSERT INTO vues (element_id, element_type, ip_visiteur, user_agent) 
                VALUES (?, ?, ?, ?)
            ");
            
            return $stmt->execute([
                $element_id,
                $element_type,
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT']
            ]);
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("Erreur lors de l'enregistrement de la vue : " . $e->getMessage());
        return false;
    }
} 