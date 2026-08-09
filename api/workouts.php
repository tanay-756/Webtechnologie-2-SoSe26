<?php

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../classes/Workout.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);

    echo json_encode([
        'success' => false,
        'message' => 'Du bist nicht angemeldet.'
    ]);
    exit;
}

$workout = new Workout();
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        echo json_encode([
            'success' => true,
            'workouts' => $workout->getAllByUser(
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

        $title = trim($data['title'] ?? '');
        $date = trim($data['date'] ?? '');
        $durationMinutes =
            intval($data['duration_minutes'] ?? 0);
        $notes = trim($data['notes'] ?? '');
        $exerciseId = intval($data['exercise_id'] ?? 0);
        $sets = intval($data['sets'] ?? 0);
        $reps = intval($data['reps'] ?? 0);
        $weightKg = floatval($data['weight_kg'] ?? 0);

        if ($title === '' || $date === '') {
            http_response_code(422);

            echo json_encode([
                'success' => false,
                'message' =>
                    'Titel und Datum sind erforderlich.'
            ]);
            exit;
        }

        $dateObject = DateTime::createFromFormat(
            'Y-m-d',
            $date
        );

        if (
            !$dateObject ||
            $dateObject->format('Y-m-d') !== $date
        ) {
            http_response_code(422);

            echo json_encode([
                'success' => false,
                'message' => 'Das Datum ist ungültig.'
            ]);
            exit;
        }

        if ($exerciseId <= 0) {
            http_response_code(422);

            echo json_encode([
                'success' => false,
                'message' => 'Bitte eine Übung auswählen.'
            ]);
            exit;
        }

        if (
            $durationMinutes < 0 ||
            $sets < 0 ||
            $reps < 0 ||
            $weightKg < 0
        ) {
            http_response_code(422);

            echo json_encode([
                'success' => false,
                'message' =>
                    'Zahlen dürfen nicht negativ sein.'
            ]);
            exit;
        }

        $workoutId = $workout->create(
            $_SESSION['user_id'],
            $title,
            $date,
            $durationMinutes,
            $notes,
            $exerciseId,
            $sets,
            $reps,
            $weightKg
        );

        http_response_code(201);

        echo json_encode([
            'success' => true,
            'message' => 'Workout wurde gespeichert.',
            'id' => $workoutId
        ]);
        exit;
    }

    if ($method === 'PATCH') {
        $data = json_decode(
            file_get_contents('php://input'),
            true
        );

        $workoutId = intval($data['id'] ?? 0);
        $title = trim($data['title'] ?? '');
        $date = trim($data['date'] ?? '');
        $durationMinutes =
            intval($data['duration_minutes'] ?? 0);
        $notes = trim($data['notes'] ?? '');
        $exerciseId = intval($data['exercise_id'] ?? 0);
        $sets = intval($data['sets'] ?? 0);
        $reps = intval($data['reps'] ?? 0);
        $weightKg = floatval($data['weight_kg'] ?? 0);

        if ($workoutId <= 0) {
            http_response_code(422);

            echo json_encode([
                'success' => false,
                'message' => 'Die Workout-ID ist ungültig.'
            ]);
            exit;
        }

        if ($title === '' || $date === '') {
            http_response_code(422);

            echo json_encode([
                'success' => false,
                'message' =>
                    'Titel und Datum sind erforderlich.'
            ]);
            exit;
        }

        $dateObject = DateTime::createFromFormat(
            'Y-m-d',
            $date
        );

        if (
            !$dateObject ||
            $dateObject->format('Y-m-d') !== $date
        ) {
            http_response_code(422);

            echo json_encode([
                'success' => false,
                'message' => 'Das Datum ist ungültig.'
            ]);
            exit;
        }

        if ($exerciseId <= 0) {
            http_response_code(422);

            echo json_encode([
                'success' => false,
                'message' => 'Bitte eine Übung auswählen.'
            ]);
            exit;
        }

        if (
            $durationMinutes < 0 ||
            $sets < 0 ||
            $reps < 0 ||
            $weightKg < 0
        ) {
            http_response_code(422);

            echo json_encode([
                'success' => false,
                'message' =>
                    'Zahlen dürfen nicht negativ sein.'
            ]);
            exit;
        }

        $updated = $workout->update(
            $workoutId,
            $_SESSION['user_id'],
            $title,
            $date,
            $durationMinutes,
            $notes,
            $exerciseId,
            $sets,
            $reps,
            $weightKg
        );

        if (!$updated) {
            http_response_code(404);

            echo json_encode([
                'success' => false,
                'message' => 'Workout wurde nicht gefunden.'
            ]);
            exit;
        }

        http_response_code(200);

        echo json_encode([
            'success' => true,
            'message' => 'Workout wurde aktualisiert.'
        ]);
        exit;
    }

    if ($method === 'DELETE') {
        $data = json_decode(
            file_get_contents('php://input'),
            true
        );

        $workoutId = intval($data['id'] ?? 0);

        if ($workoutId <= 0) {
            http_response_code(422);

            echo json_encode([
                'success' => false,
                'message' => 'Die Workout-ID ist ungültig.'
            ]);
            exit;
        }

        $deleted = $workout->delete(
            $workoutId,
            $_SESSION['user_id']
        );

        if (!$deleted) {
            http_response_code(404);

            echo json_encode([
                'success' => false,
                'message' => 'Workout wurde nicht gefunden.'
            ]);
            exit;
        }

        http_response_code(200);

        echo json_encode([
            'success' => true,
            'message' => 'Workout wurde gelöscht.'
        ]);
        exit;
    }

    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'HTTP-Methode nicht erlaubt.'
    ]);
} catch (InvalidArgumentException $error) {
    http_response_code(422);

    echo json_encode([
        'success' => false,
        'message' => $error->getMessage()
    ]);
} catch (Throwable $error) {
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' =>
            'Workout konnte nicht verarbeitet werden.'
    ]);
}
