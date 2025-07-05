<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkAdminAuth() {
    if (!isset($_SESSION['user_id']) || $_SESSION['type_compte'] !== 'admin') {
        header("Location: ../connexion.php");
        exit();
    }
}

function checkUserAuth() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../connexion.php");
        exit();
    }
}
?> 