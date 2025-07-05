<?php
session_start();
var_dump($_SESSION['user_id']); // juste pour test
include_once("..\config\database.php"); // ta connexion PDO

// Vérifier si utilisateur connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $contenu = trim($_POST['contenu'] ?? '');
    $type_media = $_POST['type_media'] ?? '';

    // Validation des données
    if (empty($contenu)) {
        $error = "Le contenu ne peut pas être vide";
    } else {
        try {
            // Gestion de l'upload de média
            $media_path = null;
            if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../assets/video/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $file_extension = strtolower(pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION));
                $allowed_image_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                $allowed_video_extensions = ['mp4', 'webm', 'ogg'];

                // Vérification du type de fichier
                if ($type_media === 'image' && !in_array($file_extension, $allowed_image_extensions)) {
                    throw new Exception("Format d'image non supporté. Formats acceptés : " . implode(', ', $allowed_image_extensions));
                }
                if ($type_media === 'video' && !in_array($file_extension, $allowed_video_extensions)) {
                    throw new Exception("Format de vidéo non supporté. Formats acceptés : " . implode(', ', $allowed_video_extensions));
                }

                // Génération d'un nom de fichier unique
                $new_filename = uniqid() . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;

                if (move_uploaded_file($_FILES['media']['tmp_name'], $upload_path)) {
                    $media_path = $upload_path;
                } else {
                    throw new Exception("Erreur lors de l'upload du fichier");
                }
            }

            // Insertion dans la base de données
            $sql = "INSERT INTO posts (utilisateur_id, contenu, media, type_media) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId, $contenu, $media_path, $type_media]);

            $message = "Publication créée avec succès !";
            // Redirection vers la page des posts après succès
            header("Location: blog.php");
            exit();

        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Créer un post</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.2/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

<?php include_once(__DIR__ . "/../includes/navbar.php"); ?>

<div class="max-w-2xl mx-auto mt-10 bg-white p-8 rounded shadow">
    <h1 class="text-2xl font-bold mb-6">Créer un nouveau post</h1>

    <?php if ($message): ?>
        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="space-y-6">
        <div>
            <label for="contenu" class="block font-medium mb-1">Contenu</label>
            <textarea id="contenu" name="contenu" rows="4" 
                class="w-full border border-gray-300 rounded p-2" 
                placeholder="Qu'avez-vous à partager ?"><?= htmlspecialchars($_POST['contenu'] ?? '') ?></textarea>
        </div>

        <div class="media-type-selector">
            <label class="form-label">Type de média :</label>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="type_media" id="type_image" value="image" checked>
                <label class="form-check-label" for="type_image">Image</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="type_media" id="type_video" value="video">
                <label class="form-check-label" for="type_video">Vidéo</label>
            </div>
        </div>

        <div>
            <label for="media" class="block font-medium mb-1">Image ou vidéo (jpeg, png, gif, mp4, webm, ogg)</label>
            <input type="file" id="media" name="media" accept="image/*,video/*" class="block w-full" />
        </div>

        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded font-semibold">
            Publier
        </button>
    </form>
</div>

<?php include_once(__DIR__ . "/../includes/footer.php"); ?>

</body>
</html>
