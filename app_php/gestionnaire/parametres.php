<?php
session_start();
include_once("../config/database.php");

// Vérification si l'utilisateur est connecté
if (!isset($_SESSION['gestionnaire_id'])) {
    header("Location: connexion.php");
    exit();
}

$user_id = $_SESSION['gestionnaire_id'];
$success_message = '';
$error_message = '';

// Récupérer les informations actuelles du gestionnaire
$stmt = $pdo->prepare("SELECT * FROM gestionnaires WHERE id = ?");
$stmt->execute([$user_id]);
$gestionnaire = $stmt->fetch(PDO::FETCH_ASSOC);

// Traitement du formulaire de mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $telephone = trim($_POST['telephone']);
    $ancien_motdepasse = $_POST['ancien_motdepasse'] ?? '';
    $nouveau_motdepasse = $_POST['nouveau_motdepasse'] ?? '';
    $confirmer_motdepasse = $_POST['confirmer_motdepasse'] ?? '';

    try {
        $pdo->beginTransaction();
        
        // Vérifier si l'email est déjà utilisé
        $stmt = $pdo->prepare("SELECT id FROM gestionnaires WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);
        if ($stmt->fetch()) {
            throw new Exception("Cette adresse email est déjà utilisée par un autre compte.");
        }

        // Mise à jour des informations de base
        $sql = "UPDATE gestionnaires SET nom = ?, email = ?, telephone = ?";
        $params = [$nom, $email, $telephone];
        
        // Vérifier si on change le mot de passe
        if (!empty($nouveau_motdepasse)) {
            if (empty($ancien_motdepasse)) {
                throw new Exception("Veuillez entrer votre mot de passe actuel pour le modifier.");
            }
            
            if (!password_verify($ancien_motdepasse, $gestionnaire['mot_de_passe'])) {
                throw new Exception("Le mot de passe actuel est incorrect.");
            }
            
            if (strlen($nouveau_motdepasse) < 8) {
                throw new Exception("Le nouveau mot de passe doit contenir au moins 8 caractères.");
            }
            
            if ($nouveau_motdepasse !== $confirmer_motdepasse) {
                throw new Exception("Les nouveaux mots de passe ne correspondent pas.");
            }
            
            $sql .= ", mot_de_passe = ?";
            $params[] = password_hash($nouveau_motdepasse, PASSWORD_DEFAULT);
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $user_id;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        $pdo->commit();
        
        // Mettre à jour la session
        $_SESSION['gestionnaire_nom'] = $nom;
        
        $success_message = "Vos informations ont été mises à jour avec succès.";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error_message = $e->getMessage();
    }
    
    // Recharger les informations du gestionnaire
    $stmt = $pdo->prepare("SELECT * FROM gestionnaires WHERE id = ?");
    $stmt->execute([$user_id]);
    $gestionnaire = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres - KWABO</title>
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
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 28px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .page-subtitle {
            color: #7f8c8d;
            font-size: 16px;
        }

        /* Card Styles */
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            overflow: hidden;
        }

        .card-header {
            padding: 20px 25px;
            border-bottom: 1px solid #eee;
            background: #f8f9fa;
        }

        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-body {
            padding: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #495057;
        }

        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            border-color: #1a237e;
            outline: none;
            box-shadow: 0 0 0 2px rgba(26, 35, 126, 0.1);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            padding: 10px 20px;
            border-radius: 6px;
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

        .alert {
            padding: 12px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #e6f7e6;
            color: #00a854;
            border-left: 4px solid #00a854;
        }

        .alert-danger {
            background: #fff1f0;
            color: #f5222d;
            border-left: 4px solid #f5222d;
        }

        .password-toggle {
            position: relative;
        }

        .password-toggle i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="../assets/images/KWA.png" alt="logo" class="logo">
                <h2>Bénin Tourisme</h2>
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <?= htmlspecialchars($_SESSION['gestionnaire_nom']) ?>
                </div>
            </div>
            <nav class="nav-menu">
                <a href="tableau_bord.php" class="nav-item">
                    <i class="fas fa-home"></i>
                    <span>Tableau de bord</span>
                </a>
                <a href="liste.php" class="nav-item">
                    <i class="fas fa-list"></i>
                    <span>Liste</span>
                </a>
                <a href="parametres.php" class="nav-item active">
                    <i class="fas fa-cog"></i>
                    <span>Paramètres</span>
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
                <h1 class="page-title">Paramètres du compte</h1>
                <p class="page-subtitle">Gérez vos informations personnelles et vos paramètres de sécurité</p>
            </div>

            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?= $success_message ?>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= $error_message ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-user-edit"></i>
                        Informations personnelles
                    </h2>
                </div>
                <div class="card-body">
                    <form action="" method="POST">
                        <div class="form-group">
                            <label for="nom">Nom complet</label>
                            <input type="text" id="nom" name="nom" class="form-control" value="<?= htmlspecialchars($gestionnaire['nom']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Adresse email</label>
                            <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($gestionnaire['email']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="telephone">Téléphone</label>
                            <input type="tel" id="telephone" name="telephone" class="form-control" value="<?= htmlspecialchars($gestionnaire['telephone'] ?? '') ?>">
                        </div>
                        
                        <h3 style="margin: 30px 0 20px; color: #2c3e50; font-size: 18px;">Changer le mot de passe</h3>
                        <p style="color: #666; margin-bottom: 20px; font-size: 14px;">
                            Laissez ces champs vides si vous ne souhaitez pas modifier votre mot de passe.
                        </p>
                        
                        <div class="form-group">
                            <label for="ancien_motdepasse">Ancien mot de passe</label>
                            <div class="password-toggle">
                                <input type="password" id="ancien_motdepasse" name="ancien_motdepasse" class="form-control">
                                <i class="fas fa-eye toggle-password" data-target="ancien_motdepasse"></i>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="nouveau_motdepasse">Nouveau mot de passe</label>
                            <div class="password-toggle">
                                <input type="password" id="nouveau_motdepasse" name="nouveau_motdepasse" class="form-control">
                                <i class="fas fa-eye toggle-password" data-target="nouveau_motdepasse"></i>
                            </div>
                            <small class="text-muted">Le mot de passe doit contenir au moins 8 caractères.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirmer_motdepasse">Confirmer le nouveau mot de passe</label>
                            <div class="password-toggle">
                                <input type="password" id="confirmer_motdepasse" name="confirmer_motdepasse" class="form-control">
                                <i class="fas fa-eye toggle-password" data-target="confirmer_motdepasse"></i>
                            </div>
                        </div>
                        
                        <div class="form-group" style="margin-top: 30px;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Enregistrer les modifications
                            </button>
                        </div>
                    </form>
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

        // Validation du formulaire
        document.querySelector('form').addEventListener('submit', function(e) {
            const nouveauMdp = document.getElementById('nouveau_motdepasse').value;
            const confirmerMdp = document.getElementById('confirmer_motdepasse').value;
            
            if (nouveauMdp !== '' || confirmerMdp !== '') {
                if (nouveauMdp.length < 8) {
                    e.preventDefault();
                    alert('Le nouveau mot de passe doit contenir au moins 8 caractères.');
                    return false;
                }
                
                if (nouveauMdp !== confirmerMdp) {
                    e.preventDefault();
                    alert('Les nouveaux mots de passe ne correspondent pas.');
                    return false;
                }
            }
        });
    </script>
</body>
</html>
