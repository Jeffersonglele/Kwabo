<?php
session_start();
include_once("../config/database.php");

if (isset($_POST['publication_id'], $_SESSION['user_id'])) {
    $publication_id = intval($_POST['publication_id']); // <-- ici $_POST
    $user_id = intval($_SESSION['user_id']);

    // Vérifier si déjà liké
    $stmt = $pdo->prepare("SELECT * FROM likes WHERE publication_id = ? AND user_id = ?");
    $stmt->execute([$publication_id, $user_id]);

    if ($stmt->rowCount() > 0) {
        // Déjà liké : on retire
        $stmtDel = $pdo->prepare("DELETE FROM likes WHERE publication_id = ? AND user_id = ?");
        $stmtDel->execute([$publication_id, $user_id]);
    } else {
        // Sinon on ajoute

    $stmtIns = $pdo->prepare("INSERT INTO likes (publication_id, user_id, date_creation) VALUES (?, ?, NOW())");
    $stmtIns->execute([$publication_id, $user_id]);

    }

    // Retourner le nouveau nombre de likes
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE publication_id = ?");
    $stmtCount->execute([$publication_id]);
    echo $stmtCount->fetchColumn();
} else {
    http_response_code(400);
    echo "Paramètres manquants ou utilisateur non connecté.";
}
?>
