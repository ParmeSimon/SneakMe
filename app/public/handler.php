<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");


$host = "localhost";
$dbname = "sneakme";
$username = "root";
$password = "";

$action = isset($_GET['action']) ? $_GET['action'] : null;
$data = json_decode(file_get_contents("php://input"), true);

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["error" => "Erreur de connexion: " . $e->getMessage()]);
    exit;
}

if ($action === 'chatbot') {
    chatbot($data);
}

function chatbot($data) {
    global $pdo;

    $mot_cle = isset($data['prompt']) ? $data['prompt'] : '';

    if (empty($mot_cle)) {
        echo json_encode(["response" => "Aucun mot-clé fourni"]);
        return;
    }

    $stmt = $pdo->prepare("SELECT reponse FROM chatbot WHERE mot_cle LIKE :mot_cle LIMIT 1");
    $stmt->execute([':mot_cle' => "%" . $mot_cle . "%"]);

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        "response" => $result ? $result['reponse'] : "Je n’ai pas compris votre demande."
    ]);
}
