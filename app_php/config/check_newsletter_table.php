<?php
include_once("../config/database.php");

try {
    // Vérifier si la table existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'newsletter'");
    if ($stmt->rowCount() == 0) {
        // La table n'existe pas, la créer
        $sql = "CREATE TABLE IF NOT EXISTS `newsletter` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `email` varchar(255) NOT NULL,
            `date_inscription` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `ip` varchar(45) DEFAULT NULL,
            `user_agent` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        $pdo->exec($sql);
        echo "Table newsletter créée avec succès.\n";
    } else {
        echo "La table newsletter existe déjà.\n";
    }
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?> 