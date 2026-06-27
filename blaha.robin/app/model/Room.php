<?php

class Room implements JsonSerializable
{
    public readonly int $room_id;
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
