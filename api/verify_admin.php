<?php
/**
 * API simple pour vérifier les identifiants d'un administrateur
 */

// Inclusion du fichier CORS
require_once __DIR__ . '/cors.php';

// Inclusion des utilitaires
require_once __DIR__ . '/utils.php';

// Obtenir la connexion à la base de données
$db = getDbConnection();

// Cette API accepte uniquement les requêtes POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['error' => 'Méthode non autorisée'], 405);
    exit;
}

// Récupérer les données JSON de la requête
$inputJSON = file_get_contents('php://input');
$data = json_decode($inputJSON, true);

// Vérifier si les données sont valides
if ($data === null) {
    sendJsonResponse(['error' => 'Données JSON invalides'], 400);
    exit;
}

// Vérifier si email et password sont présents
if (!isset($data['email']) || !isset($data['password'])) {
    sendJsonResponse(['error' => 'Email et mot de passe requis'], 400);
    exit;
}

$email = $data['email'];
$password = $data['password'];

// Vérifier si l'utilisateur existe et est admin
$stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND password = ? AND isAdmin = 1");
$stmt->execute([$email, $password]);
$admin = $stmt->fetch();

if ($admin) {
    // L'utilisateur est un admin
    sendJsonResponse(['success' => true, 'user' => $admin]);
} else {
    // L'utilisateur n'est pas un admin ou les identifiants sont incorrects
    sendJsonResponse(['success' => false, 'message' => 'Email ou mot de passe incorrect ou utilisateur non admin']);
}
?>
