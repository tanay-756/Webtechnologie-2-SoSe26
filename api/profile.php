<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../classes/User.php';

// Nicht eingeloggt
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Nicht eingeloggt']);
    exit;
}

// Nur POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Nur POST erlaubt']);
    exit;
}

// Eingaben lesen
$data      = json_decode(file_get_contents('php://input'), true);
$username  = trim($data['username'] ?? '');
$email     = trim($data['email'] ?? '');
$weight_kg = floatval($data['weight_kg'] ?? 0);
$height_cm = floatval($data['height_cm'] ?? 0);

// Accountdaten prüfen
if (!$username || !$email) {
    echo json_encode(['success' => false, 'message' => 'Bitte alle Felder ausfüllen.']);
    exit;
}

if (strlen($username) > 50) {
    echo json_encode([
        'success' => false,
        'message' => 'Der Benutzername darf höchstens 50 Zeichen lang sein.'
    ]);
    exit;
}

if (strlen($email) > 100) {
    echo json_encode(['success' => false, 'message' => 'Die E-Mail-Adresse ist zu lang.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Bitte eine gültige E-Mail-Adresse eingeben.']);
    exit;
}

// Werte prüfen
if (
    $weight_kg < 20 ||
    $weight_kg > 500 ||
    $height_cm < 50 ||
    $height_cm > 300
) {
    echo json_encode([
        'success' => false,
        'message' => 'Bitte gib realistische Werte für Gewicht und Größe ein.'
    ]);
    exit;
}

// Benutzername und E-Mail müssen eindeutig bleiben
$user = new User();
if ($user->accountDataExists($_SESSION['user_id'], $username, $email)) {
    echo json_encode([
        'success' => false,
        'message' => 'Benutzername oder E-Mail-Adresse ist bereits vergeben.'
    ]);
    exit;
}

// Profil aktualisieren
$result = $user->updateProfile(
    $_SESSION['user_id'],
    $username,
    $email,
    $weight_kg,
    $height_cm
);

if ($result) {
    // Der neue Name soll direkt auch auf dem Dashboard erscheinen.
    $_SESSION['username'] = $username;
    echo json_encode(['success' => true, 'message' => 'Profil gespeichert']);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Profil konnte nicht gespeichert werden. Benutzername oder E-Mail-Adresse ist eventuell bereits vergeben.'
    ]);
}
