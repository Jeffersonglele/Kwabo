<?php
include_once("reserve.php");




// Vérification si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération et nettoyage des données
    $nom = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_STRING);
    $prenom = filter_input(INPUT_POST, 'prenom', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $telephone = filter_input(INPUT_POST, 'telephone', FILTER_SANITIZE_STRING);
    $lieu_id = filter_input(INPUT_POST, 'lieu', FILTER_VALIDATE_INT);
    $date_visite = filter_input(INPUT_POST, 'date_visite', FILTER_SANITIZE_STRING);
    $nombre_personnes = filter_input(INPUT_POST, 'nombre_personnes', FILTER_VALIDATE_INT);
    $type_visite = filter_input(INPUT_POST, 'type_visite', FILTER_SANITIZE_STRING);
    $commentaires = filter_input(INPUT_POST, 'commentaires', FILTER_SANITIZE_STRING);

    // Validation des données
    $errors = [];
    
    if (empty($nom)) $errors[] = "Le nom est requis";
    if (empty($prenom)) $errors[] = "Le prénom est requis";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "L'email n'est pas valide";
    if (empty($telephone)) $errors[] = "Le téléphone est requis";
    if (!$lieu_id) $errors[] = "Veuillez sélectionner un lieu";
    if (empty($date_visite)) $errors[] = "La date de visite est requise";
    if (!$nombre_personnes || $nombre_personnes < 1 || $nombre_personnes > 20) {
        $errors[] = "Le nombre de personnes doit être entre 1 et 20";
    }
    if (empty($type_visite)) $errors[] = "Le type de visite est requis";

    // Si pas d'erreurs, on procède à l'insertion
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO reservations (nom, prenom, email, telephone, lieu_id, date_visite, 
                    nombre_personnes, type_visite, commentaires, date_reservation) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $nom, $prenom, $email, $telephone, $lieu_id, $date_visite,
                $nombre_personnes, $type_visite, $commentaires
            ]);

            // Redirection avec message de succès
            header("Location: ..\index.php?reservation=success#reservation");
            exit();
        } catch (PDOException $e) {
            $errors[] = "Une erreur est survenue lors de l'enregistrement de la réservation";
        }
    }

    // Si erreurs, redirection avec les erreurs
    if (!empty($errors)) {
        $error_string = implode(",", $errors);
        header("Location: ..\index.php?error=" . urlencode($error_string) . "#reservation");
        exit();
    }
} else {
    // Si accès direct au script sans POST
    header("Location: reserve.php");
    exit();
}
?> 