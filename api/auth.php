<?php
/**
 * API pour l'authentification des utilisateurs (login et inscription)
 */

// Inclure la configuration CORS au début
require_once __DIR__ . '/cors.php';

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Définir le type de contenu JSON
header('Content-Type: application/json');

// Inclusion des utilitaires
require_once __DIR__ . '/utils.php';

try {
    // Obtenir la connexion à la base de données
    $db = getDbConnection();

// Vérifier si c'est une requête POST
if (isPostRequest()) {
    // Récupérer les données de la requête
    $data = getRequestData();
    
    // Vérifier l'action demandée
    if (isset($_GET['action'])) {
        $action = $_GET['action'];
        
        // Action de connexion
        if ($action === 'login') {
            // Vérifier que les champs requis sont présents
            if (!isset($data['email']) || !isset($data['password'])) {
                sendJsonResponse(['success' => false, 'message' => 'Email et mot de passe requis'], 400);
                exit;
            }
            
            $email = $data['email'];
            $password = $data['password'];
            
            // Rechercher l'utilisateur par email
            $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Vérifier le mot de passe
                // Note: Dans une application réelle, le mot de passe devrait être haché
                if ($user['password'] === $password) {
                    
                    // Connexion réussie
                    sendJsonResponse([
                        'success' => true,
                        'message' => 'Connexion réussie',
                        'user' => $user
                    ]);
                } else {
                    // Mot de passe incorrect
                    sendJsonResponse(['success' => false, 'message' => 'Email ou mot de passe incorrect'], 401);
                }
            } else {
                // Utilisateur non trouvé
                sendJsonResponse(['success' => false, 'message' => 'Email ou mot de passe incorrect'], 401);
            }
        }
        // Action d'inscription
        else if ($action === 'register') {
            // Vérifier que les champs requis sont présents
            $requiredFields = ['username', 'nom', 'prenom', 'email', 'password'];
            if (!validateRequiredFields($data, $requiredFields)) {
                sendJsonResponse(['success' => false, 'message' => 'Tous les champs requis ne sont pas renseignés'], 400);
                exit;
            }
            
            // Vérifier si l'email existe déjà
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$data['email']]);
            if ($stmt->fetch()) {
                sendJsonResponse(['success' => false, 'message' => 'Cet email est déjà utilisé'], 409);
                exit;
            }
            
            // Vérifier si le nom d'utilisateur existe déjà
            $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$data['username']]);
            if ($stmt->fetch()) {
                sendJsonResponse(['success' => false, 'message' => 'Ce nom d\'utilisateur est déjà utilisé'], 409);
                exit;
            }
            
            // Insertion du nouvel utilisateur
            $stmt = $db->prepare("
                INSERT INTO users (username, nom, prenom, email, password, isAdmin) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            // Par défaut, les nouveaux utilisateurs ne sont pas administrateurs
            $isAdmin = 0;
            
            $success = $stmt->execute([
                $data['username'],
                $data['nom'],
                $data['prenom'],
                $data['email'],
                $data['password'], // Note: Dans une application réelle, le mot de passe devrait être haché
                $isAdmin
            ]);
            
            if ($success) {
                $userId = $db->lastInsertId();
                
                // Récupérer les informations de l'utilisateur créé
                $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $newUser = $stmt->fetch();
                
                // Supprimer le mot de passe avant de renvoyer les données
                unset($newUser['password']);
                
                sendJsonResponse([
                    'success' => true,
                    'message' => 'Inscription réussie',
                    'user' => $newUser
                ], 201);
            } else {
                sendJsonResponse(['success' => false, 'message' => 'Erreur lors de l\'inscription'], 500);
            }
        } else {
            // Action non reconnue
            sendJsonResponse(['success' => false, 'message' => 'Action non reconnue'], 400);
        }
    } else {
        // Pas d'action spécifiée
        sendJsonResponse(['success' => false, 'message' => 'Action non spécifiée'], 400);
    }
} else {
    // Méthode non supportée
    sendJsonResponse(['success' => false, 'message' => 'Méthode non supportée'], 405);
}

} catch (PDOException $e) {
    // En cas d'erreur de base de données, renvoyer une réponse d'erreur
    sendJsonResponse(['success' => false, 'message' => 'Erreur de base de données: ' . $e->getMessage()], 500);
} catch (Exception $e) {
    // En cas d'erreur générale, renvoyer une réponse d'erreur
    sendJsonResponse(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()], 500);
}
?>
