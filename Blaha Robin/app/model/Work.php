<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

/**
 * Data transfer object representing a work log entry on a ticket.
 */
class Work implements JsonSerializable
{
    /** Auto-increment primary key. */
    public readonly int $work_id;

    /** FK to the associated ticket. */
    public readonly int $work_ticket;

    /** FK to the user (technician) who logged the work. */
    public readonly int $work_user;

    /** Minutes spent. */
    public readonly int $work_minutes;

    /** Free-text description of the work done. */
    public readonly string $work_description;

    /** Creation datetime (Y-m-d H:i:s). */
    public readonly string $work_creation;

    /** Joined teacher name (populated when a JOIN is used). */
    public readonly ?string $teacher_name;

    public function __construct(array $row)
    {
        $this->work_id = (int)($row["work_id"] ?? 0);
        $this->work_ticket = (int)($row["work_ticket"] ?? 0);
        $this->work_user = (int)($row["work_user"] ?? 0);
        $this->work_minutes = (int)($row["work_minutes"] ?? 0);
        $this->work_description = $row["work_description"] ?? "";
        $this->work_creation = $row["work_creation"] ?? "";
        $this->teacher_name = $row["teacher_name"] ?? null;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
