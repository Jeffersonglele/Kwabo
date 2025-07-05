<?php
require __DIR__.'/../vendor/autoload.php';

$db = new PDO('mysql:host=db;dbname=app_db', 'user', 'password');

// Trouver les forfaits expirant dans 6 mois
$sixMonthsLater = (new DateTime())->add(new DateInterval('P6M'))->format('Y-m-d');

$stmt = $db->prepare("
    SELECT nom,email,type_compte,date_inscription,user_id
    FROM gestionnaires
    WHERE gestionnaires.date_inscription BETWEEN DATE_SUB(:target_date, INTERVAL 1 DAY) AND :target_date
    AND gestionnaires.reminder_sent = 0
");

$stmt->execute([':target_date' => $sixMonthsLater]);
$subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$client = new GuzzleHttp\Client(['base_uri' => 'http://mail-service:8000']);

foreach ($subscriptions as $sub) {
    $response = $client->post('http://mail-service:8000/api/send-reminder', [
        'json' => [
            'email' => $sub['email'],
            'nom' => $sub['name'],
            'dateend' => $sub['end_date'],
            'type_compte' => $sub['type_name']
        ]
    ]);
    
    // Marquer comme notifié
    $db->prepare("UPDATE gestionnaires SET reminder_sent = 1 WHERE user_id = ?")
       ->execute([$sub['user_id']]);
}