<?php
/**
 * API pour la gestion des utilisateurs (CRUD)
 */

// Inclusion du fichier CORS
require_once __DIR__ . '/cors.php';

// Inclusion des utilitaires
require_once __DIR__ . '/utils.php';

// Obtenir la connexion à la base de données
$db = getDbConnection();

// Traitement en fonction de la méthode HTTP
if (isGetRequest()) {
    // Récupération d'un utilisateur spécifique ou de tous les utilisateurs
    if (isset($_GET['id'])) {
        // Récupération d'un utilisateur par son ID
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $user = $stmt->fetch();
        
        if ($user) {
            sendJsonResponse($user);
        } else {
            sendJsonResponse(['error' => 'Utilisateur non trouvé'], 404);
        }
    } else {
        // Récupération de tous les utilisateurs
        $stmt = $db->query("SELECT * FROM users");
        $users = $stmt->fetchAll();
        sendJsonResponse($users);
    }
} elseif (isPostRequest()) {
    // Création d'un nouvel utilisateur
    $data = getRequestData();
    
    // Validation des champs requis
    $requiredFields = ['username', 'nom', 'prenom', 'email'];
    if (!validateRequiredFields($data, $requiredFields)) {
        sendJsonResponse(['error' => 'Tous les champs requis ne sont pas renseignés'], 400);
    }
    
    // Vérifier si l'email existe déjà
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$data['email']]);
    if ($stmt->fetch()) {
        sendJsonResponse(['error' => 'Cet email est déjà utilisé'], 409);
    }
    
    // Insertion du nouvel utilisateur
    $stmt = $db->prepare("
        INSERT INTO users (username, nom, prenom, email, isAdmin) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $isAdmin = isset($data['isAdmin']) ? $data['isAdmin'] : 0;
    
    $success = $stmt->execute([
        $data['username'],
        $data['nom'],
        $data['prenom'],
        $data['email'],
        $isAdmin
    ]);
    
    if ($success) {
        $userId = $db->lastInsertId();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $newUser = $stmt->fetch();
        
        sendJsonResponse($newUser, 201);
    } else {
        sendJsonResponse(['error' => 'Erreur lors de la création de l\'utilisateur'], 500);
    }
} elseif (isPutRequest()) {
    // Mise à jour d'un utilisateur existant
    if (!isset($_GET['id'])) {
        sendJsonResponse(['error' => 'ID utilisateur non spécifié'], 400);
    }
    
    $userId = $_GET['id'];
    $data = getRequestData();
    
    // Vérifier si l'utilisateur existe
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    if (!$stmt->fetch()) {
        sendJsonResponse(['error' => 'Utilisateur non trouvé'], 404);
    }
    
    // Construction de la requête de mise à jour
    $updateFields = [];
    $params = [];
    
    // Champs pouvant être mis à jour
    $allowedFields = ['username', 'nom', 'prenom', 'email', 'isAdmin'];
    
    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $updateFields[] = "$field = ?";
            $params[] = $data[$field];
        }
    }
    
    if (empty($updateFields)) {
        sendJsonResponse(['error' => 'Aucun champ à mettre à jour'], 400);
    }
    
    // Ajout de l'ID à la fin des paramètres
    $params[] = $userId;
    
    // Exécution de la mise à jour
    $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
    $stmt = $db->prepare($sql);
    $success = $stmt->execute($params);
    
    if ($success) {
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $updatedUser = $stmt->fetch();
        
        sendJsonResponse($updatedUser);
    } else {
        sendJsonResponse(['error' => 'Erreur lors de la mise à jour de l\'utilisateur'], 500);
    }
} elseif (isDeleteRequest()) {
    // Suppression d'un utilisateur
    if (!isset($_GET['id'])) {
        sendJsonResponse(['error' => 'ID utilisateur non spécifié'], 400);
    }
    
    $userId = $_GET['id'];
    
    // Vérifier si l'utilisateur existe
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    if (!$stmt->fetch()) {
        sendJsonResponse(['error' => 'Utilisateur non trouvé'], 404);
    }
    
    // Vérifier si l'utilisateur a des commandes
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM commandes WHERE id_user = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch();
    
    if ($result['count'] > 0) {
        sendJsonResponse(['error' => 'Impossible de supprimer cet utilisateur car il a des commandes associées'], 409);
    }
    
    // Suppression de l'utilisateur
    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
    $success = $stmt->execute([$userId]);
    
    if ($success) {
        sendJsonResponse(['message' => 'Utilisateur supprimé avec succès']);
    } else {
        sendJsonResponse(['error' => 'Erreur lors de la suppression de l\'utilisateur'], 500);
    }
} elseif (isPostRequest() && isset($_GET['action']) && $_GET['action'] === 'verify_admin') {
    // Vérification si un utilisateur est admin avec email et password
    $data = getRequestData();
    
    // Validation des champs requis
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
} else {
    // Méthode non supportée
    sendJsonResponse(['error' => 'Méthode non supportée'], 405);
}
?>
