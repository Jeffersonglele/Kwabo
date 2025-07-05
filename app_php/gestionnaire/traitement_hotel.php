<?php
session_start();
// Connexion à la base de données
require_once '../config/database.php'; // à adapter selon ton projet

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Récupération des champs texte
        $nom = $_POST['nom'];
        $description = $_POST['description'];
        $etoile = $_POST['etoiles'];
        $ville = $_POST['ville'];
        $latitude = $_POST['latitude'];
        $longitude = $_POST['longitude'];
        $telephone = $_POST['telephone'];
        $prix = $_POST['prix'];
        $contenu = $_POST['description_supp'];
        $site = $_POST['site_web'];
        $email = $_POST['email'];
        $image_nom = ''; 
        $imagesupp_nom = []; // Tableau pour stocker les noms des images supplémentaires

        // Récupération de l'ID du gestionnaire depuis la session
        $gestionnaire_id = $_SESSION['gestionnaire_id'] ?? null;

        if (!$gestionnaire_id) {
            throw new Exception("Gestionnaire non identifié");
        }

        // Gestion de l'image
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image_tmp = $_FILES['image']['tmp_name'];
            $image_nom = basename($_FILES['image']['name']);
            $image_destination = '../assets/images/' . $image_nom;
            if (!move_uploaded_file($image_tmp, $image_destination)) {
                throw new Exception("Erreur lors du déplacement de l'image");
            }
            // Store path relative to web root for frontend usage with correct relative path
            $image_nom = '../assets/images/' . $image_nom;
        }

        // Traitement des images supplémentaires (peuvent être multiples)
        if (isset($_FILES['image_supp'])) {
            // Vérifier si c'est un tableau de fichiers (plusieurs fichiers)
            if (is_array($_FILES['image_supp']['name'])) {
                // Parcourir tous les fichiers uploadés
                for ($i = 0; $i < count($_FILES['image_supp']['name']); $i++) {
                    if ($_FILES['image_supp']['error'][$i] === UPLOAD_ERR_OK) {
                        $tmp_name = $_FILES['image_supp']['tmp_name'][$i];
                        $name = basename($_FILES['image_supp']['name'][$i]);
                        $destination = '../assets/images/' . $name;
                        
                        // Vérifier si le fichier est une image
                        $check = getimagesize($tmp_name);
                        if ($check === false) {
                            throw new Exception("Le fichier " . $name . " n'est pas une image valide.");
                        }
                        
                        // Déplacer le fichier
                        if (move_uploaded_file($tmp_name, $destination)) {
                            $imagesupp_nom[] = '../assets/images/' . $name;
                        } else {
                            throw new Exception("Erreur lors du déplacement de l'image supplémentaire: " . $name);
                        }
                    }
                }
            } elseif ($_FILES['image_supp']['error'] === UPLOAD_ERR_OK) {
                // Gestion pour un seul fichier (rétrocompatibilité)
                $tmp_name = $_FILES['image_supp']['tmp_name'];
                $name = basename($_FILES['image_supp']['name']);
                $destination = '../assets/images/' . $name;
                
                if (move_uploaded_file($tmp_name, $destination)) {
                    $imagesupp_nom[] = '../assets/images/' . $name;
                } else {
                    throw new Exception("Erreur lors du déplacement de l'image supplémentaire");
                }
            }
        }
        
        // Convertir le tableau d'images supplémentaires en chaîne séparée par des virgules pour la base de données
        $imagesupp_string = !empty($imagesupp_nom) ? implode(',', $imagesupp_nom) : '';

        // Requête d'insertion
        $sql = "INSERT INTO hotels (nom, description, etoiles, ville, téléphone, latitude, longitude, image, image_supplementaire, contenu, prix_min, site, email, gestionnaire_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $nom,
            $description,
            $etoile,
            $ville,
            $telephone,
            $latitude,
            $longitude,
            $image_nom,
            $imagesupp_string,
            $contenu,
            $prix,
            $site,
            $email,
            $gestionnaire_id
        ]);

        $_SESSION['message'] = "✅ Lieu ajouté avec succès !";
        $_SESSION['message_type'] = "success"; // ou "error", selon le besoin
        header('Location: tableau_bord.php');
        exit();
    } else {
        $_SESSION['message'] = "erreur lors de l'ajout ";
        $_SESSION['message_type'] = "warning"; // ou "error", selon le besoin
        header('Location: hotels.php');
        exit();
    }
} catch (Exception $e) {
    $_SESSION['message'] = "❌ Erreur lors de l'ajout : " . $e->getMessage();
    $_SESSION['message_type'] = "error";
    header('Location: hotels.php');
    exit();
}