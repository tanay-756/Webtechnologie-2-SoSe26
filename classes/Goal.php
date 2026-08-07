<?php

require_once __DIR__ . '/../config/db.php';

class Goal
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
                id,
                description,
                target_value,
                current_value,
                unit,
                deadline,
                status
            FROM goals
            WHERE user_id = ?
            ORDER BY
                status = "aktiv" DESC,
                deadline IS NULL,
                deadline ASC,
                id DESC
        ';

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $userId);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(
            MYSQLI_ASSOC
        );
    }

    public function create(
        $userId,
        $description,
        $targetValue,
        $currentValue,
        $unit,
        $deadline
    ) {
        $sql = '
            INSERT INTO goals (
                user_id,
                description,
                target_value,
                current_value,
                unit,
                deadline,
                status
            )
            VALUES (?, ?, ?, ?, ?, ?, "aktiv")
        ';

        $stmt = $this->db->prepare($sql);

        $stmt->bind_param(
            'isddss',
            $userId,
            $description,
            $targetValue,
            $currentValue,
            $unit,
            $deadline
        );

        $stmt->execute();

        return $this->db->insert_id;
    }

    public function updateProgress(
        $goalId,
        $userId,
        $currentValue,
        $status
    ) {
        $sql = '
            UPDATE goals
            SET current_value = ?, status = ?
            WHERE id = ? AND user_id = ?
        ';

        $stmt = $this->db->prepare($sql);

        $stmt->bind_param(
            'dsii',
            $currentValue,
            $status,
            $goalId,
            $userId
        );

        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return true;
        }

        $existsSql = '
            SELECT 1
            FROM goals
            WHERE id = ? AND user_id = ?
        ';

        $existsStmt = $this->db->prepare($existsSql);
        $existsStmt->bind_param('ii', $goalId, $userId);
        $existsStmt->execute();

        return $existsStmt->get_result()->num_rows > 0;
    }
}
