<?php
// Démarrer la session si pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclure la config DB et contrôle d'accès
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/access_control.php';

// Activer le mode d’erreur PDO exception si pas déjà fait dans database.php
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Vérifier que l'utilisateur est connecté et a le bon type de compte
check_access(ALLOWED_CIRCUIT);

// Fonction pour rediriger avec un message d’erreur
function redirectWithError(string $message) {
    $_SESSION['error_message'] = $message;
    header('Location: circuits.php');
    exit();
}

// Vérifier méthode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithError("Méthode de requête non autorisée.");
}

// Récupérer l’ID du gestionnaire
$gestionnaire_id = $_SESSION['gestionnaire_id'] ?? null;
if (!$gestionnaire_id) {
    redirectWithError("Gestionnaire non identifié.");
}

// Champs obligatoires avec libellé
$required_fields = [
    'nom' => 'Nom du circuit',
    'sous_titre' => 'Sous-titre',
    'description_courte' => 'Description courte',
    'description_longue' => 'Description longue',
    'itineraire' => 'Itinéraire',
    'villes_visitees' => 'Villes visitées',
    'prix' => 'Prix',
    'duree' => 'Durée',
    'taille_groupe' => 'Taille du groupe',
    'lieu_depart' => 'Lieu de départ',
    'type' => 'Type de circuit',
    'tel' => 'Téléphone',
    'inclus' => 'Prestations incluses',
    'non_inclus' => 'Prestations non incluses',
    'places_disponibles' => 'Places disponibles',
];

// Validation des champs
$errors = [];
$form_data = [];

foreach ($required_fields as $field => $label) {
    $value = trim($_POST[$field] ?? '');
    if ($value === '') {
        $errors[] = "Le champ \"$label\" est obligatoire.";
    } else {
        $form_data[$field] = $value;
    }
}

// Validation prix et places_disponibles (numériques positifs)
if (isset($form_data['prix']) && (!is_numeric($form_data['prix']) || $form_data['prix'] < 0)) {
    $errors[] = "Le prix doit être un nombre positif.";
}
if (isset($form_data['places_disponibles']) && (!is_numeric($form_data['places_disponibles']) || $form_data['places_disponibles'] < 0)) {
    $errors[] = "Le nombre de places disponibles doit être un nombre positif.";
}

// Gestion upload image
$image_name = '';
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    $errors[] = "Une image est requise pour le circuit.";
} else {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $_FILES['image']['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime_type, $allowed_types)) {
        $errors[] = "Format de l'image non supporté. Formats acceptés : JPG, PNG, GIF, WebP.";
    }

    $max_size = 5 * 1024 * 1024; // 5 Mo
    if ($_FILES['image']['size'] > $max_size) {
        $errors[] = "L'image est trop volumineuse (max 5 Mo).";
    }
}

// Si erreurs, rediriger avec données et erreurs
if ($errors) {
    $_SESSION['form_errors'] = $errors;
    $_SESSION['form_data'] = $_POST;
    header('Location: circuits.php');
    exit();
}

// Gestion de l'image
$image_name = '';
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $image_tmp = $_FILES['image']['tmp_name'];
    $image_name = uniqid('circuit_') . '_' . basename($_FILES['image']['name']);
    $upload_dir = __DIR__ . '/../assets/images/';
    
    if (!is_dir($upload_dir) && !mkdir($upload_dir, 0755, true)) {
        redirectWithError("Impossible de créer le dossier de destination pour les images.");
    }
    
    $destination = $upload_dir . $image_name;
    
    if (!move_uploaded_file($image_tmp, $destination)) {
        redirectWithError("Erreur lors du téléchargement de l'image.");
    }
    
    // Stocker le chemin relatif pour le frontend
    $image_name = '../assets/images/' . $image_name;
} else {
    redirectWithError("Une image est requise pour le circuit.");
}

// Préparation des données pour insertion (récupérer aussi champs optionnels)
$site = !empty($_POST['site']) ? trim($_POST['site']) : '';
$email = !empty($_POST['email']) ? trim($_POST['email']) : '';

// Insertion en base dans une transaction
try {
    $pdo->beginTransaction();

    $sql = "INSERT INTO circuits (
        nom, sous_titre, description_courte, description_longue, itineraire, villes_visitees,
        prix, duree, taille_groupe, lieu_depart, image, type, tel, inclus, non_inclus,
        places_disponibles, site, email, gestionnaire_id, date_creation
    ) VALUES (
        :nom, :sous_titre, :description_courte, :description_longue, :itineraire, :villes_visitees,
        :prix, :duree, :taille_groupe, :lieu_depart, :image, :type, :tel, :inclus, :non_inclus,
        :places_disponibles, :site, :email, :gestionnaire_id, NOW()
    )";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':nom' => $form_data['nom'],
        ':sous_titre' => $form_data['sous_titre'],
        ':description_courte' => $form_data['description_courte'],
        ':description_longue' => $form_data['description_longue'],
        ':itineraire' => $form_data['itineraire'],
        ':villes_visitees' => $form_data['villes_visitees'],
        ':prix' => (float)$form_data['prix'],
        ':duree' => (int)$form_data['duree'],
        ':taille_groupe' => (int)$form_data['taille_groupe'],
        ':lieu_depart' => $form_data['lieu_depart'],
        ':image' => $image_name,
        ':type' => $form_data['type'],
        ':tel' => $form_data['tel'],
        ':inclus' => $form_data['inclus'],
        ':non_inclus' => $form_data['non_inclus'],
        ':places_disponibles' => (int)$form_data['places_disponibles'],
        ':site' => $site,
        ':email' => $email,
        ':gestionnaire_id' => $gestionnaire_id,
    ]);

    $pdo->commit();

    $_SESSION['success_message'] = "Le circuit a été ajouté avec succès !";
    header('Location: tableau_bord.php');
    exit();

} catch (PDOException $e) {
    $pdo->rollBack();

    // Supprimer image uploadée si erreur
    if (file_exists($destination)) {
        unlink($destination);
    }

    error_log("Erreur insertion circuit : " . $e->getMessage());

    $_SESSION['error_message'] = "Une erreur est survenue lors de l'ajout du circuit : " . $e->getMessage();
    $_SESSION['form_data'] = $_POST;
    header('Location: circuits.php');
    exit();
}
?>
