<?php
session_start();
include_once("..\config\database.php");

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer et nettoyer les données du formulaire
    $type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING);
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    $date_arrivee = filter_input(INPUT_POST, 'date_arrivee', FILTER_SANITIZE_STRING);
    $date_depart = filter_input(INPUT_POST, 'date_depart', FILTER_SANITIZE_STRING);
    $nombre_personnes = filter_input(INPUT_POST, 'nombre_personnes', FILTER_SANITIZE_NUMBER_INT);
    $commentaires = filter_input(INPUT_POST, 'commentaires', FILTER_SANITIZE_STRING);
    
    // Vérifier que toutes les données requises sont présentes
    if ($type && $id && $date_arrivee && $date_depart && $nombre_personnes) {
        try {
            // Vérifier si l'hôtel existe
            $stmt = $pdo->prepare("SELECT * FROM hotels WHERE id = ?");
            $stmt->execute([$id]);
            $hotel = $stmt->fetch();
            
            if ($hotel) {
                // Insérer la réservation dans la base de données
                $stmt = $pdo->prepare("INSERT INTO reservations (user_id, hotel_id, date_arrivee, date_depart, nombre_personnes, commentaires, statut, date_creation) VALUES (?, ?, ?, ?, ?, ?, 'en_attente', NOW())");
                
                $stmt->execute([
                    $_SESSION['user_id'],
                    $id,
                    $date_arrivee,
                    $date_depart,
                    $nombre_personnes,
                    $commentaires
                ]);
                
                // Rediriger vers une page de confirmation
                header("Location: confirmation_reservation.php?id=" . $pdo->lastInsertId());
                exit();
            } else {
                // L'hôtel n'existe pas
                header("Location: reserve.php?error=hotel_invalide");
                exit();
            }
        } catch (PDOException $e) {
            // En cas d'erreur de base de données
            header("Location: reserve.php?error=erreur_systeme");
            exit();
        }
    } else {
        // Données manquantes
        header("Location: reserve.php?error=donnees_manquantes");
        exit();
    }
} else {
    // Si quelqu'un accède directement à cette page sans soumettre le formulaire
    header("Location: ..\index.php");
    exit();
}
?>