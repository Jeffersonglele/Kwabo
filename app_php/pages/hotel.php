<?php
session_start();
include_once("../config/database.php");

$page_title = "Hôtels - Bénin Tourisme";

// Requête avec filtres
$sql = "SELECT * FROM hotels";
$params = [];
$where = [];

if (isset($_GET['ville']) && !empty($_GET['ville'])) {
    $where[] = "ville = ?";
    $params[] = $_GET['ville'];
}

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY prix_min ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$hotels = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= htmlspecialchars($page_title) ?></title>
    <link rel="icon" type="image/svg+xml" href="../assets/favicon/favicon.svg">
    <link rel="icon" type="image/png" sizes="96x96" href="../assets/favicon/favicon-96x96.png">
    <link rel="shortcut icon" href="../assets/favicon/favicon.ico">
    <script src="https://cdn.tailwindcss.com"></script>
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
        .price-tag {
            background: rgba(16, 185, 129, 0.1);
            color: var(--secondary);
            border-radius: 6px;
            padding: 0.25rem 0.5rem;
            font-weight: 600;
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php include_once(__DIR__ . "/../includes/navbar.php"); ?>

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-primary to-secondary py-20 text-white">
        <div class="max-w-6xl mx-auto px-6 lg:px-8 text-center fade-in">
            <h1 class="text-4xl md:text-5xl font-bold mb-6" data-aos="fade-up">
                Nos Hôtels Partenaires
            </h1>
            <p class="text-white/90 text-xl md:text-2xl mb-4 drop-shadow">
                Découvrez notre sélection d'hôtels confortables pour votre séjour au Bénin
            </p>
            <p class="text-white/70 text-lg drop-shadow">
                Des hébergements de qualité pour tous les budgets
            </p>
        </div>
    </section>

    <main class="container mx-auto px-4 md:px-8 lg:px-12 py-16 flex flex-col md:flex-row gap-8">
        <!-- Filters -->
        <section class="md:w-1/4">
            <div class="filter-card bg-white p-6 sticky top-6">
                <h2 class="text-xl font-semibold mb-6 text-gray-800">Affiner votre recherche</h2>
                <form method="GET" id="filterForm" class="space-y-5">
                    <!-- Ville -->
                    <div>
                        <label for="ville" class="block mb-2 font-medium text-gray-700">Ville</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-map-marker-alt text-gray-400"></i>
                            </div>
                            <select name="ville" id="ville" class="form-input pl-10 w-full px-4 py-3 appearance-none">
                                <option value="">Toutes les villes</option>
                                <option value="Cotonou" <?= (isset($_GET['ville']) && $_GET['ville'] === 'Cotonou') ? 'selected' : '' ?>>Cotonou</option>
                                <option value="Porto-Novo" <?= (isset($_GET['ville']) && $_GET['ville'] === 'Porto-Novo') ? 'selected' : '' ?>>Porto-Novo</option>
                                <option value="Ouidah" <?= (isset($_GET['ville']) && $_GET['ville'] === 'Ouidah') ? 'selected' : '' ?>>Ouidah</option>
                                <option value="Abomey" <?= (isset($_GET['ville']) && $_GET['ville'] === 'Abomey') ? 'selected' : '' ?>>Abomey</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <i class="fas fa-chevron-down text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Boutons -->
                    <div class="flex flex-col gap-3">
                        <button type="submit" class="btn-primary text-white font-semibold py-3 rounded-lg flex items-center justify-center">
                            <i class="fas fa-filter mr-2"></i> Appliquer les filtres
                        </button>
                        <a href="hotel.php" class="text-center text-primary border border-primary rounded-lg py-3 hover:bg-primary hover:text-white transition flex items-center justify-center">
                            <i class="fas fa-undo mr-2"></i> Réinitialiser
                        </a>
                    </div>
                </form>

                <?php if (!empty($_GET)): ?>
                    <div class="mt-6 pt-6 border-t border-gray-100">
                        <h3 class="text-lg font-semibold mb-3 text-gray-800">Filtres actifs :</h3>
                        <div class="flex flex-wrap gap-2">
                            <?php if (!empty($_GET['ville'])): ?>
                                <span class="bg-blue-100 text-blue-700 rounded-full px-3 py-1 text-sm font-medium flex items-center">
                                    <i class="fas fa-map-marker-alt mr-1 text-xs"></i>
                                    <?= htmlspecialchars($_GET['ville']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Hotel List -->
        <section class="md:w-3/4">
            <?php if (empty($hotels)): ?>
                <div class="col-span-full text-center py-16">
                    <div class="max-w-md mx-auto bg-white p-8 rounded-xl shadow-md">
                        <div class="bg-gray-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-hotel text-gray-400 text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2">Aucun hôtel trouvé</h3>
                        <p class="text-gray-600 mb-4">Essayez de modifier vos critères de recherche</p>
                        <a href="hotel.php" class="btn-primary inline-block text-white font-semibold px-6 py-2 rounded-lg">
                            Réinitialiser les filtres
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-8">
                    <?php foreach ($hotels as $hotel): ?>
                        <article class="hotel-card bg-white overflow-hidden flex flex-col h-full">
                            <!-- Image -->
                            <div class="relative overflow-hidden h-64">
                                <img src="<?= htmlspecialchars($hotel['image']) ?>" 
                                     alt="<?= htmlspecialchars($hotel['nom']) ?>" 
                                     class="w-full h-full object-cover transition-transform duration-500 hover:scale-110" />
                                <!-- Étoiles -->
                                <div class="absolute top-4 right-4 bg-gray-800 bg-opacity-70 text-white rounded-full px-3 py-1 flex items-center space-x-1 text-sm">
                                    <?php for ($i = 0; $i < $hotel['etoiles']; $i++): ?>
                                        <i class="fas fa-star text-yellow-400"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            
                            <!-- Contenu -->
                            <div class="p-6 flex flex-col flex-grow">
                                <h3 class="text-xl font-bold text-gray-800 mb-2"><?= htmlspecialchars($hotel['nom']) ?></h3>
                                <p class="text-gray-600 mb-4 flex-grow">
                                    <?= htmlspecialchars(mb_strimwidth($hotel['description'], 0, 120, '...')) ?>
                                </p>

                                <!-- Métadonnées -->
                                <div class="space-y-3 mb-6">
                                    <div class="flex items-center text-gray-700">
                                        <i class="fas fa-map-marker-alt text-secondary mr-2"></i>
                                        <span><?= htmlspecialchars($hotel['ville']) ?></span>
                                    </div>
                                    
                                    <div class="flex justify-between items-center">
                                        <div class="price-tag">
                                            À partir de <?= number_format($hotel['prix_min'], 0, ',', ' ') ?> FCFA/nuit
                                        </div>
                                        
                                        <?php if (!empty($hotel['wifi'])): ?>
                                            <span class="text-sm text-gray-500 flex items-center">
                                                <i class="fas fa-wifi text-gray-400 mr-1"></i> WiFi
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <!-- Bouton -->
                                <div class="mt-auto">
                                    <a href="hotel_detail.php?id=<?= $hotel['id'] ?>" 
                                       class="btn-secondary block text-center text-white font-semibold py-3 px-4 rounded-lg transition duration-300">
                                        <i class="fas fa-eye mr-2"></i> Voir l'hôtel
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <?php include_once(__DIR__ . "/../includes/footer.php"); ?>
</body>
</html>