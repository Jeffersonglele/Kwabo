<?php
session_start();
include_once(__DIR__ . "/../config/database.php");
include_once(__DIR__ . "/../includes/navbar.php");

// Récupérer les filtres
$region = $_GET['region'] ?? '';
$type = $_GET['type'] ?? '';
$search = $_GET['search'] ?? '';

try {
    // Construire la requête SQL
    $sql = "SELECT * FROM lieux WHERE 1=1";
    $params = [];

    if ($region) {
        $sql .= " AND region = ?";
        $params[] = $region;
    }
    if ($type) {
        $sql .= " AND type = ?";
        $params[] = $type;
    }
    if ($search) {
        $sql .= " AND (nom LIKE ? OR description LIKE ? OR ville LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    $sql .= " ORDER BY date_creation DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $lieux = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des destinations : " . $e->getMessage();
    $lieux = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Destinations - Bénin Tourisme</title>
    <link rel="icon" type="image/svg+xml" href="../assets/favicon/favicon.svg">
    <link rel="icon" type="image/png" sizes="96x96" href="../assets/favicon/favicon-96x96.png">
    <link rel="shortcut icon" href="../assets/favicon/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #f97316; /* Bleu principal */
            --secondary: #718096; /* Vert secondaire */
            --accent: #F59E0B; /* Orange pour les accents */
        }
        
        body {
            font-family: 'Poppins', sans-serif;
        }
        
        .hero-section {
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 60vh;
            display: flex;
            align-items: center;
            color: white;
        }
        
        .filter-card {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border-radius: 16px;
            background: white;
        }
        
        .destination-card {
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border-radius: 16px;
            overflow: hidden;
        }
        
        .destination-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        .badge {
            background: var(--primary);
            color: white;
        }
        
        .btn-primary {
            background: var(--primary);
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background: #f97316;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: var(--secondary);
            transition: all 0.3s;
        }
        
        .btn-secondary:hover {
            background: #f97316;
            transform: translateY(-2px);
        }
        
        .form-input {
            transition: all 0.3s;
            border: 1px solid #E5E7EB;
            border-radius: 10px;
        }
        
        .form-input:focus {
            border-color: #f97316;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }
    </style>
</head>
<body class="">
    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-primary to-secondary py-20 text-white">
        <div class="max-w-6xl mx-auto px-6 lg:px-8 text-center fade-in">
            <h1 class="text-4xl md:text-5xl font-bold mb-6" data-aos="fade-up">Découvrez nos Destinations</h1>
            <p class="text-xl md:text-2xl text-white opacity-90 max-w-3xl mx-auto">
                Explorez les merveilles culturelles et naturelles du Bénin
            </p>
        </div>
    </section>

    <!-- Filtres -->
    <section class="container mx-auto px-4 mt-12 -mb-16 relative z-10">
        <div class="filter-card bg-white p-6 sticky top-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Recherche -->
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" name="search" 
                           class="form-input pl-10 w-full px-4 py-3" 
                           placeholder="Rechercher..." 
                           value="<?= htmlspecialchars($search) ?>">
                </div>
                
                <!-- Région -->
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-map-marked-alt text-gray-400"></i>
                    </div>
                    <select name="region" class="form-input pl-10 w-full px-4 py-3 appearance-none">
                        <option value="">Toutes les régions</option>
                        <?php 
                        $regions = array_unique(array_column($lieux, 'region'));
                        foreach ($regions as $reg): ?>
                            <option value="<?= htmlspecialchars($reg) ?>" <?= $region == $reg ? 'selected' : '' ?>>
                                <?= htmlspecialchars($reg) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <i class="fas fa-chevron-down text-gray-400"></i>
                    </div>
                </div>
                
                <!-- Type -->
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-tags text-gray-400"></i>
                    </div>
                    <select name="type" class="form-input pl-10 w-full px-4 py-3 appearance-none">
                        <option value="">Tous les types</option>
                        <?php 
                        $types = array_unique(array_column($lieux, 'type'));
                        foreach ($types as $typ): ?>
                            <option value="<?= htmlspecialchars($typ) ?>" <?= $type == $typ ? 'selected' : '' ?>>
                                <?= htmlspecialchars($typ) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <i class="fas fa-chevron-down text-gray-400"></i>
                    </div>
                </div>
                
                <!-- Boutons -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="submit" 
                            class="btn-primary text-white font-semibold px-6 py-3 rounded-lg flex-1 flex items-center justify-center">
                        <i class="fas fa-filter mr-2"></i> Filtrer
                    </button>
                    <a href="destinations.php" 
                       class="btn-secondary text-white font-semibold px-6 py-3 rounded-lg flex-1 flex items-center justify-center">
                        <i class="fas fa-undo mr-2"></i> Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </section>

    <!-- Liste des destinations -->
    <section class="container mx-auto px-4 py-20">
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-8 rounded-lg">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($lieux)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8 px-4 sm:px-0">
                <?php foreach ($lieux as $lieu): ?>
                    <div class="relative group overflow-hidden rounded-2xl shadow-xl hover:shadow-2xl transition-all duration-300 bg-white ">
                        <!-- Image Container -->
                        <div class="relative h-60 overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent z-10"></div>
                            <img 
                                src="../<?= htmlspecialchars($lieu['image']) ?>" 
                                alt="<?= htmlspecialchars($lieu['nom']) ?>"
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                            >
                            <!-- Badge -->
                            <span class="absolute top-4 right-4 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide 
                                        bg-white/90 text-gray-900 backdrop-blur-sm z-20 shadow-sm">
                                <?= htmlspecialchars($lieu['type']) ?>
                            </span>
                        </div>
                        
                        <!-- Content -->
                        <div class="p-6 flex flex-col h-[calc(100%-15rem)]">
                            <!-- Title & Location -->
                            <div class="mb-4">
                                <h3 class="text-xl font-extrabold text-gray-600 text-gray mb-1 line-clamp-1">
                                    <?= htmlspecialchars($lieu['nom']) ?>
                                </h3>
                                <div class="flex items-center text-gray-600 dark:text-gray-300">
                                    <i class="fas fa-map-marker-alt text-secondary mr-2 text-sm"></i>
                                    <span class="text-sm text-gray "><?= htmlspecialchars($lieu['ville']) ?>, <?= htmlspecialchars($lieu['region']) ?></span>
                                </div>
                            </div>
                            
                            <!-- Metadata -->
                            <div class="space-y-3 mb-6">
                                <?php if (!empty($lieu['duree_visite'])): ?>
                                <div class="flex items-center text-gray-700 dark:text-gray-300">
                                    <i class="fas fa-clock text-secondary mr-2 text-sm"></i>
                                    <span class="text-sm">Visite: <?= htmlspecialchars($lieu['duree_visite']) ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Button -->
                            <div class="mt-auto">
                                <a 
                                    href="lieu_detail.php?id=<?= $lieu['id'] ?>" 
                                    class="btn-secondary w-full flex items-center justify-center gap-2 py-3 px-4 rounded-lg transition-all
                                        bg-gradient-to-r from-secondary to-secondary-dark hover:from-secondary-dark hover:to-secondary
                                        text-white font-semibold shadow-md hover:shadow-lg"
                                >
                                    <i class="fas fa-eye"></i>
                                    <span>Voir détails</span>
                                </a>
                            </div>
                        </div>
                        
                        <!-- Hover Effect -->
                        <div class="absolute inset-0 bg-black/5 group-hover:bg-black/10 transition-all duration-300 pointer-events-none"></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Empty State -->
            <div class="max-w-md mx-auto bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-lg text-center">
                <div class="w-24 h-24 mx-auto mb-6 flex items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                    <i class="fas fa-search text-gray-400 text-4xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Aucun résultat trouvé</h3>
                <p class="text-gray-600 dark:text-gray-300 mb-6">
                    Essayez d'ajuster vos critères ou découvrez nos autres destinations
                </p>
                <a 
                    href="destinations.php" 
                    class="btn-primary inline-flex items-center gap-2 px-6 py-3 rounded-lg font-semibold
                        bg-gradient-to-r from-primary to-primary-dark hover:from-primary-dark hover:to-primary
                        text-white shadow-md hover:shadow-lg transition-all"
                >
                    <i class="fas fa-compass"></i>
                    Explorer toutes les destinations
                </a>
            </div>
        <?php endif; ?>
    </section>

    <?php include_once(__DIR__ . "/../includes/footer.php"); ?>
</body>
</html>