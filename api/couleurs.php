<?php
/**
 * API pour la gestion des couleurs (CRUD)
 */

// Inclusion du fichier CORS
require_once __DIR__ . '/cors.php';

// Inclusion des utilitaires
require_once __DIR__ . '/utils.php';

// Obtenir la connexion à la base de données
$db = getDbConnection();

// Traitement en fonction de la méthode HTTP
if (isGetRequest()) {
    // Récupération d'une couleur spécifique ou de toutes les couleurs
    if (isset($_GET['id'])) {
        // Récupération d'une couleur par son ID
        $stmt = $db->prepare("SELECT id, label FROM couleurs WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $couleur = $stmt->fetch();
        
        if ($couleur) {
            sendJsonResponse(['success' => true, 'data' => $couleur]);
        } else {
            sendJsonResponse(['error' => 'Couleur non trouvée', 'success' => false], 404);
        }
    } else {
        // Récupération de toutes les couleurs
        $stmt = $db->query("SELECT id, label FROM couleurs");
        $couleurs = $stmt->fetchAll();
        sendJsonResponse($couleurs);
    }
} elseif (isPostRequest()) {
    // Création d'une nouvelle couleur
    $data = getRequestData();
    
    // Validation des champs requis
    // Vérifier si on utilise nom ou label dans la requête
    $labelValue = '';
    if (isset($data['nom']) && !empty($data['nom'])) {
        $labelValue = $data['nom'];
    } elseif (isset($data['label']) && !empty($data['label'])) {
        $labelValue = $data['label'];
    } else {
        sendJsonResponse(['error' => 'Le nom de la couleur est requis', 'success' => false], 400);
    }
    
    // Vérifier si la couleur existe déjà
    $stmt = $db->prepare("SELECT id FROM couleurs WHERE label = ?");
    $stmt->execute([$labelValue]);
    if ($stmt->fetch()) {
        sendJsonResponse(['error' => 'Cette couleur existe déjà', 'success' => false], 409);
    }
    
    // Insertion de la nouvelle couleur
    $stmt = $db->prepare("INSERT INTO couleurs (label) VALUES (?)");
    $success = $stmt->execute([$labelValue]);
    
    if ($success) {
        $couleurId = $db->lastInsertId();
        $stmt = $db->prepare("SELECT id, label FROM couleurs WHERE id = ?");
        $stmt->execute([$couleurId]);
        $newCouleur = $stmt->fetch();
        
        sendJsonResponse(['success' => true, 'message' => 'Couleur ajoutée avec succès', 'data' => $newCouleur], 201);
    } else {
        sendJsonResponse(['error' => 'Erreur lors de la création de la couleur', 'success' => false], 500);
    }
} elseif (isPutRequest()) {
    // Mise à jour d'une couleur existante
    $data = getRequestData();
    
    // Récupérer l'ID soit depuis l'URL, soit depuis les données
    if (isset($data['id'])) {
        $couleurId = $data['id'];
    } elseif (isset($_GET['id'])) {
        $couleurId = $_GET['id'];
    } else {
        sendJsonResponse(['error' => 'ID couleur non spécifié', 'success' => false], 400);
    }
    
    // Validation des champs requis - vérifier si on utilise nom ou label
    $labelValue = '';
    if (isset($data['nom']) && !empty($data['nom'])) {
        $labelValue = $data['nom'];
    } elseif (isset($data['label']) && !empty($data['label'])) {
        $labelValue = $data['label'];
    } else {
        sendJsonResponse(['error' => 'Le nom de la couleur est requis', 'success' => false], 400);
    }
    
    // Vérifier si la couleur existe
    $stmt = $db->prepare("SELECT * FROM couleurs WHERE id = ?");
    $stmt->execute([$couleurId]);
    if (!$stmt->fetch()) {
        sendJsonResponse(['error' => 'Couleur non trouvée', 'success' => false], 404);
    }
    
    // Vérifier si le nouveau label existe déjà pour une autre couleur
    $stmt = $db->prepare("SELECT id FROM couleurs WHERE label = ? AND id != ?");
    $stmt->execute([$labelValue, $couleurId]);
    if ($stmt->fetch()) {
        sendJsonResponse(['error' => 'Une couleur avec ce nom existe déjà', 'success' => false], 409);
    }
    
    // Mise à jour de la couleur
    $stmt = $db->prepare("UPDATE couleurs SET label = ? WHERE id = ?");
    $success = $stmt->execute([$labelValue, $couleurId]);
    
    if ($success) {
        $stmt = $db->prepare("SELECT id, label FROM couleurs WHERE id = ?");
        $stmt->execute([$couleurId]);
        $updatedCouleur = $stmt->fetch();
        
        sendJsonResponse(['success' => true, 'message' => 'Couleur mise à jour avec succès', 'data' => $updatedCouleur]);
    } else {
        sendJsonResponse(['error' => 'Erreur lors de la mise à jour de la couleur', 'success' => false], 500);
    }
} elseif (isDeleteRequest()) {
    // Suppression d'une couleur
    if (!isset($_GET['id'])) {
        sendJsonResponse(['error' => 'ID couleur non spécifié', 'success' => false], 400);
    }
    
    $couleurId = $_GET['id'];
    
    // Vérifier si la couleur existe
    $stmt = $db->prepare("SELECT * FROM couleurs WHERE id = ?");
    $stmt->execute([$couleurId]);
    if (!$stmt->fetch()) {
        sendJsonResponse(['error' => 'Couleur non trouvée', 'success' => false], 404);
    }
    
    // Vérifier si la couleur est utilisée dans des produits
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM produits WHERE id_couleur = ?");
    $stmt->execute([$couleurId]);
    $result = $stmt->fetch();
    
    if ($result['count'] > 0) {
        sendJsonResponse(['error' => 'Impossible de supprimer cette couleur car elle est utilisée par des produits', 'success' => false], 409);
    }
    
    // Suppression de la couleur
    $stmt = $db->prepare("DELETE FROM couleurs WHERE id = ?");
    $success = $stmt->execute([$couleurId]);
    
    if ($success) {
        sendJsonResponse(['success' => true, 'message' => 'Couleur supprimée avec succès']);
    } else {
        sendJsonResponse(['error' => 'Erreur lors de la suppression de la couleur', 'success' => false], 500);
    }
} else {
    // Méthode non supportée
    sendJsonResponse(['error' => 'Méthode non supportée', 'success' => false], 405);
}
?>
