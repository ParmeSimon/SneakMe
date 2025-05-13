<?php
/**
 * API pour la gestion des items de commande (CRUD)
 */

// Inclusion du fichier CORS
require_once __DIR__ . '/cors.php';

// Inclusion des utilitaires
require_once __DIR__ . '/utils.php';

// Obtenir la connexion à la base de données
$db = getDbConnection();

// Traitement en fonction de la méthode HTTP
if (isGetRequest()) {
    // Récupération d'un item spécifique ou de tous les items
    if (isset($_GET['id'])) {
        // Récupération d'un item par son ID
        $stmt = $db->prepare("
            SELECT i.*, p.title as produit_title, p.description as produit_description, 
                   p.price, p.url_image, c.label as categorie, co.label as couleur, 
                   po.label as pointure, s.label as sexe
            FROM items i
            JOIN produits p ON i.id_produit = p.id
            LEFT JOIN categories c ON p.id_categorie = c.id
            LEFT JOIN couleurs co ON p.id_couleur = co.id
            LEFT JOIN pointures po ON p.id_pointure = po.id
            LEFT JOIN sexes s ON p.id_sexe = s.id
            WHERE i.id = ?
        ");
        $stmt->execute([$_GET['id']]);
        $item = $stmt->fetch();
        
        if ($item) {
            sendJsonResponse($item);
        } else {
            sendJsonResponse(['error' => 'Item non trouvé'], 404);
        }
    } else {
        // Récupération de tous les items avec filtres optionnels
        $query = "
            SELECT i.*, p.title as produit_title, p.price, p.url_image,
                   c.id as commande_id, c.created_at as commande_date
            FROM items i
            JOIN produits p ON i.id_produit = p.id
            JOIN commandes c ON i.id_commande = c.id
            WHERE 1=1
        ";
        $params = [];
        
        // Filtres optionnels
        if (isset($_GET['commande_id'])) {
            $query .= " AND i.id_commande = ?";
            $params[] = $_GET['commande_id'];
        }
        
        if (isset($_GET['produit_id'])) {
            $query .= " AND i.id_produit = ?";
            $params[] = $_GET['produit_id'];
        }
        
        // Exécution de la requête
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $items = $stmt->fetchAll();
        
        sendJsonResponse($items);
    }
} elseif (isPostRequest()) {
    // Création d'un nouvel item
    $data = getRequestData();
    
    // Validation des champs requis
    $requiredFields = ['id_commande', 'id_produit'];
    if (!validateRequiredFields($data, $requiredFields)) {
        sendJsonResponse(['error' => 'Les IDs de commande et de produit sont requis'], 400);
    }
    
    // Vérifier si la commande existe
    $stmt = $db->prepare("SELECT id FROM commandes WHERE id = ?");
    $stmt->execute([$data['id_commande']]);
    if (!$stmt->fetch()) {
        sendJsonResponse(['error' => 'La commande spécifiée n\'existe pas'], 400);
    }
    
    // Vérifier si le produit existe
    $stmt = $db->prepare("SELECT id FROM produits WHERE id = ?");
    $stmt->execute([$data['id_produit']]);
    if (!$stmt->fetch()) {
        sendJsonResponse(['error' => 'Le produit spécifié n\'existe pas'], 400);
    }
    
    // Insertion du nouvel item
    $stmt = $db->prepare("INSERT INTO items (id_commande, id_produit) VALUES (?, ?)");
    $success = $stmt->execute([$data['id_commande'], $data['id_produit']]);
    
    if ($success) {
        $itemId = $db->lastInsertId();
        $stmt = $db->prepare("
            SELECT i.*, p.title as produit_title, p.description as produit_description, 
                   p.price, p.url_image, c.label as categorie, co.label as couleur, 
                   po.label as pointure, s.label as sexe
            FROM items i
            JOIN produits p ON i.id_produit = p.id
            LEFT JOIN categories c ON p.id_categorie = c.id
            LEFT JOIN couleurs co ON p.id_couleur = co.id
            LEFT JOIN pointures po ON p.id_pointure = po.id
            LEFT JOIN sexes s ON p.id_sexe = s.id
            WHERE i.id = ?
        ");
        $stmt->execute([$itemId]);
        $newItem = $stmt->fetch();
        
        sendJsonResponse($newItem, 201);
    } else {
        sendJsonResponse(['error' => 'Erreur lors de la création de l\'item'], 500);
    }
} elseif (isPutRequest()) {
    // Mise à jour d'un item existant
    if (!isset($_GET['id'])) {
        sendJsonResponse(['error' => 'ID item non spécifié'], 400);
    }
    
    $itemId = $_GET['id'];
    $data = getRequestData();
    
    // Vérifier si l'item existe
    $stmt = $db->prepare("SELECT * FROM items WHERE id = ?");
    $stmt->execute([$itemId]);
    if (!$stmt->fetch()) {
        sendJsonResponse(['error' => 'Item non trouvé'], 404);
    }
    
    // Construction de la requête de mise à jour
    $updateFields = [];
    $params = [];
    
    // Champs pouvant être mis à jour
    if (isset($data['id_produit'])) {
        // Vérifier si le produit existe
        $stmt = $db->prepare("SELECT id FROM produits WHERE id = ?");
        $stmt->execute([$data['id_produit']]);
        if (!$stmt->fetch()) {
            sendJsonResponse(['error' => 'Le produit spécifié n\'existe pas'], 400);
        }
        
        $updateFields[] = "id_produit = ?";
        $params[] = $data['id_produit'];
    }
    
    if (empty($updateFields)) {
        sendJsonResponse(['error' => 'Aucun champ à mettre à jour'], 400);
    }
    
    // Ajout de l'ID à la fin des paramètres
    $params[] = $itemId;
    
    // Exécution de la mise à jour
    $sql = "UPDATE items SET " . implode(', ', $updateFields) . " WHERE id = ?";
    $stmt = $db->prepare($sql);
    $success = $stmt->execute($params);
    
    if ($success) {
        $stmt = $db->prepare("
            SELECT i.*, p.title as produit_title, p.description as produit_description, 
                   p.price, p.url_image, c.label as categorie, co.label as couleur, 
                   po.label as pointure, s.label as sexe
            FROM items i
            JOIN produits p ON i.id_produit = p.id
            LEFT JOIN categories c ON p.id_categorie = c.id
            LEFT JOIN couleurs co ON p.id_couleur = co.id
            LEFT JOIN pointures po ON p.id_pointure = po.id
            LEFT JOIN sexes s ON p.id_sexe = s.id
            WHERE i.id = ?
        ");
        $stmt->execute([$itemId]);
        $updatedItem = $stmt->fetch();
        
        sendJsonResponse($updatedItem);
    } else {
        sendJsonResponse(['error' => 'Erreur lors de la mise à jour de l\'item'], 500);
    }
} elseif (isDeleteRequest()) {
    // Suppression d'un item
    if (!isset($_GET['id'])) {
        sendJsonResponse(['error' => 'ID item non spécifié'], 400);
    }
    
    $itemId = $_GET['id'];
    
    // Vérifier si l'item existe
    $stmt = $db->prepare("SELECT * FROM items WHERE id = ?");
    $stmt->execute([$itemId]);
    if (!$stmt->fetch()) {
        sendJsonResponse(['error' => 'Item non trouvé'], 404);
    }
    
    // Suppression de l'item
    $stmt = $db->prepare("DELETE FROM items WHERE id = ?");
    $success = $stmt->execute([$itemId]);
    
    if ($success) {
        sendJsonResponse(['message' => 'Item supprimé avec succès']);
    } else {
        sendJsonResponse(['error' => 'Erreur lors de la suppression de l\'item'], 500);
    }
} else {
    // Méthode non supportée
    sendJsonResponse(['error' => 'Méthode non supportée'], 405);
}
?>
