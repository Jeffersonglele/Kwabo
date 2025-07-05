<?php
// Démarrer la session si pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclure les fichiers de configuration
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/access_control.php';


$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Vérifier que l'utilisateur est connecté et a le bon type de compte
check_access(ALLOWED_EVENEMENT);

// Fonction pour rediriger avec un message d'erreur
function redirectWithError($message) {
    $_SESSION['error_message'] = $message;
    header('Location: evenements.php');
    exit();
}

// Vérifier que le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithError("Méthode de requête non autorisée");
}

// Récupération de l'ID du gestionnaire depuis la session
$gestionnaire_id = $_SESSION['gestionnaire_id'] ?? null;
if (!$gestionnaire_id) {
    throw new Exception("Gestionnaire non identifié");
}

// Récupération et validation des champs obligatoires
$required_fields = [
    'nom' => 'Le nom de l\'événement',
    'description' => 'La description',
    'date_debut' => 'La date de début',
    'date_fin' => 'La date de fin',
    'heure' => 'L\'heure',
    'ville' => 'La ville',
    'prix' => 'Le prix'
];

$errors = [];
$form_data = [];

foreach ($required_fields as $field => $label) {
    if (empty(trim($_POST[$field] ?? ''))) {
        $errors[] = "Le champ $label est obligatoire";
    } else {
        $form_data[$field] = trim($_POST[$field]);
    }
}

// Si des erreurs, on redirige avec les messages d'erreur
if (!empty($errors)) {
    $_SESSION['form_errors'] = $errors;
    $_SESSION['form_data'] = $_POST;
    header('Location: evenements.php');
    exit();
}

// Récupération des données validées
$nom = $form_data['nom'];
$description = $form_data['description'];
$date_debut = $form_data['date_debut'];
$date_fin = $form_data['date_fin'];
$heure = $form_data['heure'];
$ville = $form_data['ville'];
$prix = (float)$form_data['prix'];
$site = !empty($_POST['site']) ? trim($_POST['site']) : null;
$incitation = !empty($_POST['incitation']) ? trim($_POST['incitation']) : null;

// Gestion de l'upload d'image
$image_path = '';
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    $errors[] = "Une image est requise pour l'événement";
} else {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $_FILES['image']['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime_type, $allowed_types)) {
        $errors[] = "Le format de l'image n'est pas supporté. Formats acceptés : JPG, PNG, GIF, WebP";
    }

    $max_size = 5 * 1024 * 1024; // 5 Mo
    if ($_FILES['image']['size'] > $max_size) {
        $errors[] = "L'image est trop volumineuse. Taille maximale : 5 Mo";
    }

    if (empty($errors)) {
        $upload_dir = __DIR__ . '/../assets/images/';
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                $errors[] = "Impossible de créer le dossier de destination";
            }
        }

        if (empty($errors)) {
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image_filename = uniqid('event_') . '.' . strtolower($extension);
            $destination = $upload_dir . $image_filename;

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                $errors[] = "Une erreur est survenue lors du téléchargement de l'image";
            } else {
                // On garde le chemin relatif pour l’affichage
                $image_path = '../assets/images/' . $image_filename;
            }
        }
    }
}

// Si des erreurs, redirection
if (!empty($errors)) {
    $_SESSION['form_errors'] = $errors;
    $_SESSION['form_data'] = $_POST;
    header('Location: evenements.php');
    exit();
}

// Insertion en base de données
try {
    $pdo->beginTransaction();

    $sql = "INSERT INTO evenements 
        (nom, description, date_debut, date_fin, heure, ville, prix, image, site, incitation, gestionnaire_id, date_creation)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = $pdo->prepare($sql);

    if (!$stmt) {
        throw new Exception("Erreur préparation requête : " . implode(" | ", $pdo->errorInfo()));
    }

    $success = $stmt->execute([
        $nom,
        $description,
        $date_debut,
        $date_fin,
        $heure,
        $ville,
        $prix,
        $image_path,
        $site,
        $incitation,
        $gestionnaire_id,
    ]);

    if (!$success) {
        throw new Exception("Erreur exécution requête : " . implode(" | ", $stmt->errorInfo()));
    }

    $pdo->commit();

    $_SESSION['success_message'] = "L'événement a été ajouté avec succès !";
    header('Location: tableau_bord.php');
    exit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Supprimer image si upload ok mais erreur insertion
    if (!empty($image_path)) {
        $absolute_path = __DIR__ . '/../' . $image_path;
        if (file_exists($absolute_path)) {
            @unlink($absolute_path);
        }
    }

    // Log et affichage erreur
    error_log("Erreur ajout événement : " . $e->getMessage());
    echo "Erreur : " . $e->getMessage(); // afficher en local

    $_SESSION['error_message'] = "Une erreur est survenue lors de l'ajout de l'événement. Veuillez réessayer.";
    $_SESSION['form_data'] = $_POST;
    header('Location: evenements.php');
    exit();
}
?>
