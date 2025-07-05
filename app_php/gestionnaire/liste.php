<?php
session_start();
include_once("../config/database.php");

// Fonction pour afficher un tableau d'éléments
function renderItemsTable($items, $type, $emptyMessage = 'Aucun élément trouvé') {
    ?>
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-list"></i>
                <?= ucfirst($type) === 'lieu' ? 'Destinations' : ucfirst($type) . 's' ?>
            </h2>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Date d'ajout</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($items)): ?>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['nom']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($item['date_creation'])) ?></td>
                                    <td>
                                        <span class="status <?= $item['statut'] === 'actif' ? 'active' : 'inactive' ?>">
                                            <?= ucfirst($item['statut'] ?? 'inactif') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="edit.php?type=<?= $type ?>&id=<?= $item['id'] ?>" class="btn-icon btn-edit" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="supprimer.php?type=<?= $type ?>&id=<?= $item['id'] ?>" class="btn-icon btn-delete" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet élément ?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center;"><?= $emptyMessage ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
}

// Vérification si l'utilisateur est connecté
if (!isset($_SESSION['gestionnaire_id'])) {
    header("Location: connexion.php");
    exit();
}

$user_id = $_SESSION['gestionnaire_id'];
$user_type = $_SESSION['gestionnaire_type'];

// Titre de la page
$titre_page = 'Gestion des Éléments';

// Initialiser les tableaux vides
$lieux = [];
$circuits = [];
$hotels = [];
$evenements = [];

