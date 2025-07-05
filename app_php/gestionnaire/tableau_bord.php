<?php
session_start();
include_once("../config/database.php");

// Vérification si l'utilisateur est connecté
if (!isset($_SESSION['gestionnaire_id'])) {
    header("Location: connexion.php");
    exit();
}

// Vérification si le compte est validé par l'admin
try {
    $stmt = $pdo->prepare("SELECT statut_paiement FROM gestionnaires WHERE id = ?");
    $stmt->execute([$_SESSION['gestionnaire_id']]);
    $statut_paiement = $stmt->fetchColumn();
    
    if ($statut_paiement !== 'valide') {
        $_SESSION['error_message'] = "Votre compte n'est pas encore validé. Veuillez patienter.";
        header("Location: connexion.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Erreur lors de la vérification du compte. Veuillez réessayer.";
    header("Location: ../pages/connexion.php");
    exit();
}

$page_title = "Tableau de Bord - KWABO";

// Récupération des informations de l'utilisateur
$user_id = $_SESSION['gestionnaire_id'];
$user_type = $_SESSION['gestionnaire_type'];

// Récupération des statistiques selon le type de compte
try {
    switch($user_type) {
        case 'destination':
            // Total des destinations
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM lieux WHERE gestionnaire_id = ?");
            $stmt->execute([$user_id]);
            $total_items = $stmt->fetchColumn();
            
            // Destinations ajoutées ce mois
            $stmt_mois = $pdo->prepare("
                SELECT COUNT(*) 
                FROM lieux 
                WHERE gestionnaire_id = ? 
                AND MONTH(date_creation) = MONTH(CURRENT_DATE())
                AND YEAR(date_creation) = YEAR(CURRENT_DATE())
            ");
            $stmt_mois->execute([$user_id]);
            $items_mois = $stmt_mois->fetchColumn();

            // Récupération des vues totales
            $stmt_vues = $pdo->prepare("
                SELECT COUNT(*) as total_vues 
                FROM vues v
                INNER JOIN lieux d ON v.element_id = d.id
                WHERE d.gestionnaire_id = ? 
                AND v.element_type = 'destination'
            ");
            $stmt_vues->execute([$user_id]);
            $total_vues = $stmt_vues->fetchColumn() ?: 0;

            // Récupération des vues du mois
            $stmt_vues_mois = $pdo->prepare("
                SELECT COUNT(*) as vues_mois 
                FROM vues v
                INNER JOIN lieux d ON v.element_id = d.id
                WHERE d.gestionnaire_id = ? 
                AND v.element_type = 'destination'
                AND MONTH(v.date_vue) = MONTH(CURRENT_DATE())
                AND YEAR(v.date_vue) = YEAR(CURRENT_DATE())
            ");
            $stmt_vues_mois->execute([$user_id]);
            $vues_mois = $stmt_vues_mois->fetchColumn() ?: 0;

            // Top 3 des destinations les plus vues
            $stmt_top = $pdo->prepare("
                SELECT d.nom, COUNT(v.id) as nombre_vues 
                FROM lieux d
                LEFT JOIN vues v ON v.element_id = d.id AND v.element_type = 'destination'
                WHERE d.gestionnaire_id = ?
                GROUP BY d.id, d.nom
                ORDER BY nombre_vues DESC 
                LIMIT 3
            ");
            $stmt_top->execute([$user_id]);
            $top_items = $stmt_top->fetchAll(PDO::FETCH_ASSOC);
            break;

        case 'circuit':
            // Total des circuits
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM circuits WHERE gestionnaire_id = ?");
            $stmt->execute([$user_id]);
            $total_items = $stmt->fetchColumn();
            
            // Circuits ajoutés ce mois
            $stmt_mois = $pdo->prepare("
                SELECT COUNT(*) 
                FROM circuits 
                WHERE gestionnaire_id = ? 
                AND MONTH(date_creation) = MONTH(CURRENT_DATE())
                AND YEAR(date_creation) = YEAR(CURRENT_DATE())
            ");
            $stmt_mois->execute([$user_id]);
            $items_mois = $stmt_mois->fetchColumn();

            // Récupération des vues totales
            $stmt_vues = $pdo->prepare("
                SELECT COUNT(*) as total_vues 
                FROM vues v
                INNER JOIN circuits c ON v.element_id = c.id
                WHERE c.gestionnaire_id = ? 
                AND v.element_type = 'circuit'
            ");
            $stmt_vues->execute([$user_id]);
            $total_vues = $stmt_vues->fetchColumn() ?: 0;

            // Récupération des vues du mois
            $stmt_vues_mois = $pdo->prepare("
                SELECT COUNT(*) as vues_mois 
                FROM vues v
                INNER JOIN circuits c ON v.element_id = c.id
                WHERE c.gestionnaire_id = ? 
                AND v.element_type = 'circuit'
                AND MONTH(v.date_vue) = MONTH(CURRENT_DATE())
                AND YEAR(v.date_vue) = YEAR(CURRENT_DATE())
            ");
            $stmt_vues_mois->execute([$user_id]);
            $vues_mois = $stmt_vues_mois->fetchColumn() ?: 0;

            // Top 3 des circuits les plus vus
            $stmt_top = $pdo->prepare("
                SELECT c.nom, COUNT(v.id) as nombre_vues 
                FROM circuits c
                LEFT JOIN vues v ON v.element_id = c.id AND v.element_type = 'circuit'
                WHERE c.gestionnaire_id = ?
                GROUP BY c.id, c.nom
                ORDER BY nombre_vues DESC 
                LIMIT 3
            ");
            $stmt_top->execute([$user_id]);
            $top_items = $stmt_top->fetchAll(PDO::FETCH_ASSOC);
            break;

        case 'hotel':
            // Total des hôtels
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM hotels WHERE gestionnaire_id = ?");
            $stmt->execute([$user_id]);
            $total_items = $stmt->fetchColumn();
            
            // Hôtels ajoutés ce mois
            $stmt_mois = $pdo->prepare("
                SELECT COUNT(*) 
                FROM hotels 
                WHERE gestionnaire_id = ? 
                AND MONTH(date_creation) = MONTH(CURRENT_DATE())
                AND YEAR(date_creation) = YEAR(CURRENT_DATE())
            ");
            $stmt_mois->execute([$user_id]);
            $items_mois = $stmt_mois->fetchColumn();

            // Récupération des vues totales
            $stmt_vues = $pdo->prepare("
                SELECT COUNT(*) as total_vues 
                FROM vues v
                INNER JOIN hotels h ON v.element_id = h.id
                WHERE h.gestionnaire_id = ? 
                AND v.element_type = 'hotel'
            ");
            $stmt_vues->execute([$user_id]);
            $total_vues = $stmt_vues->fetchColumn() ?: 0;

            // Récupération des vues du mois
            $stmt_vues_mois = $pdo->prepare("
                SELECT COUNT(*) as vues_mois 
                FROM vues v
                INNER JOIN hotels h ON v.element_id = h.id
                WHERE h.gestionnaire_id = ? 
                AND v.element_type = 'hotel'
                AND MONTH(v.date_vue) = MONTH(CURRENT_DATE())
                AND YEAR(v.date_vue) = YEAR(CURRENT_DATE())
            ");
            $stmt_vues_mois->execute([$user_id]);
            $vues_mois = $stmt_vues_mois->fetchColumn() ?: 0;

            // Top 3 des hôtels les plus vus
            $stmt_top = $pdo->prepare("
                SELECT h.nom, COUNT(v.id) as nombre_vues 
                FROM hotels h
                LEFT JOIN vues v ON v.element_id = h.id AND v.element_type = 'hotel'
                WHERE h.gestionnaire_id = ?
                GROUP BY h.id, h.nom
                ORDER BY nombre_vues DESC 
                LIMIT 3
            ");
            $stmt_top->execute([$user_id]);
            $top_items = $stmt_top->fetchAll(PDO::FETCH_ASSOC);
            break;

        case 'evenement':
            // Total des événements
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM evenements WHERE gestionnaire_id = ?");
            $stmt->execute([$user_id]);
            $total_items = $stmt->fetchColumn();
            
            // Événements ajoutés ce mois
            $stmt_mois = $pdo->prepare("
                SELECT COUNT(*) 
                FROM evenements 
                WHERE gestionnaire_id = ? 
                AND MONTH(date_creation) = MONTH(CURRENT_DATE())
                AND YEAR(date_creation) = YEAR(CURRENT_DATE())
            ");
            $stmt_mois->execute([$user_id]);
            $items_mois = $stmt_mois->fetchColumn();

            // Récupération des vues totales
            $stmt_vues = $pdo->prepare("
                SELECT COUNT(*) as total_vues 
                FROM vues v
                INNER JOIN evenements e ON v.element_id = e.id
                WHERE e.gestionnaire_id = ? 
                AND v.element_type = 'evenement'
            ");
            $stmt_vues->execute([$user_id]);
            $total_vues = $stmt_vues->fetchColumn() ?: 0;

            // Récupération des vues du mois
            $stmt_vues_mois = $pdo->prepare("
                SELECT COUNT(*) as vues_mois 
                FROM vues v
                INNER JOIN evenements e ON v.element_id = e.id
                WHERE e.gestionnaire_id = ? 
                AND v.element_type = 'evenement'
                AND MONTH(v.date_vue) = MONTH(CURRENT_DATE())
                AND YEAR(v.date_vue) = YEAR(CURRENT_DATE())
            ");
            $stmt_vues_mois->execute([$user_id]);
            $vues_mois = $stmt_vues_mois->fetchColumn() ?: 0;

            // Top 3 des événements les plus vus
            $stmt_top = $pdo->prepare("
                SELECT e.nom, COUNT(v.id) as nombre_vues 
                FROM evenements e
                LEFT JOIN vues v ON v.element_id = e.id AND v.element_type = 'evenement'
                WHERE e.gestionnaire_id = ?
                GROUP BY e.id, e.nom
                ORDER BY nombre_vues DESC 
                LIMIT 3
            ");
            $stmt_top->execute([$user_id]);
            $top_items = $stmt_top->fetchAll(PDO::FETCH_ASSOC);
            break;
    }
} catch (PDOException $e) {
    $total_items = 0;
    $total_vues = 0;
    $vues_mois = 0;
    $items_mois = 0;
    $top_items = [];
    error_log("Erreur lors de la récupération des statistiques : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="icon" type="image/svg+xml" href="../assets/favicon/favicon.svg">
    <link rel="icon" type="image/png" sizes="96x96" href="../assets/favicon/favicon-96x96.png">
    <link rel="shortcut icon" href="../assets/favicon/favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1a237e;
            --primary-dark: #0d47a1;
            --primary-light: #e8eaf6;
            --secondary-color: #4caf50;
            --secondary-dark: #388e3c;
            --text-color: #333;
            --text-light: #666;
            --bg-color: #f5f6fa;
            --card-bg: #ffffff;
            --border-radius: 12px;
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
            --shadow-md: 0 5px 15px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.15);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            min-height: 100vh;
            line-height: 1.6;
        }

        /* Dashboard Layout */
        .dashboard {
            display: flex;
            min-height: 100vh;
            flex-direction: column;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 100%;
            background: linear-gradient(180deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 15px;
            position: fixed;
            bottom: 0;
            left: 0;
            z-index: 1000;
            display: flex;
            justify-content: space-around;
            align-items: center;
            height: 70px;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar-header {
            display: none;
        }

        .nav-menu {
            display: flex;
            width: 100%;
            justify-content: space-around;
        }

        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 8px 10px;
            border-radius: var(--border-radius);
            transition: var(--transition);
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 12px;
            width: auto;
        }

        .nav-item i {
            margin-bottom: 5px;
            font-size: 16px;
        }

        .nav-item span {
            display: block;
            font-size: 10px;
        }

        .nav-item.active, .nav-item:hover {
            background: rgba(255, 255, 255, 0.15);
            color: white;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 20px;
            margin-bottom: 70px;
        }

        .header {
            background: var(--card-bg);
            padding: 20px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .welcome-text h1 {
            font-size: 22px;
            text-align: center;
            color: var(--primary-color);
            margin-bottom: 5px;
        }

        .welcome-text p {
            text-align: center;
            color: var(--text-light);
        }

        .user-badge {
            display: inline-block;
            padding: 5px 10px;
            background: var(--primary-light);
            color: var(--primary-color);
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            margin: 10px auto;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: var(--card-bg);
            padding: 20px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .stat-card h3 {
            color: var(--text-light);
            font-size: 16px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .stat-card h3 i {
            color: var(--primary-color);
            font-size: 18px;
        }

        .stat-card .number {
            font-size: 28px;
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 10px;
        }

        .stat-card .trend {
            font-size: 14px;
            color: var(--secondary-color);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            width: 100%;
            position: relative;
        }

        .btn {
            padding: 10px 20px;
            border-radius: var(--border-radius);
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26, 35, 126, 0.3);
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
            padding: 10px 0;
            min-width: 200px;
            display: none;
            z-index: 100;
            margin-top: 5px;
        }

        .dropdown-menu.show {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        .dropdown-item {
            padding: 10px 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text-color);
            text-decoration: none;
            transition: var(--transition);
        }

        .dropdown-item:hover {
            background: var(--primary-light);
            color: var(--primary-dark);
        }

        .top-items-section {
            background: var(--card-bg);
            padding: 20px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
        }

        .top-items-section h3 {
            color: var(--primary-color);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .top-items-list {
            display: grid;
            gap: 12px;
        }

        .top-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 15px;
            background: var(--primary-light);
            border-radius: var(--border-radius);
            transition: var(--transition);
        }

        .top-item:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }

        .top-item .position {
            font-weight: 600;
            color: var(--primary-color);
            min-width: 20px;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--secondary-color);
            color: white;
            padding: 15px 25px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
            display: none;
            z-index: 1100;
            animation: slideIn 0.5s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Tablet Styles */
        @media (min-width: 768px) {
            .dashboard {
                flex-direction: row;
            }

            .sidebar {
                width: 80px;
                height: 100vh;
                top: 0;
                bottom: auto;
                flex-direction: column;
                justify-content: flex-start;
                padding: 20px 10px;
            }

            .nav-menu {
                flex-direction: column;
                gap: 10px;
            }

            .nav-item {
                width: 100%;
                flex-direction: row;
                justify-content: center;
                padding: 12px 0;
            }

            .nav-item i {
                margin-bottom: 0;
                font-size: 18px;
            }

            .nav-item span {
                display: none;
            }

            .main-content {
                margin-left: 80px;
                margin-bottom: 0;
                padding: 25px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .header {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }

            .welcome-text {
                text-align: left;
            }

            .welcome-text h1 {
                font-size: 24px;
                text-align: left;
            }

            .user-badge {
                margin: 5px 0;
            }

            .action-buttons {
                justify-content: flex-end;
                width: auto;
            }
        }

        /* Desktop Styles */
        @media (min-width: 992px) {
            .sidebar {
                width: 280px;
                padding: 25px;
            }

            .sidebar-header {
                display: block;
                padding: 20px 0;
                text-align: center;
                border-bottom: 1px solid rgba(255,255,255,0.1);
                margin-bottom: 20px;
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
            }

            .user-info {
                display: flex;
                font-size: 14px;
                padding: 10px;
                background: rgba(255,255,255,0.1);
                border-radius: 10px;
                margin-top: 10px;
                align-items: center;
                gap: 8px;
            }

            .nav-menu {
                margin-top: 20px;
            }

            .nav-item {
                justify-content: flex-start;
                padding: 12px 15px;
                margin-bottom: 5px;
            }

            .nav-item i {
                margin-right: 10px;
                width: 24px;
                text-align: center;
            }

            .nav-item span {
                display: inline;
                font-size: 14px;
            }

            .main-content {
                margin-left: 280px;
                padding: 30px;
            }

            .stats-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 20px;
            }

            .stat-card .number {
                font-size: 32px;
            }

            .header {
                padding: 25px;
            }

            .welcome-text h1 {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <?php if (isset($_GET['success'])): ?>
    <div class="notification" id="successNotification">
        <i class="fas fa-check-circle"></i>
        <?php 
        switch($_GET['type']) {
            case 'destination':
                echo "Destination ajoutée avec succès !";
                break;
            case 'circuit':
                echo "Circuit ajouté avec succès !";
                break;
            case 'hotel':
                echo "Hôtel ajouté avec succès !";
                break;
            case 'evenement':
                echo "Événement ajouté avec succès !";
                break;
        }
        ?>
    </div>
    <?php endif; ?>

    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="../assets/images/KWA.png" alt="logo" class="logo">
                <h2>KWABO</h2>
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

        <!-- Main Content -->
        <div class="main-content">
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="notification">
                    <?php 
                    echo $_SESSION['success_message'];
                    unset($_SESSION['success_message']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="notification" style="background-color: #f44336;">
                    <?php 
                    echo $_SESSION['error_message'];
                    unset($_SESSION['error_message']);
                    ?>
                </div>
            <?php endif; ?>

            <div class="header">
                <div class="welcome-text">
                    <h1>Bienvenue, <?php echo htmlspecialchars($_SESSION['gestionnaire_nom']); ?> !</h1>
                    <span class="user-badge">
                        <?php 
                        $type_names = [
                            'destination' => 'Gestionnaire de Destinations',
                            'hotel' => 'Gestionnaire d\'Hôtels',
                            'circuit' => 'Gestionnaire de Circuits',
                            'evenement' => 'Gestionnaire d\'évênements'
                        ];
                        echo $type_names[$user_type] ?? 'Gestionnaire';
                        ?>
                    </span>
                    <p>Voici un aperçu de votre activité</p>
                </div>
                <div class="action-buttons">
                    <button class="btn btn-primary" id="addButton">
                        <i class="fas fa-plus"></i>
                        Ajouter
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="dropdown-menu" id="addDropdown">
                        <?php
                        switch($user_type) {
                            case 'destination':
                                echo '<a href="destinations.php" class="dropdown-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    Destination
                                </a>';
                                break;
                            case 'circuit':
                                echo '<a href="circuits.php" class="dropdown-item">
                                    <i class="fas fa-route"></i>
                                    Circuit
                                </a>';
                                break;
                            case 'evenement':
                                echo '<a href="evenements.php" class="dropdown-item">
                                    <i class="fas fa-calendar-alt"></i>
                                    Événement
                                </a>';
                                break;
                            case 'hotel':
                                echo '<a href="hotels.php" class="dropdown-item">
                                    <i class="fas fa-hotel"></i>
                                    Hôtel
                                </a>';
                                break;
                        }
                        ?>
                    </div>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <h3><i class="fas fa-map-marker-alt"></i> Total <?php echo ucfirst($user_type); ?>s</h3>
                    <div class="number"><?php echo $total_items; ?></div>
                    <div class="trend">
                        <i class="fas fa-arrow-up"></i>
                        <span><?php echo $items_mois; ?> ajoutés ce mois</span>
                    </div>
                </div>
                <div class="stat-card">
                    <h3><i class="fas fa-eye"></i> Vues totales</h3>
                    <div class="number"><?php echo $total_vues; ?></div>
                    <div class="trend">
                        <i class="fas fa-arrow-up"></i>
                        <span><?php echo $vues_mois; ?> vues ce mois</span>
                    </div>
                </div>
                <div class="stat-card">
                    <h3><i class="fas fa-chart-line"></i> Performance</h3>
                    <div class="number">
                        <?php 
                        if ($total_items > 0) {
                            echo round(($total_vues / $total_items), 1);
                        } else {
                            echo "0";
                        }
                        ?>
                    </div>
                    <div class="trend">
                        <i class="fas fa-chart-bar"></i>
                        <span>Vues par élément</span>
                    </div>
                </div>
            </div>

            <?php if (!empty($top_items)): ?>
            <div class="top-items-section">
                <h3><i class="fas fa-trophy"></i> Top 3 des <?php echo ucfirst($user_type); ?>s les plus vus</h3>
                <div class="top-items-list">
                    <?php foreach ($top_items as $index => $item): ?>
                    <div class="top-item">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span class="position"><?php echo $index + 1; ?>.</span>
                            <span><?php echo htmlspecialchars($item['nom']); ?></span>
                        </div>
                        <div>
                            <i class="fas fa-eye"></i>
                            <?php echo $item['nombre_vues']; ?> vues
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gestion du menu déroulant
            const addButton = document.getElementById('addButton');
            const addDropdown = document.getElementById('addDropdown');

            if (addButton && addDropdown) {
                addButton.addEventListener('click', function(e) {
                    e.stopPropagation();
                    this.classList.toggle('active');
                    addDropdown.classList.toggle('show');
                });

                // Fermer le menu déroulant quand on clique ailleurs
                document.addEventListener('click', function(e) {
                    if (!addButton.contains(e.target) && !addDropdown.contains(e.target)) {
                        addButton.classList.remove('active');
                        addDropdown.classList.remove('show');
                    }
                });
            }

            // Gestion des notifications
            const notification = document.getElementById('successNotification');
            if (notification) {
                notification.style.display = 'flex';
                
                // Animation pour les cartes statistiques
                document.querySelectorAll('.stat-card').forEach(card => {
                    card.classList.add('new-update');
                });

                // Masquer la notification après 5 secondes
                setTimeout(() => {
                    notification.style.display = 'none';
                }, 5000);
            }
        });
    </script>
</body>
</html>