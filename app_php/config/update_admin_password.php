<?php
require_once('database.php');

try {
    // Hasher le mot de passe
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    
    // Mettre à jour le mot de passe dans la base de données
    $stmt = $pdo->prepare("UPDATE administrateurs SET mot_de_passe = ? WHERE email = ?");
    $stmt->execute([$password, 'contactbenintourisme@gmail.com']);
    
    echo "Mot de passe mis à jour avec succès!\n";
    echo "Vous pouvez maintenant vous connecter avec :\n";
    echo "Email: contactbenintourisme@gmail.com\n";
    echo "Mot de passe: admin123\n";
    
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
} 