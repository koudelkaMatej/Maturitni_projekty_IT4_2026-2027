<?php

class PriorityRepository extends Repository
{
    public function getPriorityById($priority_id): ?Priority
    {
        $row = $this->database->selectOne(
            "SELECT * FROM priorities WHERE priority_id = :priority_id",
            [":priority_id" => $priority_id]
        );
        return $row ? new Priority($row) : null;
    }

    public function getAllPriorities(): array
    {
        $rows = $this->database->select("SELECT * FROM priorities");
        return array_map(fn($r) => new Priority($r), $rows);
    }

    public function addPriority($priority_name, $priority_weight, $priority_color): false|string
    {
        return $this->database->insert(
            "INSERT INTO priorities (priority_name, priority_weight, priority_color) VALUES (:priority_name, :priority_weight, :priority_color)",
            [":priority_name" => $priority_name, ":priority_weight" => $priority_weight, ":priority_color" => $priority_color]
        );
    }

    public function updatePriority($priority_id, $priority_name, $priority_weight, $priority_color): void
    {
        $this->database->update(
            "UPDATE priorities SET priority_name = :priority_name, priority_weight = :priority_weight, priority_color = :priority_color WHERE priority_id = :priority_id",
            [":priority_id" => $priority_id, ":priority_name" => $priority_name, ":priority_weight" => $priority_weight, ":priority_color" => $priority_color]
        );
    }

    public function deletePriority($priority_id): void
    {
        $this->database->delete(
            "DELETE FROM priorities WHERE priority_id = :priority_id",
            [":priority_id" => $priority_id]
        );
    }
}
