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

// Récupérer toutes les catégories
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->query("SELECT id, label as nom FROM categories ORDER BY label");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la récupération des catégories']);
    }
    exit;
} 

// Ajouter une nouvelle catégorie
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['nom']) || empty($data['nom'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Le nom de la catégorie est requis']);
        exit;
    }

    
    try {
        // Vérifier si une catégorie avec ce nom existe déjà
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE label = ?");
        $checkStmt->execute([$data['nom']]);
        
        if ($checkStmt->fetchColumn() > 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Une catégorie avec ce nom existe déjà']);
            exit;
        }
        
        $stmt = $pdo->prepare("INSERT INTO categories (label) VALUES (?)");
        $stmt->execute([$data['nom']]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Catégorie ajoutée avec succès',
            'id' => $pdo->lastInsertId()
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout de la catégorie']);
    }
    exit;
}

// Modifier une catégorie
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id']) || empty($data['id']) || !isset($data['nom']) || empty($data['nom'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'L\'ID et le nom de la catégorie sont requis']);
        exit;
    }
    
    try {
        // Vérifier si une autre catégorie avec ce nom existe déjà
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE label = ? AND id != ?");
        $checkStmt->execute([$data['nom'], $data['id']]);
        
        if ($checkStmt->fetchColumn() > 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Une catégorie avec ce nom existe déjà']);
            exit;
        }
        
        $stmt = $pdo->prepare("UPDATE categories SET label = ? WHERE id = ?");
        $stmt->execute([$data['nom'], $data['id']]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Catégorie mise à jour avec succès']);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Catégorie non trouvée']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour de la catégorie']);
    }
    exit;
}

// Supprimer une catégorie
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'L\'ID de la catégorie est requis']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Catégorie supprimée avec succès']);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Catégorie non trouvée']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression de la catégorie']);
    }
    exit;
} 