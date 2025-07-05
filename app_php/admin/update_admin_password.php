<?php
require_once('../config/database.php');

try {
    // Mettre à jour le mot de passe de l'administrateur
    $password = 'admin123';
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("UPDATE administrateurs SET mot_de_passe = ? WHERE email = ?");
    $stmt->execute([$hashed_password, 'contactbenintourisme@gmail.com']);
    
    echo "Mot de passe mis à jour avec succès !";
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?> 