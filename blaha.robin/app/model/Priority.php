<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

/**
 * Data transfer object representing a ticket priority.
 */
class Priority implements JsonSerializable
{
    /** Auto-increment primary key. */
    public readonly int $priority_id;

    /** Human-readable priority name. */
    public readonly string $priority_name;

    /** Numeric weight used for sorting (higher = more important). */
    public readonly int $priority_weight;

    /** CSS colour key: gray|blue|green|orange|red. */
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
