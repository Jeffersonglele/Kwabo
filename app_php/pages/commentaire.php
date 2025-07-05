<?php
session_start();
include_once("../config/database.php");

if (isset($_POST['publication_id'], $_POST['contenu'], $_SESSION['user_id'])) {
    $publication_id = intval($_POST['publication_id']);
    $contenu = trim($_POST['contenu']);
    $user_id = $_SESSION['user_id'];

    if ($contenu !== '') {
        $stmt = $pdo->prepare("INSERT INTO commentaires (publication_id, user_id, contenu, date_creation) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$publication_id, $_SESSION['user_id'], $contenu]);

        // Affichage immédiat du commentaire (HTML)
        $stmt = $pdo->prepare("SELECT nom, prenom FROM utilisateurs WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        echo '<div class="border p-2 mb-1">';
        echo '<strong>' . htmlspecialchars($user['nom']) . ' ' . htmlspecialchars($user['prenom']) . ':</strong> ';
        echo nl2br(htmlspecialchars($contenu));
        echo '</div>';
    }
}
?>
