<?php
session_start();
include_once("../config/database.php");

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['gestionnaire_id'])) {
    header("Location: connexion.php");
    exit();
}

// Récupérer le type d'élément et l'ID
$type = $_GET['type'] ?? '';
$id = (int)($_GET['id'] ?? 0);
$gestionnaire_id = $_SESSION['gestionnaire_id'];
$user_type = $_SESSION['gestionnaire_type'];

// Vérifier les paramètres
if (empty($type) || $id <= 0) {
    $_SESSION['error'] = "Paramètres invalides";
    header("Location: liste.php");
    exit();
}

// Vérifier que le type est valide
$valid_types = ['lieu', 'circuit', 'hotel', 'evenement'];
if (!in_array($type, $valid_types)) {
    $_SESSION['error'] = "Type d'élément invalide";
    header("Location: liste.php");
    exit();
}

// Récupérer l'élément à modifier
$element = null;

switch ($type) {
    case 'lieu':
        $table = 'lieux';
        $id_field = 'id';
        break;
    case 'circuit':
        $table = 'circuits';
        $id_field = 'id';
        break;
    case 'hotel':
        $table = 'hotels';
        $id_field = 'id';
        break;
    case 'evenement':
        $table = 'evenements';
        $id_field = 'id'; 
        break;
    default:
        // Type non supporté
        throw new Exception("Type d'élément inconnu.");
}


try {
    $sql = "SELECT * FROM $table WHERE $id_field = ? AND gestionnaire_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id, $gestionnaire_id]);
    $element = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$element) {
        $_SESSION['error'] = "Élément introuvable ou accès non autorisé";
        header("Location: liste.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur lors de la récupération des données: " . $e->getMessage();
    header("Location: liste.php");
    exit();
}

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Ici, vous devrez ajouter la logique de validation et de mise à jour
        // des champs spécifiques à chaque type d'élément
        
        // Exemple pour un lieu
        if ($type === 'lieu') {
            $nom = $_POST['nom'] ?? '';
            $description = $_POST['description'] ?? '';
            
            $sql = "UPDATE lieux SET nom = ?, description = ? WHERE id = ? AND gestionnaire_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nom, $description, $id, $gestionnaire_id]);
        }
        // Ajouter les autres types (circuit, hotel) avec leurs champs spécifiques
        
        $_SESSION['success'] = "L'élément a été modifié avec succès";
        header("Location: liste.php");
        exit();
        
    } catch (PDOException $e) {
        $error = "Erreur lors de la modification: " . $e->getMessage();
    }
}

