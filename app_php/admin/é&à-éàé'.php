<?php
session_start();

// Tu peux ici mettre une authentification supplémentaire si tu veux, ou un lien privé
$_SESSION['allow_admin_login'] = true;

// Redirige vers le login
header('Location: login.php');
exit;
