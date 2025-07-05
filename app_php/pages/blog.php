<?php
session_start();
include_once("../config/database.php");

// Récupération des publications avec le nombre de likes
$stmt = $pdo->query("SELECT p.*, u.nom, u.prenom, 
                            (SELECT COUNT(*) FROM likes WHERE publication_id = p.id) as nb_likes,
                            (SELECT COUNT(*) FROM commentaires WHERE publication_id = p.id) as nb_comments
                     FROM publications p 
                     JOIN utilisateurs u ON p.user_id = u.id 
                     ORDER BY p.date_creation DESC");
$publications = $stmt->fetchAll();


// Pour chaque publication, vérifier si l'utilisateur connecté l'a déjà likée
$liked_posts = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT publication_id FROM likes WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    while ($row = $stmt->fetch()) {
        $liked_posts[] = $row['publication_id'];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Social</title>
    <link rel="icon" type="image/svg+xml" href="../assets/favicon/favicon.svg">
    <link rel="icon" type="image/png" sizes="96x96" href="../assets/favicon/favicon-96x96.png">
    <link rel="shortcut icon" href="../assets/favicon/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/css/lightbox.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary-color: #2F855A;
            --secondary-color: #DD6B20;
            --accent-color: #3498db;
        }

        @keyframes heartBeat {
            0% { transform: scale(1); }
            25% { transform: scale(1.3); }
            50% { transform: scale(1); }
            75% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .heart-beat {
            animation: heartBeat 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .new-post {
            animation: fadeIn 0.5s ease-out;
        }
        
        .custom-textarea {
            resize: none;
            scrollbar-width: thin;
            scrollbar-color: #4b5563 #1f2937;
        }
        
        .custom-textarea::-webkit-scrollbar {
            width: 6px;
        }
        
        .custom-textarea::-webkit-scrollbar-thumb {
            background-color: #4b5563;
            border-radius: 3px;
        }
        
        .btn-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .transition-all {
            transition: all 0.3s ease;
        }
        
        /* Style pour le menu de partage */
        .share-menu {
            position: absolute;
            right: 0;
            top: 100%;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 10;
            width: 200px;
            padding: 8px 0;
            margin-top: 8px;
        }
        
        .share-option {
            display: flex;
            align-items: center;
            padding: 8px 16px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .share-option:hover {
            background-color: #f3f4f6;
        }
        
        .share-option i {
            margin-right: 8px;
            width: 20px;
            text-align: center;
        }
        
        .share-option.whatsapp {
            color: #25D366;
        }
        
        .share-option.facebook {
            color: #1877F2;
        }
        
        .share-option.twitter {
            color: #1DA1F2;
        }
        
        .share-option.link {
            color: #4B5563;
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: #333;
        }

        /* Styles spécifiques au blog */
        .custom-textarea {
            resize: none;
            scrollbar-width: thin;
            scrollbar-color: #4b5563 #1f2937;
        }
        
        .custom-textarea::-webkit-scrollbar {
            width: 6px;
        }
        
        .custom-textarea::-webkit-scrollbar-thumb {
            background-color: #4b5563;
            border-radius: 3px;
        }

        .btn-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .transition-all {
            transition: all 0.3s ease;
        }
        

        

        /* Style pour le lien actif */
        .bg-gray-100 {
            background-color: #F3F4F6 !important;
        }

    

        .btn-primary {
            background: var(--secondary-color);
            border: none;
            padding: 12px 30px;
            border-radius: 30px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-primary:hover {
            background: var(--primary-color);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }

        .btn-outline-primary {
            border: 2px solid var(--secondary-color);
            color: var(--secondary-color);
            padding: 12px 30px;
            border-radius: 30px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-outline-primary:hover {
            background: var(--secondary-color);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }

        .section-title {
            position: relative;
            margin-bottom: 50px;
            text-align: center;
            color: var(--primary-color);
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--secondary-color);
            border-radius: 2px;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .new-post {
            animation: fadeIn 0.5s ease-out;
        }

        .comments-section {
            transition: opacity 0.3s ease, max-height 0.3s ease;
            opacity: 1;
            max-height: 1000px;
            overflow: hidden;
        }

        .comments-section.hidden {
            opacity: 0;
            max-height: 0;
        }
    </style>
</head>
<?php include_once("../includes/navbar.php"); ?> 
<body class="bg-gray-100 min-h-screen">
    <!-- Contenu principal -->
    <div class="container mx-auto px-4 py-8">
        <!-- Messages d'erreur et de succès -->
        <?php if (isset($_SESSION['error_messages'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <?php foreach ($_SESSION['error_messages'] as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                
    <div class="mt-3">
        <!-- Bouton Like -->
        <form method="post" onsubmit="event.preventDefault(); likerPublication(<?= $publication['id'] ?>);">
            <button id="like-btn-<?= $publication['id'] ?>" class="btn btn-outline-danger">
                ❤️ J'aime
            </button>
            <span id="like-count-<?= $publication['id'] ?>"><?= $publication['nb_likes'] ?> likes</span>
        </form>

        <!-- Commentaires existants -->
        <div id="commentaires-<?= $publication['id'] ?>" class="mt-2 mb-2">
            <?php
            $stmt_comments = $pdo->prepare("SELECT c.*, u.nom, u.prenom FROM commentaires c JOIN utilisateurs u ON c.user_id = u.id WHERE publication_id = ? ORDER BY date_creation ASC");
            $stmt_comments->execute([$publication['id']]);
            while ($comment = $stmt_comments->fetch()):
            ?>
                <div class="border p-2 mb-1">
                    <strong><?= htmlspecialchars($comment['nom']) ?> <?= htmlspecialchars($comment['prenom']) ?>:</strong>
                    <?= nl2br(htmlspecialchars($comment['contenu'])) ?>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Formulaire de commentaire -->
        <form method="post" onsubmit="event.preventDefault(); commenterPublication(<?= $publication['id'] ?>);">
            <div class="input-group">
                <input type="text" id="commentaire-input-<?= $publication['id'] ?>" class="form-control" placeholder="Votre commentaire...">
                <button class="btn btn-primary" type="submit">Commenter</button>
            </div>
        </form>
    </div>

</div>
<?php endforeach; ?>
            </div>
            <?php unset($_SESSION['error_messages']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <p><?= htmlspecialchars($_SESSION['success_message']) ?></p>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Colonne principale -->
            <div class="w-full lg:w-2/3">
                <!-- Formulaire de publication -->
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                        <h2 class="text-2xl font-bold mb-4">Créer une publication</h2>
                        <form action="publier.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                            <div>
                                <label for="titre" class="block text-sm font-medium text-gray-700">Titre</label>
                                <input type="text" name="titre" id="titre" required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                            </div>
                            <div>
                                <label for="contenu" class="block text-sm font-medium text-gray-700">Contenu</label>
                                <textarea name="contenu" id="contenu" rows="4" required
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"></textarea>
                            </div>
                            <div>
                                <label for="media" class="block text-sm font-medium text-gray-700">Média (image ou vidéo)</label>
                                <input type="file" name="media" id="media" accept="image/,video/"
                                       class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100">
                                <p class="mt-1 text-sm text-gray-500">Formats acceptés : JPG, PNG, GIF, MP4, WEBM, OGG</p>
                            </div>
                            <button type="submit" class="w-full bg-orange-500 text-white py-2 px-4 rounded-md hover:bg-orange-600 transition duration-200">
                                Publier
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <a href="connexion.php" class="btn-reserve block w-full bg-gray-100 hover:bg-sky-700 text-white font-bold py-3 px-4 rounded-lg text-center transition duration-300">
                    <p class="text-sm text-gray-500 text-center mb-4">Connectez-vous pour publier un poste</p></a>
                                        
                <?php endif; ?>

                <!-- Liste des publications -->
                <div class="space-y-6">
                    <?php foreach ($publications as $publication): ?>
                        <div class="card p-3 mt-4 shadow-sm">
                            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                                <div class="flex items-center mb-4">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center text-white font-bold">
                                        <?= strtoupper(substr($publication['prenom'], 0, 1) . substr($publication['nom'], 0, 1)) ?>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">
                                            <?= htmlspecialchars($publication['prenom']) ?> <?= htmlspecialchars($publication['nom']) ?>
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            <?= date('d/m/Y H:i', strtotime($publication['date_creation'])) ?>
                                        </p>
                                    </div>
                                </div>
                                <h3 class="text-xl font-semibold mb-2"><?= htmlspecialchars($publication['titre']) ?></h3>
                                <p class="text-gray-700 mb-4"><?= nl2br(htmlspecialchars($publication['contenu'])) ?></p>
                                
                                <?php if ($publication['media_path']): ?>
                                    <?php if ($publication['media_type'] === 'image'): ?>
                                        <div class="relative group">
                                            <img src="<?= htmlspecialchars($publication['media_path']) ?>" 
                                                 alt="Image de la publication" 
                                                 class="rounded-lg max-h-96 w-full object-cover mb-4 cursor-pointer transition-transform duration-300 group-hover:scale-[1.02]"
                                                 onclick="openLightbox(this.src)"
                                                 onerror="console.error('Erreur de chargement de l\'image:', this.src)">
                                        </div>
                                    <?php elseif ($publication['media_type'] === 'video'): ?>
                                        <div class="relative rounded-lg overflow-hidden mb-4">
                                            <video controls class="w-full max-h-96 rounded-lg">
                                                <source src="../<?= htmlspecialchars($publication['media_path']) ?>" 
                                                        type="video/mp4"
                                                        onerror="console.error('Erreur de chargement de la vidéo:', this.src)">
                                                Votre navigateur ne supporte pas la lecture de vidéos.
                                            </video>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <div class="mt-4 pt-4 border-t border-gray-100">
                                    <div class="flex justify-between items-center">
                                        <div class="flex space-x-4">
                                            <!-- Bouton Like -->
                                            <form method="post" onsubmit="event.preventDefault(); likerPublication(<?= $publication['id'] ?>);">
                                                <button id="like-btn-<?= $publication['id'] ?>" class="btn btn-outline-danger">
                                                    ❤️ J'aime
                                                </button>
                                                <span id="like-count-<?= $publication['id'] ?>"><?= $publication['nb_likes'] ?> likes</span>
                                            </form>

                                            <!-- Bouton Commentaire -->
                                            <button class="flex items-center space-x-1 text-gray-500 hover:text-indigo-600 comment-btn transition-all">
                                                <i class="far fa-comment text-xl"></i>
                                                <span><?= $publication['nb_comments'] ?? 0 ?></span>
                                            </button>
                                        </div>

                                        <!-- Menu de partage -->
                                        <div class="relative">
                                            <button class="text-gray-500 hover:text-indigo-600 share-btn transition-all" 
                                                    onclick="toggleShareMenu(<?= $publication['id'] ?>)">
                                                <i class="far fa-share-square text-xl"></i>
                                            </button>
                                            <div id="share-menu-<?= $publication['id'] ?>" class="share-menu hidden">
                                                <div class="share-option" onclick="copyLink(<?= $publication['id'] ?>)">
                                                    <i class="fas fa-link"></i>
                                                    <span>Copier le lien</span>
                                                </div>
                                                <div class="share-option whatsapp" onclick="shareWhatsApp(<?= $publication['id'] ?>)">
                                                    <i class="fab fa-whatsapp"></i>
                                                    <span>WhatsApp</span>
                                                </div>
                                                <div class="share-option facebook" onclick="shareFacebook(<?= $publication['id'] ?>)">
                                                    <i class="fab fa-facebook"></i>
                                                    <span>Facebook</span>
                                                </div>
                                                <div class="share-option twitter" onclick="shareTwitter(<?= $publication['id'] ?>)">
                                                    <i class="fab fa-twitter"></i>
                                                    <span>Twitter</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Section commentaires -->
                                    <div class="comments-section mt-4">
                                        <div class="space-y-4">
                                            <!-- Liste des commentaires existants -->
                                            <div id="commentaires-<?= $publication['id'] ?>" class="mt-2 mb-2">
                                                <?php
                                                $stmt_comments = $pdo->prepare("SELECT c.*, u.nom, u.prenom FROM commentaires c JOIN utilisateurs u ON c.user_id = u.id WHERE publication_id = ? ORDER BY date_creation ASC");
                                                $stmt_comments->execute([$publication['id']]);
                                                while ($comment = $stmt_comments->fetch()):
                                                ?>
                                                    <div class="bg-gray-50 p-3 rounded-lg mb-2">
                                                        <div class="flex items-center mb-2">
                                                            <div class="w-8 h-8 rounded-full bg-gradient-to-r from-green-500 to-blue-500 flex items-center justify-center text-white font-bold text-sm">
                                                                <?= strtoupper(substr($comment['prenom'], 0, 1) . substr($comment['nom'], 0, 1)) ?>
                                                            </div>
                                                            <div class="ml-2">
                                                                <strong class="text-sm font-medium text-gray-900">
                                                                    <?= htmlspecialchars($comment['nom']) ?> <?= htmlspecialchars($comment['prenom']) ?>
                                                                </strong>
                                                                <span class="text-xs text-gray-500 ml-2">
                                                                    <?= date('d/m/Y H:i', strtotime($comment['date_creation'])) ?>
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <p class="text-gray-700 text-sm ml-10">
                                                            <?= nl2br(htmlspecialchars($comment['contenu'])) ?>
                                                        </p>
                                                    </div>
                                                <?php endwhile; ?>
                                            </div>

                                            <!-- Formulaire de commentaire -->
                                            <?php if (isset($_SESSION['user_id'])): ?>
                                                <form method="post" onsubmit="event.preventDefault(); commenterPublication(<?= $publication['id'] ?>);" class="mt-4">
                                                    <div class="flex items-start space-x-3">
                                                        <div class="w-8 h-8 rounded-full bg-gradient-to-r from-orange-500 to-red-500 flex items-center justify-center text-white font-bold text-sm">
                                                            <?= strtoupper(substr($_SESSION['prenom'] ?? 'U', 0, 1) . substr($_SESSION['nom'] ?? 'S', 0, 1)) ?>
                                                        </div>
                                                        <div class="flex-1">
                                                            <input type="text" 
                                                                   id="commentaire-input-<?= $publication['id'] ?>" 
                                                                   class="w-full px-3 py-2 text-sm bg-gray-50 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                                                   placeholder="Ajouter un commentaire...">
                                                            <div class="flex justify-end mt-2">
                                                                <button type="submit" 
                                                                        class="px-4 py-1 text-sm font-medium text-white bg-blue-500 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                                                    Commenter
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            <?php else: ?>
                                                <a href="connexion.php" class="block text-center">
                                                    <p class="text-sm text-gray-500">Connectez-vous pour commenter et liker</p>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Événements -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="font-bold text-lg mb-4">Événements à venir</h2>
                <div class="space-y-4">
                    <div class="flex items-start space-x-3">
                        <div class="bg-indigo-100 text-indigo-800 p-2 rounded-lg text-center min-w-12">
                            <p class="font-bold text-lg">15</p>
                            <p class="text-xs">JUIN</p>
                        </div>
                        <div>
                            <h4 class="font-medium">Festichill</h4>
                            <p class="text-gray-500 text-sm">Cotonou, BENIN</p>
                            <button class="text-indigo-600 hover:text-indigo-800 text-sm font-medium mt-1">
                                Plus d'infos
                            </button>
                        </div>
                    </div>
                    <div class="flex items-start space-x-3">
                        <div>
                            <a href="gestion.php" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium mt-1">Ajouter Evènements</a>
                            <br>
                                Plus d'infos <br>
                                NB:Pour pouvoir ajouter des évènements veuillez vous inscrire en tant que gestionnaire
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include_once("../includes/footer.php"); ?>
    <script>
        // Fonctionnalités du blog
        document.addEventListener('DOMContentLoaded', function() {
            // Basculer entre thème clair/sombre
            const themeToggle = document.getElementById('theme-toggle');
            themeToggle.addEventListener('click', function() {
                document.documentElement.classList.toggle('dark');
                const icon = themeToggle.querySelector('i');
                if (document.documentElement.classList.contains('dark')) {
                    icon.classList.replace('fa-moon', 'fa-sun');
                    document.body.classList.add('bg-gray-900');
                    document.body.classList.remove('bg-gray-100');
                } else {
                    icon.classList.replace('fa-sun', 'fa-moon');
                    document.body.classList.remove('bg-gray-900');
                    document.body.classList.add('bg-gray-100');
                }
            });

            // Gestion des médias (photos/vidéos)
            const addPhotoBtn = document.getElementById('add-photo');
            const addVideoBtn = document.getElementById('add-video');
            const mediaPreview = document.getElementById('media-preview');
            const previewImage = document.getElementById('preview-image');
            const previewVideo = document.getElementById('preview-video');
            const removeMediaBtn = document.getElementById('remove-media');
            const publishPostBtn = document.getElementById('publish-post');
            const postContent = document.getElementById('post-content');
            
            let currentMediaType = null;
            let currentMediaFile = null;

            addPhotoBtn.addEventListener('click', function() {
                const input = document.createElement('input');
                input.type = 'file';
                input.accept = 'image/*';
                input.onchange = function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        currentMediaType = 'image';
                        currentMediaFile = file;
                        const reader = new FileReader();
                        reader.onload = function(event) {
                            previewImage.src = event.target.result;
                            previewImage.classList.remove('hidden');
                            previewVideo.classList.add('hidden');
                            mediaPreview.classList.remove('hidden');
                        };
                        reader.readAsDataURL(file);
                    }
                };
                input.click();
            });

            addVideoBtn.addEventListener('click', function() {
                const input = document.createElement('input');
                input.type = 'file';
                input.accept = 'video/*';
                input.onchange = function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        currentMediaType = 'video';
                        currentMediaFile = file;
                        const reader = new FileReader();
                        reader.onload = function(event) {
                            previewVideo.src = event.target.result;
                            previewVideo.classList.remove('hidden');
                            previewImage.classList.add('hidden');
                            mediaPreview.classList.remove('hidden');
                        };
                        reader.readAsDataURL(file);
                    }
                };
                input.click();
            });

            removeMediaBtn.addEventListener('click', function() {
                mediaPreview.classList.add('hidden');
                previewImage.classList.add('hidden');
                previewVideo.classList.add('hidden');
                currentMediaType = null;
                currentMediaFile = null;
            });

            // Publication d'un nouveau post
            publishPostBtn.addEventListener('click'), function() {
                const content = postContent.value.trim();
                if (!content && !currentMediaFile) return;

                const postsContainer = document.getElementById('posts-container');
                const newPost = document.createElement('div');
                newPost.className = 'bg-white rounded-lg shadow-md p-6 mb-6 new-post';
                
                // Date actuelle formatée
                const now = new Date();
                const options = { hour: '2-digit', minute: '2-digit' };
                const timeString = now.toLocaleTimeString('fr-FR', options);
                
                newPost.innerHTML = `
                    <div class="flex items
                            // Ajouter le commentaire au début de la liste
                            const commentsList = commentsSection.querySelector('.space-y-4');
                            commentsList.insertBefore(newComment, commentsList.firstChild);

                            // Réinitialiser le formulaire
                            form.querySelector('textarea').value = '';

                            // Mettre à jour le compteur de commentaires
                            const commentCount = form.closest('.bg-white').querySelector('.comment-btn span');
                            commentCount.textContent = parseInt(commentCount.textContent) + 1;
                        } else {
                            alert(data.message || 'Une erreur est survenue');
                        }
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        alert('Erreur lors de l\'envoi du commentaire');
                    });
                }
            });

            // Ajuster automatiquement la hauteur du textarea
            document.querySelectorAll('.comment-form textarea').forEach(textarea => {
                textarea.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = (this.scrollHeight) + 'px';
                });
            });
        });
    </script>
</body>

<script>
    // Fonctions pour les likes et commentaires
    function likerPublication(pubId) {
        fetch('like.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'publication_id=' + pubId
        })
        .then(res => res.text())
        .then(data => {
            document.getElementById('like-count-' + pubId).innerText = data + ' likes';
        });
    }

    function commenterPublication(pubId) {
    const input = document.getElementById('commentaire-input-' + pubId);
    const contenu = input.value;
    if (!contenu.trim()) return;

    fetch('commentaire.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'publication_id=' + pubId + '&contenu=' + encodeURIComponent(contenu)
    })
    .then(res => res.text())
    .then(html => {
        document.getElementById('commentaires-' + pubId).innerHTML += html;
        input.value = '';
    });
}

// Fonctions pour le partage
function toggleShareMenu(publicationId) {
    const menu = document.getElementById(`share-menu-${publicationId}`);
    menu.classList.toggle('hidden');
}

function copyLink(publicationId) {
    const url = `${window.location.origin}${window.location.pathname}?publication=${publicationId}`;
    navigator.clipboard.writeText(url).then(() => {
        alert('Lien copié dans le presse-papiers !');
    }).catch(err => {
        console.error('Erreur lors de la copie du lien:', err);
    });
}

function shareWhatsApp(publicationId) {
    const url = `${window.location.origin}${window.location.pathname}?publication=${publicationId}`;
    const text = encodeURIComponent('Regardez cette publication !');
    window.open(`https://wa.me/?text=${text}%20${url}`, '_blank');
}

function shareFacebook(publicationId) {
    const url = `${window.location.origin}${window.location.pathname}?publication=${publicationId}`;
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`, '_blank');
}

function shareTwitter(publicationId) {
    const url = `${window.location.origin}${window.location.pathname}?publication=${publicationId}`;
    const text = encodeURIComponent('Regardez cette publication !');
    window.open(`https://twitter.com/intent/tweet?text=${text}&url=${encodeURIComponent(url)}`, '_blank');
}

// Fermer les menus de partage quand on clique ailleurs
document.addEventListener('click', function(e) {
    if (!e.target.closest('.share-btn')) {
        document.querySelectorAll('.share-menu').forEach(menu => {
            menu.classList.add('hidden');
        });
    }
});

// Fonction pour ouvrir l'image en plein écran
function openLightbox(imageSrc) {
    const lightbox = document.createElement('div');
    lightbox.className = 'fixed inset-0 bg-black bg-opacity-90 z-50 flex items-center justify-center';
    lightbox.onclick = () => lightbox.remove();
    
    const img = document.createElement('img');
    img.src = imageSrc;
    img.className = 'max-h-[90vh] max-w-[90vw] object-contain';
    
    lightbox.appendChild(img);
    document.body.appendChild(lightbox);
}

// Empêcher la propagation du clic sur l'image
document.addEventListener('click', function(e) {
    if (e.target.tagName === 'IMG' && e.target.closest('.group')) {
        e.stopPropagation();
    }
});
function likerPublication(pubId) {
    fetch('like.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'publication_id=' + encodeURIComponent(pubId)
    })
    .then(res => {
        if (!res.ok) throw new Error('Erreur réseau');
        return res.text();
    })
    .then(data => {
        document.getElementById('like-count-' + pubId).innerText = data + ' likes';
    })
    .catch(error => {
        alert('Erreur lors du like : ' + error.message);
    });
}

</script>
</body>
</html>