// Afficher le formulaire de modification
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier <?= ucfirst($type) ?> - Bénin Tourisme</title>
    <link rel="icon" type="image/svg+xml" href="../assets/favicon/favicon.svg">
    <link rel="icon" type="image/png" sizes="96x96" href="../assets/favicon/favicon-96x96.png">
    <link rel="shortcut icon" href="../assets/favicon/favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Styles existants de liste.php */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #f5f6fa;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        input[type="text"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        textarea {
            min-height: 150px;
            resize: vertical;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
        }

        .btn-secondary {
            background: #6c757d;
            margin-right: 10px;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
        }
        /* Styles du tableau de bord */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #f5f6fa;
            min-height: 100vh;
        }

        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #1a237e 0%, #0d47a1 100%);
            color: white;
            padding: 25px;
            position: fixed;
            height: 100vh;
            transition: all 0.3s ease;
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar-header {
            padding: 20px 0;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 30px;
        }

        .sidebar-header .logo {
            width: 50px;
            height: 50px;
            margin-bottom: 10px;
            border-radius: 10px;
        }

        .sidebar-header h2 {
            font-size: 24px;
            margin-bottom: 10px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-shadow: 0 0 5px rgba(255, 255, 255, 0.5);
        }

        .user-info {
            font-size: 14px;
            color: rgba(255,255,255,0.8);
            padding: 10px;
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            margin-top: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-menu {
            margin-top: 30px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            margin-bottom: 8px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
        }

        .nav-item:hover, .nav-item.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .nav-item i {
            width: 24px;
            text-align: center;
            margin-right: 10px;
            font-size: 18px;
        }

        .nav-item.logout {
            margin-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
            padding-top: 20px;
        }

        .logout-link {
            color: inherit;
            text-decoration: none;
            display: flex;
            align-items: center;
            width: 100%;
        }

        /* Main Content */
        .main-content {
            margin-left: 280px;
            padding: 30px;
            width: calc(100% - 280px);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 28px;
            font-weight: 600;
            color: #2c3e50;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            border: none;
            font-size: 14px;
        }

        .btn-primary {
            background: #1a237e;
            color: white;
        }

        .btn-primary:hover {
            background: #0d47a1;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(26, 35, 126, 0.2);
        }

        .btn i {
            margin-right: 8px;
        }

        /* Table Styles */
        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px 20px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 500;
        }

        .status.active {
            background: #e3fcef;
            color: #00a854;
        }

        .status.inactive {
            background: #fff2f0;
            color: #f5222d;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-icon {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            color: white;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-edit {
            background: #1890ff;
        }

        .btn-delete {
            background: #ff4d4f;
        }

        .btn-icon:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .no-items {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }

        /* Styles des onglets */
        .tabs {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .tabs-header {
            display: flex;
            border-bottom: 1px solid #eee;
            background: #f8f9fa;
            padding: 0 15px;
        }

        .tab-link {
            padding: 15px 20px;
            cursor: pointer;
            color: #666;
            font-weight: 500;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
            position: relative;
        }

        .tab-link:hover {
            color: #1a237e;
            background: rgba(26, 35, 126, 0.05);
        }

        .tab-link.active {
            color: #1a237e;
            border-bottom-color: #1a237e;
            font-weight: 600;
        }

        .tab-link i {
            font-size: 16px;
        }

        .badge {
            background: #e0e0e0;
            color: #333;
            border-radius: 10px;
            padding: 2px 8px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 5px;
        }

        .tab-link.active .badge {
            background: #1a237e;
            color: white;
        }

        .tab-pane {
            display: none;
            padding: 20px;
        }

        .tab-pane.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Inclure la sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="../assets/images/KWA.png" alt="logo" class="logo">
                <h2 style="color: white;">KWABO</h2>
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <?php echo htmlspecialchars($_SESSION['gestionnaire_nom']); ?>
                </div>
            </div>
            <nav class="nav-menu">
                <a href="tableau_bord.php" class="nav-item active">
                    <i class="fas fa-home"></i>
                    <span>Tableau de bord</span>
                </a>
                <a href="liste.php" class="nav-item">
                    <i class="fas fa-list"></i>
                    <span>Liste</span>
                </a>
                <a href="parametres.php" class="nav-item">
                    <i class="fas fa-cog"></i>
                    <span>Paramètres</span>
                </a>
                <a href="deconnexion.php" class="nav-item logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Déconnexion</span>
                </a>
            </nav>
        </div>

        
        <div class="main-content">
            <div class="container">
                <div class="page-header">
                    <h1 class="page-title">Modifier <?= ucfirst($type) ?></h1>
                    <a href="liste.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Retour à la liste
                    </a>
                </div>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-error"><?= $_SESSION['error'] ?></div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <div class="card">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($element['id'] ?? '') ?>">

                        <!-- Champs spécifiques au type d'élément -->
                        <?php if ($type === 'lieu'): ?>
                            <div class="form-group">
                                <label for="nom">Nom du lieu</label>
                                <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($element['nom'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea id="description" name="description" required><?= htmlspecialchars($element['description'] ?? '') ?></textarea>
                            </div>
                            <!-- Ajouter d'autres champs spécifiques aux lieux -->
                            
                        <?php elseif ($type === 'circuit'): ?>
                            <div class="form-group">
                                <label for="nom">Nom du circuit</label>
                                <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($element['nom'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea id="description" name="description" required><?= htmlspecialchars($element['description'] ?? '') ?></textarea>
                            </div>
                            <!-- Ajouter d'autres champs spécifiques aux circuits -->
                            
                        <?php elseif ($type === 'hotel'): ?>
                            <div class="form-group">
                                <label for="nom">Nom de l'hôtel</label>
                                <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($element['nom'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea id="description" name="description" required><?= htmlspecialchars($element['description'] ?? '') ?></textarea>
                            </div>
                            <!-- Ajouter d'autres champs spécifiques aux hôtels -->

                            <?php elseif ($type === 'evenement'): ?>
                            <div class="form-group">
                                <label for="nom">Nom de l'Evênement</label>
                                <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($element['nom'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea id="description" name="description" required><?= htmlspecialchars($element['description'] ?? '') ?></textarea>
                            </div>
                            <!-- Ajouter d'autres champs spécifiques aux évenements -->
                        <?php endif; ?>

                        <div class="form-group" style="margin-top: 30px;">
                            <button type="submit" class="btn">
                                <i class="fas fa-save"></i> Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
