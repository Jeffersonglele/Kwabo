<?php
session_start();
require_once '../config/google_config.php';
require_once '../config/database.php';

if (isset($_GET['code'])) {
    // Récupérer le token
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token);

    // Récupérer les informations de l'utilisateur
    $google_oauth = new Google\Service\Oauth2($client);
    $google_account_info = $google_oauth->userinfo->get();

    $email = $google_account_info->email;
    $nom = $google_account_info->familyName;
    $prenom = $google_account_info->givenName;
    $google_id = $google_account_info->id;

    // Vérifier si l'utilisateur existe déjà
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        // Créer un nouveau compte
        $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, prenom, email, google_id, date_inscription) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$nom, $prenom, $email, $google_id]);
        
        // Récupérer l'ID du nouvel utilisateur
        $user_id = $pdo->lastInsertId();
    } else {
        $user_id = $user['id'];
        // Mettre à jour le google_id si nécessaire
        if (!$user['google_id']) {
            $stmt = $pdo->prepare("UPDATE utilisateurs SET google_id = ? WHERE id = ?");
            $stmt->execute([$google_id, $user_id]);
        }
    }

    // Créer la session
    $_SESSION['user_id'] = $user_id;
    $_SESSION['nom'] = $nom;
    $_SESSION['prenom'] = $prenom;
    $_SESSION['email'] = $email;

    // Rediriger vers la page d'accueil
    header('Location: /main.php');
    exit;
} else {
    // En cas d'erreur
    $_SESSION['error_messages'] = ["Erreur lors de l'authentification Google"];
    header('Location: connexion.php');
    exit;
}
?> 