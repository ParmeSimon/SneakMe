<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    http_response_code(200);
    exit;
}

// Vérifier si la requête est bien de type POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode(["success" => false, "message" => "Méthode non autorisée"]);
    exit;
}

// Vérifier si un fichier a été envoyé
if (!isset($_FILES["image"]) || $_FILES["image"]["error"] != 0) {
    echo json_encode(["success" => false, "message" => "Aucun fichier valide reçu"]);
    exit;
}

// Créer le dossier uploads s'il n'existe pas
$uploadDir = "../public/uploads/";
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Générer un nom de fichier unique
$fileName = uniqid() . "_" . basename($_FILES["image"]["name"]);
$targetFile = $uploadDir . $fileName;

// Vérifier le type de fichier
$imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
$allowedTypes = ["jpg", "jpeg", "png", "gif", "webp"];

if (!in_array($imageFileType, $allowedTypes)) {
    echo json_encode(["success" => false, "message" => "Seuls les fichiers JPG, JPEG, PNG, GIF et WEBP sont autorisés"]);
    exit;
}

// Déplacer le fichier téléchargé
if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
    // Construire l'URL complète de l'image
    $protocol = isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === "on" ? "https" : "http";
    $host = $_SERVER["HTTP_HOST"];
    $imageUrl = "$protocol://$host/SneakMe/public/uploads/$fileName";
    
    echo json_encode(["success" => true, "url" => $imageUrl, "message" => "Image téléchargée avec succès"]);
} else {
    echo json_encode(["success" => false, "message" => "Erreur lors du téléchargement de l'image"]);
}
?> 