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

// Récupérer tous les utilisateurs
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->query("SELECT * FROM users");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la récupération des utilisateurs']);
    }
    exit;
}

// Ajouter un nouvel utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Validation basique des données
        if (!isset($data['username']) || !isset($data['prenom']) || !isset($data['nom']) || 
            !isset($data['email']) || !isset($data['password'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Données incomplètes']);
            exit;
        }
        
        // Vérifier si l'utilisateur existe déjà
        $check = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
        $check->execute([$data['username'], $data['email']]);
        if ($check->rowCount() > 0) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Un utilisateur avec ce nom ou email existe déjà']);
            exit;
        }
        
        // Insertion de l'utilisateur
        $stmt = $pdo->prepare("INSERT INTO users (username, prenom, nom, email, password, isAdmin) VALUES (?, ?, ?, ?, ?, ?)");
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $isAdmin = isset($data['isAdmin']) ? $data['isAdmin'] : 0;
        $stmt->execute([
            $data['username'], 
            $data['prenom'], 
            $data['nom'], 
            $data['email'],
            $hashedPassword,
            $isAdmin
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Utilisateur ajouté avec succès']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout de l\'utilisateur']);
    }
    exit;
}

// Mettre à jour un utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    try {
        // Vérifier si l'ID est fourni
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID utilisateur non fourni']);
            exit;
        }
        
        $userId = $_GET['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Vérifier si l'utilisateur existe
        $check = $pdo->prepare("SELECT id FROM users WHERE id = ? LIMIT 1");
        $check->execute([$userId]);
        if ($check->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé']);
            exit;
        }
        
        // Construire la requête SQL en fonction des champs fournis
        $updateFields = [];
        $params = [];
        
        if (isset($data['username'])) {
            // Vérifier si le nom d'utilisateur existe déjà pour un autre utilisateur
            $checkUsername = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ? LIMIT 1");
            $checkUsername->execute([$data['username'], $userId]);
            if ($checkUsername->rowCount() > 0) {
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Ce nom d\'utilisateur est déjà utilisé']);
                exit;
            }
            $updateFields[] = "username = ?";
            $params[] = $data['username'];
        }
        
        if (isset($data['prenom'])) {
            $updateFields[] = "prenom = ?";
            $params[] = $data['prenom'];
        }
        
        if (isset($data['nom'])) {
            $updateFields[] = "nom = ?";
            $params[] = $data['nom'];
        }
        
        if (isset($data['email'])) {
            // Vérifier si l'email existe déjà pour un autre utilisateur
            $checkEmail = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1");
            $checkEmail->execute([$data['email'], $userId]);
            if ($checkEmail->rowCount() > 0) {
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé']);
                exit;
            }
            $updateFields[] = "email = ?";
            $params[] = $data['email'];
        }
        
        if (isset($data['password']) && !empty($data['password'])) {
            $updateFields[] = "password = ?";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        if (isset($data['isAdmin'])) {
            $updateFields[] = "isAdmin = ?";
            $params[] = $data['isAdmin'];
        }
        
        // Si aucun champ à mettre à jour, on sort
        if (empty($updateFields)) {
            echo json_encode(['success' => true, 'message' => 'Aucune modification apportée']);
            exit;
        }
        
        // Ajouter l'ID à la fin des paramètres
        $params[] = $userId;
        
        // Mettre à jour l'utilisateur
        $sql = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        echo json_encode(['success' => true, 'message' => 'Utilisateur mis à jour avec succès']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour de l\'utilisateur']);
    }
    exit;
}

// Supprimer un utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    try {
        // Vérifier si l'ID est fourni
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID utilisateur non fourni']);
            exit;
        }
        
        $userId = $_GET['id'];
        
        // Vérifier si l'utilisateur existe
        $check = $pdo->prepare("SELECT id FROM users WHERE id = ? LIMIT 1");
        $check->execute([$userId]);
        if ($check->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé']);
            exit;
        }
        
        // Supprimer l'utilisateur
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        
        echo json_encode(['success' => true, 'message' => 'Utilisateur supprimé avec succès']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression de l\'utilisateur']);
    }
    exit;
}
