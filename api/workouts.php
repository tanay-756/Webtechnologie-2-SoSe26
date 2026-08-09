<?php

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../classes/Workout.php';

function normalizeWorkoutExercises($data)
{
    if (!is_array($data)) {
        throw new InvalidArgumentException(
            'Die Anfrage enthält keine gültigen Daten.'
        );
    }

    if (array_key_exists('exercises', $data)) {
        $exerciseData = $data['exercises'];

        if (!is_array($exerciseData) || !array_is_list($exerciseData)) {
            throw new InvalidArgumentException(
                'Die Übungen müssen als Array gesendet werden.'
            );
        }
    } else {
        $exerciseData = [[
            'exercise_id' => $data['exercise_id'] ?? 0,
            'sets' => $data['sets'] ?? 0,
            'reps' => $data['reps'] ?? 0,
            'weight_kg' => $data['weight_kg'] ?? 0
        ]];
    }

    if (count($exerciseData) === 0) {
        throw new InvalidArgumentException(
            'Mindestens eine Übung ist erforderlich.'
        );
    }

    $exercises = [];

    foreach ($exerciseData as $exercise) {
        if (!is_array($exercise)) {
            throw new InvalidArgumentException(
                'Die Übungsdaten sind ungültig.'
            );
        }

        $exerciseId = filter_var(
            $exercise['exercise_id'] ?? null,
            FILTER_VALIDATE_INT
        );

        if ($exerciseId === false || $exerciseId <= 0) {
            throw new InvalidArgumentException(
                'Bitte für jede Position eine gültige Übung auswählen.'
            );
        }

        $sets = normalizeWorkoutExerciseInteger(
            $exercise['sets'] ?? 0
        );

        $reps = normalizeWorkoutExerciseInteger(
            $exercise['reps'] ?? 0
        );

        $weightKg = $exercise['weight_kg'] ?? 0;

        if (!is_numeric($weightKg) || (float) $weightKg < 0) {
            throw new InvalidArgumentException(
                'Die Übungswerte sind ungültig.'
            );
        }

        $exercises[] = [
            'exercise_id' => $exerciseId,
            'sets' => $sets,
            'reps' => $reps,
            'weight_kg' => (float) $weightKg
        ];
    }

    return $exercises;
}

function normalizeWorkoutExerciseInteger($value)
{
    $number = filter_var($value, FILTER_VALIDATE_INT);

    if ($number === false || $number < 0) {
        throw new InvalidArgumentException(
            'Die Übungswerte sind ungültig.'
        );
    }

    return $number;
}

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

        $exercises = normalizeWorkoutExercises($data);
        $title = trim($data['title'] ?? '');
        $date = trim($data['date'] ?? '');
        $durationMinutes =
            intval($data['duration_minutes'] ?? 0);
        $notes = trim($data['notes'] ?? '');

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

        if ($durationMinutes < 0) {
            http_response_code(422);

            echo json_encode([
                'success' => false,
                'message' => 'Die Dauer darf nicht negativ sein.'
            ]);
            exit;
        }

        $workoutId = $workout->create(
            $_SESSION['user_id'],
            $title,
            $date,
            $durationMinutes,
            $notes,
            $exercises
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

        $exercises = normalizeWorkoutExercises($data);
        $workoutId = intval($data['id'] ?? 0);
        $title = trim($data['title'] ?? '');
        $date = trim($data['date'] ?? '');
        $durationMinutes =
            intval($data['duration_minutes'] ?? 0);
        $notes = trim($data['notes'] ?? '');

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

        if ($durationMinutes < 0) {
            http_response_code(422);

            echo json_encode([
                'success' => false,
                'message' => 'Die Dauer darf nicht negativ sein.'
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
            $exercises
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
