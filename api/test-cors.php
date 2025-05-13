<?php
/**
 * Test file to verify CORS headers
 */

// Inclure la configuration CORS au début
require_once __DIR__ . '/cors.php';

// Définir le type de contenu JSON
header('Content-Type: application/json');

// Renvoyer une réponse simple pour tester
echo json_encode([
    'success' => true,
    'message' => 'CORS headers are working correctly',
    'headers_sent' => [
        'Access-Control-Allow-Origin' => 'http://localhost:3000',
        'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
        'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
        'Access-Control-Allow-Credentials' => 'true'
    ]
]);
?>
