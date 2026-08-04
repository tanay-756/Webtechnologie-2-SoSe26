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
$weight_kg = floatval($data['weight_kg'] ?? 0);
$height_cm = floatval($data['height_cm'] ?? 0);

// Werte prüfen
if ($weight_kg <= 0 || $height_cm <= 0) {
    echo json_encode(['success' => false, 'message' => 'Ungültige Werte']);
    exit;
}

// Profil aktualisieren
$user   = new User();
$result = $user->updateProfile($_SESSION['user_id'], $weight_kg, $height_cm);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Profil gespeichert']);
} else {
    echo json_encode(['success' => false, 'message' => 'Fehler beim Speichern']);
}
