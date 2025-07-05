<?php
session_start();
include_once("../config/database.php");

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_messages'] = ["Vous devez être connecté pour publier un article. Veuillez vous connecter ou créer un compte."];
    header('Location: connexion.php?redirect=blog&message=login_required');
    exit;
}

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier à nouveau l'authentification pour la sécurité
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error_messages'] = ["Session expirée. Veuillez vous reconnecter pour publier."];
        header('Location: connexion.php?redirect=blog&message=session_expired');
        exit;
    }
    $titre = trim($_POST['titre'] ?? '');
    $contenu = trim($_POST['contenu'] ?? '');
    $media = $_FILES['media'] ?? null;
    $user_id = $_SESSION['user_id'];
    
    // Validation basique
    $errors = [];
    if (empty($titre)) {
        $errors[] = "Le titre est requis";
    }
    if (empty($contenu)) {
        $errors[] = "Le contenu est requis";
    }
    
    // Traitement du média si il existe
    $media_path = null;
    $media_type = null;
    
    if ($media && $media['error'] === UPLOAD_ERR_OK) {
        $file_type = mime_content_type($media['tmp_name']);
        $allowed_image_types = ['image/jpeg', 'image/png', 'image/gif'];
        $allowed_video_types = ['video/mp4', 'video/webm', 'video/ogg'];
        
        if (in_array($file_type, $allowed_image_types)) {
            $media_type = 'image';
            $upload_dir = '../assets/images/';
        } elseif (in_array($file_type, $allowed_video_types)) {
            $media_type = 'video';
            $upload_dir = '../assets/videos/';
        } else {
            $errors[] = "Type de fichier non autorisé. Utilisez JPG, PNG, GIF pour les images ou MP4, WEBM, OGG pour les vidéos.";
        }
        
        if ($media_type) {
            // Créer le dossier s'il n'existe pas
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Générer un nom de fichier unique avec timestamp
            $file_extension = strtolower(pathinfo($media['name'], PATHINFO_EXTENSION));
            $new_filename = date('Ymd_His') . '_' . uniqid() . '.' . $file_extension;
            $destination = $upload_dir . $new_filename;
            
            // Déplacer le fichier
            if (move_uploaded_file($media['tmp_name'], $destination)) {
                // Stocker le chemin relatif dans la base de données 
                $media_path = '../assets/' . ($media_type === 'image' ? 'images' : 'videos') . '/' . $new_filename;
            } else {
                $errors[] = "Erreur lors du téléchargement du média. Vérifiez les permissions du dossier.";
            }
        }
    }
    
    // Si pas d'erreurs, on insère dans la base de données
    if (empty($errors)) {
        try {
            $date_creation = date('Y-m-d H:i:s');
            $stmt = $pdo->prepare("INSERT INTO publications (user_id, titre, contenu, media_path, media_type, date_creation) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $titre, $contenu, $media_path, $media_type, $date_creation]);

            // Redirection vers le blog avec un message de succès
            $_SESSION['success_message'] = "Votre publication a été ajoutée avec succès !";
            header('Location: blog.php');
            exit;
            
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de la publication : " . $e->getMessage();
        }
    }
    
    // S'il y a des erreurs, on les stocke en session
    if (!empty($errors)) {
        $_SESSION['error_messages'] = $errors;
        header('Location: blog.php');
        exit;
    }
}
?> 