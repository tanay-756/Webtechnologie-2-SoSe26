<?php

require_once __DIR__ . '/../config/db.php';

class Exercise
{
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
}