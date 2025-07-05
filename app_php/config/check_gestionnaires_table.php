<?php
include_once("database.php");

try {
    // Vérifier si la table existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'gestionnaires'");
    if ($stmt->rowCount() == 0) {
        // Créer la table gestionnaires
        $sql = "CREATE TABLE gestionnaires (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            nom VARCHAR(255) NOT NULL,
            prenom VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            telephone VARCHAR(20) NOT NULL,
            type_compte ENUM('hotel', 'destination', 'circuit', 'evenement') NOT NULL,
            statut_paiement ENUM('en_attente', 'valide', 'refuse') DEFAULT 'en_attente',
            date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
        )";
        
        $pdo->exec($sql);
        echo "Table gestionnaires créée avec succès.";
    } else {
        echo "La table gestionnaires existe déjà.";
    }
} catch (PDOException $e) {
    echo "Erreur lors de la création de la table : " . $e->getMessage();
}
?> 