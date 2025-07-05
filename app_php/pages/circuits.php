<?php
session_start();
include_once("../config/database.php");

// Récupérer les circuits depuis la base de données avec les filtres
$sql = "SELECT * FROM circuits";
$params = [];
$where = [];

if (isset($_GET['type']) && !empty($_GET['type'])) {
    $where[] = "type = ?";
    $params[] = $_GET['type'];
}

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY prix ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$circuits = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Circuits Touristiques - Bénin Tourisme</title>
    <link rel="icon" type="image/svg+xml" href="../assets/favicon/favicon.svg">
    <link rel="icon" type="image/png" sizes="96x96" href="../assets/favicon/favicon-96x96.png">
    <link rel="shortcut icon" href="../assets/favicon/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <script src="https://cdn.tailwindcss.com"></script>
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
        .duration-badge {
            background: rgba(245, 158, 11, 0.1);
            color: black;
            border: 1px solid rgba(245, 158, 11, 0.2);
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
    <?php include('../includes/navbar.php'); ?>

    <!-- Hero Section -->
     <section class="bg-gradient-to-r from-primary to-secondary py-20 text-white">
        <div class="max-w-6xl mx-auto px-6 lg:px-8 text-center fade-in">
            <h1 class="text-4xl md:text-5xl font-bold mb-6" data-aos="fade-up">
                Circuits Touristiques
            </h1>
            <p class="text-xl md:text-2xl text-white opacity-90 mb-4" data-aos="fade-up" data-aos-delay="100">
                Découvrez le Bénin authentique avec nos circuits guidés
            </p>
            <p class="text-lg text-white opacity-80" data-aos="fade-up" data-aos-delay="200">
                Des expériences uniques à travers le pays
            </p>
        </div>
    </section>

    <main class="container mx-auto px-4 md:px-8 lg:px-12 py-16 flex flex-col md:flex-row gap-8">
        <!-- Filtres -->
        <aside class="md:w-1/4">
            <div class="filter-card bg-white p-6 sticky top-6">
                <h2 class="text-xl font-semibold mb-6 text-gray-800">Affiner votre recherche</h2>
                <form method="GET" id="filterForm" class="space-y-5">
                    <!-- Type de circuit -->
                    <div>
                        <label class="block mb-2 font-medium text-gray-700">Type de circuit</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-route text-gray-400"></i>
                            </div>
                            <select name="type" class="form-input pl-10 w-full px-4 py-3 appearance-none">
                                <option value="">Tous les types</option>
                                <option value="culturel" <?= (isset($_GET['type']) && $_GET['type'] === 'culturel') ? 'selected' : '' ?>>Culturel</option>
                                <option value="aventure" <?= (isset($_GET['type']) && $_GET['type'] === 'aventure') ? 'selected' : '' ?>>Aventure</option>
                                <option value="nature" <?= (isset($_GET['type']) && $_GET['type'] === 'nature') ? 'selected' : '' ?>>Nature</option>
                                <option value="historique" <?= (isset($_GET['type']) && $_GET['type'] === 'historique') ? 'selected' : '' ?>>Historique</option>
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
                        <a href="circuits.php" class="text-center text-primary border border-primary rounded-lg py-3 hover:bg-primary hover:text-white transition flex items-center justify-center">
                            <i class="fas fa-undo mr-2"></i> Réinitialiser
                        </a>
                    </div>
                </form>

                <?php if (!empty($_GET)): ?>
                    <div class="mt-6 pt-6 border-t border-gray-100">
                        <h3 class="text-lg font-semibold mb-3 text-gray-800">Filtres actifs :</h3>
                        <div class="flex flex-wrap gap-2">
                            <?php if (!empty($_GET['type'])): ?>
                                <span class="bg-blue-100 text-blue-700 rounded-full px-3 py-1 text-sm font-medium flex items-center">
                                    <i class="fas fa-route mr-1 text-xs"></i>
                                    <?= ucfirst(htmlspecialchars($_GET['type'])) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </aside>

        <!-- Liste des circuits -->
        <section class="md:w-3/4">
            <?php if (empty($circuits)): ?>
                <div class="text-center py-16">
                    <div class="max-w-md mx-auto bg-white p-8 rounded-xl shadow-md">
                        <div class="bg-gray-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-route text-gray-400 text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2">Aucun circuit trouvé</h3>
                        <p class="text-gray-600 mb-4">Essayez de modifier vos critères de recherche</p>
                        <a href="circuits.php" class="btn-primary inline-block text-white font-semibold px-6 py-2 rounded-lg">
                            Réinitialiser les filtres
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-8">
                    <?php foreach ($circuits as $circuit): ?>
                        <article class="circuit-card bg-white overflow-hidden flex flex-col h-full" data-aos="fade-up">
                            <!-- Image -->
                            <div class="relative overflow-hidden h-64">
                                <img src="<?= htmlspecialchars($circuit['image']) ?>" 
                                     alt="<?= htmlspecialchars($circuit['nom']) ?>" 
                                     class="w-full h-full object-cover transition-transform duration-500 hover:scale-110" />
                                <!-- Durée -->
                                <div class="duration-badge absolute top-4 right-4 px-3 py-1 text-center backdrop-blur-sm bg-white/30 border border-white/20 rounded-lg shadow-sm">
                                    <i class="fas fa-clock mr-1"></i>
                                    <?= htmlspecialchars($circuit['duree']) ?> jours
                                </div>
                            </div>
                            
                            <!-- Contenu -->
                            <div class="p-6 flex flex-col flex-grow">
                                <h3 class="text-xl font-bold text-gray-800 mb-2"><?= htmlspecialchars($circuit['nom']) ?></h3>
                                <p class="text-gray-600 mb-4 flex-grow">
                                    <?= htmlspecialchars(mb_strimwidth($circuit['sous_titre'], 0, 120, '...')) ?>
                                </p>

                                <!-- Métadonnées -->
                                <div class="space-y-3 mb-6">
                                    <div class="flex items-center text-gray-700">
                                        <i class="fas fa-map-marker-alt text-secondary mr-2"></i>
                                        <span><?= htmlspecialchars($circuit['villes_visitees']) ?></span>
                                    </div>
                                    
                                    <div class="price-tag">
                                        À partir de <?= number_format($circuit['prix'], 0, ',', ' ') ?> FCFA
                                    </div>
                                </div>
                                
                                <!-- Bouton -->
                                <div class="mt-auto">
                                    <a href="circuit_detail.php?id=<?= $circuit['id'] ?>" 
                                       class="btn-secondary block text-center text-white font-semibold py-3 px-4 rounded-lg transition duration-300">
                                        <i class="fas fa-eye mr-2"></i> Découvrir ce circuit
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <?php include('../includes/footer.php'); ?>

    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true,
            easing: 'ease-out-quad'
        });
    </script>
</body>
</html>