<?php
/**
 * Configuration de la base de données
 * 
 * Ce fichier contient les paramètres de connexion à la base de données.
 */

// Paramètres de connexion à la base de données
define('DB_HOST', 'localhost');     // Hôte de la base de données
define('DB_NAME', 'sneakme');    // Nom de la base de données
define('DB_USER', 'root');          // Utilisateur de la base de données
define('DB_PASS', 'baptiste');              // Mot de passe de la base de données

/**
 * Fonction pour établir une connexion à la base de données
 * 
 * @return PDO Instance de connexion PDO
 */
function getDbConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        // En production, vous voudriez logger cette erreur plutôt que de l'afficher
        die("Erreur de connexion à la base de données: " . $e->getMessage());
    }
}
?>
