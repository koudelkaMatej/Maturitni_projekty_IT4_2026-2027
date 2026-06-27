<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

class Assignment implements JsonSerializable
{
    public readonly int $assignment_id;
    public readonly int $assignment_ticket;
    public readonly int $assignment_user;
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
