<?php
session_start();
 include_once "..\config\database.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $mot_de_passe = $_POST['mot_de_passe'];
    $mot_de_passe_conf = $_POST['mot_de_passe_conf'];

    // Vérification des champs requis
    if (!$nom || !$prenom || !$email || !$mot_de_passe || !$mot_de_passe_conf) {
        $_SESSION['error'] = "Tous les champs sont obligatoires.";
        header('Location: inscription.php');
        exit;
    }

    // Validation email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Adresse email invalide.";
        header('Location: inscription.php');
        exit;
    }

    // Vérification mot de passe
    if ($mot_de_passe !== $mot_de_passe_conf) {
        $_SESSION['error'] = "Les mots de passe ne correspondent pas.";
        header('Location: inscription.php');
        exit;
    }

    // Vérifier si email existe déjà
    $sql = "SELECT id FROM utilisateurs WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['email' => $email]);
    if ($stmt->fetch()) {
        $_SESSION['error'] = "Email déjà utilisé.";
        header('Location: inscription.php');
        exit;
    }

    // Hash du mot de passe
    $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);

    // Insertion
    $sql = "INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe) 
            VALUES (:nom, :prenom, :email, :mot_de_passe)";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        'nom' => $nom,
        'prenom' => $prenom,
        'email' => $email,
        'mot_de_passe' => $hash
    ]);

    if ($result) {
        $_SESSION['success'] = "Inscription réussie. Connectez-vous maintenant.";
        header('Location: connexion.php');
        exit;
    } else {
        $_SESSION['error'] = "Erreur lors de l'inscription.";
        header('Location: inscription.php');
        exit;
    }
} else {
    header('Location: inscription.php');
    exit;
}
