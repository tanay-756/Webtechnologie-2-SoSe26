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
            ORDER BY w.date DESC, w.id DESC, we.id ASC
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

    public function getSummaryByUser($userId)
    {
        $sql = '
            SELECT
                COUNT(*) AS total_workouts,
                COALESCE(SUM(duration_minutes), 0)
                    AS total_training_minutes
            FROM workouts
            WHERE user_id = ?
        ';

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $userId);
        $stmt->execute();

        $summary = $stmt->get_result()->fetch_assoc();

        return [
            'total_workouts' =>
                (int) $summary['total_workouts'],
            'total_training_minutes' =>
                (int) $summary['total_training_minutes']
        ];
    }

    public function getRecentByUser($userId)
    {
        $sql = '
            SELECT
                id,
                title,
                date,
                duration_minutes
            FROM workouts
            WHERE user_id = ?
            ORDER BY date DESC, id DESC
            LIMIT 5
        ';

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $userId);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function create(
        $userId,
        $title,
        $date,
        $durationMinutes,
        $notes,
        $exercises
    ) {
        $this->db->begin_transaction();

        try {
            $this->ensureExercisesExist($exercises, $userId);

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

            $this->insertExercises($workoutId, $exercises);

            $this->db->commit();

            return $workoutId;
        } catch (Throwable $error) {
            $this->db->rollback();

            throw $error;
        }
    }

    public function update(
        $workoutId,
        $userId,
        $title,
        $date,
        $durationMinutes,
        $notes,
        $exercises
    ) {
        $this->db->begin_transaction();

        try {
            $ownerSql = '
                SELECT id
                FROM workouts
                WHERE id = ? AND user_id = ?
                FOR UPDATE
            ';

            $ownerStmt = $this->db->prepare($ownerSql);
            $ownerStmt->bind_param('ii', $workoutId, $userId);
            $ownerStmt->execute();

            if ($ownerStmt->get_result()->num_rows === 0) {
                $this->db->rollback();
                return false;
            }

            $this->ensureExercisesExist($exercises, $userId);

            $workoutSql = '
                UPDATE workouts
                SET
                    title = ?,
                    date = ?,
                    duration_minutes = ?,
                    notes = ?
                WHERE id = ? AND user_id = ?
            ';

            $workoutStmt = $this->db->prepare($workoutSql);

            $workoutStmt->bind_param(
                'ssisii',
                $title,
                $date,
                $durationMinutes,
                $notes,
                $workoutId,
                $userId
            );

            $workoutStmt->execute();

            $deleteExerciseSql = '
                DELETE FROM workout_exercises
                WHERE workout_id = ?
            ';

            $deleteExerciseStmt =
                $this->db->prepare($deleteExerciseSql);

            $deleteExerciseStmt->bind_param('i', $workoutId);
            $deleteExerciseStmt->execute();

            $this->insertExercises($workoutId, $exercises);

            $this->db->commit();

            return true;
        } catch (Throwable $error) {
            $this->db->rollback();

            throw $error;
        }
    }

    public function delete($workoutId, $userId)
    {
        $sql = '
            DELETE FROM workouts
            WHERE id = ? AND user_id = ?
        ';

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ii', $workoutId, $userId);
        $stmt->execute();

        return $stmt->affected_rows > 0;
    }

    private function ensureExercisesExist($exercises, $userId)
    {
        if (!is_array($exercises) || count($exercises) === 0) {
            throw new InvalidArgumentException(
                'Mindestens eine Übung ist erforderlich.'
            );
        }

        foreach ($exercises as $exercise) {
            if (
                !is_array($exercise) ||
                !isset($exercise['exercise_id'])
            ) {
                throw new InvalidArgumentException(
                    'Die Übungsdaten sind ungültig.'
                );
            }

            $this->ensureExerciseExists($exercise['exercise_id'],$userId);
        }
    }

    private function insertExercises($workoutId, $exercises)
    {
        $sql = '
            INSERT INTO workout_exercises
                (workout_id, exercise_id, sets, reps, weight_kg)
            VALUES (?, ?, ?, ?, ?)
        ';

        $stmt = $this->db->prepare($sql);

        $exerciseId = 0;
        $sets = 0;
        $reps = 0;
        $weightKg = 0;

        $stmt->bind_param(
            'iiiid',
            $workoutId,
            $exerciseId,
            $sets,
            $reps,
            $weightKg
        );

        foreach ($exercises as $exercise) {
            $exerciseId = $exercise['exercise_id'];
            $sets = $exercise['sets'];
            $reps = $exercise['reps'];
            $weightKg = $exercise['weight_kg'];

            $stmt->execute();
        }
    }

    private function ensureExerciseExists($exerciseId, $userId)
    {
        $sql = '
            SELECT id
            FROM exercises
            WHERE id = ? AND user_id = ?
            FOR UPDATE
        ';

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ii', $exerciseId, $userId);
        $stmt->execute();

        if ($stmt->get_result()->num_rows === 0) {
            throw new InvalidArgumentException(
                'Die ausgewählte Übung existiert nicht.'
            );
        }
    }
}
