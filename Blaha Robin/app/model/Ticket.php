<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

/**
 * Data transfer object representing a single ticket.
 *
 * Contains both the core ticket columns and nullable joined
 * fields from related tables (teacher, category, room, priority).
 */
class Ticket implements JsonSerializable
{
    /** Auto-increment primary key. */
    public readonly int $ticket_id;

    /** FK to the teacher who reported the issue (nullable — the teacher may have been deleted). */
    public readonly ?int $ticket_origin;

    /** FK to the ticket category (nullable). */
    public readonly ?int $ticket_category;

    /** FK to the room (nullable). */
    public readonly ?int $ticket_room;

    /** FK to the priority (nullable). */
    public readonly ?int $ticket_priority;

    /** Short ticket title / subject. */
    public readonly string $ticket_title;

    /** Detailed description of the issue. */
    public readonly string $ticket_description;

    /** Optional deadline date (Y-m-d). */
    public readonly ?string $ticket_deadline;

    /** Whether the ticket is still open. */
    public readonly bool $ticket_is_open;

    /** Creation datetime (Y-m-d H:i:s). */
    public readonly string $ticket_creation;

    /** Joined teacher name (populated via JOIN). */
    public readonly ?string $teacher_name;

    /** Joined category name. */
    public readonly ?string $category_name;

    /** Joined room name. */
    public readonly ?string $room_name;

    /** Joined priority name. */
    public readonly ?string $priority_name;

    /** Joined priority weight. */
    public readonly ?int $priority_weight;

    /** Joined priority color key. */
    public readonly ?string $priority_color;

    /** Comma-separated assignment user IDs. */
    public readonly ?string $assignee_ids;

    /** Comma-separated assignee teacher names. */
    public readonly ?string $assignee_names;

    /** Number of users assigned to this ticket. */
    public readonly ?int $assignee_count;

    public function __construct(array $row)
    {
        $this->ticket_id = (int)($row["ticket_id"] ?? 0);
        $this->ticket_origin = isset($row["ticket_origin"]) ? (int)$row["ticket_origin"] : null;
        $this->ticket_category = isset($row["ticket_category"]) ? (int)$row["ticket_category"] : null;
        $this->ticket_room = isset($row["ticket_room"]) ? (int)$row["ticket_room"] : null;
        $this->ticket_priority = isset($row["ticket_priority"]) ? (int)$row["ticket_priority"] : null;
        $this->ticket_title = $row["ticket_title"] ?? "";
        $this->ticket_description = $row["ticket_description"] ?? "";
        $this->ticket_deadline = $row["ticket_deadline"] ?? null;
        $this->ticket_is_open = (bool)($row["ticket_is_open"] ?? true);
        $this->ticket_creation = $row["ticket_creation"] ?? "";

        $this->teacher_name = $row["teacher_name"] ?? ($this->ticket_origin === null ? "Smazaný uživatel" : null);
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
