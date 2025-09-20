<?php

class PriorityRepository extends Repository
{
    public function getPriorityById($priority_id): array
    {
        return $this->database->selectOne(
            "SELECT * FROM priorities WHERE priority_id = :priority_id",
            [
                ":priority_id" => $priority_id,
            ]
        );
    }

    public function getAllPriorities(): array
    {
        return $this->database->select(
            "SELECT * FROM priorities"
        );
    }

    public function addPriority($priority_name, $priority_weight, $priority_color): false|string
    {
        return $this->database->insert(
            "INSERT INTO priorities (priority_name, priority_weight, priority_color) VALUES (:priority_name, :priority_weight, :priority_color)",
            [
                ":priority_name" => $priority_name,
                ":priority_weight" => $priority_weight,
                ":priority_color" => $priority_color,
            ]
        );
    }

    public function updatePriority($priority_id, $priority_name, $priority_weight, $priority_color): void
    {
        $this->database->update(
            "UPDATE priorities SET priority_name = :priority_name, priority_weight = :priority_weight, priority_color = :priority_color WHERE priority_id = :priority_id",
            [
                ":priority_id" => $priority_id,
                ":priority_name" => $priority_name,
                ":priority_weight" => $priority_weight,
                ":priority_color" => $priority_color,
            ]
        );
    }

    public function deletePriority($priority_id): void
    {
        $this->database->delete(
            "DELETE FROM priorities WHERE priority_id = :priority_id",
            [
                ":priority_id" => $priority_id,
            ]
        );
    }
}