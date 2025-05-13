<?php
/**
 * Fichier d'utilitaires pour l'API
 * 
 * Contient des fonctions communes utilisées par les différents endpoints de l'API
 */

// Inclusion de la configuration de la base de données
require_once __DIR__ . '/../database/config.php';

/**
 * Envoie une réponse JSON avec le code HTTP approprié
 * 
 * @param mixed $data Les données à envoyer
 * @param int $statusCode Le code HTTP de la réponse
 */
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Vérifie si la requête est de type GET
 */
function isGetRequest() {
    return $_SERVER['REQUEST_METHOD'] === 'GET';
}

/**
 * Vérifie si la requête est de type POST
 */
function isPostRequest() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Vérifie si la requête est de type PUT
 */
function isPutRequest() {
    return $_SERVER['REQUEST_METHOD'] === 'PUT';
}

/**
 * Vérifie si la requête est de type DELETE
 */
function isDeleteRequest() {
    return $_SERVER['REQUEST_METHOD'] === 'DELETE';
}

/**
 * Récupère les données JSON envoyées dans le corps de la requête
 * 
 * @return array Les données décodées
 */
function getRequestData() {
    $json = file_get_contents('php://input');
    return json_decode($json, true) ?? [];
}

/**
 * Vérifie si tous les champs requis sont présents dans les données
 * 
 * @param array $data Les données à vérifier
 * @param array $requiredFields Les champs requis
 * @return bool True si tous les champs requis sont présents, false sinon
 */
function validateRequiredFields($data, $requiredFields) {
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            return false;
        }
    }
    return true;
}
?>
