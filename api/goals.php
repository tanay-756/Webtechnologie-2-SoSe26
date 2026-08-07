<?php

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../classes/Goal.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);

    echo json_encode([
        'success' => false,
        'message' => 'Du bist nicht angemeldet.'
    ]);
    exit;
}

$goal = new Goal();
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        echo json_encode([
            'success' => true,
            'goals' => $goal->getAllByUser(
                $_SESSION['user_id']
            )
        ]);
        exit;
    }

    if ($method === 'POST') {
        $data = json_decode(
            file_get_contents('php://input'),
            true
        );

        $description = trim(
            $data['description'] ?? ''
        );

        $targetValue = floatval(
            $data['target_value'] ?? 0
        );

        $currentValue = floatval(
            $data['current_value'] ?? 0
        );

        $unit = trim($data['unit'] ?? '');
        $deadline = trim($data['deadline'] ?? '');

        if (
            $description === '' ||
            $targetValue <= 0 ||
            $unit === ''
        ) {
            http_response_code(422);

            echo json_encode([
                'success' => false,
                'message' =>
                    'Beschreibung, Zielwert und Einheit sind erforderlich.'
            ]);
            exit;
        }

        if ($currentValue < 0) {
            http_response_code(422);

            echo json_encode([
                'success' => false,
                'message' =>
                    'Der aktuelle Wert darf nicht negativ sein.'
            ]);
            exit;
        }

        if ($deadline !== '') {
            $dateObject = DateTime::createFromFormat(
                'Y-m-d',
                $deadline
            );

            if (
                !$dateObject ||
                $dateObject->format('Y-m-d') !== $deadline
            ) {
                http_response_code(422);

                echo json_encode([
                    'success' => false,
                    'message' => 'Das Zieldatum ist ungültig.'
                ]);
                exit;
            }
        } else {
            $deadline = null;
        }

        $goalId = $goal->create(
            $_SESSION['user_id'],
            $description,
            $targetValue,
            $currentValue,
            $unit,
            $deadline
        );

        http_response_code(201);

        echo json_encode([
            'success' => true,
            'message' => 'Ziel wurde gespeichert.',
            'id' => $goalId
        ]);
        exit;
    }

    if ($method === 'PATCH') {
        $data = json_decode(
            file_get_contents('php://input'),
            true
        );

        $goalId = intval($data['id'] ?? 0);

        $currentValue = floatval(
            $data['current_value'] ?? 0
        );

        $status = trim($data['status'] ?? 'aktiv');

        $allowedStatuses = [
            'aktiv',
            'erreicht',
            'abgebrochen'
        ];

        if (
            $goalId <= 0 ||
            $currentValue < 0 ||
            !in_array($status, $allowedStatuses, true)
        ) {
            http_response_code(422);

            echo json_encode([
                'success' => false,
                'message' => 'Die Angaben sind ungültig.'
            ]);
            exit;
        }

        $updated = $goal->updateProgress(
            $goalId,
            $_SESSION['user_id'],
            $currentValue,
            $status
        );

        if (!$updated) {
            http_response_code(404);

            echo json_encode([
                'success' => false,
                'message' => 'Ziel wurde nicht gefunden.'
            ]);
            exit;
        }

        echo json_encode([
            'success' => true,
            'message' => 'Ziel wurde aktualisiert.'
        ]);
        exit;
    }

    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'HTTP-Methode nicht erlaubt.'
    ]);
} catch (Throwable $error) {
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' =>
            'Das Ziel konnte nicht verarbeitet werden.'
    ]);
}
