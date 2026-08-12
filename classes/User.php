<?php
// Datenbankverbindung einbinden
require_once __DIR__ . '/../config/db.php';

// Klasse für alle Nutzer-bezogenen Operationen
class User {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    // Neuen Nutzer registrieren
    public function register($username, $email, $password) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $hash);
        try {
            return $stmt->execute(); // Fehler abfangen
        } catch (mysqli_sql_exception $e) {
            return false; // Duplicate entry → false zurückgeben
        }
    }

    // Nutzer einloggen
    public function login($email, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if ($result && password_verify($password, $result['password_hash'])) {
            return $result;
        }
        return false;
    }

    // Profil abrufen
    public function getProfile($user_id) {
        $stmt = $this->db->prepare("SELECT id, username, email, weight_kg, height_cm, created_at FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Prüfen, ob Benutzername oder E-Mail bereits zu einem anderen Konto gehören
    public function accountDataExists($user_id, $username, $email) {
        $stmt = $this->db->prepare(
            "SELECT id FROM users WHERE id != ? AND (username = ? OR email = ?) LIMIT 1"
        );
        $stmt->bind_param("iss", $user_id, $username, $email);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    // Account- und Körperdaten aktualisieren
    public function updateProfile($user_id, $username, $email, $weight_kg, $height_cm) {
        $stmt = $this->db->prepare(
            "UPDATE users SET username = ?, email = ?, weight_kg = ?, height_cm = ? WHERE id = ?"
        );
        $stmt->bind_param("ssddi", $username, $email, $weight_kg, $height_cm, $user_id);

        try {
            return $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            return false;
        }
    }
}
