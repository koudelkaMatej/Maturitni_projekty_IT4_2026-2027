<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

/**
 * Repository for room / classroom CRUD.
 */
class RoomRepository extends Repository
{
    /**
     * Get a single room by ID.
     *
     * @param int $room_id
     * @return ?Room
     */
    public function getRoomById($room_id): ?Room
    {
        $row = $this->database->selectOne(
            "SELECT * FROM rooms WHERE room_id = :room_id",
            [":room_id" => $room_id]
        );
        return $row ? new Room($row) : null;
    }

    /**
     * Get all rooms.
     *
     * @return Room[]
     */
    public function getAllRooms(): array
    {
        $rows = $this->database->select("SELECT * FROM rooms");
        return array_map(fn($r) => new Room($r), $rows);
    }

    /**
     * Create a new room.
     *
     * @param string $room_name
     * @return false|string The new room ID, or false on failure.
     */
    public function addRoom($room_name): false|string
    {
        return $this->database->insert(
            "INSERT INTO rooms (room_name) VALUES (:room_name)",
            [":room_name" => $room_name]
        );
    }

    /**
     * Update a room's name.
     *
     * @param int    $room_id
     * @param string $room_name
     */
    public function updateRoom($room_id, $room_name): void
    {
        $this->database->update(
            "UPDATE rooms SET room_name = :room_name WHERE room_id = :room_id",
            [":room_id" => $room_id, ":room_name" => $room_name]
        );
    }

    /**
     * Delete a room. Tickets referencing it will have ticket_room set to NULL.
     *
     * @param int $room_id
     */
    public function deleteRoom($room_id): void
    {
        $this->database->delete(
            "DELETE FROM rooms WHERE room_id = :room_id",
            [":room_id" => $room_id]
        );
    }
}
