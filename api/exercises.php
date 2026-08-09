<?php

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../classes/Exercise.php';

function normalizeExerciseInput($data)
{
    if (!is_array($data)) {
        throw new InvalidArgumentException(
            'Die Anfrage enthält keine gültigen Daten.'
        );
    }

    $nameValue = $data['name'] ?? '';
    $categoryValue = $data['category'] ?? '';
    $descriptionValue = $data['description'] ?? '';

    if (
        !is_string($nameValue) ||
        !is_string($categoryValue) ||
        !is_string($descriptionValue)
    ) {
        throw new InvalidArgumentException(
            'Die Angaben sind ungültig.'
        );
    }

    $name = trim($nameValue);
    $category = trim($categoryValue);
    $description = trim($descriptionValue);

    if ($name === '' || $category === '') {
        throw new InvalidArgumentException(
            'Name und Kategorie sind erforderlich.'
        );
    }

    $allowedCategories = [
        'Kraft',
        'Cardio',
        'Stretching'
    ];

    if (!in_array($category, $allowedCategories, true)) {
        throw new InvalidArgumentException(
            'Die Kategorie ist ungültig.'
        );
    }

    return [
        'name' => $name,
        'category' => $category,
        'description' => $description
    ];
}

function normalizeExerciseId($data)
{
    if (!is_array($data)) {
        throw new InvalidArgumentException(
            'Die Übungs-ID ist ungültig.'
        );
    }

    $exerciseId = filter_var(
        $data['id'] ?? null,
        FILTER_VALIDATE_INT
    );

    if ($exerciseId === false || $exerciseId <= 0) {
        throw new InvalidArgumentException(
            'Die Übungs-ID ist ungültig.'
        );
    }

    return $exerciseId;
}

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
        $exerciseData = normalizeExerciseInput($data);

        $id = $exercise->create(
            $exerciseData['name'],
            $exerciseData['category'],
            $exerciseData['description']
        );

        http_response_code(201);

        echo json_encode([
            'success' => true,
            'message' => 'Übung wurde gespeichert.',
            'id' => $id
        ]);
        exit;
    }

    if ($method === 'PATCH') {
        $data = json_decode(file_get_contents('php://input'), true);
        $exerciseId = normalizeExerciseId($data);
        $exerciseData = normalizeExerciseInput($data);

        $updated = $exercise->update(
            $exerciseId,
            $exerciseData['name'],
            $exerciseData['category'],
            $exerciseData['description']
        );

        if (!$updated) {
            http_response_code(404);

            echo json_encode([
                'success' => false,
                'message' => 'Übung wurde nicht gefunden.'
            ]);
            exit;
        }

        http_response_code(200);

        echo json_encode([
            'success' => true,
            'message' => 'Übung wurde aktualisiert.'
        ]);
        exit;
    }

    if ($method === 'DELETE') {
        $data = json_decode(file_get_contents('php://input'), true);
        $exerciseId = normalizeExerciseId($data);
        $deleteResult = $exercise->delete($exerciseId);

        if ($deleteResult === Exercise::DELETE_NOT_FOUND) {
            http_response_code(404);

            echo json_encode([
                'success' => false,
                'message' => 'Übung wurde nicht gefunden.'
            ]);
            exit;
        }

        if ($deleteResult === Exercise::DELETE_IN_USE) {
            http_response_code(409);

            echo json_encode([
                'success' => false,
                'message' =>
                    'Die Übung wird bereits in mindestens einem ' .
                    'Workout verwendet und kann nicht gelöscht werden.'
            ]);
            exit;
        }

        http_response_code(200);

        echo json_encode([
            'success' => true,
            'message' => 'Übung wurde gelöscht.'
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
} catch (mysqli_sql_exception $error) {
    if ((int) $error->getCode() === 1062) {
        http_response_code(409);

        echo json_encode([
            'success' => false,
            'message' =>
                'Eine Übung mit diesem Namen existiert bereits.'
        ]);
        exit;
    }

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Die Übung konnte nicht verarbeitet werden.'
    ]);
} catch (Throwable $error) {
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Die Übung konnte nicht verarbeitet werden.'
    ]);
}
