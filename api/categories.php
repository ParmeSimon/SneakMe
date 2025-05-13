<?php
/**
 * API pour la gestion des catégories (CRUD)
 */

// Inclusion du fichier CORS
require_once __DIR__ . '/cors.php';

// Inclusion des utilitaires
require_once __DIR__ . '/utils.php';

// Obtenir la connexion à la base de données
$db = getDbConnection();

// Traitement en fonction de la méthode HTTP
if (isGetRequest()) {
    // Récupération d'une catégorie spécifique ou de toutes les catégories
    if (isset($_GET['id'])) {
        // Récupération d'une catégorie par son ID
        $stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $categorie = $stmt->fetch();
        
        if ($categorie) {
            sendJsonResponse(['success' => true, 'data' => $categorie]);
        } else {
            sendJsonResponse(['error' => 'Catégorie non trouvée', 'success' => false], 404);
        }
    } else {
        // Récupération de toutes les catégories
        $stmt = $db->query("SELECT * FROM categories");
        $categories = $stmt->fetchAll();
        // Retourner directement le tableau pour compatibilité avec le frontend
        sendJsonResponse($categories);
    }
} elseif (isPostRequest()) {
    // Création d'une nouvelle catégorie
    $data = getRequestData();
    
    // Validation des champs requis
    if (!isset($data['label']) || empty($data['label'])) {
        sendJsonResponse(['error' => 'Le label de la catégorie est requis', 'success' => false], 400);
    }
    
    // Vérifier si la catégorie existe déjà
    $stmt = $db->prepare("SELECT id FROM categories WHERE label = ?");
    $stmt->execute([$data['label']]);
    if ($stmt->fetch()) {
        sendJsonResponse(['error' => 'Cette catégorie existe déjà', 'success' => false], 409);
    }
    
    // Insertion de la nouvelle catégorie
    $stmt = $db->prepare("INSERT INTO categories (label) VALUES (?)");
    $success = $stmt->execute([$data['label']]);
    
    if ($success) {
        $categorieId = $db->lastInsertId();
        $stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$categorieId]);
        $newCategorie = $stmt->fetch();
        
        sendJsonResponse(['success' => true, 'message' => 'Catégorie ajoutée avec succès', 'data' => $newCategorie], 201);
    } else {
        sendJsonResponse(['error' => 'Erreur lors de la création de la catégorie', 'success' => false], 500);
    }
} elseif (isPutRequest()) {
    // Mise à jour d'une catégorie existante
    $data = getRequestData();
    
    if (isset($data['id'])) {
        $categorieId = $data['id'];
    } elseif (isset($_GET['id'])) {
        $categorieId = $_GET['id'];
    } else {
        sendJsonResponse(['error' => 'ID catégorie non spécifié', 'success' => false], 400);
    }
    
    // Validation des champs requis
    if (!isset($data['label']) || empty($data['label'])) {
        sendJsonResponse(['error' => 'Le label de la catégorie est requis', 'success' => false], 400);
    }
    
    // Vérifier si la catégorie existe
    $stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$categorieId]);
    if (!$stmt->fetch()) {
        sendJsonResponse(['error' => 'Catégorie non trouvée', 'success' => false], 404);
    }
    
    // Vérifier si le nouveau label existe déjà pour une autre catégorie
    $stmt = $db->prepare("SELECT id FROM categories WHERE label = ? AND id != ?");
    $stmt->execute([$data['label'], $categorieId]);
    if ($stmt->fetch()) {
        sendJsonResponse(['error' => 'Une catégorie avec ce label existe déjà', 'success' => false], 409);
    }
    
    // Mise à jour de la catégorie
    $stmt = $db->prepare("UPDATE categories SET label = ? WHERE id = ?");
    $success = $stmt->execute([$data['label'], $categorieId]);
    
    if ($success) {
        $stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$categorieId]);
        $updatedCategorie = $stmt->fetch();
        
        sendJsonResponse(['success' => true, 'message' => 'Catégorie mise à jour avec succès', 'data' => $updatedCategorie]);
    } else {
        sendJsonResponse(['error' => 'Erreur lors de la mise à jour de la catégorie', 'success' => false], 500);
    }
} elseif (isDeleteRequest()) {
    // Suppression d'une catégorie
    if (!isset($_GET['id'])) {
        sendJsonResponse(['error' => 'ID catégorie non spécifié', 'success' => false], 400);
    }
    
    $categorieId = $_GET['id'];
    
    // Vérifier si la catégorie existe
    $stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$categorieId]);
    if (!$stmt->fetch()) {
        sendJsonResponse(['error' => 'Catégorie non trouvée', 'success' => false], 404);
    }
    
    // Vérifier si la catégorie est utilisée dans des produits
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM produits WHERE id_categorie = ?");
    $stmt->execute([$categorieId]);
    $result = $stmt->fetch();
    
    if ($result['count'] > 0) {
        sendJsonResponse(['error' => 'Impossible de supprimer cette catégorie car elle est utilisée par des produits', 'success' => false], 409);
    }
    
    // Suppression de la catégorie
    $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
    $success = $stmt->execute([$categorieId]);
    
    if ($success) {
        sendJsonResponse(['success' => true, 'message' => 'Catégorie supprimée avec succès']);
    } else {
        sendJsonResponse(['error' => 'Erreur lors de la suppression de la catégorie', 'success' => false], 500);
    }
} else {
    // Méthode non supportée
    sendJsonResponse(['error' => 'Méthode non supportée', 'success' => false], 405);
}
?>
