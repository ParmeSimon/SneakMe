<?php
/**
 * API pour la gestion des pointures (CRUD)
 */

// Inclusion du fichier CORS
require_once __DIR__ . '/cors.php';

// Inclusion des utilitaires
require_once __DIR__ . '/utils.php';

// Obtenir la connexion à la base de données
$db = getDbConnection();

// Traitement en fonction de la méthode HTTP
if (isGetRequest()) {
    // Récupération d'une pointure spécifique ou de toutes les pointures
    if (isset($_GET['id'])) {
        // Récupération d'une pointure par son ID
        $stmt = $db->prepare("SELECT id, label FROM pointures WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $pointure = $stmt->fetch();
        
        if ($pointure) {
            sendJsonResponse(['success' => true, 'data' => $pointure]);
        } else {
            sendJsonResponse(['error' => 'Pointure non trouvée', 'success' => false], 404);
        }
    } else {
        // Récupération de toutes les pointures
        $stmt = $db->query("SELECT id, label FROM pointures ORDER BY label ASC");
        $pointures = $stmt->fetchAll();
        sendJsonResponse($pointures);
    }
} elseif (isPostRequest()) {
    // Création d'une nouvelle pointure
    $data = getRequestData();
    
    // Validation des champs requis
    // Vérifier si on utilise nom ou label dans la requête
    $labelValue = '';
    if (isset($data['nom']) && !empty($data['nom'])) {
        $labelValue = $data['nom'];
    } elseif (isset($data['label']) && !empty($data['label'])) {
        $labelValue = $data['label'];
    } else {
        sendJsonResponse(['error' => 'Le nom de la pointure est requis', 'success' => false], 400);
    }
    
    // Vérifier si la pointure existe déjà
    $stmt = $db->prepare("SELECT id FROM pointures WHERE label = ?");
    $stmt->execute([$labelValue]);
    if ($stmt->fetch()) {
        sendJsonResponse(['error' => 'Cette pointure existe déjà', 'success' => false], 409);
    }
    
    // Insertion de la nouvelle pointure
    $stmt = $db->prepare("INSERT INTO pointures (label) VALUES (?)");
    $success = $stmt->execute([$labelValue]);
    
    if ($success) {
        $pointureId = $db->lastInsertId();
        $stmt = $db->prepare("SELECT id, label FROM pointures WHERE id = ?");
        $stmt->execute([$pointureId]);
        $newPointure = $stmt->fetch();
        
        sendJsonResponse(['success' => true, 'message' => 'Pointure ajoutée avec succès', 'data' => $newPointure], 201);
    } else {
        sendJsonResponse(['error' => 'Erreur lors de la création de la pointure', 'success' => false], 500);
    }
} elseif (isPutRequest()) {
    // Mise à jour d'une pointure existante
    $data = getRequestData();
    
    // Récupérer l'ID soit depuis l'URL, soit depuis les données
    if (isset($data['id'])) {
        $pointureId = $data['id'];
    } elseif (isset($_GET['id'])) {
        $pointureId = $_GET['id'];
    } else {
        sendJsonResponse(['error' => 'ID pointure non spécifié', 'success' => false], 400);
    }
    
    // Validation des champs requis - vérifier si on utilise nom ou label
    $labelValue = '';
    if (isset($data['nom']) && !empty($data['nom'])) {
        $labelValue = $data['nom'];
    } elseif (isset($data['label']) && !empty($data['label'])) {
        $labelValue = $data['label'];
    } else {
        sendJsonResponse(['error' => 'Le nom de la pointure est requis', 'success' => false], 400);
    }
    
    // Vérifier si la pointure existe
    $stmt = $db->prepare("SELECT * FROM pointures WHERE id = ?");
    $stmt->execute([$pointureId]);
    if (!$stmt->fetch()) {
        sendJsonResponse(['error' => 'Pointure non trouvée', 'success' => false], 404);
    }
    
    // Vérifier si le nouveau label existe déjà pour une autre pointure
    $stmt = $db->prepare("SELECT id FROM pointures WHERE label = ? AND id != ?");
    $stmt->execute([$labelValue, $pointureId]);
    if ($stmt->fetch()) {
        sendJsonResponse(['error' => 'Une pointure avec ce nom existe déjà', 'success' => false], 409);
    }
    
    // Mise à jour de la pointure
    $stmt = $db->prepare("UPDATE pointures SET label = ? WHERE id = ?");
    $success = $stmt->execute([$labelValue, $pointureId]);
    
    if ($success) {
        $stmt = $db->prepare("SELECT id, label FROM pointures WHERE id = ?");
        $stmt->execute([$pointureId]);
        $updatedPointure = $stmt->fetch();
        
        sendJsonResponse(['success' => true, 'message' => 'Pointure mise à jour avec succès', 'data' => $updatedPointure]);
    } else {
        sendJsonResponse(['error' => 'Erreur lors de la mise à jour de la pointure', 'success' => false], 500);
    }
} elseif (isDeleteRequest()) {
    // Suppression d'une pointure
    if (!isset($_GET['id'])) {
        sendJsonResponse(['error' => 'ID pointure non spécifié', 'success' => false], 400);
    }
    
    $pointureId = $_GET['id'];
    
    // Vérifier si la pointure existe
    $stmt = $db->prepare("SELECT * FROM pointures WHERE id = ?");
    $stmt->execute([$pointureId]);
    if (!$stmt->fetch()) {
        sendJsonResponse(['error' => 'Pointure non trouvée', 'success' => false], 404);
    }
    
    // Vérifier si la pointure est utilisée dans des produits
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM produits WHERE id_pointure = ?");
    $stmt->execute([$pointureId]);
    $result = $stmt->fetch();
    
    if ($result['count'] > 0) {
        sendJsonResponse(['error' => 'Impossible de supprimer cette pointure car elle est utilisée par des produits', 'success' => false], 409);
    }
    
    // Suppression de la pointure
    $stmt = $db->prepare("DELETE FROM pointures WHERE id = ?");
    $success = $stmt->execute([$pointureId]);
    
    if ($success) {
        sendJsonResponse(['success' => true, 'message' => 'Pointure supprimée avec succès']);
    } else {
        sendJsonResponse(['error' => 'Erreur lors de la suppression de la pointure', 'success' => false], 500);
    }
} else {
    // Méthode non supportée
    sendJsonResponse(['error' => 'Méthode non supportée', 'success' => false], 405);
}
?>
