<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

/**
 * Data transfer object representing a ticket-to-user assignment.
 */
class Assignment implements JsonSerializable
{
    /** Auto-increment primary key (only present when used standalone). */
    public readonly int $assignment_id;

    /** FK to the assigned ticket. */
    public readonly int $assignment_ticket;

    /** FK to the assigned user (technician). */
    public readonly int $assignment_user;

    /** Joined teacher name (populated when a JOIN is used). */
    public readonly ?string $teacher_name;

    public function __construct(array $row)
    {
        $this->assignment_id = (int)($row["assignment_id"] ?? 0);
        $this->assignment_ticket = (int)($row["assignment_ticket"] ?? 0);
        $this->assignment_user = (int)($row["assignment_user"] ?? 0);
        $this->teacher_name = $row["teacher_name"] ?? null;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
