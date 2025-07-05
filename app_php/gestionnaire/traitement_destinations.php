<?php
session_start();

// Vérification de la méthode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("ERREUR: Le formulaire doit être soumis avec la méthode POST");
}

// Autoload Composer (Parsedown notamment)
require_once __DIR__ . '/../vendor/autoload.php';

// Connexion à la base de données
require_once '../config/database.php';

try {
    // Fonction Markdown -> HTML (optionnel si utilisé plus tard)
    function markdownToHtml($markdown) {
        $parsedown = new Parsedown();
        $parsedown->setSafeMode(true);
        return $parsedown->text($markdown);
    }

    // Récupération des données du formulaire
    $nom        = htmlspecialchars($_POST['nom'] ?? '');
    $description= $_POST['description'] ?? ''; // Markdown brut
    $adresse    = htmlspecialchars($_POST['adresse'] ?? '');
    $ville      = htmlspecialchars($_POST['ville'] ?? '');
    $region     = htmlspecialchars($_POST['region'] ?? '');
    $latitude   = htmlspecialchars($_POST['latitude'] ?? null);
    $longitude  = htmlspecialchars($_POST['longitude'] ?? null);
    $type       = htmlspecialchars($_POST['type'] ?? '');
    $prix       = htmlspecialchars($_POST['prix'] ?? '0.00');
    $horaires   = htmlspecialchars($_POST['horaires'] ?? '');

    // Réseaux sociaux
    $facebook   = htmlspecialchars($_POST['facebook'] ?? null);
    $instagram  = htmlspecialchars($_POST['instagram'] ?? null);
    $tiktok     = htmlspecialchars($_POST['tiktok'] ?? null);

    // Gestionnaire connecté
    $gestionnaire_id = $_SESSION['gestionnaire_id'] ?? null;
    if (!$gestionnaire_id) {
        throw new Exception("Gestionnaire non identifié.");
    }

    // Chemins à enregistrer en base
    $image_nom = null;
    $video_url = null;

    // Gestion de l'image principale
    if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image_tmp = $_FILES['image']['tmp_name'];
        $image_name = uniqid('img_') . '_' . basename($_FILES['image']['name']);
        $image_destination = __DIR__ . '/../assets/images/' . $image_name;

        if (!move_uploaded_file($image_tmp, $image_destination)) {
            throw new Exception("Erreur lors du téléchargement de l'image.");
        }

        $image_nom = 'assets/images/' . $image_name;
    }

    // Gestion de la vidéo (facultative)
    if (!empty($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
        $video_tmp = $_FILES['video']['tmp_name'];
        $video_name = uniqid('vid_') . '_' . basename($_FILES['video']['name']);
        $video_destination = __DIR__ . '/../assets/video/' . $video_name;

        if (!move_uploaded_file($video_tmp, $video_destination)) {
            throw new Exception("Erreur lors du téléchargement de la vidéo.");
        }

        $video_url = '../assets/video/' . $video_name;
    }

    // Insertion en base de données
    $sql = "INSERT INTO lieux (
                nom, description, adresse, ville, region, latitude, longitude,
                image, type, prix, video, gestionnaire_id, horaires,
                facebook, instagram, tiktok, date_creation
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $nom, $description, $adresse, $ville, $region,
        $latitude, $longitude, $image_nom, $type, $prix,
        $video_url, $gestionnaire_id, $horaires,
        $facebook, $instagram, $tiktok
    ]);

    // Succès
    $_SESSION['message'] = "✅ Lieu ajouté avec succès !";
    $_SESSION['message_type'] = "success";
    header('Location: tableau_bord.php');
    exit();

} catch (Exception $e) {
    $_SESSION['message'] = "❌ Erreur lors de l'ajout : " . $e->getMessage();
    $_SESSION['message_type'] = "error";
    header('Location: destinations.php');
    exit();
}
?>
