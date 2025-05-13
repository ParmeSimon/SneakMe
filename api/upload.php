<?php
/**
 * API pour la gestion des uploads d'images
 * 
 * Permet de télécharger des images dans le dossier data
 * et de récupérer la liste des images disponibles
 */

// Inclusion du fichier CORS
require_once __DIR__ . '/cors.php';

// Inclusion des utilitaires
require_once __DIR__ . '/utils.php';

// Définir le dossier de destination pour les uploads
$uploadDir = __DIR__ . '/../data/';

// Vérifier que le dossier existe, sinon le créer
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Traitement en fonction de la méthode HTTP
if (isGetRequest()) {
    // Récupération de la liste des images
    $images = [];
    $files = scandir($uploadDir);
    
    // URL de base pour accéder aux images
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . 
               "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/../data/';
    
    // Parcourir les fichiers et ajouter uniquement les images
    foreach ($files as $file) {
        // Ignorer les répertoires . et ..
        if ($file === '.' || $file === '..') {
            continue;
        }
        
        // Vérifier si c'est une image
        $filePath = $uploadDir . $file;
        $fileInfo = pathinfo($filePath);
        $extension = strtolower($fileInfo['extension'] ?? '');
        
        // Liste des extensions d'images autorisées
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($extension, $allowedExtensions)) {
            $images[] = [
                'filename' => $file,
                'url' => $baseUrl . $file,
                'size' => filesize($filePath),
                'uploaded_at' => date('Y-m-d H:i:s', filemtime($filePath))
            ];
        }
    }
    
    // Envoyer la réponse JSON
    sendJsonResponse($images);
    
} elseif (isPostRequest()) {
    // Vérifier si un fichier a été envoyé
    if (!isset($_FILES['image'])) {
        sendJsonResponse(['error' => 'Aucun fichier n\'a été envoyé'], 400);
    }
    
    $file = $_FILES['image'];
    
    // Vérifier s'il y a eu une erreur lors de l'upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'Le fichier dépasse la taille maximale autorisée par PHP',
            UPLOAD_ERR_FORM_SIZE => 'Le fichier dépasse la taille maximale autorisée par le formulaire',
            UPLOAD_ERR_PARTIAL => 'Le fichier n\'a été que partiellement téléchargé',
            UPLOAD_ERR_NO_FILE => 'Aucun fichier n\'a été téléchargé',
            UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant',
            UPLOAD_ERR_CANT_WRITE => 'Échec de l\'écriture du fichier sur le disque',
            UPLOAD_ERR_EXTENSION => 'Une extension PHP a arrêté l\'upload du fichier'
        ];
        
        $errorMessage = $errorMessages[$file['error']] ?? 'Erreur inconnue lors de l\'upload';
        sendJsonResponse(['error' => $errorMessage], 400);
    }
    
    // Vérifier le type de fichier
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        sendJsonResponse(['error' => 'Type de fichier non autorisé. Seuls les formats JPEG, PNG, GIF et WEBP sont acceptés'], 400);
    }
    
    // Générer un nom de fichier unique pour éviter les collisions
    $fileInfo = pathinfo($file['name']);
    $extension = strtolower($fileInfo['extension']);
    $newFilename = uniqid() . '.' . $extension;
    $destination = $uploadDir . $newFilename;
    
    // Déplacer le fichier téléchargé vers le dossier de destination
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        // URL de base pour accéder à l'image
        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . 
                   "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/../data/';
        
        // Renvoyer les informations sur le fichier téléchargé
        sendJsonResponse([
            'success' => true,
            'filename' => $newFilename,
            'original_filename' => $file['name'],
            'url' => $baseUrl . $newFilename,
            'size' => $file['size'],
            'type' => $file['type'],
            'uploaded_at' => date('Y-m-d H:i:s')
        ], 201);
    } else {
        sendJsonResponse(['error' => 'Erreur lors de l\'enregistrement du fichier'], 500);
    }
} else {
    // Méthode non supportée
    sendJsonResponse(['error' => 'Méthode non supportée'], 405);
}
?>
