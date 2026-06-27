<?php

class RoomRepository extends Repository
{
    public function getRoomById($room_id): ?Room
    {
        $row = $this->database->selectOne(
            "SELECT * FROM rooms WHERE room_id = :room_id",
            [":room_id" => $room_id]
        );
        return $row ? new Room($row) : null;
    }

    public function getAllRooms(): array
    {
        $rows = $this->database->select("SELECT * FROM rooms");
        return array_map(fn($r) => new Room($r), $rows);
    }

    public function addRoom($room_name): false|string
    {
        return $this->database->insert(
            "INSERT INTO rooms (room_name) VALUES (:room_name)",
            [":room_name" => $room_name]
        );
    }

    public function updateRoom($room_id, $room_name): void
    {
        $this->database->update(
            "UPDATE rooms SET room_name = :room_name WHERE room_id = :room_id",
            [":room_id" => $room_id, ":room_name" => $room_name]
        );
    }

    public function deleteRoom($room_id): void
    {
        $this->database->delete(
            "DELETE FROM rooms WHERE room_id = :room_id",
            [":room_id" => $room_id]
        );
    }
}
