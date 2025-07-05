<?php
// Configuration Google OAuth
$google_client_id = '836908265562-vdchpmtkjtqnkgseogf4d3pus5h7ir04.apps.googleusercontent.com';
$google_client_secret = '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';
$google_redirect_uri = 'http://localhost:8080/pages/google_callback.php';




// Initialisation du client Google
require_once __DIR__ . '/../vendor/autoload.php';

$client = new Google\Client();
$client->setClientId($google_client_id);
$client->setClientSecret($google_client_secret);
$client->setRedirectUri($google_redirect_uri);
$client->addScope("email");
$client->addScope("profile");
