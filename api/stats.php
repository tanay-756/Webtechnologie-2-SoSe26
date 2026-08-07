<?php

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../classes/Workout.php';
require_once __DIR__ . '/../classes/Goal.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);

    echo json_encode([
        'success' => false,
        'message' => 'Du bist nicht angemeldet.'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Allow: GET');

    echo json_encode([
        'success' => false,
        'message' => 'HTTP-Methode nicht erlaubt.'
    ]);
    exit;
}

try {
    $workout = new Workout();
    $goal = new Goal();

    $workoutSummary = $workout->getSummaryByUser(
        $_SESSION['user_id']
    );

    $recentWorkouts = $workout->getRecentByUser(
        $_SESSION['user_id']
    );

    $activeGoals = $goal->getActiveByUser(
        $_SESSION['user_id']
    );

    echo json_encode([
        'success' => true,
        'data' => [
            'total_workouts' =>
                $workoutSummary['total_workouts'],
            'total_training_minutes' =>
                $workoutSummary['total_training_minutes'],
            'active_goals_count' => count($activeGoals),
            'recent_workouts' => $recentWorkouts,
            'active_goals' => $activeGoals
        ]
    ]);
} catch (Throwable $error) {
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' =>
            'Dashboard-Daten konnten nicht geladen werden.'
    ]);
}
