<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

/**
 * Data transfer object representing a single ticket lifecycle event
 * (created, closed, reopened) for the ticket's activity log.
 */
class TicketEvent implements JsonSerializable
{
    /** Auto-increment primary key. */
    public readonly int $event_id;

    /** FK to the associated ticket. */
    public readonly int $event_ticket;

    /** FK to the user who triggered the event (nullable). */
    public readonly ?int $event_user;

    /** Event type: "created", "closed", or "reopened". */
    public readonly string $event_type;

    /** Creation datetime (Y-m-d H:i:s). */
    public readonly string $event_creation;

    /** Joined teacher name (populated when a JOIN is used). */
    public readonly ?string $teacher_name;

    public function __construct(array $row)
    {
        $this->event_id = (int)($row["event_id"] ?? 0);
        $this->event_ticket = (int)($row["event_ticket"] ?? 0);
        $this->event_user = isset($row["event_user"]) ? (int)$row["event_user"] : null;
        $this->event_type = $row["event_type"] ?? "";
        $this->event_creation = $row["event_creation"] ?? "";
        $this->teacher_name = $row["teacher_name"] ?? null;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
