<?php
// Vérifier si l'utilisateur est connecté
function require_gestionnaire_login() {
    if (!isset($_SESSION['gestionnaire_id'])) {
        $_SESSION['error_message'] = "Veuillez vous connecter pour accéder à cette page.";
        header("Location: connexion.php");
        exit();
    }
}

// Vérifier si l'utilisateur a le bon type de compte pour accéder à la page
function check_access($allowed_types) {
    require_gestionnaire_login();
    
    if (!isset($_SESSION['gestionnaire_type']) || !in_array($_SESSION['gestionnaire_type'], (array)$allowed_types)) {
        $_SESSION['error_message'] = "Accès non autorisé à cette section.";
        header("Location: tableau_bord.php");
        exit();
    }
}

// Définir les types de comptes autorisés pour chaque section
define('ALLOWED_DESTINATION', 'destination');
define('ALLOWED_HOTEL', 'hotel');
define('ALLOWED_CIRCUIT', 'circuit');
define('ALLOWED_EVENEMENT', 'evenement');
?>