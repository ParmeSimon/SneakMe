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

// Récupérer tous les mots-clés
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->query("SELECT * FROM actions");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la récupération des mots-clés']);
    }
    exit;
}

// Ajouter un nouveau mot-clé
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Validation basique des données
        if (!isset($data['title']) || !isset($data['description']) || !isset($data['result'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Données incomplètes']);
            exit;
        }
        
        // Vérifier si le mot-clé existe déjà
        $check = $pdo->prepare("SELECT id FROM actions WHERE title = ? LIMIT 1");
        $check->execute([$data['title']]);
        if ($check->rowCount() > 0) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Ce mot-clé existe déjà']);
            exit;
        }
        
        // Insertion du mot-clé
        $stmt = $pdo->prepare("INSERT INTO actions (title, description, result) VALUES (?, ?, ?)");
        $stmt->execute([
            $data['title'],
            $data['description'],
            $data['result']
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Mot-clé ajouté avec succès']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout du mot-clé: ' . $e->getMessage()]);
    }
    exit;
}

// Mettre à jour un mot-clé
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    try {
        // Vérifier si l'ID est fourni
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID mot-clé non fourni']);
            exit;
        }
        
        $keywordId = $_GET['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Vérifier si le mot-clé existe
        $check = $pdo->prepare("SELECT id FROM actions WHERE id = ? LIMIT 1");
        $check->execute([$keywordId]);
        if ($check->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Mot-clé non trouvé']);
            exit;
        }
        
        // Validation basique des données
        if (!isset($data['title']) || !isset($data['description']) || !isset($data['result'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Données incomplètes']);
            exit;
        }
        
        // Vérifier si le titre existe déjà pour un autre mot-clé
        $checkTitle = $pdo->prepare("SELECT id FROM actions WHERE title = ? AND id != ? LIMIT 1");
        $checkTitle->execute([$data['title'], $keywordId]);
        if ($checkTitle->rowCount() > 0) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Ce mot-clé existe déjà']);
            exit;
        }
        
        // Mise à jour du mot-clé
        $stmt = $pdo->prepare("UPDATE actions SET title = ?, description = ?, result = ? WHERE id = ?");
        $stmt->execute([
            $data['title'],
            $data['description'],
            $data['result'],
            $keywordId
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Mot-clé mis à jour avec succès']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour du mot-clé: ' . $e->getMessage()]);
    }
    exit;
}

// Supprimer un mot-clé
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    try {
        // Vérifier si l'ID est fourni
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID mot-clé non fourni']);
            exit;
        }
        
        $keywordId = $_GET['id'];
        
        // Vérifier si le mot-clé existe
        $check = $pdo->prepare("SELECT id FROM actions WHERE id = ? LIMIT 1");
        $check->execute([$keywordId]);
        if ($check->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Mot-clé non trouvé']);
            exit;
        }
        
        // Supprimer le mot-clé
        $stmt = $pdo->prepare("DELETE FROM actions WHERE id = ?");
        $stmt->execute([$keywordId]);
        
        echo json_encode(['success' => true, 'message' => 'Mot-clé supprimé avec succès']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression du mot-clé: ' . $e->getMessage()]);
    }
    exit;
}
