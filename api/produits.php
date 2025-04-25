<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json");

$pdo = new PDO("mysql:host=localhost;dbname=sneakme", "root", "");

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query("SELECT *, couleurs.label as couleur, pointures.label as pointure, categories.label as categorie, sexes.label as sexe
    FROM produits 
    JOIN couleurs on couleurs.id = produits.id_couleur 
    JOIN pointures on pointures.id = produits.id_pointure 
    JOIN categories on categories.id = produits.id_categorie 
    JOIN sexes on sexes.id = produits.id_sexe

    ");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $stmt = $pdo->prepare("INSERT INTO user (name, email) VALUES (?, ?)");
    $stmt->execute([$data['name'], $data['email']]);
    echo json_encode(['success' => true]);
    exit;
}
