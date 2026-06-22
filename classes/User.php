<?php
// Datenbankverbindung einbinden
require_once __DIR__ . '/../config/db.php';

// Klasse für alle Nutzer-bezogenen Operationen
class User {
    private $db; // Datenbankverbindung

    // Konstruktor: DB-Verbindung herstellen
    public function __construct() {
        $this->db = getDB();
    }

    // Neuen Nutzer registrieren
    public function register($username, $email, $password) {
        $hash = password_hash($password, PASSWORD_BCRYPT); // Passwort hashen
        $stmt = $this->db->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $hash); // Parameter binden
        return $stmt->execute();
    }

    // Nutzer einloggen – gibt Nutzerdaten zurück oder false
    public function login($email, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        // Passwort prüfen
        if ($result && password_verify($password, $result['password_hash'])) {
            return $result; // Login erfolgreich
        }
        return false; // Login fehlgeschlagen
    }

    // Profil eines Nutzers abrufen (ohne Passwort-Hash)
    public function getProfile($user_id) {
        $stmt = $this->db->prepare("SELECT id, username, email, weight_kg, height_cm, created_at FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Körperdaten aktualisieren
    public function updateProfile($user_id, $weight_kg, $height_cm) {
        $stmt = $this->db->prepare("UPDATE users SET weight_kg = ?, height_cm = ? WHERE id = ?");
        $stmt->bind_param("ddi", $weight_kg, $height_cm, $user_id);
        return $stmt->execute();
    }
}
?>
