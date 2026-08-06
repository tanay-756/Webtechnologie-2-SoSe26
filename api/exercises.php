<?php

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../classes/Exercise.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);

    echo json_encode([
        'success' => false,
        'message' => 'Du bist nicht angemeldet.'
    ]);
    exit;
}

$exercise = new Exercise();
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        echo json_encode([
            'success' => true,
            'exercises' => $exercise->getAll()
        ]);
        exit;
    }

    if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        $name = trim($data['name'] ?? '');
        $category = trim($data['category'] ?? '');
        $description = trim($data['description'] ?? '');

        if ($name === '' || $category === '') {
            http_response_code(422);

            echo json_encode([
                'success' => false,
                'message' => 'Name und Kategorie sind erforderlich.'
            ]);
            exit;
        }

        $allowedCategories = [
            'Kraft',
            'Cardio',
            'Stretching'
        ];

        if (!in_array($category, $allowedCategories, true)) {
            http_response_code(422);

            echo json_encode([
                'success' => false,
                'message' => 'Die Kategorie ist ungültig.'
            ]);
            exit;
        }

        $id = $exercise->create($name, $category, $description);

        http_response_code(201);

        echo json_encode([
            'success' => true,
            'message' => 'Übung wurde gespeichert.',
            'id' => $id
        ]);
        exit;
    }

    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'HTTP-Methode nicht erlaubt.'
    ]);
} catch (mysqli_sql_exception $error) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Die Übung existiert möglicherweise bereits.'
    ]);
}