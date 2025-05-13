<?php
/**
 * API pour la gestion des produits (CRUD)
 */

// Inclusion du fichier CORS
require_once __DIR__ . '/cors.php';

// Inclusion des utilitaires
require_once __DIR__ . '/utils.php';

// Obtenir la connexion à la base de données
$db = getDbConnection();

// Traitement en fonction de la méthode HTTP
if (isGetRequest()) {
    // Récupération d'un produit spécifique ou de tous les produits
    if (isset($_GET['id'])) {
        // Récupération d'un produit par son ID
        $stmt = $db->prepare("
            SELECT p.*, 
                   c.id as categorie_id, c.label as categorie_label, 
                   co.id as couleur_id, co.label as couleur_label, 
                   po.id as pointure_id, po.label as pointure_label, 
                   s.id as sexe_id, s.label as sexe_label 
            FROM produits p
            LEFT JOIN categories c ON p.id_categorie = c.id
            LEFT JOIN couleurs co ON p.id_couleur = co.id
            LEFT JOIN pointures po ON p.id_pointure = po.id
            LEFT JOIN sexes s ON p.id_sexe = s.id
            WHERE p.id = ?
        ");
        $stmt->execute([$_GET['id']]);
        $produit = $stmt->fetch();
        
        if ($produit) {
            sendJsonResponse($produit);
        } else {
            sendJsonResponse(['error' => 'Produit non trouvé'], 404);
        }
    } else {
        // Récupération de tous les produits avec filtres optionnels
        $query = "
            SELECT p.*, 
                   c.id as categorie_id, c.label as categorie_label, 
                   co.id as couleur_id, co.label as couleur_label, 
                   po.id as pointure_id, po.label as pointure_label, 
                   s.id as sexe_id, s.label as sexe_label 
            FROM produits p
            LEFT JOIN categories c ON p.id_categorie = c.id
            LEFT JOIN couleurs co ON p.id_couleur = co.id
            LEFT JOIN pointures po ON p.id_pointure = po.id
            LEFT JOIN sexes s ON p.id_sexe = s.id
            WHERE 1=1
        ";
        $params = [];
        
        // Filtres optionnels
        if (isset($_GET['categorie'])) {
            $query .= " AND p.id_categorie = ?";
            $params[] = $_GET['categorie'];
        }
        
        if (isset($_GET['couleur'])) {
            $query .= " AND p.id_couleur = ?";
            $params[] = $_GET['couleur'];
        }
        
        if (isset($_GET['pointure'])) {
            $query .= " AND p.id_pointure = ?";
            $params[] = $_GET['pointure'];
        }
        
        if (isset($_GET['sexe'])) {
            $query .= " AND p.id_sexe = ?";
            $params[] = $_GET['sexe'];
        }
        
        if (isset($_GET['prix_min'])) {
            $query .= " AND p.price >= ?";
            $params[] = $_GET['prix_min'];
        }
        
        if (isset($_GET['prix_max'])) {
            $query .= " AND p.price <= ?";
            $params[] = $_GET['prix_max'];
        }
        
        // Exécution de la requête
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $produits = $stmt->fetchAll();
        
        sendJsonResponse($produits);
    }
} elseif (isPostRequest()) {
    // Création d'un nouveau produit
    $data = getRequestData();
    
    // Validation des champs requis
    $requiredFields = ['title', 'description', 'id_couleur', 'id_pointure', 'price', 'url_image', 'id_categorie', 'id_sexe'];
    if (!validateRequiredFields($data, $requiredFields)) {
        sendJsonResponse(['error' => 'Tous les champs requis ne sont pas renseignés'], 400);
    }
    
    // Vérification des clés étrangères
    $foreignKeys = [
        ['table' => 'couleurs', 'id' => $data['id_couleur']],
        ['table' => 'pointures', 'id' => $data['id_pointure']],
        ['table' => 'categories', 'id' => $data['id_categorie']],
        ['table' => 'sexes', 'id' => $data['id_sexe']]
    ];
    
    foreach ($foreignKeys as $fk) {
        $stmt = $db->prepare("SELECT id FROM {$fk['table']} WHERE id = ?");
        $stmt->execute([$fk['id']]);
        if (!$stmt->fetch()) {
            sendJsonResponse(['error' => "La référence à {$fk['table']} (ID: {$fk['id']}) n'existe pas"], 400);
        }
    }
    
    // Insertion du nouveau produit
    $stmt = $db->prepare("
        INSERT INTO produits (title, description, id_couleur, id_pointure, price, url_image, id_categorie, id_sexe) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $success = $stmt->execute([
        $data['title'],
        $data['description'],
        $data['id_couleur'],
        $data['id_pointure'],
        $data['price'],
        $data['url_image'],
        $data['id_categorie'],
        $data['id_sexe']
    ]);
    
    if ($success) {
        $produitId = $db->lastInsertId();
        $stmt = $db->prepare("
            SELECT p.*, 
                   c.id as categorie_id, c.label as categorie_label, 
                   co.id as couleur_id, co.label as couleur_label, 
                   po.id as pointure_id, po.label as pointure_label, 
                   s.id as sexe_id, s.label as sexe_label 
            FROM produits p
            LEFT JOIN categories c ON p.id_categorie = c.id
            LEFT JOIN couleurs co ON p.id_couleur = co.id
            LEFT JOIN pointures po ON p.id_pointure = po.id
            LEFT JOIN sexes s ON p.id_sexe = s.id
            WHERE p.id = ?
        ");
        $stmt->execute([$produitId]);
        $newProduit = $stmt->fetch();
        
        sendJsonResponse($newProduit, 201);
    } else {
        sendJsonResponse(['error' => 'Erreur lors de la création du produit'], 500);
    }
} elseif (isPutRequest()) {
    // Mise à jour d'un produit existant
    if (!isset($_GET['id'])) {
        sendJsonResponse(['error' => 'ID produit non spécifié'], 400);
    }
    
    $produitId = $_GET['id'];
    $data = getRequestData();
    
    // Vérifier si le produit existe
    $stmt = $db->prepare("SELECT * FROM produits WHERE id = ?");
    $stmt->execute([$produitId]);
    if (!$stmt->fetch()) {
        sendJsonResponse(['error' => 'Produit non trouvé'], 404);
    }
    
    // Construction de la requête de mise à jour
    $updateFields = [];
    $params = [];
    
    // Champs pouvant être mis à jour
    $allowedFields = ['title', 'description', 'id_couleur', 'id_pointure', 'price', 'url_image', 'id_categorie', 'id_sexe'];
    
    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            // Vérification des clés étrangères
            if (in_array($field, ['id_couleur', 'id_pointure', 'id_categorie', 'id_sexe'])) {
                $table = str_replace('id_', '', $field) . 's'; // Convertir id_couleur en couleurs, etc.
                $stmt = $db->prepare("SELECT id FROM {$table} WHERE id = ?");
                $stmt->execute([$data[$field]]);
                if (!$stmt->fetch()) {
                    sendJsonResponse(['error' => "La référence à {$table} (ID: {$data[$field]}) n'existe pas"], 400);
                }
            }
            
            $updateFields[] = "$field = ?";
            $params[] = $data[$field];
        }
    }
    
    if (empty($updateFields)) {
        sendJsonResponse(['error' => 'Aucun champ à mettre à jour'], 400);
    }
    
    // Ajout de l'ID à la fin des paramètres
    $params[] = $produitId;
    
    // Exécution de la mise à jour
    $sql = "UPDATE produits SET " . implode(', ', $updateFields) . " WHERE id = ?";
    $stmt = $db->prepare($sql);
    $success = $stmt->execute($params);
    
    if ($success) {
        $stmt = $db->prepare("
            SELECT p.*, 
                   c.id as categorie_id, c.label as categorie_label, 
                   co.id as couleur_id, co.label as couleur_label, 
                   po.id as pointure_id, po.label as pointure_label, 
                   s.id as sexe_id, s.label as sexe_label 
            FROM produits p
            LEFT JOIN categories c ON p.id_categorie = c.id
            LEFT JOIN couleurs co ON p.id_couleur = co.id
            LEFT JOIN pointures po ON p.id_pointure = po.id
            LEFT JOIN sexes s ON p.id_sexe = s.id
            WHERE p.id = ?
        ");
        $stmt->execute([$produitId]);
        $updatedProduit = $stmt->fetch();
        
        sendJsonResponse($updatedProduit);
    } else {
        sendJsonResponse(['error' => 'Erreur lors de la mise à jour du produit'], 500);
    }
} elseif (isDeleteRequest()) {
    // Suppression d'un produit
    if (!isset($_GET['id'])) {
        sendJsonResponse(['error' => 'ID produit non spécifié'], 400);
    }
    
    $produitId = $_GET['id'];
    
    // Vérifier si le produit existe
    $stmt = $db->prepare("SELECT * FROM produits WHERE id = ?");
    $stmt->execute([$produitId]);
    if (!$stmt->fetch()) {
        sendJsonResponse(['error' => 'Produit non trouvé'], 404);
    }
    
    // Vérifier si le produit est utilisé dans des commandes
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM items WHERE id_produit = ?");
    $stmt->execute([$produitId]);
    $result = $stmt->fetch();
    
    if ($result['count'] > 0) {
        sendJsonResponse(['error' => 'Impossible de supprimer ce produit car il est utilisé dans des commandes'], 409);
    }
    
    // Suppression du produit
    $stmt = $db->prepare("DELETE FROM produits WHERE id = ?");
    $success = $stmt->execute([$produitId]);
    
    if ($success) {
        sendJsonResponse(['message' => 'Produit supprimé avec succès']);
    } else {
        sendJsonResponse(['error' => 'Erreur lors de la suppression du produit'], 500);
    }
} else {
    // Méthode non supportée
    sendJsonResponse(['error' => 'Méthode non supportée'], 405);
}
?>
