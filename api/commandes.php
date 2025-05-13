<?php
/**
 * API pour la gestion des commandes (CRUD)
 */

// Inclusion du fichier CORS
require_once __DIR__ . '/cors.php';

// Inclusion des utilitaires
require_once __DIR__ . '/utils.php';

// Obtenir la connexion à la base de données
$db = getDbConnection();

// Traitement en fonction de la méthode HTTP
if (isGetRequest()) {
    // Récupération d'une commande spécifique ou de toutes les commandes
    if (isset($_GET['id'])) {
        // Récupération d'une commande par son ID
        $stmt = $db->prepare("
            SELECT c.*, u.username, u.nom, u.prenom, u.email 
            FROM commandes c
            JOIN users u ON c.id_user = u.id
            WHERE c.id = ?
        ");
        $stmt->execute([$_GET['id']]);
        $commande = $stmt->fetch();
        
        if ($commande) {
            // Récupérer les items de la commande
            $stmt = $db->prepare("
                SELECT i.*, p.title as produit_title, p.price, p.url_image, 
                       c.label as categorie, co.label as couleur, po.label as pointure, s.label as sexe
                FROM items i
                JOIN produits p ON i.id_produit = p.id
                LEFT JOIN categories c ON p.id_categorie = c.id
                LEFT JOIN couleurs co ON p.id_couleur = co.id
                LEFT JOIN pointures po ON p.id_pointure = po.id
                LEFT JOIN sexes s ON p.id_sexe = s.id
                WHERE i.id_commande = ?
            ");
            $stmt->execute([$_GET['id']]);
            $items = $stmt->fetchAll();
            
            $commande['items'] = $items;
            
            sendJsonResponse($commande);
        } else {
            sendJsonResponse(['error' => 'Commande non trouvée'], 404);
        }
    } else {
        // Récupération de toutes les commandes avec filtres optionnels
        $query = "
            SELECT c.*, u.username, u.nom, u.prenom, u.email 
            FROM commandes c
            JOIN users u ON c.id_user = u.id
            WHERE 1=1
        ";
        $params = [];
        
        // Filtres optionnels
        if (isset($_GET['user_id'])) {
            $query .= " AND c.id_user = ?";
            $params[] = $_GET['user_id'];
        }
        
        if (isset($_GET['date_debut'])) {
            $query .= " AND c.created_at >= ?";
            $params[] = $_GET['date_debut'];
        }
        
        if (isset($_GET['date_fin'])) {
            $query .= " AND c.created_at <= ?";
            $params[] = $_GET['date_fin'];
        }
        
        // Tri par date de création décroissante (la plus récente en premier)
        $query .= " ORDER BY c.created_at DESC";
        
        // Exécution de la requête
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $commandes = $stmt->fetchAll();
        
        // Pour chaque commande, récupérer ses items
        foreach ($commandes as &$commande) {
            $stmt = $db->prepare("
                SELECT i.*, p.title as produit_title, p.price
                FROM items i
                JOIN produits p ON i.id_produit = p.id
                WHERE i.id_commande = ?
            ");
            $stmt->execute([$commande['id']]);
            $items = $stmt->fetchAll();
            
            $commande['items'] = $items;
        }
        
        sendJsonResponse($commandes);
    }
} elseif (isPostRequest()) {
    // Création d'une nouvelle commande
    $data = getRequestData();
    
    // Validation des champs requis
    if (!isset($data['id_user']) || empty($data['id_user'])) {
        sendJsonResponse(['error' => 'L\'ID de l\'utilisateur est requis'], 400);
    }
    
    if (!isset($data['items']) || !is_array($data['items']) || empty($data['items'])) {
        sendJsonResponse(['error' => 'La commande doit contenir au moins un produit'], 400);
    }
    
    // Vérifier si l'utilisateur existe
    $stmt = $db->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$data['id_user']]);
    if (!$stmt->fetch()) {
        sendJsonResponse(['error' => 'L\'utilisateur spécifié n\'existe pas'], 400);
    }
    
    // Vérifier si tous les produits existent
    foreach ($data['items'] as $item) {
        if (!isset($item['id_produit']) || empty($item['id_produit'])) {
            sendJsonResponse(['error' => 'L\'ID du produit est requis pour chaque item'], 400);
        }
        
        $stmt = $db->prepare("SELECT id FROM produits WHERE id = ?");
        $stmt->execute([$item['id_produit']]);
        if (!$stmt->fetch()) {
            sendJsonResponse(['error' => 'Le produit avec l\'ID ' . $item['id_produit'] . ' n\'existe pas'], 400);
        }
    }
    
    try {
        // Démarrer une transaction
        $db->beginTransaction();
        
        // Insertion de la nouvelle commande
        $stmt = $db->prepare("INSERT INTO commandes (id_user) VALUES (?)");
        $stmt->execute([$data['id_user']]);
        $commandeId = $db->lastInsertId();
        
        // Insertion des items de la commande
        $stmt = $db->prepare("INSERT INTO items (id_commande, id_produit) VALUES (?, ?)");
        foreach ($data['items'] as $item) {
            $stmt->execute([$commandeId, $item['id_produit']]);
        }
        
        // Valider la transaction
        $db->commit();
        
        // Récupérer la commande créée avec ses items
        $stmt = $db->prepare("
            SELECT c.*, u.username, u.nom, u.prenom, u.email 
            FROM commandes c
            JOIN users u ON c.id_user = u.id
            WHERE c.id = ?
        ");
        $stmt->execute([$commandeId]);
        $commande = $stmt->fetch();
        
        $stmt = $db->prepare("
            SELECT i.*, p.title as produit_title, p.price, p.url_image, 
                   c.label as categorie, co.label as couleur, po.label as pointure, s.label as sexe
            FROM items i
            JOIN produits p ON i.id_produit = p.id
            LEFT JOIN categories c ON p.id_categorie = c.id
            LEFT JOIN couleurs co ON p.id_couleur = co.id
            LEFT JOIN pointures po ON p.id_pointure = po.id
            LEFT JOIN sexes s ON p.id_sexe = s.id
            WHERE i.id_commande = ?
        ");
        $stmt->execute([$commandeId]);
        $items = $stmt->fetchAll();
        
        $commande['items'] = $items;
        
        sendJsonResponse($commande, 201);
    } catch (Exception $e) {
        // En cas d'erreur, annuler la transaction
        $db->rollBack();
        sendJsonResponse(['error' => 'Erreur lors de la création de la commande: ' . $e->getMessage()], 500);
    }
} elseif (isDeleteRequest()) {
    // Suppression d'une commande
    if (!isset($_GET['id'])) {
        sendJsonResponse(['error' => 'ID commande non spécifié'], 400);
    }
    
    $commandeId = $_GET['id'];
    
    // Vérifier si la commande existe
    $stmt = $db->prepare("SELECT * FROM commandes WHERE id = ?");
    $stmt->execute([$commandeId]);
    if (!$stmt->fetch()) {
        sendJsonResponse(['error' => 'Commande non trouvée'], 404);
    }
    
    try {
        // Démarrer une transaction
        $db->beginTransaction();
        
        // Supprimer d'abord les items de la commande
        $stmt = $db->prepare("DELETE FROM items WHERE id_commande = ?");
        $stmt->execute([$commandeId]);
        
        // Puis supprimer la commande
        $stmt = $db->prepare("DELETE FROM commandes WHERE id = ?");
        $stmt->execute([$commandeId]);
        
        // Valider la transaction
        $db->commit();
        
        sendJsonResponse(['message' => 'Commande supprimée avec succès']);
    } catch (Exception $e) {
        // En cas d'erreur, annuler la transaction
        $db->rollBack();
        sendJsonResponse(['error' => 'Erreur lors de la suppression de la commande: ' . $e->getMessage()], 500);
    }
} else {
    // Méthode non supportée
    sendJsonResponse(['error' => 'Méthode non supportée'], 405);
}
?>
