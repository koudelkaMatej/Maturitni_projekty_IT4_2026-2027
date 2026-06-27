<?php
/*
 * Copyright (C) 2026 INTEWAY TECHNOLOGY - All Rights Reserved
 *
 * Unauthorized copying or redistribution of this file in source
 * and binary forms via any medium is strictly prohibited.
 */

/**
 * Data transfer object representing a teacher record.
 */
class Teacher implements JsonSerializable
{
    /** Auto-increment primary key. */
    public readonly int $teacher_id;

    /** Full name of the teacher. */
    public readonly string $teacher_name;

    /** Optional short code (e.g. initials, max 6 chars). */
    public readonly ?string $teacher_code;

    public function __construct(array $row)
    {
        $this->teacher_id = (int)($row["teacher_id"] ?? 0);
        $this->teacher_name = $row["teacher_name"] ?? "";
        $this->teacher_code = $row["teacher_code"] ?? null;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
