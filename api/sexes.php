<?php
/**
 * API pour la gestion des sexes (CRUD)
 */

// Inclusion du fichier CORS
require_once __DIR__ . '/cors.php';

// Inclusion des utilitaires
require_once __DIR__ . '/utils.php';

// Obtenir la connexion à la base de données
$db = getDbConnection();

// Traitement en fonction de la méthode HTTP
if (isGetRequest()) {
    // Récupération d'un sexe spécifique ou de tous les sexes
    if (isset($_GET['id'])) {
        // Récupération d'un sexe par son ID
        $stmt = $db->prepare("SELECT * FROM sexes WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $sexe = $stmt->fetch();
        
        if ($sexe) {
            sendJsonResponse($sexe);
        } else {
            sendJsonResponse(['error' => 'Sexe non trouvé'], 404);
        }
    } else {
        // Récupération de tous les sexes
        $stmt = $db->query("SELECT * FROM sexes");
        $sexes = $stmt->fetchAll();
        sendJsonResponse($sexes);
    }
} elseif (isPostRequest()) {
    // Création d'un nouveau sexe
    $data = getRequestData();
    
    // Validation des champs requis
    if (!isset($data['label']) || empty($data['label'])) {
        sendJsonResponse(['error' => 'Le label du sexe est requis'], 400);
    }
    
    // Vérifier si le sexe existe déjà
    $stmt = $db->prepare("SELECT id FROM sexes WHERE label = ?");
    $stmt->execute([$data['label']]);
    if ($stmt->fetch()) {
        sendJsonResponse(['error' => 'Ce sexe existe déjà'], 409);
    }
    
    // Insertion du nouveau sexe
    $stmt = $db->prepare("INSERT INTO sexes (label) VALUES (?)");
    $success = $stmt->execute([$data['label']]);
    
    if ($success) {
        $sexeId = $db->lastInsertId();
        $stmt = $db->prepare("SELECT * FROM sexes WHERE id = ?");
        $stmt->execute([$sexeId]);
        $newSexe = $stmt->fetch();
        
        sendJsonResponse($newSexe, 201);
    } else {
        sendJsonResponse(['error' => 'Erreur lors de la création du sexe'], 500);
    }
} elseif (isPutRequest()) {
    // Mise à jour d'un sexe existant
    if (!isset($_GET['id'])) {
        sendJsonResponse(['error' => 'ID sexe non spécifié'], 400);
    }
    
    $sexeId = $_GET['id'];
    $data = getRequestData();
    
    // Validation des champs requis
    if (!isset($data['label']) || empty($data['label'])) {
        sendJsonResponse(['error' => 'Le label du sexe est requis'], 400);
    }
    
    // Vérifier si le sexe existe
    $stmt = $db->prepare("SELECT * FROM sexes WHERE id = ?");
    $stmt->execute([$sexeId]);
    if (!$stmt->fetch()) {
        sendJsonResponse(['error' => 'Sexe non trouvé'], 404);
    }
    
    // Vérifier si le nouveau label existe déjà pour un autre sexe
    $stmt = $db->prepare("SELECT id FROM sexes WHERE label = ? AND id != ?");
    $stmt->execute([$data['label'], $sexeId]);
    if ($stmt->fetch()) {
        sendJsonResponse(['error' => 'Un sexe avec ce label existe déjà'], 409);
    }
    
    // Mise à jour du sexe
    $stmt = $db->prepare("UPDATE sexes SET label = ? WHERE id = ?");
    $success = $stmt->execute([$data['label'], $sexeId]);
    
    if ($success) {
        $stmt = $db->prepare("SELECT * FROM sexes WHERE id = ?");
        $stmt->execute([$sexeId]);
        $updatedSexe = $stmt->fetch();
        
        sendJsonResponse($updatedSexe);
    } else {
        sendJsonResponse(['error' => 'Erreur lors de la mise à jour du sexe'], 500);
    }
} elseif (isDeleteRequest()) {
    // Suppression d'un sexe
    if (!isset($_GET['id'])) {
        sendJsonResponse(['error' => 'ID sexe non spécifié'], 400);
    }
    
    $sexeId = $_GET['id'];
    
    // Vérifier si le sexe existe
    $stmt = $db->prepare("SELECT * FROM sexes WHERE id = ?");
    $stmt->execute([$sexeId]);
    if (!$stmt->fetch()) {
        sendJsonResponse(['error' => 'Sexe non trouvé'], 404);
    }
    
    // Vérifier si le sexe est utilisé dans des produits
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM produits WHERE id_sexe = ?");
    $stmt->execute([$sexeId]);
    $result = $stmt->fetch();
    
    if ($result['count'] > 0) {
        sendJsonResponse(['error' => 'Impossible de supprimer ce sexe car il est utilisé par des produits'], 409);
    }
    
    // Suppression du sexe
    $stmt = $db->prepare("DELETE FROM sexes WHERE id = ?");
    $success = $stmt->execute([$sexeId]);
    
    if ($success) {
        sendJsonResponse(['message' => 'Sexe supprimé avec succès']);
    } else {
        sendJsonResponse(['error' => 'Erreur lors de la suppression du sexe'], 500);
    }
} else {
    // Méthode non supportée
    sendJsonResponse(['error' => 'Méthode non supportée'], 405);
}
?>
