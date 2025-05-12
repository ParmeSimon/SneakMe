<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json");

// Gérer les requêtes OPTIONS (pré-vérification CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=sneakme", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur de connexion à la base de données: ' . $e->getMessage()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Récupérer toutes les commandes avec les infos utilisateur et les produits associés
        $stmt = $pdo->query("
            SELECT 
                c.id as id_commande, 
                c.id_user, 
                c.created_at, 
                c.terminer, 
                u.prenom, 
                u.nom
            FROM commandes c
            JOIN users u ON c.id_user = u.id
            ORDER BY c.id DESC
        ");
        
        $commandes = $stmt->fetchAll();
        
        // Pour chaque commande, récupérer les produits associés
        foreach ($commandes as &$commande) {
            $stmt = $pdo->prepare("
                SELECT 
                    p.id as id_produit, 
                    p.title,
                    p.price,
                    p.url_image
                FROM items i
                JOIN produits p ON i.id_produit = p.id
                WHERE i.id_commande = ?
            ");
            $stmt->execute([$commande['id_commande']]);
            $commande['produits'] = $stmt->fetchAll();
        }
        
        echo json_encode($commandes);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la récupération des commandes: ' . $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['id_user'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID utilisateur non fourni']);
            exit;
        }
        
        $stmt = $pdo->prepare("INSERT INTO commandes (id_user, terminer) VALUES (?, ?)");
        $terminer = isset($data['terminer']) ? $data['terminer'] : 0;
        $stmt->execute([$data['id_user'], $terminer]);
        
        $id_commande = $pdo->lastInsertId();
        
        // Insérer les produits dans la commande
        if (isset($data['produits']) && is_array($data['produits'])) {
            $stmt = $pdo->prepare("INSERT INTO items (id_commande, id_produit) VALUES (?, ?)");
            foreach ($data['produits'] as $id_produit) {
                $stmt->execute([$id_commande, $id_produit]);
            }
        }
        
        echo json_encode(['success' => true, 'id_commande' => $id_commande]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la création de la commande: ' . $e->getMessage()]);
    }
    exit;
}

// Mettre à jour le statut d'une commande
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    try {
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID commande non fourni']);
            exit;
        }
        
        $id_commande = $_GET['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['terminer']) && $data['terminer'] !== 0 && $data['terminer'] !== 1) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Statut de commande non fourni ou invalide']);
            exit;
        }
        
        // Vérifier si la commande existe
        $check = $pdo->prepare("SELECT id FROM commandes WHERE id = ?");
        $check->execute([$id_commande]);
        if ($check->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Commande non trouvée']);
            exit;
        }
        
        $stmt = $pdo->prepare("UPDATE commandes SET terminer = ? WHERE id = ?");
        $result = $stmt->execute([$data['terminer'], $id_commande]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Statut de commande mis à jour']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Échec de la mise à jour du statut']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour du statut: ' . $e->getMessage()]);
    }
    exit;
}