// Récupérer les éléments en fonction du type de compte
switch ($user_type) {
    case 'destination':
        $stmt = $pdo->prepare("SELECT *, 'lieu' as type, 'actif' as statut FROM lieux WHERE gestionnaire_id = ? ORDER BY date_creation DESC");
        $stmt->execute([$user_id]);
        $lieux = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $active_tab = 'lieux';
        break;
        
    case 'circuit':
        $stmt = $pdo->prepare("SELECT *, 'circuit' as type, 'actif' as statut FROM circuits WHERE gestionnaire_id = ? ORDER BY date_creation DESC");
        $stmt->execute([$user_id]);
        $circuits = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $active_tab = 'circuits';
        break;
        
    case 'hotel':
        $stmt = $pdo->prepare("SELECT *, 'hotel' as type, 'actif' as statut FROM hotels WHERE gestionnaire_id = ? ORDER BY date_creation DESC");
        $stmt->execute([$user_id]);
        $hotels = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $active_tab = 'hotels';
        break;
    
    case 'evenement':
        $stmt = $pdo->prepare("SELECT *, 'evenement' as type, 'actif' as statut FROM evenements WHERE gestionnaire_id = ? ORDER BY date_creation DESC");
        $stmt->execute([$user_id]);
        $evenements = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $active_tab = 'evenements';
        break;

    case 'admin':
        // Admin voit tout
        $stmt = $pdo->prepare("SELECT *, 'lieu' as type, 'actif' as statut FROM lieux WHERE gestionnaire_id = ? ORDER BY date_creation DESC");
        $stmt->execute([$user_id]);
        $lieux = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stmt = $pdo->prepare("SELECT *, 'circuit' as type, 'actif' as statut FROM circuits WHERE gestionnaire_id = ? ORDER BY date_creation DESC");
        $stmt->execute([$user_id]);
        $circuits = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stmt = $pdo->prepare("SELECT *, 'hotel' as type, 'actif' as statut FROM hotels WHERE gestionnaire_id = ? ORDER BY date_creation DESC");
        $stmt->execute([$user_id]);
        $hotels = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT *, 'evenement' as type, 'actif' as statut FROM evenements WHERE gestionnaire_id = ? ORDER BY date_creation DESC");
        $stmt->execute([$user_id]);
        $evenements = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $active_tab = $_GET['tab'] ?? 'lieux';
        break;
        
    default:
        // Par défaut, on ne charge rien
        $active_tab = '';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titre_page ?> - KWABO</title>
    <link rel="icon" type="image/svg+xml" href="../assets/favicon/favicon.svg">
    <link rel="icon" type="image/png" sizes="96x96" href="../assets/favicon/favicon-96x96.png">
    <link rel="shortcut icon" href="../assets/favicon/favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
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
                <div class="nav-item active">
                    <i class="fas fa-home"></i>
                    <span>Tableau de bord</span>
                </div>
                <a href="liste.php">
                    <div class="nav-item">
                    <i class="fas fa-list"></i>
                    <span>Liste</span>
                </div>  
                </a>
                <a href="parametres.php">
                    <div class="nav-item">
                    <i class="fas fa-cog"></i>
                    <span>Paramètres</span>
                </div>
                </a>
                <div class="nav-item logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <a href="deconnexion.php" class="logout-link">Déconnexion</a>
                </div>
            </nav>
        </div>


        <!-- Main Content -->
        <div class="main-content">
            <div class="page-header">
                <div>
                    <h1 class="page-title"><?= $titre_page ?></h1>
                    <p class="page-subtitle">Gérez tous vos éléments ajoutés</p>
                </div>
                <?php
                // Déterminer la page d'ajout en fonction du type de compte
                $add_page = '';
                switch($user_type) {
                    case 'destination':
                        $add_page = '../gestionnaire/destinations.php';
                        $btn_text = 'Ajouter une destination';
                        break;
                    case 'circuit':
                        $add_page = '../gestionnaire/circuits.php';
                        $btn_text = 'Ajouter un circuit';
                        break;
                    case 'hotel':
                        $add_page = '../gestionnaire/hotels.php';
                        $btn_text = 'Ajouter un hôtel';
                        break;

                    case 'evenement':
                        $add_page = '../gestionnaire/evenements.php';
                        $btn_text = 'Ajouter un évênement';
                        break;    
                    case 'admin':
                        $add_page = '#';
                        $btn_text = 'Sélectionnez un type';
                        break;
                }
                ?>
                <?php if($user_type !== 'admin'): ?>
                <a href="<?= $add_page ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> <?= $btn_text ?>
                </a>
                <?php endif; ?>
            </div>

            <div class="tabs">
                <div class="tabs-header">
                    <?php if (in_array($user_type, ['destination', 'admin'])): ?>
                    <a href="?tab=lieux" class="tab-link <?= $active_tab === 'lieux' ? 'active' : '' ?>" data-tab="lieux">
                        <i class="fas fa-map-marker-alt"></i>
                        Destinations
                        <span class="badge"><?= count($lieux) ?></span>
                    </a>
                    <?php endif; ?>
                    
                    <?php if (in_array($user_type, ['circuit', 'admin'])): ?>
                    <a href="?tab=circuits" class="tab-link <?= $active_tab === 'circuits' ? 'active' : '' ?>" data-tab="circuits">
                        <i class="fas fa-route"></i>
                        Circuits
                        <span class="badge"><?= count($circuits) ?></span>
                    </a>
                    <?php endif; ?>
                    
                    <?php if (in_array($user_type, ['hotel', 'admin'])): ?>
                    <a href="?tab=hotels" class="tab-link <?= $active_tab === 'hotels' ? 'active' : '' ?>" data-tab="hotels">
                        <i class="fas fa-hotel"></i>
                        Hôtels
                        <span class="badge"><?= count($hotels) ?></span>
                    </a>
                    <?php endif; ?>

                    <?php if (in_array($user_type, ['evenement', 'admin'])): ?>
                    <a href="?tab=evenemnets" class="tab-link <?= $active_tab === 'evenements' ? 'active' : '' ?>" data-tab="evenements">
                        <i class="fas fa-hotel"></i>
                        Evênements
                        <span class="badge"><?= count($evenements) ?></span>
                    </a>
                    <?php endif; ?>
                </div>

                <div class="tab-content">
                    <?php if (in_array($user_type, ['destination', 'admin'])): ?>
                    <!-- Onglet Destinations -->
                    <div id="lieux" class="tab-pane <?= $active_tab === 'lieux' ? 'active' : '' ?>">
                        <?php renderItemsTable($lieux, 'lieu', 'Aucune destination trouvée'); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (in_array($user_type, ['circuit', 'admin'])): ?>
                    <!-- Onglet Circuits -->
                    <div id="circuits" class="tab-pane <?= $active_tab === 'circuits' ? 'active' : '' ?>">
                        <?php renderItemsTable($circuits, 'circuit', 'Aucun circuit trouvé'); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (in_array($user_type, ['hotel', 'admin'])): ?>
                    <!-- Onglet Hôtels -->
                    <div id="hotels" class="tab-pane <?= $active_tab === 'hotels' ? 'active' : '' ?>">
                        <?php renderItemsTable($hotels, 'hotel', 'Aucun hôtel trouvé'); ?>
                    </div>
                    <?php endif; ?>

                    <?php if (in_array($user_type, ['evenement', 'admin'])): ?>
                    <!-- Onglet Evênements -->
                    <div id="evenements" class="tab-pane <?= $active_tab === 'evenements' ? 'active' : '' ?>">
                        <?php renderItemsTable($evenements, 'evenement', 'Aucun évênement trouvé'); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Fonctionnalité d'affichage/masquage des mots de passe
        document.querySelectorAll('.toggle-password').forEach(icon => {
            icon.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        });

        // Gestion des onglets
        document.querySelectorAll('.tab-link').forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                const tabId = this.getAttribute('data-tab');
                
                // Mettre à jour l'URL sans recharger la page
                history.pushState(null, '', `?tab=${tabId}`);
                
                // Mettre à jour les onglets actifs
                document.querySelectorAll('.tab-link').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                // Afficher le contenu de l'onglet sélectionné
                document.querySelectorAll('.tab-pane').forEach(pane => {
                    pane.classList.remove('active');
                });
                document.getElementById(tabId).classList.add('active');
            });
        });
    </script>
</body>
</html>
