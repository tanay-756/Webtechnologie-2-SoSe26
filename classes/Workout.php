<?php

require_once __DIR__ . '/../config/db.php';

class Workout
{
    private $db;

    public function __construct()
    {
        $this->db = getDB();
    }

    public function getAllByUser($userId)
    {
        $sql = '
            SELECT
                w.id,
                w.title,
                w.date,
                w.duration_minutes,
                w.notes,
                e.id AS exercise_id,
                e.name AS exercise_name,
                e.category,
                we.sets,
                we.reps,
                we.weight_kg
            FROM workouts w
            LEFT JOIN workout_exercises we
                ON we.workout_id = w.id
            LEFT JOIN exercises e
                ON e.id = we.exercise_id
            WHERE w.user_id = ?
            ORDER BY w.date DESC, w.id DESC
        ';

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $userId);
        $stmt->execute();

        $result = $stmt->get_result();
        $workouts = [];

        while ($row = $result->fetch_assoc()) {
            $workoutId = $row['id'];

            if (!isset($workouts[$workoutId])) {
                $workouts[$workoutId] = [
                    'id' => $row['id'],
                    'title' => $row['title'],
                    'date' => $row['date'],
                    'duration_minutes' =>
                        $row['duration_minutes'],
                    'notes' => $row['notes'],
                    'exercises' => []
                ];
            }

            if ($row['exercise_id'] !== null) {
                $workouts[$workoutId]['exercises'][] = [
                    'id' => $row['exercise_id'],
                    'name' => $row['exercise_name'],
                    'category' => $row['category'],
                    'sets' => $row['sets'],
                    'reps' => $row['reps'],
                    'weight_kg' => $row['weight_kg']
                ];
            }
        }

        return array_values($workouts);
    }

    public function create(
        $userId,
        $title,
        $date,
        $durationMinutes,
        $notes,
        $exerciseId,
        $sets,
        $reps,
        $weightKg
    ) {
        $this->db->begin_transaction();

        try {
            $workoutSql = '
                INSERT INTO workouts
                    (user_id, title, date, duration_minutes, notes)
                VALUES (?, ?, ?, ?, ?)
            ';

            $workoutStmt = $this->db->prepare($workoutSql);

            $workoutStmt->bind_param(
                'issis',
                $userId,
                $title,
                $date,
                $durationMinutes,
                $notes
            );

            $workoutStmt->execute();
            $workoutId = $this->db->insert_id;

            $exerciseSql = '
                INSERT INTO workout_exercises
                    (workout_id, exercise_id, sets, reps, weight_kg)
                VALUES (?, ?, ?, ?, ?)
            ';

            $exerciseStmt = $this->db->prepare($exerciseSql);

            $exerciseStmt->bind_param(
                'iiiid',
                $workoutId,
                $exerciseId,
                $sets,
                $reps,
                $weightKg
            );

            $exerciseStmt->execute();

            $this->db->commit();

            return $workoutId;
        } catch (Throwable $error) {
            $this->db->rollback();

            throw $error;
        }
    }
}