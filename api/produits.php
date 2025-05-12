<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json");

// Gérer les requêtes OPTIONS (pré-vérification CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Connexion à la base de données
try {
    $pdo = new PDO("mysql:host=localhost;dbname=sneakme", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur de connexion à la base de données']);
    exit;
}

// Récupérer tous les produits
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->query("SELECT 
            produits.*, 
            couleurs.label as couleur, 
            pointures.label as pointure, 
            categories.label as categorie, 
            sexes.label as sexe,
            produits.id_couleur,
            produits.id_pointure,
            produits.id_categorie,
            produits.id_sexe
        FROM produits 
        JOIN couleurs on couleurs.id = produits.id_couleur 
        JOIN pointures on pointures.id = produits.id_pointure 
        JOIN categories on categories.id = produits.id_categorie 
        JOIN sexes on sexes.id = produits.id_sexe
        ");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la récupération des produits']);
    }
    exit;
}

// Ajouter un nouveau produit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Validation basique des données
        if (!isset($data['title']) || !isset($data['description']) || 
            !isset($data['price']) || !isset($data['url_image']) || 
            !isset($data['id_couleur']) || !isset($data['id_pointure']) || 
            !isset($data['id_categorie']) || !isset($data['id_sexe'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Données incomplètes']);
            exit;
        }
        
        // Insertion du produit
        $stmt = $pdo->prepare("INSERT INTO produits (title, description, price, url_image, id_couleur, id_pointure, id_categorie, id_sexe) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['title'],
            $data['description'],
            $data['price'],
            $data['url_image'],
            $data['id_couleur'],
            $data['id_pointure'],
            $data['id_categorie'],
            $data['id_sexe']
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Produit ajouté avec succès']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout du produit: ' . $e->getMessage()]);
    }
    exit;
}

// Mettre à jour un produit
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    try {
        // Vérifier si l'ID est fourni
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID produit non fourni']);
            exit;
        }
        
        $productId = $_GET['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Vérifier si le produit existe
        $check = $pdo->prepare("SELECT id FROM produits WHERE id = ? LIMIT 1");
        $check->execute([$productId]);
        if ($check->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Produit non trouvé']);
            exit;
        }
        
        // Validation basique des données
        if (!isset($data['title']) || !isset($data['description']) || 
            !isset($data['price']) || !isset($data['url_image']) || 
            !isset($data['id_couleur']) || !isset($data['id_pointure']) || 
            !isset($data['id_categorie']) || !isset($data['id_sexe'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Données incomplètes']);
            exit;
        }
        
        // Mise à jour du produit
        $stmt = $pdo->prepare("UPDATE produits SET 
                              title = ?, 
                              description = ?, 
                              price = ?, 
                              url_image = ?, 
                              id_couleur = ?, 
                              id_pointure = ?, 
                              id_categorie = ?, 
                              id_sexe = ? 
                              WHERE id = ?");
        $stmt->execute([
            $data['title'],
            $data['description'],
            $data['price'],
            $data['url_image'],
            $data['id_couleur'],
            $data['id_pointure'],
            $data['id_categorie'],
            $data['id_sexe'],
            $productId
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Produit mis à jour avec succès']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour du produit: ' . $e->getMessage()]);
    }
    exit;
}

// Supprimer un produit
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    try {
        // Vérifier si l'ID est fourni
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID produit non fourni']);
            exit;
        }
        
        $productId = $_GET['id'];
        
        // Vérifier si le produit existe
        $check = $pdo->prepare("SELECT id FROM produits WHERE id = ? LIMIT 1");
        $check->execute([$productId]);
        if ($check->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Produit non trouvé']);
            exit;
        }
        
        // Supprimer le produit
        $stmt = $pdo->prepare("DELETE FROM produits WHERE id = ?");
        $stmt->execute([$productId]);
        
        echo json_encode(['success' => true, 'message' => 'Produit supprimé avec succès']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression du produit: ' . $e->getMessage()]);
    }
    exit;
}
