<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

class Ticket implements JsonSerializable
{
    public readonly int $ticket_id;
    public readonly int $ticket_origin;
    public readonly ?int $ticket_category;
    public readonly ?int $ticket_room;
    public readonly ?int $ticket_priority;
    public readonly string $ticket_title;
    public readonly string $ticket_description;
    public readonly ?string $ticket_deadline;
    public readonly bool $ticket_is_open;
    public readonly string $ticket_creation;

    public readonly ?string $teacher_name;
    public readonly ?string $category_name;
    public readonly ?string $room_name;
    public readonly ?string $priority_name;
    public readonly ?int $priority_weight;
    public readonly ?string $priority_color;
    public readonly ?string $assignee_ids;
    public readonly ?string $assignee_names;
    public readonly ?int $assignee_count;

    public function __construct(array $row)
    {
        $this->ticket_id = (int)($row["ticket_id"] ?? 0);
        $this->ticket_origin = (int)($row["ticket_origin"] ?? 0);
        $this->ticket_category = isset($row["ticket_category"]) ? (int)$row["ticket_category"] : null;
        $this->ticket_room = isset($row["ticket_room"]) ? (int)$row["ticket_room"] : null;
        $this->ticket_priority = isset($row["ticket_priority"]) ? (int)$row["ticket_priority"] : null;
        $this->ticket_title = $row["ticket_title"] ?? "";
        $this->ticket_description = $row["ticket_description"] ?? "";
        $this->ticket_deadline = $row["ticket_deadline"] ?? null;
        $this->ticket_is_open = (bool)($row["ticket_is_open"] ?? true);
        $this->ticket_creation = $row["ticket_creation"] ?? "";

        $this->teacher_name = $row["teacher_name"] ?? null;
        $this->category_name = $row["category_name"] ?? null;
        $this->room_name = $row["room_name"] ?? null;
        $this->priority_name = $row["priority_name"] ?? null;
        $this->priority_weight = isset($row["priority_weight"]) ? (int)$row["priority_weight"] : null;
        $this->priority_color = $row["priority_color"] ?? null;
        $this->assignee_ids = $row["assignee_ids"] ?? null;
        $this->assignee_names = $row["assignee_names"] ?? null;
        $this->assignee_count = isset($row["assignee_count"]) ? (int)$row["assignee_count"] : null;
    }

    public function jsonSerialize(): array
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}
