<?php

require_once __DIR__ . '/../config/db.php';

class Exercise
{
    public const DELETE_SUCCESS = 'deleted';
    public const DELETE_NOT_FOUND = 'not_found';
    public const DELETE_IN_USE = 'in_use';

    private $db;

    public function __construct()
    {
        $this->db = getDB();
    }

    public function getAll()
    {
        $sql = 'SELECT id, name, category, description
                FROM exercises
                ORDER BY name ASC';

        $result = $this->db->query($sql);

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function create($name, $category, $description)
    {
        $sql = 'INSERT INTO exercises (name, category, description)
                VALUES (?, ?, ?)';

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('sss', $name, $category, $description);
        $stmt->execute();

        return $this->db->insert_id;
    }

    public function update($exerciseId, $name, $category, $description)
    {
        $this->db->begin_transaction();

        try {
            $existsSql = 'SELECT id
                          FROM exercises
                          WHERE id = ?
                          FOR UPDATE';

            $existsStmt = $this->db->prepare($existsSql);
            $existsStmt->bind_param('i', $exerciseId);
            $existsStmt->execute();

            if ($existsStmt->get_result()->num_rows === 0) {
                $this->db->rollback();
                return false;
            }

            $updateSql = 'UPDATE exercises
                          SET name = ?, category = ?, description = ?
                          WHERE id = ?';

            $updateStmt = $this->db->prepare($updateSql);
            $updateStmt->bind_param(
                'sssi',
                $name,
                $category,
                $description,
                $exerciseId
            );
            $updateStmt->execute();

            $this->db->commit();

            return true;
        } catch (Throwable $error) {
            $this->db->rollback();

            throw $error;
        }
    }

    public function delete($exerciseId)
    {
        $this->db->begin_transaction();

        try {
            $existsSql = 'SELECT id
                          FROM exercises
                          WHERE id = ?
                          FOR UPDATE';

            $existsStmt = $this->db->prepare($existsSql);
            $existsStmt->bind_param('i', $exerciseId);
            $existsStmt->execute();

            if ($existsStmt->get_result()->num_rows === 0) {
                $this->db->rollback();
                return self::DELETE_NOT_FOUND;
            }

            $usageSql = 'SELECT id
                         FROM workout_exercises
                         WHERE exercise_id = ?
                         LIMIT 1
                         FOR UPDATE';

            $usageStmt = $this->db->prepare($usageSql);
            $usageStmt->bind_param('i', $exerciseId);
            $usageStmt->execute();

            if ($usageStmt->get_result()->num_rows > 0) {
                $this->db->rollback();
                return self::DELETE_IN_USE;
            }

            $deleteSql = 'DELETE FROM exercises
                          WHERE id = ?';

            $deleteStmt = $this->db->prepare($deleteSql);
            $deleteStmt->bind_param('i', $exerciseId);
            $deleteStmt->execute();

            if ($deleteStmt->affected_rows === 0) {
                $this->db->rollback();
                return self::DELETE_NOT_FOUND;
            }

            $this->db->commit();

            return self::DELETE_SUCCESS;
        } catch (Throwable $error) {
            $this->db->rollback();

            throw $error;
        }
    }
}
