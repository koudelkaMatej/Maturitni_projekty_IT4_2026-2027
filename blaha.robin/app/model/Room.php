<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

/**
 * Data transfer object representing a room / classroom.
 */
class Room implements JsonSerializable
{
    /** Auto-increment primary key. */
    public readonly int $room_id;

    /** Human-readable room name (e.g. "Učebna 205"). */
    public readonly string $room_name;

    public function __construct(array $row)
    {
        $this->room_id = (int)($row["room_id"] ?? 0);
        $this->room_name = $row["room_name"] ?? "";
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
