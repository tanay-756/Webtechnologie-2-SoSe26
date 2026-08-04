<?php
// JSON-Antworten
header('Content-Type: application/json');

// User-Klasse einbinden
require_once __DIR__ . '/../classes/User.php';

// Nur POST erlaubt
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Nur POST erlaubt']);
    exit;
}

// Eingaben aus JSON-Body lesen
$data = json_decode(file_get_contents('php://input'), true);

$username = trim($data['username'] ?? '');
$email    = trim($data['email'] ?? '');
$password = trim($data['password'] ?? '');

// Pflichtfelder prüfen
if (!$username || !$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'Alle Felder ausfüllen']);
    exit;
}

// E-Mail validieren
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Ungültige E-Mail']);
    exit;
}

// Registrierung ausführen
$user = new User();
$result = $user->register($username, $email, $password);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Registrierung erfolgreich']);
} else {
    echo json_encode(['success' => false, 'message' => 'E-Mail oder Username bereits vergeben']);
}
