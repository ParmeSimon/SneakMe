<?php
/**
 * Point d'entrée principal de l'API SneakMe
 */

// Autoriser les requêtes CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Gérer les requêtes OPTIONS (pre-flight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Inclusion des utilitaires
require_once __DIR__ . '/utils.php';

// Message d'accueil de l'API
$apiInfo = [
    'name' => 'SneakMe API',
    'version' => '1.0.0',
    'description' => 'API pour la gestion de la base de données SneakMe',
    'endpoints' => [
        'actions' => '/api/actions.php',
        'categories' => '/api/categories.php',
        'commandes' => '/api/commandes.php',
        'couleurs' => '/api/couleurs.php',
        'items' => '/api/items.php',
        'pointures' => '/api/pointures.php',
        'produits' => '/api/produits.php',
        'sexes' => '/api/sexes.php',
        'users' => '/api/users.php'
    ]
];

// Envoyer les informations de l'API
sendJsonResponse($apiInfo);
?>
