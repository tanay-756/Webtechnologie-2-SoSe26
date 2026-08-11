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

// Eingaben lesen
$data     = json_decode(file_get_contents('php://input'), true);
$email    = trim($data['email'] ?? '');
$password = trim($data['password'] ?? '');

// Pflichtfelder prüfen
if (!$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'Alle Felder ausfüllen']);
    exit;
}

// Login ausführen
$user   = new User();
$result = $user->login($email, $password);

if ($result) {
    // Session starten
    session_start();
    session_regenerate_id(true);
    $_SESSION['user_id']  = $result['id'];
    $_SESSION['username'] = $result['username'];

    session_write_close();

    echo json_encode(['success' => true, 'message' => 'Login erfolgreich']);
} else {
    echo json_encode(['success' => false, 'message' => 'E-Mail oder Passwort falsch']);
}
