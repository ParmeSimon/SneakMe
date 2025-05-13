<?php
/**
 * API pour la gestion des actions (CRUD)
 */

// Inclusion du fichier CORS
require_once __DIR__ . '/cors.php';

// Inclusion des utilitaires
require_once __DIR__ . '/utils.php';

// Obtenir la connexion à la base de données
$db = getDbConnection();

// Traitement en fonction de la méthode HTTP
if (isGetRequest()) {
    // Récupération d'une action spécifique ou de toutes les actions
    if (isset($_GET['id'])) {
        // Récupération d'une action par son ID
        $stmt = $db->prepare("SELECT * FROM actions WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $action = $stmt->fetch();
        
        if ($action) {
            sendJsonResponse($action);
        } else {
            sendJsonResponse(['error' => 'Action non trouvée'], 404);
        }
    } else {
        // Récupération de toutes les actions
        $stmt = $db->query("SELECT * FROM actions");
        $actions = $stmt->fetchAll();
        sendJsonResponse($actions);
    }
} elseif (isPostRequest()) {
    // Création d'une nouvelle action
    $data = getRequestData();
    
    // Validation des champs requis
    $requiredFields = ['title', 'description', 'result'];
    if (!validateRequiredFields($data, $requiredFields)) {
        sendJsonResponse(['error' => 'Tous les champs requis ne sont pas renseignés'], 400);
    }
    
    // Insertion de la nouvelle action
    $stmt = $db->prepare("INSERT INTO actions (title, description, result) VALUES (?, ?, ?)");
    $success = $stmt->execute([
        $data['title'],
        $data['description'],
        $data['result']
    ]);
    
    if ($success) {
        $actionId = $db->lastInsertId();
        $stmt = $db->prepare("SELECT * FROM actions WHERE id = ?");
        $stmt->execute([$actionId]);
        $newAction = $stmt->fetch();
        
        sendJsonResponse($newAction, 201);
    } else {
        sendJsonResponse(['error' => 'Erreur lors de la création de l\'action'], 500);
    }
} elseif (isPutRequest()) {
    // Mise à jour d'une action existante
    if (!isset($_GET['id'])) {
        sendJsonResponse(['error' => 'ID action non spécifié'], 400);
    }
    
    $actionId = $_GET['id'];
    $data = getRequestData();
    
    // Vérifier si l'action existe
    $stmt = $db->prepare("SELECT * FROM actions WHERE id = ?");
    $stmt->execute([$actionId]);
    if (!$stmt->fetch()) {
        sendJsonResponse(['error' => 'Action non trouvée'], 404);
    }
    
    // Construction de la requête de mise à jour
    $updateFields = [];
    $params = [];
    
    // Champs pouvant être mis à jour
    $allowedFields = ['title', 'description', 'result'];
    
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
    $params[] = $actionId;
    
    // Exécution de la mise à jour
    $sql = "UPDATE actions SET " . implode(', ', $updateFields) . " WHERE id = ?";
    $stmt = $db->prepare($sql);
    $success = $stmt->execute($params);
    
    if ($success) {
        $stmt = $db->prepare("SELECT * FROM actions WHERE id = ?");
        $stmt->execute([$actionId]);
        $updatedAction = $stmt->fetch();
        
        sendJsonResponse($updatedAction);
    } else {
        sendJsonResponse(['error' => 'Erreur lors de la mise à jour de l\'action'], 500);
    }
} elseif (isDeleteRequest()) {
    // Suppression d'une action
    if (!isset($_GET['id'])) {
        sendJsonResponse(['error' => 'ID action non spécifié'], 400);
    }
    
    $actionId = $_GET['id'];
    
    // Vérifier si l'action existe
    $stmt = $db->prepare("SELECT * FROM actions WHERE id = ?");
    $stmt->execute([$actionId]);
    if (!$stmt->fetch()) {
        sendJsonResponse(['error' => 'Action non trouvée'], 404);
    }
    
    // Suppression de l'action
    $stmt = $db->prepare("DELETE FROM actions WHERE id = ?");
    $success = $stmt->execute([$actionId]);
    
    if ($success) {
        sendJsonResponse(['message' => 'Action supprimée avec succès']);
    } else {
        sendJsonResponse(['error' => 'Erreur lors de la suppression de l\'action'], 500);
    }
} else {
    // Méthode non supportée
    sendJsonResponse(['error' => 'Méthode non supportée'], 405);
}
?>
