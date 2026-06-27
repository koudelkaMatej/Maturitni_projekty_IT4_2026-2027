<?php

class Priority implements JsonSerializable
{
    public readonly int $priority_id;
    public readonly string $priority_name;
    public readonly int $priority_weight;
    public readonly string $priority_color;

    public function __construct(array $row)
    {
        $this->priority_id = (int)($row["priority_id"] ?? 0);
        $this->priority_name = $row["priority_name"] ?? "";
        $this->priority_weight = (int)($row["priority_weight"] ?? 0);
        $this->priority_color = $row["priority_color"] ?? "gray";
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
